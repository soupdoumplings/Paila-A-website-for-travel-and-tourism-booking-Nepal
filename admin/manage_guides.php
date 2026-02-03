<?php
require_once '../config/db.php';
require_once '../helpers/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verify admin access
require_login();
if (!is_admin()) {
    die("Access Denied. You must be an Admin to view this page.");
}

$success = '';
$error = '';

// Process guide creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_guide'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Check existing user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = "Username or Email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, 4)"); // Guide role
            if ($stmt->execute([$username, $email, $hashed])) {
                $success = "New Tour Guide created successfully.";
            } else {
                $error = "Failed to create tour guide.";
            }
        }
    }
}

// Process guide deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_guide'])) {
    $user_id = intval($_POST['user_id']);
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role_id = 4");
    if ($stmt->execute([$user_id])) {
        $success = "Tour Guide deleted successfully.";
    } else {
        $error = "Failed to delete tour guide.";
    }
}

// Fetch all guides
$stmt = $pdo->query("
    SELECT u.id, u.username, u.email, u.created_at
    FROM users u
    WHERE u.role_id = 4
    ORDER BY u.created_at DESC
");
$guides = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Manage Tour Guides";
$base = '../';
?>
<div class="admin-hero">
<?php include '../includes/header.php'; ?>
<section style="padding: 6rem 0 5rem;">
    <div class="container">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
            <a href="index.php" style="color: var(--color-stone-600); text-decoration: none; font-weight: 600;"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
            <span style="opacity: 0.3;">/</span>
            <span style="opacity: 0.7; text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.8rem; color: var(--color-stone-500);">Data Management</span>
        </div>
        <h1 style="font-size: 3.5rem; font-family: var(--font-serif); color: var(--color-stone-900); margin: 0;">Manage Tour Guides</h1>
    </div>
</section>
</div>

<div class="container" style="margin-top: -2rem; position: relative; z-index: 10; padding-bottom: 5rem;">
    <!-- Main content panel -->
    <div style="background: white; border: 1px solid var(--color-stone-200); border-radius: 1rem; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        
        <!-- Section header row -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 style="font-size: 1.5rem; color: var(--color-stone-900);">Guide Records</h2>
            <div style="color: var(--color-stone-500); font-size: 0.9rem;">Total: <?php echo count($guides); ?> guides</div>
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

        <!-- Guide registration form -->
        <div style="background: var(--color-stone-800); padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem;">
            <h3 style="color: white; margin-bottom: 1rem; font-size: 1.25rem;">Register New Tour Guide</h3>
            <form method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                <input type="hidden" name="create_guide" value="1">
                <div>
                    <label style="color: var(--color-stone-400); font-size: 0.8rem; display: block; margin-bottom: 0.5rem;">Username</label>
                    <input type="text" name="username" required style="width: 100%; padding: 0.5rem; background: var(--color-stone-900); border: 1px solid var(--color-stone-700); color: white; border-radius: 0.25rem;">
                </div>
                <div>
                    <label style="color: var(--color-stone-400); font-size: 0.8rem; display: block; margin-bottom: 0.5rem;">Email</label>
                    <input type="email" name="email" required style="width: 100%; padding: 0.5rem; background: var(--color-stone-900); border: 1px solid var(--color-stone-700); color: white; border-radius: 0.25rem;">
                </div>
                <div>
                    <label style="color: var(--color-stone-400); font-size: 0.8rem; display: block; margin-bottom: 0.5rem;">Temp Password</label>
                    <input type="password" name="password" required style="width: 100%; padding: 0.5rem; background: var(--color-stone-900); border: 1px solid var(--color-stone-700); color: white; border-radius: 0.25rem;">
                </div>
                <button type="submit" style="background: var(--color-amber-600); color: white; padding: 0.6rem 1rem; border: none; border-radius: 0.25rem; cursor: pointer; font-weight: 600;">Register Guide</button>
            </form>
        </div>

        <!-- Guide records table -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--color-stone-100);">
                        <th style="padding: 1rem; color: var(--color-stone-500); font-weight: 600;">Guide Name</th>
                        <th style="padding: 1rem; color: var(--color-stone-500); font-weight: 600;">Email</th>
                        <th style="padding: 1rem; color: var(--color-stone-500); font-weight: 600;">Joined Date</th>
                        <th style="padding: 1rem; color: var(--color-stone-500); font-weight: 600;">Status</th>
                        <th style="padding: 1rem; color: var(--color-stone-500); font-weight: 600;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($guides)): ?>
                        <tr><td colspan="4" style="padding: 1rem; text-align: center; color: var(--color-stone-500);">No tour guides found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($guides as $guide): ?>
                        <tr style="border-bottom: 1px solid var(--color-stone-100);">
                            <td style="padding: 1rem;">
                                <div style="color: var(--color-stone-900); font-weight: 600; font-size: 1rem;"><?php echo e($guide['username']); ?></div>
                            </td>
                            <td style="padding: 1rem; color: var(--color-stone-600);">
                                <?php echo e($guide['email']); ?>
                            </td>
                            <td style="padding: 1rem; color: var(--color-stone-500);">
                                <?php echo date('M d, Y', strtotime($guide['created_at'])); ?>
                            </td>
                            <td style="padding: 1rem;">
                                <span style="background: #ecfdf5; color: #065f46; padding: 0.25rem 0.6rem; border-radius: 99px; font-size: 0.75rem; font-weight: 600; border: 1px solid #6ee7b7;">Active</span>
                            </td>
                            <td style="padding: 1rem;">
                                <form method="POST" id="delete-guide-form-<?php echo $guide['id']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $guide['id']; ?>">
                                    <input type="hidden" name="delete_guide" value="1">
                                    <button type="button" onclick="handleDeleteGuide(<?php echo $guide['id']; ?>, '<?php echo addslashes($guide['username']); ?>')" style="background: var(--color-red-600); color: white; border: none; padding: 0.4rem 0.75rem; border-radius: 0.25rem; font-size: 0.75rem; cursor: pointer; transition: background 0.3s;"
                                        onmouseover="this.style.background='var(--color-red-500)'"
                                        onmouseout="this.style.background='var(--color-red-600)'">
                                        Delete
                                    </button>
                                </form>
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
async function handleDeleteGuide(id, name) {
    const confirmed = await showCustomConfirm(
        'Delete Tour Guide', 
        `Are you sure you want to delete guide "${name}"? This action cannot be undone.`
    );
    if (confirmed) {
        document.getElementById('delete-guide-form-' + id).submit();
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
