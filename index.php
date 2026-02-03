<?php
// Application entry point

// Load database connection
require_once 'config/db.php';

// Load helper functions
require_once 'helpers/functions.php';

// Render home page
include 'public/home.php';
