<?php
// mark_read.php - Mark messages as read when viewed by seller
session_start();
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
$buyer_id = isset($_GET['buyer_id']) ? (int) $_GET['buyer_id'] : 0;

if (!$listing_id || !$buyer_id) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

try {
    // Verify that this user is the seller of the listing
    $stmt = $pdo->prepare("SELECT user_id FROM listings WHERE id = ?");
    $stmt->execute([$listing_id]);
    $listing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$listing || $listing['user_id'] != $current_user_id) {
        echo json_encode(['error' => 'Not authorized']);
        exit;
    }

    // Mark messages from this buyer as read
    $stmt = $pdo->prepare("UPDATE chat_messages 
                          SET read_status = 1 
                          WHERE listing_id = ? 
                          AND sender_id = ? 
                          AND receiver_id = ? 
                          AND read_status = 0");
    $stmt->execute([$listing_id, $buyer_id, $current_user_id]);

    echo json_encode([
        'success' => true,
        'messages_marked_read' => $stmt->rowCount()
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>