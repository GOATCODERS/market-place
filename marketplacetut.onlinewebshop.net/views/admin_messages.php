<?php
// This file is included by admin_dashboard.php when the messages page is selected

// Get search filters
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Pagination settings
$page = isset($_GET['p']) ? (int) $_GET['p'] : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Build query for active chat sessions
$sessionQuery = "SELECT cs.id, cs.listing_id, cs.buyer_id, cs.seller_id, cs.last_message_at,
                l.item_name, l.price,
                b.name as buyer_name, b.surname as buyer_surname, b.email as buyer_email,
                s.name as seller_name, s.surname as seller_surname, s.email as seller_email,
                (SELECT COUNT(*) FROM chat_messages WHERE listing_id = cs.listing_id AND 
                 ((sender_id = cs.buyer_id AND receiver_id = cs.seller_id) OR 
                  (sender_id = cs.seller_id AND receiver_id = cs.buyer_id))) as message_count,
                (SELECT COUNT(*) FROM chat_messages WHERE listing_id = cs.listing_id AND 
                 ((sender_id = cs.buyer_id AND receiver_id = cs.seller_id) OR 
                  (sender_id = cs.seller_id AND receiver_id = cs.buyer_id)) AND read_status = 0) as unread_count
                FROM chat_sessions cs
                JOIN listings l ON cs.listing_id = l.id
                JOIN Users b ON cs.buyer_id = b.id
                JOIN Users s ON cs.seller_id = s.id
                WHERE 1=1";

$countQuery = "SELECT COUNT(*) as total FROM chat_sessions cs
              JOIN listings l ON cs.listing_id = l.id
              JOIN Users b ON cs.buyer_id = b.id
              JOIN Users s ON cs.seller_id = s.id
              WHERE 1=1";

$queryParams = [];
$types = "";

if (!empty($searchTerm)) {
    $searchParam = "%$searchTerm%";
    $sessionQuery .= " AND (l.item_name LIKE ? OR b.name LIKE ? OR b.surname LIKE ? OR b.email LIKE ? OR s.name LIKE ? OR s.surname LIKE ? OR s.email LIKE ?)";
    $countQuery .= " AND (l.item_name LIKE ? OR b.name LIKE ? OR b.surname LIKE ? OR b.email LIKE ? OR s.name LIKE ? OR s.surname LIKE ? OR s.email LIKE ?)";
    array_push($queryParams, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam);
    $types .= "sssssss";
}

if (!empty($dateFrom)) {
    $sessionQuery .= " AND cs.last_message_at >= ?";
    $countQuery .= " AND cs.last_message_at >= ?";
    array_push($queryParams, $dateFrom . ' 00:00:00');
    $types .= "s";
}

if (!empty($dateTo)) {
    $sessionQuery .= " AND cs.last_message_at <= ?";
    $countQuery .= " AND cs.last_message_at <= ?";
    array_push($queryParams, $dateTo . ' 23:59:59');
    $types .= "s";
}

$sessionQuery .= " ORDER BY cs.last_message_at DESC LIMIT ? OFFSET ?";
array_push($queryParams, $perPage, $offset);
$types .= "ii";

// Get total count
$countStmt = $conn->prepare($countQuery);
if (!empty($types) && !empty($queryParams)) {
    $countParams = array_slice($queryParams, 0, -2); // Remove limit and offset params
    $countTypes = substr($types, 0, -2);
    if (!empty($countParams)) {
        $countStmt->bind_param($countTypes, ...$countParams);
    }
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalSessions = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalSessions / $perPage);
$countStmt->close();

// Get sessions
$sessionStmt = $conn->prepare($sessionQuery);
if (!empty($types) && !empty($queryParams)) {
    $sessionStmt->bind_param($types, ...$queryParams);
}
$sessionStmt->execute();
$sessionsResult = $sessionStmt->get_result();
$sessions = [];
while ($row = $sessionsResult->fetch_assoc()) {
    $sessions[] = $row;
}
$sessionStmt->close();

// Get message stats for charts
$msgPerDayQuery = "SELECT DATE(created_at) as date, COUNT(*) as count 
                  FROM chat_messages 
                  WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY) 
                  GROUP BY DATE(created_at) 
                  ORDER BY date";

