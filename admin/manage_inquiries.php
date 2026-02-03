<?php
require_once '../config/db.php';
require_once '../helpers/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_login();
if (!is_admin()) {
    die("Access Denied.");
}

$success = '';
$error = '';

// Handle inquiry actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $inquiry_id = intval($_POST['inquiry_id']);
        $action = $_POST['action'];
        
        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM inquiries WHERE id = ?");
            if ($stmt->execute([$inquiry_id])) {
                $success = "Inquiry #$inquiry_id deleted.";
            } else {
                $error = "Failed to delete inquiry.";
            }
        } elseif ($action === 'mark_read') {
            $stmt = $pdo->prepare("UPDATE inquiries SET status = 'read' WHERE id = ?");
            if ($stmt->execute([$inquiry_id])) {
                $success = "Inquiry #$inquiry_id marked as read.";
            } else {
                $error = "Failed to update inquiry.";
            }
        }
    }
}

// Fetch all inquiries
$stmt = $pdo->query("SELECT * FROM inquiries ORDER BY created_at DESC");
$inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Manage Inquiries";
$base = '../';
?>
<div class="admin-hero">
<?php include '../includes/header.php'; ?>
<section style="padding: 6rem 0 5rem;">
    <div class="container">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
            <a href="index.php" style="color: var(--color-stone-600); text-decoration: none; font-weight: 600;"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
            <span style="opacity: 0.3;">/</span>
            <span style="opacity: 0.7; text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.8rem; color: var(--color-stone-500);">Communication</span>
        </div>
        <h1 style="font-size: 3.5rem; font-family: var(--font-serif); color: var(--color-stone-900); margin: 0;">Inquiries</h1>
    </div>
</section>
</div>

