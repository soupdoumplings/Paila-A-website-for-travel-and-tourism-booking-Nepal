<?php
// Premium access validation

// Initialize user session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load required files
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';

// Check POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['access_code'])) {
    redirect(url('public/premium.php'));
}

// Retrieve access code
$access_code = trim(strtoupper($_POST['access_code']));

// Validate code input
if (empty($access_code)) {
    $_SESSION['access_error'] = 'Please enter an access code.';
    redirect(url('public/premium.php'));
}

try {
    // Verify approved code
    $stmt = $pdo->prepare("
        SELECT pr.id, pr.user_id, pr.full_name, pr.email, pr.status
        FROM private_requests pr
        WHERE pr.access_code = ? AND pr.status = 'approved'
    ");
    $stmt->execute([$access_code]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($request) {
        // Enable premium access
        $_SESSION['premium_access'] = true;
        $_SESSION['access_code'] = $access_code;
        $_SESSION['premium_request_id'] = $request['id'];
        
        // Check user login
        if (!is_logged_in() && $request['user_id']) {
            $_SESSION['access_success'] = 'Access code validated! Please log in to continue.';
            redirect(url('public/authentication/login.php'));
        }
        
        // Redirect to premium
        $_SESSION['access_success'] = 'Access granted! You can now view and book exclusive tours.';
        redirect(url('public/premium.php#holdings'));
        
    } else {
        // Check existing codes
        $stmt2 = $pdo->prepare("SELECT status FROM private_requests WHERE access_code = ?");
        $stmt2->execute([$access_code]);
        $exists = $stmt2->fetch(PDO::FETCH_ASSOC);
        
        if ($exists) {
            // Handle code status
            if ($exists['status'] === 'pending') {
                $_SESSION['access_error'] = 'Your request is still pending review. Please wait for approval.';
            } else {
                $_SESSION['access_error'] = 'This access code is no longer valid.';
            }
        } else {
            // Process invalid code
            $_SESSION['access_error'] = 'Invalid access code. Please check and try again.';
        }
        
        // Return to premium
        redirect(url('public/premium.php'));
    }
    
} catch (Exception $e) {
    // Log exception error
    $_SESSION['access_error'] = 'An error occurred. Please try again.';
    redirect(url('public/premium.php'));
}
?>
