<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
if (empty($_SESSION['admin_user'])) { http_response_code(403); exit('Unauthorized'); }

// Support multiple parameter names
$attParam = $_GET['id'] ?? $_GET['att_id'] ?? $_GET['attachment_id'] ?? '';
$att_id = (int)$attParam;
if ($att_id <= 0) { http_response_code(400); echo 'Bad request'; exit; }

$stmt = $pdo->prepare("SELECT a.*, r.university_id FROM attachments a JOIN reports r ON a.report_id = r.id WHERE a.id = ? LIMIT 1");
$stmt->execute([$att_id]);
$att = $stmt->fetch();
if (!$att) { http_response_code(404); echo 'Not found'; exit; }

$storedName = basename($att['stored_name']);
$filePath = __DIR__ . '/../../uploads/reports/' . $storedName;
if (!is_file($filePath)) { http_response_code(404); echo 'File missing'; exit; }

$mime = $att['mime_type'];
if (!$mime) {
    $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;
    if ($finfo) {
        $mime = finfo_file($finfo, $filePath) ?: 'application/octet-stream';
        finfo_close($finfo);
    } else {
        $mime = 'application/octet-stream';
    }
}
header('Content-Type: ' . $mime);
header('Content-Length: ' . (string)filesize($filePath));
header('Content-Disposition: inline; filename="' . basename($att['original_name']) . '"');
readfile($filePath);
exit;


