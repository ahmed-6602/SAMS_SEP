<?php
session_start();
header('Content-Type: application/json');

// Check if student is logged in
if (!isset($_SESSION['student'])) {
    echo json_encode(["error" => "Not logged in"]);
    exit();
}

$studentId = $_SESSION['student'];

try {
    // Use PDO instead of mysqli for better performance and security
    $pdo = new PDO("mysql:host=localhost;dbname=attendance_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get all attendance records for the student
    $query = "SELECT on_date AS date, status 
              FROM attendance_details 
              WHERE student_id = ?
              ORDER BY on_date DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$studentId]);
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process data in memory
    $formatted_data = array_map(function($row) {
        return [
            'date' => date("Y-m-d", strtotime($row['date'])),
            'status' => $row['status']
        ];
    }, $data);

    echo json_encode($formatted_data);

} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
