<?php
// Load helper functions
require_once __DIR__ . '/../helpers/functions.php';
// Initialize session state
session_start();
// Clear session data
session_unset();
// Destroy session file
session_destroy();
// Redirect to login
redirect(url('admin/login.php'));
?>
