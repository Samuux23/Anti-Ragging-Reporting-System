<script type="text/javascript">
        var gk_isXlsx = false;
        var gk_xlsxFileLookup = {};
        var gk_fileData = {};
        function filledCell(cell) {
          return cell !== '' && cell != null;
        }
        function loadFileData(filename) {
        if (gk_isXlsx && gk_xlsxFileLookup[filename]) {
            try {
                var workbook = XLSX.read(gk_fileData[filename], { type: 'base64' });
                var firstSheetName = workbook.SheetNames[0];
                var worksheet = workbook.Sheets[firstSheetName];

                // Convert sheet to JSON to filter blank rows
                var jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1, blankrows: false, defval: '' });
                // Filter out blank rows (rows where all cells are empty, null, or undefined)
                var filteredData = jsonData.filter(row => row.some(filledCell));

                // Heuristic to find the header row by ignoring rows with fewer filled cells than the next row
                var headerRowIndex = filteredData.findIndex((row, index) =>
                  row.filter(filledCell).length >= filteredData[index + 1]?.filter(filledCell).length
                );
                // Fallback
                if (headerRowIndex === -1 || headerRowIndex > 25) {
                  headerRowIndex = 0;
                }

                // Convert filtered JSON back to CSV
                var csv = XLSX.utils.aoa_to_sheet(filteredData.slice(headerRowIndex)); // Create a new sheet from filtered array of arrays
                csv = XLSX.utils.sheet_to_csv(csv, { header: 1 });
                return csv;
            } catch (e) {
                console.error(e);
                return "";
            }
        }
        return gk_fileData[filename] || "";
        }
        </script><?php 
include_once __DIR__ . '/../includes/header.php'; 
session_start(); 
if (empty($_SESSION['csrf'])) { 
    $_SESSION['csrf'] = bin2hex(random_bytes(32)); 
}
?>

<style>
  .upload-area {
    transition: all 0.3s ease;
    cursor: pointer;
  }
  
  .upload-area:hover {
    border-color: #3B82F6;
    background-color: #F8FAFC;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
  }
  
  .file-type-indicators span {
    padding: 4px 8px;
    border-radius: 12px;
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.2);
    transition: all 0.2s ease;
  }
  
  .file-type-indicators span:hover {
    transform: scale(1.05);
    background: rgba(59, 130, 246, 0.2);
  }
  
  .file-info {
    background: #F0F9FF;
    border: 1px solid #BAE6FD;
    border-radius: 8px;
    padding: 12px;
    margin-top: 15px;
    font-size: 14px;
  }
  
  .file-size-info {
    background: #FEF3C7;
    border: 1px solid #FCD34D;
    border-radius: 6px;
    padding: 8px 12px;
    margin-top: 10px;
    font-size: 12px;
    color: #92400E;
  }
</style>

<div class="page-header">
  <a href="index.php" class="back-link">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
      <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    Back to Home
  </a>
  <h1>Anonymous Report Submission</h1>
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
        <label for="details">Detailed Description *</label>
        <textarea name="details" id="details" rows="6" maxlength="1000" placeholder="Please describe the incident in detail..." required></textarea>
        <div class="char-counter">
          <span id="char-count">0</span>/1000 characters
        </div>
      </div>

      <div class="form-group">
        <label>Attachments (Optional)</label>
        <div class="file-upload">
          <div class="upload-area" onclick="document.getElementById('file-input').click()">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="#6b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <polyline points="7,10 12,15 17,10" stroke="#6b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <line x1="12" y1="15" x2="12" y2="3" stroke="#6b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <p>Upload any supporting evidence (photos, documents, audio, video)</p>
            <p style="font-size: 12px; color: #666;">Click here to select files</p>
            <p style="font-size: 11px; color: #888; margin-top: 5px;">
              Supported formats: JPG, PNG, PDF, MP3, MP4, AVI, MOV, WAV, DOC, DOCX, TXT, RTF
            </p>
            <div class="file-type-indicators" style="display: flex; gap: 15px; margin-top: 10px; justify-content: center; flex-wrap: wrap;">
              <span style="font-size: 10px; color: #3B82F6;">📷 Images</span>
              <span style="font-size: 10px; color: #10B981;">📄 Documents</span>
              <span style="font-size: 10px; color: #F59E0B;">🎵 Audio</span>
              <span style="font-size: 10px; color: #EF4444;">🎥 Video</span>
            </div>
            <input type="file" name="evidence[]" id="file-input" multiple accept=".jpg,.jpeg,.png,.webp,.pdf,.mp3,.mp4,.avi,.mov,.wav,.doc,.docx,.txt,.rtf" />
          </div>
        </div>
      </div>

      <div class="privacy-assurance">
        <h3>Privacy & Anonymity Assurance</h3>
        <ul>
          <li>Your identity will remain completely anonymous</li>
          <li>No personal information is collected or stored</li>
          <li>Reports are handled by authorized personnel only</li>
          <li>You will receive a unique tracking code for status updates</li>
        </ul>
      </div>

      <button id="submitReportBtn" class="btn primary submit-btn" type="submit">Submit Anonymous Report</button>
    </form>
  </div>