$msgPerDayResult = $conn->query($msgPerDayQuery);
$msgDates = [];
$msgCounts = [];

if ($msgPerDayResult) {
    while ($row = $msgPerDayResult->fetch_assoc()) {
        $msgDates[] = date('M d', strtotime($row['date']));
        $msgCounts[] = $row['count'];
    }
}

// Calculate average response time
$avgResponseQuery = "SELECT AVG(TIMESTAMPDIFF(MINUTE, a.created_at, b.created_at)) as avg_time
                    FROM chat_messages a
                    JOIN chat_messages b ON a.listing_id = b.listing_id
                    WHERE a.sender_id = b.receiver_id 
                    AND a.receiver_id = b.sender_id
                    AND b.created_at > a.created_at
                    AND a.created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)";
$avgResponseResult = $conn->query($avgResponseQuery);
$avgResponseTime = '~12 min'; // Default value
if ($avgResponseResult && $row = $avgResponseResult->fetch_assoc()) {
    if (!is_null($row['avg_time'])) {
        $avgResponseTime = round($row['avg_time']) . ' min';
    }
}

// Handle session deletion if requested
if (isset($_GET['delete_session']) && is_numeric($_GET['delete_session'])) {
    $sessionId = $_GET['delete_session'];
    $deleteQuery = "DELETE FROM chat_sessions WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $sessionId);
    if ($stmt->execute()) {
        $successMessage = "Chat session deleted successfully";
    } else {
        $errorMessage = "Error deleting chat session";
    }
    $stmt->close();

    // Redirect to remove the query parameter
    header("Location: admin_dashboard.php?page=messages");
    exit();
}
?>

