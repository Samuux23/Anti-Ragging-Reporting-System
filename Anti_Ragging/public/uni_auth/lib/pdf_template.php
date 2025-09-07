<?php
// PDF Template for Anti-Ragging Reports
// This file contains the HTML template used for generating PDF reports

function getPDFTemplate($reportData, $attachments = []) {
    $html = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Anti-Ragging Report - ' . htmlspecialchars($reportData['report_code']) . '</title>
        <style>
            @page {
                size: A4;
                margin: 20mm;
                @top-center {
                    content: "Anti-Ragging Portal - Official Report";
                    font-size: 10pt;
                    color: #666;
                }
                @bottom-center {
                    content: "Page " counter(page) " of " counter(pages);
                    font-size: 10pt;
                    color: #666;
                }
            }
            
            body {
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                font-size: 11pt;
                line-height: 1.4;
                color: #333;
                margin: 0;
                padding: 0;
            }
            
            .header {
                text-align: center;
                border-bottom: 3px solid #2563eb;
                padding-bottom: 20px;
                margin-bottom: 30px;
            }
            
            .logo {
                width: 80px;
                height: 80px;
                margin: 0 auto 15px;
                background: #2563eb;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 24pt;
                font-weight: bold;
            }
            
            .title {
                font-size: 24pt;
                font-weight: bold;
                color: #1e40af;
                margin: 0 0 10px 0;
            }
            
            .subtitle {
                font-size: 14pt;
                color: #64748b;
                margin: 0;
            }
            
            .report-info {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 25px;
            }
            
            .info-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }
            
            .info-item {
                margin-bottom: 15px;
            }
            
            .info-label {
                font-weight: bold;
                color: #475569;
                font-size: 10pt;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 5px;
            }
            
            .info-value {
                font-size: 11pt;
                color: #1e293b;
                padding: 8px 12px;
                background: white;
                border: 1px solid #e2e8f0;
                border-radius: 4px;
                min-height: 20px;
            }
            
            .section {
                margin-bottom: 25px;
                page-break-inside: avoid;
            }
            
            .section-title {
                font-size: 16pt;
                font-weight: bold;
                color: #1e40af;
                border-bottom: 2px solid #3b82f6;
                padding-bottom: 8px;
                margin-bottom: 15px;
            }
            
            .incident-details {
                background: #fef2f2;
                border: 1px solid #fecaca;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .details-text {
                font-size: 11pt;
                line-height: 1.6;
                color: #1f2937;
                white-space: pre-wrap;
            }
            
            .attachments-section {
                background: #f0f9ff;
                border: 1px solid #bae6fd;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .attachment-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px;
                background: white;
                border: 1px solid #e2e8f0;
                border-radius: 4px;
                margin-bottom: 8px;
            }
            
            .attachment-name {
                font-weight: 500;
                color: #1e293b;
            }
            
            .attachment-type {
                font-size: 9pt;
                color: #64748b;
                text-transform: uppercase;
                background: #f1f5f9;
                padding: 2px 6px;
                border-radius: 3px;
            }
            
            .status-timeline {
                background: #f0fdf4;
                border: 1px solid #bbf7d0;
                border-radius: 8px;
                padding: 20px;
            }
            
            .timeline-item {
                display: flex;
                align-items: center;
                margin-bottom: 12px;
                padding: 10px;
                background: white;
                border-radius: 4px;
                border-left: 4px solid #22c55e;
            }
            
            .timeline-status {
                font-weight: bold;
                color: #16a34a;
                margin-right: 15px;
                min-width: 100px;
            }
            
            .timeline-date {
                font-size: 9pt;
                color: #64748b;
                margin-right: 15px;
            }
            
            .timeline-description {
                flex: 1;
                color: #1f2937;
            }
            
            .footer {
                margin-top: 40px;
                padding-top: 20px;
                border-top: 1px solid #e2e8f0;
                text-align: center;
                font-size: 9pt;
                color: #64748b;
            }
            
            .watermark {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate(-45deg);
                font-size: 48pt;
                color: rgba(59, 130, 246, 0.1);
                z-index: -1;
                pointer-events: none;
            }
            
            .page-break {
                page-break-before: always;
            }
            
            @media print {
                .page-break {
                    page-break-before: always;
                }
            }
        </style>
    </head>
    <body>
        <div class="watermark">ANTI-RAGGING PORTAL</div>
        
        <div class="header">
            <div class="logo">AR</div>
            <h1 class="title">Anti-Ragging Incident Report</h1>
            <p class="subtitle">Official Investigation Document</p>
        </div>
        
        <div class="report-info">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Report Code</div>
                    <div class="info-value">' . htmlspecialchars($reportData['report_code']) . '</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">' . htmlspecialchars(ucfirst($reportData['status'])) . '</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Incident Type</div>
                    <div class="info-value">' . htmlspecialchars(ucwords(str_replace('_', ' ', $reportData['incident_type']))) . '</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Department</div>
                    <div class="info-value">' . htmlspecialchars(ucfirst($reportData['department'])) . '</div>
                </div>
                <div class="info-item">
                    <div class="info-label">University</div>
                    <div class="info-value">' . htmlspecialchars($reportData['university_name'] ?? 'All Universities') . '</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Location</div>
                    <div class="info-value">' . htmlspecialchars($reportData['location'] ?? 'Not specified') . '</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Incident Date & Time</div>
                    <div class="info-value">' . htmlspecialchars(date('F j, Y \a\t g:i A', strtotime($reportData['incident_datetime']))) . '</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Report Submitted</div>
                    <div class="info-value">' . htmlspecialchars(date('F j, Y \a\t g:i A', strtotime($reportData['created_at']))) . '</div>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2 class="section-title">Incident Description</h2>
            <div class="incident-details">
                <div class="details-text">' . htmlspecialchars($reportData['details']) . '</div>
            </div>
        </div>';
    
    // Add attachments section if there are any
    if (!empty($attachments)) {
        $html .= '
        <div class="section">
            <h2 class="section-title">Supporting Evidence</h2>
            <div class="attachments-section">
                <p><strong>Total Attachments:</strong> ' . count($attachments) . ' files</p>';
        
        foreach ($attachments as $attachment) {
            $fileType = getFileTypeFromMIME($attachment['mime_type']);
            $fileSize = formatFileSize($attachment['size_bytes']);
            
            $html .= '
                <div class="attachment-item">
                    <div class="attachment-name">' . htmlspecialchars($attachment['original_name']) . '</div>
                    <div class="attachment-type">' . htmlspecialchars($fileType) . ' • ' . htmlspecialchars($fileSize) . '</div>
                </div>';
        }
        
        $html .= '
            </div>
        </div>';
    }
    
    // Add status timeline if available
    if (isset($reportData['status_history']) && !empty($reportData['status_history'])) {
        $html .= '
        <div class="section">
            <h2 class="section-title">Status Timeline</h2>
            <div class="status-timeline">';
        
        foreach ($reportData['status_history'] as $status) {
            $html .= '
                <div class="timeline-item">
                    <div class="timeline-status">' . htmlspecialchars(ucfirst($status['new_status'])) . '</div>
                    <div class="timeline-date">' . htmlspecialchars(date('M j, Y g:i A', strtotime($status['changed_at']))) . '</div>
                    <div class="timeline-description">Status changed from "' . htmlspecialchars($status['old_status'] ?: 'None') . '" to "' . htmlspecialchars($status['new_status']) . '"</div>
                </div>';
        }
        
        $html .= '
            </div>
        </div>';
    }
    
    $html .= '
        <div class="footer">
            <p><strong>Generated on:</strong> ' . date('F j, Y \a\t g:i A') . '</p>
            <p>This document is generated by the Anti-Ragging Portal System</p>
            <p>For official use only - Confidential Document</p>
        </div>
    </body>
    </html>';
    
    return $html;
}

// Helper function to determine file type from MIME type
function getFileTypeFromMIME($mimeType) {
    if (strpos($mimeType, 'image/') === 0) return 'Image';
    if (strpos($mimeType, 'application/pdf') === 0) return 'PDF';
    if (strpos($mimeType, 'application/msword') === 0 || strpos($mimeType, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') === 0) return 'Document';
    if (strpos($mimeType, 'text/') === 0) return 'Text';
    if (strpos($mimeType, 'audio/') === 0) return 'Audio';
    if (strpos($mimeType, 'video/') === 0) return 'Video';
    return 'File';
}

// Helper function to format file size
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>
