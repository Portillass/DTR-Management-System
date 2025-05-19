<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staff') {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get date range from request or default to current month
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Get attendance records for the date range
$sql = "SELECT a.*, u.name as user_name 
        FROM attendance a 
        JOIN users u ON a.user_id = u.id 
        WHERE a.user_id = ? AND a.date BETWEEN ? AND ?
        ORDER BY a.date DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "iss", $user_id, $start_date, $end_date);
mysqli_stmt_execute($stmt);
$attendance_records = mysqli_stmt_get_result($stmt);

// Calculate summary statistics
$sql = "SELECT 
            COUNT(*) as total_days,
            SUM(CASE WHEN time_out IS NOT NULL THEN 1 ELSE 0 END) as completed_days,
            AVG(TIMESTAMPDIFF(HOUR, time_in, time_out)) as avg_hours
        FROM attendance 
        WHERE user_id = ? AND date BETWEEN ? AND ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "iss", $user_id, $start_date, $end_date);
mysqli_stmt_execute($stmt);
$summary = mysqli_stmt_get_result($stmt)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Staff Dashboard</title>
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
                        <a class="nav-link" href="attendance.php">
                            <i class="fas fa-calendar-check"></i> Attendance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="reports.php">
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
                    <h2>Attendance Reports</h2>
                    <div class="user-info">
                        <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <span class="badge bg-primary"><?php echo htmlspecialchars($_SESSION['user_role']); ?></span>
                    </div>
                </div>

                <!-- Date Range Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       value="<?php echo $start_date; ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" 
                                       value="<?php echo $end_date; ?>">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Filter</button>
                                <a href="download_report.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                                   class="btn btn-success">
                                    <i class="fas fa-file-excel"></i> Download Report
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Summary Statistics -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-calendar-check text-primary"></i> Total Days
                                </h5>
                                <h2 class="mt-3"><?php echo $summary['total_days']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-check-circle text-success"></i> Completed Days
                                </h5>
                                <h2 class="mt-3"><?php echo $summary['completed_days']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-clock text-info"></i> Average Hours
                                </h5>
                                <h2 class="mt-3"><?php echo round($summary['avg_hours'], 1); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Report -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Detailed Report</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th>Hours</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($record = mysqli_fetch_assoc($attendance_records)): 
                                        $hours = $record['time_out'] 
                                            ? round((strtotime($record['time_out']) - strtotime($record['time_in'])) / 3600, 1)
                                            : '-';
                                    ?>
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
                                        <td><?php echo $hours; ?></td>
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
</body>
</html> 