<?php
// Database migration script

// Load DB connection
require_once __DIR__ . '/db.php';

// Define schema changes
$alter = [
    "ALTER TABLE tours ADD COLUMN difficulty VARCHAR(50) DEFAULT NULL",
    "ALTER TABLE tours ADD COLUMN max_group VARCHAR(50) DEFAULT NULL",
    "ALTER TABLE tours ADD COLUMN highlights TEXT DEFAULT NULL",
    "ALTER TABLE tours ADD COLUMN category VARCHAR(50) DEFAULT NULL",
];

// Run migration loop
foreach ($alter as $sql) {
    try {
        // Execute SQL query
        $pdo->exec($sql);
        
        // Print success message
        echo "OK: " . substr($sql, 0, 60) . "\n";
        
    } catch (PDOException $e) {
        // Handle duplicate error
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            // Skip existing column
            echo "Skip (already exists): " . substr($sql, 0, 50) . "\n";
        } else {
            // Print error message
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}

// Print finish message
echo "Done.\n";
