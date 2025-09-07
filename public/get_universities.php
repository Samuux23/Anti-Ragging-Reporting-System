<?php
require_once __DIR__ . '/../config/db.php';

// Get the university type from GET parameter
$type = trim($_GET['type'] ?? '');

// Set content type to HTML since we're returning option elements
header('Content-Type: text/html; charset=UTF-8');

// Default option
echo '<option value="">-- Select University --</option>';

if (empty($type)) {
    exit;
}

try {
    // Prepare and execute query to get universities of the specified type
    $stmt = $pdo->prepare("SELECT uni_id, uni_name FROM university WHERE uni_type = ? ORDER BY uni_name ASC");
    $stmt->execute([$type]);
    $universities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($universities)) {
        echo '<option value="">No universities found for this type</option>';
        exit;
    }
    
    // Generate option elements
    foreach ($universities as $university) {
        $uni_id = (int)$university['uni_id'];
        $uni_name = htmlspecialchars($university['uni_name'], ENT_QUOTES, 'UTF-8');
        
        echo '<option value="' . $uni_id . '">' . $uni_name . '</option>';
    }
    
} catch (PDOException $e) {
    // Log error and show user-friendly message
    error_log("Database error in get_universities.php: " . $e->getMessage());
    echo '<option value="">Error loading universities</option>';
} catch (Exception $e) {
    // Log general error
    error_log("General error in get_universities.php: " . $e->getMessage());
    echo '<option value="">Error loading universities</option>';
}
?>