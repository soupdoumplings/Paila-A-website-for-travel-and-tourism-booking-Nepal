<?php
session_start();
require_once '../config/db.php';
require_once '../helpers/functions.php';
require_login();

$tour = null;
$title = "Add New Tour";
$action = "create";

// Check edit mode
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM tours WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $tour = $stmt->fetch();
    if ($tour) {
        $title = "Edit Tour: " . e($tour['title']);
        $action = "update";
        
        // Check ownership
        if (!is_super_admin()) {
            // Verify creator
            if (isset($tour['created_by']) && $tour['created_by'] != $_SESSION['user_id']) {
                 die("Access Denied: You can only edit tours you created.");
            }
            // Legacy check
            if (!isset($tour['created_by']) || $tour['created_by'] === null) {
                die("Access Denied: You cannot edit this tour (System/Legacy Tour).");
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
    <title><?php echo $title; ?> | Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
</head>
<body class="page-body">

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
                <span style="opacity: 0.7; text-transform: uppercase; letter-spacing: 0.1em; font-size: 0.8rem; color: var(--color-stone-500);">Inventory Management</span>
            </div>
            <h1 style="font-size: 3.5rem; font-family: var(--font-serif); color: var(--color-stone-900); margin: 0;"><?php echo $title; ?></h1>
        </div>
    </section>
    </div>

    <div class="admin-container" style="margin-top: -2rem; position: relative; z-index: 10; padding-bottom: 5rem;">
        <div class="form-header">
            <div class="form-subtitle">
                <?php echo $tour ? 'Update package details and visibility' : 'Create a new adventure for your collection'; ?>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation alert-icon"></i>
                    <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']); 
                    ?>
                </div>
            <?php endif; ?>

            <?php if ($tour): ?>
            <a href="../public/package_detail/?id=<?php echo (int)$tour['id']; ?>" target="_blank" class="view-live-link">
                <i class="fa-solid fa-eye"></i> View Live Page
            </a>
            <?php endif; ?>
        </div>

        <form action="process_tour.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?php echo $action; ?>">
            <?php if($tour): ?>
                <input type="hidden" name="id" value="<?php echo $tour['id']; ?>">
                <input type="hidden" name="existing_image" value="<?php echo $tour['image']; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label">Tour Title</label>
                <input type="text" name="title" class="form-input" value="<?php echo $tour ? e($tour['title']) : ''; ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group form-col">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-input" value="<?php echo $tour ? e($tour['location']) : ''; ?>" required>
                </div>
                <div class="form-group form-col">
                    <label class="form-label">Duration</label>
                    <input type="text" name="duration" class="form-input" placeholder="e.g. 5 Days" value="<?php echo $tour ? e($tour['duration']) : ''; ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group form-col">
                    <label class="form-label">Price (NPR)</label>
                    <input type="number" name="price" class="form-input" step="0.01" value="<?php echo $tour ? e($tour['price']) : ''; ?>" required>
                </div>
                <div class="form-group form-col">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-input">
                        <option value="">—</option>
                        <option value="trekking" <?php echo ($tour && ($tour['category'] ?? '') === 'trekking') ? 'selected' : ''; ?>>Trekking</option>
                        <option value="cultural" <?php echo ($tour && ($tour['category'] ?? '') === 'cultural') ? 'selected' : ''; ?>>Cultural</option>
                        <option value="adventure" <?php echo ($tour && ($tour['category'] ?? '') === 'adventure') ? 'selected' : ''; ?>>Adventure</option>
                        <option value="wellness" <?php echo ($tour && ($tour['category'] ?? '') === 'wellness') ? 'selected' : ''; ?>>Wellness</option>
                        <option value="family" <?php echo ($tour && ($tour['category'] ?? '') === 'family') ? 'selected' : ''; ?>>Family</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group form-col">
                    <label class="form-label">Difficulty</label>
                    <input type="text" name="difficulty" class="form-input" placeholder="e.g. Easy, Moderate, Challenging" value="<?php echo $tour ? e($tour['difficulty'] ?? '') : ''; ?>">
                </div>
                <div class="form-group form-col">
                    <label class="form-label">Max Group</label>
                    <input type="text" name="max_group" class="form-input" placeholder="e.g. 12 people" value="<?php echo $tour ? e($tour['max_group'] ?? '') : ''; ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-textarea" required><?php echo $tour ? e($tour['description']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Experience Highlights</label>
                <textarea name="highlights" class="form-textarea" placeholder="One per line, e.g. Stand at Everest Base Camp, Visit Tengboche Monastery"><?php echo $tour ? e($tour['highlights'] ?? '') : ''; ?></textarea>
                <small class="help-text">One highlight per line.</small>
            </div>

            <div class="form-group">
                <label class="form-label">Tour Image</label>
                <?php if($tour && $tour['image']): ?>
                    <?php 
                        $previewImg = $tour['image'];
                        $displayImg = filter_var($previewImg, FILTER_VALIDATE_URL) ? $previewImg : "../public/uploads/" . $previewImg;
                    ?>
                    <img src="<?php echo e($displayImg); ?>" class="image-preview">
                <?php endif; ?>
                <input type="file" name="image" class="form-input" accept="image/*">
                
                <div class="url-input-container">
                    <label class="url-label">Or Image URL</label>
                    <input type="url" name="image_url" class="form-input" placeholder="https://example.com/image.jpg" value="">
                </div>

                <small class="help-text">Leave empty to keep existing image (if editing).</small>
            </div>

            <div class="form-group featured-box">
                <label class="featured-label">
                    <input type="checkbox" name="is_featured" value="1" <?php echo ($tour && ($tour['is_featured'] ?? 0) == 1) ? 'checked' : ''; ?> class="featured-checkbox">
                    <span>Feature this tour on homepage</span>
                </label>
                <p class="featured-description">
                    Featured tours are prioritized in the collection and shown in the main website's hero gallery (max 6).
                </p>
            </div>

            <button type="submit" class="btn btn-primary submit-btn-container">
                <?php echo $action === 'create' ? 'Create Tour' : 'Update Tour'; ?>
            </button>
        </form>
    </div>

</body>
</html>
