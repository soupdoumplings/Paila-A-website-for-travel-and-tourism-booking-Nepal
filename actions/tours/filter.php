<?php
// Load required files
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../helpers/functions.php';

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$min_price = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 1000000;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$duration = isset($_GET['duration']) ? $_GET['duration'] : '';
$location_filter = isset($_GET['location']) ? trim($_GET['location']) : '';

// Build base query
$query = "SELECT * FROM tours WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (title LIKE ? OR location LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $query .= " AND category = ?";
    $params[] = $category;
}

if ($duration) {
    if ($duration == 'short') { // 1-3 days
        $query .= " AND (duration REGEXP '^[1-3][[:space:]]' OR duration REGEXP '^[1-3]$')";
    } elseif ($duration == 'medium') { // 4-7 days
         $query .= " AND (duration REGEXP '^[4-7][[:space:]]' OR duration REGEXP '^[4-7]$')";
    } elseif ($duration == 'long') { // 8+ days
         $query .= " AND (duration REGEXP '^([8-9]|[1-9][0-9])[[:space:]]' OR duration REGEXP '^([8-9]|[1-9][0-9])$')";
    }
}

if ($location_filter) {
    $query .= " AND location LIKE ?";
    $params[] = "%$location_filter%";
}

// Apply price range
$query .= " AND price >= ? AND price <= ?";
$params[] = $min_price;
$params[] = $max_price;

// Apply sort order
switch ($sort) {
    case 'price_asc':
        $query .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY price DESC";
        break;
    case 'name_asc':
        $query .= " ORDER BY title ASC";
        break;
    default:
        $query .= " ORDER BY created_at DESC";
        break;
}

$tours = [];
// Execute search query
try {
    if ($pdo) {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $tours = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) { $tours = []; }

// Setup image map
$imageMap = [
    'trekking' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=800&q=80',
    'cultural' => 'https://images.unsplash.com/photo-1585409677983-0f6c41ca9c3b?w=800&q=80',
    'culture' => 'https://images.unsplash.com/photo-1585409677983-0f6c41ca9c3b?w=800&q=80',
    'adventure' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80',
    'family' => 'https://images.unsplash.com/photo-1533130061792-64b345e4a833?w=800&q=80',
    'luxury' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=800&q=80',
    'weekend' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80',
    'budget' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=800&q=80'
];

// Set JSON header
header('Content-Type: application/json');

// Buffer HTML output
ob_start();
if (count($tours) > 0):
    foreach ($tours as $tour):
        $cat = isset($tour['category']) && $tour['category'] ? $tour['category'] : 'trekking';
        $tourImg = !empty($tour['image']) ? $tour['image'] : '';
        $img = '';

        if ($tourImg) {
            if (filter_var($tourImg, FILTER_VALIDATE_URL)) {
                $img = $tourImg;
            } else {
                // Validate local image
                $localPath = __DIR__ . '/../public/uploads/' . $tourImg;
                if (file_exists($localPath)) {
                    $img = url('public/uploads/' . $tourImg);
                } else {
                    $img = $imageMap[$cat] ?? $imageMap['trekking'];
                }
            }
        } else {
            $img = $imageMap[$cat] ?? $imageMap['trekking'];
        }
        $cardHref = url('public/package_detail/index.php?id=' . (int)$tour['id']);
        ?>
        <a href="<?php echo e($cardHref); ?>" class="collection-card hover-zoom-card">
            <div class="collection-card-image-container">
                <img src="<?php echo e($img); ?>" alt="<?php echo e($tour['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                <?php if($tour['is_featured']): ?>
                    <span style="position: absolute; top: 1rem; right: 1rem; background: var(--color-amber-400); color: black; font-size: 0.7rem; font-weight: 700; padding: 0.25rem 0.5rem; border-radius: 4px;">FEATURED</span>
                <?php endif; ?>
            </div>
            <div class="collection-card-content">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                    <h3 style="font-family: var(--font-serif); font-size: 1.25rem; line-height: 1.3; color: var(--color-stone-900); margin: 0;"><?php echo e($tour['title']); ?></h3>
                </div>
                
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;">
                    <span style="font-size: 0.75rem; color: var(--color-stone-500); background: #f3f4f6; padding: 2px 8px; border-radius: 4px;">
                        <i class="fa-regular fa-clock"></i> <?php echo e($tour['duration']); ?>
                    </span>
                    <span style="font-size: 0.75rem; color: var(--color-stone-500); background: #f3f4f6; padding: 2px 8px; border-radius: 4px;">
                        <i class="fa-solid fa-layer-group"></i> <?php echo ucfirst($cat); ?>
                    </span>
                </div>

                <div style="padding-top: 1rem; border-top: 1px dashed var(--color-stone-200); display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
                    <div style="font-size: 0.85rem; color: var(--color-stone-500);">
                        <i class="fa-solid fa-location-dot"></i> <?php echo e($tour['location']); ?>
                    </div>
                    <div style="font-weight: 700; color: var(--color-emerald-700);">
                        Rs <?php echo number_format((float)$tour['price'], 0); ?>
                    </div>
                </div>
            </div>
        </a>
    <?php endforeach;
else: ?>
    <div class="no-results-box" style="grid-column: 1 / -1; text-align: center; padding: 4rem 2rem;">
        <div style="font-size: 3rem; color: var(--color-stone-300); margin-bottom: 1rem;">
            <i class="fa-solid fa-mountain-sun"></i>
        </div>
        <h3 style="font-family: var(--font-serif); font-size: 1.5rem; margin-bottom: 0.5rem;">No journeys found</h3>
        <p style="color: var(--color-stone-500); margin-bottom: 1.5rem;">We couldn't find any tours matching your criteria.</p>
        <a href="collection.php" class="btn btn-primary" style="padding: 0.5rem 1.5rem;">Clear Filters</a>
    </div>
<?php endif;
// Capture buffered HTML
$html = ob_get_clean();

// Return JSON response
echo json_encode([
    'html' => $html,
    'count' => count($tours)
]);
?>
