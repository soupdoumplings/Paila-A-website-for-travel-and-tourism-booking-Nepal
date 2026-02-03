<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/functions.php';

include __DIR__ . '/../includes/header.php';
?>

<!-- Hero -->
<section style="background: linear-gradient(180deg, #A5D1E8 0%, #D6F1FF 40%, #FFFFFF 100%); padding: 8rem 0 5rem; text-align: center;">
    <div class="container">
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(14, 165, 233, 0.1); color: #0d9488; padding: 0.4rem 1rem; border-radius: 2rem; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 2rem;">
            <i class="fa-solid fa-location-dot"></i> RECORDED LOCATIONS
        </div>
        <h1 style="font-size: 5rem; font-family: var(--font-serif); margin-bottom: 1.5rem; color: var(--color-stone-900); font-weight: 400;">The Archive</h1>
        <p style="font-size: 1.25rem; color: var(--color-stone-600); max-width: 600px; margin: 0 auto;">
            Explore Nepal's most extraordinary destinations
        </p>
    </div>
</section>

<!-- Grid -->
<section style="padding: 2rem 0 8rem; background: white;">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2.5rem;">
            <?php
            $destinations = [
                [
                    'name' => 'Everest',
                    'location' => 'Solukhumbu',
                    'image' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=600&q=80',
                    'icon' => 'fa-solid fa-mountain'
                ],
                [
                    'name' => 'Chitwan',
                    'location' => 'Narayani',
                    'image' => 'https://images.unsplash.com/photo-1533130061792-64b345e4a833?w=600&q=80',
                    'icon' => 'fa-solid fa-tree'
                ],
                [
                    'name' => 'Langtang',
                    'location' => 'Bagmati',
                    'image' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=600&q=80',
                    'icon' => 'fa-solid fa-compass'
                ],
                [
                    'name' => 'Pokhara',
                    'location' => 'Gandaki',
                    'image' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=600&q=80',
                    'icon' => 'fa-solid fa-water'
                ],
                [
                    'name' => 'Annapurna',
                    'location' => 'Gandaki',
                    'image' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=600&q=80',
                    'icon' => 'fa-solid fa-person-hiking'
                ],
                [
                    'name' => 'Mustang',
                    'location' => 'Dhaulagiri',
                    'image' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=600&q=80',
                    'icon' => 'fa-solid fa-wind'
                ],
                [
                    'name' => 'Kathmandu',
                    'location' => 'Bagmati',
                    'image' => 'https://images.unsplash.com/photo-1585409677983-0f6c41ca9c3b?w=600&q=80',
                    'icon' => 'fa-solid fa-monument'
                ],
                [
                    'name' => 'Rara Lake',
                    'location' => 'Karnali',
                    'image' => 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=600&q=80',
                    'icon' => 'fa-solid fa-droplet'
                ],
                [
                    'name' => 'Lumbini',
                    'location' => 'Lumbini',
                    'image' => 'https://images.unsplash.com/photo-1585409677983-0f6c41ca9c3b?w=600&q=80',
                    'icon' => 'fa-solid fa-hands-praying'
                ]
            ];

            // Folder path mapping
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
                <a href="<?php echo url('public/gallery.php?location=' . urlencode($dest['name'])); ?>" class="hover-zoom-card" style="position: relative; border-radius: 2rem; overflow: hidden; height: 500px; display: block; text-decoration: none; transition: transform 0.4s ease; box-shadow: 0 10px 30px rgba(0,0,0,0.15);" onmouseover="this.style.transform='translateY(-10px)'" onmouseout="this.style.transform='translateY(0)'">
                    <!-- Image -->
                    <img src="<?php echo (strpos($dest['image'], 'http') === 0) ? $dest['image'] : url($dest['image']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    
                    <!-- Overlay -->
                    <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.2) 60%, rgba(0,0,0,0) 100%);"></div>
                    
                    <!-- Content -->
                    <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 2.5rem; color: white;">
                        <!-- Tag -->
                        <div style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--color-amber-400); font-size: 0.9rem; font-weight: 500; margin-bottom: 0.5rem;">
                            <i class="<?php echo $dest['icon']; ?>"></i>
                            <span><?php echo $dest['location']; ?></span>
                        </div>
                        
                        <!-- Name -->
                        <h2 style="font-family: var(--font-serif); font-size: 2.5rem; margin-bottom: 2rem; font-weight: 400; line-height: 1.1;">
                            <?php echo $dest['name']; ?>
                        </h2>
                        
                        <!-- CTA -->
                        <div style="display: flex; align-items: center; gap: 0.75rem; font-weight: 600; font-size: 1rem; opacity: 0.9;">
                            Explore Tours
                            <i class="fa-solid fa-arrow-right-long"></i>
                        </div>
                    </div>
                </a>
                <?php
            }
            ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
