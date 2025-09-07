<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';

if (!empty($_SESSION['admin_user'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    try {
        $stmt = $pdo->prepare('SELECT * FROM ar_admin WHERE username = ? AND is_active = 1');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_user'] = [
                'username' => $admin['username'],
                'full_name' => $admin['full_name'],
                'email' => $admin['email'],
                'role' => $admin['role']
            ];
            $pdo->prepare('UPDATE ar_admin SET last_login = NOW() WHERE username = ?')->execute([$admin['username']]);
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid credentials.';
        }
    } catch (Exception $e) {
        $error = 'Login failed. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="icon" href="../../assets/images/favicon.ico">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .auth { max-width:420px; margin:60px auto; }
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <?php include_once __DIR__ . '/../../includes/header.php'; ?>
    <div class="auth">
        <div class="form-card">
            <h2>Admin Login</h2>
            <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label>Username</label>
                    <input name="username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button class="btn primary" type="submit">Sign In</button>
            </form>
        </div>
    </div>
    <?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>


