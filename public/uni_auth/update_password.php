<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (empty($_SESSION['ua_user'])) {
    header('Location: login.php');
    exit;
}

$ua = $_SESSION['ua_user'];
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    try {
        // Validate inputs
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            throw new Exception('All fields are required');
        }
        
        if ($new_password !== $confirm_password) {
            throw new Exception('New passwords do not match');
        }
        
        if (strlen($new_password) < 6) {
            throw new Exception('New password must be at least 6 characters');
        }
        
        // Verify current password
        $stmt = $pdo->prepare("SELECT password_hash FROM university_authorities WHERE id = ? AND is_active = 1");
        $stmt->execute([(int)$ua['id']]);
        $authority = $stmt->fetch();
        
        if (!$authority || !password_verify($current_password, $authority['password_hash'])) {
            throw new Exception('Current password is incorrect');
        }
        
        // Update password and set is_password_changed to 1
        $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE university_authorities SET password_hash = ?, is_password_changed = 1 WHERE id = ?");
        $stmt->execute([$new_hash, (int)$ua['id']]);
        
        // Update session
        $_SESSION['ua_user']['is_password_changed'] = 1;
        
        $success = true;
        
        // Redirect back to dashboard with success message
        header('Location: dashboard.php?password_updated=1');
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// If we get here, there was an error or it's a GET request
// Redirect back to dashboard with error
if ($error) {
    header('Location: dashboard.php?password_error=' . urlencode($error));
} else {
    header('Location: dashboard.php');
}
exit;
