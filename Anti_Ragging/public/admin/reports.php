<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';
if (empty($_SESSION['admin_user'])) { header('Location: login.php'); exit; }

$q = trim($_GET['q'] ?? '');
$params = [];
$where = '1=1';
if ($q !== '') { $where .= ' AND (r.report_code LIKE ? OR r.incident_type LIKE ? OR r.department LIKE ?)'; $params = ["%$q%","%$q%","%$q%"]; }

$stmt = $pdo->prepare("SELECT r.*, u.uni_name FROM reports r LEFT JOIN university u ON r.university_id=u.uni_id WHERE $where ORDER BY r.created_at DESC LIMIT 300");
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Manage Reports</title>
  <link rel="icon" href="../../assets/images/favicon.ico">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
  <?php include_once __DIR__ . '/../../includes/header.php'; ?>
  <div class="layout" style="margin: 20px 0;">
    <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
    <section class="content">
  <div class="page-header">
    <h1>Manage Reports</h1>
  </div>
  <div class="card">
    <form class="filters" method="get" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
      <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search reports..." style="padding: 12px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size:14px; transition: border-color 0.2s;">
      <button class="btn primary" type="submit">Search</button>
    </form>
    <div style="margin-top:12px;">
      <?php if (!$rows): ?>No reports found.<?php else: ?>
        <?php foreach ($rows as $r): ?>
          <div class="card" style="margin-bottom:10px;">
            <strong><?= htmlspecialchars($r['report_code']) ?></strong>
            <div class="muted">Status: <?= htmlspecialchars($r['status']) ?> • <?= htmlspecialchars($r['uni_name'] ?: 'Unknown') ?> • <?= htmlspecialchars($r['created_at']) ?></div>
            <div class="actions" style="display:flex; gap:8px; margin-top:8px;">
              <a class="btn secondary" href="download_pdf.php?id=<?= (int)$r['id'] ?>" target="_blank">Download PDF</a>
              <a class="btn primary" href="report_view.php?id=<?= (int)$r['id'] ?>" target="_blank">Open</a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
    </section>
  </div>
  <?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>


