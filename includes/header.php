<?php
// Session is now handled in entry scripts
// Load config
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/db.php';
}
// Load dependencies
if (!function_exists('get_unread_notification_count')) {
    require_once __DIR__ . '/../helpers/functions.php';
}

// Get notifications
$unreadCount = 0;
if (isset($_SESSION['user_id']) || isset($_SESSION['admin_id'])) {
    if (isset($pdo)) {
        try {
            $uid = $_SESSION['user_id'] ?? $_SESSION['admin_id'];
            $unreadCount = get_unread_notification_count($uid);
        } catch (Exception $e) { /* ignore */ }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Nepal Tours | Luxury Travel & Trekking'; ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>?v=1.1">
    <link rel="stylesheet" href="<?php echo url('assets/css/validation.css'); ?>?v=1.1">
    <link rel="stylesheet" href="<?php echo url('assets/css/tours.css'); ?>?v=1.1">
    <!-- Page styles -->
    <?php if (isset($extraCss) && is_array($extraCss)): ?>
        <?php foreach($extraCss as $css): ?>
            <link rel="stylesheet" href="<?php echo url($css); ?>?v=<?php echo time(); ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Admin styles -->
    <?php if(strpos($_SERVER['REQUEST_URI'], 'admin') !== false): ?>
        <link rel="stylesheet" href="<?php echo url('assets/css/admin.css'); ?>?v=1.1">
    <?php endif; ?>
    
    <!-- Scroll effects -->
    <script src="<?php echo url('assets/js/scroll-effects.js'); ?>" defer></script>
    
    <!-- Scripts -->
    <script>
        window.PAILA_CONFIG = {
            baseUrl: '<?php echo url(""); ?>'.replace(/\/$/, '') + '/'
        };
    </script>
    <script src="<?php echo url('assets/js/main.js'); ?>?v=1.1" defer></script>
</head>
<body>

    <!-- Navbar -->
    <?php 
        $navClass = "navbar";
        $isAdmin = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
        if ($isAdmin) {
            $navClass .= " nav-transparent nav-no-scroll";
        }
    ?>
    <nav class="<?php echo $navClass; ?>">
        <div class="container flex justify-between items-center">

            <?php if (!$isAdmin): ?>
                <a href="<?php echo url('index.php'); ?>" class="logo">पाइला</a>
            <?php endif; ?>
            
            <div style="display: flex; align-items: center; gap: 1rem; width: <?php echo $isAdmin ? '100%' : 'auto'; ?>; justify-content: <?php echo $isAdmin ? 'space-between' : 'flex-end'; ?>;">
                <div class="nav-links" style="display: flex; align-items: center; gap: 1rem; font-size: 0.82rem; font-weight: 500; width: <?php echo $isAdmin ? '100%' : 'auto'; ?>; justify-content: <?php echo $isAdmin ? 'space-between' : 'flex-end'; ?>;">
                    <?php if($isAdmin): ?>
                        <div style="display: flex; align-items: center; gap: 1.5rem; width: 100%;">
<a href="<?php echo url('admin/index.php'); ?>" class="<?php echo (strpos($_SERVER['REQUEST_URI'], 'admin/index.php') !== false || substr($_SERVER['REQUEST_URI'], -7) == '/admin/') ? 'active' : ''; ?>" style="font-weight: 600; font-size: 0.9rem; letter-spacing: 0.05em;">DASHBOARD</a>
                            
                            <div style="display: flex; align-items: center; gap: 1rem; padding-left: 1rem; border-left: 1px solid rgba(0,0,0,0.1); margin-right: auto;">
                                <?php if(isset($_SESSION['admin_logged_in']) && is_super_admin()): ?>
                                <a href="<?php echo $base; ?>admin/manage_admins.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], 'manage_admins.php') !== false ? 'active' : ''; ?>" style="font-size: 0.8rem; opacity: 0.8; text-decoration: none; display: flex; align-items: center; gap: 0.35rem; color: var(--color-stone-700);">
                                    <i class="fa-solid fa-users-gear"></i> ADMINS
                                </a>
                                <?php endif; ?>
                                <a href="<?php echo $base; ?>admin/export.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], 'export.php') !== false ? 'active' : ''; ?>" style="font-size: 0.8rem; opacity: 0.8; text-decoration: none; display: flex; align-items: center; gap: 0.35rem; color: var(--color-stone-700);">
                                    <i class="fa-solid fa-download"></i> EXPORT
                                </a>
                                <a href="<?php echo $base; ?>admin/import.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], 'import.php') !== false ? 'active' : ''; ?>" style="font-size: 0.8rem; opacity: 0.8; text-decoration: none; display: flex; align-items: center; gap: 0.35rem; color: var(--color-stone-700);">
                                    <i class="fa-solid fa-upload"></i> IMPORT
                                </a>
                                <a href="<?php echo $base; ?>admin/manage_guides.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], 'manage_guides.php') !== false ? 'active' : ''; ?>" style="font-size: 0.8rem; opacity: 0.8; text-decoration: none; display: flex; align-items: center; gap: 0.35rem; color: var(--color-stone-700);">
                                    <i class="fa-solid fa-person-walking-luggage"></i> GUIDES
                                </a>
                                <a href="<?php echo $base; ?>admin/tour_form.php" class="<?php echo strpos($_SERVER['REQUEST_URI'], 'tour_form.php') !== false ? 'active' : ''; ?>" style="font-size: 0.8rem; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 0.35rem; color: var(--color-emerald-700);">
                                    <i class="fa-solid fa-plus"></i> NEW
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo url('public/collection.php'); ?>">COLLECTION</a>
                        <a href="<?php echo url('public/archive.php'); ?>">ARCHIVE</a>
                        <a href="<?php echo url('public/premium.php'); ?>">PREMIUM</a>
                    <?php endif; ?>
                    
                    <?php if(isset($_SESSION['admin_logged_in']) && strpos($_SERVER['REQUEST_URI'], '/admin/') === false): ?>
                        <a href="<?php echo BASE_URL; ?>/admin/index.php" style="opacity: 0.7; text-decoration: none; font-size: 1rem;">⚙</a>
                    <?php endif; ?>
                    
                    <!-- Notifications -->
                    <?php if(is_logged_in()): ?>
                        <?php 
                            $notifLink = $isAdmin ? BASE_URL . '/admin/notifications.php' : BASE_URL . '/public/notifications.php'; 
                        ?>
                        <a href="<?php echo $notifLink; ?>" class="notification-bell" style="position: relative; color: white; font-size: 1.1rem; text-decoration: none; margin-right: 0.5rem;" title="Notifications">
                            <i class="fa-regular fa-bell"></i>
                            <?php if($unreadCount > 0): ?>
                                <span style="position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; font-size: 0.65rem; font-weight: 700; width: 16px; height: 16px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid <?php echo $isAdmin ? 'var(--color-stone-900)' : 'rgba(255,255,255,0.1)'; ?>;">
                                    <?php echo $unreadCount > 9 ? '9+' : $unreadCount; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>

                    <!-- Authentication -->
                    <?php if(is_logged_in()): ?>
                         <!-- User is logged in, show sidebar toggle -->
                         <button id="user-icon" style="background: none; border: none; color: white; font-size: 1.1rem; cursor: pointer; padding: 0.4rem; transition: opacity 0.3s;">
                            <i class="fa-solid fa-user"></i>
                        </button>
                    <?php else: ?>
                        <a href="<?php echo url('public/authentication/login.php'); ?>" style="font-weight: 500; text-decoration: none; color: white;">LOGIN</a>
                        <a href="<?php echo url('public/authentication/register.php'); ?>" style="background: white; color: black; padding: 0.4rem 1rem; border-radius: 2px; font-weight: 600; text-decoration: none; font-size: 0.75rem;">REGISTER</a>
                    <?php endif; ?>
                </div>
            </div>
    </nav>

    <!-- System alert -->
    <?php if (isset($db_error) && $db_error): ?>
    <div style="background: #fef2f2; border-bottom: 1px solid #fee2e2; padding: 0.75rem 0; text-align: center; color: #991b1b; font-size: 0.85rem; font-weight: 500; position: relative; z-index: 1000;">
        <div class="container">
            <i class="fa-solid fa-triangle-exclamation" style="margin-right: 0.5rem;"></i>
            DATABASE OFFLINE: Some features may be limited. Booking and management are in read-only mode.
        </div>
    </div>
    <?php endif; ?>

    <!-- Overlay -->
    <div id="sidebar-overlay" style="position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 99; opacity: 0; visibility: hidden; transition: all 0.3s ease;"></div>

    <!-- Sidebar -->
    <div id="user-sidebar" style="position: fixed; top: 0; right: 0; width: 350px; height: 100vh; background: var(--color-stone-900); color: white; z-index: 100; transform: translateX(100%); transition: transform 0.3s ease; box-shadow: -4px 0 20px rgba(0,0,0,0.3);">
        <!-- Close toggle -->
        <button id="close-sidebar" style="position: absolute; top: 1.5rem; right: 1.5rem; background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; line-height: 1; opacity: 0.7; transition: opacity 0.3s;">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <!-- Content -->
        <div style="padding: 3rem 2rem;">
            <!-- Profile -->
            <div style="text-align: center; padding-bottom: 2rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
                <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--color-stone-700); margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 2.5rem;">
                    <i class="fa-solid fa-user"></i>
                </div>
                <?php $currentUser = get_user(); ?>
                <h3 style="font-size: 1.25rem; margin-bottom: 0.25rem;">
                    <?php echo $currentUser ? e($currentUser['username']) : 'Guest'; ?>
                </h3>
                <p style="font-size: 0.875rem; opacity: 0.6;">
                    <?php echo $currentUser ? e($currentUser['email']) : 'Please login'; ?>
                </p>
            </div>

            <!-- Menu -->
            <nav style="margin-top: 2rem;">
                <a href="<?php echo url('public/my_account.php'); ?>" class="sidebar-menu-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-radius: 0.5rem; transition: background 0.3s; text-decoration: none; color: white; margin-bottom: 0.5rem;">
                    <i class="fa-solid fa-user-gear" style="font-size: 1.25rem; width: 1.5rem;"></i>
                    <span>My Account</span>
                </a>
                <a href="<?php echo url('public/my_bookings.php'); ?>" class="sidebar-menu-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-radius: 0.5rem; transition: background 0.3s; text-decoration: none; color: white; margin-bottom: 0.5rem;">
                    <i class="fa-solid fa-calendar-check" style="font-size: 1.25rem; width: 1.5rem;"></i>
                    <span>My Bookings</span>
                </a>
                <a href="<?php echo url('public/user_requests.php'); ?>" class="sidebar-menu-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-radius: 0.5rem; transition: background 0.3s; text-decoration: none; color: white; margin-bottom: 0.5rem;">
                    <i class="fa-solid fa-key" style="font-size: 1.25rem; width: 1.5rem;"></i>
                    <span>My Access Requests</span>
                </a>
                <?php if(is_logged_in()): ?>
                <a href="<?php echo url('actions/auth/logout.php'); ?>" class="sidebar-menu-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-radius: 0.5rem; transition: background 0.3s; text-decoration: none; color: white; margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1.5rem;">
                    <i class="fa-solid fa-right-from-bracket" style="font-size: 1.25rem; width: 1.5rem;"></i>
                    <span>Logout</span>
                </a>
                <?php else: ?>
                 <a href="<?php echo url('public/authentication/login.php'); ?>" class="sidebar-menu-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-radius: 0.5rem; transition: background 0.3s; text-decoration: none; color: white; margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1.5rem;">
                    <i class="fa-solid fa-right-to-bracket" style="font-size: 1.25rem; width: 1.5rem;"></i>
                    <span>Login</span>
                </a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
