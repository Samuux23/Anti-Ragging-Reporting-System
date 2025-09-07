<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/config.php';
if (empty($_SESSION['admin_user'])) { header('Location: login.php'); exit; }

// Fetch analytics data
$byStatus = $pdo->query("SELECT status, COUNT(*) as count FROM reports GROUP BY status ORDER BY count DESC")->fetchAll();
$byUniversity = $pdo->query("SELECT u.uni_name, COUNT(*) as count FROM reports r LEFT JOIN university u ON r.university_id=u.uni_id GROUP BY u.uni_name ORDER BY count DESC LIMIT 15")->fetchAll();
$byIncidentType = $pdo->query("SELECT incident_type, COUNT(*) as count FROM reports GROUP BY incident_type ORDER BY count DESC")->fetchAll();
$byMonth = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM reports GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month DESC LIMIT 12")->fetchAll();

// Calculate totals
$totalReports = $pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn();
$totalUniversities = $pdo->query("SELECT COUNT(DISTINCT university_id) FROM reports WHERE university_id IS NOT NULL")->fetchColumn();
$totalIncidentTypes = $pdo->query("SELECT COUNT(DISTINCT incident_type) FROM reports")->fetchColumn();

// Prepare data for charts
$statusLabels = array_column($byStatus, 'status');
$statusCounts = array_column($byStatus, 'count');

$universityLabels = array_column($byUniversity, 'uni_name');
$universityCounts = array_column($byUniversity, 'count');

$incidentLabels = array_column($byIncidentType, 'incident_type');
$incidentCounts = array_column($byIncidentType, 'count');

$monthLabels = array_column($byMonth, 'month');
$monthCounts = array_column($byMonth, 'count');

