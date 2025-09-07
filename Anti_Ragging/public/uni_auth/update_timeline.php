<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';

if (empty($_SESSION['ua_user'])) {
    http_response_code(403);
    echo 'Unauthorized';
    exit;
}

$ua = $_SESSION['ua_user'];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request.');
    }

    $report_id = (int)($_POST['report_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($report_id <= 0 || $action === '') {
        throw new Exception('Missing required fields.');
    }

    // Validate report access (if university_id is set, ensure report belongs to university)
    $params = [$report_id];
    $where = 'id = ?';
    if (!empty($ua['university_id']) && (int)$ua['university_id'] !== 27) {
        $where .= ' AND university_id = ?';
        $params[] = (int)$ua['university_id'];
    }
    $stmt = $pdo->prepare("SELECT status FROM reports WHERE $where");
    $stmt->execute($params);
    $currentStatus = $stmt->fetchColumn();
    if ($currentStatus === false) {
        throw new Exception('Report not found or access denied.');
    }

    if ($action === 'mark_university_notification_completed') {
        $stmt = $pdo->prepare("UPDATE process_timeline SET status = 'completed', completed_at = NOW() WHERE report_id = ? AND step_name = 'University Notification'");
        $stmt->execute([$report_id]);

        // Record in status history as a note
        $stmt = $pdo->prepare('INSERT INTO status_history (report_id, old_status, new_status, changed_at, changed_by, notes) VALUES (?, ?, ?, NOW(), ?, ?)');
        $changed_by = $ua['name'] . ' (' . $ua['email'] . ')';
        $note = 'Marked University Notification as completed.';
        $stmt->execute([$report_id, $currentStatus, $currentStatus, $changed_by, $note]);

        header('Location: report.php?id=' . $report_id . '&ok=1');
        exit;
    }

    if ($action === 'save_action_plan') {
        $plan = trim($_POST['action_plan'] ?? '');
        $planStatus = $_POST['plan_status'] ?? 'in_progress'; // in_progress or completed
        if ($plan === '') {
            throw new Exception('Please enter an action plan.');
        }

        // Update the Action Plan step description and status
        $stmt = $pdo->prepare("UPDATE process_timeline SET step_description = ?, status = ?, completed_at = CASE WHEN ? = 'completed' THEN NOW() ELSE completed_at END, started_at = COALESCE(started_at, NOW()) WHERE report_id = ? AND step_name = 'Action Plan'");
        $stmt->execute([$plan, ($planStatus === 'completed' ? 'completed' : 'in_progress'), ($planStatus === 'completed' ? 'completed' : 'in_progress'), $report_id]);

        // Record in status history as a note
        $stmt = $pdo->prepare('INSERT INTO status_history (report_id, old_status, new_status, changed_at, changed_by, notes) VALUES (?, ?, ?, NOW(), ?, ?)');
        $changed_by = $ua['name'] . ' (' . $ua['email'] . ')';
        $note = 'Updated Action Plan (' . ($planStatus === 'completed' ? 'completed' : 'in progress') . ').';
        $stmt->execute([$report_id, $currentStatus, $currentStatus, $changed_by, $note]);

        header('Location: report.php?id=' . $report_id . '&ok=1');
        exit;
    }

    throw new Exception('Unknown action.');
} catch (Exception $e) {
    $msg = urlencode($e->getMessage());
    header('Location: report.php?id=' . ((int)($_POST['report_id'] ?? 0)) . '&err=' . $msg);
    exit;
}


