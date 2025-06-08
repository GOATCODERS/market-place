<?php
ob_start();
// fetch_products.php - API to fetch products by category
require_once '../includes/database/connection.php';

header('Content-Type: application/json');

try {
    // Get distinct categories
    $categoryQuery = $pdo->query("SELECT DISTINCT category FROM listings ORDER BY category");
    $categories = $categoryQuery->fetchAll(PDO::FETCH_COLUMN);

    $result = [];

    // For each category, fetch products
    foreach ($categories as $category) {
        $stmt = $pdo->prepare("
            SELECT id, item_name, price, image, image_type, user_id, description 
            FROM listings 
            WHERE category = ? 
        ");
        $stmt->execute([$category]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as &$product) {
            // Convert image BLOB to base64 if it exists
            if ($product['image']) {
                $base64Image = base64_encode($product['image']);
                $product['image_path'] = "data:" . $product['image_type'] . ";base64," . $base64Image;
            } else {
                $product['image_path'] = "../public/images/placeholder.jpg";
            }

            // Match frontend naming
            $product['name'] = $product['item_name'];
            $product['seller_id'] = $product['user_id'];

            // Clean up
            unset($product['image']);
            unset($product['image_type']);
            unset($product['item_name']);
            unset($product['user_id']);
        }

        $result[$category] = $products;
    }

    echo json_encode($result);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

?>