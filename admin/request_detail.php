<?php
require_once '../config/db.php';
require_once '../helpers/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_login();
if (!is_admin()) { die("Access Denied."); }

$requestId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($requestId == 0) redirect('manage_requests.php');

$user = get_user();

// Fetch request data
$stmt = $pdo->prepare("SELECT * FROM private_requests WHERE id = ?");
$stmt->execute([$requestId]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) die("Request not found.");

// Handle status changes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'send_message') {
        $msgBody = trim($_POST['message']);
        if ($msgBody && $request['user_id']) {
            $sent = send_message($user['id'], $request['user_id'], 'private_request', $requestId, $msgBody);
            if ($sent) {
                // Notify request owner
                create_notification($request['user_id'], "New Message from Admin", "You have a new message about your access request.", "user_requests.php?id=$requestId");
            }
        }
    } elseif (in_array($_POST['action'], ['approve', 'reject'])) {
        $newStatus = ($_POST['action'] === 'approve') ? 'approved' : 'rejected';
        
        // Create access code
        $accessCode = null;
        if ($newStatus === 'approved' && empty($request['access_code'])) {
            $year = date('Y');
            $randomPart = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            $accessCode = "ESTATE-{$year}-{$randomPart}";
            
            $stmt = $pdo->prepare("UPDATE private_requests SET status = ?, access_code = ? WHERE id = ?");
            $stmt->execute([$newStatus, $accessCode, $requestId]);
            $request['access_code'] = $accessCode;
        } else {
            $stmt = $pdo->prepare("UPDATE private_requests SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $requestId]);
        }
        
        $request['status'] = $newStatus;
        
        // Send status notification
        if ($request['user_id']) {
            $message = $newStatus === 'approved' 
                ? "Your private access request has been approved! Your access code is: {$accessCode}" 
                : "Your private access request status has been updated to: {$newStatus}";
            create_notification($request['user_id'], "Access Request Update", $message, "user_requests.php?id=$requestId");
        }
    }
}

$messages = get_message_history('private_request', $requestId);

$pageTitle = "Request Details #$requestId";
$base = '../';
?>
<div class="admin-hero">
<?php include '../includes/header.php'; ?>
<section style="padding: 4rem 0;">
    <div class="container">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
             <a href="manage_requests.php" style="color: var(--color-stone-600); text-decoration: none; font-weight: 600;"><i class="fa-solid fa-arrow-left"></i> Back to Requests</a>
        </div>
        <h1 style="font-size: 3rem; font-family: var(--font-serif); color: var(--color-stone-900); margin: 0;">Request #<?php echo $requestId; ?></h1>
    </div>
</section>
</div>

