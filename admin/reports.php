<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Get date range and staff filter from request or default to current month
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$staff_filter = $_GET['staff_filter'] ?? '';

// Get all staff members for the filter dropdown
$sql = "SELECT id, name FROM users WHERE role = 'staff' ORDER BY name";
$staff_members = mysqli_query($conn, $sql);

// Build the attendance records query
$sql = "SELECT 
            a.date,
            u.name as staff_name,
            a.time_in,
            a.time_out,
            TIMESTAMPDIFF(HOUR, a.time_in, a.time_out) as hours_worked,
            CASE 
                WHEN a.time_out IS NOT NULL THEN 'Completed'
                ELSE 'Active'
            END as status
        FROM attendance a
        JOIN users u ON a.user_id = u.id
        WHERE a.date BETWEEN ? AND ?";

$params = [$start_date, $end_date];
$types = "ss";

// Add staff filter if selected
if (!empty($staff_filter)) {
    $sql .= " AND u.id = ?";
    $params[] = $staff_filter;
    $types .= "i";
}

$sql .= " ORDER BY a.date DESC, u.name";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$attendance_records = mysqli_stmt_get_result($stmt);

// Build the summary statistics query
$sql = "SELECT 
            COUNT(*) as total_days,
            SUM(CASE WHEN time_out IS NOT NULL THEN 1 ELSE 0 END) as completed_days,
            AVG(TIMESTAMPDIFF(HOUR, time_in, time_out)) as avg_hours
        FROM attendance a
        JOIN users u ON a.user_id = u.id
        WHERE a.date BETWEEN ? AND ?";

$params = [$start_date, $end_date];
$types = "ss";

// Add staff filter if selected
if (!empty($staff_filter)) {
    $sql .= " AND u.id = ?";
    $params[] = $staff_filter;
    $types .= "i";
}

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$summary = mysqli_stmt_get_result($stmt)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Dashboard</title>
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
        .stat-card {
            border-radius: 10px;
            transition: transform 0.2s;
        }
        .stat-card:hover {
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
                        <a class="nav-link active" href="reports.php">
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
                    <h2>Reports</h2>
                    <div class="user-info">
                        <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <span class="badge bg-primary"><?php echo htmlspecialchars($_SESSION['user_role']); ?></span>
                    </div>
                </div>

                <!-- Date Range Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Staff Member</label>
                                <select class="form-select" name="staff_filter">
                                    <option value="">All Staff</option>
                                    <?php while ($staff = mysqli_fetch_assoc($staff_members)): ?>
                                        <option value="<?php echo $staff['id']; ?>" <?php echo $staff_filter == $staff['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($staff['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="export_report.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&staff_filter=<?php echo $staff_filter; ?>" 
                                   class="btn btn-success">
                                    <i class="fas fa-file-excel"></i> Export to Excel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Summary Statistics -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-calendar-check stat-icon"></i> Total Days
                                </h5>
                                <h2 class="mt-3"><?php echo $summary['total_days']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-check-circle stat-icon"></i> Completed Days
                                </h5>
                                <h2 class="mt-3"><?php echo $summary['completed_days']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-clock stat-icon"></i> Average Hours
                                </h5>
                                <h2 class="mt-3"><?php echo round($summary['avg_hours'], 1); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Records -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Attendance Records</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Staff Name</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th>Hours Worked</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($record = mysqli_fetch_assoc($attendance_records)): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
                                        <td><?php echo htmlspecialchars($record['staff_name']); ?></td>
                                        <td><?php echo date('h:i A', strtotime($record['time_in'])); ?></td>
                                        <td><?php echo $record['time_out'] ? date('h:i A', strtotime($record['time_out'])) : 'Not checked out'; ?></td>
                                        <td><?php echo $record['hours_worked'] ? round($record['hours_worked'], 1) : '-'; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $record['status'] === 'Completed' ? 'success' : 'warning'; ?>">
                                                <?php echo $record['status']; ?>
                                            </span>
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