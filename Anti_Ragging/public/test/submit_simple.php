<?php 
include_once __DIR__ . '/../includes/header.php'; 
session_start(); 
if (empty($_SESSION['csrf'])) { 
    $_SESSION['csrf'] = bin2hex(random_bytes(32)); 
}
?>

<style>
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.char-counter {
  text-align: right;
  font-size: 12px;
  color: #666;
  margin-top: 5px;
}

.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
  text-decoration: none;
  display: inline-block;
  text-align: center;
  transition: background-color 0.3s;
}

.btn.primary {
  background: #007bff;
  color: white;
}

.btn.primary:hover {
  background: #0056b3;
}

.submit-btn {
  width: 100%;
  font-size: 16px;
  font-weight: 500;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #007bff;
  text-decoration: none;
  margin-bottom: 20px;
}

.back-link:hover {
  text-decoration: underline;
}

.page-header {
  margin-bottom: 30px;
}

.page-header h1 {
  margin: 10px 0;
  color: #333;
}

.page-header p {
  color: #666;
  margin: 5px 0;
}

.form-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 0 20px;
}

.form-card {
  background: white;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-intro {
  color: #666;
  margin-bottom: 30px;
}
</style>

<div class="page-header">
  <a href="index.php" class="back-link">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
      <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    Back to Home
  </a>
  <h1>Anonymous Report Submission (Simplified)</h1>
  <p>Submit an anonymous report about ragging incidents. Your identity will remain completely confidential.</p>
</div>

<div class="form-container">
  <div class="form-card">
    <?php if (isset($_SESSION['error_message'])): ?>
      <div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
        <strong>Error:</strong> <?= htmlspecialchars($_SESSION['error_message']) ?>
      </div>
      <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success_message'])): ?>
      <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
        <strong>Success:</strong> <?= htmlspecialchars($_SESSION['success_message']) ?>
        <?php if (isset($_SESSION['report_code'])): ?>
          <br>Your report code is: <strong><?= htmlspecialchars($_SESSION['report_code']) ?></strong>
        <?php endif; ?>
      </div>
      <?php unset($_SESSION['success_message'], $_SESSION['report_code']); ?>
    <?php endif; ?>
    
    <h2>Incident Report Form</h2>
    <p class="form-intro">Please provide as much detail as possible to help us investigate the incident effectively.</p>
    
    <form class="form" action="process_report.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
      <input type="hidden" name="uni_id" id="uni_id_hidden">
      <input type="hidden" name="verified_email" id="verified_email_hidden">

      <div class="form-grid">
        <div class="form-group">
          <label for="incident_type">Incident Type *</label>
          <select name="incident_type" id="incident_type" required>
            <option value="">Select incident type</option>
            <option value="bullying">Bullying</option>
            <option value="verbal_harassment">Verbal Harassment</option>
            <option value="physical_harassment">Physical Harassment</option>
            <option value="cyber_ragging">Cyber Ragging</option>
            <option value="other">Other</option>
          </select>
        </div>

        <div class="form-group">
          <label for="department">Department/Faculty *</label>
          <select name="department" id="department" required>
            <option value="">Select department</option>
            <option value="engineering">Engineering</option>
            <option value="science">Science</option>
            <option value="management">Management</option>
            <option value="arts">Arts</option>
            <option value="medicine">Medicine</option>
            <option value="technology">Technology</option>
            <option value="other">Other</option>
          </select>
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label for="typeSelect">University Type *</label>
          <?php
              // Include database connection
              require_once __DIR__ . '/../config/db.php';

              // Check if $pdo is defined
              if (!isset($pdo)) {
                  die("Database connection failed.");
              }

              // Fetch distinct university types
              try {
                  $stmt = $pdo->query("SELECT DISTINCT uni_type FROM university ORDER BY uni_type ASC");
                  $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
              } catch (PDOException $e) {
                  die("Error fetching university types: " . $e->getMessage());
              }
          ?>
          <select id="typeSelect" onchange="loadUniversities(this.value)" required>
              <option value="">-- Select Type --</option>
              <?php if (!empty($types)): ?>
                  <?php foreach ($types as $row): ?>
                      <option value="<?= htmlspecialchars($row['uni_type']) ?>">
                          <?= htmlspecialchars(ucfirst($row['uni_type'])) ?>
                      </option>
                  <?php endforeach; ?>
              <?php endif; ?>
          </select>
      </div>

        <div class="form-group">
          <label for="uniList">University *</label>
          <select name="university" id="uniList" required>
            <option value="">-- Select University --</option>
          </select>
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label for="location">Location of Incident</label>
          <input type="text" name="location" id="location" placeholder="e.g., Hostel Room 205, Main Campus Canteen" />
        </div>

        <div class="form-group">
          <label for="incident_datetime">Date & Time of Incident *</label>
          <input type="datetime-local" name="incident_datetime" id="incident_datetime" required />
        </div>
      </div>

      <div class="form-group">
        <label for="email">Your Email (for tracking) *</label>
        <input type="email" name="email" id="email" placeholder="your@email.com" required />
      </div>

      <div class="form-group">
        <label for="details">Detailed Description *</label>
        <textarea name="details" id="details" rows="6" maxlength="1000" placeholder="Please describe the incident in detail..." required></textarea>
        <div class="char-counter">
          <span id="char-count">0</span>/1000 characters
        </div>
      </div>

      <button id="submitReportBtn" class="btn primary submit-btn" type="submit">Submit Anonymous Report</button>
    </form>
  </div>
</div>

<script>
function loadUniversities(type) {
  const uniList = document.getElementById("uniList");
  const uniHidden = document.getElementById("uni_id_hidden");
  
  if (type === "") {
    uniList.innerHTML = "<option value=''>-- Select University --</option>";
    uniHidden.value = "";
    return;
  }
  
  const xhr = new XMLHttpRequest();
  xhr.open("GET", "get_universities.php?type=" + encodeURIComponent(type), true);
  xhr.onload = function () {
    if (this.status === 200) {
      uniList.innerHTML = this.responseText;
      uniHidden.value = "";
    } else {
      uniList.innerHTML = "<option value=''>Error loading universities</option>";
    }
  };
  xhr.onerror = function() {
    uniList.innerHTML = "<option value=''>Error loading universities</option>";
  };
  xhr.send();
}

document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("form.form");
  const uniHidden = document.getElementById("uni_id_hidden");
  const uniSelect = document.getElementById("uniList");
  const verifiedEmailHidden = document.getElementById("verified_email_hidden");
  const emailInput = document.getElementById("email");

  // Keep hidden uni_id in sync with the selected University option value
  if (uniSelect) {
    uniSelect.addEventListener("change", e => { 
      uniHidden.value = e.target.value || "";
    });
  }

  // Character counter for textarea
  const detailsTextarea = document.getElementById("details");
  const charCount = document.getElementById("char-count");
  
  if (detailsTextarea && charCount) {
    detailsTextarea.addEventListener("input", function() {
      charCount.textContent = this.value.length;
      if (this.value.length > 900) {
        charCount.style.color = "orange";
      } else if (this.value.length > 980) {
        charCount.style.color = "red";
      } else {
        charCount.style.color = "#666";
      }
    });
  }

  // Set max date to current date/time
  const datetimeInput = document.getElementById("incident_datetime");
  if (datetimeInput) {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    datetimeInput.max = now.toISOString().slice(0, 16);
  }

  // Intercept form submit
  form.addEventListener("submit", function (e) {
    e.preventDefault();
    
    // Don't submit until required fields are valid
    if (!form.reportValidity()) {
      return;
    }
    
    // Check if university is selected
    if (!uniHidden.value) {
      alert("Please select your university first.");
      return;
    }
    
    // Set the verified email from the email input
    verifiedEmailHidden.value = emailInput.value;
    
    // Submit the form
    form.submit();
  });
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?> 