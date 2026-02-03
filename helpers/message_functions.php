<?php
// Core helper logic

// Include project dependencies
require_once __DIR__ . '/../config/db.php';

// Database interaction logic

// Database interaction logic
function send_message($sender_id, $receiver_id, $context_type, $context_id, $message) {
    global $pdo;
    
    // Security and validation
    if (empty(trim($message))) return false;
    
    try {
        // Database interaction logic
        $stmt = $pdo->prepare("
            INSERT INTO messages (sender_id, receiver_id, context_type, context_id, message) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        // Database interaction logic
        return $stmt->execute([
            $sender_id, 
            $receiver_id, 
            $context_type, 
            $context_id, 
            htmlspecialchars($message)
        ]);
        
    } catch (PDOException $e) {
        // Database interaction logic
        return false;
    }
}

// Database interaction logic

// Database interaction logic
function get_message_history($context_type, $context_id, $user_id = null) {
    global $pdo;
    
// Database interaction logic
    $sql = "SELECT m.*, s.username as sender_name, s.role_id as sender_role_id 
            FROM messages m 
            JOIN users s ON m.sender_id = s.id 
            WHERE m.context_type = ? AND m.context_id = ?";
    
// Database interaction logic
    $params = [$context_type, $context_id];
    
// Database interaction logic
    $sql .= " ORDER BY m.created_at ASC";
    
    // Execute retrieval query and return message history
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
// Database interaction logic
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Database interaction logic

// Database interaction logic
function mark_messages_read($context_type, $context_id, $user_id) {
    global $pdo;
    
// Database interaction logic
    $stmt = $pdo->prepare("
        UPDATE messages 
        SET is_read = 1 
        WHERE context_type = ? AND context_id = ? AND receiver_id = ? AND is_read = 0
    ");
    
    return $stmt->execute([$context_type, $context_id, $user_id]);
}
