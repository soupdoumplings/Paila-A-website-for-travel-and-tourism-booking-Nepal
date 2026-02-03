<?php
session_start();
require_once '../config/db.php';
require_once '../helpers/functions.php';

require_login();
$user = get_user();

// Handle mark all
if (isset($_POST['mark_all_read'])) {
    mark_all_notifications_read($user['id']);
    redirect('notifications.php');
}

// Handle single mark
if (isset($_GET['read'])) {
    mark_notification_read($_GET['read'], $user['id']);
    redirect('notifications.php');
}

$notifications = get_user_notifications($user['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body style="background: var(--color-stone-100);">

    <div class="admin-hero">
        <?php 
            $base = '../';
            include '../includes/header.php'; 
        ?>

        <section style="padding: 6rem 0 5rem;">
            <div class="container">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <a href="index.php" style="color: var(--color-stone-600); text-decoration: none; font-weight: 600;"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
                    <span style="opacity: 0.3;">/</span>
                    <span style="opacity: 0.7; text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.8rem; color: var(--color-stone-500);">Account</span>
                </div>
                <h1 style="font-size: 3.5rem; font-family: var(--font-serif); color: var(--color-stone-900); margin: 0;">Notifications</h1>
                <p style="font-size: 1.1rem; color: var(--color-stone-600); margin-top: 1rem;">Recent alerts and updates.</p>
            </div>
        </section>
    </div>

    <div class="container" style="margin-top: -2rem; position: relative; z-index: 10; padding-bottom: 5rem;">
        
        <div style="display: flex; justify-content: flex-end; margin-bottom: 2rem;">
            <?php if(!empty($notifications)): ?>
                <form method="POST">
                    <button type="submit" name="mark_all_read" class="btn" style="background: white; border: 1px solid var(--color-stone-300); color: var(--color-stone-600); padding: 0.75rem 1.5rem; font-size: 0.9rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <i class="fa-solid fa-check-double"></i> Mark All Read
                    </button>
                </form>
            <?php endif; ?>
        </div>

            <div style="background: white; border-radius: 1rem; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <?php if (empty($notifications)): ?>
                    <div style="padding: 4rem; text-align: center; color: var(--color-stone-500);">
                        <i class="fa-regular fa-bell-slash" style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p>No notifications found.</p>
                    </div>
                <?php else: ?>
                    <ul style="list-style: none; margin: 0; padding: 0;">
                        <?php foreach ($notifications as $n): ?>
                        <li style="border-bottom: 1px solid var(--color-stone-100); padding: 1.5rem; display: flex; gap: 1.5rem; align-items: flex-start; <?php echo $n['is_read'] ? 'opacity: 0.7;' : 'background: #fdfdfd;'; ?>">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: <?php echo $n['is_read'] ? 'var(--color-stone-200)' : 'var(--color-amber-100)'; ?>; color: <?php echo $n['is_read'] ? 'var(--color-stone-500)' : 'var(--color-amber-600)'; ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fa-solid fa-bell"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <h3 style="font-weight: 600; font-size: 1.05rem; <?php echo !$n['is_read'] ? 'color: var(--color-stone-900);' : 'color: var(--color-stone-600);'; ?>">
                                        <?php echo e($n['title']); ?>
                                        <?php if(!$n['is_read']): ?>
                                            <span style="display: inline-block; width: 8px; height: 8px; background: var(--color-red-500); border-radius: 50%; margin-left: 0.5rem;"></span>
                                        <?php endif; ?>
                                    </h3>
                                    <span style="font-size: 0.85rem; color: var(--color-stone-500);"><?php echo date('M d, g:i a', strtotime($n['created_at'])); ?></span>
                                </div>
                                <p style="color: var(--color-stone-600); font-size: 0.95rem; margin-bottom: 0.75rem; line-height: 1.5;">
                                    <?php echo e($n['message']); ?>
                                </p>
                                <div style="display: flex; gap: 1rem; font-size: 0.9rem;">
                                    <?php if ($n['link']): ?>
                                        <a href="../<?php echo e($n['link']); ?>" style="color: var(--color-amber-600); text-decoration: none; font-weight: 500;">
                                            View Details &rarr;
                                        </a>
                                    <?php endif; ?>
                                    <?php if(!$n['is_read']): ?>
                                        <a href="?read=<?php echo $n['id']; ?>" style="color: var(--color-stone-400); text-decoration: none;">Mark as read</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

        </div>
    </div>

</body>
</html>
