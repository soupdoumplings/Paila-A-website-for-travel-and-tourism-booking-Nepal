<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../config/db.php';
require_once '../helpers/functions.php';

if (!is_logged_in()) redirect(url('public/authentication/login.php'));

$user = get_user();
$bookingId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($bookingId == 0) redirect('my_bookings.php');

// Load booking data
$stmt = $pdo->prepare("
    SELECT b.*, t.title as tour_title, t.image as tour_image, t.duration 
    FROM bookings b
    LEFT JOIN tours t ON b.tour_id = t.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$bookingId, $user['id']]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    redirect('my_bookings.php');
}



// Process user message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    $msgBody = trim($_POST['message']);
    if ($msgBody) {
        // Find admin user
        $adminStmt = $pdo->query("SELECT id FROM users WHERE role_id = 1 LIMIT 1");
        $adminId = $adminStmt->fetchColumn();
        
        $actualReceiverId = $adminId ?: 1; // Fallback
        
        send_message($user['id'], $actualReceiverId, 'booking', $bookingId, $msgBody);
        
        // Alert admin users
        $allAdmins = $pdo->query("SELECT id FROM users WHERE role_id IN (1, 2)");
        while ($row = $allAdmins->fetch(PDO::FETCH_ASSOC)) {
            create_notification($row['id'], "New Message: Booking #$bookingId", "User sent a message regarding booking #$bookingId", "admin/booking_detail.php?id=$bookingId");
        }
    }
}

$messages = get_message_history('booking', $bookingId);

$pageTitle = 'Booking #' . $bookingId . ' | पाइला';
include '../includes/header.php';
?>

<div class="dashboard-hero" style="background: linear-gradient(135deg, var(--color-stone-800) 0%, var(--color-stone-900) 100%); padding: 6rem 0 4rem; color: white;">
    <div class="container">
        <div style="margin-bottom: 1rem;"><a href="my_bookings.php" style="color: rgba(255,255,255,0.7); text-decoration: none;"><i class="fa-solid fa-arrow-left"></i> My Bookings</a></div>
        <h1 style="font-size: 3rem; font-family: var(--font-serif); margin-bottom: 1rem;"><?php echo e($booking['tour_title'] ?: 'Custom Journey'); ?></h1>
        <p style="opacity: 0.8; font-size: 1.1rem;">
            Booking #<?php echo $bookingId; ?> • <?php echo date('M d, Y', strtotime($booking['travel_date'])); ?>
        </p>
    </div>
</div>

<div style="padding: 4rem 0; background: var(--body-bg); min-height: 60vh;">
    <div class="container">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 3rem;">
            
            <!-- Details column -->
            <div style="display: flex; flex-direction: column; gap: 2rem;">
                
                <!-- Status Card -->
                <div style="background: white; border: 1px solid var(--border-color); border-radius: 1rem; padding: 2rem;">
                    <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">Status</h3>
                    <?php echo render_booking_timeline($booking['status']); ?>
                </div>

                <!-- Messages -->
                <div style="background: white; border: 1px solid var(--border-color); border-radius: 1rem; padding: 2rem;">
                    <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">Communication</h3>
                    
                    <div style="max-height: 400px; overflow-y: auto; margin-bottom: 2rem; padding-right: 0.5rem;">
                        <?php if (empty($messages)): ?>
                            <div style="text-align: center; color: var(--text-muted); padding: 2rem; background: var(--color-stone-50); border-radius: 0.5rem;">
                                <p>No messages yet. Feel free to ask us anything!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $m): 
                                $isMe = ($m['sender_id'] == $user['id']);
                            ?>
                                <div style="display: flex; justify-content: <?php echo $isMe ? 'flex-end' : 'flex-start'; ?>; margin-bottom: 1rem;">
                                    <div style="max-width: 80%; <?php echo $isMe ? 'background: var(--color-stone-800); color: white;' : 'background: var(--color-stone-100); color: var(--color-stone-800);'; ?> padding: 1rem; border-radius: 1rem;">
                                        <div style="font-size: 0.75rem; margin-bottom: 0.25rem; opacity: 0.7; display: flex; justify-content: space-between; gap: 1rem;">
                                            <span><?php echo $isMe ? 'You' : 'पाइला Team'; ?></span>
                                            <span><?php echo date('M d, H:i', strtotime($m['created_at'])); ?></span>
                                        </div>
                                        <div style="line-height: 1.5;"><?php echo nl2br(e($m['message'])); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="send_message">
                        <textarea name="message" required placeholder="Type your message here..." rows="3" style="width: 100%; padding: 1rem; border: 1px solid var(--border-color); border-radius: 0.5rem; margin-bottom: 1rem; font-family: inherit; resize: vertical;"></textarea>
                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">
                            <i class="fa-regular fa-paper-plane" style="margin-right: 0.5rem;"></i> Send Message
                        </button>
                    </form>
                </div>

            </div>

            <!-- Sidebar -->
            <div>
                 <div style="background: white; border: 1px solid var(--border-color); border-radius: 1rem; padding: 2rem; position: sticky; top: 2rem;">
                    <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 1.5rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em;">Booking Details</h3>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <span style="display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">Tour</span>
                        <span style="font-weight: 500; font-size: 1.05rem;"><?php echo e($booking['tour_title'] ?: 'Custom'); ?></span>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <span style="display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">Travelers</span>
                        <span style="font-weight: 500; font-size: 1.05rem;"><?php echo $booking['travelers']; ?> People</span>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <span style="display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">Date</span>
                        <span style="font-weight: 500; font-size: 1.05rem;"><?php echo date('M d, Y', strtotime($booking['travel_date'])); ?></span>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <span style="display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">Contact Email</span>
                        <span style="font-weight: 500; font-size: 1.05rem;"><?php echo e($booking['contact_email']); ?></span>
                    </div>

                 </div>
            </div>

        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
