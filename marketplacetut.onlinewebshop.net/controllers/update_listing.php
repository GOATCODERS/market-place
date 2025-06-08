<?php
// update_listing.php
ob_start();
session_start();
require_once '../includes/database/connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if listing ID is provided
if (!isset($_GET['id'])) {
    header('Location: my_listings.php');
    exit;
}

$listing_id = $_GET['id'];

// Verify that the listing belongs to the current user
$stmt = $conn->prepare("SELECT * FROM listings WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $listing_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Listing doesn't exist or doesn't belong to the user
    header('Location: my_listings.php');
    exit;
}

$listing = $result->fetch_assoc();

// Handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $item_name = trim($_POST['item_name']);
    $category = trim($_POST['category']);
    $item_condition = trim($_POST['item_condition']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);
    $purchase_date = !empty($_POST['purchase_date']) ? $_POST['purchase_date'] : null;

    // Basic validation
    if (empty($item_name) || empty($category) || empty($item_condition) || $price <= 0) {
        $error = "Please fill in all required fields.";
    } else {
        // Prepare update statement
        $updateQuery = "UPDATE listings SET 
                        item_name = ?, 
                        category = ?, 
                        item_condition = ?, 
                        price = ?, 
                        description = ?, 
                        purchase_date = ?";

        $params = [$item_name, $category, $item_condition, $price, $description, $purchase_date];
        $types = "sssdss";

        // Check if new image is uploaded
        if (!empty($_FILES['image']['name'])) {
            $image = file_get_contents($_FILES['image']['tmp_name']);
            $image_type = $_FILES['image']['type'];

            $updateQuery .= ", image = ?, image_type = ?";
            $params[] = $image;
            $params[] = $image_type;
            $types .= "ss";
        }

        $updateQuery .= " WHERE id = ? AND user_id = ?";
        $params[] = $listing_id;
        $params[] = $_SESSION['user_id'];
        $types .= "ii";

        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $message = "Listing updated successfully!";
            // Refresh listing data
            $stmt = $conn->prepare("SELECT * FROM listings WHERE id = ?");
            $stmt->bind_param("i", $listing_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $listing = $result->fetch_assoc();
        } else {
            $error = "Error updating listing: " . $conn->error;
        }
    }
}

// Get categories (add your categories here)
$categories = ['Electronics', 'Furniture', 'Clothing', 'Books', 'Sports', 'Other'];

// Get condition options
$conditions = ['New', 'Like New', 'Good', 'Fair', 'Poor'];

// Page header
include_once "../includes/header.php";
?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-2">Update Listing</h1>
        <a href="my_listings.php" class="text-blue-600 hover:underline">&larr; Back to My Listings</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="item_name" class="block text-sm font-medium text-gray-700 mb-1">Item Name*</label>
                    <input type="text" id="item_name" name="item_name"
                        value="<?php echo htmlspecialchars($listing['item_name']); ?>"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category*</label>
                    <select id="category" name="category"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category; ?>" <?php echo ($listing['category'] === $category) ? 'selected' : ''; ?>>
                                <?php echo $category; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="item_condition" class="block text-sm font-medium text-gray-700 mb-1">Condition*</label>
                    <select id="item_condition" name="item_condition"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                        <?php foreach ($conditions as $condition): ?>
                            <option value="<?php echo $condition; ?>" <?php echo ($listing['item_condition'] === $condition) ? 'selected' : ''; ?>>
                                <?php echo $condition; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price (R)*</label>
                    <input type="number" id="price" name="price"
                        value="<?php echo htmlspecialchars($listing['price']); ?>" step="0.01" min="0"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                </div>

                <div>
                    <label for="purchase_date" class="block text-sm font-medium text-gray-700 mb-1">Purchase
                        Date</label>
                    <input type="date" id="purchase_date" name="purchase_date"
                        value="<?php echo !empty($listing['purchase_date']) ? htmlspecialchars($listing['purchase_date']) : ''; ?>"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Update Image</label>
                    <input type="file" id="image" name="image" accept="image/*"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-sm text-gray-500 mt-1">Leave empty to keep current image</p>
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" name="description" rows="4"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($listing['description']); ?></textarea>
                </div>
            </div>

            <?php if (!empty($listing['image'])): ?>
                <div class="mt-4">
                    <p class="text-sm font-medium text-gray-700 mb-2">Current Image:</p>
                    <img src="data:<?php echo $listing['image_type']; ?>;base64,<?php echo base64_encode($listing['image']); ?>"
                        alt="Current listing image" class="w-32 h-32 object-cover border rounded">
                </div>
            <?php endif; ?>

            <div class="mt-6 flex justify-end">
                <a href="my_listings.php"
                    class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md mr-4 hover:bg-gray-300">Cancel</a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Update
                    Listing</button>
            </div>
        </form>
    </div>
</div>

<?php
include_once "../includes/footer.php";
?>