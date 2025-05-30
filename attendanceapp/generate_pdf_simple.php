<?php
session_start();
if (!isset($_SESSION['student'])) {
    header("Location: StudentLogin.php");
    exit();
}

$student_id = $_SESSION['student'];

// ✅ Correct path to database file
require_once 'database/database.php';

$dbo = new Database();
$conn = $dbo->conn;

// 🔹 Student Info
$stmt = $conn->prepare("SELECT name, roll_no, email_id FROM student_details WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// 🔹 Get Subject-wise Attendance
$subjectQuery = "SELECT 
    c.title as course_name,
    COUNT(CASE WHEN a.status IN ('P', 'YES') THEN 1 END) as present_count,
    COUNT(CASE WHEN a.status NOT IN ('P', 'YES') THEN 1 END) as absent_count,
    COUNT(*) as total_classes
FROM course_registration cr
JOIN course_details c ON cr.course_id = c.id
LEFT JOIN attendance_details a ON a.student_id = cr.student_id AND a.course_id = c.id
WHERE cr.student_id = ?
GROUP BY c.id, c.title";

$subjectStmt = $conn->prepare($subjectQuery);
$subjectStmt->execute([$student_id]);
$subject_attendance = $subjectStmt->fetchAll(PDO::FETCH_ASSOC);

// 🔹 Total Attendance Summary
$totalQuery = "SELECT 
    COUNT(CASE WHEN status IN ('P', 'YES') THEN 1 END) as total_present,
    COUNT(CASE WHEN status NOT IN ('P', 'YES') THEN 1 END) as total_absent,
    COUNT(*) as total_classes
FROM attendance_details 
WHERE student_id = ?";

$totalStmt = $conn->prepare($totalQuery);
$totalStmt->execute([$student_id]);
$total_attendance = $totalStmt->fetch(PDO::FETCH_ASSOC);

// Calculate percentages
$total_classes = $total_attendance['total_classes'];
$total_present = $total_attendance['total_present'];
$total_absent = $total_attendance['total_absent'];
$present_percent = $total_classes > 0 ? round(($total_present / $total_classes) * 100) : 0;
$absent_percent = 100 - $present_percent;

// Set content type header
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .student-info {
            margin-bottom: 20px;
        }
        .attendance-summary {
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
        @media print {
            body {
                margin: 0;
                padding: 20px;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Attendance Report</h1>
    </div>

    <div class="student-info">
        <h2>Student Information</h2>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($student['name']); ?></p>
        <p><strong>Roll No:</strong> <?php echo htmlspecialchars($student['roll_no']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email_id']); ?></p>
    </div>

    <div class="attendance-summary">
        <h2>Overall Attendance Summary</h2>
        <p><strong>Total Classes:</strong> <?php echo $total_classes; ?></p>
        <p><strong>Present:</strong> <?php echo $total_present; ?> (<?php echo $present_percent; ?>%)</p>
        <p><strong>Absent:</strong> <?php echo $total_absent; ?> (<?php echo $absent_percent; ?>%)</p>
    </div>

    <h2>Subject-wise Attendance</h2>
    <table>
        <thead>
            <tr>
                <th>Subject</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Total Classes</th>
                <th>Attendance %</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subject_attendance as $subject): ?>
            <tr>
                <td><?php echo htmlspecialchars($subject['course_name']); ?></td>
                <td><?php echo $subject['present_count']; ?></td>
                <td><?php echo $subject['absent_count']; ?></td>
                <td><?php echo $subject['total_classes']; ?></td>
                <td>
                    <?php 
                    $subject_percent = $subject['total_classes'] > 0 
                        ? round(($subject['present_count'] / $subject['total_classes']) * 100) 
                        : 0;
                    echo $subject_percent . '%';
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on: <?php echo date('d M Y H:i:s'); ?></p>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()">Print Report</button>
    </div>
</body>
</html> 