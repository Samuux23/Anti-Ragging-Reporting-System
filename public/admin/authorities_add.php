<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';
if (empty($_SESSION['admin_user'])) { header('Location: login.php'); exit; }

$msg = '';$err='';

// Ensure password_hash column exists (safe alter if missing)
try {
    $col = $pdo->query("SHOW COLUMNS FROM university_authorities LIKE 'password_hash'")->fetch();
    if (!$col) { $pdo->exec("ALTER TABLE university_authorities ADD COLUMN password_hash VARCHAR(255) NOT NULL DEFAULT '' AFTER department"); }
} catch (Exception $e) {}

// Load universities
$universities = $pdo->query("SELECT uni_id, uni_name FROM university ORDER BY uni_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $university_id = (int)($_POST['university_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    try {
        if (!$university_id || !$name || !$email || !$position || !$password) throw new Exception('Please fill required fields.');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception('Invalid email.');
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO university_authorities (university_id, name, email, position, department, password_hash, phone, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$university_id, $name, $email, $position, $department, $hash, $phone]);
        $msg = 'Authority added.';
    } catch (Exception $e) { $err = $e->getMessage(); }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Add University Authority</title>
  <link rel="icon" href="../../assets/images/favicon.ico">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
  <?php include_once __DIR__ . '/../../includes/header.php'; ?>
  <div class="layout" style="margin: 20px 0;">
    <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
    <section class="content">
  <div class="page-header">
    <h1>Add University Authority</h1>
  </div>
  <div class="form-card">
    <?php if ($msg): ?><div class="card" style="border-color:#c3e6cb; color:#155724;"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert"><?= htmlspecialchars($err) ?></div><?php endif; ?>
    <form method="post">
      <div class="form-grid">
        <div class="form-group">
          <label>University</label>
          <select name="university_id" required>
            <option value="">Select</option>
            <?php foreach ($universities as $u): ?>
              <option value="<?= (int)$u['uni_id'] ?>"><?= htmlspecialchars($u['uni_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Name</label>
          <input name="name" required>
        </div>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label>Email</label>
          <input name="email" type="email" required>
        </div>
        <div class="form-group">
          <label>Position</label>
          <input name="position" required>
        </div>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label>Department</label>
          <input name="department">
        </div>
        <div class="form-group">
          <label>Phone</label>
          <input name="phone">
        </div>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <button class="btn primary" type="submit">Add Authority</button>
    </form>
  </div>
    </section>
  </div>
  <?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>


