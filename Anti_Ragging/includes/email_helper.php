<?php
// Email Helper Class for Anti-Ragging Portal
// Uses PHPMailer with SMTP for reliable email delivery

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailHelper {
    private $mailer;
    private $lastError = '';
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->setupSMTP();
    }
    
    private function setupSMTP() {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = SMTP_HOST;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = SMTP_USERNAME;
            $this->mailer->Password = SMTP_PASSWORD;
            $this->mailer->Port = SMTP_PORT;
            // Choose encryption based on port (465 -> SMTPS, 587/25 -> STARTTLS/none)
            if ((int)SMTP_PORT === 465) {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            // Allow self-signed if needed (common on shared hosts)
            $this->mailer->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];
            // Optional debug
            if (defined('SMTP_DEBUG') && SMTP_DEBUG) {
                $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
                $this->mailer->Debugoutput = function ($str, $level) {
                    error_log("PHPMailer[$level]: $str");
                };
            }
            
            // Default settings
            $this->mailer->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';
            
        } catch (Exception $e) {
            $this->lastError = "Email setup failed: " . $e->getMessage();
            error_log($this->lastError);
            throw new Exception("Email configuration error");
        }
    }
    
    /**
     * Send a simple email
     */
    public function sendEmail($to, $subject, $message, $fromName = null, $fromEmail = null) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Set sender
            if ($fromName && $fromEmail) {
                $this->mailer->setFrom($fromEmail, $fromName);
            } else {
                $this->mailer->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            }
            
            // Add recipient
            $this->mailer->addAddress($to);
            
            // Set content
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $message;
            $this->mailer->AltBody = strip_tags($message);
            
            // Send email
            $result = $this->mailer->send();
            
            if ($result) {
                error_log("Email sent successfully to: $to");
                $this->lastError = '';
                return true;
            } else {
                $this->lastError = $this->mailer->ErrorInfo ?: 'Unknown mailer error';
                error_log("Failed to send email to: $to - " . $this->lastError);
                return false;
            }
            
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("Email sending failed: " . $this->lastError);
            return false;
        }
    }
    
    /**
     * Send email with PDF attachment
     */
    public function sendEmailWithPDF($to, $subject, $message, $pdfContent, $pdfFilename, $fromName = null, $fromEmail = null) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Set sender
            if ($fromName && $fromEmail) {
                $this->mailer->setFrom($fromEmail, $fromName);
            } else {
                $this->mailer->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            }
            
            // Add recipient
            $this->mailer->addAddress($to);
            
            // Set content
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $message;
            $this->mailer->AltBody = strip_tags($message);
            
            // Add PDF attachment
            $this->mailer->addStringAttachment($pdfContent, $pdfFilename, 'base64', 'application/pdf');
            
            // Send email
            $result = $this->mailer->send();
            
            if ($result) {
                error_log("Email with PDF sent successfully to: $to");
                $this->lastError = '';
                return true;
            } else {
                $this->lastError = $this->mailer->ErrorInfo ?: 'Unknown mailer error';
                error_log("Failed to send email with PDF to: $to - " . $this->lastError);
                return false;
            }
            
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("Email with PDF sending failed: " . $this->lastError);
            return false;
        }
    }
    
    /**
     * Send notification email to admin
     */
    public function sendAdminNotification($reportCode, $incidentType, $universityName, $reporterEmail) {
        $subject = "New Anonymous Report Submitted - Code: {$reportCode}";
        
        $message = "
        <html>
        <head>
            <title>New Anonymous Report</title>
        </head>
        <body>
            <h2>New Anonymous Report Submitted</h2>
            <p><strong>Report Code:</strong> {$reportCode}</p>
            <p><strong>Incident Type:</strong> " . ucwords(str_replace('_', ' ', $incidentType)) . "</p>
            <p><strong>University:</strong> {$universityName}</p>
            <p><strong>Submitted at:</strong> " . date('Y-m-d H:i:s') . "</p>
            <p><strong>Reporter Email:</strong> {$reporterEmail}</p>
            
            <p>Please log into the admin panel to review the full report details.</p>
            
            <p><em>This is an automated message from the Anonymous Ragging Report System.</em></p>
        </body>
        </html>
        ";
        
        return $this->sendEmail('admin@raggingreport.com', $subject, $message);
    }
    
    /**
     * Send confirmation email to reporter
     */
    public function sendReporterConfirmation($email, $reportCode, $universityName) {
        $subject = "Report Submitted Successfully - Code: {$reportCode}";
        
        $message = "
        <html>
        <head>
            <title>Report Confirmation</title>
        </head>
        <body>
            <h2>Your Anonymous Report Has Been Submitted</h2>
            
            <p>Thank you for submitting your anonymous report. Your courage in speaking up helps make our educational institutions safer for everyone.</p>
            
            <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3>Report Details:</h3>
                <p><strong>Report Code:</strong> <span style='background: #007bff; color: white; padding: 4px 8px; border-radius: 4px; font-family: monospace;'>{$reportCode}</span></p>
                <p><strong>University:</strong> {$universityName}</p>
                <p><strong>Submitted on:</strong> " . date('F j, Y \a\t g:i A') . "</p>
            </div>
            
            <h3>What Happens Next?</h3>
            <ul>
                <li>Your report will be reviewed by authorized personnel within 24-48 hours</li>
                <li>An investigation will be initiated based on the information provided</li>
                <li>You can check the status of your report using the report code</li>
                <li>Updates will be sent to this email address (your identity remains anonymous)</li>
            </ul>
            
            <h3>Important Notes:</h3>
            <ul>
                <li>Keep your report code <strong>{$reportCode}</strong> safe for future reference</li>
                <li>Your identity and personal information remain completely confidential</li>
                <li>If you have additional information, you can submit another report referencing this code</li>
            </ul>
            
            <div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 4px; margin: 20px 0;'>
                <strong>Emergency:</strong> If you are in immediate danger, please contact campus security or local authorities immediately.
            </div>
            
            <p>Thank you for helping create a safer educational environment.</p>
            
            <p><em>This is an automated message from the Anonymous Ragging Report System. Please do not reply to this email.</em></p>
        </body>
        </html>
        ";
        
        return $this->sendEmail($email, $subject, $message);
    }
    
    /**
     * Send email to authorities about a report
     */
    public function sendAuthorityEmail($to, $subject, $message, $pdfContent = null, $pdfFilename = null, $fromName = null, $fromEmail = null) {
        if ($pdfContent && $pdfFilename) {
            return $this->sendEmailWithPDF($to, $subject, $message, $pdfContent, $pdfFilename, $fromName, $fromEmail);
        } else {
            return $this->sendEmail($to, $subject, $message, $fromName, $fromEmail);
        }
    }

    public function getLastError() {
        return $this->lastError;
    }
}

// Create a global instance
$emailHelper = new EmailHelper();
?>
