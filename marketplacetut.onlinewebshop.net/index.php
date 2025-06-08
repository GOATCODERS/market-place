<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyTrade- Shop Unique Products from Independent Sellers</title>
    
    <link rel="stylesheet" href="public/css/styles.css">
      <style>
        .user-icon {
            display: inline-block;
            width: 52px;
            height: 40px;
            background-color: #007bff; /* Bootstrap primary color */
            color: white;
            font-weight: bold;
            text-align: center;
            line-height: 40px;
            border-radius: 40%;
            font-size: 18px;
            border: none;
            margin-left: 12px;
        }

        /* Role selection styles */
        .role-selection {
            text-align: center;
            padding: 30px;
        }
        
        .role-options {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }
        
        .role-option {
            padding: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            width: 45%;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .role-option:hover {
            border-color: #007bff;
            background-color: #f8f9fa;
        }
        
        .role-option.selected {
            border-color: #007bff;
            background-color: #e7f1ff;
        }
        
        .role-option i {
            font-size: 32px;
            color: #007bff;
            margin-bottom: 10px;
        }
        
        .role-option h4 {
            margin-bottom: 10px;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <a href="#" class="logo">Easy<span>Trade</span></a>
            <div class="search-bar">
                <input type="text" placeholder="Search for products...">
                <button>🔍</button>
            </div>
            <div class="nav-links">
                
                <a href="views/Categories.php">Categories</a>
                
               <?php if (isset($_SESSION['userArray'])): ?>
                        <?php if($_SESSION['userArray']['role']=='seller'):?>
                              <a href="views/sell.php">Sell</a>
                        <?php endif;?>
                    <?php else: ?>
                        <!-- <a href="views/sign.php" onclick="return confirm('You need to sign in first!');">Sell</a> -->
                    <?php endif; ?>
                    <!-- user icon  -->
                    <?php if (isset($_SESSION['userArray'])): ?>
                        <?php
                        $user = $_SESSION['userArray'];
                        $initial = strtoupper(substr($user['name'], 0, 1)); // Get first letter of the name
                        ?>
                        <div class="dropdown">
                            <button class="user-icon" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo $initial; ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="views/update_user.php">Update User</a></li>
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#roleModal">Update
                                        Role</a></li>
                                <li><a class="dropdown-item text-danger" href="controllers/logout.php">Lock Out</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="views/sign.php">Sign In</a>
                        <a href="views/register.php">Register</a>
                    <?php endif; ?>
                    <!-- user icon  -->
                </div>
            </div>
        </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Discover Unique Products from Independent Sellers</h1>
            <p>Browse thousands of handcrafted, vintage, and unique products from sellers around the world.</p>
            <div class="hero-buttons">
                <a href="views/Categories.php" class="btn btn-primary">Start Shopping</a>
                <a href="#" class="btn btn-outline"
                    style="background-color: transparent; color: white; border-color: white;">Become a Seller</a>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories">
        <div class="container">
            <h2 class="section-title">Popular Categories</h2>
            <div class="categories-grid">
                <a href="views/Categories.php?category=Electronics" class="category-card">
                    <div class="category-img">🖥️</div>
                    <div class="category-name">Electronics</div>
                </a>
                <a href="views/Categories.php?category=Fashion" class="category-card">
                    <div class="category-img">👕</div>
                    <div class="category-name">Fashion</div>
                </a>
                <a href="views/Categories.php?category=Home & Garden" class="category-card">
                    <div class="category-img">🏠</div>
                    <div class="category-name">Home & Garden</div>
                </a>
                <a href="views/Categories.php?category=Toys & Games" class="category-card">
                    <div class="category-img">🎮</div>
                    <div class="category-name">Toys & Games</div>
                </a>
                <a href="views/Categories.php?category=Books & Media" class="category-card">
                    <div class="category-img">📚</div>
                    <div class="category-name">Books & Media</div>
                </a>
                <a href="views/Categories.php?category=Handmade" class="category-card">
                    <div class="category-img">⚒️</div>
                    <div class="category-name">Handmade</div>
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products">
        <div class="container">
            <h2 class="section-title">Featured Products</h2>
            <div class="products-grid">
                <div class="product-card">
                    <div class="product-img"><img src="public/images/HandmadeWoodenBowl.jpeg" alt=""></div>
                    <div class="product-info">
                        <h3 class="product-title">Handcrafted Wooden Bowl</h3>
                        <p class="product-seller">By Artisan Woodworks</p>
                        <p class="product-price">R45.99</p>
                        <a href="#" class="btn btn-primary">View Product</a>
                    </div>
                </div>
                <div class="product-card">
                    <div class="product-img"><img src="public/images/EAR.jpeg" alt=""></div>
                    <div class="product-info">
                        <h3 class="product-title">Wireless Earbuds Pro</h3>
                        <p class="product-seller">By Tech Innovations</p>
                        <p class="product-price">R89.99</p>
                        <a href="#" class="btn btn-primary">View Product</a>
                    </div>
                </div>
                <div class="product-card">
                    <div class="product-img"><img src="public/images/SHIRT.jpeg" alt=""></div>
                    <div class="product-info">
                        <h3 class="product-title">Organic Cotton T-Shirt</h3>
                        <p class="product-seller">By Eco Apparel</p>
                        <p class="product-price">R29.99</p>
                        <a href="#" class="btn btn-primary">View Product</a>
                    </div>
                </div>
                <div class="product-card">
                    <div class="product-img"><img src="public/images/House.jpeg" alt=""></div>
                    <div class="product-info">
                        <h3 class="product-title">Smart Home Hub</h3>
                        <p class="product-seller">By Future Living</p>
                        <p class="product-price">R129.99</p>
                        <a href="#" class="btn btn-primary">View Product</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Seller Section -->
    <section class="seller-section">
        <div class="container">
            <div class="seller-content">
                <h2 class="section-title">Become a Seller Today</h2>
                <p>Join thousands of independent sellers who are growing their business on EasyTradePlace. It's quick and
                    easy to get started.</p>
                <a href="#" class="btn btn-primary">Start Selling</a>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials">
        <div class="container">
            <h2 class="section-title">What Our Users Say</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <p class="testimonial-text">"I've been selling my handcrafted jewelry on EasyTrade for over a year
                        now, and it's been an amazing platform to reach customers worldwide."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">JD</div>
                        <div class="author-info">
                            <h4>Jane Doe</h4>
                            <p>Seller since 2024</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"As a buyer, I love that I can discover unique products that I wouldn't
                        find anywhere else. The registration process was simple, and now I can easily purchase from my
                        favorite sellers."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">JS</div>
                        <div class="author-info">
                            <h4>John Smith</h4>
                            <p>Buyer since 2024</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"EasyTrade has transformed my small business. The platform is
                        intuitive, and I've been able to connect with customers who truly appreciate my craft."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">AW</div>
                        <div class="author-info">
                            <h4>Alex Wong</h4>
                            <p>Seller since 2023</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-container">
                <div class="footer-column">
                    <h3>Shop</h3>
                    <ul>
                        <li><a href="views/Categories.php">Categories</a></li>
                        <li><a href="#">Featured Products</a></li>
                        <li><a href="#">New Arrivals</a></li>
                        <li><a href="#">Best Sellers</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Sell</h3>
                    <ul>
                        <li><a href="#">Start Selling</a></li>
                        <li><a href="#">Seller Dashboard</a></li>
                        <li><a href="#">Seller Guidelines</a></li>
                        <li><a href="#">Success Stories</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>About</h3>
                    <ul>
                        <li><a href="#">Our Story</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Press</a></li>
                        <li><a href="#">Blog</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Help</h3>
                    <ul>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">FAQs</a></li>
                        <li><a href="#">Shipping</a></li>
                        <li><a href="#">Returns</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2025 EasyTrade. All rights reserved.</p>
            </div>
        </div>
    </footer>

    
    <!-- Role Selection Modal -->
    <div class="modal fade" id="roleModal" tabindex="-1" aria-labelledby="roleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="roleModalLabel">Select Your Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="role-selection">
                        <p>Please select your primary role on EasyTrade:</p>
                        
                        <div class="role-options">
                            <div class="role-option" onclick="selectRole('buyer')">
                                <i class="bi bi-cart"></i>
                                <h4>Buyer</h4>
                                <p>Browse and purchase products from our marketplace</p>
                            </div>
                            
                            <div class="role-option" onclick="selectRole('seller')">
                                <i class="bi bi-shop"></i>
                                <h4>Seller</h4>
                                <p>List and sell your products to our community</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveRoleBtn" disabled>Save Changes</button>
                </div>
            </div>
        </div>
    </div>

      <script>
        let selectedRole = null;
        
        function selectRole(role) {
            selectedRole = role;
            
            // Visual feedback
            document.querySelectorAll('.role-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Find the clicked option and add selected class
            document.querySelector(`.role-option:nth-child(${role === 'buyer' ? '1' : '2'})`).classList.add('selected');
            
            // Enable the save button
            document.getElementById('saveRoleBtn').disabled = false;
        }
        
        document.getElementById('saveRoleBtn').addEventListener('click', function() {
            if (selectedRole) {
                // Here you would typically send an AJAX request to update the user's role
                // For example:
                fetch('controllers/update_role.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'role=' + selectedRole
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Your role has been updated to ' + selectedRole);
                        
                        // If needed, redirect user based on their new role
                        if (selectedRole === 'seller') {
                            window.location.href = 'views/seller_dashboard.php';
                        } else {
                            window.location.href = 'index.php';
                        }
                    } else {
                        alert('Error updating role: ' + data.message);
                    }
                })
                .catch(error => {
                    // console.error('Error:', error);
                    // alert('An error occurred while updating your role.'+'\n'+error);
                });
                
                // Close the modal
                var roleModal = bootstrap.Modal.getInstance(document.getElementById('roleModal'));
                roleModal.hide();
            }
        });
    </script>
</body>

</html>