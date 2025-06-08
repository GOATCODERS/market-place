<?php
ob_start();
session_start(); // Start the session


require_once '../includes/database/connection.php';

if (isset($_POST['submit'])) {

    // Read form values
    $itemName = $_POST['itemName'];
    $category = $_POST['category'];
    $condition = $_POST['condition'];
    $purchaseDate = $_POST['purchaseDate'] ?? null;
    $price = $_POST['price'];
    $description = $_POST['description'];
    $user = $_SESSION['userArray'];

    $userid = $user['id'];
    


    // Validate form values
    if (!empty($_FILES['image']['tmp_name'])) {
    $imageData = file_get_contents($_FILES['image']['tmp_name']);
    $imageType = $_FILES['image']['type'];
    } else {
    $imageData = null;
    $imageType = null;
    }

    // Prepare SQL
    $stmt = $conn->prepare("INSERT INTO listings
    (item_name, category, item_condition, purchase_date, price, description, image, image_type ,user_id)
    VALUES (?, ?, ?, ?, ?, ?, ?, ? ,?)");
    $stmt->bind_param(
    "ssssdssss",
    $itemName,
    $category,
    $condition,
    $purchaseDate,
    $price,
    $description,
    $imageData,
    $imageType,
    $userid
    );

    if ($stmt->execute()) {
        header("Location: ../views/sell.php?success=1"); // Redirect to the same page with success message
        exit(); // Ensure no further code is executed after the redirect
    } else {
        echo "Error: " . $stmt->error; // Handle error
    }

    $stmt->close();
    $conn->close();
    }
?>