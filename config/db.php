<?php
// Database configuration file

// localhost connection
// // Define database host
// define('DB_HOST', getenv('DB_HOST') ?: 'localhost');

// // Define database user
// define('DB_USER', getenv('DB_USER') ?: 'root');

// // Define database password
// define('DB_PASS', getenv('DB_PASS') ?: '');

// // Define database name
// define('DB_NAME', getenv('DB_NAME') ?: 'nepal_tours');

// // Define base url
// define('BASE_URL', getenv('BASE_URL') !== false ? getenv('BASE_URL') : '');

// Server connection
// Define database host
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');

// Define database user
define('DB_USER', getenv('DB_USER') ?: 'np03cs4a240006');

// Define database password
define('DB_PASS', getenv('DB_PASS') ?: 'SvoFQrw1PP');

// Define database name
define('DB_NAME', getenv('DB_NAME') ?: 'np03cs4a240006');

// Define base url
define('BASE_URL', getenv('BASE_URL') ?: 'https://student.heraldcollege.edu.np/~np03cs4a240006/paila-traveling-2461787/');

// Define site name
define('SITE_NAME', 'PAILA');

// Initialize error variable
$db_error = null;

try {
    // Set connection string
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
    
    // Create new connection
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    
    // Set error mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set fetch mode
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Capture connection error
    $db_error = $e->getMessage();
}
