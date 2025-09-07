<?php
require_once __DIR__ . '/../config/db.php';

// Get POST data
$uni_id = (int)($_POST['uni_id'] ?? 0);
$email = trim($_POST['email'] ?? '');

// Set JSON response header
header('Content-Type: application/json');

/**
 * Strict email domain verification function
 */
function verifyEmailDomain($email, $allowedDomains) {
    // Convert email to lowercase for comparison
    $email = strtolower(trim($email));
    
    // Check for admin domain first
    if (str_ends_with($email, '@antiragging.xyz')) {
        return true; // Admin emails bypass domain verification
    }
    
    // Extract domain from email
    $emailParts = explode('@', $email);
    if (count($emailParts) !== 2) {
        return false; // Invalid email format
    }
    
    $emailDomain = trim($emailParts[1]);
    
    // Check if domain is empty
    if (empty($emailDomain)) {
        return false;
    }
    
    // Normalize allowed domains
    $normalizedDomains = [];
    foreach ($allowedDomains as $domain) {
        $domain = strtolower(trim($domain));
        // Remove @ prefix if present
        $domain = ltrim($domain, '@');
        if (!empty($domain)) {
            $normalizedDomains[] = $domain;
        }
    }
    
    // Check exact match only
    return in_array($emailDomain, $normalizedDomains, true);
}

/**
 * Check if email exists in admin table
 */
function isAdminEmail($pdo, $email) {
    try {
        $email = strtolower(trim($email));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM ar_admin WHERE LOWER(TRIM(email)) = ?");
        $stmt->execute([$email]);
        return (int)$stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        error_log("Error checking admin email: " . $e->getMessage());
        return false;
    }
}

// Validate required data
if (!$uni_id || empty($email)) {
    echo json_encode(['valid' => false, 'message' => 'Missing required data.']);
    exit;
}

// Validate email format using PHP filter
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['valid' => false, 'message' => 'Invalid email format.']);
    exit;
}

// Additional email format validation
if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
    echo json_encode(['valid' => false, 'message' => 'Invalid email format.']);
    exit;
}

// Perform email verification
try {
    // Step 1: Check if email is in admin table or has admin domain
    if (isAdminEmail($pdo, $email) || str_ends_with(strtolower($email), '@antiragging.xyz')) {
        echo json_encode([
            'valid' => true,
            'message' => 'Admin verification successful.',
            'university' => 'Administrator'
        ]);
        exit;
    }
    
    // Step 2: Get university data and validate
    $stmt = $pdo->prepare("SELECT uni_name, uni_email_domain FROM university WHERE uni_id = ? AND uni_email_domain IS NOT NULL AND uni_email_domain != ''");
    $stmt->execute([$uni_id]);
    $university = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$university) {
        echo json_encode(['valid' => false, 'message' => 'University not found or email domain not configured.']);
        exit;
    }
    
    $uniName = $university['uni_name'];
    $configuredDomains = $university['uni_email_domain'];
    
    // Step 3: Parse and validate domains
    if (empty($configuredDomains)) {
        echo json_encode(['valid' => false, 'message' => 'University email domain not configured.']);
        exit;
    }
    
    // Split domains by comma, semicolon, or space
    $domainList = preg_split('/[,;\s]+/', $configuredDomains);
    $domainList = array_filter($domainList); // Remove empty elements
    
    if (empty($domainList)) {
        echo json_encode(['valid' => false, 'message' => 'No valid email domains configured for this university.']);
        exit;
    }
    
    // Step 4: Perform strict domain verification
    if (verifyEmailDomain($email, $domainList)) {
        echo json_encode([
            'valid' => true,
            'message' => 'Email verification successful.',
            'university' => $uniName
        ]);
    } else {
        // Format domains for error message
        $formattedDomains = array_map(function($domain) {
            return '@' . ltrim(trim($domain), '@');
        }, $domainList);
        
        $expectedDomains = implode(', ', $formattedDomains);
        
        echo json_encode([
            'valid' => false,
            'message' => "Email domain does not match {$uniName}'s authorized domain. Please use your official university email address."
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Database error in verification: " . $e->getMessage());
    echo json_encode(['valid' => false, 'message' => 'Database error occurred. Please try again later.']);
} catch (Exception $e) {
    error_log("General error in verification: " . $e->getMessage());
    echo json_encode(['valid' => false, 'message' => 'An error occurred during verification. Please try again.']);
}
?>