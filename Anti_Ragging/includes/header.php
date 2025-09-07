<?php require_once dirname(__DIR__) . '/config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Anti‑Ragging Portal</title>
  <link rel="stylesheet" href="/../assets/css/style.css" />
  <script defer src="/../assets/js/app.js"></script>
  <link rel="icon" href="/../assets/images/favicon.ico" type="image/x-icon">
</head>
<body>
<header class="site-header">
  <div class="container">
    <div class="brand">
      <div class="logo">
        <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
          <rect width="32" height="32" rx="6" fill="#1e40af"/>
          <path d="M16 6L24 12V20C24 24 20 28 16 28C12 28 8 24 8 20V12L16 6Z" fill="white"/>
          <path d="M16 10L20 13V18C20 20 18 22 16 22C14 22 12 20 12 18V13L16 10Z" fill="#1e40af"/>
        </svg>
      </div>
      <span>Anti‑Ragging Portal</span>
    </div>
    <nav class="nav">
      <a href="<?= BASE_URL ?>/index.php" id="nav-home">Home</a>
      <a href="<?= BASE_URL ?>/submit.php" id="nav-submit">Submit Report</a>
      <a href="<?= BASE_URL ?>/track.php" id="nav-track">Track Status</a>
      <a href="<?= BASE_URL ?>/uni_auth/index.php" id="nav-admin">Admin</a>
    </nav>
    <div class="header-actions">
      <button class="icon-btn" id="dark-mode-toggle">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" fill="currentColor"/>
        </svg>
      </button>
    </div>
  </div>
</header>
<main class="container">
