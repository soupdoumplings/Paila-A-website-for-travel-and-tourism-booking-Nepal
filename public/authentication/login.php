<?php
require_once __DIR__ . '/../../helpers/functions.php';
require_once __DIR__ . '/../../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (is_logged_in()) {
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        redirect(url('admin/index.php'));
    }
    redirect(url('index.php'));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
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
        <div class="auth-wrapper">
            <!-- Left Side - Benefits -->
            <div class="auth-benefits">
                <div class="auth-benefits-header">
                    <div class="auth-benefits-badge">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        Welcome Back
                    </div>
                    <h1>Continue Your <span class="highlight">Journey</span></h1>
                    <p class="auth-benefits-description">Sign in to access your personalized travel dashboard and continue exploring extraordinary destinations around the world.</p>
                </div>
                
                <div class="auth-benefits-list">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="benefit-text">Access your saved tours</span>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="benefit-text">Track your bookings</span>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="benefit-text">Get personalized recommendations</span>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="benefit-text">Enjoy exclusive member benefits</span>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Form -->
            <div class="auth-form-section">
                <div class="auth-form-container">
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
                                <input type="password" id="password" name="password" class="auth-input" placeholder="••••••••" data-rules="required">
                            </div>
                        </div>
                        
                        <button type="submit" class="auth-submit">
                            Sign In
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </form>
                    
                    <div class="social-divider">or sign in with</div>
                    
                    <div class="social-buttons">
                        <button class="social-btn" type="button">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                            </svg>
                            Google
                        </button>
                        
                        <button class="social-btn" type="button">
                            <svg viewBox="0 0 384 512" fill="currentColor" style="width: 18px;">
                                <path d="M318.7 268.7c-.2-36.7 16.4-64.4 50-84.8-18.8-26.9-47.2-41.7-84.7-44.6-35.5-2.8-74.3 20.7-88.5 20.7-15 0-49.4-19.7-76.4-19.7C63.3 141.2 4 184.8 4 273.5q0 39.3 14.4 81.2c12.8 36.7 59 126.7 107.2 125.2 25.2-.6 43-17.9 75.8-17.9 31.8 0 48.3 17.9 76.4 17.9 48.6-.7 90.4-82.5 102.6-119.3-65.2-30.7-61.7-90-61.7-91.9zm-56.6-164.2c27.3-32.4 24.8-61.9 24-72.5-24.1 1.4-52 16.4-67.9 34.9-17.5 19.8-27.8 44.3-25.6 71.9 26.1 2 49.9-11.4 69.5-34.3z"/>
                            </svg>
                            Apple
                        </button>

                        <button class="social-btn" type="button">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M11.4 24l-11.4-2V2l11.4-2v24zm12-4.3l-9 1.5v-18.4l9 1.5v15.4z" fill="#0078D4"/>
                            </svg>
                            Outlook
                        </button>
                    </div>
                    
                    <div class="auth-footer">
                        Don't have an account? <a href="register.php">Create Account</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
