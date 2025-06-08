<?php
// Check if user is logged in and is admin
ob_start();
session_start();
if (!isset($_SESSION['userArray']) || $_SESSION['userArray']['role'] !== 'admin') {
    // Redirect to login page if not logged in or not admin
    header("Location: login.php");
    exit();
}

require_once '../includes/database/connection.php';

// Get statistics for dashboard
$totalUsers = 0;
$totalListings = 0;
$totalMessages = 0;
$recentUsers = [];
$recentListings = [];

// Count total users
$userQuery = "SELECT COUNT(*) as total FROM Users";
$userResult = $conn->query($userQuery);
if ($userResult) {
    $row = $userResult->fetch_assoc();
    $totalUsers = $row['total'];
}

// Count total listings
$listingQuery = "SELECT COUNT(*) as total FROM listings";
$listingResult = $conn->query($listingQuery);
if ($listingResult) {
    $row = $listingResult->fetch_assoc();
    $totalListings = $row['total'];
}

// Count total messages
$messageQuery = "SELECT COUNT(*) as total FROM chat_messages";
$messageResult = $conn->query($messageQuery);
if ($messageResult) {
    $row = $messageResult->fetch_assoc();
    $totalMessages = $row['total'];
}

// Get recent users
$recentUsersQuery = "SELECT id, name, surname, email, role, created_at FROM Users ORDER BY created_at DESC LIMIT 5";
$recentUsersResult = $conn->query($recentUsersQuery);
if ($recentUsersResult) {
    while ($row = $recentUsersResult->fetch_assoc()) {
        $recentUsers[] = $row;
    }
}

// Get recent listings
$recentListingsQuery = "SELECT l.id, l.item_name, l.category, l.price, l.created_at, u.name, u.surname 
                        FROM listings l
                        LEFT JOIN Users u ON l.user_id = u.id
                        ORDER BY l.created_at DESC LIMIT 5";
$recentListingsResult = $conn->query($recentListingsQuery);
if ($recentListingsResult) {
    while ($row = $recentListingsResult->fetch_assoc()) {
        $recentListings[] = $row;
    }
}

// Handle user deletion if requested
if (isset($_GET['delete_user']) && is_numeric($_GET['delete_user'])) {
    $userId = $_GET['delete_user'];
    $deleteQuery = "DELETE FROM Users WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $userId);
    if ($stmt->execute()) {
        $successMessage = "User deleted successfully";
    } else {
        $errorMessage = "Error deleting user";
    }
    $stmt->close();

    // Redirect to remove the query parameter
    header("Location: admin_dashboard.php");
    exit();
}

// Handle listing deletion if requested
if (isset($_GET['delete_listing']) && is_numeric($_GET['delete_listing'])) {
    $listingId = $_GET['delete_listing'];
    $deleteQuery = "DELETE FROM listings WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $listingId);
    if ($stmt->execute()) {
        $successMessage = "Listing deleted successfully";
    } else {
        $errorMessage = "Error deleting listing";
    }
    $stmt->close();

    // Redirect to remove the query parameter
    header("Location: admin_dashboard.php");
    exit();
}

// Get page content based on navigation
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../public/css/admindash.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1>Admin Panel</h1>
            </div>
            <div class="admin-info">
                <div class="admin-avatar">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="admin-details">
                    <p class="admin-name">
                        <?php echo $_SESSION['userArray']['name'] . ' ' . $_SESSION['userArray']['surname']; ?></p>
                    <p class="admin-email"><?php echo $_SESSION['userArray']['email']; ?></p>
                </div>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="<?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                        <a href="admin_dashboard.php?page=dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    </li>
                    <li class="<?php echo $currentPage === 'users' ? 'active' : ''; ?>">
                        <a href="admin_dashboard.php?page=users"><i class="fas fa-users"></i> Users</a>
                    </li>
                    <li class="<?php echo $currentPage === 'listings' ? 'active' : ''; ?>">
                        <a href="admin_dashboard.php?page=listings"><i class="fas fa-list"></i> Listings</a>
                    </li>
                    <li class="<?php echo $currentPage === 'messages' ? 'active' : ''; ?>">
                        <a href="admin_dashboard.php?page=messages"><i class="fas fa-comments"></i> Messages</a>
                    </li>
                    <li class="<?php echo $currentPage === 'settings' ? 'active' : ''; ?>">
                        <a href="admin_dashboard.php?page=settings"><i class="fas fa-cog"></i> Settings</a>
                    </li>
                    <li>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="header">
                <div class="page-title">
                    <?php
                    switch ($currentPage) {
                        case 'dashboard':
                            echo '<h2><i class="fas fa-tachometer-alt"></i> Dashboard Overview</h2>';
                            break;
                        case 'users':
                            echo '<h2><i class="fas fa-users"></i> User Management</h2>';
                            break;
                        case 'listings':
                            echo '<h2><i class="fas fa-list"></i> Listing Management</h2>';
                            break;
                        case 'messages':
                            echo '<h2><i class="fas fa-comments"></i> Message Center</h2>';
                            break;
                        case 'settings':
                            echo '<h2><i class="fas fa-cog"></i> System Settings</h2>';
                            break;
                        default:
                            echo '<h2><i class="fas fa-tachometer-alt"></i> Dashboard Overview</h2>';
                    }
                    ?>
                </div>
                <div class="header-actions">
                    <div class="search-box">
                        <input type="text" placeholder="Search...">
                        <button><i class="fas fa-search"></i></button>
                    </div>
                    <div class="notifications">
                        <i class="fas fa-bell"></i>
                        <span class="notification-count">3</span>
                    </div>
                </div>
            </header>

            <?php if (isset($successMessage)): ?>
                <div class="alert success">
                    <p><?php echo $successMessage; ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($errorMessage)): ?>
                <div class="alert error">
                    <p><?php echo $errorMessage; ?></p>
                </div>
            <?php endif; ?>

            <div class="content">
                <?php
                // Load content based on selected page
                switch ($currentPage) {
                    case 'dashboard':
                        include('admin_dashboard_home.php');
                        break;
                    case 'users':
                        include('admin_users.php');
                        break;
                    case 'listings':
                        include('admin_listings.php');
                        break;
                    case 'messages':
                        include('admin_messages.php');
                        break;
                    case 'settings':
                        include('admin_settings.php');
                        break;
                    default:
                        include('admin_dashboard_home.php');
                }
                ?>
            </div>
        </main>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.addEventListener('DOMContentLoaded', function () {
            const menuToggle = document.querySelector('.menu-toggle');
            const sidebar = document.querySelector('.sidebar');

            if (menuToggle) {
                menuToggle.addEventListener('click', function () {
                    sidebar.classList.toggle('open');
                });
            }

            // Close alerts after 5 seconds
            setTimeout(function () {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function (alert) {
                    alert.style.display = 'none';
                });
            }, 5000);
        });
    </script>
</body>

</html>