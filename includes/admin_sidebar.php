<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$admin = $_SESSION['admin_user'] ?? null;
?>
<aside class="sidemenu">
  <?php if ($admin): ?>
  <strong style="display:block; margin-bottom:8px;">Hello, <?= htmlspecialchars($admin['full_name'] ?? $admin['username'] ?? 'Admin') ?></strong>
  <?php endif; ?>
  <a href="dashboard.php">Home</a>
  <a href="analytics.php" target="_blank">Analytics</a>
  <a href="authorities_add.php">Add University Authority</a>
  <a href="authorities_manage.php">Manage Authorities</a>
  <a href="reports.php">Manage Reports</a>
  <a href="logs.php">Usage & Errors</a>
  <a href="backup.php">Backup System</a>
  <a href="logout.php">Logout</a>
</aside>


