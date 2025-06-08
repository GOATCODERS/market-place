<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce Admin Dashboard - Customers</title>
    <link rel="stylesheet" href="../../public/css/customer.css">
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
                <div class="nav-link">
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
                <div class="nav-link active">
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
                <div class="page-title">Customers</div>
                <div class="user-profile">
                    <div class="user-avatar">AM</div>
                    <div class="user-name">Admin</div>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">TOTAL CUSTOMERS</div>
                        <div class="stat-icon" style="background-color: var(--primary);">
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
                        <div class="stat-title">NEW CUSTOMERS</div>
                        <div class="stat-icon" style="background-color: var(--secondary);">
                            <i class="fas fa-user-plus"></i>
                        </div>
                    </div>
                    <div class="stat-value">247</div>
                    <div class="stat-change">
                        <i class="fas fa-arrow-up"></i> 8.2% from last month
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">ACTIVE CUSTOMERS</div>
                        <div class="stat-icon" style="background-color: #28a745;">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                    <div class="stat-value">3,542</div>
                    <div class="stat-change">
                        <i class="fas fa-arrow-up"></i> 2.3% from last month
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">AVERAGE LIFETIME VALUE</div>
                        <div class="stat-icon" style="background-color: var(--accent);">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="stat-value">R312.45</div>
                    <div class="stat-change negative-change">
                        <i class="fas fa-arrow-down"></i> 1.2% from last month
                    </div>
                </div>
            </div>

            <div class="top-controls">
                <div class="search-bar">
                    <input type="text" class="search-input" placeholder="Search customers...">
                    <button class="btn btn-outlined">
                        <i class="fas fa-filter"></i>
                        Filters
                    </button>
                </div>
                <div class="filter-group">
                    <select class="filter-select">
                        <option>All Customers</option>
                        <option>Active</option>
                        <option>Inactive</option>
                        <option>New</option>
                    </select>
                    <select class="filter-select">
                        <option>All Time</option>
                        <option>Last 30 Days</option>
                        <option>Last 90 Days</option>
                        <option>Last Year</option>
                    </select>
                    <button class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add Customer
                    </button>
                </div>
            </div>

            <div class="customers-table-container">
                <div class="table-header">
                    <div class="table-title">Customer List</div>
                    <div class="table-actions">
                        <button class="btn btn-outlined">
                            <i class="fas fa-download"></i>
                            Export
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="customers-table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                                <th>Last Purchase</th>
                                <th>Tags</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar">JD</div>
                                        <div class="customer-details">
                                            <div class="customer-name">John Doe</div>
                                            <div class="customer-email">johndoe@example.com</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="status-badge status-active">Active</span></td>
                                <td>24</td>
                                <td>$1,245.80</td>
                                <td>Apr 2, 2025</td>
                                <td>
                                    <span class="tag">Premium</span>
                                    <span class="tag">Loyal</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <div class="action-btn">
                                            <i class="fas fa-eye"></i>
                                        </div>
                                        <div class="action-btn">
                                            <i class="fas fa-edit"></i>
                                        </div>
                                        <div class="action-btn">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar">AS</div>
                                        <div class="customer-details">
                                            <div class="customer-name">Amanda Smith</div>
                                            <div class="customer-email">asmith@example.com</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="status-badge status-active">Active</span></td>
                                <td>18</td>
                                <td>R876.50</td>
                                <td>Mar 28, 2025</td>
                                <td>
                                    <span class="tag">Loyal</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <div class="action-btn">
                                            <i class="fas fa-eye"></i>
                                        </div>
                                        <div class="action-btn">
                                            <i class="fas fa-edit"></i>
                                        </div>
                                        <div class="action-btn">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar">RJ</div>
                                        <div class="customer-details">
                                            <div class="customer-name">Robert Johnson</div>
                                            <div class="customer-email">robert.j@example.com</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="status-badge status-new">New</span></td>
                                <td>2</td>
                                <td>R124.95</td>
                                <td>Apr 1, 2025</td>
                                <td>
                                    <span class="tag">New</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <div class="action-btn">
                                            <i class="fas fa-eye"></i>
                                        </div>
                                        <div class="action-btn">
                                            <i class="fas fa-edit"></i>
                                        </div>
                                        <div class="action-btn">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar">EW</div>
                                        <div class="customer-details">
                                            <div class="customer-name">Elena Wong</div>
                                            <div class="customer-email">elena.w@example.com</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="status-badge status-inactive">Inactive</span></td>
                                <td>5</td>
                                <td>R342.10</td>
                                <td>Jan 15, 2025</td>
                                <td>
                                    <span class="tag">Returning</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <div class="action-btn">
                                            <i class="fas fa-eye"></i>
                                        </div>
                                        <div class="action-btn">
                                            <i class="fas fa-edit"></i>
                                        </div>
                                        <div class="action-btn">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar">MP</div>
                                        <div class="customer-details">
                                            <div class="customer-name">Michael Peterson</div>
                                            <div class="customer-email">m.peterson@example.com</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="status-badge status-active">Active</span></td>
                                <td>31</td>
                                <td>R1,876.20</td>
                                <td>Mar 30, 2025</td>
                                <td>
                                    <span class="tag">Premium</span>
                                    <span class="tag">Loyal</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <div class="action-btn">
                                            <i class="fas fa-eye"></i>
                                        </div>
                                        <div class="action-btn">
                                            <i class="fas fa-edit"></i>
                                        </div>
                                        <div class="action-btn">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar">SL</div>
                                        <div class="customer-details">
                                            <div class="customer-name">Sarah Liu</div>
                                            <div class="customer-email">sarah.l@example.com</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="status-badge status-active">Active</span></td>
                                <td>12</td>
                                <td>R652.75</td>
                                <td>Mar 27, 2025</td>
                                <td>
                                    <span class="tag">Returning</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <div class="action-btn">
                                            <i class="fas fa-eye"></i>
                                        </div>
                                        <div class="action-btn">
                                            <i class="fas fa-edit"></i>
                                        </div>
                                        <div class="action-btn">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-avatar">DM</div>
                                        <div class="customer-details">
                                            <div class="customer-name">David Miller</div>
                                            <div class="customer-email">david.m@example.com</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="status-badge status-new">New</span></td>
                                <td>1</td>
                                <td>R89.99</td>
                                <td>Apr 2, 2025</td>
                                <td>
                                    <span class="tag">New</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <div class="action-btn">
                                            <i class="fas fa-eye"></i>
                                        </div>
                                        <div class="action-btn">
                                            <i class="fas fa-edit"></i>
                                        </div>
                                        <div class="action-btn">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- <div class="pagination">
                    <div class="pagination-info">Showing 1-7 of 1,250 customers</div>
                    <div class="pagination-controls">
                        <div class="pagination-button prev"><i class="fas fa-chevron-left"></i></div> -->
