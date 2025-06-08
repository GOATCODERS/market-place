<?php
session_start();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - EasyTrade</title>
   <link rel="stylesheet" href="../public/css/sign.css">
</head>

<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <a href="#" class="logo">Easy<span>Trade</span></a>
            <div class="nav-links">
                <a href="../index.php">Home</a>
                <a href="#">Categories</a>
                <a href="#">Contact</a>
                <a href="register.php">Register</a>
            </div>
        </div>
    </header>
    

    <!-- Main Content -->
    <main class="main-content">

        <div class="container">
            <div class="signin-container">
                <div class="signin-header">
                    <div
                        style="width: 80px; height: 80px; margin: 0 auto 20px; background-color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 36px;">
                        👤</div>
                    <h1>Welcome Back</h1>
                    <p>Sign in to continue shopping</p>
                </div>

                <form id="signin-form" action="../controllers/login.php" method="POST">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password"  name="password"required>
                    </div>

                    <div class="remember-forgot">
                        <div class="remember">
                            <input type="checkbox" id="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="#" class="forgot-link">Forgot password?</a>
                    </div>
                    <?php if (isset($_SESSION['error'])): ?>
                        <h2 style="color: red;">
                            <?php echo $_SESSION['error'];
                            unset($_SESSION['error']); ?>
                        </h2>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">Sign In</button>
                </form>

                <div class="alt-login">
                    <p>Or sign in with</p>
                    <div class="social-buttons">
                        <a href="#" class="social-btn">f</a>
                        <a href="#" class="social-btn">G</a>
                        <a href="#" class="social-btn">in</a>
                    </div>
                </div>

                <div class="register-link">
                    Don't have an account? <a href="#">Register Now</a>
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
</body>

</html>