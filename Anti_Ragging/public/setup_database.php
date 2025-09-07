<?php
require_once __DIR__ . '/../config/db.php';

echo "<h2>Database Setup</h2>";

try {
    // Create reports table if it doesn't exist
    $sql = "
    CREATE TABLE IF NOT EXISTS reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        report_code VARCHAR(12) NOT NULL UNIQUE,
        incident_type VARCHAR(100) NOT NULL,
        department VARCHAR(100) NOT NULL,
        incident_datetime DATETIME NOT NULL,
        details TEXT NOT NULL,
        status ENUM('Submitted', 'Under Review', 'Action Initiated', 'Resolved', 'Rejected') NOT NULL DEFAULT 'Submitted',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        university_id INT,
        location VARCHAR(255),
        reporter_email VARCHAR(255) NOT NULL,
        INDEX idx_report_code (report_code),
        INDEX idx_status (status),
        INDEX idx_university (university_id),
        INDEX idx_created_at (created_at)
    )";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Reports table created/verified</p>";
    
    // Create university table if it doesn't exist
    $sql = "
    CREATE TABLE IF NOT EXISTS university (
        uni_id INT AUTO_INCREMENT PRIMARY KEY,
        uni_name VARCHAR(255) NOT NULL,
        location VARCHAR(255) DEFAULT NULL,
        uni_type ENUM('government','private') NOT NULL,
        uni_email_domain VARCHAR(100) DEFAULT NULL,
        INDEX idx_type (uni_type),
        INDEX idx_name (uni_name)
    )";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ University table created/verified</p>";
    
    // Create status_history table if it doesn't exist
    $sql = "
    CREATE TABLE IF NOT EXISTS status_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        report_id INT NOT NULL,
        old_status VARCHAR(50) DEFAULT NULL,
        new_status VARCHAR(50) NOT NULL,
        changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_report_id (report_id),
        INDEX idx_changed_at (changed_at)
    )";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Status history table created/verified</p>";
    
    // Create attachments table if it doesn't exist
    $sql = "
    CREATE TABLE IF NOT EXISTS attachments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        report_id INT NOT NULL,
        original_name VARCHAR(255) NOT NULL,
        stored_name VARCHAR(255) NOT NULL,
        mime_type VARCHAR(100) NOT NULL,
        size_bytes INT NOT NULL,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_report_id (report_id),
        INDEX idx_uploaded_at (uploaded_at)
    )";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Attachments table created/verified</p>";
    
    // Insert sample university data if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM university");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $sql = "
        INSERT INTO university (uni_name, uni_type, uni_email_domain, location) VALUES
        ('University of Colombo', 'government', 'cmb.ac.lk', 'Colombo'),
        ('University of Peradeniya', 'government', 'pdn.ac.lk', 'Peradeniya'),
        ('University of Kelaniya', 'government', 'kln.ac.lk', 'Kelaniya'),
        ('University of Moratuwa', 'government', 'uom.lk', 'Moratuwa'),
        ('University of Jaffna', 'government', 'jfn.ac.lk', 'Jaffna'),
        ('University of Ruhuna', 'government', 'pdn.ac.lk', 'Matara'),
        ('University of Sri Jayewardenepura', 'government', 'sjp.ac.lk', 'Nugegoda'),
        ('University of Sabaragamuwa', 'government', 'sab.ac.lk', 'Belihuloya'),
        ('University of Wayamba', 'government', 'wyb.ac.lk', 'Kuliyapitiya'),
        ('University of Uva Wellassa', 'government', 'uwu.ac.lk', 'Badulla')
        ";
        
        $pdo->exec($sql);
        echo "<p style='color: green;'>✓ Sample university data inserted</p>";
    } else {
        echo "<p style='color: blue;'>ℹ University table already has {$count} records</p>";
    }
    
    echo "<p style='color: green; font-weight: bold;'>Database setup completed successfully!</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database setup error: " . $e->getMessage() . "</p>";
}
?> 