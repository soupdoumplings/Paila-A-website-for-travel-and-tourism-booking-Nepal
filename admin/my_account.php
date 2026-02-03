<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../helpers/functions.php';
require_once '../config/db.php';

// Check permissions
require_login(); 

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
            // Update profile
            try {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                if ($stmt->execute([$username, $email, $user['id']])) {
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    $success = 'Profile updated successfully!';
                    $user = get_user(); 
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

$pageTitle = "My Account | Admin";
$base = '../';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body style="background: var(--color-stone-100);">

    <div class="admin-hero">
    <?php include '../includes/header.php'; ?>
    <section style="padding: 4rem 0;">
        <div class="container">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <a href="index.php" style="color: var(--color-stone-600); text-decoration: none; font-weight: 600;"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
                <span style="opacity: 0.3;">/</span>
                <span style="opacity: 0.7; text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.8rem; color: var(--color-stone-500);">System Preferences</span>
            </div>
            <h1 style="font-size: 3.5rem; font-family: var(--font-serif); color: var(--color-stone-900); margin: 0;">My Account</h1>
        </div>
    </section>
    </div>

    <div class="container" style="margin-top: -2rem; position: relative; z-index: 10; padding-bottom: 5rem;">
        <div style="max-width: 800px;">
            
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

            <!-- Profile Section -->
            <div style="background: white; border: 1px solid var(--color-stone-200); padding: 2.5rem; border-radius: 1.5rem; margin-bottom: 3rem; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <h2 style="font-family: var(--font-serif); font-size: 1.5rem; margin-bottom: 2rem; color: var(--color-stone-900);">Profile Information</h2>
                <form method="POST" style="display: grid; gap: 1.5rem;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; opacity: 0.8; color: var(--color-stone-600);">Username</label>
                        <input type="text" name="username" value="<?php echo e($user['username']); ?>" style="width: 100%; padding: 0.8rem 1.2rem; border: 1px solid var(--color-stone-200); border-radius: 0.75rem; background: var(--color-stone-50); color: inherit;" required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; opacity: 0.8; color: var(--color-stone-600);">Email Address</label>
                        <input type="email" name="email" value="<?php echo e($user['email']); ?>" style="width: 100%; padding: 0.8rem 1.2rem; border: 1px solid var(--color-stone-200); border-radius: 0.75rem; background: var(--color-stone-50); color: inherit;" required>
                    </div>
                    <div style="padding-top: 1rem;">
                        <button type="submit" name="update_profile" class="admin-btn btn-dark" style="padding: 0.8rem 2.5rem; border-radius: 50px;">Save Changes</button>
                    </div>
                </form>
            </div>

            <!-- Security Section -->
            <div style="background: white; border: 1px solid var(--color-stone-200); padding: 2.5rem; border-radius: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <h2 style="font-family: var(--font-serif); font-size: 1.5rem; margin-bottom: 2rem; color: var(--color-stone-900);">Security & Password</h2>
                <form method="POST" style="display: grid; gap: 1.5rem;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; opacity: 0.8; color: var(--color-stone-600);">Current Password</label>
                        <input type="password" name="current_password" style="width: 100%; padding: 0.8rem 1.2rem; border: 1px solid var(--color-stone-200); border-radius: 0.75rem; background: var(--color-stone-50); color: inherit;" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div>
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; opacity: 0.8; color: var(--color-stone-600);">New Password</label>
                            <input type="password" name="new_password" style="width: 100%; padding: 0.8rem 1.2rem; border: 1px solid var(--color-stone-200); border-radius: 0.75rem; background: var(--color-stone-50); color: inherit;" required>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; opacity: 0.8; color: var(--color-stone-600);">Confirm New Password</label>
                            <input type="password" name="confirm_password" style="width: 100%; padding: 0.8rem 1.2rem; border: 1px solid var(--color-stone-200); border-radius: 0.75rem; background: var(--color-stone-50); color: inherit;" required>
                        </div>
                    </div>
                    <div style="padding-top: 1rem;">
                        <button type="submit" name="change_password" class="admin-btn btn-dark" style="padding: 0.8rem 2.5rem; border-radius: 50px;">Update Password</button>
                    </div>
                </form>
            </div>

        </div>
    </div>

</body>
</html>
