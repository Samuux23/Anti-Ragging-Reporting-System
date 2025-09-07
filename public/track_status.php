<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Check for auto-load parameter from successful submission
$auto_load_code = $_GET['auto_load'] ?? '';
$code = '';
$report = null;
$timeline = [];
$views = [];
$status_history = [];
$show_success_message = false;

// Handle auto-load or manual search
if ($auto_load_code) {
    $code = strtoupper(trim($auto_load_code));
    $show_success_message = true;
} else {
    $code = strtoupper(trim($_POST['report_code'] ?? $_GET['code'] ?? ''));
}

if ($code) {
    // Get report details with university information
    $stmt = $pdo->prepare("
        SELECT r.*, u.uni_name 
        FROM reports r 
        LEFT JOIN university u ON r.university_id = u.uni_id 
        WHERE r.report_code = ?
    ");
    $stmt->execute([$code]);
    $report = $stmt->fetch();

    if ($report) {
        // Get process timeline (limit to recent 10 entries)
        $stmt = $pdo->prepare("
            SELECT * FROM process_timeline 
            WHERE report_id = ? 
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$report['id']]);
        $timeline = $stmt->fetchAll();

        // Get report views (limit to recent 15 entries)
        $stmt = $pdo->prepare("
            SELECT * FROM report_views 
            WHERE report_id = ? 
            ORDER BY viewed_at DESC
            LIMIT 15
        ");
        $stmt->execute([$report['id']]);
        $views = $stmt->fetchAll();

        // Get status history (limit to recent 8 entries)
        $stmt = $pdo->prepare("
            SELECT * FROM status_history 
            WHERE report_id = ? 
            ORDER BY changed_at DESC
            LIMIT 8
        ");
        $stmt->execute([$report['id']]);
        $status_history = $stmt->fetchAll();

        // Count total entries for "show more" buttons
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM process_timeline WHERE report_id = ?");
        $stmt->execute([$report['id']]);
        $total_timeline = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM report_views WHERE report_id = ?");
        $stmt->execute([$report['id']]);
        $total_views = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM status_history WHERE report_id = ?");
        $stmt->execute([$report['id']]);
        $total_status_history = $stmt->fetchColumn();

        // Record this view
        $stmt = $pdo->prepare("
            INSERT INTO report_views (report_id, viewer_type, viewer_name, viewed_at, action_taken) 
            VALUES (?, 'anonymous', 'Reporter', NOW(), 'Viewed status')
        ");
        $stmt->execute([$report['id']]);
    }
}

include_once __DIR__ . '/../includes/header.php';
?>

<style>
.success-banner {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: 20px;
    text-align: center;
    margin-bottom: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

.success-banner h2 {
    margin: 0 0 10px 0;
    font-size: 24px;
    font-weight: 600;
}

.success-banner p {
    margin: 0;
    opacity: 0.9;
    font-size: 16px;
}

.search-section {
    max-width: 600px;
    margin: 40px auto;
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    text-align: center;
}

.search-section h1 {
    color: #374151;
    margin-bottom: 20px;
    font-size: 28px;
    font-weight: 300;
}

.search-form {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.search-input {
    flex: 1;
    padding: 15px 20px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 16px;
    font-family: 'Courier New', monospace;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.search-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.search-btn {
    padding: 15px 30px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
}

.search-btn:hover {
    background: #2563eb;
}

.status-card {
    max-width: 800px;
    margin: 20px auto;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}

.status-header {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    padding: 30px;
    text-align: center;
}

.status-header h1 {
    margin: 0 0 20px 0;
    font-size: 28px;
    font-weight: 300;
}

.code-box {
    background: rgba(255,255,255,0.2);
    color:rgba(245, 245, 245, 0.8);
    padding: 15px 25px;
    border-radius: 8px;
    font-family: 'Courier New', monospace;
    font-size: 18px;
    font-weight: bold;
    margin: 20px 0;
    display: inline-block;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.2);
    color: rgba(245, 245, 245, 0.8);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
}

.dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #4ade80;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.status-content {
    padding: 30px;
}

.section {
    margin-bottom: 30px;
}

.section h3 {
    color: #374151;
    font-size: 18px;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.entry-count {
    font-size: 12px;
    color: #6b7280;
    font-weight: normal;
    background: #f3f4f6;
    padding: 4px 8px;
    border-radius: 12px;
}

.process-timeline {
    position: relative;
    max-height: 600px;
    overflow-y: auto;
}

.process-step {
    display: flex;
    align-items: flex-start;
    margin-bottom: 25px;
    position: relative;
}

.process-step:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 20px;
    top: 40px;
    bottom: -25px;
    width: 2px;
    background: #e5e7eb;
}

.step-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
    flex-shrink: 0;
    font-size: 16px;
    font-weight: bold;
    color: white;
}

.step-icon.completed { background: #10b981; }
.step-icon.in_progress { background: #f59e0b; }
.step-icon.pending { background: #d1d5db; }

.step-content {
    flex: 1;
}

.step-title {
    font-weight: 600;
    color: #374151;
    margin-bottom: 5px;
}

.step-description {
    color: #6b7280;
    font-size: 14px;
    margin-bottom: 8px;
}

.step-meta {
    font-size: 12px;
    color: #9ca3af;
}

.step-meta strong {
    color: #6b7280;
}

.views-section {
    background: #f9fafb;
    border-radius: 8px;
    padding: 20px;
    max-height: 400px;
    overflow-y: auto;
}

.view-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #e5e7eb;
}

.view-item:last-child {
    border-bottom: none;
}

.viewer-info {
    flex: 1;
}

.viewer-name {
    font-weight: 600;
    color: #374151;
}

.viewer-role {
    font-size: 12px;
    color: #6b7280;
    text-transform: capitalize;
}

.viewer-action {
    font-size: 11px;
    color: #059669;
    font-style: italic;
}

.view-time {
    font-size: 12px;
    color: #9ca3af;
    text-align: right;
}

.status-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.detail {
    background: #f9fafb;
    padding: 15px;
    border-radius: 8px;
}

.detail .label {
    font-size: 12px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}

.detail .value {
    font-weight: 600;
    color: #374151;
}

.actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    padding: 20px;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn.primary {
    background: #3b82f6;
    color: white;
}

.btn.primary:hover {
    background: #2563eb;
}

.btn.secondary {
    background: #6b7280;
    color: white;
}

.btn.secondary:hover {
    background: #4b5563;
}

.btn.outline {
    background: white;
    color: #6b7280;
    border: 1px solid #d1d5db;
}

.btn.outline:hover {
    background: #f9fafb;
}

.no-views {
    text-align: center;
    color: #6b7280;
    font-style: italic;
    padding: 20px;
}

.error-message {
    background: #fee2e2;
    color: #dc2626;
    padding: 15px;
    border-radius: 8px;
    margin: 20px auto;
    max-width: 600px;
    text-align: center;
}

.status-history-section {
    background: #f9fafb;
    border-radius: 8px;
    padding: 20px;
    max-height: 300px;
    overflow-y: auto;
}

.status-history-item {
    padding: 15px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.status-history-item:last-child {
    border-bottom: none;
}

.status-change {
    flex: 1;
}

.status-change .old-status {
    color: #dc2626;
    text-decoration: line-through;
}

.status-change .new-status {
    color: #059669;
    font-weight: 600;
}

.status-change .arrow {
    color: #6b7280;
    margin: 0 8px;
}
</style>

<?php if ($show_success_message): ?>
<div class="success-banner">
    <h2>🎉 Report Submitted Successfully!</h2>
    <p>Your report has been received and assigned tracking code: <strong><?= htmlspecialchars($code) ?></strong></p>
</div>
<?php endif; ?>

<?php if (!$code): ?>
<!-- Search Form -->
<div class="search-section">
    <h1>Track Your Report</h1>
    <p>Enter your report tracking code to view the current status and progress.</p>
    
    <form method="POST" action="report_status.php" class="search-form">
        <input type="text" 
               name="report_code" 
               class="search-input" 
               placeholder="Enter report code (e.g., AR12345678)"
               pattern="AR[0-9]{8}"
               title="Report code should be in format AR12345678"
               maxlength="10"
               required>
        <button type="submit" class="search-btn">Track Report</button>
    </form>
</div>

<?php elseif (!$report): ?>
<!-- Report Not Found -->
<div class="error-message">
    <h3>Report Not Found</h3>
    <p>No report found with code: <strong><?= htmlspecialchars($code) ?></strong></p>
    <p>Please check the code and try again.</p>
    <a href="report_status.php" class="btn primary" style="margin-top: 15px;">Search Again</a>
</div>

<?php else: ?>
<!-- Report Status Display -->
<div class="status-card">
    <div class="status-header">
        <h1>Report Status Tracking</h1>
        <div class="code-box" id="status-tracking-code" data-code="<?= htmlspecialchars($report['report_code']) ?>">
            <?= htmlspecialchars($report['report_code']) ?>
        </div>
        <div class="status-badge">
            <span class="dot"></span>
            <?= htmlspecialchars($report['status']) ?>
        </div>
    </div>

    <div class="status-content">
        <!-- Basic Details -->
        <div class="section">
            <h3>Report Information</h3>
            <div class="status-details">
                <div class="detail">
                    <div class="label">University</div>
                    <div class="value"><?= htmlspecialchars($report['uni_name'] ?? 'Unknown') ?></div>
                </div>
                <div class="detail">
                    <div class="label">Incident Type</div>
                    <div class="value"><?= htmlspecialchars($report['incident_type']) ?></div>
                </div>
                <div class="detail">
                    <div class="label">Department</div>
                    <div class="value"><?= htmlspecialchars($report['department']) ?></div>
                </div>
                <div class="detail">
                    <div class="label">Location</div>
                    <div class="value"><?= htmlspecialchars($report['location'] ?? 'Not specified') ?></div>
                </div>
                <div class="detail">
                    <div class="label">Incident Date</div>
                    <div class="value"><?= htmlspecialchars(date('M j, Y g:i A', strtotime($report['incident_datetime']))) ?></div>
                </div>
                <div class="detail">
                    <div class="label">Submitted</div>
                    <div class="value"><?= htmlspecialchars(date('M j, Y g:i A', strtotime($report['created_at']))) ?></div>
                </div>
            </div>
        </div>

        <!-- Process Timeline -->
        <div class="section">
            <h3>
                Process Timeline
                <span class="entry-count"><?= count($timeline) ?> of <?= $total_timeline ?> entries</span>
            </h3>
            <div class="process-timeline">
                <?php foreach (array_slice($timeline, 0, 5) as $step): ?>
                    <div class="process-step">
                        <div class="step-icon <?= $step['status'] ?>">
                            <?php
                                switch($step['status']) {
                                    case 'completed': echo '✓'; break;
                                    case 'in_progress': echo '⟳'; break;
                                    default: echo '○'; break;
                                }
                            ?>
                        </div>
                        <div class="step-content">
                            <div class="step-title"><?= htmlspecialchars($step['step_name']) ?></div>
                            <div class="step-description"><?= htmlspecialchars($step['step_description']) ?></div>
                            <div class="step-meta">
                                <strong>Assigned to:</strong> <?= htmlspecialchars($step['assigned_to']) ?>
                                <?php if ($step['started_at']): ?>
                                    <br><strong>Started:</strong> <?= htmlspecialchars(date('M j, Y g:i A', strtotime($step['started_at']))) ?>
                                <?php endif; ?>
                                <?php if ($step['completed_at']): ?>
                                    <br><strong>Completed:</strong> <?= htmlspecialchars(date('M j, Y g:i A', strtotime($step['completed_at']))) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Status History -->
        <?php if (!empty($status_history)): ?>
        <div class="section">
            <h3>
                Status History
                <span class="entry-count"><?= count($status_history) ?> of <?= $total_status_history ?> changes</span>
            </h3>
            <div class="status-history-section">
                <?php foreach ($status_history as $history): ?>
                    <div class="status-history-item">
                        <div class="status-change">
                            <?php if ($history['old_status']): ?>
                                <span class="old-status"><?= htmlspecialchars($history['old_status']) ?></span>
                                <span class="arrow">→</span>
                            <?php endif; ?>
                            <span class="new-status"><?= htmlspecialchars($history['new_status']) ?></span>
                            <?php if ($history['notes']): ?>
                                <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                                    <?= htmlspecialchars($history['notes']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="view-time">
                            <?= htmlspecialchars(date('M j, Y g:i A', strtotime($history['changed_at']))) ?>
                            <br><small><?= htmlspecialchars($history['changed_by']) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Report Views -->
        <div class="section">
            <h3>
                Report Access History
                <span class="entry-count"><?= count($views) ?> of <?= $total_views ?> views</span>
            </h3>
            <div class="views-section">
                <?php if (empty($views)): ?>
                    <div class="no-views">No views recorded yet</div>
                <?php else: ?>
                    <?php foreach (array_slice($views, 0, 8) as $view): ?>
                        <div class="view-item">
                            <div class="viewer-info">
                                <div class="viewer-name"><?= htmlspecialchars($view['viewer_name'] ?? 'Unknown') ?></div>
                                <div class="viewer-role"><?= htmlspecialchars($view['viewer_type']) ?></div>
                                <?php if ($view['action_taken']): ?>
                                    <div class="viewer-action">Action: <?= htmlspecialchars($view['action_taken']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="view-time">
                                <?= htmlspecialchars(date('M j, Y g:i A', strtotime($view['viewed_at']))) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="actions">
        <a class="btn primary" href="report_status.php">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Search Another Code
        </a>
        <button class="btn secondary" id="copy-status-code" type="button">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                <path d="M8 4v12a2 2 0 002 2h8a2 2 0 002-2V7.242a2 2 0 00-.602-1.43L16.083 2.57A2 2 0 0014.685 2H10a2 2 0 00-2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M16 18v2a2 2 0 01-2 2H6a2 2 0 01-2-2V9a2 2 0 012-2h2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Copy Code
        </button>
        <a class="btn outline" href="submit.php">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Submit New Report
        </a>
    </div>
</div>
<?php endif; ?>

<script>
// Auto-submit form if code is provided via URL parameter
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const autoCode = urlParams.get('auto_load');
    
    if (autoCode && !document.querySelector('.status-card')) {
        // If we have auto_load parameter but no status card displayed, 
        // it means the report wasn't found, so show search form
        const input = document.querySelector('input[name="report_code"]');
        if (input) {
            input.value = autoCode;
        }
    }
});

// Copy code functionality
document.getElementById('copy-status-code')?.addEventListener('click', function() {
    const code = document.getElementById('status-tracking-code').getAttribute('data-code');
    navigator.clipboard.writeText(code).then(function() {
        this.textContent = 'Copied!';
        setTimeout(() => {
            this.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <path d="M8 4v12a2 2 0 002 2h8a2 2 0 002-2V7.242a2 2 0 00-.602-1.43L16.083 2.57A2 2 0 0014.685 2H10a2 2 0 00-2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 18v2a2 2 0 01-2 2H6a2 2 0 01-2-2V9a2 2 0 012-2h2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Copy Code
            `;
        }, 2000);
    }.bind(this));
});

// Format report code input
document.querySelector('input[name="report_code"]')?.addEventListener('input', function(e) {
    let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    
    // Auto-format as AR########
    if (value.length > 0 && !value.startsWith('AR')) {
        if (value.length <= 8) {
            value = 'AR' + value;
        }
    }
    
    // Limit to 10 characters (AR + 8 digits)
    if (value.length > 10) {
        value = value.substring(0, 10);
    }
    
    e.target.value = value;
});
</script>

<?php 
// Clear session messages after display
unset($_SESSION['success_message'], $_SESSION['error_message'], $_SESSION['report_code'], $_SESSION['university_name'], $_SESSION['uploaded_files']);

include_once __DIR__ . '/../includes/footer.php'; 
?>