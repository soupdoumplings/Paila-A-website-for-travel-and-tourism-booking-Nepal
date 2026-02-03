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
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);

        if (empty($username) || empty($email)) {
            $error = 'All profile fields are required.';
        } else {
            // Update DB
            try {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                if ($stmt->execute([$username, $email, $user['id']])) {
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    $success = 'Profile updated successfully!';
                    $user = get_user(); // Refresh data
                }
            } catch (Exception $e) {
                $error = 'Failed to update profile. Email or username might already be in use.';
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $current_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        if (empty($current_pass) || empty($new_pass)) {
            $error = 'All password fields are required.';
        } elseif ($new_pass !== $confirm_pass) {
            $error = 'New passwords do not match.';
        } else {
            // Verify password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $hash = $stmt->fetchColumn();

            if (password_verify($current_pass, $hash)) {
                $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$new_hash, $user['id']]);
                $success = 'Password changed successfully!';
            } else {
                $error = 'Current password is incorrect.';
            }
        }
    }
}

$pageTitle = 'Account Settings | पाइला';
include '../includes/header.php';
?>

<div class="dashboard-hero" style="background: linear-gradient(135deg, var(--color-teal-900) 0%, var(--color-teal-800) 100%); padding: 6rem 0 4rem; color: white;">
    <div class="container">
        <h1 style="font-size: 3rem; font-family: var(--font-serif); margin-bottom: 1rem;">Account Settings</h1>
        <p style="opacity: 0.8; font-size: 1.1rem;">Manage your profile and security preferences.</p>
    </div>
</div>

<div style="padding: 4rem 0; background: var(--body-bg);">
    <div class="container" style="max-width: 800px;">
        
        <?php if($error): ?>
            <div style="background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; padding: 1rem; border-radius: 0.75rem; margin-bottom: 2rem;">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div style="background: #dcfce7; border: 1px solid #bbf7d0; color: #166534; padding: 1rem; border-radius: 0.75rem; margin-bottom: 2rem;">
                <i class="fa-solid fa-circle-check"></i> <?php echo e($success); ?>
            </div>
        <?php endif; ?>

        <!-- Profile -->
        <div style="background: var(--card-bg); border: 1px solid var(--border-color); padding: 2.5rem; border-radius: 1.5rem; margin-bottom: 3rem; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
            <h2 style="font-family: var(--font-serif); font-size: 1.5rem; margin-bottom: 2rem;">Profile Information</h2>
            <form method="POST" style="display: grid; gap: 1.5rem;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; opacity: 0.8;">Username</label>
                    <input type="text" name="username" value="<?php echo e($user['username']); ?>" style="width: 100%; padding: 0.8rem 1.2rem; border: 1px solid var(--border-color); border-radius: 0.75rem; background: var(--body-bg); color: inherit;" required>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; opacity: 0.8;">Email Address</label>
                    <input type="email" name="email" value="<?php echo e($user['email']); ?>" style="width: 100%; padding: 0.8rem 1.2rem; border: 1px solid var(--border-color); border-radius: 0.75rem; background: var(--body-bg); color: inherit;" required>
                </div>
                <div style="padding-top: 1rem;">
                    <button type="submit" name="update_profile" class="btn btn-primary" style="padding: 0.8rem 2.5rem; border-radius: 50px;">Save Changes</button>
                </div>
            </form>
        </div>

        <!-- Security -->
        <div style="background: var(--card-bg); border: 1px solid var(--border-color); padding: 2.5rem; border-radius: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
            <h2 style="font-family: var(--font-serif); font-size: 1.5rem; margin-bottom: 2rem;">Security & Password</h2>
            <form method="POST" style="display: grid; gap: 1.5rem;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; opacity: 0.8;">Current Password</label>
                    <input type="password" name="current_password" style="width: 100%; padding: 0.8rem 1.2rem; border: 1px solid var(--border-color); border-radius: 0.75rem; background: var(--body-bg); color: inherit;" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; opacity: 0.8;">New Password</label>
                        <input type="password" name="new_password" style="width: 100%; padding: 0.8rem 1.2rem; border: 1px solid var(--border-color); border-radius: 0.75rem; background: var(--body-bg); color: inherit;" required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; opacity: 0.8;">Confirm New Password</label>
                        <input type="password" name="confirm_password" style="width: 100%; padding: 0.8rem 1.2rem; border: 1px solid var(--border-color); border-radius: 0.75rem; background: var(--body-bg); color: inherit;" required>
                    </div>
                </div>
                <div style="padding-top: 1rem;">
                    <button type="submit" name="change_password" class="btn btn-primary" style="padding: 0.8rem 2.5rem; border-radius: 50px; background: var(--color-stone-900);">Update Password</button>
                </div>
            </form>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>
