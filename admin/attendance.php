<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Get today's attendance
$today = date('Y-m-d');
$sql = "SELECT COUNT(*) as count FROM attendance WHERE date = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $today);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$todayAttendance = mysqli_fetch_assoc($result)['count'];

// Get staff attendance records
$sql = "SELECT a.*, u.name as staff_name 
        FROM attendance a 
        JOIN users u ON a.user_id = u.id 
        WHERE a.date = ? 
        ORDER BY a.time_in DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $today);
mysqli_stmt_execute($stmt);
$attendanceRecords = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management - Admin Dashboard</title>
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
                        <a class="nav-link" href="manage_staff.php">
                            <i class="fas fa-users"></i> Manage Staff
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="attendance.php">
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
                    <h2>Attendance Management</h2>
                    <div class="user-info">
                        <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <span class="badge bg-primary"><?php echo htmlspecialchars($_SESSION['user_role']); ?></span>
                    </div>
                </div>

                <!-- Attendance Summary -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-calendar-check text-success"></i> Today's Attendance
                                </h5>
                                <h2 class="card-text"><?php echo $todayAttendance; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Records -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Today's Attendance Records</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Staff Name</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($record = mysqli_fetch_assoc($attendanceRecords)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($record['staff_name']); ?></td>
                                        <td><?php echo htmlspecialchars($record['time_in']); ?></td>
                                        <td><?php echo $record['time_out'] ? htmlspecialchars($record['time_out']) : 'Not checked out'; ?></td>
                                        <td>
                                            <?php
                                            $status = 'Present';
                                            if (!$record['time_out']) {
                                                $status = 'Active';
                                            }
                                            echo '<span class="badge bg-success">' . $status . '</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="viewDetails(<?php echo $record['id']; ?>)">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <script>
        function viewDetails(id) {
            // Implement view details functionality
            alert('View details for attendance record: ' + id);
        }
    </script>
</body>
</html> 