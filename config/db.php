<?php
// Database connection settings

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
// Set database host
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');

// Set database user
define('DB_USER', getenv('DB_USER') ?: 'np03cs4a240006');

// Set database password
define('DB_PASS', getenv('DB_PASS') ?: 'SvoFQrw1PP');

// Set database name
define('DB_NAME', getenv('DB_NAME') ?: 'np03cs4a240006');

// Set site URL
define('BASE_URL', getenv('BASE_URL') ?: 'https://student.heraldcollege.edu.np/~np03cs4a240006/paila-traveling-2461787/');

// Set site name
define('SITE_NAME', 'PAILA');

// Init error flag
$db_error = null;

try {
    // Create DSN string
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
    
    // Establish PDO connection
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    
    // Set exception mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Catch connection error
    $db_error = $e->getMessage();
}
