<?php
require_once '../config/db.php';
require_once '../helpers/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_login();
if (!is_admin()) { die("Access Denied."); }

$user = get_user();

$success = '';
$error = '';

// Handle access actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $requestId = intval($_POST['request_id']);
    $action = $_POST['action'];
    $newStatus = ($action === 'approve') ? 'approved' : 'rejected';
    
    // Update request status
    $stmt = $pdo->prepare("UPDATE private_requests SET status = ? WHERE id = ?");
    if ($stmt->execute([$newStatus, $requestId])) {
        $success = "Request #$requestId updated to $newStatus.";
        
        // Notify request owner
        $reqStmt = $pdo->prepare("SELECT user_id, full_name, email FROM private_requests WHERE id = ?");
        $reqStmt->execute([$requestId]);
        $reqData = $reqStmt->fetch();
        
        if ($reqData && $reqData['user_id']) {
            $title = "Access Request Update";
            $msg = ($newStatus === 'approved') 
                ? "Congratulations! Your private access request has been approved. You can now access The Estate." 
                : "Your private access request has been reviewed. Contact us for more details.";
            
            create_notification($reqData['user_id'], $title, $msg, "user_requests.php");
        } elseif ($reqData) {
            // Handle guest user
        }
    } else {
        $error = "Failed to update request.";
    }
}

// Fetch all requests
$stmt = $pdo->query("SELECT * FROM private_requests ORDER BY created_at DESC");
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Manage Private Requests";
$base = '../';
?>
<div class="admin-hero">
<?php include '../includes/header.php'; ?>
<section style="padding: 4rem 0;">
    <div class="container">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
             <a href="index.php" style="color: var(--color-stone-600); text-decoration: none; font-weight: 600;"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
             <span style="opacity: 0.3;">/</span>
             <span style="opacity: 0.7; text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.8rem; color: var(--color-stone-500);">The Estate</span>
        </div>
        <h1 style="font-size: 3.5rem; font-family: var(--font-serif); color: var(--color-stone-900); margin: 0;">Private Requests</h1>
    </div>
</section>
</div>

<div class="container" style="padding-top: 4rem; padding-bottom: 5rem;">
    <div style="background: white; border: 1px solid var(--color-stone-200); border-radius: 1rem; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 style="font-size: 1.5rem; color: var(--color-stone-900);">Inquiries</h2>
            <div style="color: var(--color-stone-500); font-size: 0.9rem;">Total: <?php echo count($requests); ?></div>
        </div>
        
        <?php if($success): ?>
            <div style="background: #ecfdf5; border: 1px solid #059669; color: #065f46; padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--color-stone-200);">
                        <th style="padding: 1rem; color: var(--color-stone-500); font-weight: 600;">Applicant</th>
                        <th style="padding: 1rem; color: var(--color-stone-500); font-weight: 600;">Status</th>
                        <th style="padding: 1rem; color: var(--color-stone-500); font-weight: 600;">Details</th>
                        <th style="padding: 1rem; color: var(--color-stone-500); font-weight: 600;">Registered</th>
                        <th style="padding: 1rem; text-align: right; color: var(--color-stone-500); font-weight: 600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($requests)): ?>
                        <tr><td colspan="5" style="padding: 2rem; text-align: center; color: var(--color-stone-500);">No requests found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($requests as $r): ?>
                        <tr style="border-bottom: 1px solid var(--color-stone-100);">
                            <td style="padding: 1rem;">
                                <div style="font-weight: 600; color: var(--color-stone-900);"><?php echo e($r['full_name']); ?></div>
                                <div style="font-size: 0.85rem; color: var(--color-stone-500);"><?php echo e($r['email']); ?></div>
                                <div style="font-size: 0.75rem; color: var(--color-stone-400); margin-top: 0.25rem;"><?php echo date('M d, Y', strtotime($r['created_at'])); ?></div>
                            </td>
                            <td style="padding: 1rem;">
                                <?php if($r['status'] == 'pending'): ?>
                                    <span style="background: #fffbeb; color: #92400e; padding: 0.25rem 0.6rem; border-radius: 99px; font-size: 0.75rem; font-weight: 600; border: 1px solid #fcd34d;">Pending</span>
                                <?php elseif($r['status'] == 'approved'): ?>
                                    <span style="background: #ecfdf5; color: #065f46; padding: 0.25rem 0.6rem; border-radius: 99px; font-size: 0.75rem; font-weight: 600; border: 1px solid #6ee7b7;">Approved</span>
                                <?php else: ?>
                                    <span style="background: #fef2f2; color: #991b1b; padding: 0.25rem 0.6rem; border-radius: 99px; font-size: 0.75rem; font-weight: 600; border: 1px solid #fca5a5;">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 1rem; max-width: 300px;">
                                <div style="font-size: 0.9rem; color: var(--color-stone-600); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 250px;">
                                    <?php echo e($r['details'] ?: 'No details provided.'); ?>
                                </div>
                            </td>
                            <td style="padding: 1rem;">
                                <?php if($r['user_id']): ?>
                                    <span style="color: var(--color-emerald-600); font-weight: 600; font-size: 0.9rem;"><i class="fa-solid fa-check"></i> Yes</span>
                                <?php else: ?>
                                    <span style="color: var(--color-stone-400); font-size: 0.9rem;">Guest</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 1rem; text-align: right;">
                                <div style="display: flex; justify-content: flex-end; gap: 0.5rem; align-items: center;">
                                    <a href="request_detail.php?id=<?php echo $r['id']; ?>" class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; background: var(--color-stone-100); color: var(--color-stone-700); border: 1px solid var(--color-stone-300);">
                                        Review
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
