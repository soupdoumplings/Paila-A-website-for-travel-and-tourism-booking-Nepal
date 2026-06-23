<?php
require_once __DIR__ . '/../helpers/security.php';
secure_session_start();
require_once '../config/db.php';
require_once '../helpers/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare('
            SELECT u.id, u.username, u.password, r.name AS role_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.username = :username
        ');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && in_array($user['role_name'], ['admin', 'super_admin'], true) && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role_name'] = $user['role_name'];
            redirect('index.php');
        }

        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Paila</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/admin.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/validation.css'); ?>">
    <script src="<?php echo url('assets/js/validation.js'); ?>" defer></script>
</head>
<body class="admin-login-page">
    <main class="admin-login-shell">
        <section class="admin-login-card">
            <a href="<?php echo url('index.php'); ?>" class="admin-login-brand">PAILA</a>
            <p class="text-xs-caps" style="color: var(--color-amber-400); margin-bottom: 0.75rem;">Secure Admin Portal</p>
            <h1>Welcome Back</h1>
            <p class="admin-login-copy">Manage journeys, bookings, guides, and private access requests with care.</p>

            <?php if ($error): ?>
                <div class="admin-login-error">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="admin-login-form" data-validate>
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input id="username" type="text" name="username" class="form-input" data-rules="required" autocomplete="username">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input id="password" type="password" name="password" class="form-input" data-rules="required" autocomplete="current-password">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    Login <i class="fa-solid fa-arrow-right-long"></i>
                </button>
            </form>
        </section>
    </main>
</body>
</html>
