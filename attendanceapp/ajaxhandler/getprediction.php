<?php
// Step 1: Include CSV Export (if needed for exporting)
include('exportAttendance.php');

// Step 2: Call Python Predictor
$command = escapeshellcmd("python3 ../predictor.py");
$output = shell_exec($command);

// Check if the output is valid
if (!$output) {
    echo "<p>Error running Python script or no data returned.</p>";
    exit();
}

// Step 3: Parse and Display
$data = json_decode($output, true);

// Check if the data was successfully decoded
if (!$data) {
    echo "<p>Failed to parse the prediction data.</p>";
    exit();
}

echo "<h2>📊 Attendance Prediction Report</h2>";

echo "<table border='1' cellpadding='8' cellspacing='0' style='width:100%; border-collapse: collapse;'>
        <tr style='background-color: #f2f2f2;'>
            <th>Student ID</th>
            <th>Name</th>
            <th>Attendance %</th>
            <th>Status</th>
        </tr>";

foreach ($data as $row) {
    // Ensure each key exists before accessing it
    $percent = isset($row['attendance_percent']) ? round($row['attendance_percent'] * 100, 2) : 'N/A';
    $status = isset($row['status']) ? $row['status'] : 'Unknown';
    $statusColor = $status === 'Safe' ? 'green' : 'red';
    
    // Display row with data
    echo "<tr>
            <td>{$row['student_id']}</td>
            <td>{$row['name']}</td>
            <td>{$percent}%</td>
            <td><b style='color: $statusColor;'>$status</b></td>
          </tr>";
}

echo "</table>";

?>
