<?php
ob_start();
// seller_dashboard.php - Dashboard for sellers to manage their listings and buyer messages
session_start();
require_once '../includes/database/connection.php';
//marketplacetut.onlinewebshop.net/includes/database/connection.php
// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get all listings by this seller
try {
    $stmt = $pdo->prepare("
        SELECT l.*, 
               (SELECT COUNT(DISTINCT cs.buyer_id) 
                FROM chat_sessions cs 
                WHERE cs.listing_id = l.id AND cs.seller_id = ?) as buyer_count,
               (SELECT COUNT(*) 
                FROM chat_messages cm 
                WHERE cm.listing_id = l.id AND cm.receiver_id = ? AND cm.read_status = 0) as unread_count
        FROM listings l
        WHERE l.user_id = ?
        ORDER BY l.created_at DESC
    ");
    $stmt->execute([$user_id, $user_id, $user_id]);
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Include header
include_once '../includes/header.php';
?>

<style>
    :root {
        --primary: #3a86ff;
        --secondary: #8338ec;
        --accent: #ff006e;
        --light: #ffffff;
        --dark: #1a1a2e;
        --gray: #f0f0f0;
        --text: #333333;
    }

    body {
        background-color: var(--gray);
        color: var(--text);
        font-family: 'Poppins', sans-serif;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
    }

    h1,
    h2,
    h5 {
        color: var(--dark);
        font-weight: 600;
    }

    /* Sidebar styling */
    .sidebar {
        background-color: var(--light);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        padding: 0;
        overflow: hidden;
    }

    .list-group-item {
        border: none;
        border-left: 4px solid transparent;
        padding: 15px 20px;
        color: var(--text);
        transition: all 0.3s ease;
    }

    .list-group-item:hover {
        background-color: rgba(58, 134, 255, 0.05);
        border-left: 4px solid var(--primary);
    }

    .list-group-item.active {
        background-color: rgba(58, 134, 255, 0.1);
        color: var(--primary);
        border-left: 4px solid var(--primary);
        font-weight: 600;
    }

    .badge {
        padding: 5px 8px;
        border-radius: 12px;
    }

    .bg-danger {
        background-color: var(--accent) !important;
    }

    /* Card styling */
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background-color: var(--light);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 15px 20px;
    }

    .card-body {
        padding: 20px;
    }

    .card-footer {
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        padding: 15px 20px;
    }

    /* Listing image styling */
    .listing-img {
        height: 180px;
        object-fit: cover;
    }

    /* Button styling */
    .btn {
        border-radius: 8px;
        padding: 8px 16px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .btn-primary:hover {
        background-color: #2a75ea;
        border-color: #2a75ea;
    }

    .btn-secondary {
        background-color: var(--secondary);
        border-color: var(--secondary);
    }

    .btn-secondary:hover {
        background-color: #732bdb;
        border-color: #732bdb;
    }

    .btn-danger {
        background-color: var(--accent);
        border-color: var(--accent);
    }

    .btn-danger:hover {
        background-color: #e6005f;
        border-color: #e6005f;
    }

    .btn-outline-primary {
        color: var(--primary);
        border-color: var(--primary);
    }

    .btn-outline-primary:hover {
        background-color: var(--primary);
        color: var(--light);
    }

    .btn-outline-secondary {
        color: var(--secondary);
        border-color: var(--secondary);
    }

    .btn-outline-secondary:hover {
        background-color: var(--secondary);
        color: var(--light);
    }

    .btn-outline-danger {
        color: var(--accent);
        border-color: var(--accent);
    }

    .btn-outline-danger:hover {
        background-color: var(--accent);
        color: var(--light);
    }

    /* Tab content */
    .tab-content {
        background-color: var(--light);
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    /* Stats card */
    .stats-card {
        border-left: 4px solid var(--primary);
        margin-bottom: 15px;
        background-color: var(--light);
    }

    .stats-card.inquiries {
        border-left-color: var(--secondary);
    }

    .stats-card.messages {
        border-left-color: var(--accent);
    }

    .stats-value {
        font-size: 24px;
        font-weight: 600;
        color: var(--dark);
    }

    .stats-label {
        color: var(--text);
        font-size: 14px;
    }

    /* Messages styling */
    .message-list .list-group-item {
        margin-bottom: 10px;
        border-radius: 8px;
    }

    .price-tag {
        color: var(--accent);
        font-weight: 600;
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
    }

    .empty-state i {
        font-size: 48px;
        color: var(--gray);
        margin-bottom: 15px;
    }

    /* Modal styling */
    .modal-content {
        border-radius: 12px;
        border: none;
    }

    .modal-header {
        background-color: var(--light);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .modal-footer {
        border-top: 1px solid rgba(0, 0, 0, 0.05);
    }
</style>

<div class="container my-5">
    <div class="d-flex align-items-center mb-4">
        <div class="bg-primary rounded p-2 me-3 text-white">
            <i class="bi bi-shop fs-4"></i>
        </div>
        <h1 class="mb-0">Seller Dashboard</h1>
    </div>

    <div class="row">
        <div class="col-lg-3">
            <!-- Sidebar navigation -->
            <div class="sidebar mb-4">
                <div class="list-group">
                    <a href="#listings" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                        <i class="bi bi-grid me-2"></i> My Listings
                    </a>
                    <a href="#messages" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <i class="bi bi-chat-dots me-2"></i> Messages
                        <?php if (array_sum(array_column($listings, 'unread_count')) > 0): ?>
                            <span class="badge bg-danger float-end">
                                <?php echo array_sum(array_column($listings, 'unread_count')); ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="bi bi-graph-up me-2"></i> Analytics
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="bi bi-gear me-2"></i> Settings
                    </a>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Quick Stats</h5>
                </div>
                <div class="card-body p-0">
                    <div class="stats-card p-3">
                        <div class="stats-value"><?php echo count($listings); ?></div>
                        <div class="stats-label">Active Listings</div>
                    </div>
                    <div class="stats-card inquiries p-3">
                        <div class="stats-value"><?php echo array_sum(array_column($listings, 'buyer_count')); ?></div>
                        <div class="stats-label">Buyer Inquiries</div>
                    </div>
                    <div class="stats-card messages p-3">
                        <div class="stats-value"><?php echo array_sum(array_column($listings, 'unread_count')); ?></div>
                        <div class="stats-label">Unread Messages</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <!-- Tab content -->
            <div class="tab-content">
                <!-- Listings Tab -->
                <div class="tab-pane fade show active" id="listings">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-grid me-2"></i>My Listings</h2>
                        <a href="create_listing.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i> New Listing
                        </a>
                    </div>

                    <?php if (empty($listings)): ?>
                        <div class="empty-state">
                            <i class="bi bi-box"></i>
                            <h4>No Listings Yet</h4>
                            <p class="text-muted">You don't have any listings yet. Create your first listing to start
                                selling!</p>
                            <a href="create_listing.php" class="btn btn-primary mt-3">
                                <i class="bi bi-plus-circle me-2"></i> Create First Listing
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($listings as $listing): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100">
                                        <div class="position-relative">
                                            <?php if ($listing['image']): ?>
                                                <img src="data:<?php echo $listing['image_type']; ?>;base64,<?php echo base64_encode($listing['image']); ?>"
                                                    class="card-img-top listing-img" alt="<?php echo $listing['item_name']; ?>">
                                            <?php else: ?>
                                                <img src="../public/images/placeholder.jpg" class="card-img-top listing-img"
                                                    alt="Placeholder">
                                            <?php endif; ?>
                                            <div class="position-absolute top-0 end-0 m-2">
                                                <span class="badge bg-primary">
                                                    R<?php echo $listing['price']; ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo $listing['item_name']; ?></h5>
                                            <p class="card-text small text-muted">
                                                <i class="bi bi-calendar me-1"></i> Listed on
                                                <?php echo date('M j, Y', strtotime($listing['created_at'])); ?>
                                            </p>

                                            <?php if ($listing['buyer_count'] > 0): ?>
                                                <div class="d-flex align-items-center text-secondary mb-2">
                                                    <i class="bi bi-people me-2"></i>
                                                    <span><?php echo $listing['buyer_count']; ?> interested buyer(s)</span>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($listing['unread_count'] > 0): ?>
                                                <div class="d-flex align-items-center text-danger">
                                                    <i class="bi bi-envelope me-2"></i>
                                                    <span><?php echo $listing['unread_count']; ?> unread message(s)</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="card-footer bg-white">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <a href="view_buyers.php?listing_id=<?php echo $listing['id']; ?>"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-chat me-1"></i> View Inquiries
                                                </a>
                                                <div class="btn-group">
                                                    <a href="edit_listing.php?id=<?php echo $listing['id']; ?>"
                                                        class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                        onclick="confirmDelete(<?php echo $listing['id']; ?>)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Messages Tab -->
                <div class="tab-pane fade" id="messages">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-chat-dots me-2"></i>Buyer Messages</h2>
                    </div>

                    <?php if (empty($listings) || array_sum(array_column($listings, 'buyer_count')) == 0): ?>
                        <div class="empty-state">
                            <i class="bi bi-chat"></i>
                            <h4>No Messages Yet</h4>
                            <p class="text-muted">You don't have any messages from buyers yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group message-list">
                            <?php foreach ($listings as $listing):
                                if ($listing['buyer_count'] > 0):
                                    ?>
                                    <div class="list-group-item">
                                        <div class="row align-items-center">
                                            <div class="col-md-2">
                                                <?php if ($listing['image']): ?>
                                                    <img src="data:<?php echo $listing['image_type']; ?>;base64,<?php echo base64_encode($listing['image']); ?>"
                                                        class="img-fluid rounded" style="height: 70px; width: 70px; object-fit: cover;"
                                                        alt="<?php echo $listing['item_name']; ?>">
                                                <?php else: ?>
                                                    <img src="../public/images/placeholder.jpg" class="img-fluid rounded"
                                                        style="height: 70px; width: 70px; object-fit: cover;" alt="Placeholder">
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-7">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h5 class="mb-1"><?php echo $listing['item_name']; ?></h5>
                                                    <span class="price-tag">$<?php echo $listing['price']; ?></span>
                                                </div>
                                                <p class="mb-1">
                                                    <strong><?php echo $listing['buyer_count']; ?> buyer(s)</strong> have contacted
                                                    you about this item.
                                                    <?php if ($listing['unread_count'] > 0): ?>
                                                        <span class="badge bg-danger ms-2">
                                                            <?php echo $listing['unread_count']; ?> unread
                                                        </span>
                                                    <?php endif; ?>
                                                </p>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar me-1"></i> Listed on
                                                    <?php echo date('M j, Y', strtotime($listing['created_at'])); ?>
                                                </small>
                                            </div>
                                            <div class="col-md-3 text-md-end mt-3 mt-md-0">
                                                <a href="view_buyers.php?listing_id=<?php echo $listing['id']; ?>"
                                                    class="btn btn-primary">
                                                    <i class="bi bi-chat-dots me-1"></i> View Conversations
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                endif;
                            endforeach;
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<!-- <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 48px;"></i>
                </div>
                <p>Are you sure you want to delete this listing? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div> -->

<script>
    function confirmDelete(listingId) {
        document.getElementById('confirmDeleteBtn').href = 'delete_listing.php?id=' + listingId;
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }
</script>

<?php include_once '../includes/footer.php'; ?>