<?php



// Database connection (replace with your credentials)
$db_host = "pdb1052.awardspace.net";
 $db_user = "4593147_marketplace";
 $db_pass = "aY77%Lql2MLZtru^";
$db_name = "4593147_marketplace";

// $db_host = "localhost";
// $db_user = "root";
// $db_pass = "";
// $db_name = "marketplace_db";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}



?>

