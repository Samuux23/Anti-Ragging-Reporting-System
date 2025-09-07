<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// Disable all output buffering and error display for clean PDF output
while (ob_get_level()) {
    ob_end_clean();
}
ini_set('display_errors', 0);

if (empty($_SESSION['admin_user'])) { 
    http_response_code(403); 
    die('Unauthorized'); 
}

// Get analytics data from POST request
$analyticsDataJson = $_POST['analytics_data'] ?? '';
if (empty($analyticsDataJson)) {
    http_response_code(400);
    die('No analytics data provided');
}

try {
    $analyticsData = json_decode($analyticsDataJson, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }
    
    // Validate and sanitize data
    $totalReports = (int)($analyticsData['totalReports'] ?? 0);
    $totalUniversities = (int)($analyticsData['totalUniversities'] ?? 0);
    $totalIncidentTypes = (int)($analyticsData['totalIncidentTypes'] ?? 0);
    $avgReportsPerUni = (float)($analyticsData['avgReportsPerUni'] ?? 0);
    $byStatus = $analyticsData['byStatus'] ?? [];
    $byUniversity = $analyticsData['byUniversity'] ?? [];
    $byIncidentType = $analyticsData['byIncidentType'] ?? [];
    $byMonth = $analyticsData['byMonth'] ?? [];
    
} catch (Exception $e) {
    error_log("Analytics data error: " . $e->getMessage());
    http_response_code(400);
    die('Invalid analytics data');
}

// Check if Dompdf is available
$vendorPath = __DIR__ . '/../../vendor/autoload.php';
if (!file_exists($vendorPath)) {
    // Fallback: Generate simple PDF-compatible HTML
    generateSimplePDF($totalReports, $totalUniversities, $totalIncidentTypes, $avgReportsPerUni, $byStatus, $byUniversity, $byIncidentType, $byMonth);
    exit;
}

require_once $vendorPath;

if (!class_exists('Dompdf\Dompdf')) {
    // Fallback: Generate simple PDF-compatible HTML
    generateSimplePDF($totalReports, $totalUniversities, $totalIncidentTypes, $avgReportsPerUni, $byStatus, $byUniversity, $byIncidentType, $byMonth);
    exit;
}

use Dompdf\Dompdf;
use Dompdf\Options;

try {
    // Generate clean HTML for PDF
    $html = generatePDFHTML($totalReports, $totalUniversities, $totalIncidentTypes, $avgReportsPerUni, $byStatus, $byUniversity, $byIncidentType, $byMonth);
    
    // Configure Dompdf with minimal settings
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', false);
    $options->set('isRemoteEnabled', false);
    $options->set('defaultFont', 'Arial');
    $options->set('isFontSubsettingEnabled', false);
    
    // Increase memory and time limits
    ini_set('memory_limit', '512M');
    set_time_limit(300);
    
    // Create Dompdf instance
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    // Get PDF output
    $output = $dompdf->output();
    
    // Verify PDF was generated
    if (empty($output)) {
        throw new Exception('PDF output is empty');
    }
    
    // Generate filename
    $filename = 'analytics_report_' . date('Y-m-d_H-i-s') . '.pdf';
    
    // Set headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($output));
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');
    
    // Output PDF
    echo $output;
    
} catch (Exception $e) {
    error_log("PDF generation error: " . $e->getMessage());
    // Generate simple PDF-compatible HTML as fallback
    generateSimplePDF($totalReports, $totalUniversities, $totalIncidentTypes, $avgReportsPerUni, $byStatus, $byUniversity, $byIncidentType, $byMonth);
}

