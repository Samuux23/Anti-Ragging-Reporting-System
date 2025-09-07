// Function to copy report code to clipboard
        async function copyReportCode() {
            try {
                await navigator.clipboard.writeText(currentReportCode);
                showCopiedNotification();
            } catch (err) {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = currentReportCode;
                textArea.style.position = 'fixed';
                textArea.style.opacity = '0';
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    showCopiedNotification();
                } catch (fallbackErr) {
                    alert('Could not copy to clipboard. Please manually copy: ' + currentReportCode);
                }
                document.body.removeChild(textArea);
            }
        }<?php
session_start();

// Check if we have success data in session
if (!isset($_SESSION['success_message'])) {
    header('Location: index.php');
    exit;
}

$report_code = $_SESSION['report_code'] ?? 'AR00000000';
$university_name = $_SESSION['university_name'] ?? 'Unknown University';
$uploaded_files = $_SESSION['uploaded_files'] ?? [];

// Clear session data after use
unset($_SESSION['success_message']);
unset($_SESSION['report_code']);
unset($_SESSION['university_name']);
unset($_SESSION['uploaded_files']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Submission Success</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            animation: fadeIn 0.3s ease-out forwards;
        }

        @keyframes fadeIn {
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from {
                transform: translateY(-30px) scale(0.9);
                opacity: 0;
            }
            to {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        .popup {
    background: white;
    border-radius: 20px;
    padding: 40px;
    max-width: 500px;
    width: 90%;
    text-align: center;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
    animation: slideIn 0.4s ease-out 0.1s both;
    position: relative;
    max-height: 90vh;
    overflow-y: auto; /* keep only vertical scroll */
}

/* Custom Scrollbar only for .popup */
.popup::-webkit-scrollbar {
    width: 10px; /* scrollbar width */
}

.popup::-webkit-scrollbar-track {
    background: transparent; /* no track */
}

.popup::-webkit-scrollbar-thumb {
    background: #555;       /* circle color */
    border-radius: 50%;     /* make it circular */
    min-height: 20px;       /* ensure small circle */
}

.popup::-webkit-scrollbar-thumb:hover {
    background: #222; /* darker on hover */
}

/* Firefox support */
.popup {
    scrollbar-width: thin;
    scrollbar-color: #555 transparent;
}


        .popup::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #4CAF50, #2196F3, #FF9800);
            animation: shimmer 2s ease-in-out infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            border-radius: 50%;
            margin: 0 auto 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: bounce 0.6s ease-out 0.3s both;
            box-shadow: 0 10px 25px rgba(76, 175, 80, 0.3);
        }

        .success-icon::after {
            content: '✓';
            color: white;
            font-size: 40px;
            font-weight: bold;
        }

        .title {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #2c3e50, #3498db);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            font-size: 16px;
            color: #7f8c8d;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .report-code-container {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border: 2px solid #e3f2fd;
            border-radius: 15px;
            padding: 25px;
            margin: 25px 0;
            position: relative;
            overflow: hidden;
        }

        .report-code-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #2196F3, #4CAF50, #FF9800, #2196F3);
            background-size: 200% 100%;
            animation: gradientFlow 3s ease-in-out infinite;
        }

        @keyframes gradientFlow {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .code-label {
            font-size: 14px;
            font-weight: 600;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .report-code {
            font-size: 32px;
            font-weight: 800;
            color: #2c3e50;
            font-family: 'Courier New', monospace;
            letter-spacing: 3px;
            margin: 10px 0;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .code-note {
            font-size: 12px;
            color: #95a5a6;
            font-style: italic;
            margin-top: 8px;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-width: 140px;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transition: all 0.3s ease;
            transform: translate(-50%, -50%);
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-copy {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.3);
        }

        .btn-copy:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(52, 152, 219, 0.4);
        }

        .btn-track {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            box-shadow: 0 8px 20px rgba(231, 76, 60, 0.3);
        }

        .btn-track:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(231, 76, 60, 0.4);
        }

        .btn-close {
            background: linear-gradient(135deg, #95a5a6, #7f8c8d);
            color: white;
            box-shadow: 0 8px 20px rgba(149, 165, 166, 0.3);
        }

        .btn-close:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(149, 165, 166, 0.4);
        }

        .copied-notification {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #4CAF50;
            color: white;
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            opacity: 0;
            transition: all 0.3s ease;
            pointer-events: none;
            z-index: 1001;
        }

        .copied-notification.show {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1.1);
        }

        .info-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #3498db;
        }

        .info-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .info-text {
            font-size: 14px;
            color: #7f8c8d;
            line-height: 1.6;
        }

        .university-badge {
            display: inline-block;
            background: linear-gradient(135deg, #8e44ad, #9b59b6);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin: 10px 0;
        }

        @media (max-width: 768px) {
            .popup {
                padding: 30px 20px;
                margin: 20px;
                max-height: 85vh;
                overflow-y: auto;
            }
            
            .button-group {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 250px;
            }
            
            .report-code {
                font-size: 24px;
                letter-spacing: 2px;
            }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="overlay" id="successOverlay">
        <div class="popup">
            <div class="success-icon"></div>
            
            <h1 class="title">Report Submitted Successfully!</h1>
            <p class="subtitle">Your incident report has been securely submitted and assigned a unique tracking code.</p>
            
            <div class="university-badge" id="universityBadge"><?= htmlspecialchars($university_name) ?></div>
            
            <div class="report-code-container pulse">
                <div class="code-label">Your Report Code</div>
                <div class="report-code" id="reportCode"><?= htmlspecialchars($report_code) ?></div>
                <div class="code-note">Save this code to track your report status</div>
            </div>
            
            <div class="info-section">
                <div class="info-title">What happens next?</div>
                <div class="info-text" id="infoText">
                    Your report will be reviewed by our team within 24-48 hours. You'll receive email updates at key stages of the process. Use your report code to check status anytime.
                    <?php if (!empty($uploaded_files)): ?>
                        <br><br><strong>Uploaded files:</strong> <?= htmlspecialchars(implode(', ', $uploaded_files)) ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="button-group">
                <button class="btn btn-copy" onclick="copyReportCode()">
                    <span>Copy Code</span>
                </button>
                
                <a href="track_status.php?code=<?= urlencode($report_code) ?>" class="btn btn-track">
                    <span>Track Status</span>
                </a>
                
                <a href="index.php" class="btn btn-close">
                    <span>✕</span>
                    <span>Close</span>
                </a>
            </div>
        </div>
    </div>
    
    <div class="copied-notification" id="copiedNotification">
        ✓ Report code copied to clipboard!
    </div>

    <script>
        // Global variables to store report data
        let currentReportCode = '<?= htmlspecialchars($report_code) ?>';
        let currentUniversity = '<?= htmlspecialchars($university_name) ?>';

        // Function to copy report code to clipboard
        async function copyReportCode() {
            try {
                await navigator.clipboard.writeText(currentReportCode);
                showCopiedNotification();
            } catch (err) {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = currentReportCode;
                textArea.style.position = 'fixed';
                textArea.style.opacity = '0';
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    showCopiedNotification();
                } catch (fallbackErr) {
                    alert('Could not copy to clipboard. Please manually copy: ' + currentReportCode);
                }
                document.body.removeChild(textArea);
            }
        }

        // Function to show copied notification
        function showCopiedNotification() {
            const notification = document.getElementById('copiedNotification');
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 2000);
        }

        // Handle keyboard events
        function handleKeyPress(e) {
            if (e.key === 'Escape') {
                window.location.href = 'index.php';
            } else if (e.ctrlKey && e.key === 'c') {
                e.preventDefault();
                copyReportCode();
            }
        }

        // Add keyboard event listener
        document.addEventListener('keydown', handleKeyPress);

        // Auto-close popup after 2 minutes of inactivity
        let autoCloseTimer = setTimeout(() => {
            window.location.href = 'index.php';
        }, 120000); // 2 minutes

        // Reset timer on user interaction
        document.addEventListener('click', () => {
            clearTimeout(autoCloseTimer);
            autoCloseTimer = setTimeout(() => {
                window.location.href = 'index.php';
            }, 120000);
        });

        // Show popup immediately on page load
        window.onload = function() {
            const overlay = document.getElementById('successOverlay');
            overlay.style.display = 'flex';
        };
    </script>
</body>
</html>