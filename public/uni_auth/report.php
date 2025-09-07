<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';

if (empty($_SESSION['ua_user'])) {
    header('Location: login.php');
    exit;
}

$ua = $_SESSION['ua_user'];
$report_id = (int)($_GET['id'] ?? 0);
if (!$report_id) { header('Location: dashboard.php'); exit; }

// Check for password update messages
$password_updated = isset($_GET['password_updated']) && $_GET['password_updated'] == '1';
$password_error = $_GET['password_error'] ?? '';

// Fetch report, ensure it belongs to this authority's university (if university_id is set)
$params = [$report_id];
$where = 'r.id = ?';

if (!empty($ua['university_id']) && (int)$ua['university_id'] !== 27) {
    $where .= ' AND r.university_id = ?';
    $params[] = (int)$ua['university_id'];
}

$stmt = $pdo->prepare("SELECT r.*, u.uni_name FROM reports r LEFT JOIN university u ON r.university_id = u.uni_id WHERE $where");
$stmt->execute($params);
$report = $stmt->fetch();

if (!$report) { header('Location: dashboard.php'); exit; }

// Fetch attachments
$stmt = $pdo->prepare("SELECT * FROM attachments WHERE report_id = ? ORDER BY uploaded_at ASC");
$stmt->execute([$report_id]);
$attachments = $stmt->fetchAll();

// Fetch status history
$stmt = $pdo->prepare("SELECT * FROM status_history WHERE report_id = ? ORDER BY changed_at DESC");
$stmt->execute([$report_id]);
$history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Report <?= htmlspecialchars($report['report_code']) ?></title>
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
        
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        
        .card {
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 32px;
            box-shadow: var(--shadow);
        }
        
        .card .title {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 16px;
        }
        
        .card .muted {
            color: var(--text-secondary);
            margin-bottom: 16px;
        }
        
        .card h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            margin: 24px 0 16px;
        }
        .filter-input { 
        width:100%; 
        padding:10px 12px; 
        border:1px solid #ccd4e0; 
        border-radius:6px; 
        font-size:14px; 
      }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .badge.s-submitted { background: #dbeafe; color: #1e40af; }
        .badge.s-review { background: #fef3c7; color: #d97706; }
        .badge.s-action { background: #fce7f3; color: #be185d; }
        .badge.s-resolved { background: #d1fae5; color: #059669; }
        .badge.s-rejected { background: #fee2e2; color: #dc2626; }
        
        .list {
            list-style: none;
            padding: 0;
        }
        
        .list li {
            padding: 12px 0;
            border-bottom: 1px solid var(--border-light);
        }
        
        .list li:last-child {
            border-bottom: none;
        }
        
        .file {
            color: var(--primary);
            text-decoration: none;
        }
        
        .file:hover {
            text-decoration: underline;
        }
        
        .btn2 {
            display: inline-block;
            padding: 8px 16px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }
        
        .btn2:hover {
            background: var(--primary-dark);
        }
        
        .ok {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        
        .error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        
        @media (max-width: 768px) {
            .grid {
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
                <a href="email.php">
                    Send Emails
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
                    <strong>Report Details</strong>
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
            
            <div class="grid">
                <div class="card">
                    <h2 class="title">Report <?= htmlspecialchars($report['report_code']) ?></h2>
                    <p class="muted">
                        Incident: <?= htmlspecialchars(ucwords(str_replace('_',' ', $report['incident_type']))) ?> | 
                        Department: <?= htmlspecialchars($report['department']) ?> | 
                        Submitted: <?= htmlspecialchars($report['created_at']) ?>
                        <?php if (!empty($report['uni_name'])): ?>| University: <?= htmlspecialchars($report['uni_name']) ?><?php endif; ?>
                    </p>
                    <p><strong>Status:</strong>
                        <?php $cls='s-submitted'; if($report['status']==='Under Review')$cls='s-review'; elseif($report['status']==='Action Initiated')$cls='s-action'; elseif($report['status']==='Resolved')$cls='s-resolved'; elseif($report['status']==='Rejected')$cls='s-rejected'; ?>
                        <span class="badge <?= $cls ?>"><?= htmlspecialchars($report['status']) ?></span>
                    </p>
                    <h3>Details</h3>
                    <div style="background:var(--bg-secondary);padding:14px;border-radius:8px;white-space:pre-wrap;"><?= htmlspecialchars($report['details']) ?></div>
                    <div style="margin-top:12px;">
                        <a class="btn btn2" href="download_pdf.php?id=<?= (int)$report['id'] ?>">Download PDF</a>
                    </div>
                    <?php if ($attachments): ?>
                        <h3>Attachments</h3>
                        <ul class="list">
                            <?php foreach ($attachments as $a): ?>
                                <li><a class="file" href="file.php?id=<?= (int)$a['id'] ?>" target="_blank" rel="noopener">📎 <?= htmlspecialchars($a['original_name']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if ($history): ?>
                        <h3>Status History</h3>
                        <ul class="list">
                            <?php foreach ($history as $h): ?>
                                <li><strong><?= htmlspecialchars($h['new_status']) ?></strong> <span class="muted">at <?= htmlspecialchars($h['changed_at']) ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <div class="card">
                    <h3>Update Status & Publish Response</h3>
                    <?php if (!empty($_GET['ok'])): ?><div class="ok">Update saved.</div><?php endif; ?>
                    <?php if (!empty($_GET['err'])): ?><div class="error"><?= htmlspecialchars($_GET['err']) ?></div><?php endif; ?>
                    <form method="post" action="update_report.php">
                        <input class='filter-input' type="hidden" name="id" value="<?= (int)$report['id'] ?>">
                        <label for="status">Status</label>
                        <select class='filter-input' id="status" name="status" required>
                            <?php $statuses=['Submitted','Under Review','Action Initiated','Resolved','Rejected']; foreach($statuses as $s){ $sel = $s===$report['status']?'selected':''; echo "<option $sel value=\"".htmlspecialchars($s)."\">".htmlspecialchars($s)."</option>"; } ?>
                        </select> <br>
                        <label for="notes">Response/Notes (visible in history)</label>
                        <textarea class='filter-input' id="notes" name="notes" rows="6" placeholder="Write your response, actions taken, or next steps..."></textarea><br>
                        <button class="btn" type="submit" style="margin-top:10px;">Save Update</button>
                    </form>
                    <div style="margin-top:12px;">
                        <a class="btn btn2" href="email.php?report_id=<?= (int)$report['id'] ?>">Email Authorities</a>
                    </div>
                </div>
                <div class="card">
                    <h3>Process Timeline Controls</h3>
                    <form method="post" action="update_timeline.php" style="margin-bottom:16px;">
                        <input type="hidden" name="report_id" value="<?= (int)$report['id'] ?>">
                        <input type="hidden" name="action" value="mark_university_notification_completed">
                        <button class="btn" type="submit">Mark "University Notification" as Completed</button>
                    </form>

                    <form method="post" action="update_timeline.php">
                        <input type="hidden" name="report_id" value="<?= (int)$report['id'] ?>">
                        <input type="hidden" name="action" value="save_action_plan">
                        <label for="action_plan">Action Plan</label>
                        <textarea class='filter-input' id="action_plan" name="action_plan" rows="5" placeholder="Describe the action plan..."></textarea>
                        <label for="plan_status" style="margin-top:8px; display:block;">Action Plan Status</label>
                        <select class='filter-input' id="plan_status" name="plan_status">
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                        <button class="btn" type="submit" style="margin-top:10px;">Save Action Plan</button>
                    </form>
                </div>
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


