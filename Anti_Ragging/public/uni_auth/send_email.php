<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/email_helper.php';

if (empty($_SESSION['ua_user'])) {
    http_response_code(403);
    echo 'Unauthorized';
    exit;
}

$ua = $_SESSION['ua_user'];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request');
    }

    $report_id = (int)($_POST['report_id'] ?? 0);
    $recipients = $_POST['recipients'] ?? [];
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $extra = trim($_POST['extra_emails'] ?? '');
    $attachPdf = isset($_POST['attach_pdf']) && $_POST['attach_pdf'] == '1';

    if (!$report_id || !$subject || !$message) {
        throw new Exception('Subject and message are required');
    }

    // Validate report access (if university_id is set, ensure report belongs to university)
    $params = [$report_id];
    $where = 'id = ?';
    
    if (!empty($ua['university_id']) && (int)$ua['university_id'] !== 27) {
        $where .= ' AND university_id = ?';
        $params[] = (int)$ua['university_id'];
    }
    
    $stmt = $pdo->prepare("SELECT report_code, university_id FROM reports WHERE $where LIMIT 1");
    $stmt->execute($params);
    $r = $stmt->fetch();
    
    if (!$r) {
        throw new Exception('Report not found or access denied');
    }

    $validRecipients = [];
    
    // Validate recipients from directory (any active authority across universities)
    if ($recipients) {
        $place = implode(',', array_fill(0, count($recipients), '?'));
        $stmt = $pdo->prepare("SELECT email FROM university_authorities WHERE is_active = 1 AND email IN ($place)");
        $stmt->execute($recipients);
        $validRecipients = array_column($stmt->fetchAll(), 'email');
    }

    // Parse and validate extra emails (allow external)
    if ($extra !== '') {
        $parts = array_filter(array_map('trim', explode(',', $extra)));
        foreach ($parts as $em) {
            if (filter_var($em, FILTER_VALIDATE_EMAIL)) {
                $validRecipients[] = $em;
            }
        }
    }

    // Deduplicate and ensure not empty
    $validRecipients = array_values(array_unique($validRecipients));
    if (!$validRecipients) {
        throw new Exception('No valid recipients. Please provide at least one valid email address.');
    }

    // Prepare email content
    $htmlMessage = '<html><body>' . nl2br(htmlspecialchars($message)) . '<hr><small>Regarding report ' . htmlspecialchars($r['report_code']) . '</small></body></html>';
    
    // If PDF requested, generate PDF and send with attachment
    if ($attachPdf) {
        // Generate PDF using our template
        require_once __DIR__ . '/lib/pdf_template.php';
        
        // Get report data for PDF
        $stmtPdf = $pdo->prepare('SELECT r.*, u.uni_name FROM reports r LEFT JOIN university u ON r.university_id = u.uni_id WHERE r.id = ?');
        $stmtPdf->execute([$report_id]);
        $reportData = $stmtPdf->fetch();
        
        // Get attachments
        $stmtAtt = $pdo->prepare('SELECT * FROM attachments WHERE report_id = ? ORDER BY uploaded_at ASC');
        $stmtAtt->execute([$report_id]);
        $attachments = $stmtAtt->fetchAll();
        
        // Generate PDF HTML
        $pdfHtml = getPDFTemplate($reportData, $attachments);
        
        // Convert HTML to PDF using Dompdf
        require_once __DIR__ . '/../../vendor/autoload.php';
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($pdfHtml);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();
        
        $pdfFilename = 'report_' . $r['report_code'] . '.pdf';
        
        // Send emails with PDF attachment
        $okAll = true;
        foreach ($validRecipients as $to) {
            $ok = $emailHelper->sendEmailWithPDF($to, $subject, $htmlMessage, $pdfContent, $pdfFilename, $ua['name'], $ua['email']);
            if (!$ok) { $okAll = false; }
        }
    } else {
        // Send emails without PDF attachment
        $okAll = true;
        foreach ($validRecipients as $to) {
            $ok = $emailHelper->sendEmail($to, $subject, $htmlMessage, $ua['name'], $ua['email']);
            if (!$ok) { $okAll = false; }
        }
    }

    if ($okAll) {
        header('Location: dashboard.php?mail=1');
    } else {
        $err = method_exists($emailHelper, 'getLastError') ? $emailHelper->getLastError() : '';
        $msg = urlencode($err ?: 'Some emails failed to send. Please try again.');
        header('Location: dashboard.php?mail=0&err=' . $msg);
    }
    exit;
} catch (Exception $e) {
    $msg = urlencode($e->getMessage());
    header('Location: dashboard.php?mail=0&err=' . $msg);
    exit;
}


