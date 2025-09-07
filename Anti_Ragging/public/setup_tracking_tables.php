<?php
require_once __DIR__ . '/../config/db.php';

echo "<h2>Setting up Enhanced Tracking Tables</h2>";

try {
    // Create report_views table to track who has viewed the report
    $sql = "
    CREATE TABLE IF NOT EXISTS report_views (
        id INT AUTO_INCREMENT PRIMARY KEY,
        report_id INT NOT NULL,
        viewer_type ENUM('admin', 'university_authority', 'system') NOT NULL,
        viewer_name VARCHAR(255),
        viewer_email VARCHAR(255),
        viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        action_taken VARCHAR(255),
        notes TEXT,
        INDEX idx_report_id (report_id),
        INDEX idx_viewer_type (viewer_type),
        INDEX idx_viewed_at (viewed_at),
        FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Report views table created</p>";
    
    // Create process_timeline table for detailed process tracking
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
        INDEX idx_report_id (report_id),
        INDEX idx_status (status),
        INDEX idx_step_name (step_name),
        FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Process timeline table created</p>";
    
    // Create university_authorities table if it doesn't exist
    $sql = "
    CREATE TABLE IF NOT EXISTS university_authorities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        university_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        position VARCHAR(255) NOT NULL,
        department VARCHAR(255),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_university_id (university_id),
        INDEX idx_email (email),
        FOREIGN KEY (university_id) REFERENCES university(uni_id)
    )";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ University authorities table created</p>";
    
    // Insert sample university authorities for testing
    $stmt = $pdo->query("SELECT COUNT(*) FROM university_authorities");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Get first university
        $stmt = $pdo->query("SELECT uni_id FROM university LIMIT 1");
        $uni = $stmt->fetch();
        
        if ($uni) {
            $sql = "
            INSERT INTO university_authorities (university_id, name, email, position, department) VALUES
            ({$uni['uni_id']}, 'Dr. John Smith', 'dean@university.edu', 'Dean of Students', 'Student Affairs'),
            ({$uni['uni_id']}, 'Prof. Sarah Johnson', 'sarah.johnson@university.edu', 'Head of Department', 'Computer Science'),
            ({$uni['uni_id']}, 'Mr. Michael Brown', 'security@university.edu', 'Campus Security Head', 'Security')
            ";
            
            $pdo->exec($sql);
            echo "<p style='color: green;'>✓ Sample university authorities inserted</p>";
        }
    }
    
    // Insert default process steps for reports
    $stmt = $pdo->query("SELECT COUNT(*) FROM process_timeline");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Get first report for testing
        $stmt = $pdo->query("SELECT id FROM reports LIMIT 1");
        $report = $stmt->fetch();
        
        if ($report) {
            $sql = "
            INSERT INTO process_timeline (report_id, step_name, step_description, status, assigned_to) VALUES
            ({$report['id']}, 'Report Submission', 'Anonymous report submitted by user', 'completed', 'System'),
            ({$report['id']}, 'Initial Review', 'Report reviewed by admin team', 'in_progress', 'Admin Team'),
            ({$report['id']}, 'University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities'),
            ({$report['id']}, 'Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team'),
            ({$report['id']}, 'Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities'),
            ({$report['id']}, 'Resolution', 'Case resolved and closed', 'pending', 'System')
            ";
            
            $pdo->exec($sql);
            echo "<p style='color: green;'>✓ Sample process timeline inserted</p>";
        }
    }
    
    echo "<p style='color: green; font-weight: bold;'>Enhanced tracking tables setup completed!</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
}
?> 