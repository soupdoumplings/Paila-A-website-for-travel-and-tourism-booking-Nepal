<?php
// Load required files
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $details = trim($_POST['details'] ?? '');
    
    // Validate input data
    if (empty($fullName) || empty($email)) {
        // Handle validation error
        $_SESSION['access_error'] = 'Please provide both your name and email.';
        // Redirect with error
    }

    try {
        // Identify existing user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        $userId = $user ? $user['id'] : null;

        // Store request record
        $stmt = $pdo->prepare("INSERT INTO private_requests (user_id, full_name, email, details) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $fullName, $email, $details]);
        $requestId = $pdo->lastInsertId();

        // Notify system admins
        $stmt = $pdo->prepare("SELECT id FROM users WHERE role_id IN (1, 2)");
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($admins as $adminId) {
            create_notification(
                $adminId, 
                'New Private Access Request', 
                "New request from $fullName.", 
                "admin/manage_requests.php?id=$requestId"
            );
        }

        // Notify requesting user
        if ($userId) {
            create_notification(
                $userId,
                'Request Received',
                'Your private access request has been received and is under review.',
                'user_requests.php'
            );
        }

        $_SESSION['access_success'] = 'Your request has been received! Our concierge will review it and contact you via email with an access code if approved.';
        // Redirect with success

    // Catch database errors
        $_SESSION['access_error'] = 'There was a problem submitting your request. Please try again later.';
        redirect(url('public/premium.php?error=db_error'));
    }
} else {
    // Redirect to premium
    redirect(url('public/premium.php'));
}
?>
