<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';
if (empty($_SESSION['admin_user'])) { header('Location: login.php'); exit; }

// Example: show recent status_history entries and report_views as usage; errors assumed logged in server logs
$history = $pdo->query("SELECT sh.*, r.report_code FROM status_history sh JOIN reports r ON sh.report_id=r.id ORDER BY sh.changed_at DESC LIMIT 50")->fetchAll();
$views = $pdo->query("SELECT rv.*, r.report_code FROM report_views rv JOIN reports r ON rv.report_id=r.id ORDER BY rv.viewed_at DESC LIMIT 50")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Usage & Errors</title>
  <link rel="icon" href="../../assets/images/favicon.ico">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
  <?php include_once __DIR__ . '/../../includes/header.php'; ?>
  <div class="page-header"><h1>Usage & Errors</h1><p class="muted">Recent activity and views</p></div>
  <div class="card">
    <h3>Recent Status Changes</h3>
    <?php if (!$history): ?>No data<?php else: ?>
      <?php foreach ($history as $h): ?>
        <div class="card" style="margin-bottom:10px;">
          <strong><?= htmlspecialchars($h['report_code']) ?></strong> changed to <strong><?= htmlspecialchars($h['new_status']) ?></strong>
          <div class="muted">At <?= htmlspecialchars($h['changed_at']) ?> by <?= htmlspecialchars($h['changed_by']) ?></div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <div class="card" style="margin-top:16px;">
    <h3>Recent Views</h3>
    <?php if (!$views): ?>No data<?php else: ?>
      <?php foreach ($views as $v): ?>
        <div class="card" style="margin-bottom:10px;">
          <strong><?= htmlspecialchars($v['report_code']) ?></strong> viewed by <?= htmlspecialchars($v['viewer_type']) ?>
          <div class="muted">At <?= htmlspecialchars($v['viewed_at']) ?> (<?= htmlspecialchars($v['viewer_email']) ?>)</div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>


