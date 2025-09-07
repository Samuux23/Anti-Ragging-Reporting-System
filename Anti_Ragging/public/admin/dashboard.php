<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';
if (empty($_SESSION['admin_user'])) { header('Location: login.php'); exit; }
$admin = $_SESSION['admin_user'];

// Quick metrics
$metrics = [
  'reports_total' => 0,
  'reports_under_review' => 0,
  'reports_resolved' => 0
];
try {
  $metrics['reports_total'] = (int)$pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn();
  $metrics['reports_under_review'] = (int)$pdo->query("SELECT COUNT(*) FROM reports WHERE status='Under Review'")->fetchColumn();
  $metrics['reports_resolved'] = (int)$pdo->query("SELECT COUNT(*) FROM reports WHERE status='Resolved'")->fetchColumn();
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link rel="icon" href="../../assets/images/favicon.ico">
  <link rel="stylesheet" href="../../assets/css/style.css">
  <style>
    .layout { display:grid; grid-template-columns: 220px 1fr; gap:16px; }
    .sidemenu { border:1px solid var(--border); border-radius:12px; padding:16px; height: fit-content; }
    .sidemenu a { display:block; padding:10px 12px; border-radius:10px; text-decoration:none; color: var(--text-primary); margin-bottom:8px; }
    .sidemenu a:hover { background: var(--bg-secondary); }
    .content { }
    .cards { display:grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap:12px; }
  </style>
</head>
<body>
  <?php include_once __DIR__ . '/../../includes/header.php'; ?>
  <div class="layout" style="margin: 20px 0;">
    <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
    <section class="content">
      <div class="page-header">
        <h1>Admin Dashboard</h1>
        <p class="muted">Quick overview</p>
      </div>
      <div class="cards">
        <div class="card"><h3>Total Reports</h3><p class="muted"><?= $metrics['reports_total'] ?></p></div>
        <div class="card"><h3>Under Review</h3><p class="muted"><?= $metrics['reports_under_review'] ?></p></div>
        <div class="card"><h3>Resolved</h3><p class="muted"><?= $metrics['reports_resolved'] ?></p></div>
      </div>
      <div class="card" style="margin-top:16px;">
        <h3>Shortcuts</h3>
        <div class="actions" style="display:flex; gap:10px; margin-top:10px; flex-wrap:wrap;">
          <a class="btn primary" href="analytics.php">View Analytics</a>
          <a class="btn secondary" href="authorities_add.php">Add Authority</a>
          <a class="btn secondary" href="reports.php">Manage Reports</a>
        </div>
      </div>
    </section>
  </div>
  <?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>


