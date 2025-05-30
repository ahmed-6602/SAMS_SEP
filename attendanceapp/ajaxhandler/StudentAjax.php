<?php
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/attendanceapp/database/database.php";
require_once $path . "/attendanceapp/database/StudentDetails.php"; // ✅ case-sensitive match
$action = $_REQUEST["action"];

if (!empty($action)) {
    if ($action == "verifyUser") {
        // retrieve what was sent
        $un = $_POST["user_name"];
        $pw = $_POST["password"];

        // check if exists in database
        $dbo = new Database();
        $sdo = new student_details(); // ✅ Correct class for students
        $rv = $sdo->verifyUser($dbo, $un, $pw);

        if ($rv['status'] == "ALL OK") {
            session_start();
            $_SESSION['student'] = $rv['id']; // ✅ Session variable for students
        }

        // artificial delay (not needed in production)
        for ($i = 0; $i < 100000; $i++) {
            for ($j = 0; $j < 2000; $j++) {}
        }

        echo json_encode($rv);
    }
}
?>
