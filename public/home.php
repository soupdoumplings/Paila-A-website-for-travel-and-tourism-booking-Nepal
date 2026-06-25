<?php
require_once __DIR__ . '/../helpers/security.php';
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    secure_session_start();
}
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../config/db.php';

// Extra page styles
$extraCss = ['assets/css/home.css'];

include __DIR__ . '/../includes/header.php';

// Count tour categories
$categoryCounts = [];
try {
    if ($pdo) {
        $stmt = $pdo->query("SELECT LOWER(category) as cat, COUNT(*) as count FROM tours GROUP BY category");
        while ($row = $stmt->fetch()) {
            $categoryCounts[$row['cat']] = $row['count'];
        }
    }
} catch (Exception $e) {}

// Count total records
$totalToursCount = 0;
$totalDestinationsCount = 0;
try {
    if ($pdo) {
        $totalToursCount = $pdo->query("SELECT COUNT(*) FROM tours")->fetchColumn();
        $totalDestinationsCount = $pdo->query("SELECT COUNT(DISTINCT location) FROM tours")->fetchColumn();
    }
} catch (Exception $e) {}
?>

<!-- Hero -->
<section id="parallax-container">
    <!-- Background -->
    <div id="parallax-bg" style="background-color: #000;">
        <video id="hero-video-1" muted playsinline 
               style="position: absolute; top: 0; left: 0; width: 100%; height: 112vh; object-fit: cover; z-index: -2; opacity: 0; transition: opacity 1.5s ease-in-out;">
        </video>
        <video id="hero-video-2" muted playsinline 
               style="position: absolute; top: 0; left: 0; width: 100%; height: 112vh; object-fit: cover; z-index: -2; opacity: 0; transition: opacity 1.5s ease-in-out;">
        </video>
        <div class="hero-bg overlay" style="z-index: -1;"></div>
        
        <!-- Video config -->
        <div id="video-config" 
             data-base-url="<?php echo url('assets/video/'); ?>"
             data-clips='["1.mp4", "2.mp4", "3.mp4", "4.mp4", "5.mp4", "6.mp4", "7.mp4", "8.mp4"]'>
        </div>
    </div>

    <!-- Content -->
    <div id="hero-content">
        <div class="container-fluid relative z-10 animate-fadeIn hero-text-container">
            <p class="text-xs-caps text-amber delay-300 animate-slideUp" style="margin-bottom: 2.5rem; font-size: 0.9rem; letter-spacing: 0.25em;">Nature as Nobility</p>
            
            <h1 class="animate-slideUp delay-300" style="font-size: 3.5rem; line-height: 1.1; margin-bottom: 1.5rem; font-weight: 700; letter-spacing: -0.02em;">
                Discover the<br>
                <span style="color: var(--color-amber-400);">Magic</span> of Nepal
            </h1>
            
            <p class="animate-slideUp delay-500" style="font-size: 1rem; max-width: 500px; margin-bottom: 2.5rem; opacity: 0.9; line-height: 1.6;">
                From the majestic Himalayas to ancient temples, embark on a journey where wilderness meets refinement.
            </p>

            <!-- Search -->
            <form action="#tours" method="GET" class="hero-search-form animate-slideUp delay-500" data-validate>
                <div class="hero-search-input-wrapper">
                     <input type="text" id="search-input" name="search" placeholder="Where do you want to explore?" class="hero-search-input" data-rules="required|min:3">
                     <!-- Icon -->
                     <i class="fa-solid fa-magnifying-glass hero-search-icon"></i>
                </div>
                <button type="submit" class="btn btn-primary" style="padding: 0.4rem 2rem; font-size: 1rem; border-radius: 10px; white-space: nowrap; display: flex; align-items: center; gap: 0.5rem;">
                    Search Tours <i class="fa-solid fa-arrow-right-long"></i>
                </button>
            </form>

            <!-- Stats -->
            <div class="hero-stats animate-slideUp delay-700">
                <div>
                    <div class="stat-number"><?php echo $totalToursCount; ?>+</div>
                    <div class="stat-label">Curated Journeys</div>
                </div>
                <div>
                    <div class="stat-number"><?php echo $totalDestinationsCount; ?></div>
                    <div class="stat-label">Destinations</div>
                </div>
                <div>
                    <div class="stat-number">8+</div>
                    <div class="stat-label">Years Experience</div>
                </div>
            </div>
        </div>
        <a href="#collection" class="hero-scroll-cue animate-fadeIn delay-700" aria-label="Scroll to featured journeys">
            <i class="fa-solid fa-chevron-down"></i>
        </a>
    </div>

    <!-- About -->
    <div id="about-content">
        <div class="container">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center;">
                <!-- Left -->
                <div style="padding-bottom: 3rem;">
                    <p class="text-xs-caps" style="color: var(--color-amber-400); margin-bottom: 1rem;">OUR STORY</p>
                    <h2 style="font-size: 2.5rem; font-family: var(--font-serif); margin-bottom: 2rem; line-height: 1.2; color: white;">
                        Big Adventures Start With <span style="color: var(--color-amber-400);">Small Steps</span>
                    </h2>
                    <p style="color: rgba(255,255,255,0.9); font-size: 1.05rem; line-height: 1.8;">
                        We believe that the most meaningful journeys don't happen all at once but instead they’re built one step at a time. Whether it’s reaching a mountain peak or discovering a new side of yourself, it all begins with that simple, intentional decision to move forward.
                    </p>
                    <p style="color: rgba(255,255,255,0.9); font-size: 1.05rem; line-height: 1.8; margin-top: 1.5rem;">
                        <strong>Next <span style="color: var(--color-amber-400);">पाइला</span></strong> was created to help you take that next step. We handle the details and guide you through the paths, so you can focus on the experience itself. We’re here to show you that when you have the right support, a small step today can lead to the adventure of a lifetime.
                    </p>
                </div>

                <!-- Right -->
                <div style="position: relative;">
                    <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80" 
                         alt="Himalayan Mountain Range" 
                         style="width: 100%; border-radius: 1rem; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Editorial Concept -->
