<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';

if (empty($_SESSION['ua_user'])) {
    header('Location: login.php');
    exit;
}

$ua = $_SESSION['ua_user'];
$message = '';
$error = '';
$password_message = '';
$password_error = '';

// Check for password update messages
$password_updated = isset($_GET['password_updated']) && $_GET['password_updated'] == '1';
$password_error = $_GET['password_error'] ?? '';

// Handle profile form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    if (empty($name) || empty($position)) {
        $error = 'Name and position are required fields.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE university_authorities SET name = ?, position = ?, department = ?, phone = ?, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$name, $position, $department, $phone, $ua['id']]);
            
            if ($result) {
                // Update session data
                $_SESSION['ua_user']['name'] = $name;
                $_SESSION['ua_user']['position'] = $position;
                $_SESSION['ua_user']['department'] = $department;
                $_SESSION['ua_user']['phone'] = $phone;
                
                $message = 'Profile updated successfully!';
                
                // Refresh user data
                $stmt = $pdo->prepare("SELECT ua.*, u.uni_name FROM university_authorities ua LEFT JOIN university u ON ua.university_id = u.uni_id WHERE ua.id = ?");
                $stmt->execute([$ua['id']]);
                $_SESSION['ua_user'] = $stmt->fetch();
                $ua = $_SESSION['ua_user'];
            } else {
                $error = 'Failed to update profile. Please try again.';
            }
        } catch (PDOException $e) {
            $error = 'Database error occurred. Please try again.';
        }
    }
}

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    try {
        // Validate inputs
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            throw new Exception('All password fields are required');
        }
        
        if ($new_password !== $confirm_password) {
            throw new Exception('New passwords do not match');
        }
        
        if (strlen($new_password) < 6) {
            throw new Exception('New password must be at least 6 characters');
        }
        
        // Verify current password
        $stmt = $pdo->prepare("SELECT password_hash FROM university_authorities WHERE id = ? AND is_active = 1");
        $stmt->execute([(int)$ua['id']]);
        $authority = $stmt->fetch();
        
        if (!$authority || !password_verify($current_password, $authority['password_hash'])) {
            throw new Exception('Current password is incorrect');
        }
        
        // Update password and set is_password_changed to 1
        $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE university_authorities SET password_hash = ?, is_password_changed = 1 WHERE id = ?");
        $stmt->execute([$new_hash, (int)$ua['id']]);
        
        // Update session
        $_SESSION['ua_user']['is_password_changed'] = 1;
        
        $password_message = 'Password updated successfully!';
        
    } catch (Exception $e) {
        $password_error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Update Profile - Authority Dashboard</title>
    <link rel="icon" href="../../assets/images/favicon.ico">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        /* Password Popup */
        .password-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .password-popup .popup-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 400px;
            width: 90%;
            text-align: center;
        }
        .password-popup h3 {
            margin-bottom: 20px;
            color: #dc2626;
        }
        .password-popup input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 16px;
        }
        .password-popup .btn {
            margin: 0 8px;
        }
        .password-popup .error-message {
            color: #dc2626;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .password-popup .success-message {
            color: #059669;
            margin-bottom: 16px;
            font-size: 14px;
        }
        
        /* Dashboard Layout */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: var(--bg-secondary);
            border-right: 1px solid var(--border);
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
        }
        
        .sidebar-header h3 {
            color: var(--primary);
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .sidebar-header p {
            color: var(--text-secondary);
            font-size: 14px;
            margin: 0;
        }
        
        .sidebar-nav {
            padding: 0 20px;
        }
        
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-primary);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 8px;
            transition: all 0.2s ease;
        }
        
        .sidebar-nav a:hover {
            background: var(--bg-accent);
            color: var(--primary);
        }
        
        .sidebar-nav a.active {
            background: var(--primary);
            color: white;
        }
        
        .sidebar-nav .icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .topbar {
            background: var(--bg-primary);
            border-bottom: 1px solid var(--border);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .topbar strong {
            color: var(--primary);
            font-size: 16px;
        }
        
        .topbar .muted {
            color: var(--text-light);
            margin: 0 10px;
        }
        
        .university-info {
            color: var(--text-secondary);
            font-size: 14px;
            margin-top: 5px;
        }
        
        .profile-form, .password-form {
            max-width: 600px;
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 40px;
            box-shadow: var(--shadow-lg);
            margin-bottom: 30px;
        }
        
        .profile-form h2, .password-form h2 {
            color: var(--text-primary);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.2s ease;
            background: var(--bg-primary);
            color: var(--text-primary);
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }
        
        .form-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin-top: 32px;
        }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            text-align: center;
        }
        
        .alert.success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }
        
        .alert.error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }
        
        .readonly-field {
            background: var(--bg-secondary) !important;
            color: var(--text-secondary) !important;
            cursor: not-allowed;
        }
        
        .section-divider {
            border-top: 1px solid var(--border);
            margin: 40px 0;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <?php if (isset($ua['is_password_changed']) && (int)$ua['is_password_changed'] === 0): ?>
    <div class="password-popup" id="passwordPopup">
      <div class="popup-content">
        <h3>Password Update Required</h3>
        <p>For security reasons, please update your password.</p>
        
        <?php if ($password_error): ?>
          <div class="error-message"><?= htmlspecialchars($password_error) ?></div>
        <?php endif; ?>
        
        <?php if ($password_updated): ?>
          <div class="success-message">Password updated successfully! The popup will close automatically.</div>
          <script>
            // Close popup after 2 seconds
            setTimeout(function() {
              document.getElementById('passwordPopup').style.display = 'none';
            }, 2000);
          </script>
        <?php else: ?>
          <form id="passwordForm" method="post" action="update_password.php">
            <input type="password" name="current_password" placeholder="Current Password" required>
            <input type="password" name="new_password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
            <div>
              <button type="submit" class="btn primary">Update Password</button>
            </div>
          </form>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
    
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3><?= htmlspecialchars($ua['name']) ?></h3>
                <p><?= htmlspecialchars($ua['position']) ?></p>
                <?php if (!empty($ua['university_id']) && (int)$ua['university_id'] !== 27 && !empty($ua['university_name'])): ?>
                    <div class="university-info"><?= htmlspecialchars($ua['university_name']) ?></div>
                <?php else: ?>
                    <div class="university-info">All Universities</div>
                <?php endif; ?>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php">
                    Dashboard
                </a>
                <a href="update_profile.php" class="active">
                    Update Profile
                </a>
                <a href="logout.php">
                    Logout
                </a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="topbar">
                <div>
                    <strong>Update Profile</strong>
                    <?php if (!empty($ua['university_id']) && (int)$ua['university_id'] !== 27 && !empty($ua['university_name'])): ?>
                        <span class="muted">|</span>
                        <span><?= htmlspecialchars($ua['university_name']) ?></span>
                    <?php else: ?>
                        <span class="muted">|</span>
                        <span>All Universities</span>
                    <?php endif; ?>
                </div>
                <div>
                    <span>Welcome, <?= htmlspecialchars($ua['name']) ?></span>
                </div>
            </div>
            
            <!-- Profile Update Form -->
            <div class="profile-form">
                <h2>Update Your Profile</h2>
                
                <?php if ($message): ?>
                    <div class="alert success"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="post">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" value="<?= htmlspecialchars($ua['email']) ?>" readonly class="readonly-field">
                        <small>Email address cannot be changed</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($ua['name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="position">Position *</label>
                        <input type="text" id="position" name="position" value="<?= htmlspecialchars($ua['position']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="department">Department</label>
                        <input type="text" id="department" name="department" value="<?= htmlspecialchars($ua['department'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($ua['phone'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="university">University</label>
                        <input type="text" id="university" value="<?= htmlspecialchars($ua['university_name'] ?? 'All Universities') ?>" readonly class="readonly-field">
                        <small>University assignment cannot be changed</small>
                    </div>
                    
                    <div class="form-actions">
                        <a href="dashboard.php" class="btn secondary">Cancel</a>
                        <button type="submit" class="btn primary">Update Profile</button>
                    </div>
                </form>
            </div>
            
            <!-- Password Change Form -->
            <div class="password-form">
                <h2>Change Password</h2>
                
                <?php if ($password_message): ?>
                    <div class="alert success"><?= htmlspecialchars($password_message) ?></div>
                <?php endif; ?>
                
                <?php if ($password_error): ?>
                    <div class="alert error"><?= htmlspecialchars($password_error) ?></div>
                <?php endif; ?>
                
                <form method="post">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="form-group">
                        <label for="current_password">Current Password *</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password *</label>
                        <input type="password" id="new_password" name="new_password" required>
                        <small>Password must be at least 6 characters long</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn primary">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
      // Prevent closing password popup until password is changed
      document.addEventListener('DOMContentLoaded', function() {
        const popup = document.getElementById('passwordPopup');
        if (popup) {
          // Prevent clicking outside to close
          popup.addEventListener('click', function(e) {
            if (e.target === popup) {
              e.preventDefault();
              e.stopPropagation();
            }
          });
        }
      });
    </script>
</body>
</html>
