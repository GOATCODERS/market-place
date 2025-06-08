<?php
// Process actions if any
$messageClass = '';
$message = '';

// Handle user role update
if (isset($_POST['update_role'])) {
    $userId = $_POST['user_id'];
    $newRole = $_POST['role'];

    // Validate role
    $validRoles = ['buyer', 'seller', 'admin', 'customer'];
    if (in_array($newRole, $validRoles)) {
        $updateQuery = "UPDATE Users SET role = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("si", $newRole, $userId);

        if ($stmt->execute()) {
            $message = "User role updated successfully";
            $messageClass = "success";
        } else {
            $message = "Error updating user role";
            $messageClass = "error";
        }
        $stmt->close();
    } else {
        $message = "Invalid role selected";
        $messageClass = "error";
    }
}

// Get search filters
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$roleFilter = isset($_GET['role']) ? $_GET['role'] : '';

// Pagination settings
$page = isset($_GET['p']) ? (int) $_GET['p'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Build query
$userQuery = "SELECT * FROM Users WHERE 1=1";
$countQuery = "SELECT COUNT(*) as total FROM Users WHERE 1=1";

$queryParams = [];
$types = "";

if (!empty($searchTerm)) {
    $searchParam = "%$searchTerm%";
    $userQuery .= " AND (name LIKE ? OR surname LIKE ? OR email LIKE ?)";
    $countQuery .= " AND (name LIKE ? OR surname LIKE ? OR email LIKE ?)";
    array_push($queryParams, $searchParam, $searchParam, $searchParam);
    $types .= "sss";
}

if (!empty($roleFilter)) {
    $userQuery .= " AND role = ?";
    $countQuery .= " AND role = ?";
    array_push($queryParams, $roleFilter);
    $types .= "s";
}

// Get total count
$countStmt = $conn->prepare($countQuery);
if (!empty($types) && !empty($queryParams)) {
    $countStmt->bind_param($types, ...$queryParams);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalUsers = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalUsers / $perPage);
$countStmt->close();

// Add pagination parameters to user query
$userQuery .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
array_push($queryParams, $perPage, $offset);
$types .= "ii";

// Get users
$userStmt = $conn->prepare($userQuery);
if (!empty($types) && !empty($queryParams)) {
    $userStmt->bind_param($types, ...$queryParams);
}
$userStmt->execute();
$usersResult = $userStmt->get_result();
$users = [];
while ($row = $usersResult->fetch_assoc()) {
    $users[] = $row;
}
$userStmt->close();
?>

<div class="admin-users-page">
    <?php if (!empty($message)): ?>
        <div class="alert <?php echo $messageClass; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="filters-container">
        <form method="GET" action="admin_dashboard.php" class="filters-form">
            <input type="hidden" name="page" value="users">

            <div class="form-group">
                <input type="text" name="search" placeholder="Search users..."
                    value="<?php echo htmlspecialchars($searchTerm); ?>">
            </div>

            <div class="form-group">
                <select name="role">
                    <option value="">All Roles</option>
                    <option value="buyer" <?php echo $roleFilter === 'buyer' ? 'selected' : ''; ?>>Buyer</option>
                    <option value="seller" <?php echo $roleFilter === 'seller' ? 'selected' : ''; ?>>Seller</option>
                    <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="customer" <?php echo $roleFilter === 'customer' ? 'selected' : ''; ?>>Customer</option>
                </select>
            </div>

            <button type="submit" class="btn-primary">Filter</button>
            <a href="admin_dashboard.php?page=users" class="btn-secondary">Reset</a>

            <div class="export-buttons">
                <a href="export_users.php?format=pdf<?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '';
                echo !empty($roleFilter) ? '&role=' . urlencode($roleFilter) : ''; ?>"
                    class="btn-export" target="_blank">
                    <i class="fas fa-file-pdf"></i> Export as PDF
                </a>
                <a href="export_users.php?format=docx<?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '';
                echo !empty($roleFilter) ? '&role=' . urlencode($roleFilter) : ''; ?>"
                    class="btn-export">
                    <i class="fas fa-file-word"></i> Export as DOCX
                </a>
            </div>
        </form>
    </div>

    <div class="users-table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['name'] . ' ' . $user['surname']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <form method="POST" action="admin_dashboard.php?page=users" class="inline-form role-form">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <select name="role" class="role-select">
                                    <option value="buyer" <?php echo $user['role'] === 'buyer' ? 'selected' : ''; ?>>Buyer
                                    </option>
                                    <option value="seller" <?php echo $user['role'] === 'seller' ? 'selected' : ''; ?>>Seller
                                    </option>
                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin
                                    </option>
                                    <option value="customer" <?php echo $user['role'] === 'customer' ? 'selected' : ''; ?>>
                                        Customer</option>
                                </select>
                                <button type="submit" name="update_role" class="btn-sm btn-primary">Update</button>
                            </form>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td class="actions">
                            <a href="view_user.php?id=<?php echo $user['id']; ?>" class="btn-sm btn-info"
                                title="View Details"><i class="fas fa-eye"></i></a>
                            <a href="#" class="btn-sm btn-warning edit-user" data-id="<?php echo $user['id']; ?>"
                                title="Edit User"><i class="fas fa-edit"></i></a>
                            <a href="admin_dashboard.php?page=users&delete_user=<?php echo $user['id']; ?>"
                                class="btn-sm btn-danger"
                                onclick="return confirm('Are you sure you want to delete this user?')"
                                title="Delete User"><i class="fas fa-trash-alt"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="no-data">No users found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="admin_dashboard.php?page=users&p=<?php echo $page - 1; ?>&search=<?php echo urlencode($searchTerm); ?>&role=<?php echo urlencode($roleFilter); ?>"
                    class="pagination-btn">&laquo; Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="admin_dashboard.php?page=users&p=<?php echo $i; ?>&search=<?php echo urlencode($searchTerm); ?>&role=<?php echo urlencode($roleFilter); ?>"
                    class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="admin_dashboard.php?page=users&p=<?php echo $page + 1; ?>&search=<?php echo urlencode($searchTerm); ?>&role=<?php echo urlencode($roleFilter); ?>"
                    class="pagination-btn">Next &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="user-stats">
        <div class="stat-card">
            <h4>User Role Distribution</h4>
            <div class="chart-container">
                <canvas id="userRoleChart"></canvas>
            </div>
        </div>

        <div class="stat-card">
            <h4>User Registration Trends</h4>
            <div class="chart-container">
                <canvas id="userRegistrationChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Role distribution chart
            <?php
            $roleQuery = "SELECT role, COUNT(*) as count FROM Users GROUP BY role";
            $roleResult = $conn->query($roleQuery);
            $roles = [];
            $roleCounts = [];

            if ($roleResult) {
                while ($row = $roleResult->fetch_assoc()) {
                    $roles[] = ucfirst($row['role']);
                    $roleCounts[] = $row['count'];
                }
            }
            ?>

            const roleCtx = document.getElementById('userRoleChart').getContext('2d');
            new Chart(roleCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($roles); ?>,
                    datasets: [{
                        data: <?php echo json_encode($roleCounts); ?>,
                        backgroundColor: [
                            'rgba(58, 134, 255, 0.7)',
                            'rgba(131, 56, 236, 0.7)',
                            'rgba(255, 0, 110, 0.7)',
                            'rgba(255, 193, 7, 0.7)'
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

            // Registration trends chart
            <?php
            $trendsQuery = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
                        FROM Users 
                        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                        ORDER BY month";
            $trendsResult = $conn->query($trendsQuery);
            $months = [];
            $counts = [];

            if ($trendsResult) {
                while ($row = $trendsResult->fetch_assoc()) {
                    $months[] = date('M Y', strtotime($row['month'] . '-01'));
                    $counts[] = $row['count'];
                }
            }
            ?>

            const trendsCtx = document.getElementById('userRegistrationChart').getContext('2d');
            new Chart(trendsCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($months); ?>,
                    datasets: [{
                        label: 'New Users',
                        data: <?php echo json_encode($counts); ?>,
                        backgroundColor: 'rgba(131, 56, 236, 0.7)',
                        borderColor: 'rgba(131, 56, 236, 1)',
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

            // Edit user functionality
            const editButtons = document.querySelectorAll('.edit-user');
            editButtons.forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    const userId = this.getAttribute('data-id');
                    window.location.href = `edit_user.php?id=${userId}`;
                });
            });
        });
    </script>
</div>