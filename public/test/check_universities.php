<?php
require_once __DIR__ . '/../config/db.php';

echo "<h2>University Data Check</h2>";

try {
    $stmt = $pdo->query("SELECT uni_id, uni_name, uni_type, uni_email_domain FROM university ORDER BY uni_id");
    $universities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Type</th><th>Email Domain</th></tr>";
    
    foreach ($universities as $uni) {
        $domain = $uni['uni_email_domain'] ?: 'NULL';
        echo "<tr>";
        echo "<td>{$uni['uni_id']}</td>";
        echo "<td>{$uni['uni_name']}</td>";
        echo "<td>{$uni['uni_type']}</td>";
        echo "<td>{$domain}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?> 