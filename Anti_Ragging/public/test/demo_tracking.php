<?php
require_once __DIR__ . '/../config/db.php';

echo "<h2>Enhanced Tracking System Demo</h2>";

try {
    // First, let's set up the tracking tables
    echo "<h3>1. Setting up tracking tables...</h3>";
    
    // Create process_timeline table
    $sql = "
    CREATE TABLE IF NOT EXISTS process_timeline (
        id INT AUTO_INCREMENT PRIMARY KEY,
        report_id INT NOT NULL,
        step_name VARCHAR(100) NOT NULL,
        step_description TEXT,
        status ENUM('pending', 'in_progress', 'completed', 'failed') NOT NULL DEFAULT 'pending',
        assigned_to VARCHAR(255),
        started_at TIMESTAMP NULL,
        completed_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        notes TEXT,
        INDEX idx_report_id (report_id),
        INDEX idx_status (status),
        FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Process timeline table created</p>";
    
    // Create report_views table
    $sql = "
    CREATE TABLE IF NOT EXISTS report_views (
        id INT AUTO_INCREMENT PRIMARY KEY,
        report_id INT NOT NULL,
        viewer_type ENUM('admin', 'university_authority', 'system', 'investigator') NOT NULL,
        viewer_name VARCHAR(255),
        viewer_email VARCHAR(255),
        viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        action_taken VARCHAR(255),
        notes TEXT,
        INDEX idx_report_id (report_id),
        INDEX idx_viewer_type (viewer_type),
        FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Report views table created</p>";
    
    // Check if we have any reports to work with
    $stmt = $pdo->query("SELECT COUNT(*) FROM reports");
    $report_count = $stmt->fetchColumn();
    
    if ($report_count == 0) {
        echo "<p style='color: orange;'>⚠ No reports found. Please submit a report first to see the tracking system in action.</p>";
        echo "<p><a href='submit.php'>Go to Report Submission</a></p>";
        exit;
    }
    
    // Get a sample report
    $stmt = $pdo->query("SELECT * FROM reports LIMIT 1");
    $report = $stmt->fetch();
    
    echo "<h3>2. Working with Report: {$report['report_code']}</h3>";
    
    // Check if process timeline exists for this report
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM process_timeline WHERE report_id = ?");
    $stmt->execute([$report['id']]);
    $timeline_count = $stmt->fetchColumn();
    
    if ($timeline_count == 0) {
        echo "<p>Creating process timeline for this report...</p>";
        
        // Create process timeline
        $process_steps = [
            ['Report Submission', 'Anonymous report submitted by user', 'completed', 'System'],
            ['Initial Review', 'Report reviewed by admin team', 'in_progress', 'Admin Team'],
            ['University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities'],
            ['Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team'],
            ['Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities'],
            ['Resolution', 'Case resolved and closed', 'pending', 'System']
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO process_timeline (report_id, step_name, step_description, status, assigned_to, started_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        foreach ($process_steps as $step) {
            $stmt->execute([$report['id'], $step[0], $step[1], $step[2], $step[3]]);
        }
        
        // Mark first step as completed
        $stmt = $pdo->prepare("
            UPDATE process_timeline 
            SET status = 'completed', completed_at = NOW() 
            WHERE report_id = ? AND step_name = 'Report Submission'
        ");
        $stmt->execute([$report['id']]);
        
        echo "<p style='color: green;'>✓ Process timeline created</p>";
    }
    
    // Add some sample views
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM report_views WHERE report_id = ?");
    $stmt->execute([$report['id']]);
    $views_count = $stmt->fetchColumn();
    
    if ($views_count == 0) {
        echo "<p>Adding sample report views...</p>";
        
        $sample_views = [
            ['admin', 'System Administrator', 'admin@raggingreport.com', 'Initial Review', 'Report reviewed and validated'],
            ['university_authority', 'Dr. John Smith', 'dean@university.edu', 'University Notification', 'Report received and acknowledged'],
            ['investigator', 'Mr. Michael Brown', 'security@university.edu', 'Investigation Started', 'Investigation team assigned']
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO report_views (report_id, viewer_type, viewer_name, viewer_email, action_taken, notes) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($sample_views as $view) {
            $stmt->execute([$report['id'], $view[0], $view[1], $view[2], $view[3], $view[4]]);
        }
        
        echo "<p style='color: green;'>✓ Sample views added</p>";
    }
    
    // Display the tracking information
    echo "<h3>3. Tracking Information for Report {$report['report_code']}</h3>";
    
    // Get process timeline
    $stmt = $pdo->prepare("SELECT * FROM process_timeline WHERE report_id = ? ORDER BY created_at ASC");
    $stmt->execute([$report['id']]);
    $timeline = $stmt->fetchAll();
    
    echo "<h4>Process Timeline:</h4>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
    echo "<tr><th>Step</th><th>Status</th><th>Assigned To</th><th>Started</th><th>Completed</th></tr>";
    
    foreach ($timeline as $step) {
        $status_color = '';
        switch($step['status']) {
            case 'completed': $status_color = 'green'; break;
            case 'in_progress': $status_color = 'orange'; break;
            default: $status_color = 'gray'; break;
        }
        
        echo "<tr>";
        echo "<td>{$step['step_name']}</td>";
        echo "<td style='color: {$status_color};'>{$step['status']}</td>";
        echo "<td>{$step['assigned_to']}</td>";
        echo "<td>" . ($step['started_at'] ? $step['started_at'] : 'Not started') . "</td>";
        echo "<td>" . ($step['completed_at'] ? $step['completed_at'] : 'Not completed') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Get report views
    $stmt = $pdo->prepare("SELECT * FROM report_views WHERE report_id = ? ORDER BY viewed_at DESC");
    $stmt->execute([$report['id']]);
    $views = $stmt->fetchAll();
    
    echo "<h4>Report Views:</h4>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
    echo "<tr><th>Viewer</th><th>Type</th><th>Action</th><th>Viewed At</th></tr>";
    
    foreach ($views as $view) {
        echo "<tr>";
        echo "<td>{$view['viewer_name']}</td>";
        echo "<td>{$view['viewer_type']}</td>";
        echo "<td>{$view['action_taken']}</td>";
        echo "<td>{$view['viewed_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Get status history
    $stmt = $pdo->prepare("SELECT * FROM status_history WHERE report_id = ? ORDER BY changed_at ASC");
    $stmt->execute([$report['id']]);
    $status_history = $stmt->fetchAll();
    
    echo "<h4>Status History:</h4>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
    echo "<tr><th>Old Status</th><th>New Status</th><th>Changed By</th><th>Changed At</th></tr>";
    
    foreach ($status_history as $status) {
        echo "<tr>";
        echo "<td>" . ($status['old_status'] ?: 'None') . "</td>";
        echo "<td>{$status['new_status']}</td>";
        echo "<td>{$status['changed_by']}</td>";
        echo "<td>{$status['changed_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>4. How to Use the Tracking System</h3>";
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px;'>";
    echo "<h4>For Users:</h4>";
    echo "<ul>";
    echo "<li>Submit a report using the form</li>";
    echo "<li>Get a unique report code</li>";
    echo "<li>Track progress using the code</li>";
    echo "<li>See who has viewed your report</li>";
    echo "</ul>";
    
    echo "<h4>For Admins:</h4>";
    echo "<ul>";
    echo "<li>View all reports in the admin panel</li>";
    echo "<li>Update process timeline steps</li>";
    echo "<li>Record when you view reports</li>";
    echo "<li>Update report status</li>";
    echo "</ul>";
    
    echo "<h4>For University Authorities:</h4>";
    echo "<ul>";
    echo "<li>Access reports assigned to your university</li>";
    echo "<li>Update investigation progress</li>";
    echo "<li>Record actions taken</li>";
    echo "<li>Mark steps as completed</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<p><a href='track.php'>Try the tracking system</a> | <a href='submit.php'>Submit a new report</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
}
?> 