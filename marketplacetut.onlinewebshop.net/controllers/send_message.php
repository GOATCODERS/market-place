<?php
ob_start();
// send_message.php - Send a chat message
session_start();
require_once '../includes/database/connection.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$current_user_id = $_SESSION['user_id'];

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$listing_id = isset($data['listing_id']) ? (int) $data['listing_id'] : 0;
$receiver_id = isset($data['receiver_id']) ? (int) $data['receiver_id'] : 0;
$message = isset($data['message']) ? trim($data['message']) : '';

if (!$listing_id || !$receiver_id || empty($message)) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

try {
    // Get listing owner info
    $stmt = $pdo->prepare("SELECT user_id FROM listings WHERE id = ?");
    $stmt->execute([$listing_id]);
    $listing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$listing) {
        echo json_encode(['error' => 'Listing not found']);
        exit;
    }

    // Verify that one of the users is the listing owner
    $seller_id = $listing['user_id'];
    if ($current_user_id != $seller_id && $receiver_id != $seller_id) {
        echo json_encode(['error' => 'Invalid chat participants']);
        exit;
    }

    // Insert message
    $stmt = $pdo->prepare("INSERT INTO chat_messages (sender_id, receiver_id, listing_id, message) 
                          VALUES (?, ?, ?, ?)");
    $stmt->execute([$current_user_id, $receiver_id, $listing_id, $message]);

    // Update chat session last message timestamp
    $buyer_id = ($current_user_id == $seller_id) ? $receiver_id : $current_user_id;

    $stmt = $pdo->prepare("UPDATE chat_sessions SET last_message_at = CURRENT_TIMESTAMP 
                          WHERE listing_id = ? AND buyer_id = ? AND seller_id = ?");
    $stmt->execute([$listing_id, $buyer_id, $seller_id]);

    // Get the username of the current user
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$current_user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => [
            'id' => $pdo->lastInsertId(),
            'sender_id' => $current_user_id,
            'receiver_id' => $receiver_id,
            'listing_id' => $listing_id,
            'message' => $message,
            'sender_name' => $user['name'],
            'created_at' => date('Y-m-d H:i:s')
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>