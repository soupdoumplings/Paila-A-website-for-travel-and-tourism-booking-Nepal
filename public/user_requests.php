<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../config/db.php';
require_once '../helpers/functions.php';

if (!is_logged_in()) redirect(url('public/authentication/login.php'));
$user = get_user();

// Check detail mode
$detailId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Process user message
if ($detailId > 0 && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    // Check request owner
    $check = $pdo->prepare("SELECT id FROM private_requests WHERE id = ? AND user_id = ?");
    $check->execute([$detailId, $user['id']]);
    if ($check->rowCount() > 0) {
        $msgBody = trim($_POST['message']);
        if ($msgBody) {
             // Find admin user
            $adminStmt = $pdo->query("SELECT id FROM users WHERE role_id = 1 LIMIT 1");
            $adminId = $adminStmt->fetchColumn() ?: 1;

            send_message($user['id'], $adminId, 'private_request', $detailId, $msgBody);
            
             // Alert admin users
            $allAdmins = $pdo->query("SELECT id FROM users WHERE role_id IN (1, 2)");
            while ($row = $allAdmins->fetch(PDO::FETCH_ASSOC)) {
                create_notification($row['id'], "New Message: Request #$detailId", "User sent a message regarding their access request.", "admin/request_detail.php?id=$detailId");
            }
        }
    }
}

