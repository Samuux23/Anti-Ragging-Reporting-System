<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';
if (empty($_SESSION['admin_user'])) { header('Location: login.php'); exit; }

$report_id = (int)($_GET['id'] ?? 0);
if (!$report_id) { header('Location: reports.php'); exit; }

$stmt = $pdo->prepare("SELECT r.*, u.uni_name FROM reports r LEFT JOIN university u ON r.university_id = u.uni_id WHERE r.id = ?");
$stmt->execute([$report_id]);
$report = $stmt->fetch();
if (!$report) { header('Location: reports.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM attachments WHERE report_id = ? ORDER BY uploaded_at ASC");
$stmt->execute([$report_id]);
$attachments = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM status_history WHERE report_id = ? ORDER BY changed_at DESC");
$stmt->execute([$report_id]);
$history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Report <?= htmlspecialchars($report['report_code']) ?></title>
  <link rel="icon" href="../../assets/images/favicon.ico">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
  <?php include_once __DIR__ . '/../../includes/header.php'; ?>
  <div class="layout" style="margin: 20px 0;">
    <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
    <section class="content">
      <div class="page-header"><h1>Report <?= htmlspecialchars($report['report_code']) ?></h1></div>
      <div class="card" style="text-align:left;">
        <p class="muted">University: <?= htmlspecialchars($report['uni_name'] ?: 'Unknown') ?> • Status: <?= htmlspecialchars($report['status']) ?> • Submitted: <?= htmlspecialchars($report['created_at']) ?></p>
        <p><strong>Incident Type:</strong> <?= htmlspecialchars(ucwords(str_replace('_',' ',$report['incident_type']))) ?></p>
        <p><strong>Department:</strong> <?= htmlspecialchars($report['department']) ?></p>
        <p><strong>Location:</strong> <?= htmlspecialchars($report['location']) ?></p>
        <h3 style="margin-top:12px;">Details</h3>
        <div style="background:#f9fafb;padding:14px;border-radius:8px;white-space:pre-wrap;"><?= htmlspecialchars($report['details']) ?></div>
        <div style="margin-top:12px;">
          <a class="btn primary" href="download_pdf.php?id=<?= (int)$report['id'] ?>" target="_blank" rel="noopener">Download PDF</a>
        </div>
      </div>
      <?php if ($attachments): ?>
      <div class="card" style="margin-top:12px; text-align:left;">
        <h3>Attachments</h3>
        <ul style="margin-top:10px;">
          <?php foreach ($attachments as $a): ?>
            <li><a href="file.php?id=<?= (int)$a['id'] ?>" target="_blank" rel="noopener"><?= htmlspecialchars($a['original_name']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
      <?php if ($history): ?>
      <div class="card" style="margin-top:12px; text-align:left;">
        <h3>Status History</h3>
        <ul style="margin-top:10px;">
          <?php foreach ($history as $h): ?>
            <?php $statusText = isset($h['new_status']) ? $h['new_status'] : (isset($h['status']) ? $h['status'] : ''); ?>
            <?php $noteText = isset($h['note']) && $h['note'] !== '' ? ' (' . htmlspecialchars($h['note']) . ')' : ''; ?>
            <li><?= htmlspecialchars($h['changed_at']) ?> — <?= htmlspecialchars($statusText) ?><?= $noteText ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
    </section>
  </div>
  <?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>


