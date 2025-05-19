<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Handle staff actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_id = $_POST['staff_id'] ?? '';
    $action = $_POST['action'] ?? '';

    if ($staff_id && $action) {
        if ($action === 'promote') {
            // Promote staff to admin
            $sql = "UPDATE users SET role = 'admin' WHERE id = ? AND role = 'staff'";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $staff_id);
            
            if (mysqli_stmt_execute($stmt)) {
                // Log the activity
                $admin_id = $_SESSION['user_id'];
                $sql = "INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'Promote Staff', 'Promoted staff to admin role')";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $admin_id);
                mysqli_stmt_execute($stmt);
                
                $_SESSION['success'] = "Staff member has been promoted to admin successfully.";
            } else {
                $_SESSION['error'] = "Failed to promote staff member.";
            }
        } elseif ($action === 'delete') {
            // Delete staff member
            $sql = "DELETE FROM users WHERE id = ? AND role = 'staff'";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $staff_id);
            
            if (mysqli_stmt_execute($stmt)) {
                // Log the activity
                $admin_id = $_SESSION['user_id'];
                $sql = "INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'Delete Staff', 'Deleted staff member')";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $admin_id);
                mysqli_stmt_execute($stmt);
                
                $_SESSION['success'] = "Staff member has been deleted successfully.";
            } else {
                $_SESSION['error'] = "Failed to delete staff member.";
            }
        }
    }
    
    // Redirect to prevent form resubmission
    header('Location: manage_staff.php');
    exit();
}

// Get all staff members
$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM attendance WHERE user_id = u.id) as attendance_count,
        (SELECT COUNT(*) FROM activity_logs WHERE user_id = u.id) as activity_count
        FROM users u 
        WHERE u.role = 'staff'
        ORDER BY u.name";
$result = mysqli_query($conn, $sql);
$staff_members = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff - Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: #fff;
            padding: 10px 20px;
            margin: 5px 0;
        }
        .sidebar .nav-link:hover {
            background-color: #495057;
        }
        .sidebar .nav-link.active {
            background-color: #0d6efd;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .main-content {
            padding: 20px;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .staff-card {
            transition: transform 0.2s;
        }
        .staff-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="text-center mb-4">
                    <h4 class="text-white">Admin Panel</h4>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="manage_staff.php">
                            <i class="fas fa-users"></i> Manage Staff
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="attendance.php">
                            <i class="fas fa-calendar-check"></i> Attendance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../profile.php">
                            <i class="fas fa-user"></i> Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Manage Staff</h2>
                    <div class="user-info">
                        <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <span class="badge bg-primary"><?php echo htmlspecialchars($_SESSION['user_role']); ?></span>
                    </div>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Staff List -->
                <div class="row">
                    <?php foreach ($staff_members as $staff): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card staff-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-user-circle text-primary"></i>
                                        <?php echo htmlspecialchars($staff['name']); ?>
                                    </h5>
                                    <span class="badge bg-info">Staff</span>
                                </div>
                                <p class="card-text">
                                    <i class="fas fa-envelope text-muted"></i> <?php echo htmlspecialchars($staff['email']); ?>
                                </p>
                                <div class="row text-center mb-3">
                                    <div class="col-6">
                                        <h6 class="text-muted">Attendance</h6>
                                        <h4><?php echo $staff['attendance_count']; ?></h4>
                                    </div>
                                    <div class="col-6">
                                        <h6 class="text-muted">Activities</h6>
                                        <h4><?php echo $staff['activity_count']; ?></h4>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to promote this staff member to admin?');">
                                        <input type="hidden" name="staff_id" value="<?php echo $staff['id']; ?>">
                                        <input type="hidden" name="action" value="promote">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-user-plus"></i> Promote to Admin
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this staff member? This action cannot be undone.');">
                                        <input type="hidden" name="staff_id" value="<?php echo $staff['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html> 