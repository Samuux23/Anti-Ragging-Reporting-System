// Character counter for textarea
document.addEventListener("DOMContentLoaded", function () {
  // Apply saved theme early
  const savedTheme = localStorage.getItem("theme");
  if (savedTheme === "dark") {
    document.documentElement.setAttribute("data-theme", "dark");
  }
  const textarea = document.getElementById("details");
  const charCount = document.getElementById("char-count");

  if (textarea && charCount) {
    textarea.addEventListener("input", function () {
      const currentLength = this.value.length;
      charCount.textContent = currentLength;

      if (currentLength > 900) {
        charCount.style.color = "#ef4444";
      } else if (currentLength > 800) {
        charCount.style.color = "#f59e0b";
      } else {
        charCount.style.color = "#9ca3af";
      }
    });
  }

  // Navigation highlighting
  const currentPath = window.location.pathname;
  const navLinks = document.querySelectorAll(".nav a");

  navLinks.forEach((link) => {
    if (
      link.getAttribute("href") === currentPath ||
      (currentPath.includes("submit.php") &&
        link.getAttribute("href").includes("submit.php")) ||
      (currentPath.includes("track.php") &&
        link.getAttribute("href").includes("track.php"))
    ) {
      link.classList.add("active");
    }
  });

  // File upload button functionality
  const uploadBtn = document.querySelector(".upload-btn");
  const fileInput = document.querySelector('input[type="file"]');

  if (uploadBtn && fileInput) {
    uploadBtn.addEventListener("click", function () {
      fileInput.click();
    });

    fileInput.addEventListener("change", function () {
      if (this.files.length > 0) {
        const fileNames = Array.from(this.files)
          .map((file) => file.name)
          .join(", ");
        uploadBtn.textContent = `${this.files.length} file(s) selected`;
        uploadBtn.style.background = "#10b981";
      }
    });
  }

  // FAQ functionality
  const faqItems = document.querySelectorAll(".faq-item");

  faqItems.forEach((item) => {
    const question = item.querySelector(".faq-question");

    question.addEventListener("click", function () {
      const isActive = item.classList.contains("active");

      // Close all FAQ items
      faqItems.forEach((faq) => faq.classList.remove("active"));

      // Open clicked item if it wasn't active
      if (!isActive) {
        item.classList.add("active");
      }
    });
  });

  // Reveal on scroll
  const revealEls = document.querySelectorAll(".reveal");
  const revealObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("revealed");
          revealObserver.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.2 }
  );

  revealEls.forEach((el) => revealObserver.observe(el));

  // Animated counters
  const counters = document.querySelectorAll(".metric-number[data-target]");
  const animateCounter = (el) => {
    const target = Number(el.dataset.target || 0);
    const prefix = el.dataset.prefix || "";
    const suffix = el.dataset.suffix || "";
    const fixed = Number(el.dataset.fixed || 0);
    let current = 0;
    const duration = 1200;
    const start = performance.now();

    function tick(now) {
      const progress = Math.min((now - start) / duration, 1);
      current = target * progress;
      el.textContent = `${prefix}${current.toFixed(fixed)}${suffix}`;
      if (progress < 1) requestAnimationFrame(tick);
    }

    requestAnimationFrame(tick);
  };

  const countersObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          countersObserver.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.6 }
  );

  counters.forEach((c) => countersObserver.observe(c));

  // Dark mode toggle (placeholder for future implementation)
  const darkModeToggle = document.getElementById("dark-mode-toggle");

  if (darkModeToggle) {
    const updateIcon = () => {
      const isDark =
        document.documentElement.getAttribute("data-theme") === "dark";
      darkModeToggle.title = isDark
        ? "Switch to light mode"
        : "Switch to dark mode";
    };
    updateIcon();
    darkModeToggle.addEventListener("click", function () {
      const current = document.documentElement.getAttribute("data-theme");
      const next = current === "dark" ? "light" : "dark";
      if (next === "dark") {
        document.documentElement.setAttribute("data-theme", "dark");
      } else {
        document.documentElement.removeAttribute("data-theme");
      }
      localStorage.setItem("theme", next === "dark" ? "dark" : "light");
      updateIcon();
    });
  }

  // Copy tracking code on success page
  const copyBtn = document.getElementById("copy-code-btn");
  const codeBox = document.getElementById("tracking-code");
  if (copyBtn && codeBox) {
    copyBtn.addEventListener("click", async () => {
      try {
        await navigator.clipboard.writeText(
          codeBox.dataset.code || codeBox.textContent.trim()
        );
        copyBtn.textContent = "Copied!";
        copyBtn.style.background = "#10b981";
        copyBtn.style.color = "#fff";
        setTimeout(() => {
          copyBtn.textContent = "Copy code";
          copyBtn.removeAttribute("style");
        }, 1500);
      } catch (e) {
        console.warn("Clipboard copy failed:", e);
      }
    });
  }

  // Copy on status page
  const copyStatusBtn = document.getElementById("copy-status-code");
  const statusCodeBox = document.getElementById("status-tracking-code");
  if (copyStatusBtn && statusCodeBox) {
    copyStatusBtn.addEventListener("click", async () => {
      try {
        await navigator.clipboard.writeText(
          statusCodeBox.dataset.code || statusCodeBox.textContent.trim()
        );
        copyStatusBtn.textContent = "Copied!";
        copyStatusBtn.style.background = "#10b981";
        copyStatusBtn.style.color = "#fff";
        setTimeout(() => {
          copyStatusBtn.textContent = "Copy code";
          copyStatusBtn.removeAttribute("style");
        }, 1500);
      } catch (e) {
        console.warn("Clipboard copy failed:", e);
      }
    });
  }
});
document.addEventListener("DOMContentLoaded", () => {
  const firstInput = document.querySelector(
    "form input, form select, form textarea"
  );
  if (firstInput) firstInput.focus();
});


