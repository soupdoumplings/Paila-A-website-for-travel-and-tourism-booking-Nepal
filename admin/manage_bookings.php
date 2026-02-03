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

// Process form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $booking_id = intval($_POST['booking_id']);
        $action = $_POST['action'];
        
        if ($action === 'approve') {
            $guide_id = intval($_POST['tour_guide_id']);
            if ($guide_id > 0) {
                $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed', tour_guide_id = ? WHERE id = ?");
                if ($stmt->execute([$guide_id, $booking_id])) {
                    $success = "Booking #$booking_id approved and guide assigned.";
                } else {
                    $error = "Failed to approve booking.";
                }
            } else {
                $error = "Please select a tour guide.";
            }
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
            if ($stmt->execute([$booking_id])) {
                $success = "Booking #$booking_id rejected.";
            } else {
                $error = "Failed to reject booking.";
            }
        }
    }
}

// Retrieve booking data
$stmt = $pdo->query("
    SELECT b.*, t.title as tour_title, u.username as guide_name
    FROM bookings b
    LEFT JOIN tours t ON b.tour_id = t.id
    LEFT JOIN users u ON b.tour_guide_id = u.id
    ORDER BY b.created_at DESC
");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get guide list
$stmt = $pdo->query("SELECT id, username FROM users WHERE role_id = 4 ORDER BY username ASC");
$guides = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Manage Bookings";
$base = '../';
?>
<div class="admin-hero">
<?php include '../includes/header.php'; ?>
<section style="padding: 6rem 0 5rem;">
    <div class="container">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
            <a href="index.php" style="color: var(--color-stone-600); text-decoration: none; font-weight: 600;"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
            <span style="opacity: 0.3;">/</span>
            <span style="opacity: 0.7; text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.8rem; color: var(--color-stone-500);">Inventory Management</span>
        </div>
        <h1 style="font-size: 3.5rem; font-family: var(--font-serif); color: var(--color-stone-900); margin: 0;">Manage Bookings</h1>
    </div>
</section>
</div>

<div class="container" style="margin-top: -2rem; position: relative; z-index: 10; padding-bottom: 5rem;">
    <!-- Content wrapper -->
    <div style="background: white; border: 1px solid var(--color-stone-200); border-radius: 1rem; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        
        <!-- Page header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 style="font-size: 1.5rem; color: var(--color-stone-900);">Booking Records</h2>
            <div style="color: var(--color-stone-500); font-size: 0.9rem;">Total: <?php echo count($bookings); ?> bookings</div>
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

        <!-- Data table -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--color-stone-100);">
                        <th style="padding: 1rem; color: var(--color-stone-400); font-weight: 500;">Details</th>
                        <th style="padding: 1rem; color: var(--color-stone-400); font-weight: 500;">Customer</th>
                        <th style="padding: 1rem; color: var(--color-stone-400); font-weight: 500;">Tour</th>
                        <th style="padding: 1rem; color: var(--color-stone-400); font-weight: 500;">Assign Guide</th>
                        <th style="padding: 1rem; color: var(--color-stone-400); font-weight: 500;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($bookings)): ?>
                        <tr><td colspan="5" style="padding: 1rem; text-align: center; color: var(--color-stone-500);">No bookings found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $b): ?>
                        <tr style="border-bottom: 1px solid var(--color-stone-100); background: <?php echo $b['status'] == 'pending' ? 'var(--color-stone-50)' : 'transparent'; ?>">
                            <td style="padding: 1rem;">
                                <div style="color: var(--color-stone-900); font-weight: 700;">#<?php echo $b['id']; ?></div>
                                <div style="color: var(--color-stone-500); font-size: 0.8rem;"><?php echo date('M d, Y', strtotime($b['created_at'])); ?></div>
                                <div style="margin-top: 0.25rem;">
                                    <?php if($b['status'] == 'pending'): ?>
                                        <span style="background: #f59e0b; color: #78350f; padding: 0.1rem 0.4rem; border-radius: 0.25rem; font-size: 0.7rem; font-weight: 600;">PENDING</span>
                                    <?php elseif($b['status'] == 'confirmed'): ?>
                                        <span style="background: #10b981; color: #064e3b; padding: 0.1rem 0.4rem; border-radius: 0.25rem; font-size: 0.7rem; font-weight: 600;">CONFIRMED</span>
                                    <?php else: ?>
                                        <span style="background: #ef4444; color: #7f1d1d; padding: 0.1rem 0.4rem; border-radius: 0.25rem; font-size: 0.7rem; font-weight: 600;">CANCELLED</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="padding: 1rem; color: var(--color-stone-600);">
                                <div style="color: var(--color-stone-900); font-weight: 600;"><?php echo e($b['customer_name']); ?></div>
                                <div style="font-size: 0.8rem; opacity: 0.8;"><?php echo e($b['contact_email']); ?></div>
                                <div style="font-size: 0.8rem; margin-top: 0.25rem;">Travelers: <?php echo $b['travelers']; ?></div>
                            </td>
                            <td style="padding: 1rem; color: var(--color-stone-600);">
                                <div style="color: var(--color-stone-900); font-weight: 500;"><?php echo e($b['tour_title'] ?? '[Deleted Tour]'); ?></div>
                                <div style="font-size: 0.8rem; opacity: 0.8;"><?php echo e($b['travel_date']); ?></div>
                            </td>
                            <td style="padding: 1rem;">
                                <?php if($b['status'] == 'pending'): ?>
                                    <form method="POST" id="form-<?php echo $b['id']; ?>">
                                        <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                        <input type="hidden" name="action" id="action-<?php echo $b['id']; ?>" value="">
                                        <select name="tour_guide_id" required style="background: white; color: var(--color-stone-900); border: 1px solid var(--color-stone-200); padding: 0.4rem; border-radius: 0.25rem; width: 100%;">
                                            <option value="">Select Guide...</option>
                                            <?php foreach($guides as $g): ?>
                                                <option value="<?php echo $g['id']; ?>"><?php echo e($g['username']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                <?php elseif($b['status'] == 'confirmed'): ?>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--color-emerald-400);">
                                        <i class="fa-solid fa-user-check"></i>
                                        <?php echo e($b['guide_name'] ?: 'Unknown'); ?>
                                    </div>
                                <?php else: ?>
                                    <span style="color: var(--color-stone-600);">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 1rem;">
                                <?php if($b['status'] == 'pending'): ?>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button onclick="submitBooking(<?php echo $b['id']; ?>, 'approve')" style="background: #10b981; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.5rem; cursor: pointer; font-size: 0.85rem; font-weight: 700; transition: background 0.2s;">
                                            Approve
                                        </button>
                                        <button onclick="submitBooking(<?php echo $b['id']; ?>, 'reject')" style="background: #ef4444; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.5rem; cursor: pointer; font-size: 0.85rem; font-weight: 700; transition: background 0.2s;">
                                            Reject
                                        </button>
                                        <a href="booking_detail.php?id=<?php echo $b['id']; ?>" style="background: #f5f5f4; color: #44403c; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 0.5rem; text-decoration: none; border: 1px solid #d6d3d1; transition: all 0.2s;" title="View Details">
                                            <i class="fa-regular fa-eye"></i>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <a href="booking_detail.php?id=<?php echo $b['id']; ?>" style="background: var(--color-stone-100); color: var(--color-stone-700); padding: 0.4rem 0.8rem; border-radius: 0.25rem; font-size: 0.8rem; text-decoration: none; border: 1px solid var(--color-stone-300);">
                                        View Details
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
async function submitBooking(id, action) {
    if (action === 'reject') {
        const confirmed = await showCustomConfirm(
            'Reject Booking', 
            `Are you sure you want to reject booking #${id}? This action will cancel the reservation.`
        );
        if (!confirmed) return;
    }
    
    document.getElementById('action-' + id).value = action;
    const form = document.getElementById('form-' + id);
    if (action === 'approve') {
        const select = form.querySelector('select');
        if (select.value === "") {
            alert('Please select a tour guide first.');
            return;
        }
    }
    form.submit();
}

function showCustomConfirm(title, message, confirmText = 'Yes, Reject') {
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

<!-- Custom Confirmation Modal -->
<div id="confirmModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 400px; text-align: center; padding: 2rem;">
        <div style="width: 60px; height: 60px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
            <i class="fa-solid fa-triangle-exclamation" style="color: #ef4444; font-size: 1.5rem;"></i>
        </div>
        <h2 id="confirmTitle" style="font-size: 1.5rem; font-weight: 700; color: var(--color-stone-900); margin-bottom: 0.5rem;">Are you sure?</h2>
        <p id="confirmMessage" style="color: var(--color-stone-500); margin-bottom: 2rem; line-height: 1.5;">This action cannot be undone. Do you really want to proceed?</p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <button id="confirmCancel" style="padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--color-stone-200); background: white; color: var(--color-stone-600); font-weight: 600; cursor: pointer;">Cancel</button>
            <button id="confirmProceed" style="padding: 0.75rem; border-radius: 0.5rem; border: none; background: #ef4444; color: white; font-weight: 600; cursor: pointer;">Yes, Reject</button>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
