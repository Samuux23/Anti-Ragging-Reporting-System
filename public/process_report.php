<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/email_helper.php';

// Function to generate a unique report code (matching existing format AR########)
function generateReportCode() {
    return 'AR' . str_pad(random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT);
}

// Function to sanitize filename
function sanitizeFilename($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    return substr($filename, 0, 100); // Limit length
}

// Function to get file extension
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// Initialize response
$errors = [];
$success = false;
$report_code = '';
$university_name = '';

// Debug: Log the request
error_log("process_report.php called - Method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));

try {
    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method. Please submit the form properly.');
    }
    
    // CSRF protection
    if (empty($_POST['csrf']) || $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')) {
        throw new Exception('Security validation failed. Please refresh the page and try again.');
    }
    
    // Get and validate form data
    $uni_id = (int)($_POST['uni_id'] ?? 0);
    $verified_email = trim($_POST['verified_email'] ?? '');
    $incident_type = trim($_POST['incident_type'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $incident_datetime = $_POST['incident_datetime'] ?? '';
    $details = trim($_POST['details'] ?? '');
    
    // Validation with user-friendly error messages
    if (!$uni_id) {
        $errors[] = 'Please select your university from the dropdown.';
    }
    
    if (!$verified_email || !filter_var($verified_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid verified university email is required. Please complete the verification step.';
    }
    
    if (!$incident_type) {
        $errors[] = 'Please select the type of incident you are reporting.';
    }
    
    if (!$department) {
        $errors[] = 'Please specify your department or faculty.';
    }
    
    if (!$incident_datetime) {
        $errors[] = 'Please provide the date and time when the incident occurred.';
    } else {
        // Validate datetime format and check if it's not in the future
        $datetime = DateTime::createFromFormat('Y-m-d\TH:i', $incident_datetime);
        if (!$datetime) {
            $errors[] = 'The date and time format is invalid. Please use the date picker.';
        } elseif ($datetime > new DateTime()) {
            $errors[] = 'The incident date cannot be in the future. Please check the date and time.';
        }
    }
    
    if (!$details) {
        $errors[] = 'Please provide a detailed description of the incident.';
    } elseif (strlen($details) < 20) {
        $errors[] = 'The description is too short. Please provide at least 20 characters describing the incident.';
    } elseif (strlen($details) > 1000) {
        $errors[] = 'The description is too long. Please limit your description to 1000 characters.';
    }
    
    // If there are validation errors, throw them
    if (!empty($errors)) {
        throw new Exception(implode('<br>', $errors));
    }
    
    // Get university information
    $stmt = $pdo->prepare("SELECT uni_name, uni_email_domain FROM university WHERE uni_id = ?");
    $stmt->execute([$uni_id]);
    $university = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$university) {
        throw new Exception('The selected university could not be found. Please refresh the page and try again.');
    }
    
    // Generate unique report code
    do {
        $report_code = generateReportCode();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reports WHERE report_code = ?");
        $stmt->execute([$report_code]);
        $exists = $stmt->fetchColumn() > 0;
    } while ($exists);
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Insert report into database (matching existing schema)
    $stmt = $pdo->prepare("
        INSERT INTO reports (
            report_code, incident_type, department, incident_datetime, 
            details, status, university_id, location, reporter_email, created_at
        ) VALUES (?, ?, ?, ?, ?, 'Submitted', ?, ?, ?, NOW())
    ");
    
    // Debug: Log the values being inserted
    error_log("Inserting report with values: " . json_encode([
        'report_code' => $report_code,
        'incident_type' => $incident_type,
        'department' => $department,
        'incident_datetime' => $incident_datetime,
        'details' => substr($details, 0, 100) . '...',
        'uni_id' => $uni_id,
        'location' => $location ?: null,
        'verified_email' => $verified_email
    ]));
    
    $stmt->execute([
        $report_code,
        $incident_type,
        $department,
        $incident_datetime,
        $details,
        $uni_id,
        $location ?: null,
        $verified_email
    ]);
    
    $report_id = $pdo->lastInsertId();
    
    // Debug: Log successful insertion
    error_log("Report inserted successfully with ID: " . $report_id);
    
    // Insert initial status history (guard against accidental duplicate submissions)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM status_history WHERE report_id = ? AND old_status = '' AND new_status = 'Submitted'");
    $stmt->execute([$report_id]);
    if ((int)$stmt->fetchColumn() === 0) {
        $stmt = $pdo->prepare("INSERT INTO status_history (report_id, old_status, new_status, changed_at) VALUES (?, '', 'Submitted', NOW())");
        $stmt->execute([$report_id]);
    }
    
    // Create process timeline entries
    $process_steps = [
        ['Report Submission', 'Anonymous report submitted by user', 'completed', 'System'],
        ['Initial Review', 'Report reviewed by admin team', 'pending', 'Admin Team'],
        ['University Notification', 'Report forwarded to university authorities', 'pending', 'University Authorities'],
        ['Investigation', 'Investigation initiated by university', 'pending', 'Investigation Team'],
        ['Action Plan', 'Action plan developed and implemented', 'pending', 'University Authorities'],
        ['Resolution', 'Case resolved and closed', 'pending', 'System']
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO process_timeline (report_id, step_name, step_description, status, assigned_to, started_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    foreach ($process_steps as $step) {
        $started_at = $step[2] === 'completed' ? 'NOW()' : 'NULL';
        $stmt->execute([$report_id, $step[0], $step[1], $step[2], $step[3]]);
    }
    
    // Mark first step as completed
    $stmt = $pdo->prepare("
        UPDATE process_timeline 
        SET status = 'completed', completed_at = NOW() 
        WHERE report_id = ? AND step_name = 'Report Submission'
    ");
    $stmt->execute([$report_id]);
    
    // Handle file uploads
    // Prefer central uploads root from config and ensure subfolder exists
    $upload_dir = (isset($uploadsRoot) ? $uploadsRoot : (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads')) . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR;
    $uploaded_files = [];

    if (!is_dir($upload_dir)) {
        // Use recursive directory creation; permissions ignored on Windows
        if (!@mkdir($upload_dir, 0775, true) && !is_dir($upload_dir)) {
            error_log('Failed to create upload directory: ' . $upload_dir);
        }
    }

    if (!empty($_FILES['evidence']['name'][0])) {
        $allowed_extensions = [
            // Images
            'jpg', 'jpeg', 'png', 'webp',
            // Documents
            'pdf', 'doc', 'docx', 'txt', 'rtf',
            // Audio
            'mp3', 'wav',
            // Video
            'mp4', 'avi', 'mov'
        ];
        $max_file_size = 50 * 1024 * 1024; // 50MB to match config
        $max_files = 10; // Increased file limit for multiple file types
        
        $file_count = count($_FILES['evidence']['name']);
        
        if ($file_count > $max_files) {
            throw new Exception("You can upload a maximum of {$max_files} files. Please remove some files and try again.");
        }
        
        for ($i = 0; $i < $file_count; $i++) {
            if ($_FILES['evidence']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            
            if ($_FILES['evidence']['error'][$i] !== UPLOAD_ERR_OK) {
                $upload_errors = [
                    UPLOAD_ERR_INI_SIZE => 'File is too large (server limit exceeded)',
                    UPLOAD_ERR_FORM_SIZE => 'File is too large (form limit exceeded)',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
                ];
                
                $error_code = $_FILES['evidence']['error'][$i];
                $error_msg = $upload_errors[$error_code] ?? 'Unknown upload error';
                throw new Exception("File upload failed: {$error_msg} (File " . ($i + 1) . ")");
            }
            
            $original_name = $_FILES['evidence']['name'][$i];
            $file_size = $_FILES['evidence']['size'][$i];
            $tmp_name = $_FILES['evidence']['tmp_name'][$i];
            
            // Validate file size
            if ($file_size > $max_file_size) {
                throw new Exception("The file '{$original_name}' is too large. Maximum file size is 10MB.");
            }
            
            // Validate file extension
            $extension = getFileExtension($original_name);
            if (!in_array($extension, $allowed_extensions)) {
                throw new Exception("The file type '{$extension}' is not allowed. Please upload only JPG, PNG, WEBP, PDF, DOC, DOCX, TXT, RTF, MP3, WAV, MP4, AVI, or MOV files.");
            }
            
            // Generate unique filename using report code
            $filename = $report_code . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
            $file_path = $upload_dir . $filename;
            
            // Move uploaded file
            if (!@move_uploaded_file($tmp_name, $file_path)) {
                error_log('move_uploaded_file failed to ' . $file_path . ' (tmp: ' . $tmp_name . ')');
                throw new Exception("Failed to save the file '{$original_name}'. Please try again.");
            }
            
            // Get MIME type (robust across environments)
            $mime_type = 'application/octet-stream';
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                if ($finfo) {
                    $detected = finfo_file($finfo, $file_path);
                    if ($detected) { $mime_type = $detected; }
                    finfo_close($finfo);
                }
            } else {
                // Fallback by extension
                $extToMime = [
                    // Images
                    'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp',
                    // Documents
                    'pdf' => 'application/pdf', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'txt' => 'text/plain', 'rtf' => 'application/rtf',
                    // Audio
                    'mp3' => 'audio/mpeg', 'wav' => 'audio/wav',
                    // Video
                    'mp4' => 'video/mp4', 'avi' => 'video/x-msvideo', 'mov' => 'video/quicktime'
                ];
                $mime_type = $extToMime[$extension] ?? 'application/octet-stream';
            }
            
            // Additional MIME type validation for security
            $allowed_mime_types = [
                // Images
                'image/jpeg', 'image/png', 'image/webp',
                // Documents
                'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain', 'application/rtf',
                // Audio
                'audio/mpeg', 'audio/wav', 'audio/mp3',
                // Video
                'video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo'
            ];
            
            if (!in_array($mime_type, $allowed_mime_types)) {
                // Remove the uploaded file
                @unlink($file_path);
                throw new Exception("The file '{$original_name}' has an invalid MIME type ({$mime_type}). Please upload a valid file.");
            }
            
            // Insert file record into database (matching existing schema)
            $stmt = $pdo->prepare("
                INSERT INTO attachments (report_id, original_name, stored_name, mime_type, size_bytes, uploaded_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$report_id, $original_name, $filename, $mime_type, $file_size]);
            
            $uploaded_files[] = $original_name;
        }
    } elseif (!empty($_FILES['evidence']['name'][0]) && !$upload_dir) {
        // Log that file uploads were skipped due to directory issues
        error_log("File uploads skipped due to upload directory issues");
    }
    
    // Commit transaction
    $pdo->commit();
    
    $success = true;
    $university_name = $university['uni_name'];
    
    // Send notification emails (don't fail if emails fail)
    try {
        global $emailHelper;
        
        $adminSent = $emailHelper->sendAdminNotification($report_code, $incident_type, $university['uni_name'], $verified_email);
        $reporterSent = $emailHelper->sendReporterConfirmation($verified_email, $report_code, $university['uni_name']);
        
        // If at least one notification was sent, mark 'University Notification' step as completed
        if ($adminSent || $reporterSent) {
            $stmt = $pdo->prepare("UPDATE process_timeline SET status = 'completed', completed_at = NOW() WHERE report_id = ? AND step_name = 'University Notification'");
            $stmt->execute([$report_id]);
        }
        
    } catch (Exception $e) {
        // Log email error but don't fail the report submission
        error_log("Email notification error: " . $e->getMessage());
    }
    
    // Clear CSRF token after successful submission
    unset($_SESSION['csrf']);
    
} catch (PDOException $e) {
    // Rollback transaction on database error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Database error in process_report.php: " . $e->getMessage());
    
    // Check for specific database errors
    if ($e->getCode() == '23000') {
        $errors[] = 'A duplicate entry was detected. Please try again.';
    } elseif (strpos($e->getMessage(), 'foreign key constraint') !== false) {
        $errors[] = 'Invalid university selection. Please refresh the page and try again.';
    } else {
        $errors[] = 'A database error occurred while saving your report. Please try again in a few minutes.';
    }
    
} catch (Exception $e) {
    // Rollback transaction on any error
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Error in process_report.php: " . $e->getMessage());
    $errors[] = $e->getMessage();
}

// Handle both AJAX and regular form submissions
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    // AJAX request - return JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Report submitted successfully!' : implode('<br>', $errors),
        'report_code' => $report_code,
        'university' => $university_name,
        'uploaded_files' => $uploaded_files ?? []
    ]);
} else {
    // Regular form submission - redirect with message
    if ($success) {
        $_SESSION['success_message'] = 'Your report has been submitted successfully!';
        $_SESSION['report_code'] = $report_code;
        $_SESSION['university_name'] = $university_name;
        $_SESSION['uploaded_files'] = $uploaded_files ?? [];
        header('Location: report_success.php'); // Redirect to success page
    } else {
        $_SESSION['error_message'] = implode('<br>', $errors);
        // Check if the request came from submit_simple.php
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (strpos($referer, 'submit_simple.php') !== false) {
            header('Location: submit_simple.php');
        } else {
            header('Location: submit.php');
        }
    }
    exit;
}
?>