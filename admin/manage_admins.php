<?php
require_once '../config/db.php';
require_once '../helpers/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check security
if (!is_logged_in() || !is_super_admin()) {
    die("Access Denied. You must be a Super Admin to view this page.");
}

$success = '';
$error = '';

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_admin'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        if (empty($username) || empty($email) || empty($password)) {
            $error = "All fields are required.";
        } else {
            // Check existence
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error = "Username or Email already exists.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, 2)"); // Admin role
                if ($stmt->execute([$username, $email, $hashed])) {
                    $success = "New Admin created successfully.";
                } else {
                    $error = "Failed to create admin.";
                }
            }
        }
    }
    elseif (isset($_POST['update_role'])) {
        $user_id = intval($_POST['user_id']);
        $new_role_id = intval($_POST['role_id']);
        
        // Update user role
        if (in_array($new_role_id, [1, 2, 3])) {
            if ($user_id == $_SESSION['user_id']) {
                 $error = "You cannot change your own role here.";
            } else {
                $stmt = $pdo->prepare("UPDATE users SET role_id = ? WHERE id = ?");
                if ($stmt->execute([$new_role_id, $user_id])) {
                    $success = "User role updated successfully.";
                } else {
                    $error = "Failed to update role.";
                }
            }
        }
    }
    elseif (isset($_POST['delete_user'])) {
        $user_id = intval($_POST['user_id']);
        if ($user_id == $_SESSION['user_id']) {
            $error = "You cannot delete yourself.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$user_id])) {
                $success = "User deleted successfully.";
            } else {
                $error = "Failed to delete user.";
            }
        }
    }
}

