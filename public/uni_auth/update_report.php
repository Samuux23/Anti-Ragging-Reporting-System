<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';

if (empty($_SESSION['ua_user'])) {
    header('Location: login.php');
    exit;
}

$ua = $_SESSION['ua_user'];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request.');
    }

    $report_id = (int)($_POST['id'] ?? 0);
    $new_status = trim($_POST['status'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (!$report_id || !$new_status) {
        throw new Exception('Missing required fields.');
    }

    // Ensure the report belongs to this university (if university_id is set)
    $params = [$report_id];
    $where = 'id = ?';
    
    if (!empty($ua['university_id']) && (int)$ua['university_id'] !== 27) {
        $where .= ' AND university_id = ?';
        $params[] = (int)$ua['university_id'];
    }
    
    $stmt = $pdo->prepare("SELECT status FROM reports WHERE $where");
    $stmt->execute($params);
    $current = $stmt->fetchColumn();
    
    if ($current === false) {
        throw new Exception('Report not found or access denied.');
    }

    // Update report status
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('UPDATE reports SET status = ? WHERE id = ?');
    $stmt->execute([$new_status, $report_id]);

    // Insert status history with notes as published response
    $stmt = $pdo->prepare('INSERT INTO status_history (report_id, old_status, new_status, changed_at, changed_by, notes) VALUES (?, ?, ?, NOW(), ?, ?)');
    $changed_by = $ua['name'] . ' (' . $ua['email'] . ')';
    $stmt->execute([$report_id, $current, $new_status, $changed_by, $notes]);

    // Optional: mark process timeline steps based on status
    $map = [
        'Submitted' => 'Report Submission',
        'Under Review' => 'Initial Review',
        'Action Initiated' => 'Investigation',
        'Resolved' => 'Resolution'
    ];
    if (isset($map[$new_status])) {
        $stmt = $pdo->prepare("UPDATE process_timeline SET status = 'completed', completed_at = NOW() WHERE report_id = ? AND step_name = ?");
        $stmt->execute([$report_id, $map[$new_status]]);
    }

    $pdo->commit();

    header('Location: report.php?id=' . $report_id . '&ok=1');
    exit;
} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $msg = urlencode($e->getMessage());
    header('Location: report.php?id=' . ((int)($_POST['id'] ?? 0)) . '&err=' . $msg);
    exit;
}


