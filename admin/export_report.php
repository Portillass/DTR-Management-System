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

// Get staff name if filter is applied
$staff_name = '';
if (!empty($staff_filter)) {
    $sql = "SELECT name FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $staff_filter);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $staff_name = $row['name'];
    }
}

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

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="attendance_report.xls"');
header('Cache-Control: max-age=0');

// Create Excel content
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        table { border-collapse: collapse; }
        th, td { border: 1px solid #000000; padding: 5px; }
        th { background-color: #f0f0f0; }
        .header { font-size: 16px; font-weight: bold; }
        .subheader { font-size: 14px; }
        .summary { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <table>
        <!-- Title -->
        <tr>
            <td colspan="6" class="header">Attendance Report</td>
        </tr>
        <tr>
            <td colspan="6" class="subheader">
                Period: <?php echo date('M d, Y', strtotime($start_date)); ?> to <?php echo date('M d, Y', strtotime($end_date)); ?>
                <?php if (!empty($staff_name)): ?>
                    <br>Staff Member: <?php echo htmlspecialchars($staff_name); ?>
                <?php endif; ?>
            </td>
        </tr>
        <tr><td colspan="6"></td></tr>

        <!-- Summary -->
        <tr class="summary">
            <td colspan="6" class="subheader">Summary Statistics</td>
        </tr>
        <tr class="summary">
            <td colspan="2">Total Days</td>
            <td colspan="4"><?php echo $summary['total_days']; ?></td>
        </tr>
        <tr class="summary">
            <td colspan="2">Completed Days</td>
            <td colspan="4"><?php echo $summary['completed_days']; ?></td>
        </tr>
        <tr class="summary">
            <td colspan="2">Average Hours per Day</td>
            <td colspan="4"><?php echo round($summary['avg_hours'], 1); ?></td>
        </tr>
        <tr><td colspan="6"></td></tr>

        <!-- Table Header -->
        <tr>
            <th>Date</th>
            <th>Staff Name</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Hours Worked</th>
            <th>Status</th>
        </tr>

        <!-- Table Data -->
        <?php while ($record = mysqli_fetch_assoc($attendance_records)): ?>
        <tr>
            <td><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
            <td><?php echo htmlspecialchars($record['staff_name']); ?></td>
            <td><?php echo date('h:i A', strtotime($record['time_in'])); ?></td>
            <td><?php echo $record['time_out'] ? date('h:i A', strtotime($record['time_out'])) : 'Not checked out'; ?></td>
            <td><?php echo $record['hours_worked'] ? round($record['hours_worked'], 1) : '-'; ?></td>
            <td><?php echo $record['status']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html> 