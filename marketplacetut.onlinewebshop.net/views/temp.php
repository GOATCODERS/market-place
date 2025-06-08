<?php
// view_buyers.php - View all buyers who have contacted about a specific listing
session_start();
require_once '..\includes\database\connection.php';

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
               (SELECT MAX(created_at) FROM chat_messages 
                WHERE (sender_id = cs.buyer_id AND receiver_id = cs.seller_id) 
                   OR (sender_id = cs.seller_id AND receiver_id = cs.buyer_id)
                   AND listing_id = cs.listing_id) as last_message_time,
               (SELECT COUNT(*) FROM chat_messages 
                WHERE sender_id = cs.buyer_id AND receiver_id = cs.seller_id 
                AND listing_id = cs.listing_id AND read_status = 0) as unread_count,
               (SELECT message FROM chat_messages 
                WHERE (sender_id = cs.buyer_id AND receiver_id = cs.seller_id) 
                   OR (sender_id = cs.seller_id AND receiver_id = cs.buyer_id)
                   AND listing_id = cs.listing_id
                ORDER BY created_at DESC LIMIT 1) as last_message
        FROM chat_sessions cs
        JOIN users u ON cs.buyer_id = u.id
        WHERE cs.listing_id = ? AND cs.seller_id = ?
        ORDER BY last_message_time DESC
    ");
    $stmt->execute([$listing_id, $user_id]);
    $buyers = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- Bootstrap CSS without integrity -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons (Optional if using Bootstrap icons) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

</head>

<body>
    <?php
    // Include header and navigation
    include_once '../includes/header.php';
    ?>
    <div class="container my-5">
        <div class="d-flex align-items-center mb-4">
            <nav aria-label="breadcrumb" class="w-100">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="seller_dashboard.php"><i class="bi bi-house me-1"></i>Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="bi bi-people me-1"></i>Buyers for <?php echo $listing['item_name']; ?>
                    </li>
                </ol>
            </nav>
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary rounded p-2 me-3 text-white">
                            <i class="bi bi-tag-fill fs-4"></i>
                        </div>
                        <h2 class="mb-0"><?php echo htmlspecialchars($listing['item_name']); ?></h2>
                    </div>
                    <div class="price-tag">
                        $<?php echo number_format($listing['price'], 2); ?>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="position-relative" style="height: 240px;">
                            <?php if ($listing['image']): ?>
                                <img src="data:<?php echo $listing['image_type']; ?>;base64,<?php echo base64_encode($listing['image']); ?>"
                                    class="listing-img" alt="<?php echo htmlspecialchars($listing['item_name']); ?>">
                            <?php else: ?>
                                <img src="../public/images/placeholder.jpg" class="listing-img" alt="Placeholder">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="mb-4">
                            <div class="badge bg-secondary mb-2">
                                <i class="bi bi-bookmark me-1"></i><?php echo htmlspecialchars($listing['category']); ?>
                            </div>

                            <p class="text-muted mb-4">
                                <i class="bi bi-calendar me-1"></i> Listed on
                                <?php echo date('F j, Y', strtotime($listing['created_at'])); ?>
                            </p>

                            <ul class="list-unstyled item-details">
                                <li>
                                    <span class="detail-label"><i class="bi bi-info-circle me-2"></i>Condition:</span>
                                    <span><?php echo htmlspecialchars($listing['item_condition']); ?></span>
                                </li>
                                <li>
                                    <span class="detail-label"><i class="bi bi-chat-quote me-2"></i>Description:</span>
                                    <span><?php echo htmlspecialchars($listing['description']); ?></span>
                                </li>
                            </ul>
                        </div>

                        <div class="d-flex mt-3">
                            <a href="edit_listing.php?id=<?php echo $listing_id; ?>"
                                class="btn btn-outline-primary me-2">
                                <i class="bi bi-pencil me-1"></i> Edit Listing
                            </a>
                            <a href="seller_dashboard.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="bg-secondary rounded p-2 me-3 text-white">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                        <h3 class="mb-0">Interested Buyers</h3>
                    </div>
                    <div class="badge bg-primary">
                        <?php echo count($buyers); ?> buyer<?php echo count($buyers) != 1 ? 's' : ''; ?>
                    </div>
                </div>
            </div>

            <?php if (empty($buyers)): ?>
                <div class="card-body">
                    <div class="empty-state">
                        <i class="bi bi-chat"></i>
                        <h4>No Buyers Yet</h4>
                        <p class="text-muted">No buyers have contacted you about this listing yet.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="list-group buyer-list">
                    <?php foreach ($buyers as $buyer): ?>
                        <a href="chat.php?listing_id=<?php echo $listing_id; ?>&user_id=<?php echo $buyer['buyer_id']; ?>"
                            class="list-group-item list-group-item-action <?php echo $buyer['unread_count'] > 0 ? 'unread' : ''; ?>">
                            <div class="d-flex">
                                <div class="buyer-avatar">
                                    <?php echo strtoupper(substr($buyer['name'], 0, 1)); ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="d-flex align-items-center">
                                            <h5 class="mb-0 me-2"><?php echo htmlspecialchars($buyer['name']); ?></h5>
                                            <?php if ($buyer['unread_count'] > 0): ?>
                                                <span class="badge bg-danger">
                                                    <?php echo $buyer['unread_count']; ?> new
                                                    message<?php echo $buyer['unread_count'] != 1 ? 's' : ''; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="time-badge">
                                            <?php
                                            $time_diff = time() - strtotime($buyer['last_message_time']);
                                            if ($time_diff < 60) {
                                                echo "<i class='bi bi-clock me-1'></i>Just now";
                                            } elseif ($time_diff < 3600) {
                                                echo "<i class='bi bi-clock me-1'></i>" . floor($time_diff / 60) . " min ago";
                                            } elseif ($time_diff < 86400) {
                                                echo "<i class='bi bi-clock me-1'></i>" . floor($time_diff / 3600) . " hr ago";
                                            } else {
                                                echo "<i class='bi bi-calendar2 me-1'></i>" . date('M j', strtotime($buyer['last_message_time']));
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="message-preview mb-1">
                                        <?php if ($buyer['last_message']): ?>
                                            <span
                                                class="text-truncate"><?php echo htmlspecialchars($buyer['last_message']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">No messages yet</span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-primary">
                                        <i class="bi bi-chat-dots me-1"></i>View conversation
                                    </small>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="card-footer">
                <a href="seller_dashboard.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
    <?php include_once '../includes/footer.php'; ?>
    <!-- Bootstrap JS without integrity -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>

</html>