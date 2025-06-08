<?php
require_once '../includes/database/connection.php';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM listings WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "Deleted";
}  

?>