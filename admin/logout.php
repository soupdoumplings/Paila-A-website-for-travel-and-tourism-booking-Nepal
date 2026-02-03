<?php
require_once __DIR__ . '/../helpers/functions.php';
session_start();
session_unset();
session_destroy();
// Redirect to login
redirect(url('admin/login.php'));
?>
