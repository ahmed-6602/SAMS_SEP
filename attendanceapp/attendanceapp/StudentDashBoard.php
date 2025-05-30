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
$conn = $dbo->conn; // ✅ Directly access the PDO connection

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

// 🔹 Filter Logic
$filter = $_GET['filter'] ?? 'all';

if ($filter === 'month') {
    $query = "SELECT on_date, status, c.title as course_name 
              FROM attendance_details a
              JOIN course_details c ON a.course_id = c.id
              WHERE a.student_id = ? 
              AND MONTH(on_date) = MONTH(CURDATE()) AND YEAR(on_date) = YEAR(CURDATE()) 
              ORDER BY on_date DESC";
} elseif ($filter === 'week') {
    $query = "SELECT on_date, status, c.title as course_name 
              FROM attendance_details a
              JOIN course_details c ON a.course_id = c.id
              WHERE a.student_id = ? 
              AND WEEK(on_date, 1) = WEEK(CURDATE(), 1) AND YEAR(on_date) = YEAR(CURDATE()) 
              ORDER BY on_date DESC";
} else {
    $query = "SELECT on_date, status, c.title as course_name 
              FROM attendance_details a
              JOIN course_details c ON a.course_id = c.id
              WHERE a.student_id = ? 
              ORDER BY on_date DESC";
}

$fullStmt = $conn->prepare($query);
$fullStmt->execute([$student_id]);
$full_attendance = $fullStmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Student Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4caf50;
            --danger-color: #f44336;
            --background-color: #f8f9fa;
            --card-background: #ffffff;
            --text-primary: #2b2d42;
            --text-secondary: #6c757d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--background-color);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .dashboard-header {
            background: var(--card-background);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-text h2 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .logout-btn {
            background: var(--danger-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #d32f2f;
            transform: translateY(-2px);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: var(--card-background);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.8rem;
            color: var(--text-secondary);
        }

        .info-item strong {
            color: var(--text-primary);
            margin-right: 0.5rem;
        }

        .chart-container {
            background: var(--card-background);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            width: 300px;
            margin-left: auto;
            margin-right: auto;
        }

        .ml-box {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .ml-box h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .prediction-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .filter-section {
            background: var(--card-background);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        select {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
        }

        .download-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--primary-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .download-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 1rem;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--text-primary);
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .status-present {
            color: var(--success-color);
            font-weight: 500;
        }

        .status-absent {
            color: var(--danger-color);
            font-weight: 500;
        }

        .subject-card {
            background: var(--card-background);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
        }
        
        .subject-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .subject-name {
            font-size: 1.2rem;
            color: var(--primary-color);
            font-weight: 500;
        }
        
        .attendance-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .stat-item {
            text-align: center;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .stat-value {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                margin: 1rem auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <div class="welcome-text">
                <h2>Welcome, <?php echo htmlspecialchars($student['name']); ?> 👋</h2>
                <p>Here's your attendance overview</p>
            </div>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <h3><i class="fas fa-user"></i> Your Profile</h3>
                <div class="info-item">
                    <strong>Roll No:</strong> <?php echo htmlspecialchars($student['roll_no']); ?>
                </div>
                <div class="info-item">
                    <strong>Email:</strong> <?php echo htmlspecialchars($student['email_id']); ?>
                </div>
            </div>

            <div class="card">
                <h3><i class="fas fa-chart-pie"></i> Overall Attendance</h3>
                <div class="attendance-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo (int)$total_present; ?></div>
                        <div class="stat-label">Present</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo (int)$total_absent; ?></div>
                        <div class="stat-label">Absent</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo (int)$total_classes; ?></div>
                        <div class="stat-label">Total Classes</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="chart-container">
            <canvas id="attendanceChart"></canvas>
        </div>

        <div class="ml-box">
            <h3><i class="fas fa-robot"></i> Overall Attendance Prediction</h3>
            <div class="prediction-item">
                <i class="fas fa-check-circle"></i>
                <p>Present: <strong><?php echo (int)$present_percent; ?>%</strong></p>
            </div>
            <div class="prediction-item">
                <i class="fas fa-times-circle"></i>
                <p>Absent: <strong><?php echo (int)$absent_percent; ?>%</strong></p>
            </div>
        </div>

        <h3 style="margin: 2rem 0 1rem;"><i class="fas fa-book"></i> Subject-wise Attendance</h3>
        <?php foreach ($subject_attendance as $subject): ?>
            <div class="subject-card">
                <div class="subject-header">
                    <div class="subject-name"><?php echo htmlspecialchars($subject['course_name']); ?></div>
                </div>
                <div class="attendance-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo (int)$subject['present_count']; ?></div>
                        <div class="stat-label">Present</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo (int)$subject['absent_count']; ?></div>
                        <div class="stat-label">Absent</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">
                            <?php 
                            $subject_percent = $subject['total_classes'] > 0 
                                ? round(($subject['present_count'] / $subject['total_classes']) * 100) 
                                : 0;
                            echo (int)$subject_percent . '%';
                            ?>
                        </div>
                        <div class="stat-label">Attendance %</div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="filter-section">
            <form method="GET">
                <label for="filter">Filter by:</label>
                <select name="filter" id="filter" onchange="this.form.submit()">
                    <option value="all" <?php echo ($filter == 'all') ? 'selected' : ''; ?>>All Time</option>
                    <option value="month" <?php echo ($filter == 'month') ? 'selected' : ''; ?>>This Month</option>
                    <option value="week" <?php echo ($filter == 'week') ? 'selected' : ''; ?>>This Week</option>
                </select>
            </form>
            <div style="margin-top: 1rem;">
                <a href="generate_pdf.php" target="_blank" class="download-btn">
                    <i class="fas fa-download"></i> Download PDF Report
                </a>
            </div>
        </div>

        <div class="card">
            <h3><i class="fas fa-calendar-check"></i> Detailed Attendance Record</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Subject</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($full_attendance as $att): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(date("d-M-Y", strtotime($att['on_date']))); ?></td>
                        <td><?php echo htmlspecialchars($att['course_name']); ?></td>
                        <td class="<?php echo ($att['status'] === 'P' || $att['status'] === 'YES') ? 'status-present' : 'status-absent'; ?>">
                            <?php echo ($att['status'] === 'P' || $att['status'] === 'YES') ? 'Present' : 'Absent'; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('attendanceChart');
            if (ctx) {
                const attendanceChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Present', 'Absent'],
                        datasets: [{
                            data: [<?php echo (int)$total_present; ?>, <?php echo (int)$total_absent; ?>],
                            backgroundColor: ['#4caf50', '#f44336'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 15,
                                    font: {
                                        family: 'Poppins',
                                        size: 12
                                    }
                                }
                            }
                        },
                        cutout: '65%'
                    }
                });
            }
        });
    </script>
</body>
</html>
