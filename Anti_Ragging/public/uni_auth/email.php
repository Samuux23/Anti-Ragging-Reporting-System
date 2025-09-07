<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';

if (empty($_SESSION['ua_user'])) {
    header('Location: login.php');
    exit;
}

$ua = $_SESSION['ua_user'];
$report_id = (int)($_GET['report_id'] ?? 0);
if (!$report_id) { header('Location: dashboard.php'); exit; }

// Check for password update messages
$password_updated = isset($_GET['password_updated']) && $_GET['password_updated'] == '1';
$password_error = $_GET['password_error'] ?? '';

// Ensure report belongs to this university (if university_id is set and not 27) and load summary
$params = [$report_id];
$where = 'r.id = ?';

if (!empty($ua['university_id']) && (int)$ua['university_id'] !== 27) {
    $where .= ' AND r.university_id = ?';
    $params[] = (int)$ua['university_id'];
}

$stmt = $pdo->prepare("SELECT r.id, r.report_code, r.incident_type, r.department, r.created_at, r.status, u.uni_name FROM reports r LEFT JOIN university u ON r.university_id = u.uni_id WHERE $where");
$stmt->execute($params);
$report = $stmt->fetch();

if (!$report) { header('Location: dashboard.php'); exit; }

// Load all active authorities across universities for directory selection
$authorities = [];
$stmtU = $pdo->prepare('SELECT ua.id, ua.name, ua.email, ua.position, ua.university_id, u.uni_name FROM university_authorities ua LEFT JOIN university u ON ua.university_id = u.uni_id WHERE ua.is_active = 1 ORDER BY u.uni_name ASC, ua.name ASC');
$stmtU->execute();
$authorities = $stmtU->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Email about <?= htmlspecialchars($report['report_code']) ?></title>
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
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 24px;
            transition: color 0.2s ease;
        }
        
        .back-link:hover {
            color: var(--primary-dark);
        }
        
        .form-card {
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 40px;
            box-shadow: var(--shadow-lg);
            max-width: 800px;
        }
        
        .form-card h2 {
            font-size: 28px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .form-intro {
            color: var(--text-secondary);
            margin-bottom: 32px;
        }
        
        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
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
        
        .chips {
            margin-top: 8px;
        }
        
        .chip {
            display: inline-block;
            background: var(--bg-secondary);
            color: var(--text-secondary);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-right: 8px;
        }
        
        .actions {
            display: flex;
            gap: 16px;
            justify-content: flex-start;
            margin-top: 32px;
        }
        
        @media (max-width: 768px) {
            .row {
                grid-template-columns: 1fr;
            }
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
                <?php if (!empty($ua['university_name'])): ?>
                    <div class="university-info"><?= htmlspecialchars($ua['university_name']) ?></div>
                <?php else: ?>
                    <div class="university-info">All Universities</div>
                <?php endif; ?>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php">
                    Dashboard
                </a>
                <a href="update_profile.php">
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
                    <strong>Send Email</strong>
                    <?php if (!empty($ua['university_name'])): ?>
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
            
            <a href="report.php?id=<?= (int)$report['id'] ?>" class="back-link">← Back to Report</a>
            
            <div class="form-card">
                <h2>Email Authorities</h2>
                <p class="form-intro">Compose an email regarding report <strong><?= htmlspecialchars($report['report_code']) ?></strong>. You can select recipients from the directory and/or add additional email addresses.</p>
                
                <form method="post" action="send_email.php">
                    <input type="hidden" name="report_id" value="<?= (int)$report['id'] ?>">
                    
                    <?php if ($authorities): ?>
                    <div class="row">
                        <div class="form-group">
                            <label>Directory Recipients</label>
                            <div style="max-height:260px; overflow:auto; border:1px solid var(--border); border-radius:8px; padding:10px;">
                                <?php foreach ($authorities as $a): if (strcasecmp($a['email'], $ua['email'])===0) continue; ?>
                                    <label style="display:block; margin-bottom:8px; font-weight:400;">
                                        <input type="checkbox" name="recipients[]" value="<?= htmlspecialchars($a['email']) ?>">
                                        <?= htmlspecialchars($a['name'] . ' - ' . $a['position']) ?> (<?= htmlspecialchars($a['email']) ?>)
                                        <?php if (!empty($a['uni_name'])): ?>
                                            <span class="chip"><?= htmlspecialchars($a['uni_name']) ?></span>
                                        <?php endif; ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <div class="chips">
                                <span class="chip">Select one or more recipients</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Additional Emails (comma-separated)</label>
                            <input type="text" name="extra_emails" placeholder="user1@example.com, user2@example.com">
                            <div class="chips">
                                <span class="chip">Non-directory recipients allowed</span>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="form-group">
                        <label>Recipient Emails (comma-separated)</label>
                        <input type="text" name="extra_emails" placeholder="user1@example.com, user2@example.com" required>
                        <div class="chips">
                            <span class="chip">Enter email addresses separated by commas</span>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject" value="Action on report <?= htmlspecialchars($report['report_code']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="message" rows="8" required>Dear Team,

Please review the following report and provide your feedback or actions.

Report Code: <?= htmlspecialchars($report['report_code']) ?>
Incident Type: <?= htmlspecialchars(ucwords(str_replace('_',' ', $report['incident_type']))) ?>
Department: <?= htmlspecialchars($report['department']) ?>
Status: <?= htmlspecialchars($report['status']) ?>
<?php if (!empty($report['uni_name'])): ?>University: <?= htmlspecialchars($report['uni_name']) ?><?php endif; ?>

Regards,
<?= htmlspecialchars($ua['name']) ?>
<?= htmlspecialchars($ua['position']) ?>
<?= htmlspecialchars($ua['university_name'] ?? 'Anti-Ragging Portal') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label><input type="checkbox" name="attach_pdf" value="1" checked> Attach PDF summary of the report</label>
                    </div>
                    
                    <div class="actions">
                        <button class="btn primary" type="submit">Send Email</button>
                        <a class="btn secondary" href="report.php?id=<?= (int)$report['id'] ?>">Cancel</a>
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


