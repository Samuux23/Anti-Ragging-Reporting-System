<?php include_once __DIR__ . '/../includes/header.php'; session_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Privacy Policy | Anti‑Ragging Reporting System</title>
  <link rel="icon" href="../assets/images/favicon.ico">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    .policy-container {
      max-width: 1000px;
      margin: 40px auto;
      padding: 0 20px;
    }
    
    .policy-card {
      background: var(--bg-primary);
      border: 1px solid var(--border);
      border-radius: 16px;
      box-shadow: var(--shadow-lg);
      overflow: hidden;
    }
    
    .policy-header {
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      color: white;
      padding: 40px;
      text-align: center;
      position: relative;
    }
    
    .policy-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
      opacity: 0.3;
    }
    
    .policy-eyebrow {
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
    
    .policy-title {
      font-size: 36px;
      font-weight: 700;
      margin: 0 0 16px 0;
      line-height: 1.2;
    }
    
    .policy-subtitle {
      font-size: 18px;
      opacity: 0.9;
      margin: 0;
      line-height: 1.5;
    }
    
    .policy-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
      justify-content: center;
      margin-top: 24px;
    }
    
    .meta-item {
      display: flex;
      align-items: center;
      gap: 8px;
      background: rgba(255, 255, 255, 0.1);
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 14px;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .meta-dot {
      width: 8px;
      height: 8px;
      background: var(--accent);
      border-radius: 50%;
      box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.3);
    }
    
    .policy-toc {
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
    
    .policy-content {
      padding: 40px;
    }
    
    .policy-section {
      margin-bottom: 32px;
      padding-bottom: 24px;
      border-bottom: 1px solid var(--border-light);
    }
    
    .policy-section:last-child {
      border-bottom: none;
      margin-bottom: 0;
    }
    
    .policy-section h3 {
      font-size: 24px;
      font-weight: 600;
      color: var(--text-primary);
      margin: 0 0 16px 0;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    .policy-section h3::before {
      content: '';
      width: 4px;
      height: 24px;
      background: var(--primary);
      border-radius: 2px;
    }
    
    .policy-section p {
      color: var(--text-secondary);
      line-height: 1.7;
      margin: 0 0 16px 0;
    }
    
    .policy-section ul {
      color: var(--text-secondary);
      line-height: 1.7;
      margin: 16px 0;
      padding-left: 24px;
    }
    
    .policy-section li {
      margin-bottom: 8px;
    }
    
    .policy-section strong {
      color: var(--text-primary);
    }
    
    .policy-details {
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 20px;
      background: var(--bg-secondary);
      margin: 20px 0;
    }
    
    .policy-details summary {
      cursor: pointer;
      font-weight: 600;
      color: var(--text-primary);
      margin-bottom: 16px;
      display: block;
    }
    
    .policy-details[open] summary {
      margin-bottom: 16px;
    }
    
    .policy-details p {
      margin: 0;
    }
    
    .policy-chip {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border: 1px solid currentColor;
    }
    
    .policy-chip.ok {
      color: var(--success);
      background: rgba(16, 185, 129, 0.1);
      border-color: rgba(16, 185, 129, 0.3);
    }
    
    .policy-chip.warn {
      color: var(--warning);
      background: rgba(245, 158, 11, 0.1);
      border-color: rgba(245, 158, 11, 0.3);
    }
    
    .policy-chip.danger {
      color: var(--error);
      background: rgba(239, 68, 68, 0.1);
      border-color: rgba(239, 68, 68, 0.3);
    }
    
    .policy-footer {
      background: var(--bg-secondary);
      border-top: 1px solid var(--border);
      padding: 24px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 16px;
    }
    
    .policy-actions {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }
    
    .policy-actions .btn {
      font-size: 14px;
      padding: 10px 20px;
    }
    
    .policy-copyright {
      color: var(--text-light);
      font-size: 14px;
    }
    
    .policy-back-to-top {
      position: fixed;
      right: 20px;
      bottom: 20px;
      z-index: 100;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
    }
    
    .policy-back-to-top.visible {
      opacity: 1;
      visibility: visible;
    }
    
    @media (max-width: 768px) {
      .policy-container {
        margin: 20px auto;
        padding: 0 16px;
      }
      
      .policy-header {
        padding: 30px 20px;
      }
      
      .policy-title {
        font-size: 28px;
      }
      
      .policy-subtitle {
        font-size: 16px;
      }
      
      .policy-toc {
        padding: 20px;
      }
      
      .policy-content {
        padding: 20px;
      }
      
      .toc-list {
        grid-template-columns: 1fr;
      }
      
      .policy-footer {
        padding: 20px;
        flex-direction: column;
        text-align: center;
      }
      
      .policy-actions {
        justify-content: center;
      }
    }
  </style>
</head>
<body>
  <div class="policy-container">
    <article class="policy-card">
      <header class="policy-header">
        <span class="policy-eyebrow">Privacy Policy</span>
        <h1 class="policy-title">Anti‑Ragging Reporting System</h1>
        <p class="policy-subtitle">How we collect, use, protect, and share data to keep students safe while enabling effective investigations.</p>
        <div class="policy-meta">
          <span class="meta-item">
            <span class="meta-dot"></span>
            Effective: <time datetime="2025-08-31">31 Aug 2025</time>
          </span>
          <span class="meta-item">
            Last updated: <time datetime="2025-08-31">31 Aug 2025</time>
          </span>
          <span class="policy-chip ok">Anonymous reporting</span>
          <span class="policy-chip warn">Severity‑based escalation</span>
        </div>
      </header>

      <nav class="policy-toc">
        <h2 class="toc-title">Contents</h2>
        <ul class="toc-list">
          <li class="toc-item"><a href="#purpose">1. Purpose of the System</a></li>
          <li class="toc-item"><a href="#info-we-collect">2. Information We Collect</a></li>
          <li class="toc-item"><a href="#use-of-info">3. Use of Information</a></li>
          <li class="toc-item"><a href="#sharing-access">4. Data Sharing & Access Control</a></li>
          <li class="toc-item"><a href="#student-rights">5. Student Rights</a></li>
          <li class="toc-item"><a href="#retention">6. Data Retention</a></li>
          <li class="toc-item"><a href="#security">7. Security Measures</a></li>
          <li class="toc-item"><a href="#legal">8. Legal Compliance</a></li>
          <li class="toc-item"><a href="#changes">9. Changes to this Policy</a></li>
          <li class="toc-item"><a href="#contact">10. Contact Information</a></li>
        </ul>
      </nav>

      <div class="policy-content">
        <section id="purpose" class="policy-section">
          <h3>1. Purpose of the System</h3>
          <p>The Anti‑Ragging Reporting System ("System") enables students to safely report ragging incidents, including the option to submit reports <strong>anonymously</strong>. University authorities and University Grants Commission (UGC) administrators receive direct access to these reports for analysis, investigation, and action. Each report is assigned a unique tracking ID so students can view the status of their report. Cases are escalated to relevant authorities based on the <strong>seriousness</strong> of the incident.</p>
        </section>

        <section id="info-we-collect" class="policy-section">
          <h3>2. Information We Collect</h3>
          <ul>
            <li><strong>Report Details</strong>: Description of the incident, date/time, location, involved parties, and any attachments (e.g., images, documents, audio).</li>
            <li><strong>Optional Personal Information</strong>: Name, email, phone or other identifiers you may choose to provide. <em>Not required to submit a report.</em></li>
            <li><strong>System‑Generated Data</strong>: A unique tracking ID and submission timestamps.</li>
            <li><strong>Technical Information</strong>: Device/browser details, IP address, and security logs for fraud prevention and platform integrity.</li>
          </ul>
        </section>

        <section id="use-of-info" class="policy-section">
          <h3>3. Use of Information</h3>
          <ul>
            <li><strong>Incident Management</strong>: Provide authorized university authorities and UGC admins the details necessary to assess, investigate, and resolve reports.</li>
            <li><strong>Communication</strong>: If you provide contact details, we may use them to request clarification or provide updates.</li>
            <li><strong>Escalation by Severity</strong>: Route and notify appropriate authorities based on risk, urgency, and policy.</li>
            <li><strong>Service Improvement</strong>: Use non‑identifiable analytics to understand trends and strengthen anti‑ragging measures.</li>
          </ul>
        </section>

        <section id="sharing-access" class="policy-section">
          <h3>4. Data Sharing & Access Control</h3>
          <p><strong>University Authorities</strong> and <strong>UGC Administrators</strong> have role‑based access to report data for investigation and action. Personal information (if provided) is kept confidential and disclosed only when legally required or with your explicit consent. Reports submitted anonymously are processed without attempting to identify the reporter.</p>
          <details class="policy-details">
            <summary>Who gets notified for severe cases?</summary>
            <p>For high‑severity incidents, notifications are sent to designated university officials (e.g., Anti‑Ragging Cell, Student Affairs, Security), UGC admins, and—where required by law—external authorities. Notification scope depends on predefined severity thresholds.</p>
          </details>
        </section>

        <section id="student-rights" class="policy-section">
          <h3>5. Student Rights</h3>
          <ul>
            <li><strong>Right to Anonymity</strong>: Submit reports without sharing your identity.</li>
            <li><strong>Right to Track</strong>: Monitor your report status using the unique tracking ID.</li>
            <li><strong>Right to Data Protection</strong>: Expect secure storage and restricted access to any personal data you choose to provide.</li>
          </ul>
        </section>

        <section id="retention" class="policy-section">
          <h3>6. Data Retention</h3>
          <p>We retain reports only as long as necessary to investigate and resolve cases, meet institutional requirements, and comply with applicable laws. After resolution, data may be archived for compliance, audit, and prevention purposes according to retention schedules.</p>
        </section>

        <section id="security" class="policy-section">
          <h3>7. Security Measures</h3>
          <ul>
            <li>Encryption in transit and at rest where applicable.</li>
            <li>Role‑based access controls, least‑privilege permissions, and session management.</li>
            <li>Monitoring, logging, and periodic security reviews.</li>
            <li>Secure handling of evidence files and controlled access workflows.</li>
          </ul>
          <p class="policy-chip danger">If you suspect a security issue, contact us immediately.</p>
        </section>

        <section id="legal" class="policy-section">
          <h3>8. Legal Compliance</h3>
          <p>The System operates in accordance with applicable laws and regulations related to student protection, anti‑ragging, and data privacy. Where required, reports may be shared with law‑enforcement or regulatory bodies.</p>
        </section>

        <section id="changes" class="policy-section">
          <h3>9. Changes to this Policy</h3>
          <p>We may update this Privacy Policy from time to time. Material changes will be communicated through the System portal and reflected by the <em>Last Updated</em> date above.</p>
        </section>

        <section id="contact" class="policy-section">
          <h3>10. Contact Information</h3>
          <address>
            <strong>Anti‑Ragging Cell / UGC Admin</strong><br />
            Email: <a href="mailto:report@antiragging.xyz">report@antiragging.xyz</a><br />
            Phone: <a href="tel:+94112223344">+94 11 222 3344</a><br />
            Address: 123 street, city, country.
          </address>
        </section>
      </div>

      <div class="policy-footer">
        <div class="policy-actions">
          <button class="btn primary" onclick="window.print()">Print / Save as PDF</button>
          <button class="btn secondary" onclick="toggleOpenAll()" id="toggleBtn">Expand All</button>
          <a class="btn secondary" href="#top" onclick="window.scrollTo({top:0, behavior:'smooth'})">Back to top</a>
        </div>
        <div class="policy-copyright">© <span id="year"></span> Anti‑Ragging Reporting System</div>
      </div>
    </article>
  </div>

  <button class="btn policy-back-to-top" onclick="window.scrollTo({top:0, behavior:'smooth'})" aria-label="Back to top" id="backToTop">↑</button>

  <script>
    // Footer year
    document.getElementById('year').textContent = new Date().getFullYear();

    // Expand/Collapse all details elements
    function toggleOpenAll(){
      const btn = document.getElementById('toggleBtn');
      const items = Array.from(document.querySelectorAll('details'));
      const anyClosed = items.some(d => !d.open);
      items.forEach(d => d.open = anyClosed);
      btn.textContent = anyClosed ? 'Collapse All' : 'Expand All';
    }

    // Back to top button visibility
    window.addEventListener('scroll', function() {
      const backToTop = document.getElementById('backToTop');
      if (window.scrollY > 300) {
        backToTop.classList.add('visible');
      } else {
        backToTop.classList.remove('visible');
      }
    });
  </script>
</body>
</html>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>