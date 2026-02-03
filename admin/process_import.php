<?php
session_start();
$base = '../';
require_once $base . 'helpers/functions.php';
require_once $base . 'config/db.php';

// Check user access
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: import.php');
    exit;
}

$import_mode = isset($_POST['import_mode']) ? $_POST['import_mode'] : 'skip';
$imported = 0;
$skipped = 0;
$updated = 0;
$errors = [];

try {
    // Check file upload
    if (!isset($_FILES['jsonFile']) || $_FILES['jsonFile']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error');
    }
    
    // Read JSON contents
    $jsonContent = file_get_contents($_FILES['jsonFile']['tmp_name']);
    $tours = json_decode($jsonContent, true);
    
    if ($tours === null) {
        throw new Exception('Invalid JSON format');
    }
    
    if (!is_array($tours)) {
        throw new Exception('JSON must contain an array of tours');
    }
    
    // Delete existing tours
    if ($import_mode === 'replace') {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        $pdo->exec('DELETE FROM bookings');
        $pdo->exec('DELETE FROM tours');
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    // Check schema column
    try {
        $pdo->query("SELECT is_featured FROM tours LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE tours ADD COLUMN is_featured TINYINT(1) DEFAULT 0 AFTER exclusions");
    }
    
    // Process each tour
    foreach ($tours as $tour) {
        try {
            // Map field names
            $title = $tour['title'] ?? ($tour['name'] ?? '');
            $location = $tour['location'] ?? ($tour['destination_name'] ?? '');
            $price = $tour['price'] ?? 0;
            $image = $tour['image'] ?? ($tour['image_url'] ?? '');
            $inclusions = $tour['inclusions'] ?? ($tour['includes'] ?? '');
            $exclusions = $tour['exclusions'] ?? ($tour['excludes'] ?? '');
            $is_featured = $tour['is_featured'] ?? 0;
            $created_by = $_SESSION['admin_id'] ?? ($_SESSION['user_id'] ?? null);

            // Check required fields
            if (empty($title) || empty($location)) {
                $errors[] = "Skipped tour: Missing title or location";
                $skipped++;
                continue;
            }
            
            // Find existing tour
            $stmt = $pdo->prepare('SELECT id FROM tours WHERE title = ?');
            $stmt->execute([$title]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                if ($import_mode === 'skip') {
                    $skipped++;
                    continue;
                } elseif ($import_mode === 'update') {
                    // Update existing tour
                    $sql = "UPDATE tours SET 
                        location = ?, 
                        price = ?, 
                        duration = ?, 
                        description = ?, 
                        category = ?, 
                        difficulty = ?, 
                        max_group = ?, 
                        highlights = ?, 
                        image = ?,
                        best_season = ?,
                        altitude_max = ?,
                        permit_requirements = ?,
                        itinerary = ?,
                        inclusions = ?,
                        exclusions = ?,
                        is_featured = ?
                        WHERE id = ?";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $location,
                        $price,
                        $tour['duration'] ?? '',
                        $tour['description'] ?? '',
                        $tour['category'] ?? '',
                        $tour['difficulty'] ?? '',
                        $tour['max_group'] ?? null,
                        $tour['highlights'] ?? '',
                        $image,
                        $tour['best_season'] ?? null,
                        $tour['altitude_max'] ?? null,
                        $tour['permit_requirements'] ?? null,
                        $tour['itinerary'] ?? null,
                        $inclusions,
                        $exclusions,
                        $is_featured,
                        $existing['id']
                    ]);
                    $updated++;
                    continue;
                }
            }
            
            // Insert new tour
            $sql = "INSERT INTO tours (
                title, location, price, duration, description, category, 
                difficulty, max_group, highlights, image, best_season,
                altitude_max, permit_requirements, itinerary, inclusions, exclusions,
                is_featured, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $title,
                $location,
                $price,
                $tour['duration'] ?? '',
                $tour['description'] ?? '',
                $tour['category'] ?? '',
                $tour['difficulty'] ?? '',
                $tour['max_group'] ?? null,
                $tour['highlights'] ?? '',
                $image,
                $tour['best_season'] ?? null,
                $tour['altitude_max'] ?? null,
                $tour['permit_requirements'] ?? null,
                $tour['itinerary'] ?? null,
                $inclusions,
                $exclusions,
                $is_featured,
                $created_by
            ]);
            $imported++;
            
        } catch (Exception $e) {
            $errors[] = "Error importing " . ($tour['title'] ?? 'Unknown') . ": " . $e->getMessage();
        }
    }
    
    // Build result message
    $message = "Import completed! ";
    if ($imported > 0) $message .= "$imported tours imported. ";
    if ($updated > 0) $message .= "$updated tours updated. ";
    if ($skipped > 0) $message .= "$skipped tours skipped. ";
    
    if (!empty($errors)) {
        $_SESSION['import_errors'] = $errors;
        $message .= count($errors) . " errors occurred.";
    }
    
    header('Location: import.php?message=' . urlencode($message));
    exit;
    
} catch (Exception $e) {
    header('Location: import.php?error=' . urlencode($e->getMessage()));
    exit;
}
?>
