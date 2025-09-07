<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';

if (empty($_SESSION['ua_user'])) {
    header('Location: login.php');
    exit;
}

$ua = $_SESSION['ua_user'];

// Check for password update messages
$password_updated = isset($_GET['password_updated']) && $_GET['password_updated'] == '1';
$password_error = $_GET['password_error'] ?? '';

// Filters
$status = $_GET['status'] ?? '';
$q = trim($_GET['q'] ?? '');

// Fetch reports based on university_id
$params = [];
$where = '1=1'; // Default condition

// If university_id is not NULL and not 27, filter by university, otherwise show all reports
if (!empty($ua['university_id']) && (int)$ua['university_id'] !== 27) {
    $where = 'r.university_id = ?';
    $params[] = (int)$ua['university_id'];
}

if ($status !== '') {
    $where .= ' AND r.status = ?';
    $params[] = $status;
}

if ($q !== '') {
    $where .= ' AND (r.report_code LIKE ? OR r.incident_type LIKE ? OR r.department LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

$stmt = $pdo->prepare("SELECT r.id, r.report_code, r.incident_type, r.department, r.status, r.created_at, r.location, u.uni_name FROM reports r LEFT JOIN university u ON r.university_id = u.uni_id WHERE $where ORDER BY r.created_at DESC LIMIT 200");
$stmt->execute($params);
$reports = $stmt->fetchAll();

// Fetch attachments for listed reports (limited fields)
$attachmentsByReport = [];
if ($reports) {
    $ids = array_map(function($r){ return (int)$r['id']; }, $reports);
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmtA = $pdo->prepare("SELECT id, report_id, original_name FROM attachments WHERE report_id IN ($in) ORDER BY uploaded_at ASC");
    $stmtA->execute($ids);
    foreach ($stmtA->fetchAll() as $a) {
        $rid = (int)$a['report_id'];
        if (!isset($attachmentsByReport[$rid])) $attachmentsByReport[$rid] = [];
        if (count($attachmentsByReport[$rid]) < 3) { // show first 3
            $attachmentsByReport[$rid][] = $a;
        }
    }
}

// Fetch all active authorities for email form (directory list)
$authorities = [];
$stmtU = $pdo->prepare("SELECT ua.id, ua.name, ua.email, ua.position, ua.university_id, u.uni_name FROM university_authorities ua LEFT JOIN university u ON ua.university_id = u.uni_id WHERE ua.is_active = 1 ORDER BY u.uni_name ASC, ua.name ASC");
$stmtU->execute();
$authorities = $stmtU->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Authority Dashboard</title>
    <link rel="icon" href="../../assets/images/favicon.ico">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
      :root {
        --primary: #2563eb;
        --primary-dark: #1d4ed8;
        --secondary: #64748b;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --bg-primary: #ffffff;
        --bg-secondary: #f8fafc;
        --bg-accent: #f1f5f9;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
        --text-light: #94a3b8;
        --border: #e2e8f0;
        --border-light: #f1f5f9;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
      }

      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        background: var(--bg-secondary);
        color: var(--text-primary);
        line-height: 1.6;
      }

      .password-popup {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        backdrop-filter: blur(4px);
      }

      .password-popup .popup-content {
        background: white;
        padding: 40px;
        border-radius: 16px;
        max-width: 450px;
        width: 90%;
        text-align: center;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border);
      }

      .password-popup h3 {
        margin-bottom: 16px;
        color: var(--danger);
        font-size: 20px;
        font-weight: 600;
      }

      .password-popup p {
        margin-bottom: 24px;
        color: var(--text-secondary);
      }

      .password-popup input {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid var(--border);
        border-radius: 8px;
        margin-bottom: 16px;
        font-size: 16px;
        transition: border-color 0.2s ease;
      }

      .password-popup input:focus {
        outline: none;
        border-color: var(--primary);
      }

      .password-popup .btn {
        margin: 8px 0;
        padding: 12px 24px;
        font-weight: 500;
      }

      .password-popup .error-message {
        color: var(--danger);
        margin-bottom: 16px;
        font-size: 14px;
        background: #fef2f2;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #fecaca;
      }

      .password-popup .success-message {
        color: var(--success);
        margin-bottom: 16px;
        font-size: 14px;
        background: #f0fdf4;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #bbf7d0;
      }
      
      /* Dashboard Layout */
      .dashboard-container {
        display: flex;
        min-height: 100vh;
      }
      
      .sidebar {
        width: 280px;
        background: var(--bg-primary);
        border-right: 1px solid var(--border);
        padding: 0;
        position: fixed;
        height: 100vh;
        overflow-y: auto;
        box-shadow: var(--shadow);
      }
      
      .main-content {
        flex: 1;
        margin-left: 280px;
        background: var(--bg-secondary);
        min-height: 100vh;
      }
      
      .sidebar-header {
        padding: 32px 24px;
        border-bottom: 1px solid var(--border);
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
      }
      
      .sidebar-header h3 {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 4px;
      }

      .sidebar-header p {
        font-size: 14px;
        opacity: 0.9;
        margin-bottom: 8px;
      }
      
      .university-info {
        font-size: 13px;
        opacity: 0.8;
        background: rgba(255,255,255,0.1);
        padding: 8px 12px;
        border-radius: 6px;
        margin-top: 12px;
      }
      
      .sidebar-nav {
        padding: 24px 16px;
      }
      
      .sidebar-nav a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        color: var(--text-primary);
        text-decoration: none;
        border-radius: 10px;
        margin-bottom: 4px;
        transition: all 0.2s ease;
        font-weight: 500;
      }
      
      .sidebar-nav a:hover {
        background: var(--bg-accent);
        color: var(--primary);
        transform: translateX(4px);
      }
      
      .sidebar-nav a.active {
        background: var(--primary);
        color: white;
        box-shadow: var(--shadow);
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
        padding: 24px 32px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: var(--shadow-sm);
        position: sticky;
        top: 0;
        z-index: 100;
      }
      
      .topbar-left strong {
        color: var(--text-primary);
        font-size: 20px;
        font-weight: 600;
      }

      .topbar-left .breadcrumb {
        color: var(--text-secondary);
        font-size: 14px;
        margin-top: 4px;
      }
      
      .topbar-right {
        color: var(--text-secondary);
        font-size: 14px;
      }
      
      .reports-section {
        padding: 32px;
      }

      .reports-header {
        margin-bottom: 32px;
      }
      
      .reports-header h2 {
        color: var(--text-primary);
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
      }
      
      .reports-header .info {
        color: var(--text-secondary);
        font-size: 16px;
      }

      .filters-card {
        background: var(--bg-primary);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 32px;
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
      }

      .filters {
        display: flex;
        gap: 16px;
        align-items: end;
        flex-wrap: wrap;
      }

      .filter-group {
        flex: 1;
        min-width: 200px;
      }

      .filter-group label {
        display: block;
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 8px;
        font-size: 14px;
      }
      
      .filter-input { 
        width: 100%; 
        padding: 12px 16px; 
        border: 2px solid var(--border); 
        border-radius: 8px; 
        font-size: 14px; 
        transition: border-color 0.2s ease;
        background: white;
      }

      .filter-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
      }

      .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        white-space: nowrap;
      }

      .btn.primary {
        background: var(--primary);
        color: white;
      }

      .btn.primary:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
        box-shadow: var(--shadow);
      }

      .btn.secondary {
        background: var(--bg-secondary);
        color: var(--text-primary);
        border: 1px solid var(--border);
      }

      .btn.secondary:hover {
        background: var(--bg-accent);
      }

      /* Card Grid Layout */
      .reports-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
        gap: 24px;
        margin-top: 24px;
      }

      .report-card {
        background: var(--bg-primary);
        border-radius: 12px;
        padding: 24px;
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
      }

      .report-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
      }

      .report-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
      }

      .report-card h3 {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
        flex: 1;
      }

      .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-left: 12px;
      }

      .status-badge.s-submitted {
        background: #dbeafe;
        color: #1d4ed8;
      }

      .status-badge.s-review {
        background: #fef3c7;
        color: #d97706;
      }

      .status-badge.s-action {
        background: #fde68a;
        color: #92400e;
      }

      .status-badge.s-resolved {
        background: #d1fae5;
        color: #065f46;
      }

      .status-badge.s-rejected {
        background: #fee2e2;
        color: #991b1b;
      }

      .report-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 20px;
        padding: 16px;
        background: var(--bg-secondary);
        border-radius: 8px;
      }

      .meta-item {
        display: flex;
        flex-direction: column;
      }

      .meta-label {
        font-size: 12px;
        font-weight: 500;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
      }

      .meta-value {
        font-size: 14px;
        color: var(--text-primary);
        font-weight: 500;
      }

      .report-files {
        margin-bottom: 20px;
      }

      .files-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
      }

      .file-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        background: var(--bg-accent);
        color: var(--text-primary);
        text-decoration: none;
        border-radius: 6px;
        font-size: 13px;
        transition: all 0.2s ease;
        border: 1px solid var(--border-light);
      }

      .file-link:hover {
        background: var(--primary);
        color: white;
      }

      .report-actions {
        display: flex;
        gap: 12px;
        margin-bottom: 16px;
      }

      .email-section {
        border-top: 1px solid var(--border);
        padding-top: 20px;
      }

      .email-toggle {
        background: none;
        border: none;
        color: var(--primary);
        font-weight: 500;
        cursor: pointer;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 0;
        transition: color 0.2s ease;
      }

      .email-toggle:hover {
        color: var(--primary-dark);
      }

      .email-form {
        margin-top: 16px;
        padding: 20px;
        background: var(--bg-secondary);
        border-radius: 8px;
        border: 1px solid var(--border);
      }

      .email-form label {
        display: block;
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 6px;
        font-size: 14px;
      }

      .email-form select,
      .email-form input,
      .email-form textarea {
        width: 100%;
        padding: 12px 14px;
        border: 2px solid var(--border);
        border-radius: 6px;
        font-size: 14px;
        margin-bottom: 16px;
        transition: border-color 0.2s ease;
        background: white;
      }

      .email-form select:focus,
      .email-form input:focus,
      .email-form textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
      }

      .email-form select[multiple] {
        min-height: 100px;
      }

      .email-form textarea {
        resize: vertical;
        min-height: 100px;
      }

      .no-reports {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        color: var(--text-secondary);
      }

      .no-reports h3 {
        font-size: 18px;
        margin-bottom: 8px;
        color: var(--text-primary);
      }

      /* Responsive */
      @media (max-width: 1200px) {
        .reports-grid {
          grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        }
      }

      @media (max-width: 768px) {
        .sidebar {
          transform: translateX(-100%);
          transition: transform 0.3s ease;
        }

        .main-content {
          margin-left: 0;
        }

        .reports-grid {
          grid-template-columns: 1fr;
        }

        .filters {
          flex-direction: column;
        }

        .filter-group {
          min-width: 100%;
        }

        .reports-section {
          padding: 20px;
        }

        .topbar {
          padding: 16px 20px;
        }

        .report-meta {
          grid-template-columns: 1fr;
        }
      }

      /* Enhanced hover effects */
      .report-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--primary-dark));
        opacity: 0;
        transition: opacity 0.2s ease;
      }

      .report-card:hover::before {
        opacity: 1;
      }

      /* Loading states */
      .loading {
        opacity: 0.6;
        pointer-events: none;
      }
    </style>
