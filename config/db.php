<?php
// db.php - Database connection

$host = 'localhost';
$dbname = 'u192900825_anti_ragging';
$user = 'u192900825_ar_admin';
$pass = '8pqCpmwiC!F4Bq5';

try {
    // Use concatenation to avoid variable parsing issues in password
    $pdo = new PDO('mysql:host=' . $host . ';dbname=' . $dbname . ';charset=utf8mb4', $user, $pass);

    // Set PDO options
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // throw exceptions
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // associative array
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // use native prepares

    // Explicitly set collation to utf8mb4_unicode_ci
    $pdo->exec("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>