<section class="editorial-intro">
    <div class="container editorial-intro-grid">
        <div>
            <p class="text-xs-caps">PAILA JOURNAL</p>
            <h2>Nepal, framed as a living gallery.</h2>
        </div>
        <div class="editorial-intro-copy">
            <p>Paila treats nature as luxury: quiet lodges, sacred trails, high-altitude light, and journeys arranged with the care of a private collection.</p>
            <div class="editorial-notes">
                <span>Curated routes</span>
                <span>Local guides</span>
                <span>Refined stays</span>
            </div>
        </div>
    </div>
</section>

<!-- Tours -->
<section id="collection" class="section-padding">
    <div class="container">
        <div class="section-editorial-head">
            <div>
                <p class="text-xs-caps">THE COLLECTION</p>
                <h2>Featured<br>Journeys</h2>
            </div>
            <div class="section-editorial-copy">
                <p>The classic Himalayan trek through breathtaking landscapes, from subtropical forests to high-altitude desert, crossing ancient monasteries and vibrant Sherpa villages.</p>
                <a href="<?php echo url('public/collection.php'); ?>">View All Tours -></a>
            </div>
        </div>

        <!-- Grid -->
        <div class="tours-grid">
            <?php
            $displayTours = [];
            try {
                if ($pdo) {
                    $stmt = $pdo->query("SELECT id, title, location, price, duration, description, image, category FROM tours WHERE is_featured = 1 ORDER BY id DESC LIMIT 6");
                    $displayTours = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            } catch (Exception $e) {}
            
            if (count($displayTours) > 0) {
                foreach ($displayTours as $tour) {
                    $category = isset($tour['category']) && $tour['category'] ? $tour['category'] : 'trekking';
                    $categoryLabel = ucwords(str_replace(['_', '-'], ' ', $category));
                    $location = isset($tour['destination_name']) ? $tour['destination_name'] : (isset($tour['location']) ? $tour['location'] : '');
                    $img = get_tour_image($tour);

                    $cardHref = url('public/package_detail/index.php?id=' . (int)$tour['id']);
                    ?>
                    <a href="<?php echo e($cardHref); ?>" class="collection-card hover-zoom-card">
                        <div class="collection-card-image-container">
                            <img src="<?php echo e($img); ?>" alt="<?php echo e($tour['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <span class="journey-card-badge"><?php echo e($categoryLabel); ?></span>
                        </div>
                        <div class="collection-card-content">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                <h3 style="font-family: var(--font-serif); font-size: 1.5rem; line-height: 1.2; color: var(--color-stone-900); margin: 0;"><?php echo e($tour['title']); ?></h3>
                            </div>
                            
                            <p class="collection-card-desc" style="color: var(--color-stone-500); font-size: 0.9rem; margin-bottom: 0; line-height: 1.6;">
                                <?php echo e(substr($tour['description'] ?? '', 0, 100) . '...'); ?>
                            </p>
    
                            <div style="padding-top: 1.5rem; border-top: 1px dashed var(--color-stone-200); display: flex; justify-content: space-between; align-items: center;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--color-stone-500); font-size: 0.85rem;">
                                    <i class="fa-solid fa-location-dot"></i> <?php echo e($location); ?>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--color-stone-400); font-weight: 700;">From</div>
                                    <div class="price-display card-price"><span class="currency">रू</span><span class="amount"><?php echo number_format((float)($tour['price'] ?? 0), 2); ?></span></div>
                                </div>
                            </div>
                        </div>
                    </a>
                    <?php
                }
            } else {
                echo '<div style="grid-column: 1/-1; text-align: center; padding: 3rem; background: var(--color-stone-100); border-radius: 1rem;">';
                echo '<p style="color: var(--color-stone-600);">No tours yet. Create a package from the admin dashboard to see them here.</p>';
                echo '</div>';
            }
            ?>
        </div>

        <div class="gallery-footnote">Each package is built as a complete travel object: route, stay, guide, safety, and story.</div>
    </div>
