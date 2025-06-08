<?php
ob_start();
// get_user_chats.php - Get all chat sessions for the current user
session_start();
require_once '../includes/database/connection.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$current_user_id = $_SESSION['user_id'];

try {
    // Get all chat sessions where current user is involved
    $stmt = $pdo->prepare("
        SELECT cs.*, 
               l.item_name, l.price,
               CASE 
                   WHEN cs.seller_id = ? THEN cs.buyer_id
                   ELSE cs.seller_id
               END as other_user_id,
               u.uname as other_user_name,
               (SELECT COUNT(*) FROM chat_messages 
                WHERE listing_id = cs.listing_id 
                AND receiver_id = ? 
                AND read_status = 0) as unread_count,
               (SELECT message FROM chat_messages 
                WHERE (sender_id = cs.buyer_id AND receiver_id = cs.seller_id) 
                OR (sender_id = cs.seller_id AND receiver_id = cs.buyer_id)
                AND listing_id = cs.listing_id
                ORDER BY created_at DESC LIMIT 1) as last_message
        FROM chat_sessions cs
        JOIN listings l ON cs.listing_id = l.id
        JOIN users u ON (CASE WHEN cs.seller_id = ? THEN cs.buyer_id ELSE cs.seller_id END) = u.id
        WHERE cs.buyer_id = ? OR cs.seller_id = ?
        ORDER BY cs.last_message_at DESC
    ");
    $stmt->execute([$current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id]);

    $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['chats' => $chats]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>