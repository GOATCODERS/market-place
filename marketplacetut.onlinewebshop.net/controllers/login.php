<?php
ob_start();

require_once '../includes/database/connection.php';
require_once '../models/userModel.php';
require_once 'userController.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Initialize UserController with database connection
    $userController = new UserController($conn);

    // Attempt login
    $response = $userController->loginUser($email, $password);

    if ($_SESSION['userArray']['role'] == 'admin') {
        header('Location: ../views/admin_dashboard.php'); // Redirect to admin page
        exit();

     }

    // Redirect based on response
    if ($response['success']) {
        header('Location: ../index.php'); // Redirect to dashboard on success
        exit();
    } else {
        $_SESSION['error'] = $response['message']; // Store error message in session
        header('Location: ../views/sign.php'); // Redirect back to login page
        exit();
    }
}
?>