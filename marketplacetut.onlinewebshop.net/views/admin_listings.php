<?php
ob_start();
// Initialize variables early to prevent undefined variable errors
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
$conditionFilter = isset($_GET['condition']) ? $_GET['condition'] : '';
$priceMin = isset($_GET['price_min']) ? (float) $_GET['price_min'] : '';
$priceMax = isset($_GET['price_max']) ? (float) $_GET['price_max'] : '';

// Process actions if any
$messageClass = '';
$message = '';

// Pagination settings
$page = isset($_GET['p']) ? (int) $_GET['p'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get distinct categories and conditions for filters
$categoriesQuery = "SELECT DISTINCT category FROM listings ORDER BY category";
$categoriesResult = $conn->query($categoriesQuery);
$categories = [];
if ($categoriesResult) {
    while ($row = $categoriesResult->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}

$conditionsQuery = "SELECT DISTINCT item_condition FROM listings ORDER BY item_condition";
$conditionsResult = $conn->query($conditionsQuery);
$conditions = [];
if ($conditionsResult) {
    while ($row = $conditionsResult->fetch_assoc()) {
        $conditions[] = $row['item_condition'];
    }
}

// Build query
$listingQuery = "SELECT l.*, u.name, u.surname, u.email 
                FROM listings l 
                LEFT JOIN Users u ON l.user_id = u.id 
                WHERE 1=1";
$countQuery = "SELECT COUNT(*) as total FROM listings l WHERE 1=1";

$queryParams = [];
$types = "";

if (!empty($searchTerm)) {
    $searchParam = "%$searchTerm%";
    $listingQuery .= " AND (l.item_name LIKE ? OR l.description LIKE ?)";
    $countQuery .= " AND (item_name LIKE ? OR description LIKE ?)";
    array_push($queryParams, $searchParam, $searchParam);
    $types .= "ss";
}

if (!empty($categoryFilter)) {
    $listingQuery .= " AND l.category = ?";
    $countQuery .= " AND category = ?";
    array_push($queryParams, $categoryFilter);
    $types .= "s";
}

if (!empty($conditionFilter)) {
    $listingQuery .= " AND l.item_condition = ?";
    $countQuery .= " AND item_condition = ?";
    array_push($queryParams, $conditionFilter);
    $types .= "s";
}

if ($priceMin !== '') {
    $listingQuery .= " AND l.price >= ?";
    $countQuery .= " AND price >= ?";
    array_push($queryParams, $priceMin);
    $types .= "d";
}

if ($priceMax !== '') {
    $listingQuery .= " AND l.price <= ?";
    $countQuery .= " AND price <= ?";
    array_push($queryParams, $priceMax);
    $types .= "d";
}

$listingQuery .= " ORDER BY l.created_at DESC LIMIT ? OFFSET ?";
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
$totalListings = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalListings / $perPage);
$countStmt->close();

// Get listings
$listingStmt = $conn->prepare($listingQuery);
if (!empty($types) && !empty($queryParams)) {
    $listingStmt->bind_param($types, ...$queryParams);
}
$listingStmt->execute();
$listingsResult = $listingStmt->get_result();
$listings = [];
while ($row = $listingsResult->fetch_assoc()) {
    $listings[] = $row;
}

$listingStmt->close();
?>


<div class="admin-listings-page">
    <?php if (!empty($message)): ?>
        <div class="alert <?php echo $messageClass; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="filters-container">
        <form method="GET" action="admin_dashboard.php" class="filters-form">
            <input type="hidden" name="page" value="listings">

            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="search" placeholder="Search listings..."
                        value="<?php echo htmlspecialchars($searchTerm); ?>">
                </div>

                <div class="form-group">
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>" <?php echo $categoryFilter === $category ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <select name="condition">
                        <option value="">All Conditions</option>
                        <?php foreach ($conditions as $condition): ?>
                            <option value="<?php echo htmlspecialchars($condition); ?>" <?php echo $conditionFilter === $condition ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($condition); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <input type="number" name="price_min" placeholder="Min Price" step="0.01"
                        value="<?php echo $priceMin !== '' ? $priceMin : ''; ?>">
                </div>

                <div class="form-group">
                    <input type="number" name="price_max" placeholder="Max Price" step="0.01"
                        value="<?php echo $priceMax !== '' ? $priceMax : ''; ?>">
                </div>

                <div class="form-group buttons">
                    <button type="submit" class="btn-primary">Filter</button>
                    <a href="admin_dashboard.php?page=listings" class="btn-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div class="listings-table-container">
    <div class="export-buttons">
    <a href="export_listings.php<?php echo !empty($searchTerm) ? '?search=' . urlencode($searchTerm) : '?'; ?><?php echo !empty($categoryFilter) ? '&category=' . urlencode($categoryFilter) : ''; ?><?php echo !empty($conditionFilter) ? '&condition=' . urlencode($conditionFilter) : ''; ?><?php echo $priceMin !== '' ? '&price_min=' . $priceMin : ''; ?><?php echo $priceMax !== '' ? '&price_max=' . $priceMax : ''; ?>" class="btn-primary">
        <i class="fas fa-file-export"></i> Export Listings
    </a>
</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Condition</th>
                    <th>Price</th>
                    <th>Seller</th>
                    <th>Listed Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listings as $listing): ?>
                    <tr>
                        <td><?php echo $listing['id']; ?></td>
                        <td><?php echo htmlspecialchars($listing['item_name']); ?></td>
                        <td><?php echo htmlspecialchars($listing['category']); ?></td>
                        <td><?php echo htmlspecialchars($listing['item_condition']); ?></td>
                        <td>R<?php echo number_format($listing['price'], 2); ?></td>
                        <td>
                            <?php if (isset($listing['name'])): ?>
                                <?php echo htmlspecialchars($listing['name'] . ' ' . $listing['surname']); ?>
                                <span class="user-email"><?php echo htmlspecialchars($listing['email']); ?></span>
                            <?php else: ?>
                                Unknown
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($listing['created_at'])); ?></td>
                        <td class="actions">
                            <a href="view_listing.php?id=<?php echo $listing['id']; ?>" class="btn-sm btn-info"
                                title="View Details"><i class="fas fa-eye"></i></a>
                            <a href="#" class="btn-sm btn-warning feature-listing" data-id="<?php echo $listing['id']; ?>"
                                title="Feature Listing"><i class="fas fa-star"></i></a>
                            <a href="admin_dashboard.php?page=listings&delete_listing=<?php echo $listing['id']; ?>"
                                class="btn-sm btn-danger"
                                onclick="return confirm('Are you sure you want to delete this listing?')"
                                title="Delete Listing"><i class="fas fa-trash-alt"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($listings)): ?>
                    <tr>
                        <td colspan="8" class="no-data">No listings found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="admin_dashboard.php?page=listings&p=<?php echo $page - 1; ?>&search=<?php echo urlencode($searchTerm); ?>&category=<?php echo urlencode($categoryFilter); ?>&condition=<?php echo urlencode($conditionFilter); ?>&price_min=<?php echo $priceMin; ?>&price_max=<?php echo $priceMax; ?>"
                    class="pagination-btn">&laquo; Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="admin_dashboard.php?page=listings&p=<?php echo $i; ?>&search=<?php echo urlencode($searchTerm); ?>&category=<?php echo urlencode($categoryFilter); ?>&condition=<?php echo urlencode($conditionFilter); ?>&price_min=<?php echo $priceMin; ?>&price_max=<?php echo $priceMax; ?>"
                    class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="admin_dashboard.php?page=listings&p=<?php echo $page + 1; ?>&search=<?php echo urlencode($searchTerm); ?>&category=<?php echo urlencode($categoryFilter); ?>&condition=<?php echo urlencode($conditionFilter); ?>&price_min=<?php echo $priceMin; ?>&price_max=<?php echo $priceMax; ?>"
                    class="pagination-btn">Next &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="listing-stats">
        <div class="stat-card">
            <h4>Listings by Category</h4>
            <div class="chart-container">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <div class="stat-card">
            <h4>Price Distribution</h4>
            <div class="chart-container">
                <canvas id="priceChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Categories chart
            <?php
            $categoryQuery = "SELECT category, COUNT(*) as count FROM listings GROUP BY category ORDER BY count DESC LIMIT 10";
            $categoryResult = $conn->query($categoryQuery);
            $categoryLabels = [];
            $categoryCounts = [];

            if ($categoryResult) {
                while ($row = $categoryResult->fetch_assoc()) {
                    $categoryLabels[] = $row['category'];
                    $categoryCounts[] = $row['count'];
                }
            }
            ?>

            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            new Chart(categoryCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($categoryLabels); ?>,
                    datasets: [{
                        label: 'Number of Listings',
                        data: <?php echo json_encode($categoryCounts); ?>,
                        backgroundColor: 'rgba(58, 134, 255, 0.7)',
                        borderColor: 'rgba(58, 134, 255, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Price distribution chart
            <?php
            $priceQuery = "SELECT 
                        CASE
                            WHEN price BETWEEN 0 AND 50 THEN '0-50'
                            WHEN price BETWEEN 50.01 AND 100 THEN '50-100'
                            WHEN price BETWEEN 100.01 AND 250 THEN '100-250'
                            WHEN price BETWEEN 250.01 AND 500 THEN '250-500'
                            WHEN price BETWEEN 500.01 AND 1000 THEN '500-1000'
                            ELSE '1000+'
                        END as price_range,
                        COUNT(*) as count
                      FROM listings
                      GROUP BY price_range
                      ORDER BY 
                        CASE price_range
                            WHEN '0-50' THEN 1
                            WHEN '50-100' THEN 2
                            WHEN '100-250' THEN 3
                            WHEN '250-500' THEN 4
                            WHEN '500-1000' THEN 5
                            WHEN '1000+' THEN 6
                        END";
            $priceResult = $conn->query($priceQuery);
            $priceRanges = [];
            $priceCounts = [];

            if ($priceResult) {
                while ($row = $priceResult->fetch_assoc()) {
                    $priceRanges[] = '$' . $row['price_range'];
                    $priceCounts[] = $row['count'];
                }
            }
            ?>

            const priceCtx = document.getElementById('priceChart').getContext('2d');
            new Chart(priceCtx, {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode($priceRanges); ?>,
                    datasets: [{
                        data: <?php echo json_encode($priceCounts); ?>,
                        backgroundColor: [
                            'rgba(58, 134, 255, 0.7)',
                            'rgba(131, 56, 236, 0.7)',
                            'rgba(255, 0, 110, 0.7)',
                            'rgba(255, 193, 7, 0.7)',
                            'rgba(76, 175, 80, 0.7)',
                            'rgba(255, 87, 34, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });

            // Feature listing functionality
            const featureButtons = document.querySelectorAll('.feature-listing');
            featureButtons.forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    const listingId = this.getAttribute('data-id');
                    alert('Feature functionality would be implemented here for listing ID: ' + listingId);
                    // Actual implementation would involve AJAX call to update featured status
                });
            });
        });
    </script>
</div>