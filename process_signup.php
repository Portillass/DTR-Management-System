<?php
session_start();
require_once 'config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
        exit();
    }

    if ($password !== $confirm_password) {
        echo json_encode(['status' => 'error', 'message' => 'Passwords do not match']);
        exit();
    }

    // Check if email already exists
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user with role 'staff'
    $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'staff')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashed_password);

    if (mysqli_stmt_execute($stmt)) {
        $user_id = mysqli_insert_id($conn);
        
        // Create empty profile
        $sql = "INSERT INTO user_profiles (user_id) VALUES (?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);

        // Log activity
        $sql = "INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'signup', 'New staff account created')";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);

        echo json_encode([
            'status' => 'success', 
            'message' => 'Account created successfully! You can now login.'
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error creating account']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?> 