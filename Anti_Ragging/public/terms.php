<?php include_once __DIR__ . '/../includes/header.php'; session_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Terms of Service | Anti‑Ragging Reporting System</title>
  <link rel="icon" href="../assets/images/favicon.ico">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    .terms-container {
      max-width: 1000px;
      margin: 40px auto;
      padding: 0 20px;
    }
    
    .terms-card {
      background: var(--bg-primary);
      border: 1px solid var(--border);
      border-radius: 16px;
      box-shadow: var(--shadow-lg);
      overflow: hidden;
    }
    
    .terms-header {
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      color: white;
      padding: 40px;
      text-align: center;
      position: relative;
    }
    
    .terms-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
      opacity: 0.3;
    }
    
    .terms-eyebrow {
      display: inline-block;
      background: rgba(255, 255, 255, 0.2);
      color: white;
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 14px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 16px;
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .terms-title {
      font-size: 36px;
      font-weight: 700;
      margin: 0 0 16px 0;
      line-height: 1.2;
    }
    
    .terms-subtitle {
      font-size: 18px;
      opacity: 0.9;
      margin: 0;
      line-height: 1.5;
    }
    
    .terms-toc {
      background: var(--bg-secondary);
      border-bottom: 1px solid var(--border);
      padding: 24px 40px;
    }
    
    .toc-title {
      font-size: 16px;
      font-weight: 600;
      color: var(--text-secondary);
      margin: 0 0 16px 0;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .toc-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 8px;
      margin: 0;
      padding: 0;
      list-style: none;
    }
    
    .toc-item a {
      display: block;
      padding: 8px 0;
      color: var(--text-secondary);
      text-decoration: none;
      transition: all 0.2s ease;
      border-bottom: 1px dashed transparent;
    }
    
    .toc-item a:hover {
      color: var(--primary);
      border-bottom-color: var(--primary);
    }
    
    .terms-content {
      padding: 40px;
    }
    
    .terms-section {
      margin-bottom: 32px;
      padding-bottom: 24px;
      border-bottom: 1px solid var(--border-light);
    }
    
    .terms-section:last-child {
      border-bottom: none;
      margin-bottom: 0;
    }
    
    .terms-section h3 {
      font-size: 24px;
      font-weight: 600;
      color: var(--text-primary);
      margin: 0 0 16px 0;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    .terms-section h3::before {
      content: '';
      width: 4px;
      height: 24px;
      background: var(--primary);
      border-radius: 2px;
    }
    
    .terms-section p {
      color: var(--text-secondary);
      line-height: 1.7;
      margin: 0 0 16px 0;
    }
    
    .terms-section ul {
      color: var(--text-secondary);
      line-height: 1.7;
      margin: 16px 0;
      padding-left: 24px;
    }
    
    .terms-section li {
      margin-bottom: 8px;
    }
    
    .terms-section strong {
      color: var(--text-primary);
    }
    
    .terms-footer {
      background: var(--bg-secondary);
      border-top: 1px solid var(--border);
      padding: 24px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 16px;
    }
    
    .terms-copyright {
      color: var(--text-light);
      font-size: 14px;
    }
    
    .terms-type {
      color: var(--text-secondary);
      font-size: 14px;
      font-weight: 600;
    }
    
    @media (max-width: 768px) {
      .terms-container {
        margin: 20px auto;
        padding: 0 16px;
      }
      
      .terms-header {
        padding: 30px 20px;
      }
      
      .terms-title {
        font-size: 28px;
      }
      
      .terms-subtitle {
        font-size: 16px;
      }
      
      .terms-toc {
        padding: 20px;
      }
      
      .terms-content {
        padding: 20px;
      }
      
      .toc-list {
        grid-template-columns: 1fr;
      }
      
      .terms-footer {
        padding: 20px;
        flex-direction: column;
        text-align: center;
      }
    }
  </style>
</head>
<body>
  <div class="terms-container">
    <article class="terms-card">
      <header class="terms-header">
        <span class="terms-eyebrow">Terms of Service</span>
        <h1 class="terms-title">Anti‑Ragging Reporting System</h1>
        <p class="terms-subtitle">The rules and responsibilities governing the use of our platform.</p>
      </header>

      <nav class="terms-toc">
        <h2 class="toc-title">Contents</h2>
        <ul class="toc-list">
          <li class="toc-item"><a href="#acceptance">1. Acceptance of Terms</a></li>
          <li class="toc-item"><a href="#user-resp">2. User Responsibilities</a></li>
          <li class="toc-item"><a href="#anon">3. Anonymous Reporting</a></li>
          <li class="toc-item"><a href="#false">4. False or Malicious Reports</a></li>
          <li class="toc-item"><a href="#authority">5. Authority Responsibilities</a></li>
          <li class="toc-item"><a href="#access">6. Access & Availability</a></li>
          <li class="toc-item"><a href="#limitation">7. Limitation of Liability</a></li>
          <li class="toc-item"><a href="#changes">8. Changes to the Terms</a></li>
          <li class="toc-item"><a href="#contact">9. Contact Information</a></li>
        </ul>
      </nav>

      <div class="terms-content">
        <section id="acceptance" class="terms-section">
          <h3>1. Acceptance of Terms</h3>
          <p>By using the Anti‑Ragging Reporting System ("System"), you agree to comply with these Terms of Service and applicable laws and regulations. If you do not agree, please do not use the System.</p>
        </section>

        <section id="user-resp" class="terms-section">
          <h3>2. User Responsibilities</h3>
          <ul>
            <li>Submit reports honestly and in good faith.</li>
            <li>Refrain from disclosing personal details of third parties unnecessarily.</li>
            <li>Use the tracking ID responsibly to check the status of your report.</li>
          </ul>
        </section>

        <section id="anon" class="terms-section">
          <h3>3. Anonymous Reporting</h3>
          <p>Students have the right to report incidents anonymously. However, providing contact information may assist in investigation and resolution.</p>
        </section>

        <section id="false" class="terms-section">
          <h3>4. False or Malicious Reports</h3>
          <p>Submitting false, misleading, or malicious reports is strictly prohibited and may result in disciplinary or legal action.</p>
        </section>

        <section id="authority" class="terms-section">
          <h3>5. Authority Responsibilities</h3>
          <ul>
            <li>University authorities and UGC administrators will review reports in a timely and confidential manner.</li>
            <li>Authorities will escalate cases based on seriousness and ensure fair procedures.</li>
            <li>Authorities must safeguard the information provided by students and respect anonymity when chosen.</li>
          </ul>
        </section>

        <section id="access" class="terms-section">
          <h3>6. Access & Availability</h3>
          <p>While we strive to maintain continuous access, the System may be unavailable due to maintenance, updates, or technical issues. We are not liable for any inconvenience caused.</p>
        </section>

        <section id="limitation" class="terms-section">
          <h3>7. Limitation of Liability</h3>
          <p>The System is a reporting tool. We are not responsible for the actions or omissions of authorities or third parties handling reported cases. Our liability is limited to maintaining and safeguarding the platform.</p>
        </section>

        <section id="changes" class="terms-section">
          <h3>8. Changes to the Terms</h3>
          <p>We may revise these Terms of Service from time to time. Continued use of the System after updates means you accept the new terms.</p>
        </section>

        <section id="contact" class="terms-section">
          <h3>9. Contact Information</h3>
          <address>
            <strong>Anti‑Ragging Cell / UGC Admin</strong><br />
            Email: <a href="mailto:antiragging@example.edu">antiragging@example.edu</a><br />
            Phone: <a href="tel:+94112223344">+94 11 222 3344</a><br />
            Address: [Insert University / UGC Address]
          </address>
        </section>
      </div>

      <div class="terms-footer">
        <div class="terms-copyright">© <span id="year"></span> Anti‑Ragging Reporting System</div>
        <div class="terms-type">Terms of Service</div>
      </div>
    </article>
  </div>

  <script>
    document.getElementById('year').textContent = new Date().getFullYear();
  </script>
</body>
</html>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>