// Get all users
$stmt = $pdo->query("
    SELECT u.id, u.username, u.email, u.created_at, r.name as role_name, r.id as role_id
    FROM users u
    JOIN roles r ON u.role_id = r.id
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$roles = [
    1 => 'Super Admin',
    2 => 'Admin',
    3 => 'User',
    4 => 'Tour Guide'
];

$pageTitle = "Manage Admins";
$base = '../';
?>
<div class="admin-hero">
<?php include '../includes/header.php'; ?>
<section style="padding: 6rem 0 5rem;">
    <div class="container">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
            <a href="index.php" style="color: var(--color-stone-600); text-decoration: none; font-weight: 600;"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
            <span style="opacity: 0.3;">/</span>
            <span style="opacity: 0.7; text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.8rem; color: var(--color-stone-500);">Access Control</span>
        </div>
        <h1 style="font-size: 3.5rem; font-family: var(--font-serif); color: var(--color-stone-900); margin: 0;">Manage System Users</h1>
    </div>
</section>
</div>

<div class="container" style="margin-top: -2rem; position: relative; z-index: 10; padding-bottom: 5rem;">
    <!-- Content wrapper -->
    <div style="background: white; border: 1px solid var(--color-stone-200); border-radius: 1rem; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        
        <!-- Create admin form -->
        <div style="background: var(--color-stone-50); padding: 1.5rem; border: 1px solid var(--color-stone-200); border-radius: 0.75rem; margin-bottom: 2rem;">
            <h3 style="color: var(--color-stone-900); margin-bottom: 1rem; font-size: 1.25rem;">Create New Admin</h3>
            <form method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                <input type="hidden" name="create_admin" value="1">
                <div>
                    <label style="color: var(--color-stone-600); font-size: 0.8rem; display: block; margin-bottom: 0.5rem;">Username</label>
                    <input type="text" name="username" required style="width: 100%; padding: 0.75rem; background: white; border: 1px solid var(--color-stone-300); color: var(--color-stone-900); border-radius: 0.5rem;">
                </div>
                <div>
                    <label style="color: var(--color-stone-600); font-size: 0.8rem; display: block; margin-bottom: 0.5rem;">Email</label>
                    <input type="email" name="email" required style="width: 100%; padding: 0.75rem; background: white; border: 1px solid var(--color-stone-300); color: var(--color-stone-900); border-radius: 0.5rem;">
                </div>
                <div>
                    <label style="color: var(--color-stone-600); font-size: 0.8rem; display: block; margin-bottom: 0.5rem;">Password</label>
                    <input type="password" name="password" required style="width: 100%; padding: 0.75rem; background: white; border: 1px solid var(--color-stone-300); color: var(--color-stone-900); border-radius: 0.5rem;">
                </div>
                <button type="submit" class="admin-btn btn-green">Create Admin</button>
            </form>
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

        <!-- User list -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--color-stone-800);">
                        <th style="padding: 1rem; color: var(--color-stone-400); font-weight: 500;">User</th>
                        <th style="padding: 1rem; color: var(--color-stone-400); font-weight: 500;">Email</th>
                        <th style="padding: 1rem; color: var(--color-stone-400); font-weight: 500;">Current Role</th>
                        <th style="padding: 1rem; color: var(--color-stone-400); font-weight: 500;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr style="border-bottom: 1px solid var(--color-stone-800);">
                        <td style="padding: 1rem;">
                            <div style="color: white; font-weight: 500; font-size: 1rem;"><?php echo e($user['username']); ?></div>
                            <div style="color: var(--color-stone-500); font-size: 0.75rem;">ID: <?php echo $user['id']; ?></div>
                        </td>
                        <td style="padding: 1rem; color: var(--color-stone-600);">
                            <?php echo e($user['email']); ?>
                        </td>
                        <td style="padding: 1rem;">
                            <span style="color: var(--color-amber-500); font-weight: 500; text-transform: capitalize;">
                                <?php echo ucfirst(str_replace('_', ' ', $user['role_name'])); ?>
                            </span>
                        </td>
                        <td style="padding: 1rem;">
                            <form method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="update_role" value="1">
                                <select name="role_id" style="background: var(--color-stone-900); border: 1px solid var(--color-stone-700); color: white; border-radius: 0.25rem; padding: 0.4rem 0.5rem; outline: none; font-size: 0.875rem;">
                                    <?php foreach ($roles as $rnId => $rnName): ?>
                                        <option value="<?php echo $rnId; ?>" <?php echo $user['role_id'] == $rnId ? 'selected' : ''; ?>>
                                            <?php echo $rnName; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" style="background: var(--color-emerald-700); color: white; border: none; padding: 0.4rem 0.75rem; border-radius: 0.25rem; font-size: 0.75rem; cursor: pointer; transition: background 0.3s;"
                                    onmouseover="this.style.background='#059669'"
                                    onmouseout="this.style.background='var(--color-emerald-700)'">
                                    Update
                                </button>
                                <button type="button" onclick="handleDeleteUser(<?php echo $user['id']; ?>, '<?php echo addslashes($user['username']); ?>')" style="background: var(--color-red-600); color: white; border: none; padding: 0.4rem 0.75rem; border-radius: 0.25rem; font-size: 0.75rem; cursor: pointer; transition: background 0.3s;"
                                    onmouseover="this.style.background='var(--color-red-500)'"
                                    onmouseout="this.style.background='var(--color-red-600)'">
                                    Delete
                                </button>
                                <input type="hidden" name="delete_user" id="delete-trigger-<?php echo $user['id']; ?>" value="">
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
async function handleDeleteUser(id, name) {
    if (id === <?php echo $_SESSION['user_id']; ?>) {
        alert('You cannot delete yourself.');
        return;
    }
    
    const confirmed = await showCustomConfirm(
        'Delete System User', 
        `Are you sure you want to delete "${name}"? This action cannot be undone.`
    );
    if (confirmed) {
        const trigger = document.getElementById('delete-trigger-' + id);
        trigger.name = "delete_user";
        trigger.value = "1";
        trigger.form.submit();
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
            <button id="confirmProceed" style="padding: 0.75rem; border-radius: 0.5rem; border: none; background: #ef4444; color: white; font-weight: 600; cursor: pointer;">Yes, Delete</button>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
