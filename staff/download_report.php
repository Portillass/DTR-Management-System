<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staff') {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$format = $_GET['format'] ?? 'csv';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Get attendance records
$sql = "SELECT a.*, u.name as user_name 
        FROM attendance a 
        JOIN users u ON a.user_id = u.id 
        WHERE a.user_id = ? AND a.date BETWEEN ? AND ?
        ORDER BY a.date DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "iss", $user_id, $start_date, $end_date);
mysqli_stmt_execute($stmt);
$attendance_records = mysqli_stmt_get_result($stmt);

// Get summary statistics
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

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="attendance_report.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for proper Excel encoding
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add title and period
fputcsv($output, ['Attendance Report']);
fputcsv($output, ['Period: ' . date('M d, Y', strtotime($start_date)) . ' to ' . date('M d, Y', strtotime($end_date))]);
fputcsv($output, []); // Empty line

// Add summary
fputcsv($output, ['Summary']);
fputcsv($output, ['Total Days', $summary['total_days']]);
fputcsv($output, ['Completed Days', $summary['completed_days']]);
fputcsv($output, ['Average Hours', round($summary['avg_hours'], 1)]);
fputcsv($output, []); // Empty line

// Add table header
fputcsv($output, ['Date', 'Time In', 'Time Out', 'Hours', 'Status']);

// Add table data
while ($record = mysqli_fetch_assoc($attendance_records)) {
    $hours = $record['time_out'] 
        ? round((strtotime($record['time_out']) - strtotime($record['time_in'])) / 3600, 1)
        : '-';
    
    fputcsv($output, [
        date('M d, Y', strtotime($record['date'])),
        date('h:i A', strtotime($record['time_in'])),
        $record['time_out'] ? date('h:i A', strtotime($record['time_out'])) : 'Not checked out',
        $hours,
        $record['time_out'] ? 'Completed' : 'Active'
    ]);
}

// Close the output stream
fclose($output);
?> 