<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';
if (empty($_SESSION['admin_user'])) { header('Location: login.php'); exit; }

$msg = '';$err = '';

// Ensure table/columns exist (defensive)
try {
  $pdo->exec("CREATE TABLE IF NOT EXISTS university_authorities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    university_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    email VARCHAR(200) NOT NULL,
    position VARCHAR(150) NOT NULL,
    department VARCHAR(150) DEFAULT '',
    phone VARCHAR(50) DEFAULT '',
    password_hash VARCHAR(255) NOT NULL DEFAULT '',
    is_active TINYINT(1) NOT NULL DEFAULT 1
  )");
} catch (Exception $e) {}

// Handle updates
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['id'])) {
  $id = (int)$_POST['id'];
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $position = trim($_POST['position'] ?? '');
  $department = trim($_POST['department'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $is_active = isset($_POST['is_active']) ? 1 : 0;
  $new_password = trim($_POST['new_password'] ?? '');
  try {
    if (!$id || !$name || !$email || !$position) throw new Exception('Please fill required fields.');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception('Invalid email.');
    if ($new_password !== '') {
      $hash = password_hash($new_password, PASSWORD_BCRYPT);
      $stmt = $pdo->prepare("UPDATE university_authorities SET name=?, email=?, position=?, department=?, phone=?, is_active=?, password_hash=? WHERE id=?");
      $stmt->execute([$name,$email,$position,$department,$phone,$is_active,$hash,$id]);
    } else {
      $stmt = $pdo->prepare("UPDATE university_authorities SET name=?, email=?, position=?, department=?, phone=?, is_active=? WHERE id=?");
      $stmt->execute([$name,$email,$position,$department,$phone,$is_active,$id]);
    }
    $msg = 'Authority updated.';
  } catch (Exception $e) { $err = $e->getMessage(); }
}

// Load authorities with university name
$stmt = $pdo->query("SELECT ua.*, u.uni_name FROM university_authorities ua LEFT JOIN university u ON ua.university_id = u.uni_id ORDER BY u.uni_name, ua.name");
$authorities = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Manage Authorities</title>
  <link rel="icon" href="../../assets/images/favicon.ico">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
  <?php include_once __DIR__ . '/../../includes/header.php'; ?>
  <div class="layout" style="margin: 20px 0;">
    <?php include_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
    <section class="content" style="text-align:left;">
      <div class="page-header"><h1>Manage University Authorities</h1></div>
      <?php if ($msg): ?><div class="card" style="border-color:#c3e6cb; color:#155724;"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
      <?php if ($err): ?><div class="alert"><?= htmlspecialchars($err) ?></div><?php endif; ?>
      <?php if (!$authorities): ?>
        <div class="card">No authorities found.</div>
      <?php else: ?>
        <?php foreach ($authorities as $a): ?>
          <div class="form-card" style="margin-bottom:16px;">
            <form method="post">
              <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
              <div class="form-grid">
                <div class="form-group">
                  <label>University</label>
                  <input value="<?= htmlspecialchars($a['uni_name'] ?: '') ?>" disabled>
                </div>
                <div class="form-group">
                  <label>Name</label>
                  <input name="name" value="<?= htmlspecialchars($a['name']) ?>" required>
                </div>
              </div>
              <div class="form-grid">
                <div class="form-group">
                  <label>Email</label>
                  <input name="email" type="email" value="<?= htmlspecialchars($a['email']) ?>" required>
                </div>
                <div class="form-group">
                  <label>Position</label>
                  <input name="position" value="<?= htmlspecialchars($a['position']) ?>" required>
                </div>
              </div>
              <div class="form-grid">
                <div class="form-group">
                  <label>Department</label>
                  <input name="department" value="<?= htmlspecialchars($a['department']) ?>">
                </div>
                <div class="form-group">
                  <label>Phone</label>
                  <input name="phone" value="<?= htmlspecialchars($a['phone']) ?>">
                </div>
              </div>
              <div class="form-grid">
                <div class="form-group">
                  <label>New Password (leave blank to keep)</label>
                  <input name="new_password" type="password" placeholder="••••••">
                </div>
                <div class="form-group" style="display:flex; align-items:flex-end; gap:8px;">
                  <label style="margin-bottom:0;">Active</label>
                  <input type="checkbox" name="is_active" <?= (int)$a['is_active'] === 1 ? 'checked' : '' ?> style="width:auto;">
                </div>
              </div>
              <button class="btn primary" type="submit">Save Changes</button>
            </form>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </div>
  <?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>


