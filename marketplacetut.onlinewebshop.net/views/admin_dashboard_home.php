<!-- Dashboard Overview Content -->
<section class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon users">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-details">
            <h3>Total Users</h3>
            <p class="stat-number"><?php echo $totalUsers; ?></p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon listings">
            <i class="fas fa-list"></i>
        </div>
        <div class="stat-details">
            <h3>Total Listings</h3>
            <p class="stat-number"><?php echo $totalListings; ?></p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon messages">
            <i class="fas fa-comments"></i>
        </div>
        <div class="stat-details">
            <h3>Messages</h3>
            <p class="stat-number"><?php echo $totalMessages; ?></p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon active">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-details">
            <h3>Active Chats</h3>
            <?php
            // Count active chat sessions (had activity in the last 24 hours)
            $activeChatsQuery = "SELECT COUNT(*) as total FROM chat_sessions 
                               WHERE last_message_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            $activeChatsResult = $conn->query($activeChatsQuery);
            $activeChats = 0;
            if ($activeChatsResult) {
                $row = $activeChatsResult->fetch_assoc();
                $activeChats = $row['total'];
            }
            ?>
            <p class="stat-number"><?php echo $activeChats; ?></p>
        </div>
    </div>
</section>

<div class="dashboard-content">
    <section class="recent-section">
        <div class="section-header">
            <h3>Recent Users</h3>
            <a href="admin_dashboard.php?page=users" class="view-all">View All</a>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUsers as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['name']) . ' ' . htmlspecialchars($user['surname']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><span
                                    class="badge <?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recentUsers)): ?>
                        <tr>
                            <td colspan="5" class="no-data">No users found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="recent-section">
        <div class="section-header">
            <h3>Recent Listings</h3>
            <a href="admin_dashboard.php?page=listings" class="view-all">View All</a>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Listed By</th>
                        <th>Listed Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentListings as $listing): ?>
                        <tr>
                            <td><?php echo $listing['id']; ?></td>
                            <td><?php echo htmlspecialchars($listing['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($listing['category']); ?></td>
                            <td>$<?php echo number_format($listing['price'], 2); ?></td>
                            <td><?php echo isset($listing['name']) ? htmlspecialchars($listing['name'] . ' ' . $listing['surname']) : 'Unknown'; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($listing['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recentListings)): ?>
                        <tr>
                            <td colspan="6" class="no-data">No listings found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="activity-section">
        <div class="section-header">
            <h3>System Activity</h3>
        </div>
        <div class="activity-chart">
            <?php
            // Get activity statistics for the last 7 days
            $activityQuery = "SELECT 
                DATE(created_at) as date,
                COUNT(*) as count
                FROM (
                    SELECT created_at FROM Users WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
                    UNION ALL
                    SELECT created_at FROM listings WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
                    UNION ALL
                    SELECT created_at FROM chat_messages WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
                ) as all_activity
                GROUP BY DATE(created_at)
                ORDER BY date";

            $activityResult = $conn->query($activityQuery);
            $activityData = [];
            $dates = [];
            $counts = [];

            if ($activityResult) {
                while ($row = $activityResult->fetch_assoc()) {
                    $dates[] = date('M d', strtotime($row['date']));
                    $counts[] = $row['count'];
                }
            }

            // Fill in missing dates with zero counts
            $period = new DatePeriod(
                new DateTime(date('Y-m-d', strtotime('-6 days'))),
                new DateInterval('P1D'),
                new DateTime(date('Y-m-d', strtotime('+1 day')))
            );

            $fullDates = [];
            $fullCounts = [];

            foreach ($period as $date) {
                $dateStr = $date->format('M d');
                $index = array_search($dateStr, $dates);
                $fullDates[] = $dateStr;
                $fullCounts[] = ($index !== false) ? $counts[$index] : 0;
            }
            ?>

            <div class="chart-container">
                <canvas id="activityChart"></canvas>
            </div>

            <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
            <script>
                // Create activity chart
                document.addEventListener('DOMContentLoaded', function () {
                    const ctx = document.getElementById('activityChart').getContext('2d');
                    const activityChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode($fullDates); ?>,
                            datasets: [{
                                label: 'System Activity',
                                data: <?php echo json_encode($fullCounts); ?>,
                                backgroundColor: 'rgba(58, 134, 255, 0.2)',
                                borderColor: 'rgba(58, 134, 255, 1)',
                                tension: 0.4,
                                fill: true,
                                borderWidth: 2,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        display: true,
                                        drawBorder: false,
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                });
            </script>
        </div>
    </section>
</div>