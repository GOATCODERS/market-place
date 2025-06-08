<?php
session_start(); // Ensure session starts at the very top

require_once '../includes/database/connection.php';
require_once '../models/userModel.php';

class UserController
{
    private $db;

    public function __construct($mysqli)
    {
        $this->db = $mysqli;
    }

    public function registerUser($name, $email, $password, $role, $surname)
    {
        // Validate input
        if (empty($name) || empty($email) || empty($password) || empty($role) || empty($surname)) {
            return ['success' => false, 'message' => 'All fields are required.'];
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Check if email already exists
        $sqlCheck = "SELECT id FROM Users WHERE email = ?";
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->bind_param('s', $email);
        $stmtCheck->execute();
        $result = $stmtCheck->get_result();

        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => 'Email already registered.'];
        }

        // Insert user into database
        $sql = "INSERT INTO Users (name, email, password, role, surname) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('sssss', $name, $email, $hashedPassword, $role, $surname);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'User registered successfully.'];
        } else {
            return ['success' => false, 'message' => 'Registration failed.'];
        }
    }

    public function loginUser($email, $password)
    {
        // Validate input
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email and password are required.'];
        }

        // Fetch user from database
        $sql = "SELECT id, name, surname, email, password, role FROM Users WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        $userData = $result->fetch_assoc();

        // Verify password
        if (!password_verify($password, $userData['password'])) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        // Ensure the `User` class is included
        require_once '../models/userModel.php';

        // Store User object in session
        $User = new User($userData['name'], $userData['surname'], $userData['email'], $userData['role']);
        $_SESSION['user'] = $User;

        // Store user array in session (alternative method)
        $_SESSION['userArray'] = [
            'id' => $userData['id'],
            'name' => $userData['name'],
            'surname' => $userData['surname'],
            'email' => $userData['email'],
            'role' => $userData['role']
        ];

        $_SESSION['user_id'] = $userData['id'];

        return ['success' => true, 'message' => 'Login successful.'];
    }
}
?>
