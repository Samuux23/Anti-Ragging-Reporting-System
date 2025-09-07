<?php include_once __DIR__ . '/../includes/header.php'; ?>
<div class="page-header">
  <a href="index.php" class="back-link">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
      <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    Back to Home
  </a>
  <h1>Track Report Status</h1>
  <p>Enter your report code to check the current status and investigation progress.</p>
</div>

<div class="form-container">
  <div class="form-card">
    <h2>Report Code Lookup</h2>
    <p class="form-intro">Enter the unique code you received when submitting your report.</p>
    
    <form class="form" action="track_status.php" method="post">
      <div class="form-group">
        <label for="report_code">Report Code</label>
        <div class="search-container">
          <input type="text" name="report_code" id="report_code" 
                 value="<?= htmlspecialchars($_GET['code'] ?? '') ?>"
                 placeholder="e.g., AR12345678" maxlength="12" required />
          <button class="btn primary search-btn" type="submit">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
              <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
              <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Search
          </button>
        </div>
      </div>
    </form>

    <?php if (!empty($_GET['notfound'])): ?>
      <div class="alert">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
          <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="2"/>
          <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="2"/>
        </svg>
        Report not found. Please check the code and try again.
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
