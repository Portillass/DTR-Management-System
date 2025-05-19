<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staff') {
    header('Location: ../index.php');
    exit();
}

// Get staff's attendance records
$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// Get today's attendance
$sql = "SELECT * FROM attendance WHERE user_id = ? AND date = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "is", $user_id, $today);
mysqli_stmt_execute($stmt);
$todayAttendance = mysqli_stmt_get_result($stmt)->fetch_assoc();

// Get monthly attendance summary
$currentMonth = date('Y-m');
$sql = "SELECT 
            COUNT(*) as total_days,
            SUM(CASE WHEN time_out IS NOT NULL THEN 1 ELSE 0 END) as completed_days
        FROM attendance 
        WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "is", $user_id, $currentMonth);
mysqli_stmt_execute($stmt);
$monthlyStats = mysqli_stmt_get_result($stmt)->fetch_assoc();

// Get attendance history
$sql = "SELECT * FROM attendance WHERE user_id = ? ORDER BY date DESC LIMIT 30";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$attendanceHistory = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - Staff Dashboard</title>
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="text-center mb-4">
                    <h4 class="text-white">Staff Panel</h4>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
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
                        <a class="nav-link" href="profile.php">
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

                <!-- Today's Status -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-calendar-check text-primary"></i> Today's Status
                                </h5>
                                <?php if ($todayAttendance): ?>
                                    <div class="mt-3">
                                        <p><strong>Time In:</strong> <?php echo date('h:i A', strtotime($todayAttendance['time_in'])); ?></p>
                                        <?php if ($todayAttendance['time_out']): ?>
                                            <p><strong>Time Out:</strong> <?php echo date('h:i A', strtotime($todayAttendance['time_out'])); ?></p>
                                        <?php else: ?>
                                            <p><strong>Status:</strong> <span class="badge bg-success">Active</span></p>
                                            <button class="btn btn-primary" onclick="checkOut()">Check Out</button>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="mt-3">
                                        <p>You haven't checked in today.</p>
                                        <button class="btn btn-primary" onclick="checkIn()">Check In</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-chart-pie text-success"></i> Monthly Summary
                                </h5>
                                <div class="mt-3">
                                    <p><strong>Total Days:</strong> <?php echo $monthlyStats['total_days']; ?></p>
                                    <p><strong>Completed Days:</strong> <?php echo $monthlyStats['completed_days']; ?></p>
                                    <p><strong>Attendance Rate:</strong> 
                                        <?php
                                        $rate = $monthlyStats['total_days'] > 0 
                                            ? round(($monthlyStats['completed_days'] / $monthlyStats['total_days']) * 100, 1) 
                                            : 0;
                                        echo $rate . '%';
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance History -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Attendance History</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($record = mysqli_fetch_assoc($attendanceHistory)): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($record['time_in'])); ?></td>
                                        <td>
                                            <?php 
                                            echo $record['time_out'] 
                                                ? date('h:i A', strtotime($record['time_out']))
                                                : '<span class="badge bg-warning">Not checked out</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($record['time_out']): ?>
                                                <span class="badge bg-success">Completed</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Active</span>
                                            <?php endif; ?>
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
        function checkIn() {
            fetch('process_attendance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'check_in'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                alert('An error occurred. Please try again.');
            });
        }

        function checkOut() {
            fetch('process_attendance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'check_out'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                alert('An error occurred. Please try again.');
            });
        }
    </script>
</body>
</html> 