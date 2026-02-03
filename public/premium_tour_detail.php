<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../config/db.php';
require_once '../helpers/functions.php';

// Check access
if (!isset($_SESSION['premium_access']) || $_SESSION['premium_access'] !== true) {
    redirect(url('public/premium.php'));
}

// Require login
if (!is_logged_in()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    redirect(url('public/authentication/login.php'));
}

$tour_id = isset($_GET['id']) ? $_GET['id'] : '';

// Tour data
$tours = [
    'helicopter' => [
        'id' => 'helicopter',
        'title' => 'Luxury Everest Helicopter Tour',
        'category' => 'LUXURY',
        'difficulty' => 'Easy',
        'duration' => '1 Day',
        'price' => 1999,
        'image' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=1200&q=80',
        'description' => 'Experience the majesty of Mount Everest from a private helicopter. This exclusive tour includes champagne breakfast at Everest View Hotel, aerial photography opportunities, and personalized commentary from expert pilots.',
        'highlights' => [
            'Private helicopter exclusively for your group',
            'Champagne breakfast at Everest View Hotel (3,880m)',
            'Aerial views of Everest, Lhotse, and Ama Dablam',
            'Professional aerial photography assistance',
            'Luxury ground transportation included'
        ],
        'max_travelers' => 5
    ],
    'mustang' => [
        'id' => 'mustang',
        'title' => 'Upper Mustang Expedition',
        'category' => 'ADVENTURE',
        'difficulty' => 'Moderate',
        'duration' => '12 Days',
        'price' => 7199,
        'image' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&q=80',
        'description' => 'Journey to the forbidden kingdom of Mustang, a restricted area that preserves ancient Tibetan Buddhist culture. This exclusive expedition includes special permits, luxury camping, and access to centuries-old monasteries.',
        'highlights' => [
            'Restricted area permit and special access',
            'Private guide and support team',
            'Luxury glamping accommodations',
            'Visit to ancient Lo Manthang walled city',
            'Exclusive monastery access and cultural ceremonies',
            'Helicopter evacuation insurance included'
        ],
        'max_travelers' => 8
    ]
];

if (!isset($tours[$tour_id])) {
    redirect(url('public/premium.php'));
}

$tour = $tours[$tour_id];

$pageTitle = $tour['title'] . ' | पाइला';
include '../includes/header.php';
?>

<!-- Hero -->
<section style="position: relative; height: 70vh; min-height: 500px; background: url('<?php echo $tour['image']; ?>') center/cover; display: flex; align-items: flex-end;">
    <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.3) 60%, rgba(0,0,0,0) 100%);"></div>
    <div class="container" style="position: relative; z-index: 10; padding-bottom: 3rem; color: white;">
        <p style="color: var(--color-amber-400); margin-bottom: 1rem; font-size: 0.9rem; font-weight: 600; text-transform: uppercase;">
            <i class="fa-solid fa-lock"></i> PRIVATE JOURNEY
        </p>
        <h1 style="font-size: 3.5rem; font-family: var(--font-serif); margin-bottom: 1rem;">
            <?php echo e($tour['title']); ?>
        </h1>
        <div style="display: flex; gap: 2rem; font-size: 1rem; opacity: 0.9;">
            <span><i class="fa-regular fa-clock"></i> <?php echo $tour['duration']; ?></span>
            <span><i class="fa-solid fa-signal"></i> <?php echo $tour['difficulty']; ?></span>
            <span style="color: var(--color-amber-400); font-size: 1.5rem; font-weight: 600;">Rs <?php echo number_format($tour['price']); ?></span>
        </div>
    </div>
</section>

<!-- Content -->
<section style="padding: 5rem 0; background: white;">
    <div class="container" style="max-width: 1100px;">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 4rem;">
            
            <!-- Tour Info -->
            <div>
                <h2 style="font-size: 2rem; font-family: var(--font-serif); margin-bottom: 2rem;">About This Experience</h2>
                <p style="font-size: 1.125rem; line-height: 1.8; color: var(--color-stone-700); margin-bottom: 3rem;">
                    <?php echo e($tour['description']); ?>
                </p>
                
                <h3 style="font-size: 1.5rem; font-family: var(--font-serif); margin-bottom: 1.5rem;">Highlights</h3>
                <ul style="list-style: none; padding: 0; margin-bottom: 3rem;">
                    <?php foreach ($tour['highlights'] as $highlight): ?>
                        <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--color-stone-100); display: flex; align-items: flex-start; gap: 1rem;">
                            <i class="fa-solid fa-circle-check" style="color: var(--color-amber-400); margin-top: 0.25rem;"></i>
                            <span style="flex: 1; line-height: 1.6;"><?php echo e($highlight); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Booking Form -->
            <div>
                <div style="background: var(--color-stone-50); border: 1px solid var(--color-stone-200); border-radius: 1rem; padding: 2rem; position: sticky; top: 2rem;">
                    <h3 style="font-size: 1.5rem; font-family: var(--font-serif); margin-bottom: 1.5rem;">Request Booking</h3>
                    
                    <form action="<?php echo url('actions/bookings/process_booking.php'); ?>" method="POST">
                        <input type="hidden" name="tour_id" value="0">
                        <input type="hidden" name="premium_tour_id" value="<?php echo $tour['id']; ?>">
                        <input type="hidden" name="customer_name" value="<?php echo e($_SESSION['username'] ?? ''); ?>">
                        <input type="hidden" name="contact_email" value="<?php echo e($_SESSION['email'] ?? ''); ?>">
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 0.5rem;">Travel Date</label>
                            <input type="date" name="travel_date" required min="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-stone-300); border-radius: 0.5rem;">
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 0.5rem;">Number of Travelers</label>
                            <input type="number" name="travelers" required min="1" max="<?php echo $tour['max_travelers']; ?>" value="2" style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-stone-300); border-radius: 0.5rem;">
                            <small style="color: var(--color-stone-500);">Maximum: <?php echo $tour['max_travelers']; ?> people</small>
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 0.5rem;">Phone Number</label>
                            <input type="tel" name="phone" style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-stone-300); border-radius: 0.5rem;">
                        </div>
                        
                        <div style="margin-bottom: 2rem;">
                            <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 0.5rem;">Special Requests (Optional)</label>
                            <textarea name="special_requests" rows="3" style="width: 100%; padding: 0.75rem; border: 1px solid var(--color-stone-300); border-radius: 0.5rem; resize: vertical;"></textarea>
                        </div>
                        
                        <div style="background: #fffbeb; border: 1px solid #fbbf24; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; font-size: 0.85rem; color: #92400e;">
                            <i class="fa-solid fa-circle-info"></i> This is a booking request. Our team will contact you within 24 hours to finalize details.
                        </div>
                        
                        <button type="submit" class="btn" style="width: 100%; background: var(--color-stone-900); color: white; padding: 1rem; font-weight: 600; font-size: 1rem; border: none; cursor: pointer; border-radius: 0.5rem;">
                            Request Booking • Rs <?php echo number_format($tour['price']); ?>
                        </button>
                    </form>
                </div>
            </div>
            
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
