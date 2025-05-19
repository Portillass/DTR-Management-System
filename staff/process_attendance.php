<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staff') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$current_time = date('Y-m-d H:i:s');

if ($action === 'check_in') {
    // Check if already checked in today
    $sql = "SELECT id FROM attendance WHERE user_id = ? AND date = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $today);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'You have already checked in today']);
        exit();
    }

    // Insert check-in record
    $sql = "INSERT INTO attendance (user_id, date, time_in) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iss", $user_id, $today, $current_time);

    if (mysqli_stmt_execute($stmt)) {
        // Log activity
        $sql = "INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'check_in', 'Checked in for the day')";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);

        echo json_encode(['status' => 'success', 'message' => 'Check-in successful']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error checking in']);
    }
} elseif ($action === 'check_out') {
    // Check if checked in today
    $sql = "SELECT id FROM attendance WHERE user_id = ? AND date = ? AND time_out IS NULL";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $today);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 0) {
        echo json_encode(['status' => 'error', 'message' => 'No active check-in found for today']);
        exit();
    }

    // Update check-out time
    $sql = "UPDATE attendance SET time_out = ? WHERE user_id = ? AND date = ? AND time_out IS NULL";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sis", $current_time, $user_id, $today);

    if (mysqli_stmt_execute($stmt)) {
        // Log activity
        $sql = "INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'check_out', 'Checked out for the day')";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);

        echo json_encode(['status' => 'success', 'message' => 'Check-out successful']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error checking out']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?> 