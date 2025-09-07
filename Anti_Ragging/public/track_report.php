<?php 
session_start();
require_once __DIR__ . '/../config/db.php';

$report = null;
$error_message = null;
$search_performed = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['tracking_code'])) {
    $tracking_code = strtoupper(trim($_POST['tracking_code']));
    $search_performed = true;
    
    try {
        // Get report details with university name and attachment count (schema-aligned)
        $stmt = $pdo->prepare("
            SELECT r.*, u.uni_name,
                   (SELECT COUNT(a.id) FROM attachments a WHERE a.report_id = r.id) AS file_count
            FROM reports r 
            LEFT JOIN university u ON r.university_id = u.uni_id
            WHERE r.report_code = ?
            LIMIT 1
        ");
        $stmt->execute([$tracking_code]);
        $report = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$report) {
            $error_message = "No report found with tracking code: " . htmlspecialchars($tracking_code);
        }
        
    } catch (PDOException $e) {
        error_log("Database error in track_report.php: " . $e->getMessage());
        $error_message = "An error occurred while searching. Please try again later.";
    }
}

include_once __DIR__ . '/../includes/header.php'; 
?>


<div class="tracking-container">
  <div class="page-header">
    <a href="index.php" class="back-button">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
        <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      Back to Home
    </a>
    <h1>Track Your Report</h1>
    <p>Enter your tracking code to view the current status of your report</p>
  </div>

  <div class="tracking-card">
    <form method="POST" class="search-form">
      <div class="form-group">
        <label for="tracking_code">Tracking Code</label>
        <input 
          type="text" 
          id="tracking_code" 
          name="tracking_code" 
          placeholder="Enter tracking code (e.g., AR12345678)" 
          maxlength="20"
          value="<?= isset($_POST['tracking_code']) ? htmlspecialchars($_POST['tracking_code']) : '' ?>"
          required
        >
      </div>
      <button type="submit" class="search-button">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
          <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Search
      </button>
    </form>

    <?php if ($error_message): ?>
      <div class="error-message">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
          <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2"/>
          <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2"/>
        </svg>
        <?= $error_message ?>
      </div>
    <?php elseif ($search_performed && !$report): ?>
      <div class="no-results">
        <div class="no-results-icon">🔍</div>
        <p>No report found with the provided tracking code.</p>
        <p style="font-size: 14px; margin-top: 10px;">Please check your tracking code and try again.</p>
      </div>
    <?php endif; ?>

    <?php if ($report): ?>
      <div class="report-found">
        <div class="report-header">
          <div class="report-title">
            <h2>Report #<?= htmlspecialchars($report['report_code']) ?></h2>
            <span class="status-badge status-<?= htmlspecialchars(strtolower(str_replace(' ', '_', $report['status']))) ?>">
              <?= htmlspecialchars($report['status']) ?>
            </span>
          </div>
          <div class="report-meta">
            Submitted on <?= date('F j, Y \a\t g:i A', strtotime($report['created_at'])) ?>
            <?php if (!empty($report['updated_at']) && $report['updated_at'] !== $report['created_at']): ?>
              • Last updated <?= date('F j, Y \a\t g:i A', strtotime($report['updated_at'])) ?>
            <?php endif; ?>
          </div>
        </div>

        <div class="report-details">
          <div class="detail-group">
            <h3>Report Information</h3>
            <div class="detail-item">
              <span class="detail-label">University</span>
              <span class="detail-value"><?= htmlspecialchars($report['uni_name']) ?></span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Department</span>
              <span class="detail-value"><?= htmlspecialchars($report['department']) ?></span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Incident Type</span>
              <span class="detail-value"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $report['incident_type']))) ?></span>
            </div>
          </div>

          <div class="detail-group">
            <h3>Incident Details</h3>
            <?php if (!empty($report['location'])): ?>
            <div class="detail-item">
              <span class="detail-label">Location</span>
              <span class="detail-value"><?= htmlspecialchars($report['location']) ?></span>
            </div>
            <?php endif; ?>
            <div class="detail-item">
              <span class="detail-label">Date & Time</span>
              <span class="detail-value"><?= date('F j, Y \a\t g:i A', strtotime($report['incident_datetime'])) ?></span>
            </div>
            <?php if (!empty($report['file_count'])): ?>
            <div class="detail-item">
              <span class="detail-label">Attachments</span>
              <span class="detail-value"><?= (int)$report['file_count'] ?> file(s) uploaded</span>
            </div>
            <?php endif; ?>
          </div>

          <div class="incident-description">
            <h3>Description</h3>
            <div class="description-text"><?= nl2br(htmlspecialchars($report['details'])) ?></div>
          </div>
        </div>

        <!-- Progress Section -->
        <div class="progress-section">
          <h3 class="progress-title">Report Progress</h3>
          <div class="progress-steps">
            <?php
            $statuses = ['Submitted','Under Review','Action Initiated','Resolved'];
            $currentStatus = $report['status'];
            $currentIndex = array_search($currentStatus, $statuses);
            if ($currentIndex === false) { $currentIndex = 0; }
            $progressPercentage = (($currentIndex + 1) / count($statuses)) * 100;
            ?>
            
            <div class="progress-line">
              <div class="progress-line-fill" style="width: <?= $progressPercentage ?>%;"></div>
            </div>
            
            <?php foreach ($statuses as $idx => $label): ?>
              <?php $stepClass = $idx < $currentIndex ? 'step-completed' : ($idx === $currentIndex ? 'step-current' : 'step-pending'); ?>
              <div class="progress-step">
                <div class="step-circle <?= $stepClass ?>">
                  <?php if ($stepClass === 'step-completed'): ?>
                    ✓
                  <?php else: ?>
                    <?= $idx + 1 ?>
                  <?php endif; ?>
                </div>
                <div class="step-label"><?= htmlspecialchars($label) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <?php if (!empty($report['admin_notes'])): ?>
          <div class="admin-notes">
            <h4>Updates from Review Team</h4>
            <p><?= nl2br(htmlspecialchars($report['admin_notes'])) ?></p>
          </div>
        <?php endif; ?>

        <?php if ($report['status'] === 'Resolved' && !empty($report['resolution_details'])): ?>
          <div class="admin-notes" style="background: #d4edda; border-color: #c3e6cb;">
            <h4 style="color: #155724;">Resolution Details</h4>
            <p style="color: #155724;">&nbsp;<?= nl2br(htmlspecialchars($report['resolution_details'])) ?></p>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Auto-uppercase tracking code input
  const trackingInput = document.getElementById('tracking_code');
  trackingInput.addEventListener('input', function() {
    this.value = this.value.toUpperCase();
  });
  
  // Focus on tracking input
  trackingInput.focus();
  
  // Add some animation to the progress line
  const progressFill = document.querySelector('.progress-line-fill');
  if (progressFill) {
    const targetWidth = progressFill.style.width;
    progressFill.style.width = '0%';
    setTimeout(() => {
      progressFill.style.width = targetWidth;
    }, 500);
  }
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>