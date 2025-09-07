<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (empty($_SESSION['ua_user'])) {
    http_response_code(403);
    echo 'Unauthorized';
    exit;
}

$ua = $_SESSION['ua_user'];
// Support multiple parameter names for attachment id
$attParam = $_GET['id'] ?? $_GET['att_id'] ?? $_GET['attachment_id'] ?? '';
$att_id = (int)$attParam;
if ($att_id <= 0) {
    http_response_code(400);
    echo 'Bad request';
    exit;
}

// Load attachment and ensure it belongs to same university (if university_id is set)
$params = [$att_id];
$where = 'a.id = ?';

if (!empty($ua['university_id']) && (int)$ua['university_id'] !== 27) {
    $where .= ' AND r.university_id = ?';
    $params[] = (int)$ua['university_id'];
}

$stmt = $pdo->prepare("SELECT a.*, r.university_id FROM attachments a JOIN reports r ON a.report_id = r.id WHERE $where LIMIT 1");
$stmt->execute($params);
$att = $stmt->fetch();

if (!$att) {
    http_response_code(404);
    echo 'Not found or access denied';
    exit;
}

$storedName = basename($att['stored_name']);
$filePath = __DIR__ . '/../../uploads/reports/' . $storedName;
if (!is_file($filePath)) {
    http_response_code(404);
    echo 'File missing';
    exit;
}

// Stream file
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


