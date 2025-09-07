<?php
require_once __DIR__ . '/../config/db.php';

echo "<h2>Simple Database Test</h2>";

try {
    // Test basic connection
    $stmt = $pdo->query("SELECT 1");
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Test university table
    $stmt = $pdo->query("SELECT COUNT(*) FROM university");
    $count = $stmt->fetchColumn();
    echo "<p>Universities in database: {$count}</p>";
    
    if ($count > 0) {
        // Get first university
        $stmt = $pdo->query("SELECT uni_id, uni_name FROM university LIMIT 1");
        $uni = $stmt->fetch();
        echo "<p>First university: ID {$uni['uni_id']} - {$uni['uni_name']}</p>";
        
        // Test report insertion
        $report_code = 'AR' . str_pad(random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            INSERT INTO reports (
                report_code, incident_type, department, incident_datetime, 
                details, status, university_id, location, reporter_email, created_at
            ) VALUES (?, ?, ?, ?, ?, 'Submitted', ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $report_code,
            'test',
            'test',
            '2024-01-01 12:00:00',
            'Test report for debugging',
            $uni['uni_id'],
            'Test location',
            'test@test.com'
        ]);
        
        $report_id = $pdo->lastInsertId();
        
        // Insert status history
        $stmt = $pdo->prepare("
            INSERT INTO status_history (report_id, old_status, new_status, changed_at) 
            VALUES (?, '', 'Submitted', NOW())
        ");
        $stmt->execute([$report_id]);
        
        $pdo->commit();
        
        echo "<p style='color: green;'>✓ Test report inserted successfully! ID: {$report_id}, Code: {$report_code}</p>";
        
        // Clean up - delete the test report
        $stmt = $pdo->prepare("DELETE FROM reports WHERE id = ?");
        $stmt->execute([$report_id]);
        echo "<p style='color: blue;'>✓ Test report cleaned up</p>";
        
    } else {
        echo "<p style='color: red;'>✗ No universities found in database</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
}
?> 