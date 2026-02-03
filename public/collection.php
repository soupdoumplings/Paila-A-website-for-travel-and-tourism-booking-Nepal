<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../config/db.php';

// Extra page styles
$extraCss = ['assets/css/collection.css'];

include __DIR__ . '/../includes/header.php';

// Parse filter params
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$min_price = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 1000000;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$duration = isset($_GET['duration']) ? $_GET['duration'] : '';
$location_filter = isset($_GET['location']) ? trim($_GET['location']) : '';

// Build SQL query
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

// Apply price filter
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

// Execute database query
$tours = [];
try {
    if ($pdo) {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $tours = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) { /* Ignore */ }

// Category fallback images
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
?>

<!-- Hero -->
<div class="collection-hero">
    <div class="container relative z-10 text-center">
        <p class="text-xs-caps animate-slideUp" style="color: var(--color-stone-600); margin-bottom: 1rem; letter-spacing: 0.25em;">CURATED JOURNEYS</p>
        <h1 class="animate-slideUp delay-100" style="font-size: 3.5rem; font-family: var(--font-serif); margin-bottom: 2rem; color: var(--color-stone-900);">
            Find Your <span style="color: var(--color-teal-600);">Perfect Path</span>
        </h1>
        <p class="animate-slideUp delay-200" style="max-width: 600px; margin: 0 auto; line-height: 1.8; color: var(--color-stone-600);">
            Discover our handpicked selection of premium trekking and cultural experiences across the Himalayas.
        </p>
    </div>
</div>

<!-- Content -->
<section class="collection-grid-section">
    <div class="container">
        <div class="grid grid-cols-12 gap-8" style="display: grid; grid-template-columns: repeat(12, 1fr); gap: 2rem;">
            
            <!-- Sidebar -->
            <aside class="col-span-3" style="grid-column: span 3;">
                <div class="filters-sidebar sticky top-4">
                    <form action="" method="GET" id="filterForm">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem;">
                            <h3 style="font-family: var(--font-serif); font-size: 1.25rem; font-weight: 600; color: var(--color-stone-900); margin: 0;">Filters</h3>
                            <a href="collection.php" style="font-size: 0.8rem; color: var(--color-stone-500); text-decoration: underline;">Reset</a>
                        </div>

                        <!-- Location -->
                        <input type="hidden" name="location" value="<?php echo e($location_filter); ?>">

                        <!-- Search -->
                        <div style="margin-bottom: 2rem;">
                            <label class="filter-label">Search</label>
                            <div class="filter-input-wrapper">
                                <i class="fa-solid fa-magnifying-glass filter-search-icon"></i>
                                <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Keyword..." class="filter-input">
                            </div>
                        </div>

                        <!-- Category -->
                        <div style="margin-bottom: 2rem;">
                            <label class="filter-label">Category</label>
                            <select name="category" class="filter-select">
                                <option value="">All Categories</option>
                                <option value="trekking" <?php echo $category == 'trekking' ? 'selected' : ''; ?>>Trekking</option>
                                <option value="cultural" <?php echo $category == 'cultural' ? 'selected' : ''; ?>>Cultural Tours</option>
                                <option value="culture" <?php echo $category == 'culture' ? 'selected' : ''; ?>>Culture & Heritage</option>
                                <option value="adventure" <?php echo $category == 'adventure' ? 'selected' : ''; ?>>Adventure Sports</option>
                                <option value="family" <?php echo $category == 'family' ? 'selected' : ''; ?>>Family & Wellness</option>
                                <option value="luxury" <?php echo $category == 'luxury' ? 'selected' : ''; ?>>Luxury</option>
                                <option value="budget" <?php echo $category == 'budget' ? 'selected' : ''; ?>>Budget Friendly</option>
                            </select>
                        </div>

                        <!-- Duration -->
                        <div style="margin-bottom: 2rem;">
                            <label class="filter-label">Duration</label>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: var(--color-stone-600);">
                                    <input type="radio" name="duration" value="" <?php echo $duration == '' ? 'checked' : ''; ?>> Any Duration
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: var(--color-stone-600);">
                                    <input type="radio" name="duration" value="short" <?php echo $duration == 'short' ? 'checked' : ''; ?>> Short (1-3 Days)
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: var(--color-stone-600);">
                                    <input type="radio" name="duration" value="medium" <?php echo $duration == 'medium' ? 'checked' : ''; ?>> Medium (4-7 Days)
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: var(--color-stone-600);">
                                    <input type="radio" name="duration" value="long" <?php echo $duration == 'long' ? 'checked' : ''; ?>> Long (8+ Days)
                                </label>
                            </div>
                        </div>

                        <!-- Price -->
                        <div style="margin-bottom: 2rem;">
                            <label class="filter-label">Price Range</label>
                            <div style="margin-bottom: 1rem; font-size: 0.9rem; font-weight: 600; color: var(--color-emerald-700);">
                                Rs <span id="price-val"><?php echo $max_price; ?></span>
                            </div>
                            <input type="range" name="max_price" min="0" max="500000" step="5000" value="<?php echo $max_price; ?>" oninput="document.getElementById('price-val').innerText = this.value">
                            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--color-stone-400); margin-top: 0.5rem;">
                                <span>Rs 0</span>
                                <span>Rs 5L+</span>
                            </div>
                        </div>

                    </form>
                </div>
            </aside>

            <!-- Grid -->
            <div class="col-span-9" style="grid-column: span 9;">
                <!-- Top Bar -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <p style="color: var(--color-stone-500); font-size: 0.95rem;">
                        Showing <span id="journey-count" style="font-weight: 700; color: var(--color-stone-900);"><?php echo count($tours); ?></span> journeys 
                        <?php if($location_filter): ?> in <span style="color: var(--color-teal-600); font-weight: 600;"><?php echo e($location_filter); ?></span> <?php endif; ?>
                    </p>
                    
                    <form action="" method="GET" style="display: flex; align-items: center; gap: 0.75rem;">
                         <!-- Ensure params -->
                        <?php foreach($_GET as $key => $val): if($key != 'sort') echo '<input type="hidden" name="'.e($key).'" value="'.e($val).'">'; endforeach; ?>
                        
                        <label for="sort" style="font-size: 0.9rem; color: var(--color-stone-600);">Sort by:</label>
                        <select name="sort" id="sortSelect" style="border: none; background: transparent; font-weight: 600; color: var(--color-stone-900); cursor: pointer; padding-right: 1.5rem;">
                            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest</option>
                            <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Name: A-Z</option>
                        </select>
                    </form>
                </div>

                <!-- Tours -->
                <div class="tours-grid" id="toursGrid" style="grid-template-columns: repeat(3, 1fr) !important;"> 
                    <?php if (count($tours) > 0): ?>
                        <?php foreach ($tours as $tour): ?>
                            <?php
                            $img = get_tour_image($tour);
                            $cat = isset($tour['category']) && $tour['category'] ? $tour['category'] : 'trekking';
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
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-results-box">
                            <div style="font-size: 3rem; color: var(--color-stone-300); margin-bottom: 1rem;">
                                <i class="fa-solid fa-mountain-sun"></i>
                            </div>
                            <h3 style="font-family: var(--font-serif); font-size: 1.5rem; margin-bottom: 0.5rem;">No journeys found</h3>
                            <p style="color: var(--color-stone-500); margin-bottom: 1.5rem;">We couldn't find any tours matching your criteria.</p>
                            <a href="collection.php" class="btn btn-primary" style="padding: 0.5rem 1.5rem;">Clear Filters</a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination -->
                <?php if (count($tours) > 20): // Placeholder ?>
                <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 4rem;">
                    <button class="btn" style="background: white; border: 1px solid var(--border-color);">Prev</button>
                    <button class="btn btn-primary">1</button>
                    <button class="btn" style="background: white; border: 1px solid var(--border-color);">2</button>
                    <button class="btn" style="background: white; border: 1px solid var(--border-color);">3</button>
                    <button class="btn" style="background: white; border: 1px solid var(--border-color);">Next</button>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>

<script src="<?php echo url('assets/js/collection-ajax.js'); ?>"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
