<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Function to generate a unique report code
function generateReportCode() {
    return 'AR' . str_pad(random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT);
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['test_message'] = 'Invalid request method.';
    $_SESSION['test_message_type'] = 'error';
    header('Location: test_submit.php');
    exit;
}

// CSRF protection
if (empty($_POST['csrf']) || $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')) {
    $_SESSION['test_message'] = 'Security validation failed.';
    $_SESSION['test_message_type'] = 'error';
    header('Location: test_submit.php');
    exit;
}

try {
    // Get form data
    $uni_id = (int)($_POST['uni_id'] ?? 0);
    $verified_email = trim($_POST['verified_email'] ?? '');
    $incident_type = trim($_POST['incident_type'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $incident_datetime = $_POST['incident_datetime'] ?? '';
    $details = trim($_POST['details'] ?? '');
    
    // Basic validation
    if (!$uni_id || !$verified_email || !$incident_type || !$department || !$incident_datetime || !$details) {
        throw new Exception('All required fields must be filled.');
    }
    
    // Generate unique report code
    do {
        $report_code = generateReportCode();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reports WHERE report_code = ?");
        $stmt->execute([$report_code]);
        $exists = $stmt->fetchColumn() > 0;
    } while ($exists);
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Insert report
    $stmt = $pdo->prepare("
        INSERT INTO reports (
            report_code, incident_type, department, incident_datetime, 
            details, status, university_id, location, reporter_email, created_at
        ) VALUES (?, ?, ?, ?, ?, 'Submitted', ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $report_code,
        $incident_type,
        $department,
        $incident_datetime,
        $details,
        $uni_id,
        $location ?: null,
        $verified_email
    ]);
    
    $report_id = $pdo->lastInsertId();
    
    // Insert status history
    $stmt = $pdo->prepare("
        INSERT INTO status_history (report_id, old_status, new_status, changed_at) 
        VALUES (?, '', 'Submitted', NOW())
    ");
    $stmt->execute([$report_id]);
    
    // Commit transaction
    $pdo->commit();
    
    $_SESSION['test_message'] = "Test report submitted successfully! Report Code: {$report_code}, ID: {$report_id}";
    $_SESSION['test_message_type'] = 'success';
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['test_message'] = 'Database error: ' . $e->getMessage();
    $_SESSION['test_message_type'] = 'error';
} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['test_message'] = 'Error: ' . $e->getMessage();
    $_SESSION['test_message_type'] = 'error';
}

header('Location: test_submit.php');
exit;
?> 