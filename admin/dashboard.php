<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Get total staff count
$sql = "SELECT COUNT(*) as total_staff FROM users WHERE role = 'staff'";
$result = mysqli_query($conn, $sql);
$totalStaff = mysqli_fetch_assoc($result)['total_staff'];

// Get total attendance today
$today = date('Y-m-d');
$sql = "SELECT COUNT(*) as total_attendance FROM attendance WHERE date = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $today);
mysqli_stmt_execute($stmt);
$todayAttendance = mysqli_stmt_get_result($stmt)->fetch_assoc()['total_attendance'];

// Get monthly attendance summary
$currentMonth = date('Y-m');
$sql = "SELECT 
            COUNT(*) as total_days,
            SUM(CASE WHEN time_out IS NOT NULL THEN 1 ELSE 0 END) as completed_days
        FROM attendance 
        WHERE DATE_FORMAT(date, '%Y-%m') = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $currentMonth);
mysqli_stmt_execute($stmt);
$monthlyStats = mysqli_stmt_get_result($stmt)->fetch_assoc();

// Get weekly attendance data for graph
$sql = "SELECT 
            DATE(date) as date,
            COUNT(*) as total,
            SUM(CASE WHEN time_out IS NOT NULL THEN 1 ELSE 0 END) as completed
        FROM attendance 
        WHERE date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(date)
        ORDER BY date";
$result = mysqli_query($conn, $sql);
$weeklyData = $result;

// Get staff attendance distribution
$sql = "SELECT 
            u.name,
            COUNT(a.id) as attendance_count
        FROM users u
        LEFT JOIN attendance a ON u.id = a.user_id
        WHERE u.role = 'staff'
        AND a.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY u.id
        ORDER BY attendance_count DESC
        LIMIT 5";
$result = mysqli_query($conn, $sql);
$staffAttendance = $result;

// Get recent activity
$sql = "SELECT al.*, u.name as user_name 
        FROM activity_logs al
        JOIN users u ON al.user_id = u.id
        ORDER BY al.created_at DESC LIMIT 5";
$result = mysqli_query($conn, $sql);
$activities = $result;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .stat-card {
            border-radius: 10px;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        element.style {
    display: block;
    box-sizing: border-box;
    height: 204px;
    width: 204px;
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
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_staff.php">
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
                    <h2>Dashboard</h2>
                    <div class="user-info">
                        <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <span class="badge bg-primary"><?php echo htmlspecialchars($_SESSION['user_role']); ?></span>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-users stat-icon"></i> Total Staff
                                </h5>
                                <h2 class="mt-3"><?php echo $totalStaff; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-calendar-check stat-icon"></i> Today's Attendance
                                </h5>
                                <h2 class="mt-3"><?php echo $todayAttendance; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-chart-pie stat-icon"></i> Monthly Completion
                                </h5>
                                <h2 class="mt-3">
                                    <?php
                                    $rate = $monthlyStats['total_days'] > 0 
                                        ? round(($monthlyStats['completed_days'] / $monthlyStats['total_days']) * 100, 1) 
                                        : 0;
                                    echo $rate . '%';
                                    ?>
                                </h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Graphs -->
                <div class="row mb-4">
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Weekly Attendance Overview</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="weeklyAttendanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Top Staff Attendance</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="staffAttendanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($activity = mysqli_fetch_assoc($activities)): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y h:i A', strtotime($activity['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($activity['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($activity['action']); ?></td>
                                        <td><?php echo htmlspecialchars($activity['details']); ?></td>
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
        // Weekly Attendance Chart
        const weeklyCtx = document.getElementById('weeklyAttendanceChart').getContext('2d');
        new Chart(weeklyCtx, {
            type: 'bar',
            data: {
                labels: [
                    <?php 
                    $weeklyData->data_seek(0);
                    while ($row = mysqli_fetch_assoc($weeklyData)) {
                        echo "'" . date('M d', strtotime($row['date'])) . "',";
                    }
                    ?>
                ],
                datasets: [{
                    label: 'Total Attendance',
                    data: [
                        <?php 
                        $weeklyData->data_seek(0);
                        while ($row = mysqli_fetch_assoc($weeklyData)) {
                            echo $row['total'] . ",";
                        }
                        ?>
                    ],
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }, {
                    label: 'Completed Days',
                    data: [
                        <?php 
                        $weeklyData->data_seek(0);
                        while ($row = mysqli_fetch_assoc($weeklyData)) {
                            echo $row['completed'] . ",";
                        }
                        ?>
                    ],
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Staff Attendance Chart
        const staffCtx = document.getElementById('staffAttendanceChart').getContext('2d');
        new Chart(staffCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php 
                    $staffAttendance->data_seek(0);
                    while ($row = mysqli_fetch_assoc($staffAttendance)) {
                        echo "'" . $row['name'] . "',";
                    }
                    ?>
                ],
                datasets: [{
                    data: [
                        <?php 
                        $staffAttendance->data_seek(0);
                        while ($row = mysqli_fetch_assoc($staffAttendance)) {
                            echo $row['attendance_count'] . ",";
                        }
                        ?>
                    ],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(153, 102, 255, 0.5)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    </script>
</body>
</html> 