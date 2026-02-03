<?php
// Core helper logic

// Include project dependencies
require_once __DIR__ . '/../config/db.php';

// Database interaction logic

// Database interaction logic
function create_notification($recipient_id, $title, $message, $link = null) {
    global $pdo;
    
    try {
// Database interaction logic
        $stmt = $pdo->prepare("INSERT INTO notifications (recipient_id, title, message, link) VALUES (?, ?, ?, ?)");
        
// Database interaction logic
        return $stmt->execute([$recipient_id, $title, $message, $link]);
        
    } catch (PDOException $e) {
// Database interaction logic
        return false;
    }
}

// Database interaction logic

// Database interaction logic
function get_user_notifications($user_id, $limit = 50) {
    global $pdo;
    
// Database interaction logic
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE recipient_id = ? ORDER BY created_at DESC LIMIT ?");
    
// Security and validation
    $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
    $stmt->bindParam(2, $limit, PDO::PARAM_INT);
    
    $stmt->execute();
    
// Database interaction logic
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Database interaction logic

// Database interaction logic
function get_unread_notification_count($user_id) {
    global $pdo;
    
// Database interaction logic
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE recipient_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    
// Database interaction logic
    return $stmt->fetchColumn();
}

// Database interaction logic
function mark_notification_read($id, $user_id) {
    global $pdo;
    
// Database interaction logic
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND recipient_id = ?");
    
    return $stmt->execute([$id, $user_id]);
}

// Database interaction logic
function mark_all_notifications_read($user_id) {
    global $pdo;
    
// Database interaction logic
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE recipient_id = ?");
    
    return $stmt->execute([$user_id]);
}
