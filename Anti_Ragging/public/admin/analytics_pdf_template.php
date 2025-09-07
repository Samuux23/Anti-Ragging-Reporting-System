<?php
// Analytics PDF Template for Anti-Ragging Portal
// This file contains the HTML template used for generating analytics PDF reports

function getAnalyticsPDFTemplate($analyticsData) {
    // Validate input data
    if (!is_array($analyticsData)) {
        throw new Exception('Analytics data must be an array');
    }
    
    // Set default values for missing data
    $totalReports = $analyticsData['totalReports'] ?? 0;
    $totalUniversities = $analyticsData['totalUniversities'] ?? 0;
    $totalIncidentTypes = $analyticsData['totalIncidentTypes'] ?? 0;
    $avgReportsPerUni = $analyticsData['avgReportsPerUni'] ?? 0;
    $byStatus = $analyticsData['byStatus'] ?? [];
    $byUniversity = $analyticsData['byUniversity'] ?? [];
    $byIncidentType = $analyticsData['byIncidentType'] ?? [];
    $byMonth = $analyticsData['byMonth'] ?? [];
    
    $html = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Anti-Ragging Portal - Analytics Report</title>
        <style>
            body {
                font-family: "DejaVu Sans", Arial, sans-serif;
                font-size: 10pt;
                line-height: 1.4;
                color: #333;
                margin: 0;
                padding: 15mm;
            }
            
            .header {
                text-align: center;
                border-bottom: 3px solid #2563eb;
                padding-bottom: 20px;
                margin-bottom: 25px;
            }
            
            .logo {
                width: 70px;
                height: 70px;
                margin: 0 auto 15px;
                background: #2563eb;
                border-radius: 50%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 20pt;
                font-weight: bold;
            }
            
            .title {
                font-size: 20pt;
                font-weight: bold;
                color: #1e40af;
                margin: 0 0 10px 0;
            }
            
            .subtitle {
                font-size: 12pt;
                color: #64748b;
                margin: 0;
            }
            
            .generated-date {
                font-size: 9pt;
                color: #64748b;
                margin-top: 10px;
            }
            
            .stats-container {
                margin-bottom: 25px;
            }
            
            .stats-row {
                display: table;
                width: 100%;
                margin-bottom: 15px;
            }
            
            .stat-card {
                display: table-cell;
                width: 25%;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 15px;
                text-align: center;
                margin-right: 10px;
            }
            
            .stat-card:last-child {
                margin-right: 0;
            }
            
            .stat-number {
                font-size: 18pt;
                font-weight: bold;
                color: #1e40af;
                margin-bottom: 5px;
                display: block;
            }
            
            .stat-label {
                font-size: 9pt;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .section {
                margin-bottom: 25px;
                break-inside: avoid;
            }
            
            .section-title {
                font-size: 14pt;
                font-weight: bold;
                color: #1e40af;
                border-bottom: 2px solid #3b82f6;
                padding-bottom: 8px;
                margin-bottom: 15px;
            }
            
            .chart-container {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .chart-title {
                font-size: 12pt;
                font-weight: bold;
                color: #1e293b;
                margin-bottom: 15px;
                text-align: center;
            }
            
            .data-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 15px;
            }
            
            .data-table th,
            .data-table td {
                border: 1px solid #e2e8f0;
                padding: 8px 12px;
                text-align: left;
                font-size: 9pt;
            }
            
            .data-table th {
                background: #f1f5f9;
                font-weight: bold;
                color: #475569;
            }
            
            .data-table tr:nth-child(even) {
                background: #f8fafc;
            }
            
            .status-container {
                margin-bottom: 20px;
            }
            
            .status-item {
                display: inline-block;
                width: 22%;
                background: #f0f9ff;
                border: 1px solid #bae6fd;
                border-radius: 8px;
                padding: 15px;
                text-align: center;
                margin-right: 2%;
                margin-bottom: 10px;
                vertical-align: top;
            }
            
            .status-item:nth-child(4n) {
                margin-right: 0;
            }
            
            .status-count {
                font-size: 16pt;
                font-weight: bold;
                color: #0369a1;
                margin-bottom: 5px;
                display: block;
            }
            
            .status-name {
                font-size: 9pt;
                color: #0c4a6e;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .trend-chart {
                background: #f0fdf4;
                border: 1px solid #bbf7d0;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .trend-item {
                display: block;
                padding: 10px;
                background: white;
                border-radius: 4px;
                margin-bottom: 8px;
                border-left: 4px solid #22c55e;
                overflow: hidden;
            }
            
            .trend-month {
                font-weight: bold;
                color: #16a34a;
                float: left;
                width: 100px;
            }
            
            .trend-count {
                font-weight: bold;
                color: #1f2937;
                float: right;
            }
            
            .clearfix::after {
                content: "";
                display: table;
                clear: both;
            }
            
            .footer {
                margin-top: 30px;
                padding-top: 15px;
                border-top: 1px solid #e2e8f0;
                text-align: center;
                font-size: 8pt;
                color: #64748b;
            }
            
            .page-break {
                page-break-before: always;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="logo">AR</div>
            <h1 class="title">Anti-Ragging Portal Analytics</h1>
            <p class="subtitle">Comprehensive System Statistics &amp; Insights</p>
            <p class="generated-date">Generated on: ' . date('F j, Y \a\t g:i A') . '</p>
        </div>
        
        <div class="stats-container">
            <div class="stats-row">
                <div class="stat-card">
                    <span class="stat-number">' . number_format($totalReports) . '</span>
                    <div class="stat-label">Total Reports</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number">' . number_format($totalUniversities) . '</span>
                    <div class="stat-label">Universities</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number">' . number_format($totalIncidentTypes) . '</span>
                    <div class="stat-label">Incident Types</div>
                </div>
                <div class="stat-card">
                    <span class="stat-number">' . number_format($avgReportsPerUni, 1) . '</span>
                    <div class="stat-label">Avg Reports/Uni</div>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2 class="section-title">Report Status Distribution</h2>
            <div class="status-container">';
    
    // Add status counts with proper handling
    if (!empty($byStatus)) {
        foreach ($byStatus as $statusData) {
            $status = isset($statusData['status']) ? $statusData['status'] : 'Unknown';
            $count = isset($statusData['count']) ? $statusData['count'] : 0;
            
            $html .= '
                <div class="status-item">
                    <span class="status-count">' . number_format($count) . '</span>
                    <div class="status-name">' . htmlspecialchars(ucfirst($status)) . '</div>
                </div>';
        }
    } else {
        $html .= '<p>No status data available.</p>';
    }
    
    $html .= '
            </div>
        </div>
        
        <div class="section">
            <h2 class="section-title">Top Universities by Report Count</h2>
            <div class="chart-container">
                <div class="chart-title">University Report Distribution</div>';
    
    if (!empty($byUniversity)) {
        $html .= '
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>University</th>
                            <th>Reports</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        foreach ($byUniversity as $index => $university) {
            $rank = $index + 1;
            $uniName = isset($university['uni_name']) ? $university['uni_name'] : 'Unknown University';
            $count = isset($university['count']) ? $university['count'] : 0;
            $percentage = $totalReports > 0 ? round(($count / $totalReports) * 100, 1) : 0;
            
            $html .= '
                        <tr>
                            <td>' . $rank . '</td>
                            <td>' . htmlspecialchars($uniName) . '</td>
                            <td>' . number_format($count) . '</td>
                            <td>' . $percentage . '%</td>
                        </tr>';
        }
        
        $html .= '
                    </tbody>
                </table>';
    } else {
        $html .= '<p>No university data available.</p>';
    }
    
    $html .= '
            </div>
        </div>
        
        <div class="section">
            <h2 class="section-title">Incident Type Analysis</h2>
            <div class="chart-container">
                <div class="chart-title">Reports by Incident Type</div>';
    
    if (!empty($byIncidentType)) {
        $html .= '
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Incident Type</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        foreach ($byIncidentType as $incidentData) {
            $incidentType = isset($incidentData['incident_type']) ? $incidentData['incident_type'] : 'Unknown';
            $count = isset($incidentData['count']) ? $incidentData['count'] : 0;
            $percentage = $totalReports > 0 ? round(($count / $totalReports) * 100, 1) : 0;
            
            $html .= '
                        <tr>
                            <td>' . htmlspecialchars(ucwords(str_replace('_', ' ', $incidentType))) . '</td>
                            <td>' . number_format($count) . '</td>
                            <td>' . $percentage . '%</td>
                        </tr>';
        }
        
        $html .= '
                    </tbody>
                </table>';
    } else {
        $html .= '<p>No incident type data available.</p>';
    }
    
    $html .= '
            </div>
        </div>
        
        <div class="section">
            <h2 class="section-title">Monthly Trend Analysis</h2>
            <div class="trend-chart">
                <div class="chart-title">Report Submission Trends</div>';
    
    if (!empty($byMonth)) {
        foreach ($byMonth as $monthData) {
            $month = isset($monthData['month']) ? $monthData['month'] : 'Unknown';
            $count = isset($monthData['count']) ? $monthData['count'] : 0;
            
            $html .= '
                <div class="trend-item clearfix">
                    <span class="trend-month">' . htmlspecialchars($month) . '</span>
                    <span class="trend-count">' . number_format($count) . ' reports</span>
                </div>';
        }
    } else {
        $html .= '<p>No monthly trend data available.</p>';
    }
    
    $html .= '
            </div>
        </div>
        
        <div class="footer">
            <p><strong>Report Summary:</strong> This analytics report provides comprehensive insights into the Anti-Ragging Portal system performance</p>
            <p>Generated by the Anti-Ragging Portal System - For administrative use only</p>
        </div>
    </body>
    </html>';
    
    return $html;
}
?>