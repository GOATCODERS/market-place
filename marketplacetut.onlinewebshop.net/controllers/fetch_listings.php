<?php
// fetch_listings.php
ob_start();
session_start();
require_once '../includes/database/connection.php'; // include your DB connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

// Fetch listings from the database
$query = $conn->query("SELECT * FROM listings WHERE user_id = {$_SESSION['user_id']} ORDER BY created_at DESC");
$listings = $query->fetch_all(MYSQLI_ASSOC);

if (empty($listings)) {
    echo '<div class="text-center text-gray-500 py-8">No items listed yet. Create your first listing!</div>';
} else {
    foreach ($listings as $item):
        $imgSrc = '';
        if (!empty($item['image'])) {
            $imgSrc = 'data:' . $item['image_type'] . ';base64,' . base64_encode($item['image']);
        }
        ?>
        <div class="border border-gray-200 rounded-lg p-4">
            <div class="flex flex-col md:flex-row gap-4">
                <?php if ($imgSrc): ?>
                    <div class="w-full md:w-24">
                        <img src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>"
                            class="w-full h-24 object-cover rounded-md">
                    </div>
                <?php endif; ?>

                <div class="flex-1">
                    <div class="flex justify-between">
                        <h3 class="font-semibold"><?php echo htmlspecialchars($item['item_name']); ?></h3>
                        <span class="text-green-600 font-medium">R<?php echo htmlspecialchars($item['price']); ?></span>
                    </div>

                    <div class="flex flex-wrap gap-2 mt-1">
                        <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                            <?php echo htmlspecialchars($item['category']); ?>
                        </span>
                        <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">
                            <?php echo htmlspecialchars($item['item_condition']); ?>
                        </span>
                    </div>

                    <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                        <?php echo htmlspecialchars($item['description']); ?>
                    </p>

                    <div class="flex justify-between items-center mt-2">
                        <span class="text-xs text-gray-500">
                            Listed on <?php echo date('M d, Y', strtotime($item['created_at'])); ?>
                            <?php if (!empty($item['purchase_date'])): ?>
                                | Purchased on <?php echo date('M d, Y', strtotime($item['purchase_date'])); ?>
                            <?php endif; ?>
                        </span>
                        <div class="flex space-x-3">
                            <a href="update_listing.php?id=<?php echo $item['id']; ?>"
                                class="text-blue-600 text-sm hover:text-blue-800">
                                Edit
                            </a>
                            <a href="#" data-id="<?php echo $item['id']; ?>"
                                class="text-red-600 text-sm hover:text-red-800 delete-btn">
                                Remove
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    endforeach;
}
?>