<?php
require_once __DIR__ . '/../config/db.php';

$report_id = (int)($_GET['id'] ?? 0);

if (!$report_id) {
    die("Invalid report ID");
}

try {
    // Get report details
    $stmt = $pdo->prepare("
        SELECT r.*, u.uni_name 
        FROM reports r 
        LEFT JOIN university u ON r.university_id = u.uni_id 
        WHERE r.id = ?
    ");
    $stmt->execute([$report_id]);
    $report = $stmt->fetch();
    
    if (!$report) {
        die("Report not found");
    }
    
    // Get status history
    $stmt = $pdo->prepare("
        SELECT * FROM status_history 
        WHERE report_id = ? 
        ORDER BY changed_at ASC
    ");
    $stmt->execute([$report_id]);
    $history = $stmt->fetchAll();
    
    // Get attachments
    $stmt = $pdo->prepare("
        SELECT * FROM attachments 
        WHERE report_id = ? 
        ORDER BY uploaded_at ASC
    ");
    $stmt->execute([$report_id]);
    $attachments = $stmt->fetchAll();
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Report Details - <?= htmlspecialchars($report['report_code']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .section { margin-bottom: 30px; }
        .section h3 { border-bottom: 2px solid #007bff; padding-bottom: 5px; }
        .field { margin-bottom: 15px; }
        .field label { font-weight: bold; display: inline-block; width: 150px; }
        .field span { color: #666; }
        .status-history { background: #f8f9fa; padding: 15px; border-radius: 5px; }
        .status-item { margin-bottom: 10px; padding: 8px; background: white; border-radius: 3px; }
        .attachments { background: #f8f9fa; padding: 15px; border-radius: 5px; }
        .attachment-item { margin-bottom: 10px; padding: 8px; background: white; border-radius: 3px; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #007bff; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_view_reports.php" class="back-link">← Back to Reports</a>
        
        <div class="header">
            <h1>Report Details</h1>
            <h2>Report Code: <?= htmlspecialchars($report['report_code']) ?></h2>
            <p>Status: <strong><?= htmlspecialchars($report['status']) ?></strong></p>
        </div>
        
        <div class="section">
            <h3>Basic Information</h3>
            <div class="field">
                <label>Report ID:</label>
                <span><?= htmlspecialchars($report['id']) ?></span>
            </div>
            <div class="field">
                <label>University:</label>
                <span><?= htmlspecialchars($report['uni_name'] ?? 'Unknown') ?></span>
            </div>
            <div class="field">
                <label>Incident Type:</label>
                <span><?= htmlspecialchars($report['incident_type']) ?></span>
            </div>
            <div class="field">
                <label>Department:</label>
                <span><?= htmlspecialchars($report['department']) ?></span>
            </div>
            <div class="field">
                <label>Location:</label>
                <span><?= htmlspecialchars($report['location'] ?? 'Not specified') ?></span>
            </div>
            <div class="field">
                <label>Incident Date/Time:</label>
                <span><?= htmlspecialchars($report['incident_datetime']) ?></span>
            </div>
            <div class="field">
                <label>Reporter Email:</label>
                <span><?= htmlspecialchars($report['reporter_email']) ?></span>
            </div>
            <div class="field">
                <label>Submitted:</label>
                <span><?= htmlspecialchars($report['created_at']) ?></span>
            </div>
        </div>
        
        <div class="section">
            <h3>Incident Details</h3>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; white-space: pre-wrap;">
                <?= htmlspecialchars($report['details']) ?>
            </div>
        </div>
        
        <?php if (!empty($history)): ?>
        <div class="section">
            <h3>Status History</h3>
            <div class="status-history">
                <?php foreach ($history as $item): ?>
                    <div class="status-item">
                        <strong><?= htmlspecialchars($item['new_status']) ?></strong>
                        <?php if ($item['old_status']): ?>
                            (changed from <?= htmlspecialchars($item['old_status']) ?>)
                        <?php endif; ?>
                        <br>
                        <small><?= htmlspecialchars($item['changed_at']) ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($attachments)): ?>
        <div class="section">
            <h3>Attachments (<?= count($attachments) ?>)</h3>
            <div class="attachments">
                <?php foreach ($attachments as $attachment): ?>
                    <div class="attachment-item">
                        <strong><?= htmlspecialchars($attachment['original_name']) ?></strong><br>
                        <small>
                            Size: <?= number_format($attachment['size_bytes'] / 1024, 2) ?> KB<br>
                            Type: <?= htmlspecialchars($attachment['mime_type']) ?><br>
                            Uploaded: <?= htmlspecialchars($attachment['uploaded_at']) ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html> 