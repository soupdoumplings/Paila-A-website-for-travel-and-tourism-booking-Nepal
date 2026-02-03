<?php
session_start();
require_once '../config/db.php';
require_once '../helpers/functions.php';

// Parse URL location
$location = isset($_GET['location']) ? trim($_GET['location']) : '';

// Define gallery data
$galleries = [
    'Everest' => [
        'name' => 'Everest',
        'province' => 'Solukhumbu',
        'elevation' => '2,860m - 5,364m',
        'best_season' => 'Best: Mar-May, Sep-Nov',
        'tour_count' => 8,
        'hero_image' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=1200&q=80',
    ],
    'Chitwan' => [
        'name' => 'Chitwan',
        'province' => 'Narayani',
        'elevation' => '100m - 815m',
        'best_season' => 'Best: Oct-Mar',
        'tour_count' => 5,
        'hero_image' => 'https://images.unsplash.com/photo-1533130061792-64b345e4a833?w=1200&q=80',
    ],
    'Langtang' => [
        'name' => 'Langtang',
        'province' => 'Bagmati',
        'elevation' => '1,400m - 4,984m',
        'best_season' => 'Best: Mar-May, Oct-Nov',
        'tour_count' => 6,
        'hero_image' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&q=80',
    ],
    'Pokhara' => [
        'name' => 'Pokhara',
        'province' => 'Gandaki',
        'elevation' => '827m',
        'best_season' => 'Best: Sep-Nov, Mar-May',
        'tour_count' => 12,
        'hero_image' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=1200&q=80',
    ],
    'Annapurna' => [
        'name' => 'Annapurna',
        'province' => 'Gandaki',
        'elevation' => '800m - 5,416m',
        'best_season' => 'Best: Mar-May, Sep-Nov',
        'tour_count' => 10,
        'hero_image' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&q=80',
    ],
    'Mustang' => [
        'name' => 'Mustang',
        'province' => 'Dhaulagiri',
        'elevation' => '2,800m - 3,840m',
        'best_season' => 'Best: Mar-Nov',
        'tour_count' => 4,
        'hero_image' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=1200&q=80',
    ],
    'Kathmandu' => [
        'name' => 'Kathmandu',
        'province' => 'Bagmati',
        'elevation' => '1,400m',
        'best_season' => 'Best: Oct-Apr',
        'tour_count' => 15,
        'hero_image' => 'https://images.unsplash.com/photo-1585409677983-0f6c41ca9c3b?w=1200&q=80',
    ],
    'Rara Lake' => [
        'name' => 'Rara Lake',
        'province' => 'Karnali',
        'elevation' => '2,990m',
        'best_season' => 'Best: Apr-May, Sep-Nov',
        'tour_count' => 3,
        'hero_image' => 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=1200&q=80',
    ],
    'Lumbini' => [
        'name' => 'Lumbini',
        'province' => 'Lumbini',
        'elevation' => '150m',
        'best_season' => 'Best: Oct-Mar',
        'tour_count' => 7,
        'hero_image' => 'https://images.unsplash.com/photo-1585409677983-0f6c41ca9c3b?w=1200&q=80',
    ]
];

// Validate location param
if (!isset($galleries[$location])) {
    header('Location: archive.php');
    exit;
}

$gallery = $galleries[$location];

// Folder path mapping
$folderMap = [
    'Rara Lake' => 'Rara',
    'Kathmandu' => 'kathmandu'
];

$folderName = isset($folderMap[$location]) ? $folderMap[$location] : $location;
$imagesDir = __DIR__ . '/../assets/images/' . $folderName;
$webPath = '../assets/images/' . $folderName;

// Load local images
$galleryImages = [];
if (is_dir($imagesDir)) {
    $files = glob($imagesDir . '/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);
    if ($files) {
        foreach ($files as $file) {
            $galleryImages[] = $webPath . '/' . basename($file);
        }
    }
}

// Use default images
if (empty($galleryImages)) {
    // Keeping some defaults just in case, or could simply be empty
    $galleryImages = [
        'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=400&q=80',
        'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&q=80',
        'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=400&q=80',
        'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=400&q=80'
    ];
} else {
    // Use first local
    $gallery['hero_image'] = $galleryImages[0];
}

$gallery['images'] = $galleryImages;

// Get DB tour count
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tours WHERE location LIKE ?");
    $stmt->execute(['%' . $gallery['name'] . '%']);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result && $result['count'] > 0) {
        $gallery['tour_count'] = $result['count'];
    }
} catch (Exception $e) {
    // Use default count
}

