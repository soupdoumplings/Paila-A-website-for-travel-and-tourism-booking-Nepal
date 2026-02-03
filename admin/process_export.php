<?php
session_start();
$base = '../';
require_once $base . 'helpers/functions.php';
require_once $base . 'config/db.php';

// Verify login
require_login();

$format = isset($_GET['format']) ? $_GET['format'] : 'json';

try {
    // Get tours
    $stmt = $pdo->query("SELECT id, title, location, price, duration, description, category, difficulty, max_group, highlights, image, best_season, altitude_max, permit_requirements, itinerary, inclusions, exclusions, is_featured, created_at FROM tours ORDER BY id ASC");
    $tours = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($format === 'json') {
        // Generate JSON
        $filename = 'tours_export_' . date('Y-m-d_H-i-s') . '.json';
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo json_encode($tours, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
        
    } elseif ($format === 'csv') {
        // Generate CSV
        $filename = 'tours_export_' . date('Y-m-d_H-i-s') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Add headers
        if (!empty($tours)) {
            fputcsv($output, array_keys($tours[0]));
        }
        
        // Add rows
        foreach ($tours as $tour) {
            fputcsv($output, $tour);
        }
        
        fclose($output);
        exit;
        
    } elseif ($format === 'season_json') {
        // Generate seasonal
        // Check directory
        $data_dir = $base . 'data';
        if (!is_dir($data_dir)) {
            mkdir($data_dir, 0755, true);
        }
        
        // Sort tours
        $seasons = [
            'spring' => [],
            'autumn' => [],
            'monsoon' => [],
            'year_round' => []
        ];
        
        foreach ($tours as $tour) {
            $best_season = strtolower($tour['best_season'] ?? '');
            
            if (strpos($best_season, 'spring') !== false) {
                $seasons['spring'][] = $tour;
            }
            if (strpos($best_season, 'autumn') !== false) {
                $seasons['autumn'][] = $tour;
            }
            if (strpos($best_season, 'monsoon') !== false || strpos($best_season, 'winter') !== false) {
                $seasons['monsoon'][] = $tour;
            }
            if (strpos($best_season, 'year') !== false) {
                $seasons['year_round'][] = $tour;
            }
        }
        
        // Save files
        foreach ($seasons as $season_name => $season_tours) {
            if (empty($season_tours)) continue;
            
            $filename = $data_dir . '/' . $season_name . '_tours.json';
            $json_data = json_encode($season_tours, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            file_put_contents($filename, $json_data);
        }
        
        // Redirect success
        header('Location: export.php?success=season');
        exit;
    }
    
} catch (PDOException $e) {
    echo "Export Error: " . $e->getMessage();
    exit;
}
?>
