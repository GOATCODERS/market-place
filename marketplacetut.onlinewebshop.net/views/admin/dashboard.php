<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce Admin Dashboard</title>
    <link rel="stylesheet" href="../../public/css/admindash.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</head>

<body>
    <div class="dashboard">
        <div class="sidebar">
            <div class="logo">
                <div class="logo-circle"></div>
                EasyTrade
            </div>
            <div class="nav-links">
                <div class="nav-link active">
                    <i class="fas fa-th-large icon"></i>
                    <span>Dashboard</span>
                </div>
                <div class="nav-link">
                    <i class="fas fa-shopping-cart icon"></i>
                    <span>Orders</span>
                </div>
                <div class="nav-link">
                    <i class="fas fa-box icon"></i>
                    <span>Products</span>
                </div>
                <div class="nav-link">
                    <i class="fas fa-users icon"></i>
                    <span>Customers</span>
                </div>
                <div class="nav-link">
                    <i class="fas fa-chart-line icon"></i>
                    <span>Analytics</span>
                </div>
                <div class="nav-link">
                    <i class="fas fa-cog icon"></i>
                    <span>Settings</span>
                </div>
            </div>
        </div>
        <div class="content">
            <div class="header">
                <div class="page-title">Dashboard Overview</div>
                <div class="user-profile">
                    <div class="user-avatar">AM</div>
                    <div class="user-name">Admin</div>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">TOTAL SALES</div>
                        <div class="stat-icon" style="background-color: var(--primary);">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="stat-value">R24,571</div>
                    <div class="stat-change">
                        <i class="fas fa-arrow-up"></i> 12.5% from last month
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">TOTAL ORDERS</div>
                        <div class="stat-icon" style="background-color: var(--secondary);">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                    </div>
                    <div class="stat-value">1,204</div>
                    <div class="stat-change">
                        <i class="fas fa-arrow-up"></i> 8.2% from last month
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">TOTAL CUSTOMERS</div>
                        <div class="stat-icon" style="background-color: var(--accent);">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-value">5,783</div>
                    <div class="stat-change">
                        <i class="fas fa-arrow-up"></i> 4.7% from last month
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">CONVERSION RATE</div>
                        <div class="stat-icon" style="background-color: #28a745;">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                    </div>
                    <div class="stat-value">3.24%</div>
                    <div class="stat-change negative-change">
                        <i class="fas fa-arrow-down"></i> 1.2% from last month
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="chart-container">
                    <div class="chart-header">
                        <div class="chart-title">Sales Analytics</div>
                        <div class="chart-options">
                            <select>
                                <option>Last 7 Days</option>
                                <option>Last 30 Days</option>
                                <option>Last 90 Days</option>
                            </select>
                        </div>
                    </div>
                    <div class="chart">
                        <div class="chart-bar" style="height: 50%;">
                            <div class="chart-label">Mon</div>
                        </div>
                        <div class="chart-bar" style="height: 65%;">
                            <div class="chart-label">Tue</div>
                        </div>
                        <div class="chart-bar" style="height: 80%;">
                            <div class="chart-label">Wed</div>
                        </div>
                        <div class="chart-bar" style="height: 95%;">
                            <div class="chart-label">Thu</div>
                        </div>
                        <div class="chart-bar" style="height: 75%;">
                            <div class="chart-label">Fri</div>
                        </div>
                        <div class="chart-bar" style="height: 60%;">
                            <div class="chart-label">Sat</div>
                        </div>
                        <div class="chart-bar" style="height: 45%;">
                            <div class="chart-label">Sun</div>
                        </div>
                    </div>
                </div>
                <div class="order-list">
                    <div class="order-list-header">
                        <div class="order-list-title">Recent Orders</div>
                        <div class="view-all">View All</div>
                    </div>
                    <div class="order-item">
                        <div class="order-info">
                            <div class="order-id">#ORD-7812</div>
                            <div class="order-date">Apr 2, 2025</div>
                        </div>
                        <div class="order-amount">$342.50</div>
                        <div class="status-badge status-delivered">Delivered</div>
                    </div>
                    <div class="order-item">
                        <div class="order-info">
                            <div class="order-id">#ORD-7811</div>
                            <div class="order-date">Apr 2, 2025</div>
                        </div>
                        <div class="order-amount">R128.75</div>
                        <div class="status-badge status-processing">Processing</div>
                    </div>
                    <div class="order-item">
                        <div class="order-info">
                            <div class="order-id">#ORD-7810</div>
                            <div class="order-date">Apr 1, 2025</div>
                        </div>
                        <div class="order-amount">R425.00</div>
                        <div class="status-badge status-delivered">Delivered</div>
                    </div>
                    <div class="order-item">
                        <div class="order-info">
                            <div class="order-id">#ORD-7809</div>
                            <div class="order-date">Apr 1, 2025</div>
                        </div>
                        <div class="order-amount">R65.25</div>
                        <div class="status-badge status-cancelled">Cancelled</div>
                    </div>
                    <div class="order-item">
                        <div class="order-info">
                            <div class="order-id">#ORD-7808</div>
                            <div class="order-date">Mar 31, 2025</div>
                        </div>
                        <div class="order-amount">R215.80</div>
                        <div class="status-badge status-delivered">Delivered</div>
                    </div>
                </div>
            </div>

            <div class="products-table">
                <div class="table-header">
                    <div class="table-title">Top Products</div>
                    <input type="text" class="search-input" placeholder="Search products...">
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Sales</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="product-info">
                                    <div class="product-image"></div>
                                    <div>
                                        <div class="product-name">Wireless Earbuds</div>
                                        <div class="product-category">Electronics</div>
                                    </div>
                                </div>
                            </td>
                            <td>Electronics</td>
                            <td>$89.99</td>
                            <td><span class="stock-level in-stock">In Stock (45)</span></td>
                            <td>248</td>
                            <td>
                                <div class="action-buttons">
                                    <div class="action-btn edit-btn">
                                        <i class="fas fa-edit"></i>
                                    </div>
                                    <div class="action-btn delete-btn">
                                        <i class="fas fa-trash"></i>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="product-info">
                                    <div class="product-image"></div>
                                    <div>
                                        <div class="product-name">Smart Watch</div>
                                        <div class="product-category">Electronics</div>
                                    </div>
                                </div>
                            </td>
                            <td>Electronics</td>
                            <td>$159.99</td>
                            <td><span class="stock-level low-stock">Low Stock (8)</span></td>
                            <td>187</td>
                            <td>
                                <div class="action-buttons">
                                    <div class="action-btn edit-btn">
                                        <i class="fas fa-edit"></i>
                                    </div>
                                    <div class="action-btn delete-btn">
                                        <i class="fas fa-trash"></i>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="product-info">
                                    <div class="product-image"></div>
                                    <div>
                                        <div class="product-name">Leather Backpack</div>
                                        <div class="product-category">Fashion</div>
                                    </div>
                                </div>
                            </td>
                            <td>Fashion</td>
                            <td>$79.99</td>
                            <td><span class="stock-level in-stock">In Stock (32)</span></td>
                            <td>165</td>
                            <td>
                                <div class="action-buttons">
                                    <div class="action-btn edit-btn">
                                        <i class="fas fa-edit"></i>
                                    </div>
                                    <div class="action-btn delete-btn">
                                        <i class="fas fa-trash"></i>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="product-info">
                                    <div class="product-image"></div>
                                    <div>
                                        <div class="product-name">Yoga Mat</div>
                                        <div class="product-category">Sports</div>
                                    </div>
                                </div>
                            </td>
                            <td>Sports</td>
                            <td>$29.95</td>
                            <td><span class="stock-level out-stock">Out of Stock</span></td>
                            <td>142</td>
                            <td>
                                <div class="action-buttons">
                                    <div class="action-btn edit-btn">
                                        <i class="fas fa-edit"></i>
                                    </div>
                                    <div class="action-btn delete-btn">
                                        <i class="fas fa-trash"></i>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="product-info">
                                    <div class="product-image"></div>
                                    <div>
                                        <div class="product-name">Coffee Maker</div>
                                        <div class="product-category">Home</div>
                                    </div>
                                </div>
                            </td>
                            <td>Home</td>
                            <td>$49.99</td>
                            <td><span class="stock-level in-stock">In Stock (21)</span></td>
                            <td>124</td>
                            <td>
                                <div class="action-buttons">
                                    <div class="action-btn edit-btn">
                                        <i class="fas fa-edit"></i>
                                    </div>
                                    <div class="action-btn delete-btn">
                                        <i class="fas fa-trash"></i>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>