<?php
session_start();
require_once '../config/db.php';
require_once '../helpers/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' || (isset($_GET['action']) && $_GET['action'] === 'delete')) {
    
    // Process tour delete
    if (isset($_GET['action']) && $_GET['action'] === 'delete') {
        $id = $_GET['id'];
        
        // Verify edit rights
        if (!is_super_admin()) {
            $check = $pdo->prepare("SELECT created_by FROM tours WHERE id = ?");
            $check->execute([$id]);
            $owner = $check->fetchColumn();
            if ($owner && $owner != $_SESSION['user_id']) {
                die("Access Denied: You can only delete tours you created.");
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM tours WHERE id = :id");
        $stmt->execute(['id' => $id]);
        redirect('index.php');
    }

    $action = $_POST['action'];
    $title = $_POST['title'];
    $location = $_POST['location'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    $description = $_POST['description'];
    $difficulty = isset($_POST['difficulty']) ? trim($_POST['difficulty']) : null;
    $max_group = isset($_POST['max_group']) ? trim($_POST['max_group']) : null;
    $highlights = isset($_POST['highlights']) ? trim($_POST['highlights']) : null;
    $category = isset($_POST['category']) ? trim($_POST['category']) : null;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    // Process uploaded image
    $imagePath = null;
    if (isset($_POST['existing_image'])) {
        $imagePath = $_POST['existing_image'];
    }

    // Use image URL
    if (isset($_POST['image_url']) && !empty(trim($_POST['image_url']))) {
        $imagePath = trim($_POST['image_url']);
    }

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['name'] !== '') {
        if ($_FILES['image']['error'] === 0) {
            $uploadDir = '../public/uploads/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    $_SESSION['error'] = "Failed to create upload directory: $uploadDir";
                    error_log("Upload Error: Failed to create $uploadDir");
                }
            }

            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $imagePath = $fileName;
                } else {
                    $perm = substr(sprintf('%o', fileperms($uploadDir)), -4);
                    $_SESSION['error'] = "Failed to move file to $uploadDir. (Permissions: $perm). Check if the folder is writable.";
                    error_log("Upload Error: move_uploaded_file failed. Perms: $perm");
                    
                    $redirectBack = "tour_form.php" . ($action === 'update' ? "?id=" . $_POST['id'] : "");
                    redirect($redirectBack);
                }
            } else {
                $_SESSION['error'] = "Invalid file type: $ext. Only JPG, PNG, and WEBP are allowed.";
                $redirectBack = "tour_form.php" . ($action === 'update' ? "?id=" . $_POST['id'] : "");
                redirect($redirectBack);
            }
        } else {
            $redirectBack = "tour_form.php" . ($action === 'update' ? "?id=" . $_POST['id'] : "");
            redirect($redirectBack);
        }
    }

    if ($action === 'create') {
        try {
            $stmt = $pdo->prepare("INSERT INTO tours (title, location, price, duration, description, image, difficulty, max_group, highlights, category, is_featured, created_by) VALUES (:title, :location, :price, :duration, :description, :image, :difficulty, :max_group, :highlights, :category, :is_featured, :created_by)");
            $stmt->execute([
                'title' => $title, 'location' => $location, 'price' => $price, 'duration' => $duration,
                'description' => $description, 'image' => $imagePath,
                'difficulty' => $difficulty, 'max_group' => $max_group, 'highlights' => $highlights, 'category' => $category,
                'is_featured' => $is_featured,
                'created_by' => $_SESSION['user_id']
            ]);
        } catch (PDOException $e) {
            // Handle legacy schema
             if (strpos($e->getMessage(), 'Unknown column') !== false) {
                 $stmt = $pdo->prepare("INSERT INTO tours (title, location, price, duration, description, image, difficulty, max_group, highlights, category) VALUES (:title, :location, :price, :duration, :description, :image, :difficulty, :max_group, :highlights, :category)");
                 $stmt->execute([
                    'title' => $title, 'location' => $location, 'price' => $price, 'duration' => $duration,
                    'description' => $description, 'image' => $imagePath,
                    'difficulty' => $difficulty, 'max_group' => $max_group, 'highlights' => $highlights, 'category' => $category
                ]);
            } else {
                throw $e;
            }
        }
        $newId = (int) $pdo->lastInsertId();
        redirect('../public/package_detail/?id=' . $newId);
    } elseif ($action === 'update') {
        $id = (int) $_POST['id'];
        
        // Verify edit rights
        if (!is_super_admin()) {
            $check = $pdo->prepare("SELECT created_by FROM tours WHERE id = ?");
            $check->execute([$id]);
            $owner = $check->fetchColumn();
            if ($owner && $owner != $_SESSION['user_id']) {
                die("Access Denied: You can only edit tours you created.");
            }
        }

        try {
            $stmt = $pdo->prepare("UPDATE tours SET title = :title, location = :location, price = :price, duration = :duration, description = :description, image = :image, difficulty = :difficulty, max_group = :max_group, highlights = :highlights, category = :category, is_featured = :is_featured WHERE id = :id");
            $stmt->execute([
                'title' => $title, 'location' => $location, 'price' => $price, 'duration' => $duration,
                'description' => $description, 'image' => $imagePath,
                'difficulty' => $difficulty, 'max_group' => $max_group, 'highlights' => $highlights, 'category' => $category,
                'is_featured' => $is_featured, 'id' => $id
            ]);
        } catch (PDOException $e) {
             if (strpos($e->getMessage(), 'Unknown column') !== false) {
                   $stmt = $pdo->prepare("UPDATE tours SET title = :title, location = :location, price = :price, duration = :duration, description = :description, image = :image WHERE id = :id");
                    $stmt->execute([
                    'title' => $title, 'location' => $location, 'price' => $price, 'duration' => $duration,
                    'description' => $description, 'image' => $imagePath, 'id' => $id
                  ]);
             } else {
                 throw $e;
             }
        }
        redirect('../public/package_detail/?id=' . $id);
    }
} else {
    redirect('index.php');
}
?>
