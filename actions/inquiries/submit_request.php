<?php
require_once __DIR__ . '/../../helpers/security.php';
secure_session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';

require_post_request();
require_csrf_token();

$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$details = trim($_POST['details'] ?? '');

if ($fullName === '' || $email === '') {
    $_SESSION['access_error'] = 'Please provide both your name and email.';
    redirect(url('public/premium.php#contact'));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['access_error'] = 'Please enter a valid email address.';
    redirect(url('public/premium.php#contact'));
}

try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $userId = $user ? (int) $user['id'] : null;

    $stmt = $pdo->prepare('INSERT INTO private_requests (user_id, full_name, email, details) VALUES (?, ?, ?, ?)');
    $stmt->execute([$userId, $fullName, $email, $details]);
    $requestId = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare('SELECT id FROM users WHERE role_id IN (1, 2)');
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

    if ($userId) {
        create_notification(
            $userId,
            'Request Received',
            'Your private access request has been received and is under review.',
            'user_requests.php'
        );
    }

    $_SESSION['access_success'] = 'Your request has been received. Our concierge will review it and contact you with an access code if approved.';
} catch (Exception $e) {
    $_SESSION['access_error'] = 'There was a problem submitting your request. Please try again later.';
}

redirect(url('public/premium.php#contact'));
