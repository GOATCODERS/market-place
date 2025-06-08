<?php
ob_start();
// chat.php - Chat interface for messaging sellers
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['user_id']) && $_GET['user_id'] == $_SESSION['user_id']) {
    // Store message in session instead of sending raw JSON
    $_SESSION['buy_message_error'] = json_encode([
        'message' => 'You cant buy what you are selling'
    ]);

    // Redirect
    header('Location: Categories.php');
    exit;
}
// Get current user ID
$current_user_id = $_SESSION['user_id'];

// Include header
include_once '../includes/header.php';
?>

<link rel="stylesheet" href="../public/css/chat.css">

<div class="container mt-4 mb-4">
    <h1>Messages</h1>

    <div class="chat-container">
        <!-- Chat Sidebar - List of conversations -->
        <div class="chat-sidebar">
            <div class="chat-sidebar-header">
                My Conversations
            </div>
            <div class="chat-list" id="chat-list">
                <div class="text-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Box - Actual conversation -->
        <div class="chat-box" id="chat-box">
            <div class="chat-header" id="chat-header">
                <div class="back-button" onclick="backToChatList()">←</div>
                <div class="chat-header-content">
                    <div class="chat-header-title">Select a conversation</div>
                </div>
            </div>

            <div class="message-container" id="message-container">
                <div class="text-center p-5">
                    <p>Select a conversation from the left to start messaging</p>
                </div>
            </div>

            <form class="message-form" id="message-form">
                <textarea class="message-input" id="message-input" placeholder="Type your message..."
                    rows="1"></textarea>
                <button type="submit" class="send-button">Send</button>
            </form>
        </div>
    </div>
</div>

<script src="../public/js/chat.js"></script>

<?php
// Include footer
include_once '../includes/footer.php';
?>