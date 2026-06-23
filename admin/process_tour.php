<?php
require_once __DIR__ . '/../helpers/security.php';
secure_session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/functions.php';
require_login();
require_post_request();
require_csrf_token();

function tour_form_url($action, $id = 0) {
    return 'tour_form.php' . ($action === 'update' && $id > 0 ? '?id=' . (int) $id : '');
}

function fail_tour_form($message, $action, $id = 0) {
    $_SESSION['error'] = $message;
    redirect(tour_form_url($action, $id));
}

function optional_text($key) {
    $value = trim($_POST[$key] ?? '');
    return $value === '' ? null : $value;
}

function optional_int($key) {
    $value = trim($_POST[$key] ?? '');
    if ($value === '') {
        return null;
    }
    return max(0, (int) $value);
}

function current_admin_id() {
    return $_SESSION['admin_id'] ?? ($_SESSION['user_id'] ?? null);
}

function can_manage_tour($pdo, $id) {
    if (is_super_admin()) {
        return true;
    }

    $check = $pdo->prepare('SELECT created_by FROM tours WHERE id = ?');
    $check->execute([$id]);
    $owner = $check->fetchColumn();

    return $owner && isset($_SESSION['user_id']) && (int) $owner === (int) $_SESSION['user_id'];
}

$action = $_POST['action'] ?? '';
$id = (int) ($_POST['id'] ?? 0);

if ($action === 'delete') {
    if ($id <= 0 || !can_manage_tour($pdo, $id)) {
        die('Access Denied: You can only delete tours you created.');
    }

    try {
        $stmt = $pdo->prepare('DELETE FROM tours WHERE id = :id');
        $stmt->execute(['id' => $id]);
    } catch (PDOException $e) {
        $_SESSION['error'] = 'This tour has bookings attached. Cancel or move those bookings before deleting it.';
    }

    redirect('index.php');
}

if (!in_array($action, ['create', 'update'], true)) {
    redirect('index.php');
}

$title = trim($_POST['title'] ?? '');
$location = trim($_POST['location'] ?? '');
$price = trim($_POST['price'] ?? '');
$duration = trim($_POST['duration'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($title === '' || $location === '' || $price === '' || $duration === '' || $description === '') {
    fail_tour_form('Please complete all required package fields.', $action, $id);
}

if (!is_numeric($price) || (float) $price < 0) {
    fail_tour_form('Package price must be a valid positive number.', $action, $id);
}

if ($action === 'update' && ($id <= 0 || !can_manage_tour($pdo, $id))) {
    die('Access Denied: You can only edit tours you created.');
}

$difficulty = optional_text('difficulty');
$max_group = optional_int('max_group');
$highlights = optional_text('highlights');
$category = optional_text('category');
$best_season = optional_text('best_season');
$altitude_max = optional_int('altitude_max');
$permit_requirements = optional_text('permit_requirements');
$itinerary = optional_text('itinerary');
$inclusions = optional_text('inclusions');
$exclusions = optional_text('exclusions');
$is_featured = isset($_POST['is_featured']) ? 1 : 0;

$imagePath = trim($_POST['existing_image'] ?? '');
$imageUrl = trim($_POST['image_url'] ?? '');

if ($imageUrl !== '') {
    if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
        fail_tour_form('Please enter a valid image URL, or leave the URL field empty.', $action, $id);
    }
    $imagePath = $imageUrl;
}

$uploadError = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
if ($uploadError !== UPLOAD_ERR_NO_FILE) {
    if ($uploadError !== UPLOAD_ERR_OK) {
        fail_tour_form('Image upload failed. Please choose a smaller JPG, PNG, WEBP, or AVIF file and try again.', $action, $id);
    }

    $tmpPath = $_FILES['image']['tmp_name'] ?? '';
    if (!$tmpPath || !is_uploaded_file($tmpPath)) {
        fail_tour_form('Image upload could not be verified. Please try again.', $action, $id);
    }

    $uploadDir = __DIR__ . '/../public/uploads/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        fail_tour_form('Could not create the upload directory. Please check folder permissions.', $action, $id);
    }

    if (!is_writable($uploadDir)) {
        fail_tour_form('The upload directory is not writable. Please check folder permissions.', $action, $id);
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($tmpPath);
    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/avif' => 'avif',
    ];

    if (!isset($allowedTypes[$mimeType])) {
        fail_tour_form('Invalid image type. Only JPG, PNG, WEBP, and AVIF images are allowed.', $action, $id);
    }

    $originalName = pathinfo($_FILES['image']['name'] ?? 'tour', PATHINFO_FILENAME);
    $safeName = strtolower(preg_replace('/[^a-zA-Z0-9_-]+/', '-', $originalName));
    $safeName = trim($safeName, '-');
    if ($safeName === '') {
        $safeName = 'tour';
    }

    $fileName = time() . '_' . bin2hex(random_bytes(4)) . '_' . $safeName . '.' . $allowedTypes[$mimeType];
    $targetPath = $uploadDir . $fileName;

    if (!move_uploaded_file($tmpPath, $targetPath)) {
        fail_tour_form('Failed to save the uploaded image. Please check folder permissions and try again.', $action, $id);
    }

    $imagePath = $fileName;
}

$tourData = [
    'title' => $title,
    'location' => $location,
    'price' => (float) $price,
    'duration' => $duration,
    'description' => $description,
    'category' => $category,
    'difficulty' => $difficulty,
    'max_group' => $max_group,
    'highlights' => $highlights,
    'image' => $imagePath === '' ? null : $imagePath,
    'best_season' => $best_season,
    'altitude_max' => $altitude_max,
    'permit_requirements' => $permit_requirements,
    'itinerary' => $itinerary,
    'inclusions' => $inclusions,
    'exclusions' => $exclusions,
    'is_featured' => $is_featured,
];

if ($action === 'create') {
    $stmt = $pdo->prepare("
        INSERT INTO tours (
            title, location, price, duration, description, category, difficulty,
            max_group, highlights, image, best_season, altitude_max,
            permit_requirements, itinerary, inclusions, exclusions, is_featured, created_by
        ) VALUES (
            :title, :location, :price, :duration, :description, :category, :difficulty,
            :max_group, :highlights, :image, :best_season, :altitude_max,
            :permit_requirements, :itinerary, :inclusions, :exclusions, :is_featured, :created_by
        )
    ");

    $stmt->execute($tourData + ['created_by' => current_admin_id()]);
    redirect('../public/package_detail/?id=' . (int) $pdo->lastInsertId());
}

$stmt = $pdo->prepare("
    UPDATE tours SET
        title = :title,
        location = :location,
        price = :price,
        duration = :duration,
        description = :description,
        category = :category,
        difficulty = :difficulty,
        max_group = :max_group,
        highlights = :highlights,
        image = :image,
        best_season = :best_season,
        altitude_max = :altitude_max,
        permit_requirements = :permit_requirements,
        itinerary = :itinerary,
        inclusions = :inclusions,
        exclusions = :exclusions,
        is_featured = :is_featured
    WHERE id = :id
");

$stmt->execute($tourData + ['id' => $id]);
redirect('../public/package_detail/?id=' . $id);
?>
