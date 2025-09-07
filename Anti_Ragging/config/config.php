<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'u192900825_anti_ragging');
define('DB_USER', 'u192900825_ar_admin');
define('DB_PASS', '8pqCpmwiC!F4Bq5');

define('BASE_URL', '/public');

define('MAX_UPLOAD_BYTES', 50 * 1024 * 1024); // Increased to 50MB to match frontend
define('ALLOWED_MIME', serialize([
  // Images
  'image/jpeg', 'image/png', 'image/webp',
  // Documents
  'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'text/plain', 'application/rtf',
  // Audio
  'audio/mpeg', 'audio/wav', 'audio/mp3',
  // Video
  'video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo'
]));

// Email Configuration
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 465);
define('SMTP_USERNAME', 'report@antiragging.xyz');
define('SMTP_PASSWORD', '8pqCpmwiC!F4Bq5');
define('SMTP_FROM_EMAIL', 'report@antiragging.xyz');
define('SMTP_FROM_NAME', 'Anti-Ragging Portal');

date_default_timezone_set('Asia/Colombo');

$uploadsRoot = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
if (!is_dir($uploadsRoot)) {
  @mkdir($uploadsRoot, 0775, true);
}
