<?php
require_once __DIR__ . '/../helpers/security.php';
if (session_status() === PHP_SESSION_NONE) {
    secure_session_start();
}

require_once '../helpers/functions.php';
require_once '../config/db.php';

if (!is_logged_in()) {
    redirect(url('public/authentication/login.php'));
}

$user = get_user();
$user_id = $user['id'];

// Load user bookings
$stmt = $pdo->prepare("
    SELECT
        b.*,
        t.title as tour_title,
        t.image as tour_image,
        t.duration,
        t.location as tour_location,
        t.category as tour_category,
        gu.username as guide_username,
        gp.full_name as guide_full_name,
        gp.phone as guide_phone,
        gp.languages as guide_languages,
        gp.specialties as guide_specialties,
        gp.experience_years as guide_experience_years,
        gp.rating as guide_rating,
        gp.avatar as guide_avatar
    FROM bookings b
    LEFT JOIN tours t ON b.tour_id = t.id
    LEFT JOIN users gu ON gu.id = b.tour_guide_id
    LEFT JOIN guide_profiles gp ON gp.user_id = gu.id
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

$pageTitle = 'My Bookings | पाइला';
include '../includes/header.php';
?>

<div class="dashboard-hero" style="background: linear-gradient(135deg, var(--color-teal-900) 0%, var(--color-teal-800) 100%); padding: 6rem 0 4rem; color: white;">
    <div class="container">
        <h1 style="font-size: 3rem; font-family: var(--font-serif); margin-bottom: 1rem;">My Bookings</h1>
        <p style="opacity: 0.8; font-size: 1.1rem;">Track your journey requests and upcoming adventures.</p>
    </div>
</div>

<div style="padding: 4rem 0; min-height: 50vh; background: var(--body-bg);">
    <div class="container">
        <?php if (empty($bookings)): ?>
            <div style="text-align: center; padding: 5rem 2rem; background: var(--card-bg); border-radius: 1.5rem; border: 1px solid var(--border-color);">
                <div style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.2;"><i class="fa-solid fa-calendar-xmark"></i></div>
                <h2 style="font-family: var(--font-serif); margin-bottom: 1rem;">No bookings found</h2>
                <p style="color: var(--text-muted); margin-bottom: 2rem;">It looks like you haven't booked any journeys yet.</p>
                <a href="<?php echo url('public/collection.php'); ?>" class="btn btn-primary" style="border-radius: 50px; padding: 1rem 2.5rem;">Explore Collections</a>
            </div>
        <?php else: ?>
            <div style="display: grid; gap: 2rem;">
                <?php foreach ($bookings as $booking): ?>
                    <?php
                    $bookingImage = get_tour_image([
                        'image' => $booking['tour_image'] ?? '',
                        'location' => $booking['tour_location'] ?? '',
                        'category' => $booking['tour_category'] ?? '',
                    ]);
                    ?>
                    <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 1.5rem; display: flex; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); transition: transform 0.3s ease;">
                        <div style="width: 250px; height: 180px; flex-shrink: 0; position: relative;">
                            <img src="<?php echo e($bookingImage); ?>" alt="<?php echo e($booking['tour_title'] ?: 'Booked journey'); ?>" loading="lazy" decoding="async" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div style="padding: 2rem; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div style="font-size: 0.8rem; color: var(--color-teal-700); font-weight: 700; text-transform: uppercase; margin-bottom: 0.5rem; letter-spacing: 0.05em;">
                                        Booking #<?php echo $booking['id']; ?> - <?php echo date('M d, Y', strtotime($booking['created_at'])); ?>
                                        <?php if(!empty($booking['is_premium'])): ?>
                                            <span style="margin-left: 0.5rem; background: var(--color-teal-900); color: var(--color-amber-500); border-radius: 999px; padding: 0.2rem 0.55rem;">Premium Guide</span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="<?php echo url('public/booking_detail.php?id=' . $booking['id']); ?>" class="btn" style="padding: 0.25rem 0.75rem; border: 1px solid var(--border-color); font-size: 0.8rem; border-radius: 2rem;">Details</a>
                                </div>
                                <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;"><?php echo e($booking['tour_title'] ?: 'Custom Journey'); ?></h3>
                                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0;">
                                    <i class="fa-solid fa-clock"></i> <?php echo e($booking['duration'] ?: 'N/A'); ?> - <i class="fa-solid fa-user"></i> <?php echo e($booking['customer_name']); ?>
                                </p>
                            </div>
                            
                            <?php 
                            $status = $booking['status'] ?? 'pending';
                            echo render_booking_timeline($status, true); 
                            ?>

                            <?php if($status === 'confirmed' && !empty($booking['tour_guide_id'])): ?>
                                <?php
                                $guideName = $booking['guide_full_name'] ?: $booking['guide_username'];
                                $guideAvatar = $booking['guide_avatar'] ?: 'assets/images/Pokhara/pexels-photo-30131353.jpeg';
                                ?>
                                <div style="margin-top: 1rem; border-top: 1px solid var(--border-color); padding-top: 1rem; display: flex; align-items: center; gap: 0.9rem;">
                                    <img src="<?php echo e(url($guideAvatar)); ?>" alt="<?php echo e($guideName); ?>" loading="lazy" decoding="async" style="width: 58px; height: 58px; border-radius: 50%; object-fit: cover; border: 3px solid white; box-shadow: 0 6px 18px rgba(0,0,0,0.12);">
                                    <div style="flex: 1;">
                                        <div style="font-size: 0.74rem; color: var(--color-amber-600); font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em;"><?php echo !empty($booking['is_premium']) ? 'Premium guide privilege' : 'Assigned guide'; ?></div>
                                        <div style="font-weight: 800; color: var(--color-stone-900);"><?php echo e($guideName); ?> <span style="font-size: 0.85rem; color: var(--color-stone-500); font-weight: 600;">/ <?php echo number_format((float)($booking['guide_rating'] ?: 4.8), 1); ?></span></div>
                                        <div style="font-size: 0.85rem; color: var(--color-stone-500);"><?php echo e($booking['guide_specialties'] ?: $booking['guide_languages'] ?: 'Local Nepal guide'); ?></div>
                                    </div>
                                    <a href="<?php echo url('public/booking_detail.php?id=' . $booking['id']); ?>" style="width: 38px; height: 38px; border-radius: 50%; background: var(--color-teal-900); color: white; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;" title="View guide details"><i class="fa-solid fa-chevron-right"></i></a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
