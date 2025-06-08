<?php
ob_start();
session_start(); // Ensure session starts at the very top
require_once '../includes/database/connection.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$current_user_id = $_SESSION['user_id'];

// Get parameters
$listing_id = isset($_GET['listing_id']) ? (int) $_GET['listing_id'] : 0;
$other_user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;

if (!$listing_id || !$other_user_id) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

try {
    // Check if chat session exists or create it
    $stmt = $pdo->prepare("SELECT id FROM chat_sessions WHERE listing_id = ? AND 
                          ((buyer_id = ? AND seller_id = ?) OR (buyer_id = ? AND seller_id = ?))");
    $stmt->execute([$listing_id, $current_user_id, $other_user_id, $other_user_id, $current_user_id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        // Get listing owner info
        $stmt = $pdo->prepare("SELECT user_id FROM listings WHERE id = ?");
        $stmt->execute([$listing_id]);
        $listing = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$listing) {
            echo json_encode(['error' => 'Listing not found']);
            exit;
        }

        $seller_id = $listing['user_id'];
        $buyer_id = ($current_user_id == $seller_id) ? $other_user_id : $current_user_id;

        // Create new chat session
        $stmt = $pdo->prepare("INSERT INTO chat_sessions (listing_id, buyer_id, seller_id) VALUES (?, ?, ?)");
        $stmt->execute([$listing_id, $buyer_id, $seller_id]);
    }

    // Fetch messages
    $stmt = $pdo->prepare("SELECT cm.*, u.name as sender_name FROM chat_messages cm
                           JOIN Users u ON cm.sender_id = u.id
                           WHERE cm.listing_id = ? AND 
                           ((cm.sender_id = ? AND cm.receiver_id = ?) OR 
                            (cm.sender_id = ? AND cm.receiver_id = ?))
                           ORDER BY cm.created_at ASC");
    $stmt->execute([$listing_id, $current_user_id, $other_user_id, $other_user_id, $current_user_id]);

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mark messages as read
    $stmt = $pdo->prepare("UPDATE chat_messages SET read_status = 1 
                           WHERE listing_id = ? AND sender_id = ? AND receiver_id = ? AND read_status = 0");
    $stmt->execute([$listing_id, $other_user_id, $current_user_id]);

    // Get listing details
    $stmt = $pdo->prepare("SELECT l.item_name, l.price, u.name as seller_name 
                           FROM listings l JOIN Users u ON l.user_id = u.id 
                           WHERE l.id = ?");
    $stmt->execute([$listing_id]);
    $listing_details = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'messages' => $messages,
        'listing' => $listing_details
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