</div>

<!-- Verification Modal -->
<div id="verification-modal" class="ar-modal" hidden aria-hidden="true" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.5); justify-content: center; align-items: flex-start; padding: 50px 10px 10px;">
  <div class="ar-modal__content" style="background: #fff; padding: 25px 30px; border-radius: 8px; max-width: 400px; width: 100%; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
    <h3>Verify & Confirm</h3>
    <p>Please complete the human verification and enter your university email. The university email will be used to verify your identity and confirm your report. It is not used for any other purpose and not publicly shared.<a href="privacy_policy.php#info-we-collect" target="_blank"> learn more</a></p>

    <div class="form-group">
      <label for="uni_email">University Email *</label>
      <input type="email" id="uni_email" placeholder="your@university.edu" required>
    </div>

    <!-- hCaptcha widget container -->
    <div id="hcaptcha-container" style="margin: 15px 0;"></div>
    
    <button type="button" id="verify-submit" class="btn primary" style="width: 100%; margin-top: 15px;">Verify & Submit</button>
    <p class="terms-notice">By submitting this report, you agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy_policy.php" target="_blank">Privacy Policy</a>.</p>
    <p id="verification-error" class="error"></p>
  </div>
</div>

<!-- hCaptcha -->
<script src="https://js.hcaptcha.com/1/api.js" async defer></script>

<script>
let hcaptchaWidgetId = null;

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

// Initialize hCaptcha when modal opens
function initializeHCaptcha() {
  if (window.hcaptcha && !hcaptchaWidgetId) {
    hcaptchaWidgetId = hcaptcha.render('hcaptcha-container', {
      sitekey: '230a1744-f122-40ca-8e14-5c98d10a07a6',
      theme: 'light',
      size: 'normal'
    });
  }
}

// Reset hCaptcha
function resetHCaptcha() {
  if (window.hcaptcha && hcaptchaWidgetId !== null) {
    hcaptcha.reset(hcaptchaWidgetId);
  }
}