<div class="container" style="margin-top: -2rem; position: relative; z-index: 10; padding-bottom: 5rem;">
    <!-- Main content panel -->
    <div style="background: white; border: 1px solid var(--color-stone-200); border-radius: 1rem; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        
        <!-- Section header row -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 style="font-size: 1.5rem; color: var(--color-stone-900);">Messages</h2>
            <div style="color: var(--color-stone-500); font-size: 0.9rem;">Total: <?php echo count($inquiries); ?> messages</div>
        </div>
        
        <?php if($success): ?>
            <div style="background: rgba(6, 78, 59, 0.5); border: 1px solid #065f46; color: #a7f3d0; padding: 0.75rem 1rem; border-radius: 0.25rem; margin-bottom: 1.5rem; font-size: 0.875rem;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div style="background: rgba(127, 29, 29, 0.5); border: 1px solid #991b1b; color: #fecaca; padding: 0.75rem 1rem; border-radius: 0.25rem; margin-bottom: 1.5rem; font-size: 0.875rem;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Inquiry message table -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
                <thead>
                    <tr style="background: var(--color-stone-100); color: var(--color-stone-700); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid var(--color-stone-200);">
                        <th style="padding: 1rem; text-align: left;">ID</th>
                        <th style="padding: 1rem; text-align: left;">Date</th>
                        <th style="padding: 1rem; text-align: left;">Sender</th>
                        <th style="padding: 1rem; text-align: left; width: 40%;">Message</th>
                        <th style="padding: 1rem; text-align: center;">Status</th>
                        <th style="padding: 1rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($inquiries)): ?>
                        <tr>
                            <td colspan="6" style="padding: 3rem; text-align: center; color: var(--color-stone-500); font-style: italic;">No inquiries found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($inquiries as $msg): ?>
                            <tr style="border-bottom: 1px solid var(--color-stone-100);">
                                <td style="padding: 1rem; font-family: monospace; color: var(--color-stone-500);">#<?php echo $msg['id']; ?></td>
                                <td style="padding: 1rem; font-size: 0.9rem;"><?php echo date('M j, Y', strtotime($msg['created_at'])); ?></td>
                                <td style="padding: 1rem;">
                                    <div style="font-weight: 600; color: var(--color-stone-900);"><?php echo htmlspecialchars($msg['name']); ?></div>
                                    <div style="font-size: 0.85rem; color: var(--color-stone-500);"><?php echo htmlspecialchars($msg['email']); ?></div>
                                    <div style="font-size: 0.85rem; color: var(--color-stone-500);"><?php echo htmlspecialchars($msg['phone']); ?></div>
                                </td>
                                <td style="padding: 1rem; font-size: 0.9rem; line-height: 1.6; color: var(--color-stone-700);">
                                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <?php if ($msg['status'] === 'new'): ?>
                                        <span style="background: #fef3c7; color: #92400e; padding: 0.25rem 0.5rem; border-radius: 1rem; font-size: 0.75rem; font-weight: 600;">NEW</span>
                                    <?php elseif ($msg['status'] === 'read'): ?>
                                        <span style="background: #e7e5e4; color: #57534e; padding: 0.25rem 0.5rem; border-radius: 1rem; font-size: 0.75rem; font-weight: 600;">READ</span>
                                    <?php else: ?>
                                        <span style="background: #d1fae5; color: #065f46; padding: 0.25rem 0.5rem; border-radius: 1rem; font-size: 0.75rem; font-weight: 600;">REPLIED</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; text-align: right;">
                                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                        <!-- Email reply action -->
                                        <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>?subject=Re: Inquiry from Paila website" 
                                           class="btn" 
                                           style="padding: 0.5rem 0.75rem; font-size: 0.85rem; background: var(--color-stone-100); color: var(--color-stone-700); text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem;">
                                           <i class="fa-solid fa-reply"></i> Reply
                                        </a>

                                        <?php if ($msg['status'] === 'new'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="inquiry_id" value="<?php echo $msg['id']; ?>">
                                                <input type="hidden" name="action" value="mark_read">
                                                <button type="submit" class="btn" style="padding: 0.5rem 0.75rem; font-size: 0.85rem; background: var(--color-stone-100); color: var(--color-stone-700); border: none; cursor: pointer;" title="Mark as Read">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Inquiry delete action -->
                                        <form method="POST" id="delete-form-<?php echo $msg['id']; ?>" style="display: inline;">
                                            <input type="hidden" name="inquiry_id" value="<?php echo $msg['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="button" onclick="handleDeleteInquiry(<?php echo $msg['id']; ?>)" class="btn" style="padding: 0.5rem 0.75rem; font-size: 0.85rem; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; cursor: pointer;" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
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

<style>
/* Responsive mobile layout */
@media (max-width: 768px) {
    .container { padding-left: 1rem; padding-right: 1rem; }
}
</style>

<script>
async function handleDeleteInquiry(id) {
    const confirmed = await showCustomConfirm(
        'Delete Inquiry', 
        `Are you sure you want to delete inquiry #${id}? This action cannot be undone.`
    );
    if (confirmed) {
        document.getElementById('delete-form-' + id).submit();
    }
}

function showCustomConfirm(title, message, confirmText = 'Yes, Delete') {
    return new Promise((resolve) => {
        const modal = document.getElementById('confirmModal');
        const titleEl = document.getElementById('confirmTitle');
        const msgEl = document.getElementById('confirmMessage');
        const proceedBtn = document.getElementById('confirmProceed');
        const cancelBtn = document.getElementById('confirmCancel');

        titleEl.textContent = title;
        msgEl.textContent = message;
        proceedBtn.textContent = confirmText;
        
        modal.style.display = 'flex';

        const handleProceed = () => {
            modal.style.display = 'none';
            cleanup();
            resolve(true);
        };

        const handleCancel = () => {
            modal.style.display = 'none';
            cleanup();
            resolve(false);
        };

        const cleanup = () => {
            proceedBtn.removeEventListener('click', handleProceed);
            cancelBtn.removeEventListener('click', handleCancel);
        };

        proceedBtn.addEventListener('click', handleProceed);
        cancelBtn.addEventListener('click', handleCancel);
    });
}
</script>

<!-- Deletion confirm modal -->
<div id="confirmModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 400px; text-align: center; padding: 2rem;">
        <div style="width: 60px; height: 60px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
            <i class="fa-solid fa-triangle-exclamation" style="color: #ef4444; font-size: 1.5rem;"></i>
        </div>
        <h2 id="confirmTitle" style="font-size: 1.5rem; font-weight: 700; color: var(--color-stone-900); margin-bottom: 0.5rem;">Are you sure?</h2>
        <p id="confirmMessage" style="color: var(--color-stone-500); margin-bottom: 2rem; line-height: 1.5;">This action cannot be undone. Do you really want to proceed?</p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <button id="confirmCancel" style="padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--color-stone-200); background: white; color: var(--color-stone-600); font-weight: 600; cursor: pointer;">Cancel</button>
            <button id="confirmProceed" style="padding: 0.75rem; border-radius: 0.5rem; border: none; background: #ef4444; color: white; font-weight: 600; cursor: pointer;">Yes, Delete</button>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
