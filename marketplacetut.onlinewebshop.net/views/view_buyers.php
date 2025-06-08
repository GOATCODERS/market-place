<?php
ob_start();
// view_buyers.php - View all buyers who have contacted about a specific listing
session_start();
require_once '../includes/database/connection.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$listing_id = isset($_GET['listing_id']) ? (int) $_GET['listing_id'] : 0;

if (!$listing_id) {
    header('Location: seller_dashboard.php');
    exit;
}

// Verify that this listing belongs to the current user
try {
    $stmt = $pdo->prepare("SELECT * FROM listings WHERE id = ? AND user_id = ?");
    $stmt->execute([$listing_id, $user_id]);
    $listing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$listing) {
        // Not the owner of this listing
        header('Location: seller_dashboard.php');
        exit;
    }

    // Get all buyers who have contacted about this listing
    $stmt = $pdo->prepare("
    SELECT cs.buyer_id, u.name, u.email,
           (
               SELECT MAX(created_at) 
               FROM chat_messages 
               WHERE (
                   (sender_id = cs.buyer_id AND receiver_id = cs.seller_id) 
                   OR 
                   (sender_id = cs.seller_id AND receiver_id = cs.buyer_id)
               )
               AND listing_id = cs.listing_id
           ) AS last_message_time,
           (
               SELECT COUNT(*) 
               FROM chat_messages 
               WHERE sender_id = cs.buyer_id 
               AND receiver_id = cs.seller_id 
               AND listing_id = cs.listing_id 
               AND read_status = 0
           ) AS unread_count,
           (
               SELECT message 
               FROM chat_messages 
               WHERE (
                   (sender_id = cs.buyer_id AND receiver_id = cs.seller_id) 
                   OR 
                   (sender_id = cs.seller_id AND receiver_id = cs.buyer_id)
               )
               AND listing_id = cs.listing_id
               ORDER BY created_at DESC 
               LIMIT 1
           ) AS last_message
    FROM chat_sessions cs
    JOIN Users u ON cs.buyer_id = u.id
    WHERE cs.listing_id = ? AND cs.seller_id = ?
    ORDER BY last_message_time DESC
");

    $stmt->execute([$listing_id, $user_id]);
    $buyers = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Include header
include_once '../includes/header.php';
?>

<style>
    :root {
        --primary: #6366f1;
        /* Indigo */
        --primary-dark: #4f46e5;
        --accent: #ec4899;
        /* Pink */
        --light-bg: #f9fafb;
        --border: #e5e7eb;
        --gray-text: #6b7280;
        --dark-text: #1f2937;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background-color: #f3f4f6;
        color: var(--dark-text);
        line-height: 1.5;
    }

    .container {
        max-width: 1024px;
        margin: 0 auto;
        padding: 0 16px;
    }

    .breadcrumb {
        display: flex;
        gap: 8px;
        padding: 12px 0;
        margin-bottom: 20px;
        align-items: center;
        font-size: 0.9rem;
    }

    .breadcrumb a {
        color: var(--primary);
        text-decoration: none;
    }

    .breadcrumb-divider {
        color: var(--gray-text);
    }

    .card {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 24px;
        overflow: hidden;
    }

    .card-header {
        border-bottom: 1px solid var(--border);
        padding: 16px 24px;
    }

    .card-body {
        padding: 24px;
    }

    .card-footer {
        border-top: 1px solid var(--border);
        padding: 16px 24px;
        background-color: var(--light-bg);
    }

    .price-tag {
        background-color: var(--accent);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
    }

    .category-badge {
        background-color: #f3f4f6;
        color: var(--gray-text);
        padding: 4px 12px;
        border-radius: 16px;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        margin-right: 8px;
    }

    .category-badge i {
        margin-right: 4px;
    }

    .button {
        padding: 10px 16px;
        border-radius: 6px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        cursor: pointer;
        font-size: 0.9rem;
    }

    .button-primary {
        background-color: var(--primary);
        color: white;
        border: none;
    }

    .button-primary:hover {
        background-color: var(--primary-dark);
    }

    .button-outline {
        background-color: transparent;
        border: 1px solid var(--primary);
        color: var(--primary);
    }

    .button-outline:hover {
        background-color: rgba(99, 102, 241, 0.05);
    }

    .button-secondary {
        background-color: #f3f4f6;
        color: var(--gray-text);
        border: 1px solid var(--border);
    }

    .button-secondary:hover {
        background-color: #e5e7eb;
    }

    .button i {
        margin-right: 6px;
    }

    .button-group {
        display: flex;
        gap: 12px;
    }

    .listing-details {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .listing-details .detail-row {
        display: flex;
        gap: 8px;
    }

    .listing-details .detail-label {
        font-weight: 600;
        min-width: 100px;
        color: var(--gray-text);
    }

    .listing-image {
        aspect-ratio: 1/1;
        width: 100%;
        object-fit: cover;
        border-radius: 6px;
    }

    .buyer-list {
        display: flex;
        flex-direction: column;
    }

    .buyer-item {
        display: flex;
        padding: 16px;
        border-bottom: 1px solid var(--border);
        text-decoration: none;
        color: inherit;
        transition: background-color 0.2s;
    }

    .buyer-item:hover {
        background-color: var(--light-bg);
    }

    .buyer-item:last-child {
        border-bottom: none;
    }

    .buyer-avatar {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        background-color: var(--primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.2rem;
        margin-right: 16px;
        flex-shrink: 0;
    }

    .buyer-info {
        flex-grow: 1;
        min-width: 0;
        /* For text truncation */
    }

    .buyer-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 4px;
        align-items: center;
    }

    .buyer-name {
        font-weight: 600;
        margin: 0;
        font-size: 1rem;
    }

    .buyer-time {
        font-size: 0.85rem;
        color: var(--gray-text);
    }

    .buyer-message {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: var(--gray-text);
        font-size: 0.9rem;
    }

    .view-text {
        color: var(--primary);
        font-size: 0.85rem;
        margin-top: 4px;
    }

    .unread-badge {
        background-color: var(--accent);
        color: white;
        font-size: 0.75rem;
        padding: 2px 8px;
        border-radius: 12px;
        margin-left: 8px;
    }

    .buyers-count {
        background-color: var(--primary);
        color: white;
        font-size: 0.85rem;
        padding: 4px 12px;
        border-radius: 16px;
    }

    .section-title {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
    }

    .empty-message {
        padding: 40px 0;
        text-align: center;
        color: var(--gray-text);
    }

    .empty-message i {
        font-size: 48px;
        margin-bottom: 16px;
        display: block;
    }

    .flex-between {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .flex {
        display: flex;
        align-items: center;
    }

    .flex-column {
        display: flex;
        flex-direction: column;
    }

    .unread-indicator {
        border-left: 4px solid var(--primary);
        background-color: rgba(99, 102, 241, 0.05);
    }

    @media (max-width: 768px) {
        .listing-grid {
            flex-direction: column;
        }

        .listing-image-container {
            width: 100%;
            margin-bottom: 20px;
        }
    }
</style>

<div class="container">
    <!-- Breadcrumb navigation -->
    <div class="breadcrumb">
        <a href="seller_dashboard.php"><i class="bi bi-house"></i> Dashboard</a>
        <span class="breadcrumb-divider">/</span>
        <span>Buyers for <?php echo htmlspecialchars($listing['item_name']); ?></span>
    </div>

    <!-- Listing details card -->
    <div class="card">
        <div class="card-header flex-between">
            <div class="flex">
                <h2 class="section-title"><?php echo htmlspecialchars($listing['item_name']); ?></h2>
                <span class="price-tag"
                    style="margin-left: 16px;">R<?php echo number_format($listing['price'], 2); ?></span>
            </div>
            <div class="category-badge">
                <i class="bi bi-tag"></i> <?php echo htmlspecialchars($listing['category']); ?>
            </div>
        </div>

        <div class="card-body">
            <div class="listing-grid" style="display: flex; gap: 24px;">
                <!-- Listing image -->
                <div class="listing-image-container" style="width: 35%; flex-shrink: 0;">
                    <?php if ($listing['image']): ?>
                        <img src="data:<?php echo $listing['image_type']; ?>;base64,<?php echo base64_encode($listing['image']); ?>"
                            class="listing-image" alt="<?php echo htmlspecialchars($listing['item_name']); ?>">
                    <?php else: ?>
                        <img src="../public/images/placeholder.jpg" class="listing-image" alt="Placeholder">
                    <?php endif; ?>
                </div>

                <!-- Listing info -->
                <div style="flex-grow: 1;">
                    <div class="listing-details">
                        <div class="detail-row">
                            <span class="detail-label"><i class="bi bi-calendar-date"></i> Listed:</span>
                            <span><?php echo date('F j, Y', strtotime($listing['created_at'])); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><i class="bi bi-stars"></i> Condition:</span>
                            <span><?php echo htmlspecialchars($listing['item_condition']); ?></span>
                        </div>
                        <div class="detail-row" style="align-items: flex-start;">
                            <span class="detail-label"><i class="bi bi-card-text"></i> Description:</span>
                            <span
                                style="line-height: 1.5;"><?php echo htmlspecialchars($listing['description']); ?></span>
                        </div>
                    </div>

                    <div class="button-group" style="margin-top: 24px;">
                            <!-- edit_listing.php?id=<?php echo $listing_id; ?> -->
                     <a href="#" class="button button-outline">
                            <i class="bi bi-pencil"></i> Edit Listing
                         </a> 
                        <a href="seller_dashboard.php" class="button button-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Interested buyers card -->
    <div class="card">
        <div class="card-header flex-between">
            <h3 class="section-title">Interested Buyers</h3>
            <span class="buyers-count"><?php echo count($buyers); ?>
                buyer<?php echo count($buyers) != 1 ? 's' : ''; ?></span>
        </div>

        <?php if (empty($buyers)): ?>
            <div class="card-body">
                <div class="empty-message">
                    <i class="bi bi-chat-left"></i>
                    <h4>No Buyers Yet</h4>
                    <p>You haven't received any messages about this listing yet.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="buyer-list">
                <?php foreach ($buyers as $buyer): ?>
                    <a href="chat.php?listing_id=<?php echo $listing_id; ?>&user_id=<?php echo $buyer['buyer_id']; ?>"
                        class="buyer-item <?php echo $buyer['unread_count'] > 0 ? 'unread-indicator' : ''; ?>">
                        <div class="buyer-avatar">
                            <?php echo strtoupper(substr($buyer['name'], 0, 1)); ?>
                        </div>
                        <div class="buyer-info">
                            <div class="buyer-header">
                                <div class="flex">
                                    <h4 class="buyer-name"><?php echo htmlspecialchars($buyer['name']); ?></h4>
                                    <?php if ($buyer['unread_count'] > 0): ?>
                                        <span class="unread-badge">
                                            <?php echo $buyer['unread_count']; ?> new
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <span class="buyer-time">
                                    <?php
                                    $time_diff = time() - strtotime($buyer['last_message_time']);
                                    if ($time_diff < 60) {
                                        echo "Just now";
                                    } elseif ($time_diff < 3600) {
                                        echo floor($time_diff / 60) . "m ago";
                                    } elseif ($time_diff < 86400) {
                                        echo floor($time_diff / 3600) . "h ago";
                                    } else {
                                        echo date('M j', strtotime($buyer['last_message_time']));
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="buyer-message">
                                <?php if ($buyer['last_message']): ?>
                                    <?php echo htmlspecialchars($buyer['last_message']); ?>
                                <?php else: ?>
                                    <span style="font-style: italic;">No messages yet</span>
                                <?php endif; ?>
                            </div>
                            <div class="view-text">
                                <i class="bi bi-chat-text"></i> View conversation
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="card-footer">
            <a href="seller_dashboard.php" class="button button-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>