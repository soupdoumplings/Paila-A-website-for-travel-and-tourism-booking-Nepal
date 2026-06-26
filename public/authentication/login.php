<?php
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../helpers/functions.php';
require_once __DIR__ . '/../../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    secure_session_start();
}

if (is_logged_in()) {
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        redirect(url('admin/index.php'));
    }
    redirect(url('index.php'));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            if (isset($user['role_id'])) {
                // Load user role
                $stmt = $pdo->prepare("SELECT name FROM roles WHERE id = ?");
                $stmt->execute([$user['role_id']]);
                $roleName = $stmt->fetchColumn();
                
                if ($roleName) {
                    $_SESSION['role_name'] = $roleName;
                    if ($roleName === 'admin' || $roleName === 'super_admin') {
                        $_SESSION['admin_logged_in'] = true;
                    }
                }
            }
            // Redirect by role
            if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
                redirect(url('admin/index.php'));
            } else {
                redirect(url('index.php'));
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Paila Tours</title>
    <link rel="stylesheet" href="<?php echo url('assets/css/auth.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/validation.css'); ?>">
    <script src="<?php echo url('assets/js/validation.js'); ?>" defer></script>
</head>
<body>
    <div class="auth-container">
        <a href="<?php echo url('index.php'); ?>" class="auth-back-link">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            पाइला
        </a>
        <div class="auth-wrapper auth-login">
            <!-- Left Side - Benefits -->
            <div class="auth-benefits">
                <div class="auth-benefits-header">
                    <div class="auth-benefits-badge">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        Member Atelier
                    </div>
                    <h1>Return to Your <span class="highlight">Paila</span></h1>
                    <p class="auth-benefits-description">Step back into your Nepal travel desk: bookings, guide updates, and curated journeys arranged with quiet care.</p>
                </div>
                
                <div class="auth-benefits-list">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="benefit-text">Review your curated journeys</span>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="benefit-text">Track booking requests</span>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="benefit-text">Receive assigned guide details</span>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="benefit-text">Continue private access requests</span>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Form -->
            <div class="auth-form-section">
                <div class="auth-form-container">
                    <div class="auth-form-header">
                        <span class="auth-kicker">Secure sign in</span>
                        <h2>Welcome back</h2>
                        <p>Use the email connected to your Paila booking or account.</p>
                    </div>

                    <?php 
                    $prefill_email = isset($_GET['email']) ? e($_GET['email']) : ''; 
                    $is_booked = isset($_GET['booked']) && $_GET['booked'] == '1';
                    $temp_pass = isset($_SESSION['new_account_pass']) ? $_SESSION['new_account_pass'] : null;
                    ?>

                    <?php if($is_booked): ?>
                        <div class="auth-alert" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 1.5rem; border-radius: 0.75rem; margin-bottom: 2rem; text-align: left;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; font-weight: 700;">
                                <i class="fa-solid fa-circle-check"></i> Booking Received!
                            </div>
                            <p style="font-size: 0.875rem; opacity: 0.9; line-height: 1.5;">
                                We've created an account for you to track your journeys. Log in with the credentials below to continue.
                            </p>
                            <?php if($temp_pass): ?>
                                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed #86efac; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 0.8rem; opacity: 0.8;">Temporary Password:</span>
                                    <span style="font-family: monospace; font-weight: 700; font-size: 1rem; color: #15803d; background: white; padding: 0.2rem 0.6rem; border-radius: 4px; border: 1px solid #dcfce7;"><?php echo e($temp_pass); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if($error): ?>
                        <div class="auth-alert auth-alert-error">
                            <?php echo e($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="login.php" class="auth-form" data-validate>
                        <?php echo csrf_field(); ?>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <input type="email" id="email" name="email" class="auth-input" placeholder="your@email.com" data-rules="required|email" value="<?php echo $prefill_email ?: (isset($_POST['email']) ? e($_POST['email']) : ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <input type="password" id="password" name="password" class="auth-input" placeholder="********" data-rules="required">
                            </div>
                        </div>
                        
                        <button type="submit" class="auth-submit">
                            Sign In
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </form>
                    
                    <div class="auth-footer">
                        Don't have an account? <a href="register.php">Create Account</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