<div class="container" style="padding-top: 4rem; padding-bottom: 5rem;">
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        
        <!-- Request details column -->
        <div>
            <!-- Applicant info section -->
            <div style="background: white; border: 1px solid var(--color-stone-200); border-radius: 1rem; padding: 2rem; margin-bottom: 2rem;">
                <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-stone-100); padding-bottom: 0.5rem;">Applicant Details</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div>
                        <div style="font-size: 0.85rem; color: var(--color-stone-500); text-transform: uppercase; letter-spacing: 0.05em;">Name</div>
                        <div style="font-weight: 500; font-size: 1.1rem;"><?php echo e($request['full_name']); ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; color: var(--color-stone-500); text-transform: uppercase; letter-spacing: 0.05em;">Email</div>
                        <div style="font-weight: 500; font-size: 1.1rem;"><?php echo e($request['email']); ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.85rem; color: var(--color-stone-500); text-transform: uppercase; letter-spacing: 0.05em;">Status</div>
                        <div>
                             <?php if($request['status'] == 'pending'): ?>
                                <span style="background: #fffbeb; color: #92400e; padding: 0.25rem 0.6rem; border-radius: 4px; font-weight: 600;">Pending</span>
                            <?php elseif($request['status'] == 'approved'): ?>
                                <span style="background: #ecfdf5; color: #065f46; padding: 0.25rem 0.6rem; border-radius: 4px; font-weight: 600;">Approved</span>
                            <?php else: ?>
                                <span style="background: #fef2f2; color: #991b1b; padding: 0.25rem 0.6rem; border-radius: 4px; font-weight: 600;">Rejected</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                         <div style="font-size: 0.85rem; color: var(--color-stone-500); text-transform: uppercase; letter-spacing: 0.05em;">User Account</div>
                         <div><?php echo $request['user_id'] ? '#' . $request['user_id'] : 'Guest'; ?></div>
                    </div>
                </div>
                
                <?php if($request['access_code'] && $request['status'] === 'approved'): ?>
                <div style="margin-top: 1.5rem; padding: 1rem; background: #ecfdf5; border: 1px solid #10b981; border-radius: 0.5rem;">
                    <div style="font-size: 0.85rem; color: #065f46; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Access Code</div>
                    <div style="font-size: 1.25rem; font-weight: 700; color: #065f46; font-family: monospace; letter-spacing: 0.1em;">
                        <?php echo e($request['access_code']); ?>
                    </div>
                    <div style="font-size: 0.75rem; color: #059669; margin-top: 0.5rem;">
                        <i class="fa-solid fa-circle-check"></i> Share this code with the applicant
                    </div>
                </div>
                <?php endif; ?>
                
                <div>
                     <div style="font-size: 0.85rem; color: var(--color-stone-500); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Request Details</div>
                     <p style="background: var(--color-stone-50); padding: 1rem; border-radius: 0.5rem; line-height: 1.6; color: var(--color-stone-700);">
                         <?php echo nl2br(e($request['details'])); ?>
                     </p>
                </div>
            </div>
            
            <!-- Dialogue history section -->
            <div style="background: white; border: 1px solid var(--color-stone-200); border-radius: 1rem; padding: 2rem;">
                <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">Dialogue</h3>
                
                <?php if(!$request['user_id']): ?>
                    <div style="padding: 1rem; background: #fffbe6; border: 1px solid #ffe58f; border-radius: 0.5rem; color: #856404;">
                        <i class="fa-solid fa-triangle-exclamation"></i> Applicant is a guest. Messaging is unavailable until they register with this email.
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
                        <textarea name="message" required placeholder="Type a message to the applicant..." rows="3" style="width: 100%; padding: 1rem; border: 1px solid var(--color-stone-300); border-radius: 0.5rem; margin-bottom: 1rem; font-family: inherit;"></textarea>
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
                <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1.5rem; text-transform: uppercase; color: var(--color-stone-500);">Actions</h3>
                
                <form method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php if($request['status'] !== 'approved'): ?>
                    <button type="submit" name="action" value="approve" class="btn" style="width: 100%; background: var(--color-emerald-600); color: white; border: none; padding: 1rem;">
                        <i class="fa-solid fa-check"></i> Approve Request
                    </button>
                    <?php endif; ?>
                    
                    <?php if($request['status'] !== 'rejected'): ?>
                    <button type="submit" name="action" value="reject" class="btn" style="width: 100%; background: white; border: 1px solid var(--color-red-200); color: var(--color-red-600); padding: 1rem;">
                        <i class="fa-solid fa-ban"></i> Reject Request
                    </button>
                    <?php endif; ?>
                </form>
                
                <hr style="border: 0; border-top: 1px solid var(--color-stone-200); margin: 1.5rem 0;">
                
                <div style="font-size: 0.9rem; color: var(--color-stone-600);">
                    <p style="margin-bottom: 0.5rem;"><i class="fa-regular fa-envelope"></i> <?php echo e($request['email']); ?></p>
                    <p><i class="fa-regular fa-clock"></i> Applied on <?php echo date('M d, Y', strtotime($request['created_at'])); ?></p>
                </div>
            </div>
        </div>
        
    </div>
</div>

<?php include '../includes/footer.php'; ?>
