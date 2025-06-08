<?php
ob_start();
require_once '../includes/database/connection.php';
require_once '../models/userModel.php';
require_once 'userController.php';


session_start();

$controller = new UserController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $surname = $_POST['surname'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'seller';
     
    
    // Validate input
    $response = $controller->registerUser($name, $email, $password, $role , $surname);
    //  echo json_encode($role);

    // Redirect to the login page or show a success message
    if ($response['success'] === true) {
        // Redirect to the login page or show a success message
        header("Location: ../views/sign.php?message=" . urlencode($response['message']));
        exit;
    } else {
    
        echo $response['message'];
    }
}
?>