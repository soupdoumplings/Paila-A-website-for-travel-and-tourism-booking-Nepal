<?php
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../helpers/functions.php';
require_once __DIR__ . '/../../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    secure_session_start();
}

if (is_logged_in()) {
    redirect(url('index.php'));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($fullname) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
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
                        <?php echo csrf_field(); ?>
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
                                <input type="password" id="password" name="password" class="auth-input" placeholder="********" data-rules="required|min:8">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <input type="password" id="confirm_password" name="confirm_password" class="auth-input" placeholder="********" data-rules="required" data-match="password">
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
                    
                    <div class="auth-footer">
                        Already have an account? <a href="login.php">Sign In</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
