<?php
session_start();
require_once 'config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    // Check if user exists
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            
            if ($remember) {
                // Set cookie for 30 days
                setcookie('user_email', $email, time() + (86400 * 30), "/");
            }
            
            // Redirect based on role
            $redirect = 'index.php';
            if ($user['role'] === 'admin') {
                $redirect = 'admin/dashboard.php';
            } elseif ($user['role'] === 'staff') {
                $redirect = 'staff/dashboard.php';
            }
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Login successful',
                'redirect' => $redirect
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?> 