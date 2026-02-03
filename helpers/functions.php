<?php
// Core helper logic

// Include project dependencies
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/message_functions.php';
require_once __DIR__ . '/notification_functions.php';

// Security and validation
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Helper functions
function redirect($url) {
    header("Location: $url");
    exit();
}

// Helper functions
function url($path) {
    $base = rtrim(BASE_URL, '/');
    return $base . '/' . ltrim($path, '/');
}

// Security and validation
function require_login() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        redirect(url('public/authentication/login.php')); 
    }
}

// Security and validation
function is_logged_in() {
    return isset($_SESSION['user_id']) || isset($_SESSION['admin_id']);
}

// Database interaction logic
function get_user() {
    if (is_logged_in()) {
        global $pdo;
        
        // Determine user identification from active session variables
        $userId = $_SESSION['user_id'] ?? $_SESSION['admin_id'];
        
        // Check cached session data to optimize database performance
        if (!isset($_SESSION['role_name']) || !isset($_SESSION['username'])) {
            // Fetch user profile with role information from database
            $stmt = $pdo->prepare("
                SELECT u.*, r.name as role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Cache user profile information in session for efficiency
                $_SESSION['role_name'] = $user['role_name'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                return $user;
            }
        }
        
        // Return user data directly from cached session variables
        return [
            'id' => $userId,
            'username' => $_SESSION['username'] ?? 'User',
            'email' => $_SESSION['email'] ?? '',
            'role' => $_SESSION['role_name'] ?? 'user'
        ];
    }
    
    // Return null value if user authentication is missing
    return null;
}

// Security and validation
function is_super_admin() {
    $user = get_user();
    
    // Check multiple role fields for super admin privileges
    return $user && ($user['role'] === 'super_admin' || (isset($user['role_name']) && $user['role_name'] === 'super_admin'));
}

// Security and validation
function is_admin() {
    $user = get_user();
    
    if (!$user) return false;
    
    // Extract user role from database or session cache
    $role = $user['role'] ?? $user['role_name'] ?? '';
    
    // Validate against predefined list of administrator roles
    return in_array($role, ['admin', 'super_admin']);
}

// Determining if user belongs to the tourguide role
function is_tour_guide() {
    $user = get_user();
    if (!$user) return false;
    
    $role = $user['role'] ?? $user['role_name'] ?? '';
    return $role === 'tourguide';
}

// User interface utilities
function render_booking_timeline($status = 'pending', $isCompact = false) {
    // Initialize basic visual classes for each timeline step
    $submittedClass = 'completed';  // Always green checkmark
    $pendingClass = ($status === 'pending') ? 'active' : 'completed';
    $finalClass = '';  // Will be set based on status below
    $finalIcon = 'fa-circle-check';  // Default: checkmark
    $finalLabel = 'Confirmed';  // Default label
    $progressWidth = '16%';  // Default: middle of first step

    // Modify timeline progress based on current booking status
    if ($status === 'pending') {
        // Still under review
        $progressWidth = '50%';  // Progress bar stops at middle of second step
        
    } elseif ($status === 'confirmed') {
        // Booking approved
        $finalClass = 'completed';  // Green checkmark on final step
        $progressWidth = '84%';  // Progress bar reaches final step
        
    } elseif ($status === 'cancelled') {
        // Booking rejected
        $finalClass = 'rejected';  // Red X on final step
        $finalIcon = 'fa-circle-xmark';  // X icon instead of checkmark
        $finalLabel = 'Rejected';  // Change label
        $progressWidth = '84%';  // Progress bar still reaches final step
    }

    // Apply compact visual styling based on parameter setting
    $compactClass = $isCompact ? 'timeline-compact' : '';
    
    // Start output buffering to capture visual component markup
    ob_start(); ?>
    
    <div class="booking-timeline-wrapper <?php echo $compactClass; ?>">
        <div class="booking-timeline">
            <!-- Animated progress bar that fills based on status -->
            <div class="progress-bar" style="width: <?php echo $progressWidth; ?>;"></div>
            
            <!-- Step 1: Submitted (always completed) -->
            <div class="timeline-step <?php echo $submittedClass; ?>">
                <div class="step-icon"><i class="fa-solid fa-paper-plane"></i></div>
                <div class="step-label">Submitted</div>
            </div>
            
            <!-- Step 2: Pending Review (active if pending, completed otherwise) -->
            <div class="timeline-step <?php echo $pendingClass; ?>">
                <div class="step-icon"><i class="fa-solid fa-clock"></i></div>
                <div class="step-label">Pending Review</div>
            </div>
            
            <!-- Step 3: Final outcome (confirmed or rejected) -->
            <div class="timeline-step <?php echo $finalClass; ?>">
                <div class="step-icon"><i class="fa-solid <?php echo $finalIcon; ?>"></i></div>
                <div class="step-label"><?php echo $finalLabel; ?></div>
            </div>
        </div>
    </div>
    
    <?php
    // Return captured visual markup from the output buffer
    return ob_get_clean();
}
// Image discovery logic
function get_tour_image($tour) {
    // 1. Check for external URL
    if (!empty($tour['image']) && filter_var($tour['image'], FILTER_VALIDATE_URL)) {
        return $tour['image'];
    }

    // 2. Check for uploaded file
    if (!empty($tour['image'])) {
        $uploadPath = __DIR__ . '/../public/uploads/' . $tour['image'];
        if (file_exists($uploadPath)) {
            return url('public/uploads/' . $tour['image']);
        }
    }

    // 3. Auto-discovery by location
    if (!empty($tour['location'])) {
        $location = trim($tour['location']);
        // Remove common suffixes like " Valley" or " Region" for better folder matching
        $folderName = preg_replace('/ (Valley|Region|District)$/i', '', $location); 
        
        $folderMap = [
            'Rara Lake' => 'Rara',
            'Kathmandu' => 'kathmandu'
        ];
        
        $finalFolder = isset($folderMap[$folderName]) ? $folderMap[$folderName] : $folderName;
        $imagesDir = __DIR__ . '/../assets/images/' . $finalFolder;
        
        if (is_dir($imagesDir)) {
            $files = glob($imagesDir . '/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);
            if ($files && count($files) > 0) {
                return url('assets/images/' . $finalFolder . '/' . basename($files[0]));
            }
        }
    }

    // 4. Category-based fallbacks
    $cat = isset($tour['category']) ? strtolower($tour['category']) : 'trekking';
    $stockMap = [
        'trekking' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=800&q=80',
        'cultural' => 'https://images.unsplash.com/photo-1585409677983-0f6c41ca9c3b?w=800&q=80',
        'culture' => 'https://images.unsplash.com/photo-1585409677983-0f6c41ca9c3b?w=800&q=80',
        'adventure' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80',
        'family' => 'https://images.unsplash.com/photo-1533130061792-64b345e4a833?w=800&q=80',
        'luxury' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=800&q=80',
        'weekend' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80',
        'budget' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=800&q=80'
    ];

    return $stockMap[$cat] ?? $stockMap['trekking'];
}