</head>
<body>
    <?php if (isset($ua['is_password_changed']) && (int)$ua['is_password_changed'] === 0): ?>
    <div class="password-popup" id="passwordPopup">
      <div class="popup-content">
        <h3>🔒 Password Update Required</h3>
        <p>For security reasons, please update your password before continuing.</p>
        
        <?php if ($password_error): ?>
          <div class="error-message"><?= htmlspecialchars($password_error) ?></div>
        <?php endif; ?>
        
        <?php if ($password_updated): ?>
          <div class="success-message">✅ Password updated successfully! The popup will close automatically.</div>
          <script>
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
                    <div class="university-info">All Universities Access</div>
                <?php endif; ?>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="active">
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
                <div class="topbar-left">
                    <strong>Authority Dashboard</strong>
                    <div class="breadcrumb">
                        <?php if (!empty($ua['university_id']) && (int)$ua['university_id'] !== 27 && !empty($ua['university_name'])): ?>
                            <?= htmlspecialchars($ua['university_name']) ?>
                        <?php else: ?>
                            All Universities
                        <?php endif; ?>
                    </div>
                </div>
                <div class="topbar-right">
                    Welcome back, <strong><?= htmlspecialchars($ua['name']) ?></strong>
                </div>
            </div>
            
            <div class="reports-section">
                <?php if (isset($_GET['mail']) && $_GET['mail'] == '1'): ?>
                    <div class="ok">Emails sent successfully.</div>
                <?php elseif (isset($_GET['mail']) && $_GET['mail'] == '0'): ?>
                    <div class="error">Failed to send emails. <?= !empty($_GET['err']) ? htmlspecialchars($_GET['err']) : '' ?></div>
                <?php endif; ?>
                <div class="reports-header">
                    <h2>Incident Reports</h2>
                    <div class="info">
                        <?php if (!empty($ua['university_id']) && (int)$ua['university_id'] !== 27): ?>
                            Showing reports for <?= htmlspecialchars($ua['university_name'] ?? 'your university') ?>
                        <?php else: ?>
                            Showing all reports from all universities
                        <?php endif; ?>
                        • <strong><?= count($reports) ?></strong> reports found
                    </div>
                </div>
                
                <div class="filters-card">
                    <form class="filters" method="get">
                        <div class="filter-group">
                            <label for="status">Status Filter</label>
                            <select name="status" id="status" class='filter-input'>
                                <option value="">All Statuses</option>
                                <?php
                                $statuses = ['Submitted','Under Review','Action Initiated','Resolved','Rejected'];
                                foreach ($statuses as $s) {
                                    $sel = $status === $s ? 'selected' : '';
                                    echo "<option $sel value=\"" . htmlspecialchars($s) . "\">" . htmlspecialchars($s) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="search">Search Reports</label>
                            <input id="search" class='filter-input' type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search by code, type, or department...">
                        </div>
                        <div class="filter-group" style="flex: 0;">
                            <button class="btn primary" type="submit">Filter</button>
                        </div>
                    </form>
                </div>

                <?php if (!$reports): ?>
                    <div class="no-reports">
                        <h3>No Reports Found</h3>
                        <p>Try adjusting your filters or search criteria.</p>
                    </div>
                <?php else: ?>
                    <div class="reports-grid">
                        <?php foreach ($reports as $r): ?>
                            <?php
                                $class = 's-submitted';
                                if ($r['status'] === 'Under Review') $class = 's-review';
                                elseif ($r['status'] === 'Action Initiated') $class = 's-action';
                                elseif ($r['status'] === 'Resolved') $class = 's-resolved';
                                elseif ($r['status'] === 'Rejected') $class = 's-rejected';
                                $rid = (int)$r['id'];
                                $atts = $attachmentsByReport[$rid] ?? [];
                            ?>
                            <div class="report-card">
                                <div class="report-card-header">
                                    <h3><?= htmlspecialchars($r['report_code']) ?></h3>
                                    <span class="status-badge <?= $class ?>"><?= htmlspecialchars($r['status']) ?></span>
                                </div>

                                <div class="report-meta">
                                    <div class="meta-item">
                                        <span class="meta-label">Incident Type</span>
                                        <span class="meta-value"><?= htmlspecialchars(ucwords(str_replace('_',' ', $r['incident_type']))) ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Department</span>
                                        <span class="meta-value"><?= htmlspecialchars($r['department']) ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Date Reported</span>
                                        <span class="meta-value"><?= date('M j, Y', strtotime($r['created_at'])) ?></span>
                                    </div>
                                    <?php if (!empty($ua['university_id']) && !empty($r['uni_name'])): ?>
                                    <div class="meta-item">
                                        <span class="meta-label">University</span>
                                        <span class="meta-value"><?= htmlspecialchars($r['uni_name']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <?php if ($atts): ?>
                                    <div class="report-files">
                                        <div class="meta-label" style="margin-bottom: 8px;">📎 Attachments</div>
                                        <div class="files-list">
                                            <?php foreach ($atts as $a): ?>
                                                <a class="file-link" href="file.php?id=<?= (int)$a['id'] ?>" target="_blank" rel="noopener">
                                                    <?= htmlspecialchars($a['original_name']) ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="report-actions">
                                    <a class="btn primary" href="report.php?id=<?= $rid ?>">View Details</a>
                                </div>

                                <?php if ($authorities): ?>
                                <div class="email-section">
                                    <details>
                                        <summary class="email-toggle">Email Authorities</summary>
                                        <div class="email-form">
                                            <form method="post" action="send_email.php">
                                                <input type="hidden" name="report_id" value="<?= $rid ?>">
                                                
                                                <label>Recipients</label>
                                                <div style="max-height:200px; overflow:auto; border:1px solid var(--border); border-radius:8px; padding:10px; background:white;">
                                                    <?php foreach ($authorities as $au): if (strcasecmp($au['email'], $ua['email'])===0) continue; ?>
                                                        <label style="display:block; margin-bottom:8px; font-weight:400;">
                                                            <input type="checkbox" name="recipients[]" value="<?= htmlspecialchars($au['email']) ?>">
                                                            <?= htmlspecialchars($au['name'] . ' - ' . $au['position']) ?> (<?= htmlspecialchars($au['email']) ?>)
                                                            <?php if (!empty($au['uni_name'])): ?>
                                                                <span class="file-link" style="padding:2px 6px;"><?= htmlspecialchars($au['uni_name']) ?></span>
                                                            <?php endif; ?>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                                
                                                <label>Subject</label>
                                                <input type="text" name="subject" value="Action required: Report <?= htmlspecialchars($r['report_code']) ?>" required>
                                                
                                                <label>Message</label>
                                                <textarea name="message" rows="4" placeholder="Write your message..." required></textarea>
                                                
                                                <button class="btn primary" type="submit">Send Email</button>
                                            </form>
                                        </div>
                                    </details>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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

        // Add smooth interactions
        const reportCards = document.querySelectorAll('.report-card');
        reportCards.forEach(card => {
          card.addEventListener('mouseenter', function() {
            this.style.borderColor = 'var(--primary)';
          });
          
          card.addEventListener('mouseleave', function() {
            this.style.borderColor = 'var(--border)';
          });
        });

        // Enhanced form interactions
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
          input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
          });
          
          input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
          });
        });
      });
    </script>
</body>
</html>