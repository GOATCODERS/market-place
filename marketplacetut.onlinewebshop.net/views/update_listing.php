<?php
// update_listing.php
session_start();
require_once '../includes/database/connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: sign.php');
    exit;
}

// Check if listing ID is provided
if (!isset($_GET['id'])) {
    header('Location: sell.php');
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
    header('Location: sell.php');
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

// Add custom CSS
echo '
<style>
    /* Enhanced Form Styling */
    .form-container {
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }
    
    .form-container:hover {
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1), 0 4px 6px rgba(0, 0, 0, 0.05);
    }
    
    .form-heading {
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .input-group {
        margin-bottom: 1.25rem;
    }
    
    .input-label {
        display: block;
        font-weight: 500;
        font-size: 0.875rem;
        color: #374151;
        margin-bottom: 0.5rem;
    }
    
    .input-label .required {
        color: #ef4444;
        margin-left: 0.25rem;
    }
    
    .form-input {
        width: 100%;
        padding: 0.625rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        transition: all 0.3s ease;
    }
    
    .form-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
    }
    
    .form-select {
        width: 100%;
        padding: 0.625rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        background-image: url("data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }
    
    .form-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
    }
    
    .file-input-wrapper {
        position: relative;
    }
    
    .file-input-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1rem;
        background-color: #f3f4f6;
        border: 1px dashed #d1d5db;
        border-radius: 0.375rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .file-input-label:hover {
        background-color: #e5e7eb;
    }
    
    .file-input {
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    
    .image-preview {
        margin-top: 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.375rem;
        padding: 0.5rem;
        background-color: #f9fafb;
    }
    
    .form-textarea {
        width: 100%;
        padding: 0.625rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        min-height: 120px;
        resize: vertical;
    }
    
    .form-textarea:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.625rem 1.25rem;
        font-weight: 500;
        border-radius: 0.375rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .btn-primary {
        background-color: #3b82f6;
        color: white;
    }
    
    .btn-primary:hover {
        background-color: #2563eb;
    }
    
    .btn-secondary {
        background-color: #f3f4f6;
        color: #374151;
    }
    
    .btn-secondary:hover {
        background-color: #e5e7eb;
    }
    
    .form-footer {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e5e7eb;
    }
    
    .alert {
        padding: 1rem;
        border-radius: 0.375rem;
        margin-bottom: 1.5rem;
    }
    
    .alert-success {
        background-color: #d1fae5;
        border: 1px solid #6ee7b7;
        color: #065f46;
    }
    
    .alert-error {
        background-color: #fee2e2;
        border: 1px solid #fca5a5;
        color: #b91c1c;
    }
    
    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .fade-in {
        animation: fadeIn 0.3s ease-out forwards;
    }
</style>
';
?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-6 fade-in">
        <h1 class="text-2xl font-bold mb-2">Update Listing</h1>
        <a href="sell.php" class="text-blue-600 hover:underline flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1">
                <path d="M19 12H5"></path>
                <path d="M12 19l-7-7 7-7"></path>
            </svg>
            Back to My Listings
        </a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success fade-in">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <?php echo $message; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error fade-in">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <?php echo $error; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="form-container p-6 fade-in">
        <h2 class="text-xl font-semibold form-heading">Edit Item Details</h2>

        <form method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="input-group">
                    <label for="item_name" class="input-label">Item Name<span class="required">*</span></label>
                    <input type="text" id="item_name" name="item_name"
                        value="<?php echo htmlspecialchars($listing['item_name']); ?>" class="form-input" required>
                </div>

                <div class="input-group">
                    <label for="category" class="input-label">Category<span class="required">*</span></label>
                    <select id="category" name="category" class="form-select" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category; ?>" <?php echo ($listing['category'] === $category) ? 'selected' : ''; ?>>
                                <?php echo $category; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="input-group">
                    <label for="item_condition" class="input-label">Condition<span class="required">*</span></label>
                    <select id="item_condition" name="item_condition" class="form-select" required>
                        <?php foreach ($conditions as $condition): ?>
                            <option value="<?php echo $condition; ?>" <?php echo ($listing['item_condition'] === $condition) ? 'selected' : ''; ?>>
                                <?php echo $condition; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="input-group">
                    <label for="price" class="input-label">Price (R)<span class="required">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500">R</span>
                        </div>
                        <input type="number" id="price" name="price"
                            value="<?php echo htmlspecialchars($listing['price']); ?>" step="0.01" min="0"
                            class="form-input pl-8" required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="purchase_date" class="input-label">Purchase Date</label>
                    <input type="date" id="purchase_date" name="purchase_date"
                        value="<?php echo !empty($listing['purchase_date']) ? htmlspecialchars($listing['purchase_date']) : ''; ?>"
                        class="form-input">
                </div>

                <div class="input-group">
                    <label class="input-label">Update Image</label>
                    <div class="file-input-wrapper">
                        <label class="file-input-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                            <span id="file-name">Choose an image</span>
                            <input type="file" id="image" name="image" accept="image/*" class="file-input">
                        </label>
                        <p class="text-sm text-gray-500 mt-1">Leave empty to keep current image</p>
                    </div>
                </div>

                <div class="md:col-span-2 input-group">
                    <label for="description" class="input-label">Description</label>
                    <textarea id="description" name="description" rows="4"
                        class="form-textarea"><?php echo htmlspecialchars($listing['description']); ?></textarea>
                </div>
            </div>

            <?php if (!empty($listing['image'])): ?>
                <div class="image-preview">
                    <p class="text-sm font-medium text-gray-700 mb-2">Current Image:</p>
                    <img src="data:<?php echo $listing['image_type']; ?>;base64,<?php echo base64_encode($listing['image']); ?>"
                        alt="Current listing image" class="w-32 h-32 object-cover border rounded">
                </div>
            <?php endif; ?>

            <div class="form-footer">
                <a href="my_listings.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="mr-1">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Update file input display with selected filename
    document.getElementById('image').addEventListener('change', function (e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : 'Choose an image';
        document.getElementById('file-name').textContent = fileName;
    });
</script>

<?php
include_once "../includes/footer.php";
?>