</section>

<!-- Categories -->
<section id="archive" class="section-padding">
    <div class="container interest-editorial">
        <aside class="interest-copy">
            <p class="text-xs-caps">EXPLORE BY INTEREST</p>
            <h2>Find Your Adventure</h2>
            <p>Whether you seek the thrill of mountain peaks or the serenity of ancient temples, discover experiences tailored to your spirit.</p>
            <a href="<?php echo url('public/collection.php'); ?>">Browse the archive -></a>
        </aside>

        <!-- Slider -->
        <div class="interest-slider-panel">
        <div class="category-slider-wrapper">
             <button class="slider-arrow prev" id="cat-prev"><i class="fa-solid fa-chevron-left"></i></button>
             
            <div class="category-grid" id="category-slider">
                <?php
                $categories = [
                    ['name' => 'Trekking', 'icon' => 'fa-solid fa-mountain-sun', 'image' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=800&q=80', 'filter' => 'trekking'],
                    ['name' => 'Cultural', 'icon' => 'fa-solid fa-landmark', 'image' => 'https://images.unsplash.com/photo-1585409677983-0f6c41ca9c3b?w=800&q=80', 'filter' => 'cultural'],
                    ['name' => 'Culture', 'icon' => 'fa-solid fa-building-columns', 'image' => 'https://images.unsplash.com/photo-1585409677983-0f6c41ca9c3b?w=800&q=80', 'filter' => 'culture'],
                    ['name' => 'Adventure', 'icon' => 'fa-solid fa-parachute-box', 'image' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80', 'filter' => 'adventure'],
                    ['name' => 'Wellness', 'icon' => 'fa-solid fa-spa', 'image' => 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=800&q=80', 'filter' => 'wellness'],
                    ['name' => 'Family', 'icon' => 'fa-solid fa-people-group', 'image' => 'https://images.unsplash.com/photo-1533130061792-64b345e4a833?w=800&q=80', 'filter' => 'family'],
                    ['name' => 'Photography', 'icon' => 'fa-solid fa-camera', 'image' => 'https://images.unsplash.com/photo-1452421822248-d4c2b47f0c81?w=800&q=80', 'filter' => 'photography'],
                    ['name' => 'Weekend', 'icon' => 'fa-solid fa-calendar-day', 'image' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80', 'filter' => 'weekend'],
                    ['name' => 'Luxury', 'icon' => 'fa-solid fa-gem', 'image' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=800&q=80', 'filter' => 'luxury'],
                    ['name' => 'Budget', 'icon' => 'fa-solid fa-wallet', 'image' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=800&q=80', 'filter' => 'budget']
                ];

                foreach ($categories as $cat) {
                    $actualCount = $categoryCounts[$cat['filter']] ?? 0;
                    $countLabel = $actualCount . ($actualCount == 1 ? ' Tour' : ' Tours');
                    ?>
                    <a href="<?php echo url('public/collection.php?category=' . $cat['filter']); ?>" class="category-card">
                        <img src="<?php echo $cat['image']; ?>" alt="<?php echo $cat['name']; ?>" class="category-card-bg">
                        <div class="category-card-overlay">
                            <div class="category-icon"><i class="<?php echo $cat['icon']; ?>"></i></div>
                            <div class="category-name"><?php echo $cat['name']; ?></div>
                            <div class="category-count"><?php echo $countLabel; ?></div>
                        </div>
                    </a>
                    <?php
                }
                ?>
            </div>

            <button class="slider-arrow next" id="cat-next"><i class="fa-solid fa-chevron-right"></i></button>
        </div>

        <!-- Dots -->
        <div class="slider-dots" id="cat-dots">
            <!-- Generated by JS -->
        </div>
        
            <script>
            // Slider init
            document.addEventListener('DOMContentLoaded', function() {
                const slider = document.getElementById('category-slider');
                const dotsContainer = document.getElementById('cat-dots');
                const items = slider.querySelectorAll('.category-card');
                const prevBtn = document.getElementById('cat-prev');
                const nextBtn = document.getElementById('cat-next');

                if (!slider || !dotsContainer || !prevBtn || !nextBtn || !items.length) {
                    return;
                }

                let pageCount = 1;

                function getMaxScroll() {
                    return Math.max(0, slider.scrollWidth - slider.clientWidth);
                }

                function getPageWidth() {
                    return Math.max(1, slider.clientWidth * 0.82);
                }

                function renderDots() {
                    const maxScroll = getMaxScroll();
                    pageCount = maxScroll > 0 ? Math.max(1, Math.ceil(maxScroll / getPageWidth()) + 1) : 1;
                    dotsContainer.innerHTML = '';

                    for (let i = 0; i < pageCount; i++) {
                        const dot = document.createElement('button');
                        dot.className = 'slider-dot';
                        dot.type = 'button';
                        dot.ariaLabel = 'Go to interest group ' + (i + 1);
                        dot.addEventListener('click', () => {
                            const targetScroll = pageCount === 1 ? 0 : (i / (pageCount - 1)) * getMaxScroll();
                            slider.scrollTo({ left: targetScroll, behavior: 'smooth' });
                        });
                        dotsContainer.appendChild(dot);
                    }

                    updateSliderState();
                }

                function updateSliderState() {
                    const maxScroll = slider.scrollWidth - slider.clientWidth;
                    const scrollPercentage = maxScroll > 0 ? slider.scrollLeft / maxScroll : 0;
                    const activeDotIndex = Math.min(pageCount - 1, Math.round(scrollPercentage * Math.max(1, pageCount - 1)));
                    const dots = dotsContainer.children;

                    for (let i = 0; i < dots.length; i++) {
                        dots[i].classList.toggle('active', i === activeDotIndex);
                    }

                    prevBtn.disabled = slider.scrollLeft <= 4;
                    nextBtn.disabled = slider.scrollLeft >= maxScroll - 4;
                }

                // Arrow logic
                prevBtn.addEventListener('click', () => {
                   slider.scrollBy({ left: -getPageWidth(), behavior: 'smooth' });
                });
                nextBtn.addEventListener('click', () => {
                   slider.scrollBy({ left: getPageWidth(), behavior: 'smooth' });
                });

                slider.addEventListener('scroll', updateSliderState, { passive: true });
                window.addEventListener('resize', renderDots);
                renderDots();
            });
            </script>
        </div>
    </div>
</section>

<!-- Destinations -->
<section id="destinations" class="section-padding">
    <div class="container-fluid">
        <div style="margin-bottom: 3rem;">
            <p class="text-xs-caps" style="color: var(--color-teal-900); margin-bottom: 0.5rem;">DESTINATIONS</p>
            <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                <h2 style="font-size: 3rem; font-family: var(--font-serif);">
                    Explore Nepal's<br>
                    <span style="color: var(--color-teal-900);">Treasures</span>
                </h2>
                <a href="<?php echo url('public/archive.php'); ?>" style="color: var(--color-stone-600); font-size: 0.9rem;">View All Destinations →</a>
            </div>
        </div>

        <!-- Destinations responsive grid layout -->
        <div class="destination-gallery">
            <?php
            $destinations = [
                ['name' => 'Everest', 'location' => 'Solukhumbu', 'desc' => 'Home to the world\'s highest peak and iconic Sherpa culture', 'altitude' => '2,860m - 5,364m', 'image' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=600&q=80', 'icon' => 'fa-solid fa-mountain'],
                ['name' => 'Chitwan', 'location' => 'Narayani', 'desc' => 'Wildlife sanctuary with tigers, rhinos, and jungle adventures', 'altitude' => '150m', 'image' => 'https://images.unsplash.com/photo-1533130061792-64b345e4a833?w=600&q=80', 'icon' => 'fa-solid fa-tree'],
                ['name' => 'Langtang', 'location' => 'Bagmati', 'desc' => 'The valley of glaciers, close to Kathmandu', 'altitude' => '3,870m - 4,984m', 'image' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=600&q=80', 'icon' => 'fa-solid fa-compass'],
                ['name' => 'Pokhara', 'location' => 'Gandaki', 'desc' => 'Lakeside paradise with stunning Annapurna views', 'altitude' => '822m', 'image' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=600&q=80', 'icon' => 'fa-solid fa-water']
            ];

            // Directory Mapping
            $folderMap = [
                'Rara Lake' => 'Rara',
                'Kathmandu' => 'kathmandu'
            ];

            foreach ($destinations as &$dest) {
                 $folderName = isset($folderMap[$dest['name']]) ? $folderMap[$dest['name']] : $dest['name'];
                 $imagesDir = __DIR__ . '/../assets/images/' . $folderName;
                 $webPath = '../assets/images/' . $folderName;

                 if (is_dir($imagesDir)) {
                    $files = glob($imagesDir . '/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);
                    if ($files && count($files) > 0) {
                        $dest['image'] = url('assets/images/' . $folderName . '/' . basename($files[0]));
                    }
                 }
            }
            unset($dest); // Break reference

            foreach ($destinations as $dest) {
                ?>
                <a href="<?php echo url('public/archive.php?destination=' . urlencode($dest['name'])); ?>" class="destination-card hover-zoom-card">
                    <!-- Image -->
                    <img src="<?php echo (strpos($dest['image'], 'http') === 0) ? $dest['image'] : url($dest['image']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    
                    <!-- Overlay -->
                    <div class="destination-gradient"></div>
                    
                    <!-- Content -->
                    <div class="destination-content">
                        <!-- Tag -->
                        <div style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--color-amber-400); font-size: 0.85rem; font-weight: 600; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                            <i class="<?php echo $dest['icon']; ?>"></i>
                            <span><?php echo $dest['location']; ?></span>
                        </div>
                        
                        <!-- Name -->
                        <h3 style="font-family: var(--font-serif); font-size: 2rem; margin-bottom: 0.75rem; font-weight: 400; line-height: 1.1; color: white;">
                            <?php echo $dest['name']; ?>
                        </h3>
                        
                        <!-- Desc -->
                        <p style="font-size: 0.85rem; opacity: 0.8; margin-bottom: 0.75rem; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <?php echo $dest['desc']; ?>
                        </p>
                        
                         <!-- Altitude -->
                        <div style="font-size: 0.75rem; opacity: 0.6; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-layer-group"></i> Altitude: <?php echo $dest['altitude']; ?>
                        </div>
                    </div>
                </a>
                <?php
            }
            ?>
        </div>
    </div>
</section>

<!-- Why Us -->
<section id="about" class="section-padding">
    <div class="container">
        <div class="trust-standard">
            <!-- Left -->
            <div class="trust-collage">
                <img src="<?php echo url('assets/images/guest_experience.jpg'); ?>" alt="Guest experience in Nepal" class="trust-main-photo">
                <img src="https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=700&q=80" alt="Himalayan trail" class="trust-offset-photo">
                <!-- Rating -->
                <div class="trust-rating">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <i class="fa-solid fa-trophy" style="font-size: 1.5rem; color: var(--color-emerald-700);"></i>
                        <span style="font-size: 2rem; font-weight: 700;">4.9/5</span>
                    </div>
                    <p style="font-size: 0.85rem; color: var(--color-stone-600); margin: 0;">Guest Rating</p>
                    <p style="font-size: 0.75rem; font-style: italic; color: var(--color-stone-500); margin-top: 0.5rem;">"An extraordinary journey that exceeded every expectation."</p>
                </div>
            </div>

            <!-- Right -->
            <div class="trust-copy">
                <p class="text-xs-caps" style="color: var(--color-emerald-700); margin-bottom: 0.5rem;">THE STANDARD</p>
                <h2 style="font-size: 2.5rem; font-family: var(--font-serif); margin-bottom: 2rem;">Why Travel<br>With Us</h2>

                <!-- Why travel with us grid -->
                <div class="standard-grid">
                    <?php
                    $features = [
                        ['num' => '01', 'icon' => 'fa-solid fa-shield-halved', 'title' => 'Secure', 'desc' => 'Curated itineraries with verified security protocols and trusted local partners.', 'color' => 'var(--color-teal-900)'], // Emerald
                        ['num' => '02', 'icon' => 'fa-regular fa-heart', 'title' => 'Authentic', 'desc' => 'Genuine experiences crafted with deep local knowledge and cultural respect.', 'color' => '#f43f5e'], // Rose
                        ['num' => '03', 'icon' => 'fa-solid fa-award', 'title' => 'Premium', 'desc' => 'Exceptional quality without compromise. Nature deserves luxury treatment.', 'color' => '#f59e0b'], // Amber
                        ['num' => '04', 'icon' => 'fa-solid fa-headset', 'title' => 'Support', 'desc' => '24/7 assistance throughout your journey, from booking to return.', 'color' => '#3b82f6'], // Blue
                        ['num' => '05', 'icon' => 'fa-regular fa-compass', 'title' => 'Expertise', 'desc' => 'Over 8 years of crafting unforgettable Himalayan experiences.', 'color' => '#8b5cf6'], // Violet
                        ['num' => '06', 'icon' => 'fa-solid fa-user-group', 'title' => 'Community', 'desc' => 'Supporting local communities and sustainable tourism practices.', 'color' => 'var(--color-teal-900)'] // Teal
                    ];

                    foreach ($features as $feature) {
                        ?>
                        <div class="standard-card">
                            <span style="font-family: var(--font-serif); font-size: 2.25rem; font-weight: 700; color: <?php echo $feature['color']; ?>; opacity: 0.3; line-height: 1;">
                                <?php echo $feature['num']; ?>
                            </span>
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                    <i class="<?php echo $feature['icon']; ?>" style="color: <?php echo $feature['color']; ?>; font-size: 1.1rem;"></i>
                                    <h4 style="font-weight: 700; font-size: 1.1rem; color: var(--color-stone-900); margin: 0;"><?php echo $feature['title']; ?></h4>
                                </div>
                                <p style="color: var(--color-stone-600); font-size: 0.925rem; line-height: 1.6; margin: 0;"><?php echo $feature['desc']; ?></p>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials-section section-padding">
    <div class="container">
        <div style="text-align: center; margin-bottom: 3rem;">
            <p class="text-xs-caps" style="color: var(--color-teal-900); margin-bottom: 0.5rem; letter-spacing: 0.2em;">TRAVELER STORIES</p>
            <h2 style="font-size: 3rem; font-family: var(--font-serif); margin-bottom: 2rem;">What Our Guests Say</h2>
        </div>

        <!-- Guest testimonials grid layout -->
        <div class="instagram-review-grid">
            <?php
            $testimonials = [
                [
                    'quote' => 'An extraordinary journey that exceeded every expectation. The attention to detail and authentic experiences made this trek unforgettable.',
                    'name' => 'Rajesh Hamal',
                    'location' => 'Kathmandu, Nepal',
                    'tour' => 'Everest Base Camp Trek',
                    'rating' => 5,
                    'initials' => 'RH',
                    'photo' => url('assets/images/Everest/photo-1544735716-87fa59a45b4e.jpg')
                ],
                [
                    'quote' => 'From the moment we arrived, everything was seamless. Our guide was knowledgeable and the luxury lodges were incredible.',
                    'name' => 'K ma hancy hoina para',
                    'location' => 'Lalitpur, Nepal',
                    'tour' => 'Annapurna Circuit',
                    'rating' => 5,
                    'initials' => 'KH',
                    'photo' => url('assets/images/Annapurna/photo-1697621535550-1c671d4969c4.jpg')
                ],
                [
                    'quote' => 'The perfect blend of adventure and comfort. PAILA truly understands how to create meaningful travel experiences.',
                    'name' => 'Falano ghar ko chora',
                    'location' => 'Bhaktapur, Nepal',
                    'tour' => 'Kathmandu Heritage Walk',
                    'rating' => 5,
                    'initials' => 'FG',
                    'photo' => url('assets/images/kathmandu/meghraj-neupane-YtEzq-hnv5k-unsplash.jpg')
                ],
                [
                    'quote' => 'Best decision we made for our Nepal trip. Professional, safe, and absolutely stunning scenery every single day.',
                    'name' => 'Kale dai',
                    'location' => 'Chitwan, Nepal',
                    'tour' => 'Luxury Everest Helicopter Tour',
                    'rating' => 5,
                    'initials' => 'KD',
                    'photo' => url('assets/images/Chitwan/photo-1534215782964-d58601aa091c.jpg')
                ]
            ];

            foreach ($testimonials as $test) {
                ?>
                <article class="instagram-post-card" tabindex="0">
                    <div class="ig-post-header">
                        <div class="ig-post-avatar" aria-hidden="true"><?php echo e($test['initials']); ?></div>
                        <div>
                            <div class="ig-post-name"><?php echo $test['name']; ?></div>
                            <div class="ig-post-location"><?php echo $test['location']; ?></div>
                        </div>
                        <span class="ig-post-menu" aria-hidden="true"></span>
                    </div>
                    <div class="ig-post-photo">
                        <img src="<?php echo $test['photo']; ?>" alt="<?php echo $test['tour']; ?>">
                        <div class="ig-post-tag"><?php echo $test['tour']; ?></div>
                    </div>
                    <div class="ig-post-body">
                        <div class="ig-post-actions" aria-hidden="true">
                            <span><i class="fa-regular fa-heart"></i></span>
                            <span><i class="fa-regular fa-comment"></i></span>
                            <span><i class="fa-regular fa-paper-plane"></i></span>
                            <span><i class="fa-regular fa-bookmark"></i></span>
                        </div>
                        <div class="ig-post-rating">
                            <?php for($i=0; $i<$test['rating']; $i++) echo '<i class="fa-solid fa-star"></i> '; ?>
                        </div>
                        <p class="ig-post-caption"><strong><?php echo $test['name']; ?></strong> <?php echo $test['quote']; ?></p>
                    </div>
                </article>
                <?php
            }
            ?>
        </div>
    </div>
</section>

<!-- Private -->
<section id="private" class="private-journey-section">
    <!-- Private journey split section -->
    <div class="grid-responsive-split" style="min-height: 600px; gap: 0; background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-radius: 1.5rem; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.1); border: 1px solid rgba(255, 255, 255, 0.5);">
        <!-- Left -->
        <div class="private-journey-image" style="opacity: 0.9;"></div>
        
        <!-- Right -->
        <div class="private-journey-content" style="padding: 5rem;">
            <div style="max-width: 500px;">
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(4, 47, 46, 0.1); color: var(--color-teal-900); padding: 0.4rem 1rem; border-radius: 2rem; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 2rem;">
                    <i class="fa-solid fa-lock"></i> BY INVITATION ONLY
                </div>
                
                <h2 style="font-size: 4.5rem; font-family: var(--font-serif); margin-bottom: 2rem; line-height: 1; color: var(--color-stone-900); letter-spacing: -0.02em;">
                    Private <br>
                    <span style="color: var(--color-teal-900);">Journeys</span>
                </h2>
                
                <p style="font-size: 1.1rem; line-height: 1.8; color: var(--color-stone-600); margin-bottom: 3rem;">
                    An exclusive luxury collection of bespoke journeys reserved for discerning travelers. Private expeditions, helicopter access, and experiences beyond the ordinary.
                </p>
                
                <a href="#contact" class="btn" style="background: var(--color-stone-900); color: white; padding: 1rem 2.5rem; border-radius: 50px; text-decoration: none; display: inline-block; font-weight: 500; transition: transform 0.3s ease;">
                    Request Private Access <i class="fa-solid fa-arrow-right-long" style="margin-left: 0.5rem;"></i>
                </a>

                <!-- Three column feature list -->
                <div class="grid-responsive-3" style="margin-top: 5rem; padding-top: 3.5rem; border-top: 1px solid rgba(4, 47, 46, 0.15); gap: 2rem;">
                    <div style="border-right: 1px solid rgba(4, 47, 46, 0.1); padding-right: 1.5rem;">
                        <div style="font-size: 0.7rem; color: var(--color-teal-900); font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 0.75rem;">Exclusive</div>
                        <div style="font-size: 1.6rem; font-weight: 400; font-family: var(--font-serif); color: var(--color-stone-900);">Private Guides</div>
                    </div>
                    <div style="border-right: 1px solid rgba(4, 47, 46, 0.1); padding-right: 1.5rem;">
                        <div style="font-size: 0.7rem; color: var(--color-teal-900); font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 0.75rem;">Premium</div>
                        <div style="font-size: 1.6rem; font-weight: 400; font-family: var(--font-serif); color: var(--color-stone-900);">Luxury Lodges</div>
                    </div>
                    <div>
                        <div style="font-size: 0.7rem; color: var(--color-teal-900); font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 0.75rem;">Bespoke</div>
                        <div style="font-size: 1.6rem; font-weight: 400; font-family: var(--font-serif); color: var(--color-stone-900);">Custom Itineraries</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact -->
<section class="contact-section" style="border-top: none;">
    <div class="container">
        <!-- Contact details and form -->
        <div class="grid-responsive-split">
            <!-- Left -->
            <div>
                <p class="text-xs-caps" style="color: var(--color-amber-400); margin-bottom: 0.5rem; font-weight: 600;">GET IN TOUCH</p>
                <h2 style="font-size: 3rem; font-family: var(--font-serif); margin-bottom: 2rem; color: white;">Plan Your Dream<br>Adventure</h2>
                <p style="font-size: 1.1rem; color: rgba(255, 255, 255, 0.8); margin-bottom: 3rem; line-height: 1.8;">
                    Ready to embark on the journey of a lifetime? Our expert team is here to help you craft the perfect Himalayan experience tailored to your dreams.
                </p>

                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div style="display: flex; align-items: start; gap: 1rem;">
                        <i class="fa-solid fa-location-dot" style="font-size: 1.25rem; color: white;"></i>
                        <div>
                            <div style="font-weight: 600; margin-bottom: 0.25rem; color: white;">Visit Us</div>
                            <div style="color: rgba(255, 255, 255, 0.6); font-size: 0.9rem;">Thamel, Kathmandu, Nepal</div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: start; gap: 1rem;">
                        <i class="fa-solid fa-envelope" style="font-size: 1.25rem; color: #bae6fd;"></i>
                        <div>
                            <div style="font-weight: 600; margin-bottom: 0.25rem; color: white;">Email</div>
                            <div style="color: rgba(255, 255, 255, 0.6); font-size: 0.9rem;">hello@paila.travel</div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: start; gap: 1rem;">
                         <i class="fa-solid fa-phone" style="font-size: 1.25rem; color: #f43f5e;"></i>
                        <div>
                            <div style="font-weight: 600; margin-bottom: 0.25rem; color: white;">Call</div>
                            <div style="color: rgba(255, 255, 255, 0.6); font-size: 0.9rem;">+977 1 234 5678</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="contact-form-card" id="contact" style="border: none;">
                <h3 style="font-size: 1.5rem; margin-bottom: 1.5rem; font-family: var(--font-serif);">Send us a message</h3>
                
                <?php if(isset($_SESSION['success'])): ?>
                    <div style="background: rgba(4, 47, 46, 0.08); color: var(--color-teal-900); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid rgba(4, 47, 46, 0.24);">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['error'])): ?>
                    <div style="background: #fef2f2; color: #b91c1c; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid #fecaca;">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="<?php echo url('actions/inquiries/submit_inquiry.php'); ?>" method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;" data-validate>
                    <?php echo csrf_field(); ?>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 0.5rem; color: var(--color-stone-700);">Full Name</label>
                        <input type="text" name="name" placeholder="Your name" style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-stone-300); border-radius: 0.5rem; font-size: 0.95rem; background: white;" data-rules="required|min:3">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 0.5rem; color: var(--color-stone-700);">Email</label>
                        <input type="email" name="email" placeholder="you@email.com" style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-stone-300); border-radius: 0.5rem; font-size: 0.95rem; background: white;" data-rules="required|email">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 0.5rem; color: var(--color-stone-700);">Phone</label>
                        <input type="tel" name="phone" placeholder="+977 ..." style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-stone-300); border-radius: 0.5rem; font-size: 0.95rem; background: white;" data-rules="phone">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 0.5rem; color: var(--color-stone-700);">How can we help?</label>
                        <textarea name="message" rows="4" placeholder="Tell us about your dream trip..." style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-stone-300); border-radius: 0.5rem; font-size: 0.95rem; resize: vertical; background: white;" data-rules="required|min:10"></textarea>
                    </div>
                    <button type="submit" class="btn" style="width: 100%; background: var(--color-teal-900); color: white; padding: 1rem; border-radius: 8px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 0.5rem; border: none; cursor: pointer; transition: background 0.3s;">
                        <i class="fa-solid fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
