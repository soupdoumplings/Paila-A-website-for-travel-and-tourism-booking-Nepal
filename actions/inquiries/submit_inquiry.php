<?php
// Start user session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Load required files
require_once '../../config/db.php';
require_once '../../helpers/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validate contact inputs
    if (empty($name) || empty($email) || empty($message)) {
        $_SESSION['error'] = "Name, Email, and Message are required.";
        redirect(url('public/home.php#contact'));
    }

    try {
        if ($pdo) {
            // Insert inquiry record
            $stmt = $pdo->prepare("INSERT INTO inquiries (name, email, phone, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $message]);
            
            // Notify system admins
            try {
                $adminStmt = $pdo->query("SELECT id FROM users WHERE role_id IN (1, 2)");
                $admins = $adminStmt->fetchAll(PDO::FETCH_COLUMN);

                foreach ($admins as $adminId) {
                    create_notification(
                        $adminId,
                        'New Inquiry Received',
                        "You have a new message from $name.",
                        "admin/manage_inquiries.php"
                    );
                }
            // Handle notification failure
            
            $_SESSION['success'] = "Thank you! Your message has been sent. We will contact you soon.";
        } else {
            $_SESSION['error'] = "Database connection error.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Submission failed: " . $e->getMessage();
    }

    // Redirect to home
    redirect(url('public/home.php#contact'));
} else {
    redirect(url('public/home.php'));
}
