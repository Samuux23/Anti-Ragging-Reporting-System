<?php
require_once __DIR__ . '/../config/db.php';

echo "<h2>Complete System Test</h2>";

try {
    // Test 1: Database Connection
    $stmt = $pdo->query("SELECT 1");
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Test 2: Check all required tables
    $tables = ['reports', 'university', 'status_history', 'attachments'];
    $allTablesExist = true;
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p style='color: green;'>✓ Table '$table' exists with $count records</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Table '$table' error: " . $e->getMessage() . "</p>";
            $allTablesExist = false;
        }
    }
    
    if (!$allTablesExist) {
        echo "<p style='color: red;'>✗ Some tables are missing or have issues</p>";
        exit;
    }
    
    // Test 3: Check university data
    $stmt = $pdo->query("SELECT COUNT(*) FROM university");
    $uniCount = $stmt->fetchColumn();
    
    if ($uniCount > 0) {
        echo "<p style='color: green;'>✓ University data available ($uniCount universities)</p>";
        
        // Get a sample university for testing
        $stmt = $pdo->query("SELECT uni_id, uni_name, uni_email_domain FROM university LIMIT 1");
        $uni = $stmt->fetch();
        echo "<p>Sample university: {$uni['uni_name']} (ID: {$uni['uni_id']}, Domain: {$uni['uni_email_domain']})</p>";
        
        // Test 4: Test report insertion
        $report_code = 'AR' . str_pad(random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        
        $pdo->beginTransaction();
        
        try {
            // Insert test report
            $stmt = $pdo->prepare("
                INSERT INTO reports (
                    report_code, incident_type, department, incident_datetime, 
                    details, status, university_id, location, reporter_email, created_at
                ) VALUES (?, ?, ?, ?, ?, 'Submitted', ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $report_code,
                'test_incident',
                'test_department',
                '2024-01-01 12:00:00',
                'Test report for system verification',
                $uni['uni_id'],
                'Test location',
                'test@' . ltrim($uni['uni_email_domain'], '@')
            ]);
            
            $report_id = $pdo->lastInsertId();
            
            // Insert status history
            $stmt = $pdo->prepare("
                INSERT INTO status_history (report_id, old_status, new_status, changed_at) 
                VALUES (?, '', 'Submitted', NOW())
            ");
            $stmt->execute([$report_id]);
            
            $pdo->commit();
            
            echo "<p style='color: green;'>✓ Test report inserted successfully! ID: $report_id, Code: $report_code</p>";
            
            // Test 5: Verify report retrieval
            $stmt = $pdo->prepare("SELECT r.*, u.uni_name FROM reports r JOIN university u ON r.university_id = u.uni_id WHERE r.id = ?");
            $stmt->execute([$report_id]);
            $report = $stmt->fetch();
            
            if ($report) {
                echo "<p style='color: green;'>✓ Report retrieval successful</p>";
                echo "<p>Retrieved report: {$report['report_code']} - {$report['incident_type']} at {$report['uni_name']}</p>";
            } else {
                echo "<p style='color: red;'>✗ Report retrieval failed</p>";
            }
            
            // Test 6: Check status history
            $stmt = $pdo->prepare("SELECT * FROM status_history WHERE report_id = ?");
            $stmt->execute([$report_id]);
            $history = $stmt->fetch();
            
            if ($history) {
                echo "<p style='color: green;'>✓ Status history created successfully</p>";
            } else {
                echo "<p style='color: red;'>✗ Status history creation failed</p>";
            }
            
            // Clean up test data
            $stmt = $pdo->prepare("DELETE FROM reports WHERE id = ?");
            $stmt->execute([$report_id]);
            echo "<p style='color: blue;'>✓ Test data cleaned up</p>";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "<p style='color: red;'>✗ Test report insertion failed: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ No universities found in database</p>";
    }
    
    // Test 7: Check upload directory
    $upload_dir = __DIR__ . '/../uploads/reports/';
    if (is_dir($upload_dir)) {
        if (is_writable($upload_dir)) {
            echo "<p style='color: green;'>✓ Upload directory exists and is writable</p>";
        } else {
            echo "<p style='color: orange;'>⚠ Upload directory exists but is not writable</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ Upload directory does not exist (will be created when needed)</p>";
    }
    
    // Test 8: Check email configuration
    if (function_exists('mail')) {
        echo "<p style='color: green;'>✓ PHP mail function is available</p>";
    } else {
        echo "<p style='color: orange;'>⚠ PHP mail function is not available</p>";
    }
    
    echo "<p style='color: green; font-weight: bold;'>Complete system test finished!</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ General error: " . $e->getMessage() . "</p>";
}
?> 