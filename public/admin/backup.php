<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';
if (empty($_SESSION['admin_user'])) { header('Location: login.php'); exit; }

if (isset($_GET['download']) && $_GET['download'] === '1') {
    $tables = ['reports','status_history','process_timeline','report_views','university','university_authorities','attachments'];
    $boundary = 'BOUND_' . bin2hex(random_bytes(6));
    $zipName = 'backup_' . date('Ymd_His') . '.zip';
    $tmpZip = tempnam(sys_get_temp_dir(), 'bk');
    $zip = new ZipArchive();
    if ($zip->open($tmpZip, ZipArchive::CREATE)!==true) { die('Could not create archive'); }
    foreach ($tables as $t) {
        $stmt = $pdo->query('SELECT * FROM ' . $t);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $csv = '';
        if ($rows) {
            $csv .= implode(',', array_map(function($h){ return '"'.str_replace('"','""',$h).'"'; }, array_keys($rows[0]))) . "\r\n";
            foreach ($rows as $r) {
                $csv .= implode(',', array_map(function($v){ return '"'.str_replace('"','""',(string)$v).'"'; }, array_values($r))) . "\r\n";
            }
        }
        $zip->addFromString($t . '.csv', $csv);
    }
    $zip->close();
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename=' . $zipName);
    header('Content-Length: ' . filesize($tmpZip));
    readfile($tmpZip);
    @unlink($tmpZip);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Backup System</title>
  <link rel="icon" href="../../assets/images/favicon.ico">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
  <?php include_once __DIR__ . '/../../includes/header.php'; ?>
  <div class="layout" style="margin: 20px 0;">
    <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
    <section class="content">
  <div class="page-header"><h1>Backup System</h1><p class="muted">Download CSV dump as ZIP</p></div>
  <div class="card">
    <p>Exports key tables to CSV and bundles in a ZIP archive.</p>
    <a class="btn primary" href="backup.php?download=1">Download Backup ZIP</a>
  </div>
    </section>
  </div>
  <?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>