include '../includes/header.php';
?>

<!-- Hero Section -->
<section style="position: relative; height: 75vh; min-height: 600px; overflow: hidden; background: #000;">
    <!-- Background Image -->
    <img src="<?php echo $gallery['hero_image']; ?>" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.7;">
    
    <!-- Overlay -->
    <div style="position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.7) 100%);"></div>
    
    <!-- Content -->
    <div class="container" style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); padding-bottom: 4rem; width: 100%; max-width: 1200px;">
        <!-- Back Link -->
        <a href="archive.php" style="display: inline-flex; align-items: center; gap: 0.5rem; color: white; text-decoration: none; margin-bottom: 2rem; font-size: 0.9rem; opacity: 0.9; transition: opacity 0.3s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Destinations
        </a>
        
        <!-- Location Pin Badge -->
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(14, 165, 233, 0.2); backdrop-filter: blur(10px); color: #0ea5e9; padding: 0.5rem 1rem; border-radius: 2rem; font-size: 0.85rem; font-weight: 600; margin-bottom: 1.5rem; border: 1px solid rgba(14, 165, 233, 0.3);">
            <i class="fa-solid fa-location-dot"></i>
            <?php echo e($gallery['province']); ?>
        </div>
        
        <!-- Location Title -->
        <h1 style="font-family: var(--font-serif); font-size: 4.5rem; color: white; margin-bottom: 1rem; font-weight: 400; line-height: 1.1;">
            <?php echo e($gallery['name']); ?>
        </h1>
        
        <!-- Metadata Badges -->
        <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 2rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; color: white; font-size: 0.95rem;">
                <i class="fa-solid fa-mountain" style="opacity: 0.7;"></i>
                <span><?php echo e($gallery['elevation']); ?></span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem; color: white; font-size: 0.95rem;">
                <i class="fa-solid fa-calendar" style="opacity: 0.7;"></i>
                <span><?php echo e($gallery['best_season']); ?></span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem; color: white; font-size: 0.95rem;">
                <i class="fa-solid fa-route" style="opacity: 0.7;"></i>
                <span><?php echo $gallery['tour_count']; ?> tours available</span>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Section -->
<section style="background: white; padding: 0;">
    <div class="container" style="max-width: 1200px; padding: 0;">
        <!-- Gallery Label -->
        <div style="padding: 2rem 0 1rem; border-bottom: 1px solid var(--color-stone-200);">
            <span style="font-size: 0.85rem; font-weight: 600; color: var(--color-stone-600); text-transform: uppercase; letter-spacing: 0.1em;">Gallery:</span>
        </div>
        
        <!-- Image Gallery -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; border-bottom: 1px solid var(--color-stone-200);">
            <?php foreach ($gallery['images'] as $index => $image): ?>
                <div style="position: relative; aspect-ratio: 1; overflow: hidden; border-right: <?php echo ($index < 3) ? '1px solid var(--color-stone-200)' : 'none'; ?>;">
                    <img src="<?php echo $image; ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section style="background: white; padding: 4rem 0 6rem;">
    <div class="container" style="max-width: 1200px; text-align: center;">
        <h2 style="font-family: var(--font-serif); font-size: 2.5rem; margin-bottom: 1rem; color: var(--color-stone-900);">
            Ready to Explore?
        </h2>
        <p style="color: var(--color-stone-600); font-size: 1.1rem; margin-bottom: 2.5rem; max-width: 600px; margin-left: auto; margin-right: auto;">
            Discover our curated collection of tours and experiences in <?php echo e($gallery['name']); ?>
        </p>
        <a href="collection.php?location=<?php echo urlencode($gallery['name']); ?>" 
           style="display: inline-flex; align-items: center; gap: 0.75rem; background: var(--color-stone-900); color: white; padding: 1.25rem 3rem; border-radius: 0.5rem; text-decoration: none; font-weight: 600; font-size: 1.05rem; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.15);"
           onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.2)';"
           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';">
            Browse <?php echo e($gallery['name']); ?> Tours
            <i class="fa-solid fa-arrow-right-long"></i>
        </a>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