<div class="admin-messages-page">
    <div class="filters-container">
        <form method="GET" action="admin_dashboard.php" class="filters-form">
            <input type="hidden" name="page" value="messages">

            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="search" placeholder="Search conversations..."
                        value="<?php echo htmlspecialchars($searchTerm); ?>">
                </div>

                <div class="form-group">
                    <input type="date" name="date_from" placeholder="From Date" value="<?php echo $dateFrom; ?>">
                </div>

                <div class="form-group">
                    <input type="date" name="date_to" placeholder="To Date" value="<?php echo $dateTo; ?>">
                </div>

                <div class="form-group buttons">
                    <button type="submit" class="btn-primary">Filter</button>
                    <a href="admin_dashboard.php?page=messages" class="btn-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div class="messages-stats">
        <div class="stat-card">
            <div class="stat-icon messages">
                <i class="fas fa-comments"></i>
            </div>
            <div class="stat-details">
                <h3>Total Chat Sessions</h3>
                <p class="stat-number"><?php echo $totalSessions; ?></p>
            </div>
        </div>

        <?php
        // Count total messages
        $totalMessagesQuery = "SELECT COUNT(*) as total FROM chat_messages";
        $totalMessagesResult = $conn->query($totalMessagesQuery);
        $totalMessages = 0;
        if ($totalMessagesResult) {
            $row = $totalMessagesResult->fetch_assoc();
            $totalMessages = $row['total'];
        }
        ?>
        <div class="stat-card">
            <div class="stat-icon active">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stat-details">
                <h3>Total Messages</h3>
                <p class="stat-number"><?php echo $totalMessages; ?></p>
            </div>
        </div>

        <?php
        // Count unread messages
        $unreadQuery = "SELECT COUNT(*) as total FROM chat_messages WHERE read_status = 0";
        $unreadResult = $conn->query($unreadQuery);
        $unreadMessages = 0;
        if ($unreadResult) {
            $row = $unreadResult->fetch_assoc();
            $unreadMessages = $row['total'];
        }
        ?>
        <div class="stat-card">
            <div class="stat-icon unread">
                <i class="fas fa-envelope-open"></i>
            </div>
            <div class="stat-details">
                <h3>Unread Messages</h3>
                <p class="stat-number"><?php echo $unreadMessages; ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon response">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-details">
                <h3>Avg. Response Time</h3>
                <p class="stat-number"><?php echo $avgResponseTime; ?></p>
            </div>
        </div>
    </div>

    <div class="chat-activity-chart">
        <h4>Message Activity (Last 7 Days)</h4>
        <div class="chart-container">
            <canvas id="messageActivityChart"></canvas>
        </div>
    </div>

    <div class="sessions-table-container">
        <h4>Active Chat Sessions</h4>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Listing</th>
                    <th>Buyer</th>
                    <th>Seller</th>
                    <th>Messages</th>
                    <th>Unread</th>
                    <th>Last Activity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sessions as $session): ?>
                    <tr>
                        <td><?php echo $session['id']; ?></td>
                        <td>
                            <a href="view_listing.php?id=<?php echo $session['listing_id']; ?>" class="listing-link">
                                <?php echo htmlspecialchars($session['item_name']); ?>
                            </a>
                            <span class="listing-price">$<?php echo number_format($session['price'], 2); ?></span>
                        </td>
                        <td>
                            <a href="view_user.php?id=<?php echo $session['buyer_id']; ?>">
                                <?php echo htmlspecialchars($session['buyer_name'] . ' ' . $session['buyer_surname']); ?>
                            </a>
                            <span class="user-email"><?php echo htmlspecialchars($session['buyer_email']); ?></span>
                        </td>
                        <td>
                            <a href="view_user.php?id=<?php echo $session['seller_id']; ?>">
                                <?php echo htmlspecialchars($session['seller_name'] . ' ' . $session['seller_surname']); ?>
                            </a>
                            <span class="user-email"><?php echo htmlspecialchars($session['seller_email']); ?></span>
                        </td>
                        <td class="center"><?php echo $session['message_count']; ?></td>
                        <td class="center">
                            <?php if ($session['unread_count'] > 0): ?>
                                <span class="badge unread"><?php echo $session['unread_count']; ?></span>
                            <?php else: ?>
                                <span class="badge read">0</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M d, Y g:i A', strtotime($session['last_message_at'])); ?></td>
                        <td class="actions">
                            <a href="view_conversation.php?session_id=<?php echo $session['id']; ?>" class="btn-view"
                                title="View Conversation">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="admin_dashboard.php?page=messages&delete_session=<?php echo $session['id']; ?>"
                                class="btn-delete" title="Delete Session"
                                onclick="return confirm('Are you sure you want to delete this chat session? This action cannot be undone.');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (count($sessions) === 0): ?>
                    <tr>
                        <td colspan="8" class="no-records">No chat sessions found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="admin_dashboard.php?page=messages&p=1<?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?><?php echo !empty($dateFrom) ? '&date_from=' . urlencode($dateFrom) : ''; ?><?php echo !empty($dateTo) ? '&date_to=' . urlencode($dateTo) : ''; ?>"
                        class="first-page">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="admin_dashboard.php?page=messages&p=<?php echo $page - 1; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?><?php echo !empty($dateFrom) ? '&date_from=' . urlencode($dateFrom) : ''; ?><?php echo !empty($dateTo) ? '&date_to=' . urlencode($dateTo) : ''; ?>"
                        class="prev-page">
                        <i class="fas fa-angle-left"></i>
                    </a>
                <?php endif; ?>

                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);

                for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                    <a href="admin_dashboard.php?page=messages&p=<?php echo $i; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?><?php echo !empty($dateFrom) ? '&date_from=' . urlencode($dateFrom) : ''; ?><?php echo !empty($dateTo) ? '&date_to=' . urlencode($dateTo) : ''; ?>"
                        class="page-number <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="admin_dashboard.php?page=messages&p=<?php echo $page + 1; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?><?php echo !empty($dateFrom) ? '&date_from=' . urlencode($dateFrom) : ''; ?><?php echo !empty($dateTo) ? '&date_to=' . urlencode($dateTo) : ''; ?>"
                        class="next-page">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="admin_dashboard.php?page=messages&p=<?php echo $totalPages; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?><?php echo !empty($dateFrom) ? '&date_from=' . urlencode($dateFrom) : ''; ?><?php echo !empty($dateTo) ? '&date_to=' . urlencode($dateTo) : ''; ?>"
                        class="last-page">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Page-specific scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Message Activity Chart
        const ctx = document.getElementById('messageActivityChart').getContext('2d');
        const messageChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($msgDates); ?>,
                datasets: [{
                    label: 'Messages',
                    data: <?php echo json_encode($msgCounts); ?>,
                    backgroundColor: 'rgba(58, 134, 255, 0.2)',
                    borderColor: 'rgba(58, 134, 255, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(58, 134, 255, 1)',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(200, 200, 200, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(26, 26, 46, 0.9)',
                        padding: 10,
                        bodyFont: {
                            size: 14
                        },
                        callbacks: {
                            title: function () {
                                return '';
                            },
                            label: function (context) {
                                return `${context.label}: ${context.raw} messages`;
                            }
                        }
                    }
                },
                maintainAspectRatio: false,
                responsive: true
            }
        });

        // Function to handle view conversation click
        const viewButtons = document.querySelectorAll('.btn-view');
        viewButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                const url = this.getAttribute('href');
                window.location.href = url;
            });
        });

        // Confirm dialog for delete button
        const deleteButtons = document.querySelectorAll('.btn-delete');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                if (!confirm('Are you sure you want to delete this chat session? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        });
    });
