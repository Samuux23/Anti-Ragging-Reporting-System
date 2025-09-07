<?php
require_once __DIR__ . '/../config/db.php';

echo "<h2>Database Connection Test</h2>";

try {
    // Test database connection
    $stmt = $pdo->query("SELECT 1");
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Check if tables exist
    $tables = ['reports', 'university', 'status_history', 'attachments'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("DESCRIBE $table");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<p style='color: green;'>✓ Table '$table' exists with " . count($columns) . " columns</p>";
            
            echo "<ul>";
            foreach ($columns as $column) {
                echo "<li>{$column['Field']} - {$column['Type']} " . ($column['Null'] == 'NO' ? '(NOT NULL)' : '(NULL)') . "</li>";
            }
            echo "</ul>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Table '$table' does not exist or error: " . $e->getMessage() . "</p>";
        }
    }
    
    // Check university data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM university");
    $result = $stmt->fetch();
    echo "<p>Universities in database: " . $result['count'] . "</p>";
    
    if ($result['count'] > 0) {
        $stmt = $pdo->query("SELECT uni_id, uni_name, uni_type, uni_email_domain FROM university LIMIT 5");
        $universities = $stmt->fetchAll();
        echo "<h3>Sample Universities:</h3>";
        echo "<ul>";
        foreach ($universities as $uni) {
            echo "<li>ID: {$uni['uni_id']} - {$uni['uni_name']} ({$uni['uni_type']}) - Domain: {$uni['uni_email_domain']}</li>";
        }
        echo "</ul>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
}
?> 