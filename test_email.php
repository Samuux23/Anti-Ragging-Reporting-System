<?php
// Test Email Script for Anti-Ragging Portal
// This script tests the SMTP email connection

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/email_helper.php';

echo "<h2>Testing SMTP Email Connection</h2>";

try {
    // Test basic email
    echo "<p>Testing basic email...</p>";
    
    $testEmail = 'test@example.com'; // Change this to your test email
    $subject = 'SMTP Test - Anti-Ragging Portal';
    $message = '
    <html>
    <body>
        <h2>SMTP Test Email</h2>
        <p>This is a test email to verify the SMTP connection is working properly.</p>
        <p><strong>SMTP Settings:</strong></p>
        <ul>
            <li>Host: ' . SMTP_HOST . '</li>
            <li>Port: ' . SMTP_PORT . '</li>
            <li>Username: ' . SMTP_USERNAME . '</li>
            <li>From Email: ' . SMTP_FROM_EMAIL . '</li>
        </ul>
        <p><em>Sent at: ' . date('Y-m-d H:i:s') . '</em></p>
    </body>
    </html>
    ';
    
    $result = $emailHelper->sendEmail($testEmail, $subject, $message);
    
    if ($result) {
        echo "<p style='color: green;'>✅ Basic email test PASSED!</p>";
    } else {
        echo "<p style='color: red;'>❌ Basic email test FAILED!</p>";
    }
    
    // Test admin notification
    echo "<p>Testing admin notification...</p>";
    
    $adminResult = $emailHelper->sendAdminNotification(
        'TEST123', 
        'verbal_abuse', 
        'Test University', 
        'test@example.com'
    );
    
    if ($adminResult) {
        echo "<p style='color: green;'>✅ Admin notification test PASSED!</p>";
    } else {
        echo "<p style='color: red;'>❌ Admin notification test FAILED!</p>";
    }
    
    // Test reporter confirmation
    echo "<p>Testing reporter confirmation...</p>";
    
    $reporterResult = $emailHelper->sendReporterConfirmation(
        $testEmail, 
        'TEST123', 
        'Test University'
    );
    
    if ($reporterResult) {
        echo "<p style='color: green;'>✅ Reporter confirmation test PASSED!</p>";
    } else {
        echo "<p style='color: red;'>❌ Reporter confirmation test FAILED!</p>";
    }
    
    echo "<hr>";
    echo "<h3>Test Summary</h3>";
    echo "<p>All email tests completed. Check your email inbox and the error logs for results.</p>";
    echo "<p><strong>Note:</strong> If tests fail, check:</p>";
    echo "<ul>";
    echo "<li>SMTP credentials in config.php</li>";
    echo "<li>Firewall/network restrictions</li>";
    echo "<li>Hostinger SMTP settings</li>";
    echo "<li>PHP error logs</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Email test failed with exception: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Check the configuration and try again.</p>";
}

echo "<hr>";
echo "<p><a href='public/admin/'>Go to Admin Panel</a> | <a href='public/'>Go to Public Site</a></p>";
?>
