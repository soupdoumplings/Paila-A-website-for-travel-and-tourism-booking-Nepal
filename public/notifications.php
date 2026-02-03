<?php
session_start();
require_once '../config/db.php';
require_once '../helpers/functions.php';

if (!is_logged_in()) {
    redirect(url('public/authentication/login.php'));
}

$user = get_user();

// Mark all read
if (isset($_POST['mark_all_read'])) {
    mark_all_notifications_read($user['id']);
    redirect(url('public/notifications.php'));
}

// Mark single read
if (isset($_GET['read'])) {
    mark_notification_read($_GET['read'], $user['id']);
    redirect(url('public/notifications.php'));
}

$notifications = get_user_notifications($user['id']);
include '../includes/header.php';
?>

<section class="page-header" style="background: var(--color-stone-900); padding: 8rem 0 4rem; color: white;">
    <div class="container">
        <h1 style="font-family: var(--font-serif); font-size: 3rem; margin-bottom: 1rem;">Notifications</h1>
        <p style="opacity: 0.8; font-size: 1.1rem;">Updates about your journeys.</p>
    </div>
</section>

<section style="padding: 4rem 0; background: var(--color-stone-50); min-height: 60vh;">
    <div class="container" style="max-width: 800px;">
        
        <div style="display: flex; justify-content: flex-end; margin-bottom: 2rem;">
             <?php if(!empty($notifications)): ?>
                <form method="POST">
                    <button type="submit" name="mark_all_read" class="btn" style="background: white; border: 1px solid var(--color-stone-300); color: var(--color-stone-600); padding: 0.6rem 1.2rem; font-size: 0.9rem;">
                        <i class="fa-solid fa-check-double"></i> Mark All Read
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <div style="background: white; border-radius: 1rem; overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); border: 1px solid var(--color-stone-200);">
            <?php if (empty($notifications)): ?>
                <div style="padding: 4rem; text-align: center; color: var(--color-stone-500);">
                    <i class="fa-regular fa-bell-slash" style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p>You have no notifications at this time.</p>
                </div>
            <?php else: ?>
                <ul style="list-style: none; margin: 0; padding: 0;">
                    <?php foreach ($notifications as $n): ?>
                    <li style="border-bottom: 1px solid var(--color-stone-100); padding: 1.5rem; display: flex; gap: 1.5rem; align-items: flex-start; <?php echo $n['is_read'] ? 'opacity: 0.7;' : 'background: #fdfdfd;'; ?>">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: <?php echo $n['is_read'] ? 'var(--color-stone-100)' : '#fef3c7'; ?>; color: <?php echo $n['is_read'] ? 'var(--color-stone-400)' : 'var(--color-amber-500)'; ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <h3 style="font-weight: 600; font-size: 1.05rem; <?php echo !$n['is_read'] ? 'color: var(--color-stone-900);' : 'color: var(--color-stone-600);'; ?>">
                                    <?php echo e($n['title']); ?>
                                    <?php if(!$n['is_read']): ?>
                                        <span style="display: inline-block; width: 8px; height: 8px; background: var(--color-amber-500); border-radius: 50%; margin-left: 0.5rem;"></span>
                                    <?php endif; ?>
                                </h3>
                                <span style="font-size: 0.85rem; color: var(--color-stone-400);"><?php echo date('M d', strtotime($n['created_at'])); ?></span>
                            </div>
                            <p style="color: var(--color-stone-600); font-size: 0.95rem; margin-bottom: 0.75rem; line-height: 1.5;">
                                <?php echo e($n['message']); ?>
                            </p>
                            <div style="display: flex; gap: 1rem; font-size: 0.9rem;">
                                <?php if ($n['link']): ?>
                                    <a href="<?php echo e($n['link']); ?>" style="color: var(--color-stone-900); text-decoration: underline; font-weight: 500;">
                                        View Details
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
</section>

<?php include '../includes/footer.php'; ?>
