<?php
// User logout handler

// Load dependencies
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear session data
$_SESSION = array();

// Destroy session file
session_destroy();

// Redirect to login
redirect(url('public/authentication/login.php'));

// Exit script
exit();
?>