document.addEventListener("DOMContentLoaded", function () {
  console.log("DOM loaded, initializing...");
  
  const form = document.querySelector("form.form");
  const modal = document.getElementById("verification-modal");
  const verifyBtn = document.getElementById("verify-submit");
  const errorMsg = document.getElementById("verification-error");
  const uniHidden = document.getElementById("uni_id_hidden");
  const uniSelect = document.getElementById("uniList");
  const verifiedEmailHidden = document.getElementById("verified_email_hidden");
  
  // Debug: Check if all elements are found
  console.log("Form found:", !!form);
  console.log("Modal found:", !!modal);
  console.log("Verify button found:", !!verifyBtn);
  console.log("Error message element found:", !!errorMsg);
  console.log("University hidden field found:", !!uniHidden);
  console.log("University select found:", !!uniSelect);
  console.log("Verified email hidden field found:", !!verifiedEmailHidden);

  // Modal control functions
  function closeModal() { 
    console.log("Closing modal...");
    if (modal) {
      modal.classList.remove("is-open"); 
      modal.setAttribute("hidden", ""); 
      modal.setAttribute("aria-hidden", "true");
      modal.style.display = "none";
      resetHCaptcha();
      console.log("Modal closed");
    } else {
      console.log("Modal element not found for closing");
    }
  }
  
  function openModal()  { 
    console.log("Opening modal...");
    if (modal) {
      modal.removeAttribute("hidden"); 
      modal.classList.add("is-open"); 
      modal.setAttribute("aria-hidden", "false");
      modal.style.display = "flex";
      console.log("Modal classes:", modal.className);
      console.log("Modal hidden attribute:", modal.getAttribute("hidden"));
      console.log("Modal aria-hidden:", modal.getAttribute("aria-hidden"));
      console.log("Modal display style:", modal.style.display);
      
      // Initialize hCaptcha when modal opens
      setTimeout(() => {
        initializeHCaptcha();
        document.getElementById("uni_email").focus();
      }, 100);
    } else {
      console.log("Modal element not found for opening");
    }
  }

  // Ensure modal is closed on page load
  closeModal();

  // Keep hidden uni_id in sync with the selected University option value
  if (uniSelect) {
    uniSelect.addEventListener("change", e => { 
      uniHidden.value = e.target.value || "";
      if (errorMsg) {
        errorMsg.textContent = "";
      }
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

  // File upload handling
  const fileInput = document.getElementById("file-input");
  const uploadArea = fileInput.closest('.upload-area');
  
  // Function to clear file display
  function clearFileDisplay() {
    const fileInfo = uploadArea.querySelector('.file-info');
    const sizeInfo = uploadArea.querySelector('.file-size-info');
    if (fileInfo) fileInfo.remove();
    if (sizeInfo) sizeInfo.remove();
  }
  
  // Global function to clear files
  window.clearFiles = function() {
    fileInput.value = '';
    clearFileDisplay();
  };
  
  if (fileInput && uploadArea) {
    fileInput.addEventListener('change', function() {
      const files = this.files;
      if (files.length > 0) {
        let fileInfo = [];
        let totalSize = 0;
        const maxFileSize = 50 * 1024 * 1024; // 50MB limit
        const supportedTypes = {
          'image': ['.jpg', '.jpeg', '.png', '.webp'],
          'document': ['.pdf', '.doc', '.docx', '.txt', '.rtf'],
          'audio': ['.mp3', '.wav'],
          'video': ['.mp4', '.avi', '.mov']
        };
        
        for (let i = 0; i < files.length; i++) {
          const file = files[i];
          const fileExt = '.' + file.name.split('.').pop().toLowerCase();
          let fileType = 'other';
          
          // Determine file type
          if (supportedTypes.image.includes(fileExt)) fileType = 'image';
          else if (supportedTypes.document.includes(fileExt)) fileType = 'document';
          else if (supportedTypes.audio.includes(fileExt)) fileType = 'audio';
          else if (supportedTypes.video.includes(fileExt)) fileType = 'video';
          
          // Check if file type is supported
          if (fileType === 'other') {
            alert(`File "${file.name}" has an unsupported format. Please use supported file types.`);
            continue;
          }
          
          // Check file size
          if (file.size > maxFileSize) {
            alert(`File "${file.name}" is too large. Maximum size is 50MB.`);
            continue;
          }
          
          totalSize += file.size;
          fileInfo.push(`${file.name} (${fileType})`);
        }
        
        if (fileInfo.length > 0) {
          // Remove previous file info if exists
          const prevFileInfo = uploadArea.querySelector('.file-info');
          if (prevFileInfo) {
            prevFileInfo.remove();
          }
          
          // Remove previous size info if exists
          const prevSizeInfo = uploadArea.querySelector('.file-size-info');
          if (prevSizeInfo) {
            prevSizeInfo.remove();
          }
          
          // Create file info display
          const fileInfoDiv = document.createElement('div');
          fileInfoDiv.className = 'file-info';
          fileInfoDiv.innerHTML = `<strong>Selected Files:</strong><br>${fileInfo.join('<br>')}`;
          uploadArea.appendChild(fileInfoDiv);
          
          // Create size info display
          const sizeInfo = document.createElement('div');
          sizeInfo.className = 'file-size-info';
          sizeInfo.innerHTML = `
            <strong>Total Size:</strong> ${(totalSize / (1024 * 1024)).toFixed(2)} MB
            <button type="button" onclick="clearFiles()" style="float: right; background: #EF4444; color: white; border: none; padding: 2px 8px; border-radius: 4px; font-size: 10px; cursor: pointer;">Clear All</button>
          `;
          uploadArea.appendChild(sizeInfo);
        }
      }
    });
  }

  // Intercept form submit
  form.addEventListener("submit", function (e) {
    e.preventDefault();
    console.log("Form submit intercepted");
    
    // Don't show modal until required fields are valid
    if (!form.reportValidity()) {
      console.log("Form validation failed");
      return;
    }
    
    // Check if university is selected
    if (!uniHidden.value) {
      console.log("No university selected");
      alert("Please select your university first.");
      return;
    }
    
    console.log("University selected:", uniHidden.value);
    
    // Clear any previous error messages
    errorMsg.textContent = "";
    document.getElementById("uni_email").value = "";
    
    // Show the modal
    console.log("About to open modal");
    openModal();
  });

  // Verify button inside modal
  verifyBtn.addEventListener("click", async function () {
    const email = (document.getElementById("uni_email").value || "").trim();
    const uni_id = parseInt(uniHidden.value || "0", 10);

    // Clear previous errors
    errorMsg.textContent = "";
    errorMsg.className = "error";

    // Validation
    if (!email) {
      errorMsg.textContent = "Please enter your university email.";
      return;
    }
    
    if (!uni_id) {
      errorMsg.textContent = "Please select your university first.";
      return;
    }
    
    // Basic email format validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      errorMsg.textContent = "Please enter a valid email address.";
      return;
    }
    
    // Check if hCaptcha is loaded
    if (!window.hcaptcha) {
      errorMsg.textContent = "Security verification failed to load. Please refresh the page.";
      return;
    }

    // Get hCaptcha response
    let hcaptchaResponse = null;
    try {
      hcaptchaResponse = hcaptcha.getResponse(hcaptchaWidgetId);
    } catch (error) {
      console.error("hCaptcha error:", error);
    }

    if (!hcaptchaResponse) {
      errorMsg.textContent = "Please complete the security verification.";
      return;
    }

    try {
      // Show loading state
      const originalText = verifyBtn.textContent;
      verifyBtn.textContent = "Verifying...";
      verifyBtn.disabled = true;

      // Prepare form data
      const formData = new URLSearchParams({
        email: email,
        uni_id: uni_id.toString(),
        hcaptcha: hcaptchaResponse
      });

      // Send verification request
      console.log("Sending verification request to:", "verify_email_captcha.php");
      console.log("Form data:", formData.toString());
      
      const response = await fetch("./verify_email_captcha.php", {
        method: "POST",
        headers: { 
          "Content-Type": "application/x-www-form-urlencoded",
          "X-Requested-With": "XMLHttpRequest"
        },
        body: formData
      });
      
      console.log("Response status:", response.status);
      console.log("Response ok:", response.ok);

      // Check if response is ok
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      // Parse JSON response
      const data = await response.json();

      if (data.valid === true) {
        // Success
        errorMsg.className = "success-message";
        errorMsg.textContent = "Verification successful! Submitting report...";
        
        // Add verified email to the form
        verifiedEmailHidden.value = email;
        
        // Close modal and submit form
        setTimeout(() => {
          closeModal();
          form.submit();
        }, 1000);
        
      } else {
        // Show error message from server
        errorMsg.className = "error";
        errorMsg.textContent = data.message || "Verification failed. Please try again.";
        resetHCaptcha(); // Reset hCaptcha on error
      }
      
    } catch (error) {
      console.error("Verification error:", error);
      errorMsg.className = "error";
      errorMsg.textContent = "Network error occurred. Please check your connection and try again.";
      resetHCaptcha(); // Reset hCaptcha on error
    } finally {
      // Reset button state
      verifyBtn.textContent = "Verify & Submit";
      verifyBtn.disabled = false;
    }
  });

  // Close modal when clicking outside of it
  modal.addEventListener("click", function(e) {
    if (e.target === modal) {
      closeModal();
    }
  });

  // Close modal with Escape key
  document.addEventListener("keydown", function(e) {
    if (e.key === "Escape" && modal.classList.contains("is-open")) {
      closeModal();
    }
  });
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>