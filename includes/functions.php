<?php
// Function to log user activity
function logActivity($user_id, $activity_type, $description = '') {
    global $conn;
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $sql = "INSERT INTO activity_logs (user_id, activity_type, description, ip_address) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isss", $user_id, $activity_type, $description, $ip_address);
    return mysqli_stmt_execute($stmt);
}

// Function to get user profile
function getUserProfile($user_id) {
    global $conn;
    
    $sql = "SELECT u.*, p.* FROM users u 
            LEFT JOIN user_profiles p ON u.id = p.user_id 
            WHERE u.id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Function to update user profile
function updateUserProfile($user_id, $data) {
    global $conn;
    
    // Check if profile exists
    $sql = "SELECT id FROM user_profiles WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Update existing profile
        $sql = "UPDATE user_profiles SET 
                phone = ?, 
                address = ?, 
                bio = ? 
                WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssi", 
            $data['phone'], 
            $data['address'], 
            $data['bio'], 
            $user_id
        );
    } else {
        // Create new profile
        $sql = "INSERT INTO user_profiles (user_id, phone, address, bio) 
                VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isss", 
            $user_id, 
            $data['phone'], 
            $data['address'], 
            $data['bio']
        );
    }
    
    return mysqli_stmt_execute($stmt);
}

// Function to get user activity logs
function getUserActivityLogs($user_id, $limit = 10) {
    global $conn;
    
    $sql = "SELECT * FROM activity_logs 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $logs = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $logs[] = $row;
    }
    
    return $logs;
}

// Function to check if user has permission
function hasPermission($user_id, $required_role) {
    global $conn;
    
    $sql = "SELECT role FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    if (!$user) return false;
    
    // Define role hierarchy
    $role_hierarchy = array(
        'user' => 1,
        'staff' => 2,
        'admin' => 3
    );
    
    return $role_hierarchy[$user['role']] >= $role_hierarchy[$required_role];
}
?> 