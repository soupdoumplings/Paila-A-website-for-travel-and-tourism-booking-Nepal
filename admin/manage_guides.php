<?php
require_once __DIR__ . '/../helpers/security.php';
require_once '../config/db.php';
require_once '../helpers/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    secure_session_start();
}

require_login();
if (!is_admin()) {
    die("Access Denied. You must be an Admin to view this page.");
}

$success = '';
$error = '';
$defaultAvatar = 'assets/images/Pokhara/pexels-photo-30131353.jpeg';

function guide_profile_payload(array $source, string $defaultAvatar): array
{
    return [
        'full_name' => trim($source['full_name'] ?? ''),
        'phone' => trim($source['phone'] ?? ''),
        'license_no' => trim($source['license_no'] ?? ''),
        'languages' => trim($source['languages'] ?? ''),
        'specialties' => trim($source['specialties'] ?? ''),
        'experience_years' => max(0, (int)($source['experience_years'] ?? 0)),
        'rating' => min(5, max(1, (float)($source['rating'] ?? 4.8))),
        'bio' => trim($source['bio'] ?? ''),
        'avatar' => trim($source['avatar'] ?? '') ?: $defaultAvatar,
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_guide'])) {
    require_csrf_token();

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $profile = guide_profile_payload($_POST, $defaultAvatar);

    if ($username === '' || $email === '' || $password === '' || $profile['full_name'] === '') {
        $error = "Username, email, password, and full name are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid guide email address.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            $error = "Username or email already exists.";
        } else {
            try {
                $pdo->beginTransaction();
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, 4)");
                $stmt->execute([$username, $email, $hashed]);
                $guideId = (int)$pdo->lastInsertId();

                $stmt = $pdo->prepare("
                    INSERT INTO guide_profiles
                    (user_id, full_name, phone, license_no, languages, specialties, experience_years, rating, bio, avatar, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $guideId,
                    $profile['full_name'],
                    $profile['phone'],
                    $profile['license_no'],
                    $profile['languages'],
                    $profile['specialties'],
                    $profile['experience_years'],
                    $profile['rating'],
                    $profile['bio'],
                    $profile['avatar'],
                    $_SESSION['user_id'] ?? null,
                ]);

                $pdo->commit();
                $success = "Guide account and profile created successfully.";
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error = "Failed to create guide profile.";
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    require_csrf_token();
    $guideId = (int)($_POST['user_id'] ?? 0);
    $profile = guide_profile_payload($_POST, $defaultAvatar);

    if ($guideId < 1 || $profile['full_name'] === '') {
        $error = "Guide and full name are required.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role_id = 4");
        $stmt->execute([$guideId]);

        if (!$stmt->fetchColumn()) {
            $error = "Guide account could not be found.";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO guide_profiles
                (user_id, full_name, phone, license_no, languages, specialties, experience_years, rating, bio, avatar, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    full_name = VALUES(full_name),
                    phone = VALUES(phone),
                    license_no = VALUES(license_no),
                    languages = VALUES(languages),
                    specialties = VALUES(specialties),
                    experience_years = VALUES(experience_years),
                    rating = VALUES(rating),
                    bio = VALUES(bio),
                    avatar = VALUES(avatar)
            ");
            if ($stmt->execute([
                $guideId,
                $profile['full_name'],
                $profile['phone'],
                $profile['license_no'],
                $profile['languages'],
                $profile['specialties'],
                $profile['experience_years'],
                $profile['rating'],
                $profile['bio'],
                $profile['avatar'],
                $_SESSION['user_id'] ?? null,
            ])) {
                $success = "Guide profile updated.";
            } else {
                $error = "Failed to update guide profile.";
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_guide'])) {
    require_csrf_token();
    $user_id = (int)($_POST['user_id'] ?? 0);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE tour_guide_id = ?");
    $stmt->execute([$user_id]);

    if ((int)$stmt->fetchColumn() > 0) {
        $error = "This guide is assigned to bookings. Reassign those bookings before deleting the account.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role_id = 4");
        if ($stmt->execute([$user_id])) {
            $success = "Tour guide deleted successfully.";
        } else {
            $error = "Failed to delete tour guide.";
        }
    }
}

$stmt = $pdo->query("
    SELECT
        u.id,
        u.username,
        u.email,
        u.created_at,
        gp.full_name,
        gp.phone,
        gp.license_no,
        gp.languages,
        gp.specialties,
        gp.experience_years,
        gp.rating,
        gp.bio,
        gp.avatar,
        creator.username AS created_by_name,
        (SELECT COUNT(*) FROM bookings b WHERE b.tour_guide_id = u.id) AS assigned_count
    FROM users u
    LEFT JOIN guide_profiles gp ON gp.user_id = u.id
    LEFT JOIN users creator ON creator.id = gp.created_by
    WHERE u.role_id = 4
    ORDER BY u.created_at DESC
");
$guides = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Manage Tour Guides";
$base = '../';
?>
<div class="admin-hero">
<?php include '../includes/header.php'; ?>
<section style="padding: 6rem 0 5rem;">
    <div class="container">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
            <a href="index.php" style="color: var(--color-stone-600); text-decoration: none; font-weight: 600;"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
            <span style="opacity: 0.3;">/</span>
            <span style="opacity: 0.7; text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.8rem; color: var(--color-stone-500);">Guide Privilege</span>
        </div>
        <h1 style="font-size: 3.5rem; font-family: var(--font-serif); color: var(--color-stone-900); margin: 0;">Guide Profiles</h1>
        <p style="max-width: 760px; margin-top: 1rem; color: var(--color-stone-600); font-size: 1.05rem;">Admins create guide accounts with the guest-facing details used when a booking is approved. Superadmins still create admin accounts from the admin manager.</p>
    </div>
</section>
</div>

<div class="container" style="margin-top: -2rem; position: relative; z-index: 10; padding-bottom: 5rem;">
    <div style="background: white; border: 1px solid var(--color-stone-200); border-radius: 1rem; padding: 2rem; box-shadow: 0 20px 60px rgba(28,25,23,0.08);">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 2rem;">
            <div>
                <div style="color: var(--color-amber-600); font-size: 0.78rem; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase;">Premium Guide Desk</div>
                <h2 style="font-size: 1.6rem; color: var(--color-stone-900); margin-top: 0.35rem;">Create and maintain guide identities</h2>
            </div>
            <div style="color: var(--color-stone-500); font-size: 0.9rem;">Total: <?php echo count($guides); ?> guides</div>
        </div>

        <?php if($success): ?>
            <div style="background: rgba(4, 47, 46, 0.08); border: 1px solid var(--color-teal-900); color: var(--color-teal-900); padding: 0.85rem 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.9rem;"><?php echo e($success); ?></div>
        <?php endif; ?>

        <?php if($error): ?>
            <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.85rem 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.9rem;"><?php echo e($error); ?></div>
        <?php endif; ?>

        <div style="background: var(--color-teal-900); padding: 1.75rem; border-radius: 0.75rem; margin-bottom: 2rem; color: white; box-shadow: inset 0 1px 0 rgba(255,255,255,0.08);">
            <h3 style="margin-bottom: 1rem; font-size: 1.25rem; font-family: var(--font-serif);">Register New Guide</h3>
            <form method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; align-items: end;">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="create_guide" value="1">
                <div>
                    <label style="font-size: 0.78rem; display: block; margin-bottom: 0.45rem; color: rgba(255,255,255,0.72);">Username</label>
                    <input type="text" name="username" required style="width: 100%; padding: 0.65rem; border-radius: 0.35rem; border: 1px solid rgba(255,255,255,0.18); background: rgba(0,0,0,0.18); color: white;">
                </div>
                <div>
                    <label style="font-size: 0.78rem; display: block; margin-bottom: 0.45rem; color: rgba(255,255,255,0.72);">Email</label>
                    <input type="email" name="email" required style="width: 100%; padding: 0.65rem; border-radius: 0.35rem; border: 1px solid rgba(255,255,255,0.18); background: rgba(0,0,0,0.18); color: white;">
                </div>
                <div>
                    <label style="font-size: 0.78rem; display: block; margin-bottom: 0.45rem; color: rgba(255,255,255,0.72);">Temp Password</label>
                    <input type="password" name="password" required style="width: 100%; padding: 0.65rem; border-radius: 0.35rem; border: 1px solid rgba(255,255,255,0.18); background: rgba(0,0,0,0.18); color: white;">
                </div>
                <div>
                    <label style="font-size: 0.78rem; display: block; margin-bottom: 0.45rem; color: rgba(255,255,255,0.72);">Full Name</label>
                    <input type="text" name="full_name" required style="width: 100%; padding: 0.65rem; border-radius: 0.35rem; border: 1px solid rgba(255,255,255,0.18); background: rgba(0,0,0,0.18); color: white;">
                </div>
                <div>
                    <label style="font-size: 0.78rem; display: block; margin-bottom: 0.45rem; color: rgba(255,255,255,0.72);">Phone</label>
                    <input type="text" name="phone" placeholder="+977 ..." style="width: 100%; padding: 0.65rem; border-radius: 0.35rem; border: 1px solid rgba(255,255,255,0.18); background: rgba(0,0,0,0.18); color: white;">
                </div>
                <div>
                    <label style="font-size: 0.78rem; display: block; margin-bottom: 0.45rem; color: rgba(255,255,255,0.72);">License No.</label>
                    <input type="text" name="license_no" style="width: 100%; padding: 0.65rem; border-radius: 0.35rem; border: 1px solid rgba(255,255,255,0.18); background: rgba(0,0,0,0.18); color: white;">
                </div>
                <div>
                    <label style="font-size: 0.78rem; display: block; margin-bottom: 0.45rem; color: rgba(255,255,255,0.72);">Languages</label>
                    <input type="text" name="languages" placeholder="Nepali, English" style="width: 100%; padding: 0.65rem; border-radius: 0.35rem; border: 1px solid rgba(255,255,255,0.18); background: rgba(0,0,0,0.18); color: white;">
                </div>
                <div>
                    <label style="font-size: 0.78rem; display: block; margin-bottom: 0.45rem; color: rgba(255,255,255,0.72);">Specialties</label>
                    <input type="text" name="specialties" placeholder="Everest, culture..." style="width: 100%; padding: 0.65rem; border-radius: 0.35rem; border: 1px solid rgba(255,255,255,0.18); background: rgba(0,0,0,0.18); color: white;">
                </div>
                <div>
                    <label style="font-size: 0.78rem; display: block; margin-bottom: 0.45rem; color: rgba(255,255,255,0.72);">Experience</label>
                    <input type="number" name="experience_years" min="0" value="3" style="width: 100%; padding: 0.65rem; border-radius: 0.35rem; border: 1px solid rgba(255,255,255,0.18); background: rgba(0,0,0,0.18); color: white;">
                </div>
                <div>
                    <label style="font-size: 0.78rem; display: block; margin-bottom: 0.45rem; color: rgba(255,255,255,0.72);">Rating</label>
                    <input type="number" name="rating" min="1" max="5" step="0.1" value="4.8" style="width: 100%; padding: 0.65rem; border-radius: 0.35rem; border: 1px solid rgba(255,255,255,0.18); background: rgba(0,0,0,0.18); color: white;">
                </div>
                <div style="grid-column: 1 / -1;">
                    <label style="font-size: 0.78rem; display: block; margin-bottom: 0.45rem; color: rgba(255,255,255,0.72);">Avatar / destination image path</label>
                    <input type="text" name="avatar" placeholder="assets/images/..." style="width: 100%; padding: 0.65rem; border-radius: 0.35rem; border: 1px solid rgba(255,255,255,0.18); background: rgba(0,0,0,0.18); color: white;">
                </div>
                <div style="grid-column: 1 / -1;">
                    <label style="font-size: 0.78rem; display: block; margin-bottom: 0.45rem; color: rgba(255,255,255,0.72);">Short guest-facing bio</label>
                    <textarea name="bio" rows="2" style="width: 100%; padding: 0.65rem; border-radius: 0.35rem; border: 1px solid rgba(255,255,255,0.18); background: rgba(0,0,0,0.18); color: white; resize: vertical;"></textarea>
                </div>
                <button type="submit" style="background: var(--color-amber-500); color: var(--color-stone-900); padding: 0.8rem 1rem; border: none; border-radius: 0.35rem; cursor: pointer; font-weight: 800;">Create Guide</button>
            </form>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 1.5rem;">
            <?php if(empty($guides)): ?>
                <div style="padding: 2rem; text-align: center; color: var(--color-stone-500); border: 1px dashed var(--color-stone-300); border-radius: 1rem;">No tour guides found.</div>
            <?php else: ?>
                <?php foreach ($guides as $guide): ?>
                    <?php
                    $guideName = $guide['full_name'] ?: $guide['username'];
                    $avatar = $guide['avatar'] ?: $defaultAvatar;
                    ?>
                    <div style="border: 1px solid var(--color-stone-200); border-radius: 1rem; overflow: hidden; background: #fffdfa; box-shadow: 0 14px 40px rgba(28,25,23,0.06);">
                        <div style="display: flex; gap: 1rem; padding: 1.25rem; border-bottom: 1px solid var(--color-stone-100); align-items: center;">
                            <img src="<?php echo e(url($avatar)); ?>" alt="<?php echo e($guideName); ?>" style="width: 72px; height: 72px; object-fit: cover; border-radius: 50%; border: 3px solid white; box-shadow: 0 6px 16px rgba(0,0,0,0.14);">
                            <div style="flex: 1;">
                                <h3 style="font-family: var(--font-serif); font-size: 1.45rem; margin: 0 0 0.25rem;"><?php echo e($guideName); ?></h3>
                                <div style="color: var(--color-stone-500); font-size: 0.9rem;"><?php echo e($guide['email']); ?></div>
                                <div style="margin-top: 0.45rem; display: flex; gap: 0.45rem; flex-wrap: wrap;">
                                    <span style="background: rgba(4,47,46,0.08); color: var(--color-teal-900); border: 1px solid rgba(4,47,46,0.18); border-radius: 99px; padding: 0.2rem 0.55rem; font-size: 0.75rem; font-weight: 800;"><?php echo number_format((float)($guide['rating'] ?: 4.8), 1); ?> rating</span>
                                    <span style="background: #fffbeb; color: #92400e; border: 1px solid #fde68a; border-radius: 99px; padding: 0.2rem 0.55rem; font-size: 0.75rem; font-weight: 800;"><?php echo (int)($guide['assigned_count'] ?? 0); ?> assigned</span>
                                </div>
                            </div>
                        </div>

                        <form method="POST" style="padding: 1.25rem; display: grid; gap: 0.9rem;">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="user_id" value="<?php echo (int)$guide['id']; ?>">
                            <input type="hidden" name="update_profile" value="1">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                <label style="font-size: 0.75rem; font-weight: 800; color: var(--color-stone-500); text-transform: uppercase;">Full Name<input type="text" name="full_name" value="<?php echo e($guideName); ?>" required style="margin-top: 0.35rem; width: 100%; padding: 0.6rem; border: 1px solid var(--color-stone-200); border-radius: 0.45rem;"></label>
                                <label style="font-size: 0.75rem; font-weight: 800; color: var(--color-stone-500); text-transform: uppercase;">Phone<input type="text" name="phone" value="<?php echo e($guide['phone'] ?? ''); ?>" style="margin-top: 0.35rem; width: 100%; padding: 0.6rem; border: 1px solid var(--color-stone-200); border-radius: 0.45rem;"></label>
                                <label style="font-size: 0.75rem; font-weight: 800; color: var(--color-stone-500); text-transform: uppercase;">License<input type="text" name="license_no" value="<?php echo e($guide['license_no'] ?? ''); ?>" style="margin-top: 0.35rem; width: 100%; padding: 0.6rem; border: 1px solid var(--color-stone-200); border-radius: 0.45rem;"></label>
                                <label style="font-size: 0.75rem; font-weight: 800; color: var(--color-stone-500); text-transform: uppercase;">Experience<input type="number" min="0" name="experience_years" value="<?php echo (int)($guide['experience_years'] ?? 0); ?>" style="margin-top: 0.35rem; width: 100%; padding: 0.6rem; border: 1px solid var(--color-stone-200); border-radius: 0.45rem;"></label>
                                <label style="font-size: 0.75rem; font-weight: 800; color: var(--color-stone-500); text-transform: uppercase;">Rating<input type="number" min="1" max="5" step="0.1" name="rating" value="<?php echo e($guide['rating'] ?: '4.8'); ?>" style="margin-top: 0.35rem; width: 100%; padding: 0.6rem; border: 1px solid var(--color-stone-200); border-radius: 0.45rem;"></label>
                                <label style="font-size: 0.75rem; font-weight: 800; color: var(--color-stone-500); text-transform: uppercase;">Joined<input type="text" value="<?php echo date('M d, Y', strtotime($guide['created_at'])); ?>" disabled style="margin-top: 0.35rem; width: 100%; padding: 0.6rem; border: 1px solid var(--color-stone-200); border-radius: 0.45rem; background: var(--color-stone-50);"></label>
                            </div>
                            <label style="font-size: 0.75rem; font-weight: 800; color: var(--color-stone-500); text-transform: uppercase;">Languages<input type="text" name="languages" value="<?php echo e($guide['languages'] ?? ''); ?>" style="margin-top: 0.35rem; width: 100%; padding: 0.6rem; border: 1px solid var(--color-stone-200); border-radius: 0.45rem;"></label>
                            <label style="font-size: 0.75rem; font-weight: 800; color: var(--color-stone-500); text-transform: uppercase;">Specialties<input type="text" name="specialties" value="<?php echo e($guide['specialties'] ?? ''); ?>" style="margin-top: 0.35rem; width: 100%; padding: 0.6rem; border: 1px solid var(--color-stone-200); border-radius: 0.45rem;"></label>
                            <label style="font-size: 0.75rem; font-weight: 800; color: var(--color-stone-500); text-transform: uppercase;">Avatar / destination image<input type="text" name="avatar" value="<?php echo e($avatar); ?>" style="margin-top: 0.35rem; width: 100%; padding: 0.6rem; border: 1px solid var(--color-stone-200); border-radius: 0.45rem;"></label>
                            <label style="font-size: 0.75rem; font-weight: 800; color: var(--color-stone-500); text-transform: uppercase;">Bio<textarea name="bio" rows="3" style="margin-top: 0.35rem; width: 100%; padding: 0.6rem; border: 1px solid var(--color-stone-200); border-radius: 0.45rem; resize: vertical;"><?php echo e($guide['bio'] ?? ''); ?></textarea></label>
                            <div style="display: flex; gap: 0.75rem; justify-content: space-between;">
                                <button type="submit" style="background: var(--color-teal-900); color: white; border: none; padding: 0.7rem 1rem; border-radius: 0.45rem; cursor: pointer; font-weight: 800;">Save Profile</button>
                                <button type="button" onclick="handleDeleteGuide(<?php echo (int)$guide['id']; ?>, '<?php echo addslashes($guideName); ?>')" style="background: white; color: #991b1b; border: 1px solid #fecaca; padding: 0.7rem 1rem; border-radius: 0.45rem; cursor: pointer; font-weight: 800;">Delete</button>
                            </div>
                        </form>
                        <form method="POST" id="delete-guide-form-<?php echo (int)$guide['id']; ?>" style="display: none;">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="user_id" value="<?php echo (int)$guide['id']; ?>">
                            <input type="hidden" name="delete_guide" value="1">
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
async function handleDeleteGuide(id, name) {
    const confirmed = await showCustomConfirm(
        'Delete Tour Guide',
        `Are you sure you want to delete guide "${name}"? Reassign bookings first if this guide is active.`
    );
    if (confirmed) {
        document.getElementById('delete-guide-form-' + id).submit();
    }
}

function showCustomConfirm(title, message, confirmText = 'Yes, Delete') {
    return new Promise((resolve) => {
        const modal = document.getElementById('confirmModal');
        const titleEl = document.getElementById('confirmTitle');
        const msgEl = document.getElementById('confirmMessage');
        const proceedBtn = document.getElementById('confirmProceed');
        const cancelBtn = document.getElementById('confirmCancel');

        titleEl.textContent = title;
        msgEl.textContent = message;
        proceedBtn.textContent = confirmText;
        modal.style.display = 'flex';

        const handleProceed = () => {
            modal.style.display = 'none';
            cleanup();
            resolve(true);
        };
        const handleCancel = () => {
            modal.style.display = 'none';
            cleanup();
            resolve(false);
        };
        const cleanup = () => {
            proceedBtn.removeEventListener('click', handleProceed);
            cancelBtn.removeEventListener('click', handleCancel);
        };

        proceedBtn.addEventListener('click', handleProceed);
        cancelBtn.addEventListener('click', handleCancel);
    });
}
</script>

<div id="confirmModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 400px; text-align: center; padding: 2rem;">
        <div style="width: 60px; height: 60px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
            <i class="fa-solid fa-triangle-exclamation" style="color: #ef4444; font-size: 1.5rem;"></i>
        </div>
        <h2 id="confirmTitle" style="font-size: 1.5rem; font-weight: 700; color: var(--color-stone-900); margin-bottom: 0.5rem;">Are you sure?</h2>
        <p id="confirmMessage" style="color: var(--color-stone-500); margin-bottom: 2rem; line-height: 1.5;">This action cannot be undone. Do you really want to proceed?</p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <button id="confirmCancel" style="padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--color-stone-200); background: white; color: var(--color-stone-600); font-weight: 600; cursor: pointer;">Cancel</button>
            <button id="confirmProceed" style="padding: 0.75rem; border-radius: 0.5rem; border: none; background: #ef4444; color: white; font-weight: 600; cursor: pointer;">Yes, Delete</button>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
