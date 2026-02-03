<?php
session_start();
require_once '../config/db.php';
require_once '../helpers/functions.php';

$error = '';

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

        // Validate input fields
    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
                // Fetch admin user
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

                // Verify credentials
        if ($user && password_verify($password, $user['password'])) {
            // Establish admin session
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            redirect('index.php');
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Nepal Tours</title>
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/admin.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/validation.css'); ?>">
    <script src="<?php echo url('assets/js/validation.js'); ?>" defer></script>
</head>
<body style="background: var(--color-stone-100);">

    <!-- Login box wrapper -->
        <h2 style="margin-bottom: 2rem;">Admin Login</h2>
        
        <?php if($error): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <!-- Admin login form -->
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input" data-rules="required">
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" data-rules="required">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>
        
        <p style="margin-top: 2rem; font-size: 0.9rem;">
            <a href="../index.php">Create new password? (JK, go back home)</a>
        </p>
    </div>

</body>
</html>