</script>

<!-- Additional CSS for message center -->
<style>
    .admin-messages-page {
        padding: 20px 0;
    }

    .filters-container {
        background-color: var(--gray);
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .filters-form .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: flex-end;
    }

    .filters-form .form-group {
        flex: 1;
        min-width: 200px;
    }

    .filters-form input[type="text"],
    .filters-form input[type="date"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .filters-form .buttons {
        display: flex;
        gap: 10px;
    }

    .messages-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .chat-activity-chart {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .chart-container {
        height: 300px;
        position: relative;
    }

    .sessions-table-container {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .sessions-table-container h4 {
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 18px;
        color: var(--dark);
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th,
    .data-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .data-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: var(--dark);
    }

    .data-table td.center {
        text-align: center;
    }

    .data-table .listing-link {
        display: block;
        font-weight: 500;
        color: var(--primary);
        text-decoration: none;
        margin-bottom: 3px;
    }

    .data-table .listing-price {
        display: block;
        font-size: 0.85em;
        color: #777;
    }

    .data-table .user-email {
        display: block;
        font-size: 0.85em;
        color: #777;
    }

    .data-table .badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 500;
    }

    .data-table .badge.unread {
        background-color: var(--accent);
        color: white;
    }

    .data-table .badge.read {
        background-color: #e0e0e0;
        color: #777;
    }

    .data-table .actions {
        display: flex;
        gap: 10px;
    }

    .data-table .btn-view,
    .data-table .btn-delete {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 4px;
        color: white;
        text-decoration: none;
    }

    .data-table .btn-view {
        background-color: var(--primary);
    }

    .data-table .btn-delete {
        background-color: #ff4757;
    }

    .data-table .no-records {
        text-align: center;
        padding: 30px;
        color: #777;
    }

    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 20px;
        gap: 5px;
    }

    .pagination a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 36px;
        min-width: 36px;
        padding: 0 5px;
        border-radius: 4px;
        background-color: #f8f9fa;
        color: var(--text);
        text-decoration: none;
        transition: all 0.2s;
    }

    .pagination a:hover {
        background-color: #e9ecef;
    }

    .pagination a.active {
        background-color: var(--primary);
        color: white;
    }

    .pagination .first-page,
    .pagination .prev-page,
    .pagination .next-page,
    .pagination .last-page {
        font-size: 14px;
    }

    /* Responsive adjustments */
    @media (max-width: 992px) {
        .messages-stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .filters-form .form-group {
            width: 100%;
            min-width: 100%;
        }

        .data-table thead {
            display: none;
        }

        .data-table,
        .data-table tbody,
        .data-table tr,
        .data-table td {
            display: block;
            width: 100%;
        }

        .data-table tr {
            margin-bottom: 15px;
            border: 1px solid #eee;
            border-radius: 4px;
            overflow: hidden;
        }

        .data-table td {
            display: flex;
            padding: 10px 15px;
            text-align: right;
            border-bottom: 1px solid #eee;
        }

        .data-table td::before {
            content: attr(data-label);
            font-weight: 600;
            margin-right: auto;
            text-align: left;
        }

        .data-table td.actions {
            justify-content: flex-end;
        }
    }

    @media (max-width: 576px) {
        .messages-stats {
            grid-template-columns: 1fr;
        }
    }
</style>