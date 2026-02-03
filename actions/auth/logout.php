<?php
// User logout handler

// Load required files
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';

// Initialize session state
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear session variables
$_SESSION = array();

// Destroy active session
session_destroy();

// Redirect to login
redirect(url('public/authentication/login.php'));

// Stop script execution
exit();
?>
