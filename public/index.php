<?php include_once __DIR__ . '/../includes/header.php'; ?>
<section class="hero">
  <div class="badge">Secure • Anonymous • Confidential</div>
  <h1>Anonymous Anti‑Ragging Reporting System</h1>
  <p>A secure platform for reporting ragging incidents anonymously. Help create a safer educational environment for everyone.</p>
  <div class="cta">
    <a class="btn primary" href="submit.php">
      Submit Report Now
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
        <path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </a>
    <a class="btn secondary" href="track.php">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/>
        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
      </svg>
      Track Report Status
    </a>
  </div>

  <form id="quick-track" class="quick-track" action="track_status.php" method="post">
    <label for="quick-code" class="sr-only">Report Code</label>
    <input id="quick-code" name="report_code" type="text" placeholder="Enter code e.g., AR12345678" maxlength="12" required />
    <button class="btn primary" type="submit">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
        <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      Search
    </button>
  </form>
</section>

<section class="features">
  <div class="section-header">
    <h2>Why Choose Our Platform?</h2>
    <p>Built with security, privacy, and effectiveness at its core to ensure every voice is heard safely.</p>
  </div>
  <div class="grid-4">
    <article class="card reveal feature-accent">
      <div class="icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
          <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z" fill="#3b82f6"/>
          <path d="M12 7l-3 3 3 3 3-3-3-3z" fill="white"/>
        </svg>
      </div>
      <h3>Anonymous Reporting</h3>
      <p>Submit reports completely anonymously with guaranteed privacy protection. Your identity remains secure throughout the process.</p>
    </article>
    <article class="card reveal feature-accent" data-reveal-delay="100">
      <div class="icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
          <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-9-5z" fill="#3b82f6"/>
          <path d="M12 9v6M9 12h6" stroke="white" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </div>
      <h3>Real-Time Alerts</h3>
      <p>Immediate notification system ensures rapid response to urgent situations and emergency reports.</p>
    </article>
  </div>
</section>

<section class="how-it-works">
  <div class="section-header">
    <h2>How It Works</h2>
    <p>A simple, secure process designed to protect your privacy while ensuring effective resolution.</p>
  </div>
  <div class="steps-container">
    <div class="step reveal">
      <div class="step-number">01</div>
      <h3>Submit Report</h3>
      <p>Fill out the anonymous form with incident details and supporting evidence.</p>
    </div>
    <div class="step reveal" data-reveal-delay="100">
      <div class="step-number">02</div>
      <h3>Get Tracking Code</h3>
      <p>Receive a unique code to monitor your report status and investigation progress.</p>
    </div>
    <div class="step reveal" data-reveal-delay="200">
      <div class="step-number">03</div>
      <h3>Investigation Process</h3>
      <p>Our team reviews the case while maintaining complete confidentiality.</p>
    </div>
    <div class="step reveal" data-reveal-delay="300">
      <div class="step-number">04</div>
      <h3>Resolution & Follow-up</h3>
      <p>Appropriate action is taken and you're updated on the outcome.</p>
    </div>
  </div>
</section>

<section class="metrics">
  <div class="metrics-container">
    <div class="metric">
      <div class="metric-number" data-target="100" data-suffix="%">0%</div>
      <div class="metric-label">Anonymous</div>
      <div class="metric-description">Complete privacy protection</div>
    </div>
    <div class="metric">
      <div class="metric-number" data-target="24" data-suffix="/7">0</div>
      <div class="metric-label">Available</div>
      <div class="metric-description">Round the clock reporting</div>
    </div>
    <div class="metric">
      <div class="metric-number" data-target="10" data-prefix="5-" data-fixed="0">0</div>
      <div class="metric-label">Days Average</div>
      <div class="metric-description">Investigation timeline</div>
    </div>
  </div>
</section>

<section class="faq">
  <div class="section-header">
    <h2>Frequently Asked Questions</h2>
    <p>Get answers to common questions about our reporting system.</p>
  </div>
  <div class="faq-container">
    <div class="faq-item">
      <div class="faq-question">
        <span>Is my identity completely protected?</span>
        <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <div class="faq-answer">
        We never collect personal identifiers in the form. Tracking is handled via a random code only. Evidence files are scrubbed of metadata when possible.
      </div>
    </div>
    <div class="faq-item">
      <div class="faq-question">
        <span>How long does investigation take?</span>
        <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <div class="faq-answer">
        Typically 5–10 business days depending on complexity and evidence. You'll see updates in the tracker as the case progresses.
      </div>
    </div>
    <div class="faq-item">
      <div class="faq-question">
        <span>Can I upload evidence files?</span>
        <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <div class="faq-answer">
        Yes, you can attach images and PDFs (2MB each). They are encrypted at rest and visible only to authorized staff.
      </div>
    </div>
    <div class="faq-item">
      <div class="faq-question">
        <span>What happens after I submit a report?</span>
        <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <div class="faq-answer">
        You'll receive a tracking code. Our team reviews the report, may request additional info, and will update the status as actions are taken.
      </div>
    </div>
    <div class="faq-item">
      <div class="faq-question">
        <span>How do I track my report status?</span>
        <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <div class="faq-answer">
        Enter your tracking code in the search box above or visit the Track Status page to see the latest updates and timeline.
      </div>
    </div>
  </div>
</section>

<section class="cta-section">
  <div class="cta-content">
    <h2>Ready to Make a Difference?</h2>
    <p>Your voice matters. Report incidents safely and help create a better environment for everyone.</p>
    <a class="btn primary large" href="submit.php">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
        <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z" fill="currentColor"/>
      </svg>
      Submit Anonymous Report
    </a>
  </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
