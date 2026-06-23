<?php
require_once __DIR__ . '/../../helpers/security.php';
secure_session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';

require_post_request();
require_csrf_token();

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $message === '') {
    $_SESSION['error'] = 'Name, email, and message are required.';
    redirect(url('index.php#contact'));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Please enter a valid email address.';
    redirect(url('index.php#contact'));
}

if (strlen($message) < 10) {
    $_SESSION['error'] = 'Please tell us a little more about your trip.';
    redirect(url('index.php#contact'));
}

try {
    $stmt = $pdo->prepare('INSERT INTO inquiries (name, email, phone, message) VALUES (?, ?, ?, ?)');
    $stmt->execute([$name, $email, $phone, $message]);

    try {
        $adminStmt = $pdo->query('SELECT id FROM users WHERE role_id IN (1, 2)');
        $admins = $adminStmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($admins as $adminId) {
            create_notification(
                $adminId,
                'New Inquiry Received',
                "You have a new message from $name.",
                'admin/manage_inquiries.php'
            );
        }
    } catch (Exception $e) {
        // Inquiry is saved; notification failure should not block the guest.
    }

    $_SESSION['success'] = 'Thank you. Your message has been sent, and our team will contact you soon.';
} catch (Exception $e) {
    $_SESSION['error'] = 'We could not send your message right now. Please try again later.';
}

redirect(url('index.php#contact'));
