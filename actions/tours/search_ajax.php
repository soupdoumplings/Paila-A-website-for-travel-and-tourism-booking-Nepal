<?php
require_once __DIR__ . '/../../config/db.php';
header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit();
}

if (!isset($pdo) || isset($db_error)) {
    echo json_encode([]);
    exit();
}

// Run search query
$stmt = $pdo->prepare("SELECT id, title, location, price, duration, image, category FROM tours WHERE title LIKE :query OR location LIKE :query OR description LIKE :query ORDER BY is_featured DESC, id DESC LIMIT 5");
$stmt->execute(['query' => '%' . $query . '%']);
$results = $stmt->fetchAll();

// Output JSON response
echo json_encode($results, JSON_UNESCAPED_SLASHES);
?>
