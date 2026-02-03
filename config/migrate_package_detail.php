<?php
// Database migration script

// Load database connection
require_once __DIR__ . '/db.php';

// Define migration steps
$alter = [
    "ALTER TABLE tours ADD COLUMN difficulty VARCHAR(50) DEFAULT NULL",
    "ALTER TABLE tours ADD COLUMN max_group VARCHAR(50) DEFAULT NULL",
    "ALTER TABLE tours ADD COLUMN highlights TEXT DEFAULT NULL",
    "ALTER TABLE tours ADD COLUMN category VARCHAR(50) DEFAULT NULL",
];

// Execute migration loop
foreach ($alter as $sql) {
    try {
        // Run SQL command
        $pdo->exec($sql);
        
        // Output success message
        echo "OK: " . substr($sql, 0, 60) . "\n";
        
    } catch (PDOException $e) {
        // Check duplicate column
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            // Skip existing column
            echo "Skip (already exists): " . substr($sql, 0, 50) . "\n";
        } else {
            // Output error message
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}

// Output completion message
echo "Done.\n";
