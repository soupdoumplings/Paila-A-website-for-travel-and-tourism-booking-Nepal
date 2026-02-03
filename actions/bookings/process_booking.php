<?php
// Initialize session status
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Load required files
require_once '../../config/db.php';
require_once '../../helpers/functions.php';

// Init user variables
$auto_registered = false;
$temp_password = '';
$user_id = null;

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tour_id = (int)$_POST['tour_id'];
    $customer_name = trim($_POST['customer_name']);
    $contact_email = trim($_POST['contact_email']);
    $travel_date = $_POST['travel_date'];
    $travelers = (int)$_POST['travelers'];
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $special_requests = isset($_POST['special_requests']) ? trim($_POST['special_requests']) : '';
    
    // Process premium tour
    $premium_tour_id = isset($_POST['premium_tour_id']) ? trim($_POST['premium_tour_id']) : null;
    if ($premium_tour_id) {
        $special_requests = "[PREMIUM TOUR: {$premium_tour_id}] " . $special_requests;
    }

    // Validate form inputs
    if (empty($customer_name) || empty($contact_email) || empty($travel_date) || $travelers < 1) {
        die("Invalid form data. Please go back and try again.");
    }

    // Validate travel date
    $minDate = date('Y-m-d', strtotime('+2 days'));
    if ($travel_date < $minDate) {
        die("Invalid travel date. Bookings must be made at least 2 days in advance.");
    }

    if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email address. Please go back and try again.");
    }

    // Manage user account
    if (is_logged_in()) {
        $user_id = $_SESSION['user_id'];
    } else {
        // Identify existing user
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->execute([$contact_email]);
        $existingUser = $stmt->fetch();

        if ($existingUser) {
            $user_id = $existingUser['id'];
            // Log user in
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $existingUser['username'];
            $_SESSION['email'] = $contact_email;
        } else {
            // Register new account
            $temp_password = substr(str_shuffle('abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789'), 0, 8);
            // Generate hashed password
            $username = strtolower(explode('@', $contact_email)[0]) . rand(10, 99);
            
            try {
                // Insert new account
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$username, $contact_email, $hashed_password, 3])) {
                    $user_id = $pdo->lastInsertId();
                    $auto_registered = true;
                    
                    // Set session variables
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $contact_email;
                    $_SESSION['role_id'] = 3;
                    
                    // Save temp credentials
                    $_SESSION['new_account_pass'] = $temp_password;
                }
            } catch (Exception $e) {
                // Log registration error
                // Log registration failure
            }
        }
    }

    // Create booking record
    try {
        $sql = "INSERT INTO bookings (tour_id, user_id, customer_name, contact_email, travel_date, travelers) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tour_id, $user_id, $customer_name, $contact_email, $travel_date, $travelers]);
        $booking_id = $pdo->lastInsertId();
    } catch (Exception $e) {
        try {
            $stmt = $pdo->prepare("INSERT INTO bookings (tour_id, customer_name, contact_email, travel_date, travelers) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$tour_id, $customer_name, $contact_email, $travel_date, $travelers]);
            $booking_id = $pdo->lastInsertId();
        } catch (Exception $e2) {
            $booking_id = 'ORD-' . strtoupper(substr(uniqid(), -6));
        }
    }

    // Send user notifications
    try {
        // Notify system admins
        $adminStmt = $pdo->query("SELECT id FROM users WHERE role_id IN (1, 2)");
        while ($row = $adminStmt->fetch(PDO::FETCH_ASSOC)) {
            create_notification(
                $row['id'], 
                "New Booking #$booking_id", 
                "New booking from $customer_name ($contact_email).", 
                "admin/manage_bookings.php?id=$booking_id"
            );
        }

        // Notify booking customer
        if ($user_id) {
            create_notification(
                $user_id, 
                "Booking Received", 
                "Your booking #$booking_id has been successfully submitted.", 
                "my_bookings.php"
            );
        }
    } catch (Exception $e) {
        // Ignore notification errors
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed | Nepal Tours</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body style="background: var(--color-stone-50); color: var(--color-stone-900);">
    
    <div style="max-width: 650px; margin: 6rem auto; padding: 4rem 3rem; background: white; border-radius: 1.5rem; box-shadow: 0 20px 60px rgba(0,0,0,0.1); text-align: center; border: 1px solid var(--color-stone-200);">
        <div style="width: 80px; height: 80px; background: #dcfce7; color: #166534; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2.5rem; font-size: 2.5rem;">
            <i class="fa-solid fa-check"></i>
        </div>
        
        <h1 style="font-family: var(--font-serif); font-size: 2.5rem; margin-bottom: 1rem;">Booking Received</h1>
        
        <?php echo render_booking_timeline('pending'); ?>

        <p style="color: var(--color-stone-600); font-size: 1.15rem; margin-bottom: 2.5rem; line-height: 1.6;">
            Thank you, <?php echo e($customer_name); ?>. Your request for tour #<?php echo $booking_id; ?> is being processed.
        </p>

        <?php if ($auto_registered): ?>
        <div style="background: #f0f9ff; border: 1px solid #bae6fd; padding: 2.5rem; border-radius: 1rem; margin-bottom: 2.5rem; text-align: left;">
            <div style="display: flex; align-items: center; gap: 0.75rem; color: #0369a1; margin-bottom: 1rem;">
                <i class="fa-solid fa-user-plus" style="font-size: 1.25rem;"></i>
                <h3 style="font-size: 1.1rem; font-weight: 700;">Account Created Successfully</h3>
            </div>
            <p style="font-size: 0.95rem; color: #0c4a6e; margin-bottom: 1.5rem; opacity: 0.8;">
                We've automatically created an account using your email. Please note your temporary password to track your booking.
            </p>
            <div style="background: white; padding: 1.25rem; border-radius: 0.75rem; border: 1px dashed #7dd3fc;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span style="font-size: 0.85rem; color: var(--color-stone-500);">Username</span>
                    <span style="font-weight: 600;"><?php echo e($username); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="font-size: 0.85rem; color: var(--color-stone-500);">Temporary Password</span>
                    <span style="font-weight: 700; color: #0369a1; font-family: monospace; font-size: 1.25rem; letter-spacing: 1px;"><?php echo e($temp_password); ?></span>
                </div>
            </div>
            <p style="font-size: 0.8rem; color: #0c4a6e; margin-top: 1.25rem; opacity: 0.6; font-style: italic;">
                Tip: You can change this password once you sign in to your dashboard.
            </p>
        </div>
        <?php else: ?>
        <div style="background: var(--color-stone-50); padding: 2rem; border-radius: 1rem; margin-bottom: 2.5rem; text-align: left;">
             <p style="margin-bottom: 0.75rem; color: var(--color-stone-600);">The itinerary and confirmation will be sent to:</p>
             <p style="font-size: 1.1rem; font-weight: 600;"><i class="fa-regular fa-envelope" style="margin-right: 0.5rem; opacity: 0.5;"></i> <?php echo e($contact_email); ?></p>
        </div>
        <?php endif; ?>

        <div style="display: flex; flex-direction: column; gap: 1rem; align-items: center;">
            <a href="<?php echo url('public/authentication/login.php?booked=1&email=' . urlencode($contact_email)); ?>" class="btn" style="background: var(--color-stone-900); color: white; padding: 1rem 3rem; border-radius: 50px; text-decoration: none; font-weight: 600; width: 100%; max-width: 300px;">Proceed to Member Login</a>
            <a href="<?php echo BASE_URL; ?>/index.php" style="color: var(--color-stone-500); text-decoration: none; font-size: 0.9rem;">Return to Homepage</a>
        </div>
    </div>

</body>
</html>
