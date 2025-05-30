<?php
$conn = new mysqli("localhost", "root", "", "attendance_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Total number of classes = total unique dates
$dateResult = $conn->query("SELECT COUNT(DISTINCT on_date) AS total_classes FROM attendance_details");
$total_classes = $dateResult->fetch_assoc()['total_classes'];

// Estimate number of weeks passed
$weeksResult = $conn->query("SELECT COUNT(DISTINCT WEEK(on_date)) AS weeks FROM attendance_details");
$weeks_passed = $weeksResult->fetch_assoc()['weeks'];

// Get all students
$students = $conn->query("SELECT roll_no, name FROM student_details");

// Prepare CSV
$fp = fopen('data/attendance.csv', 'w');
fputcsv($fp, ['student_id', 'name', 'total_classes', 'attended_classes', 'weeks_passed']);

while ($student = $students->fetch_assoc()) {
    $roll_no = $student['roll_no'];
    $name = $student['name'];

    // Count attended classes
    $attendedResult = $conn->query("SELECT COUNT(*) AS attended FROM attendance_details WHERE roll_no = '$roll_no' AND status = 'present'");
    $attended = $attendedResult->fetch_assoc()['attended'];

    // Write to CSV
    fputcsv($fp, [$roll_no, $name, $total_classes, $attended, $weeks_passed]);
}

fclose($fp);
echo "✅ Attendance exported to CSV successfully!";
$conn->close();
?>