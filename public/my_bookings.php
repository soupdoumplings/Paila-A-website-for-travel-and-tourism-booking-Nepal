<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
    SELECT b.*, t.title as tour_title, t.image as tour_image, t.duration 
    FROM bookings b
    LEFT JOIN tours t ON b.tour_id = t.id
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
                    <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 1.5rem; display: flex; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); transition: transform 0.3s ease;">
                        <div style="width: 250px; height: 180px; flex-shrink: 0; position: relative;">
                            <img src="<?php echo $booking['tour_image'] ? 'uploads/'.$booking['tour_image'] : 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=400&q=80'; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div style="padding: 2rem; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div style="font-size: 0.8rem; color: var(--color-teal-700); font-weight: 700; text-transform: uppercase; margin-bottom: 0.5rem; letter-spacing: 0.05em;">
                                        Booking #<?php echo $booking['id']; ?> • <?php echo date('M d, Y', strtotime($booking['created_at'])); ?>
                                    </div>
                                    <a href="<?php echo url('public/booking_detail.php?id=' . $booking['id']); ?>" class="btn" style="padding: 0.25rem 0.75rem; border: 1px solid var(--border-color); font-size: 0.8rem; border-radius: 2rem;">Details</a>
                                </div>
                                <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;"><?php echo e($booking['tour_title'] ?: 'Custom Journey'); ?></h3>
                                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0;">
                                    <i class="fa-solid fa-clock"></i> <?php echo e($booking['duration'] ?: 'N/A'); ?> • <i class="fa-solid fa-user"></i> <?php echo e($booking['customer_name']); ?>
                                </p>
                            </div>
                            
                            <?php 
                            $status = $booking['status'] ?? 'pending';
                            echo render_booking_timeline($status, true); 
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
