<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../uni_auth/lib/pdf_template.php';

if (empty($_SESSION['admin_user'])) { 
    http_response_code(403); 
    exit('Unauthorized'); 
}

$report_id = (int)($_GET['id'] ?? 0);
if (!$report_id) { 
    http_response_code(400); 
    echo 'Bad request'; 
    exit; 
}

// Load comprehensive report data (admin can view all)
$stmt = $pdo->prepare('
    SELECT r.*, u.uni_name 
    FROM reports r 
    LEFT JOIN university u ON r.university_id = u.uni_id 
    WHERE r.id = ?
');
$stmt->execute([$report_id]);
$report = $stmt->fetch();

if (!$report) { 
    http_response_code(404); 
    echo 'Report not found'; 
    exit; 
}

// Load attachments with full details
$stmt = $pdo->prepare('
    SELECT id, original_name, stored_name, mime_type, size_bytes, uploaded_at 
    FROM attachments 
    WHERE report_id = ? 
    ORDER BY uploaded_at ASC
');
$stmt->execute([$report_id]);
$attachments = $stmt->fetchAll();

// Load status history
$stmt = $pdo->prepare('
    SELECT old_status, new_status, changed_at, notes 
    FROM status_history 
    WHERE report_id = ? 
    ORDER BY changed_at ASC
');
$stmt->execute([$report_id]);
$status_history = $stmt->fetchAll();

// Load process timeline if available
$stmt = $pdo->prepare('
    SELECT step_name, step_description, status, assigned_to, started_at, completed_at 
    FROM process_timeline 
    WHERE report_id = ? 
    ORDER BY started_at ASC
');
$stmt->execute([$report_id]);
$process_timeline = $stmt->fetchAll();

// Prepare report data for template
$reportData = [
    'report_code' => $report['report_code'],
    'status' => $report['status'],
    'incident_type' => $report['incident_type'],
    'department' => $report['department'],
    'university_name' => $report['uni_name'] ?? 'All Universities',
    'location' => $report['location'],
    'incident_datetime' => $report['incident_datetime'],
    'created_at' => $report['created_at'],
    'details' => $report['details'],
    'status_history' => $status_history,
    'process_timeline' => $process_timeline
];

// Generate HTML using our template
$html = getPDFTemplate($reportData, $attachments);

// Use Dompdf for better HTML to PDF conversion
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

try {
    // Configure Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $options->set('isRemoteEnabled', false);
    $options->set('defaultFont', 'Helvetica');
    $options->set('defaultPaperSize', 'A4');
    $options->set('defaultPaperOrientation', 'portrait');
    
    // Create Dompdf instance
    $dompdf = new Dompdf($options);
    
    // Load HTML
    $dompdf->loadHtml($html);
    
    // Set paper size and orientation
    $dompdf->setPaper('A4', 'portrait');
    
    // Render PDF
    $dompdf->render();
    
    // Generate filename
    $filename = 'admin_report_' . $report['report_code'] . '_' . date('Y-m-d_H-i-s') . '.pdf';
    
    // Output PDF
    $dompdf->stream($filename, [
        'Attachment' => true,
        'Content-Type' => 'application/pdf'
    ]);
    
} catch (Exception $e) {
    // Fallback to simple HTML output if Dompdf fails
    error_log("PDF generation failed: " . $e->getMessage());
    
    // Set headers for HTML download
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="admin_report_' . $report['report_code'] . '.html"');
    
    // Output HTML directly
    echo $html;
}
?>


