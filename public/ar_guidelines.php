<?php include_once __DIR__ . '/../includes/header.php'; session_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Anti-Ragging Guidelines | Anti-Ragging Reporting System</title>
  <link rel="icon" href="../assets/images/favicon.ico">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    .guidelines-container {
      max-width: 900px;
      margin: 40px auto;
      padding: 0 20px;
    }
    
    .guidelines-header {
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      color: white;
      padding: 40px;
      text-align: center;
      border-radius: 16px 16px 0 0;
      position: relative;
      overflow: hidden;
    }
    
    .guidelines-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
      opacity: 0.3;
    }
    
    .guidelines-title {
      font-size: 36px;
      font-weight: 700;
      margin: 0 0 16px 0;
      line-height: 1.2;
      position: relative;
      z-index: 1;
    }
    
    .guidelines-subtitle {
      font-size: 18px;
      opacity: 0.9;
      margin: 0;
      line-height: 1.5;
      position: relative;
      z-index: 1;
    }
    
    .guidelines-card {
      background: var(--bg-primary);
      border: 1px solid var(--border);
      border-radius: 0 0 16px 16px;
      box-shadow: var(--shadow-lg);
      overflow: hidden;
    }
    
    .guidelines-content {
      padding: 40px;
    }
    
    .guidelines-section {
      margin-bottom: 32px;
      padding-bottom: 24px;
      border-bottom: 1px solid var(--border-light);
    }
    
    .guidelines-section:last-child {
      border-bottom: none;
      margin-bottom: 0;
    }
    
    .guidelines-section h2 {
      font-size: 28px;
      font-weight: 600;
      color: var(--text-primary);
      margin: 0 0 20px 0;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    .guidelines-section h2::before {
      content: '';
      width: 4px;
      height: 28px;
      background: var(--primary);
      border-radius: 2px;
    }
    
    .guidelines-section p {
      color: var(--text-secondary);
      line-height: 1.7;
      margin: 0 0 16px 0;
      font-size: 16px;
    }
    
    .guidelines-section ul {
      color: var(--text-secondary);
      line-height: 1.7;
      margin: 16px 0;
      padding-left: 24px;
    }
    
    .guidelines-section li {
      margin-bottom: 12px;
      font-size: 16px;
    }
    
    .guidelines-section strong {
      color: var(--text-primary);
    }
    
    .guidelines-section a {
      color: var(--primary);
      text-decoration: none;
      transition: color 0.2s ease;
    }
    
    .guidelines-section a:hover {
      color: var(--primary-dark);
      text-decoration: underline;
    }
    
    .guidelines-button {
      display: inline-block;
      margin-top: 20px;
      padding: 12px 24px;
      background: var(--primary);
      color: white;
      text-decoration: none;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.2s ease;
      border: 2px solid var(--primary);
    }
    
    .guidelines-button:hover {
      background: var(--primary-dark);
      border-color: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: var(--shadow-lg);
    }
    
    .guidelines-links {
      margin-top: 32px;
      padding: 0;
      list-style: none;
    }
    
    .guidelines-links li {
      margin-bottom: 16px;
      padding: 0;
    }
    
    .guidelines-links a {
      display: block;
      padding: 16px 20px;
      background: var(--bg-secondary);
      border: 1px solid var(--border);
      border-radius: 8px;
      color: var(--text-primary);
      text-decoration: none;
      transition: all 0.2s ease;
      font-weight: 500;
    }
    
    .guidelines-links a:hover {
      background: var(--bg-accent);
      border-color: var(--primary);
      transform: translateY(-2px);
      box-shadow: var(--shadow);
    }
    
    .guidelines-footer {
      background: var(--bg-secondary);
      border-top: 1px solid var(--border);
      padding: 24px 40px;
      text-align: center;
    }
    
    .footer-nav {
      display: flex;
      justify-content: center;
      gap: 20px;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }
    
    .footer-nav a {
      padding: 12px 20px;
      background: var(--bg-primary);
      border: 1px solid var(--border);
      border-radius: 8px;
      color: var(--text-secondary);
      text-decoration: none;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    
    .footer-nav a:hover {
      background: var(--primary);
      color: white;
      border-color: var(--primary);
      transform: translateY(-2px);
    }
    
    .footer-copyright {
      color: var(--text-light);
      font-size: 14px;
    }
    
    @media (max-width: 768px) {
      .guidelines-container {
        margin: 20px auto;
        padding: 0 16px;
      }
      
      .guidelines-header {
        padding: 30px 20px;
      }
      
      .guidelines-title {
        font-size: 28px;
      }
      
      .guidelines-subtitle {
        font-size: 16px;
      }
      
      .guidelines-content {
        padding: 20px;
      }
      
      .guidelines-section h2 {
        font-size: 24px;
      }
      
      .footer-nav {
        flex-direction: column;
        align-items: center;
      }
      
      .footer-nav a {
        width: 100%;
        text-align: center;
      }
    }
  </style>
</head>
<body>
  <div class="guidelines-container">
    <header class="guidelines-header">
      <h1 class="guidelines-title">Anti-Ragging Reporting System</h1>
      <p class="guidelines-subtitle">Anti-Ragging Guidelines & Legal Framework</p>
    </header>

    <div class="guidelines-card">
      <div class="guidelines-content">
        <section class="guidelines-section">
          <h2>What is Ragging?</h2>
          <p>Ragging includes any act that causes physical or psychological harm, fear, humiliation, or harassment to a student or staff member. Examples include forcing humiliating acts, threats, intimidation, sexual harassment, or physical assault.</p>
        </section>

        <section class="guidelines-section">
          <h2>Key Legal Provisions</h2>
          <ul>
            <li><strong>Ragging is a criminal offence</strong>, punishable by imprisonment up to 2 years (up to 10 years for severe cases).</li>
            <li><strong>Intimidation, wrongful restraint, or property damage</strong> carry penalties of 5–20 years imprisonment.</li>
            <li><strong>Students convicted</strong> may be expelled, <strong>staff convicted</strong> may be dismissed.</li>
            <li><strong>Bail restrictions</strong> apply in serious cases.</li>
          </ul>
        </section>

        <section class="guidelines-section">
          <h2>Your Rights as a Student</h2>
          <ul>
            <li><strong>Right to a safe environment:</strong> Expect a ragging-free educational atmosphere.</li>
            <li><strong>Right to anonymity:</strong> Submit reports without sharing your identity.</li>
            <li><strong>Right to confidentiality:</strong> Your personal information remains protected.</li>
            <li><strong>Right to track complaints:</strong> Monitor your report status securely.</li>
          </ul>
        </section>

        <section class="guidelines-section">
          <h2>How to Report Incidents</h2>
          <ul>
            <li><strong>Online Portal:</strong> <a href="https://eugc.ac.lk/rag/" target="_blank">UGC Ragging Complaint Portal</a></li>
            <li><strong>Emergency Hotline:</strong> Contact UGC helpline numbers immediately.</li>
            <li><strong>Campus Reporting:</strong> Report to Anti-Ragging Committee, student welfare officer, or security.</li>
            <li><strong>Direct Contact:</strong> Reach out to university authorities or UGC officials.</li>
          </ul>
        </section>

        <section class="guidelines-section">
          <h2>Legal Framework</h2>
          <p>The anti-ragging laws in Sri Lanka are comprehensive and strictly enforced. The legal framework includes:</p>
          <ul>
            <li><strong>Act No. 20 of 1998:</strong> Prohibition of Ragging and Other Forms of Violence in Educational Institutions</li>
            <li><strong>UGC Regulations:</strong> Guidelines for universities and higher education institutions</li>
            <li><strong>Penal Code Provisions:</strong> Criminal penalties for various forms of harassment</li>
            <li><strong>Institutional Policies:</strong> University-specific anti-ragging measures</li>
          </ul>
          
          <a class="guidelines-button" href="https://www.lawnet.gov.lk/wp-content/uploads/cons_stat_up2_2006/1998Y0V0C20A.html" target="_blank">
            📖 Read Full Act No. 20 of 1998
          </a>
        </section>

        <section class="guidelines-section">
          <h2>Additional Resources</h2>
          <ul class="guidelines-links">
            <li>
              <a href="https://www.ugc.ac.lk/index.php?option=com_content&view=article&id=2366&catid=153&Itemid=103&lang=en" target="_blank">
                🏛️ UGC Anti-Ragging Initiatives & Programs
              </a>
            </li>
            <li>
              <a href="https://www.researchgate.net/publication/321808559_PSYCHOLOGICAL_SOCIOLOGICAL_AND_POLITICAL_DIMENSIONS_OF_RAGGING_IN_SRI_LANKAN_UNIVERSITIES" target="_blank">
                📚 Psychological & Sociological Research on Ragging
              </a>
            </li>
            <li>
              <a href="https://www.ugc.ac.lk/en/student-affairs/anti-ragging-cell.html" target="_blank">
                🚨 UGC Anti-Ragging Cell Contact Information
              </a>
            </li>
          </ul>
        </section>
      </div>

      <div class="guidelines-footer">
        <nav class="footer-nav">
          <a href="privacy_policy.php">Privacy Policy</a>
          <a href="terms.php">Terms of Service</a>
          <a href="submit.php">Submit Report</a>
          <a href="track.php">Track Report</a>
        </nav>
        <div class="footer-copyright">
          © <span id="year"></span> Anti-Ragging Reporting System - Empowering Students for a Safe Educational Environment
        </div>
      </div>
    </div>
  </div>

  <script>
    document.getElementById('year').textContent = new Date().getFullYear();
  </script>
</body>
</html>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>