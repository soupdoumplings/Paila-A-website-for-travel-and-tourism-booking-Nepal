<?php
// Message helper functions

// Include database configuration
require_once __DIR__ . '/../config/db.php';



// Send new message
function send_message($sender_id, $receiver_id, $context_type, $context_id, $message) {
    global $pdo;
    
    // Validate message content
    if (empty(trim($message))) return false;
    
    try {
        // Prepare insert query
        $stmt = $pdo->prepare("
            INSERT INTO messages (sender_id, receiver_id, context_type, context_id, message) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        // Execute message insertion
        return $stmt->execute([
            $sender_id, 
            $receiver_id, 
            $context_type, 
            $context_id, 
            htmlspecialchars($message)
        ]);
        
    } catch (PDOException $e) {
        // Handle database errors
        return false;
    }
}

// Fetch message history
function get_message_history($context_type, $context_id, $user_id = null) {
    global $pdo;
    

    $sql = "SELECT m.*, s.username as sender_name, s.role_id as sender_role_id 
            FROM messages m 
            JOIN users s ON m.sender_id = s.id 
            WHERE m.context_type = ? AND m.context_id = ?";
    

    $params = [$context_type, $context_id];
    

    $sql .= " ORDER BY m.created_at ASC";
    
    // Execute history search
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Mark messages read
function mark_messages_read($context_type, $context_id, $user_id) {
    global $pdo;
    

    $stmt = $pdo->prepare("
        UPDATE messages 
        SET is_read = 1 
        WHERE context_type = ? AND context_id = ? AND receiver_id = ? AND is_read = 0
    ");
    
    return $stmt->execute([$context_type, $context_id, $user_id]);
}