function generatePDFHTML($totalReports, $totalUniversities, $totalIncidentTypes, $avgReportsPerUni, $byStatus, $byUniversity, $byIncidentType, $byMonth) {
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Analytics Report</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                font-size: 12px; 
                margin: 20px; 
                color: #333; 
            }
            .header { 
                text-align: center; 
                margin-bottom: 30px; 
                border-bottom: 2px solid #2563eb; 
                padding-bottom: 20px; 
            }
            .title { 
                font-size: 24px; 
                color: #1e40af; 
                margin: 0 0 10px 0; 
            }
            .date { 
                color: #666; 
                margin-top: 10px; 
            }
            .stats-table { 
                width: 100%; 
                margin-bottom: 30px; 
            }
            .stats-table td { 
                width: 25%; 
                text-align: center; 
                padding: 15px; 
                background: #f8fafc; 
                border: 1px solid #e2e8f0; 
            }
            .stat-number { 
                font-size: 20px; 
                font-weight: bold; 
                color: #1e40af; 
                display: block; 
            }
            .stat-label { 
                font-size: 10px; 
                color: #666; 
                text-transform: uppercase; 
            }
            .section { 
                margin-bottom: 30px; 
            }
            .section-title { 
                font-size: 16px; 
                color: #1e40af; 
                border-bottom: 1px solid #3b82f6; 
                padding-bottom: 5px; 
                margin-bottom: 15px; 
            }
            .data-table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-bottom: 20px; 
            }
            .data-table th, .data-table td { 
                border: 1px solid #e2e8f0; 
                padding: 8px; 
                text-align: left; 
                font-size: 10px; 
            }
            .data-table th { 
                background: #f1f5f9; 
                font-weight: bold; 
            }
            .footer { 
                margin-top: 40px; 
                text-align: center; 
                font-size: 10px; 
                color: #666; 
                border-top: 1px solid #e2e8f0; 
                padding-top: 15px; 
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1 class="title">Anti-Ragging Portal Analytics</h1>
            <p>Comprehensive System Statistics & Insights</p>
            <p class="date">Generated on: <?= date('F j, Y \a\t g:i A') ?></p>
        </div>

        <table class="stats-table">
            <tr>
                <td>
                    <span class="stat-number"><?= number_format($totalReports) ?></span>
                    <div class="stat-label">Total Reports</div>
                </td>
                <td>
                    <span class="stat-number"><?= number_format($totalUniversities) ?></span>
                    <div class="stat-label">Universities</div>
                </td>
                <td>
                    <span class="stat-number"><?= number_format($totalIncidentTypes) ?></span>
                    <div class="stat-label">Incident Types</div>
                </td>
                <td>
                    <span class="stat-number"><?= number_format($avgReportsPerUni, 1) ?></span>
                    <div class="stat-label">Avg Reports/Uni</div>
                </td>
            </tr>
        </table>

        <div class="section">
            <h2 class="section-title">Report Status Distribution</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($byStatus as $statusData): 
                        $status = $statusData['status'] ?? 'Unknown';
                        $count = $statusData['count'] ?? 0;
                        $percentage = $totalReports > 0 ? round(($count / $totalReports) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars(ucfirst($status)) ?></td>
                        <td><?= number_format($count) ?></td>
                        <td><?= $percentage ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2 class="section-title">Top Universities by Report Count</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>University</th>
                        <th>Reports</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($byUniversity as $index => $university): 
                        $rank = $index + 1;
                        $uniName = $university['uni_name'] ?? 'Unknown University';
                        $count = $university['count'] ?? 0;
                        $percentage = $totalReports > 0 ? round(($count / $totalReports) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td><?= $rank ?></td>
                        <td><?= htmlspecialchars($uniName) ?></td>
                        <td><?= number_format($count) ?></td>
                        <td><?= $percentage ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2 class="section-title">Incident Type Analysis</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Incident Type</th>
                        <th>Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($byIncidentType as $incidentData): 
                        $incidentType = $incidentData['incident_type'] ?? 'Unknown';
                        $count = $incidentData['count'] ?? 0;
                        $percentage = $totalReports > 0 ? round(($count / $totalReports) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', $incidentType))) ?></td>
                        <td><?= number_format($count) ?></td>
                        <td><?= $percentage ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2 class="section-title">Monthly Trend Analysis</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Reports</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($byMonth as $monthData): 
                        $month = $monthData['month'] ?? 'Unknown';
                        $count = $monthData['count'] ?? 0;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($month) ?></td>
                        <td><?= number_format($count) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p><strong>Report Summary:</strong> This analytics report provides comprehensive insights into the Anti-Ragging Portal system performance</p>
            <p>Generated by the Anti-Ragging Portal System - For administrative use only</p>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

function generateSimplePDF($totalReports, $totalUniversities, $totalIncidentTypes, $avgReportsPerUni, $byStatus, $byUniversity, $byIncidentType, $byMonth) {
    $filename = 'analytics_report_' . date('Y-m-d_H-i-s') . '.html';
    
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo generatePDFHTML($totalReports, $totalUniversities, $totalIncidentTypes, $avgReportsPerUni, $byStatus, $byUniversity, $byIncidentType, $byMonth);
}
?>