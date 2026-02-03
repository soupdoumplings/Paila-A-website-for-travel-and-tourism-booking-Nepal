<?php
require_once '../config/db.php';
require_once '../helpers/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_login();
if (!is_admin()) { die("Access Denied."); }

$bookingId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($bookingId == 0) redirect('manage_bookings.php');

$user = get_user();

// Fetch booking information
$stmt = $pdo->prepare("
    SELECT b.*, t.title as tour_title, u.username as guide_name 
    FROM bookings b
    LEFT JOIN tours t ON b.tour_id = t.id
    LEFT JOIN users u ON b.tour_guide_id = u.id
    WHERE b.id = ?
");
$stmt->execute([$bookingId]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) die("Booking not found.");

// Handle admin messages
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'send_message') {
        $msgBody = trim($_POST['message']);
        if ($msgBody && $booking['user_id']) {
            $sent = send_message($user['id'], $booking['user_id'], 'booking', $bookingId, $msgBody);
            if ($sent) {
                // Notify booking owner
                create_notification($booking['user_id'], "New Message regarding Booking #$bookingId", "You have a new message from the admin.", "user_booking_detail.php?id=$bookingId");
            }
        }
    }
}

$messages = get_message_history('booking', $bookingId); // Get message history

$pageTitle = "Booking Details #$bookingId";
$base = '../';
?>
<div class="admin-hero">
<?php include '../includes/header.php'; ?>
<section style="padding: 6rem 0 5rem;">
    <div class="container">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
             <a href="manage_bookings.php" style="color: var(--color-stone-600); text-decoration: none; font-weight: 600;"><i class="fa-solid fa-arrow-left"></i> Back to Bookings</a>
        </div>
        <h1 style="font-size: 3rem; font-family: var(--font-serif); color: var(--color-stone-900); margin: 0;">Booking #<?php echo $bookingId; ?></h1>
    </div>
</section>
</div>

<div class="container" style="margin-top: -2rem; position: relative; z-index: 10; padding-bottom: 5rem;">
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        
        <!-- Booking details column -->
        <div>
            <!-- Booking info -->
            <div style="background: white; border: 1px solid var(--color-stone-200); border-radius: 1rem; padding: 2rem; margin-bottom: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-stone-100); padding-bottom: 1rem;">
                    <h3 style="font-size: 1.25rem; font-weight: 600;"><?php echo e($booking['tour_title'] ?? '[Deleted Tour]'); ?></h3>
                    <?php if($booking['status'] == 'pending'): ?>
                        <span style="background: #fffbeb; color: #92400e; padding: 0.25rem 0.6rem; border-radius: 4px; font-weight: 600;">Pending</span>
                    <?php elseif($booking['status'] == 'confirmed'): ?>
                        <span style="background: #ecfdf5; color: #065f46; padding: 0.25rem 0.6rem; border-radius: 4px; font-weight: 600;">Confirmed</span>
                    <?php else: ?>
                        <span style="background: #fef2f2; color: #991b1b; padding: 0.25rem 0.6rem; border-radius: 4px; font-weight: 600;">Cancelled</span>
                    <?php endif; ?>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div>
                        <div style="font-size: 0.85rem; color: var(--color-stone-500); text-transform: uppercase; letter-spacing: 0.05em;">Customer Name</div>
                        <div style="font-weight: 500; font-size: 1.1rem;"><?php echo e($booking['customer_name']); ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; color: var(--color-stone-500); text-transform: uppercase; letter-spacing: 0.05em;">Contact Email</div>
                        <div style="font-weight: 500; font-size: 1.1rem;"><?php echo e($booking['contact_email']); ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; color: var(--color-stone-500); text-transform: uppercase; letter-spacing: 0.05em;">Travel Date</div>
                        <div style="font-weight: 500;"><?php echo e($booking['travel_date']); ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; color: var(--color-stone-500); text-transform: uppercase; letter-spacing: 0.05em;">Party Size</div>
                        <div style="font-weight: 500;"><?php echo $booking['travelers']; ?> People</div>
                    </div>
                    <div>
                         <div style="font-size: 0.85rem; color: var(--color-stone-500); text-transform: uppercase; letter-spacing: 0.05em;">User Account</div>
                         <div><?php echo $booking['user_id'] ? '#' . $booking['user_id'] : 'Guest Mode'; ?></div>
                    </div>
                    <div>
                         <div style="font-size: 0.85rem; color: var(--color-stone-500); text-transform: uppercase; letter-spacing: 0.05em;">Assigned Guide</div>
                         <div><?php echo $booking['guide_name'] ? e($booking['guide_name']) : 'Not Assigned'; ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Dialogue history section -->
            <div style="background: white; border: 1px solid var(--color-stone-200); border-radius: 1rem; padding: 2rem;">
                <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">Dialogue</h3>
                
                <!-- Check guest account -->
                    <div style="padding: 1rem; background: #fffbe6; border: 1px solid #ffe58f; border-radius: 0.5rem; color: #856404;">
                         <i class="fa-solid fa-triangle-exclamation"></i> Customer booked as guest. Messaging is unavailable until they register or link account.
                    </div>
                <?php else: ?>
                    <div style="max-height: 300px; overflow-y: auto; margin-bottom: 2rem; border: 1px solid var(--color-stone-100); border-radius: 0.5rem; padding: 1rem; background: var(--color-stone-50);">
                        <?php if (empty($messages)): ?>
                            <p style="text-align: center; color: var(--color-stone-400); font-style: italic; margin-top: 1rem;">No messages yet.</p>
                        <?php else: ?>
                            <?php foreach ($messages as $m): 
                                $isMe = ($m['sender_id'] == $user['id']);
                            ?>
                                <div style="display: flex; justify-content: <?php echo $isMe ? 'flex-end' : 'flex-start'; ?>; margin-bottom: 1rem;">
                                    <div style="max-width: 70%; <?php echo $isMe ? 'background: var(--color-stone-900); color: white;' : 'background: white; border: 1px solid var(--color-stone-200);'; ?> padding: 0.75rem 1rem; border-radius: 1rem;">
                                        <div style="font-size: 0.85rem; padding-bottom: 0.25rem; margin-bottom: 0.25rem; border-bottom: 1px solid rgba(200,200,200,0.1); opacity: 0.8; display: flex; justify-content: space-between; gap: 1rem;">
                                            <span><?php echo $isMe ? 'You' : e($m['sender_name']); ?></span>
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
                        <textarea name="message" required placeholder="Type a message to the customer..." rows="3" style="width: 100%; padding: 1rem; border: 1px solid var(--color-stone-300); border-radius: 0.5rem; margin-bottom: 1rem; font-family: inherit;"></textarea>
                        <button type="submit" class="btn" style="background: var(--color-stone-900); color: white; padding: 0.75rem 1.5rem; width: 100%;">
                            <i class="fa-regular fa-paper-plane" style="margin-right: 0.5rem;"></i> Send Message
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Management sidebar panel -->
        <div>
            <div style="background: white; border: 1px solid var(--color-stone-200); border-radius: 1rem; padding: 2rem; position: sticky; top: 2rem;">
                <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1.5rem; text-transform: uppercase; color: var(--color-stone-500);">Management</h3>
                 <p style="font-size: 0.9rem; color: var(--color-stone-600); margin-bottom: 1rem;">
                     To change status or assign guide, use the <a href="manage_bookings.php" style="color: var(--color-amber-600); font-weight: 600;">Booking Manager</a> list view.
                 </p>
            </div>
        </div>
        
    </div>
</div>

<?php include '../includes/footer.php'; ?>
