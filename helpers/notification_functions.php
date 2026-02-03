<?php
// Notification helper functions

// Include database configuration
require_once __DIR__ . '/../config/db.php';



// Create new notification
function create_notification($recipient_id, $title, $message, $link = null) {
    global $pdo;
    
    try {
// Prepare notification insert
        $stmt = $pdo->prepare("INSERT INTO notifications (recipient_id, title, message, link) VALUES (?, ?, ?, ?)");
        
// Execute notification insert
        return $stmt->execute([$recipient_id, $title, $message, $link]);
        
    } catch (PDOException $e) {
// Handle notification errors
        return false;
    }
}



// Fetch user notifications
function get_user_notifications($user_id, $limit = 50) {
    global $pdo;
    
// Prepare notification retrieval
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE recipient_id = ? ORDER BY created_at DESC LIMIT ?");
    
// Bind query parameters
    $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
    $stmt->bindParam(2, $limit, PDO::PARAM_INT);
    
    $stmt->execute();
    
// Return notification results
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



// Get unread count
function get_unread_notification_count($user_id) {
    global $pdo;
    
// Count unread records
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE recipient_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    
// Return unread count
    return $stmt->fetchColumn();
}

// Mark single read
function mark_notification_read($id, $user_id) {
    global $pdo;
    
// Update read status
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND recipient_id = ?");
    
    return $stmt->execute([$id, $user_id]);
}

// Mark all read
function mark_all_notifications_read($user_id) {
    global $pdo;
    
// Update all status
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE recipient_id = ?");
    
    return $stmt->execute([$user_id]);
}
