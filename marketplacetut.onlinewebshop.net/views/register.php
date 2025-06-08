<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - EasyTrade</title>
    <link rel="stylesheet" href="../public/css/register.css">
</head>

<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <a href="#" class="logo">Easy<span>Trade</span></a>
            <div class="nav-links">
                <a href="../index.php">Home</a>
                <a href="Categories.php">Categories</a>
                <!-- <a href="#">Contact</a> -->
                <a href="register.php">Register</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="register-container">
                <div class="register-image">
                    <h2>Join Our Community</h2>
                    <p>Create an account to access exclusive features and start shopping from thousands of unique
                        products.</p>

                    <div class="benefits">
                        <div class="benefit-item">
                            <div class="benefit-icon">✓</div>
                            <div>Purchase products from independent sellers</div>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-icon">✓</div>
                            <div>Track orders and manage your wishlist</div>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-icon">✓</div>
                            <div>Receive personalized recommendations</div>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-icon">✓</div>
                            <div>Option to become a seller and list products</div>
                        </div>
                    </div>
                </div>

                <div class="register-form">
                    <h1>Create Account</h1>
                        
                    <!-- <div class="form-tabs">
                        <div class="form-tab active" id="customer-tab">Customer</div>
                        <div class="form-tab" id="seller-tab">Seller</div>
                    </div> -->

                    <form id="register-form" action="../controllers/user.php" method="POST" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first-name">First Name</label>
                                <input type="text" id="first-name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="last-name">Last Name</label>
                                <input type="text" id="last-name"  name="surname" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password"  required>
                        </div>

                        <div class="form-group">
                            <label for="confirm-password">Confirm Password</label>
                            <input type="password" id="confirm-password" required>
                        </div>

                        <!-- Seller-specific fields (hidden by default) -->
                        <div class="seller-fields" id="seller-fields">
                          <!--  <div class="form-group">
                                <label for="store-name">Store Name</label>
                                <input type="text" id="store-name">
                            </div>

                            <div class="form-group">
                                <label for="store-description">Store Description</label>
                                <input type="text" id="store-description">
                            </div> -->

                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone">
                            </div>
                        </div>

                        <div class="remember">
                            <input type="checkbox" id="terms" required>
                            <label for="terms">I agree to the Terms of Service and Privacy Policy</label>
                        </div>
                            
                             <div class="form-group">
                                <select id="role" name="role" class="custom-select">
                                    <option value="customer">Choose</option>
                                    <option value="seller">Seller</option>
                                    <option value="buyer">Buyer</option>
                                    <option value="customer">Customer</option>
                                </select>
                            </div>
                        <!-- type of user -->
                       
                     <!--  end if type of user -->
                        <button type="submit" class="btn btn-primary">Create Account</button>
                    </form>

                    <div class="alt-login">
                        <p>Or continue with</p>
                        <div class="social-buttons">
                            <a href="#" class="social-btn">f</a>
                            <a href="#" class="social-btn">G</a>
                            <a href="#" class="social-btn">in</a>
                        </div>
                    </div>

                    <div class="register-link">
                        Already have an account? <a href="#">Sign In</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <p>&copy; 2025 EasyTrade. All rights reserved.</p>
                <div class="footer-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Help Center</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Simple JavaScript to toggle between customer and seller tabs
        document.getElementById('customer-tab').addEventListener('click', function () {
            this.classList.add('active');
            document.getElementById('seller-tab').classList.remove('active');
            document.getElementById('seller-fields').style.display = 'none';
            // Set the role to 'customer' in the hidden input field
            document.querySelector('input[name="role"]').value = 'customer';
        });

        // document.getElementById('seller-tab').addEventListener('click', function () {
        //     this.classList.add('active');
        //     document.getElementById('customer-tab').classList.remove('active');
        //     document.getElementById('seller-fields').style.display = 'block';

        //     // Set the role to 'seller' in the hidden input field
        //     document.querySelector('input[name="role"]').value = 'seller';

        // });
    </script>
</body>

</html>