// Load view data
if ($detailId > 0) {
    // Load single request
    $stmt = $pdo->prepare("SELECT * FROM private_requests WHERE id = ? AND user_id = ?");
    $stmt->execute([$detailId, $user['id']]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$request) redirect(url('public/user_requests.php'));
    
    $messages = get_message_history('private_request', $detailId);
    $viewMode = 'detail';
} else {
    // Load all requests
    $stmt = $pdo->prepare("SELECT * FROM private_requests WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user['id']]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $viewMode = 'list';
}

$pageTitle = ($detailId > 0) ? "Request #$detailId" : "My Access Requests";
include '../includes/header.php';
?>

<div class="dashboard-hero" style="background: linear-gradient(135deg, var(--color-stone-800) 0%, var(--color-stone-900) 100%); padding: 6rem 0 4rem; color: white;">
    <div class="container">
        <?php if($viewMode === 'detail'): ?>
            <div style="margin-bottom: 1rem;"><a href="<?php echo url('public/user_requests.php'); ?>" style="color: rgba(255,255,255,0.7); text-decoration: none;"><i class="fa-solid fa-arrow-left"></i> My Requests</a></div>
            <h1 style="font-size: 3rem; font-family: var(--font-serif); margin-bottom: 1rem;">Access Request Details</h1>
        <?php else: ?>
            <h1 style="font-size: 3rem; font-family: var(--font-serif); margin-bottom: 1rem;">My Access Requests</h1>
            <p style="opacity: 0.8; font-size: 1.1rem;">Manage your applications for Private Journeys.</p>
        <?php endif; ?>
    </div>
</div>

<div style="padding: 4rem 0; background: var(--body-bg); min-height: 60vh;">
    <div class="container">
        
        <?php if($viewMode === 'list'): ?>
            <?php if (empty($requests)): ?>
                <div style="text-align: center; padding: 5rem 2rem; background: var(--card-bg); border-radius: 1.5rem; border: 1px solid var(--border-color);">
                     <h2 style="font-family: var(--font-serif); margin-bottom: 1rem;">No requests found</h2>
                     <p style="color: var(--text-muted); margin-bottom: 2rem;">You haven't submitted any private access requests.</p>
                     <a href="<?php echo url('public/premium.php'); ?>" class="btn btn-primary" style="padding: 1rem 2rem;">Apply for Access</a>
                </div>
            <?php else: ?>
                <div style="background: white; border-radius: 1rem; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead style="background: var(--color-stone-50); border-bottom: 1px solid var(--border-color);">
                            <tr>
                                <th style="padding: 1rem; text-align: left; font-weight: 600;">Date</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600;">Status</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600;">Details</th>
                                <th style="padding: 1rem; text-align: right; font-weight: 600;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($requests as $r): ?>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 1rem; color: var(--text-muted);"><?php echo date('M d, Y', strtotime($r['created_at'])); ?></td>
                                    <td style="padding: 1rem;">
                                        <?php if($r['status'] == 'pending'): ?>
                                            <span style="background: #fffbeb; color: #92400e; padding: 0.25rem 0.6rem; border-radius: 4px; font-weight: 600;">Pending</span>
                                        <?php elseif($r['status'] == 'approved'): ?>
                                            <span style="background: #ecfdf5; color: #065f46; padding: 0.25rem 0.6rem; border-radius: 4px; font-weight: 600;">Approved</span>
                                        <?php else: ?>
                                            <span style="background: #fef2f2; color: #991b1b; padding: 0.25rem 0.6rem; border-radius: 4px; font-weight: 600;">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 1rem; font-size: 0.9rem; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo e($r['details']); ?>
                                    </td>
                                    <td style="padding: 1rem; text-align: right;">
                                        <a href="?id=<?php echo $r['id']; ?>" class="btn" style="padding: 0.5rem 1rem; border: 1px solid var(--border-color); color: var(--color-stone-700);">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Detail View -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 3rem;">
                <!-- Messages -->
                <div style="background: white; border: 1px solid var(--border-color); border-radius: 1rem; padding: 2rem;">
                    <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">Communication</h3>
                     <div style="max-height: 400px; overflow-y: auto; margin-bottom: 2rem; padding-right: 0.5rem;">
                        <?php if (empty($messages)): ?>
                            <div style="text-align: center; color: var(--text-muted); padding: 2rem; background: var(--color-stone-50); border-radius: 0.5rem;">
                                <p>No messages yet.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $m): 
                                $isMe = ($m['sender_id'] == $user['id']);
                            ?>
                                <div style="display: flex; justify-content: <?php echo $isMe ? 'flex-end' : 'flex-start'; ?>; margin-bottom: 1rem;">
                                    <div style="max-width: 80%; <?php echo $isMe ? 'background: var(--color-stone-800); color: white;' : 'background: var(--color-stone-100); color: var(--color-stone-800);'; ?> padding: 1rem; border-radius: 1rem;">
                                        <div style="font-size: 0.75rem; margin-bottom: 0.25rem; opacity: 0.7; display: flex; justify-content: space-between; gap: 1rem;">
                                            <span><?php echo $isMe ? 'You' : 'Paila Team'; ?></span>
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

                <!-- Info -->
                <div>
                     <div style="background: white; border: 1px solid var(--border-color); border-radius: 1rem; padding: 2rem;">
                         <div style="margin-bottom: 1.5rem;">
                            <span style="display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">Status</span>
                            <?php if($request['status'] == 'pending'): ?>
                                <span style="background: #fffbeb; color: #92400e; padding: 0.25rem 0.6rem; border-radius: 4px; font-weight: 600;">Pending Review</span>
                            <?php elseif($request['status'] == 'approved'): ?>
                                <span style="background: #ecfdf5; color: #065f46; padding: 0.25rem 0.6rem; border-radius: 4px; font-weight: 600;">Approved</span>
                                <div style="margin-top: 1rem; font-size: 0.9rem; color: #065f46;">
                                    <?php if (!empty($request['access_code'])): ?>
                                        You have been granted access code: <strong style="font-family: monospace; letter-spacing: 1px;"><?php echo e($request['access_code']); ?></strong>
                                    <?php else: ?>
                                        Access approved. Your code will be visible here shortly or sent via email.
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span style="background: #fef2f2; color: #991b1b; padding: 0.25rem 0.6rem; border-radius: 4px; font-weight: 600;">Application Rejected</span>
                            <?php endif; ?>
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                             <span style="display: block; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">Submitted On</span>
                             <span style="font-weight: 500;"><?php echo date('M d, Y', strtotime($request['created_at'])); ?></span>
                        </div>
                     </div>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<?php include '../includes/footer.php'; ?>
