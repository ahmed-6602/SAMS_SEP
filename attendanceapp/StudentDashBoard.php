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

// Get current date and time
$current_date = date('Y-m-d');
$current_time = date('H:i:s');

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
            --warning-color: #ff9800;
            --info-color: #2196f3;
            --background-color: #f0f2f5;
            --card-background: #ffffff;
            --text-primary: #2b2d42;
            --text-secondary: #6c757d;
            --gradient-primary: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%);
            --gradient-success: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
            --gradient-danger: linear-gradient(135deg, #f44336 0%, #e53935 100%);
            --gradient-warning: linear-gradient(135deg, #ff9800 0%, #fb8c00 100%);
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
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .dashboard-header {
            background: var(--gradient-primary);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
            pointer-events: none;
        }

        .welcome-text h2 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .welcome-text p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .datetime-info {
            text-align: right;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .card {
            background: var(--card-background);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient-primary);
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card h3 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            color: var(--text-secondary);
            font-size: 1.1rem;
            padding: 0.5rem;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .info-item:hover {
            background-color: #f8f9fa;
        }

        .info-item strong {
            color: var(--text-primary);
            margin-right: 0.8rem;
            font-weight: 500;
            min-width: 100px;
        }

        .chart-container {
            background: var(--card-background);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            width: 100%;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .filter-section {
            background: var(--card-background);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        select {
            padding: 0.8rem 1.5rem;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            font-size: 1rem;
            color: var(--text-primary);
            background-color: white;
            transition: all 0.3s ease;
            min-width: 200px;
        }

        select:hover {
            border-color: var(--primary-color);
        }

        .download-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            background: var(--gradient-primary);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .attendance-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 1rem;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .attendance-table th,
        .attendance-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .attendance-table th {
            background: var(--gradient-primary);
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .attendance-table tr:last-child td {
            border-bottom: none;
        }

        .attendance-table tr:hover {
            background-color: #f8f9fa;
        }

        .status-present {
            color: var(--success-color);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-absent {
            color: var(--danger-color);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            margin-top: 0.5rem;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--gradient-success);
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .subject-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .subject-card:hover {
            transform: translateY(-2px);
        }

        .subject-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .subject-name {
            font-weight: 600;
            color: var(--text-primary);
        }

        .subject-stats {
            display: flex;
            gap: 1rem;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }

            .dashboard-header {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .filter-section {
                flex-direction: column;
                gap: 1rem;
            }

            .datetime-info {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <div class="welcome-text">
                <h2>Welcome, <?php echo htmlspecialchars($student['name']); ?></h2>
                <p>Roll No: <?php echo htmlspecialchars($student['roll_no']); ?></p>
                <div class="datetime-info">
                    <p><?php echo date('l, F j, Y'); ?></p>
                    <p><?php echo date('h:i A'); ?></p>
                </div>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <h3><i class="fas fa-user-graduate"></i> Student Information</h3>
                <div class="info-item">
                    <strong>Name:</strong> <?php echo htmlspecialchars($student['name']); ?>
                </div>
                <div class="info-item">
                    <strong>Roll No:</strong> <?php echo htmlspecialchars($student['roll_no']); ?>
                </div>
                <div class="info-item">
                    <strong>Email:</strong> <?php echo htmlspecialchars($student['email_id']); ?>
                </div>
            </div>

            <div class="card">
                <h3><i class="fas fa-chart-pie"></i> Overall Attendance</h3>
                <div class="info-item">
                    <strong>Total Classes:</strong> <?php echo $total_classes; ?>
                </div>
                <div class="info-item">
                    <strong>Present:</strong> <?php echo $total_present; ?> (<?php echo $present_percent; ?>%)
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $present_percent; ?>%"></div>
                    </div>
                </div>
                <div class="info-item">
                    <strong>Absent:</strong> <?php echo $total_absent; ?> (<?php echo $absent_percent; ?>%)
                </div>
            </div>
        </div>

        <div class="chart-container">
            <canvas id="attendanceChart"></canvas>
        </div>

        <div class="filter-section">
            <select id="filter" onchange="window.location.href='?filter='+this.value">
                <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Time</option>
                <option value="month" <?php echo $filter === 'month' ? 'selected' : ''; ?>>This Month</option>
                <option value="week" <?php echo $filter === 'week' ? 'selected' : ''; ?>>This Week</option>
            </select>
            <a href="generate_pdf_simple.php" class="download-btn">
                <i class="fas fa-download"></i> Download Report
            </a>
        </div>

        <div class="card">
            <h3><i class="fas fa-book"></i> Subject-wise Attendance</h3>
            <?php foreach ($subject_attendance as $subject): 
                $subject_percent = $subject['total_classes'] > 0 
                    ? round(($subject['present_count'] / $subject['total_classes']) * 100) 
                    : 0;
            ?>
            <div class="subject-card">
                <div class="subject-header">
                    <div class="subject-name"><?php echo htmlspecialchars($subject['course_name']); ?></div>
                    <div class="subject-stats">
                        <span><i class="fas fa-check-circle"></i> <?php echo $subject['present_count']; ?> Present</span>
                        <span><i class="fas fa-times-circle"></i> <?php echo $subject['absent_count']; ?> Absent</span>
                        <span><i class="fas fa-chart-line"></i> <?php echo $subject_percent; ?>%</span>
                    </div>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $subject_percent; ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <h3><i class="fas fa-list"></i> Recent Attendance</h3>
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Subject</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($full_attendance as $record): ?>
                    <tr>
                        <td><?php echo date('d M Y', strtotime($record['on_date'])); ?></td>
                        <td><?php echo htmlspecialchars($record['course_name']); ?></td>
                        <td class="<?php echo $record['status'] === 'YES' ? 'status-present' : 'status-absent'; ?>">
                            <i class="fas <?php echo $record['status'] === 'YES' ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                            <?php echo $record['status'] === 'YES' ? 'Present' : 'Absent'; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Chart.js configuration
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent'],
                datasets: [{
                    data: [<?php echo $present_percent; ?>, <?php echo $absent_percent; ?>],
                    backgroundColor: [
                        '#4caf50',
                        '#f44336'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                family: 'Poppins',
                                size: 14
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
