<?php
// Turn off PHP error display for production
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start output buffering to catch any errors
ob_start();

// Set header for JSON response
header('Content-Type: application/json');

try {
    // Start session
    session_start();

    // Check if the user is logged in
    if (!isset($_SESSION['userArray']) || !isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }

    // Check if it's a POST request and role is set
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['role'])) {
        throw new Exception('Invalid request method or missing role parameter');
    }

    $role = $_POST['role'];

    // Validate role value (must be either 'buyer' or 'seller')
    if ($role !== 'buyer' && $role !== 'seller') {
        throw new Exception('Invalid role selected');
    }

    // Get user ID from session
    $userId = $_SESSION['user_id'];

    // Database connection
     $db_host = "pdb1052.awardspace.net";
     $db_user = "4593147_marketplace";
     $db_pass = "aY77%Lql2MLZtru^";
     $db_name = "4593147_marketplace";
    // Create connection directly (without requiring external file)
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Include user model if needed
   require_once __DIR__ . '/../models/userModel.php';

 
    // Update user role in database using mysqli
    $sql = "UPDATE Users SET role = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }

    $stmt->bind_param('si', $role, $userId);

    if (!$stmt->execute()) {
        throw new Exception('Failed to execute statement: ' . $stmt->error);
    }

    // Check if any rows were affected
    if ($stmt->affected_rows == 0) {
        // This isn't necessarily an error - might be setting the same role
        // We'll continue but log it
        error_log("No rows affected when updating role for user ID $userId");
    }

    // Update role in session variables
    $_SESSION['userArray']['role'] = $role;

    // If user object is stored in session, update it too
    if (isset($_SESSION['user']) && method_exists($_SESSION['user'], 'setRole')) {
        $_SESSION['user']->setRole($role);
    }

    // Clean up
    $stmt->close();
    $conn->close();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Role updated successfully',
        'role' => $role
    ]);

} catch (Exception $e) {
    // Log the error for server-side troubleshooting
    error_log('Update role error: ' . $e->getMessage());

    // Return error response to client
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// End output buffering and flush
ob_end_flush();
?>