// Reverse month data for chronological order
$monthLabels = array_reverse($monthLabels);
$monthCounts = array_reverse($monthCounts);

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Analytics Dashboard</title>
  <link rel="icon" href="../../assets/images/favicon.ico">
  <link rel="stylesheet" href="../../assets/css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .pdf-download-btn {
      position: fixed;
      top: 20px;
      right: 20px;
      background: #dc2626;
      color: white;
      border: none;
      padding: 12px 20px;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
      transition: all 0.2s ease;
      z-index: 1000;
    }
    
    .pdf-download-btn:hover {
      background: #b91c1c;
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(220, 38, 38, 0.4);
    }
    
    .pdf-download-btn:active {
      transform: translateY(0);
    }
  </style>
  <style>
    .analytics-container {
      padding: 20px;
      max-width: 1400px;
      margin: 0 auto;
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    
    .stat-card {
      background: var(--bg-primary);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      box-shadow: var(--shadow);
      transition: transform 0.2s ease;
    }
    
    .stat-card:hover {
      transform: translateY(-2px);
    }
    
    .stat-number {
      font-size: 2.5rem;
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 8px;
    }
    
    .stat-label {
      color: var(--text-secondary);
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .charts-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
      margin-bottom: 30px;
    }
    
    .chart-container {
      background: var(--bg-primary);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 20px;
      box-shadow: var(--shadow);
    }
    
    .chart-title {
      font-size: 1.2rem;
      font-weight: 600;
      color: var(--text-primary);
      margin-bottom: 20px;
      text-align: center;
    }
    
    .gauge-container {
      background: var(--bg-primary);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 20px;
      box-shadow: var(--shadow);
      margin-bottom: 30px;
    }
    
    .gauge-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 20px;
    }
    
    .gauge-item {
      text-align: center;
    }
    
    .gauge-canvas {
      margin: 0 auto;
    }
    
    .gauge-label {
      margin-top: 10px;
      font-weight: 500;
      color: var(--text-primary);
    }
    
    .gauge-value {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--primary);
      margin-top: 5px;
    }
    
    .full-width-chart {
      grid-column: 1 / -1;
    }
    
    @media (max-width: 768px) {
      .charts-grid {
        grid-template-columns: 1fr;
      }
      
      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }
    
    .loading {
      text-align: center;
      padding: 40px;
      color: var(--text-secondary);
    }
    
    .spinner {
      border: 3px solid var(--border);
      border-top: 3px solid var(--primary);
      border-radius: 50%;
      width: 30px;
      height: 30px;
      animation: spin 1s linear infinite;
      margin: 0 auto 20px;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
  </style>
</head>
<body>
  <?php include_once __DIR__ . '/../../includes/header.php'; ?>
  
  <button class="pdf-download-btn" onclick="downloadAnalyticsPDF()">
    Download Analytics PDF
  </button>
  
  <div class="analytics-container">
    <h1 style="margin-bottom: 30px; color: var(--text-primary);">Analytics Dashboard</h1>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-number" id="totalReports"><?= number_format($totalReports) ?></div>
        <div class="stat-label">Total Reports</div>
      </div>
      <div class="stat-card">
        <div class="stat-number" id="totalUniversities"><?= number_format($totalUniversities) ?></div>
        <div class="stat-label">Universities</div>
      </div>
      <div class="stat-card">
        <div class="stat-number" id="totalIncidentTypes"><?= number_format($totalIncidentTypes) ?></div>
        <div class="stat-label">Incident Types</div>
      </div>
      <div class="stat-card">
        <div class="stat-number" id="avgReportsPerUni"><?= $totalUniversities > 0 ? number_format($totalReports / $totalUniversities, 1) : 0 ?></div>
        <div class="stat-label">Avg Reports/Uni</div>
      </div>
    </div>
    
    <!-- Status Gauges -->
    <div class="gauge-container">
      <h2 class="chart-title">Report Status Distribution</h2>
      <div class="gauge-grid">
        <?php foreach ($byStatus as $status): ?>
        <div class="gauge-item">
          <canvas class="gauge-canvas" id="gauge-<?= str_replace(' ', '-', $status['status']) ?>" width="120" height="120"></canvas>
          <div class="gauge-label"><?= htmlspecialchars($status['status']) ?></div>
          <div class="gauge-value"><?= number_format($status['count']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    
    <!-- Charts Grid -->
    <div class="charts-grid">
      <!-- Reports by University -->
      <div class="chart-container">
        <h3 class="chart-title">Reports by University</h3>
        <canvas id="universityChart" width="400" height="300"></canvas>
      </div>
      
      <!-- Reports by Incident Type -->
      <div class="chart-container">
        <h3 class="chart-title">Reports by Incident Type</h3>
        <canvas id="incidentChart" width="400" height="300"></canvas>
      </div>
    </div>
    
    <!-- Monthly Trend Chart -->
    <div class="chart-container full-width-chart">
      <h3 class="chart-title">Monthly Report Trends</h3>
      <canvas id="trendChart" width="800" height="300"></canvas>
    </div>
  </div>
  
  <script>
    // Color palette for charts
    const colors = [
      '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6',
      '#EC4899', '#06B6D4', '#84CC16', '#F97316', '#6366F1'
    ];
    
    // Animate numbers
    function animateNumber(element, target, duration = 1000) {
      const start = 0;
      const increment = target / (duration / 16);
      let current = start;
      
      const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
          current = target;
          clearInterval(timer);
        }
        element.textContent = Math.floor(current).toLocaleString();
      }, 16);
    }
    
    // Initialize number animations
    document.addEventListener('DOMContentLoaded', function() {
      animateNumber(document.getElementById('totalReports'), <?= $totalReports ?>);
      animateNumber(document.getElementById('totalUniversities'), <?= $totalUniversities ?>);
      animateNumber(document.getElementById('totalIncidentTypes'), <?= $totalIncidentTypes ?>);
      animateNumber(document.getElementById('avgReportsPerUni'), <?= $totalUniversities > 0 ? $totalReports / $totalUniversities : 0 ?>);
    });
    
    // Create Gauge Charts
    function createGauge(canvasId, value, maxValue, label) {
      const canvas = document.getElementById(canvasId);
      const ctx = canvas.getContext('2d');
      const centerX = canvas.width / 2;
      const centerY = canvas.height / 2;
      const radius = Math.min(centerX, centerY) - 10;
      
      // Calculate angle
      const percentage = value / maxValue;
      const startAngle = -Math.PI / 2;
      const endAngle = startAngle + (percentage * Math.PI);
      
      // Draw background arc
      ctx.beginPath();
      ctx.arc(centerX, centerY, radius, startAngle, startAngle + Math.PI, false);
      ctx.strokeStyle = '#E5E7EB';
      ctx.lineWidth = 12;
      ctx.stroke();
      
      // Draw value arc with animation
      let currentAngle = startAngle;
      const animateGauge = () => {
        if (currentAngle < endAngle) {
          currentAngle += 0.05;
          
          ctx.beginPath();
          ctx.arc(centerX, centerY, radius, startAngle, currentAngle, false);
          ctx.strokeStyle = colors[Math.floor(Math.random() * colors.length)];
          ctx.lineWidth = 12;
          ctx.lineCap = 'round';
          ctx.stroke();
          
          requestAnimationFrame(animateGauge);
        }
      };
      
      setTimeout(animateGauge, 500);
    }
    
    // Initialize gauges
    <?php 
    $maxStatusCount = max(array_column($byStatus, 'count'));
    foreach ($byStatus as $status): 
    ?>
    createGauge('gauge-<?= str_replace(' ', '-', $status['status']) ?>', <?= $status['count'] ?>, <?= $maxStatusCount ?>, '<?= htmlspecialchars($status['status']) ?>');
    <?php endforeach; ?>
    
    // Create University Chart
    const universityCtx = document.getElementById('universityChart').getContext('2d');
    new Chart(universityCtx, {
      type: 'doughnut',
      data: {
        labels: <?= json_encode($universityLabels) ?>,
        datasets: [{
          data: <?= json_encode($universityCounts) ?>,
          backgroundColor: colors.slice(0, <?= count($universityLabels) ?>),
          borderWidth: 2,
          borderColor: '#ffffff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              padding: 20,
              usePointStyle: true
            }
          }
        },
        animation: {
          animateRotate: true,
          animateScale: true,
          duration: 2000,
          easing: 'easeOutQuart'
        }
      }
    });
    
    // Create Incident Type Chart
    const incidentCtx = document.getElementById('incidentChart').getContext('2d');
    new Chart(incidentCtx, {
      type: 'bar',
      data: {
        labels: <?= json_encode($incidentLabels) ?>,
        datasets: [{
          label: 'Number of Reports',
          data: <?= json_encode($incidentCounts) ?>,
          backgroundColor: colors.slice(0, <?= count($incidentLabels) ?>),
          borderColor: colors.slice(0, <?= count($incidentLabels) ?>),
          borderWidth: 1,
          borderRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0,0,0,0.1)'
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        },
        animation: {
          duration: 2000,
          easing: 'easeOutQuart',
          onProgress: function(animation) {
            animation.chart.data.datasets.forEach(function(dataset, i) {
              dataset.data.forEach(function(value, index) {
                const model = dataset._meta[Object.keys(dataset._meta)[0]].data[index]._model;
                model.y = animation.animationObject.currentStep / animation.animationObject.numSteps * value;
              });
            });
          }
        }
      }
    });
    
    // Create Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
      type: 'line',
      data: {
        labels: <?= json_encode($monthLabels) ?>,
        datasets: [{
          label: 'Reports per Month',
          data: <?= json_encode($monthCounts) ?>,
          borderColor: '#3B82F6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          borderWidth: 3,
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#3B82F6',
          pointBorderColor: '#ffffff',
          pointBorderWidth: 2,
          pointRadius: 6,
          pointHoverRadius: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0,0,0,0.1)'
            }
          },
          x: {
            grid: {
              color: 'rgba(0,0,0,0.1)'
            }
          }
        },
        animation: {
          duration: 3000,
          easing: 'easeOutQuart',
          onProgress: function(animation) {
            const chart = animation.chart;
            const dataset = chart.data.datasets[0];
            const meta = chart.getDatasetMeta(0);
            
            meta.data.forEach(function(bar, index) {
              const value = dataset.data[index];
              const model = bar._model;
              model.y = animation.animationObject.currentStep / animation.animationObject.numSteps * value;
            });
          }
        }
      }
    });
    
    // Function to download analytics PDF
    function downloadAnalyticsPDF() {
      // Show loading state
      const btn = document.querySelector('.pdf-download-btn');
      const originalText = btn.textContent;
      btn.textContent = '⏳ Generating PDF...';
      btn.disabled = true;
      
      // Prepare analytics data for PDF
      const analyticsData = {
        totalReports: <?= $totalReports ?>,
        totalUniversities: <?= $totalUniversities ?>,
        totalIncidentTypes: <?= $totalIncidentTypes ?>,
        avgReportsPerUni: <?= $totalUniversities > 0 ? $totalReports / $totalUniversities : 0 ?>,
        byStatus: <?= json_encode($byStatus) ?>,
        byUniversity: <?= json_encode($byUniversity) ?>,
        byIncidentType: <?= json_encode($byIncidentType) ?>,
        byMonth: <?= json_encode($byMonth) ?>
      };
      
      // Create form data
      const formData = new FormData();
      formData.append('analytics_data', JSON.stringify(analyticsData));
      
      // Send request to generate PDF
      fetch('analytics_pdf.php', {
        method: 'POST',
        body: formData
      })
      .then(response => {
        if (response.ok) {
          return response.blob();
        }
        throw new Error('PDF generation failed');
      })
      .then(blob => {
        // Create download link
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'anti_ragging_analytics_' + new Date().toISOString().slice(0, 10) + '.pdf';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        // Reset button
        btn.textContent = originalText;
        btn.disabled = false;
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Failed to generate PDF. Please try again.');
        
        // Reset button
        btn.textContent = originalText;
        btn.disabled = false;
      });
    }
  </script>
  <?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>


