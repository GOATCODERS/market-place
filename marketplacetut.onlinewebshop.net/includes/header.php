<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/header.css">
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
  </style>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <!-- Header -->
    <header>
        <div class="container header-container">
            <a href="../index.php" class="logo">Easy<span>Trade</span></a>
            <div class="nav-links">
                <a href="../index.php">Home</a>

                <?php if ($_SESSION['userArray']['role'] == 'admin'): ?>

                <a href="../views/Categories.php">Categories</a>
                 <?php endif; ?>
                <a href="#">Contact</a>
                <?php if (isset($_SESSION['userArray'])): ?>
                    <?php
                    $user = $_SESSION['userArray'];
                    $initial = strtoupper(substr($user['name'], 0, 1)); // Get first letter of the name
                    ?>
                    <div class="dropdown">
                        <button class="user-icon " id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo $initial; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="update_user.php">Update User</a></li>
                            <li><a class="dropdown-item text-danger" href="../controllers/logout.php">Lock Out</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="../views/sign.php">Sign In</a>
                    <a href="../views/register.php">Register</a>
                <?php endif; ?>
            </div>
            
        </div>
        
    </header>
  

</body>

</html>