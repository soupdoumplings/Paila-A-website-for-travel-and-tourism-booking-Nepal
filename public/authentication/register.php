<?php
require_once __DIR__ . '/../../helpers/functions.php';
require_once __DIR__ . '/../../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (is_logged_in()) {
    redirect(url('index.php'));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($fullname) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check email duplicate
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already exists.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // Save as user role
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, 3)");
            if ($stmt->execute([$fullname, $email, $hashed_password])) {
                $new_user_id = $pdo->lastInsertId();
                
                // Assign prior bookings
                $linkBookings = $pdo->prepare("UPDATE bookings SET user_id = ? WHERE contact_email = ? AND user_id IS NULL");
                $linkBookings->execute([$new_user_id, $email]);
                
                // Assign prior requests
                $linkRequests = $pdo->prepare("UPDATE private_requests SET user_id = ? WHERE email = ? AND user_id IS NULL");
                $linkRequests->execute([$new_user_id, $email]);

                $success = 'Registration successful! You can now <a href="' . url('public/authentication/login.php') . '">login</a>.';
            } else {
                $error = 'Something went wrong. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Paila Tours</title>
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
                        Join Us
                    </div>
                    <h1>Begin Your <span class="highlight">Journey</span></h1>
                    <p class="auth-benefits-description">Join thousands of adventurers who have discovered extraordinary experiences with us. Create your account and unlock a world of possibilities.</p>
                </div>
                
                <div class="auth-benefits-list">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="benefit-text">Exclusive access to private tours</span>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="benefit-text">Personalized travel recommendations</span>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="benefit-text">Early booking privileges</span>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="benefit-text">Member-only discounts</span>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Form -->
            <div class="auth-form-section">
                <div class="auth-form-container">
                    <div class="auth-form-header">
                        <h2>Create Account</h2>
                        <p>Fill in your details to get started</p>
                    </div>
                    
                    <?php if($error): ?>
                        <div class="auth-alert auth-alert-error">
                            <?php echo e($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($success): ?>
                        <div class="auth-alert auth-alert-success">
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="register.php" class="auth-form" data-validate>
                        <div class="form-group">
                            <label for="fullname">Full Name</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <input type="text" id="fullname" name="fullname" class="auth-input" placeholder="John Doe" data-rules="required|min:3" value="<?php echo isset($_POST['fullname']) ? e($_POST['fullname']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <input type="email" id="email" name="email" class="auth-input" placeholder="your@email.com" data-rules="required|email" value="<?php echo isset($_POST['email']) ? e($_POST['email']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <input type="password" id="password" name="password" class="auth-input" placeholder="••••••••" data-rules="required|min:8">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <input type="password" id="confirm_password" name="confirm_password" class="auth-input" placeholder="••••••••" data-rules="required" data-match="password">
                            </div>
                        </div>
                        
                        <div class="terms-group">
                            <input type="checkbox" id="terms" data-rules="required">
                            <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                        </div>
                        
                        <button type="submit" class="auth-submit">
                            Create Account
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </form>
                    
                    <div class="social-divider">or sign up with</div>
                    
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
                        Already have an account? <a href="login.php">Sign In</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
