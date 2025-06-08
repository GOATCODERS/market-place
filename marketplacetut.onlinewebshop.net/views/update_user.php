<?php
ob_start();
session_start();
require_once '../includes/database/connection.php';
// Start session to maintain user login stat

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}


// Get user data
$user_id = $_SESSION['user_id'];
$userData = null;
$message = "";

// Get current user data
$sql = "SELECT id, name, surname, email, role FROM Users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $userData = $result->fetch_assoc();
} else {
    $message = "User not found!";
}

// Handle form submission for profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $fieldsToUpdate = [];
    $types = "";
    $values = [];

    // Check which fields were selected for update
    if (isset($_POST['update_name']) && !empty($_POST['name'])) {
        $fieldsToUpdate[] = "name = ?";
        $types .= "s";
        $values[] = $_POST['name'];
    }

    if (isset($_POST['update_surname']) && !empty($_POST['surname'])) {
        $fieldsToUpdate[] = "surname = ?";
        $types .= "s";
        $values[] = $_POST['surname'];
    }

    if (isset($_POST['update_email']) && !empty($_POST['email'])) {
        $fieldsToUpdate[] = "email = ?";
        $types .= "s";
        $values[] = $_POST['email'];
    }

    if (isset($_POST['update_password']) && !empty($_POST['password'])) {
        // Hash the password for security
        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $fieldsToUpdate[] = "password = ?";
        $types .= "s";
        $values[] = $hashed_password;
    }

    // Only proceed if there are fields to update
    if (!empty($fieldsToUpdate)) {
        $sql = "UPDATE Users SET " . implode(", ", $fieldsToUpdate) . " WHERE id = ?";
        $types .= "i";
        $values[] = $user_id;

        $stmt = $conn->prepare($sql);
        // Dynamically bind parameters
        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            $message = "Profile updated successfully!";
            // Refresh user data
            $stmt = $conn->prepare("SELECT id, name, surname, email, role FROM Users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $userData = $result->fetch_assoc();
        } else {
            $message = "Error updating profile: " . $conn->error;
        }
    } else {
        $message = "No fields selected for update or empty values provided.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <style>
        :root {
            --primary: #3a86ff;
            --secondary: #8338ec;
            --accent: #ff006e;
            --light: #ffffff;
            --dark: #1a1a2e;
            --gray: #f0f0f0;
            --text: #333333;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--gray);
            color: var(--text);
            margin: 2px;
            padding: 3px;
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 5rem;
            background-color: var(--light);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .profile-info {
            background-color: var(--gray);
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .profile-info h2 {
            color: var(--secondary);
            margin-top: 0;
        }

        .profile-detail {
            margin-bottom: 0.5rem;
        }

        .profile-detail span {
            font-weight: bold;
        }

        .update-form {
            background-color: var(--gray);
            padding: 1.5rem;
            border-radius: 8px;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .checkbox-group input[type="checkbox"] {
            margin-right: 0.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        button {
            background-color: var(--primary);
            color: var(--light);
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: var(--secondary);
        }

        .message {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 4px;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            background-color: var(--accent);
            color: white;
            border-radius: 4px;
            font-size: 0.9rem;
            margin-left: 0.5rem;
        }
    </style>
</head>

<body>
   
   <main>
    <div class="container">
        <h1>User Profile</h1>

        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($userData): ?>
            <div class="profile-info">
                <h2>Your Information</h2>
                <div class="profile-detail">
                    <span>Name:</span> <?php echo htmlspecialchars($userData['name']); ?>
                </div>
                <div class="profile-detail">
                    <span>Surname:</span> <?php echo htmlspecialchars($userData['surname']); ?>
                </div>
                <div class="profile-detail">
                    <span>Email:</span> <?php echo htmlspecialchars($userData['email']); ?>
                </div>
                <div class="profile-detail">
                    <span>Role:</span>
                    <span class="role-badge"><?php echo htmlspecialchars(ucfirst($userData['role'])); ?></span>
                </div>
            </div>

            <div class="update-form">
                <h2>Update Profile</h2>
                <p>Select the fields you want to update:</p>

                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="update_name" name="update_name">
                            <label for="update_name">Update Name</label>
                        </div>
                        <input type="text" name="name" placeholder="New Name"
                            value="<?php echo htmlspecialchars($userData['name']); ?>">
                    </div>

                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="update_surname" name="update_surname">
                            <label for="update_surname">Update Surname</label>
                        </div>
                        <input type="text" name="surname" placeholder="New Surname"
                            value="<?php echo htmlspecialchars($userData['surname']); ?>">
                    </div>

                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="update_email" name="update_email">
                            <label for="update_email">Update Email</label>
                        </div>
                        <input type="email" name="email" placeholder="New Email"
                            value="<?php echo htmlspecialchars($userData['email']); ?>">
                    </div>

                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="update_password" name="update_password">
                            <label for="update_password">Update Password</label>
                        </div>
                        <input type="password" name="password" placeholder="New Password">
                    </div>

                    <button type="submit" name="update_profile">Update Profile</button>
                </form>
            </div>
        <?php else: ?>
            <div class="message error">
                Error: Unable to retrieve user data. Please try again later.
            </div>
        <?php endif; ?>
    </div>
</main>
    <script>
        // Enable/disable form fields based on checkbox selection
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');

            function updateFieldState(checkbox, inputField) {
                inputField.disabled = !checkbox.checked;
                inputField.style.opacity = checkbox.checked ? '1' : '0.5';
            }

            checkboxes.forEach(function (checkbox) {
                const inputField = checkbox.closest('.form-group').querySelector('input[type="text"], input[type="email"], input[type="password"]');

                // Initialize state
                updateFieldState(checkbox, inputField);

                // Update state on change
                checkbox.addEventListener('change', function () {
                    updateFieldState(checkbox, inputField);
                });
            });
        });
    </script>
</body>

</html>