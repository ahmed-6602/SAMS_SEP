<?php
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/attendanceapp/database/database.php";

class student_details
{
    public function verifyUser($dbo, $email, $pw)
    {
        $rv = ["id" => -1, "status" => "ERROR"];
        $query = "SELECT id, password FROM student_details WHERE email_id = :email";
        $stmt = $dbo->conn->prepare($query);
        try {
            $stmt->execute([":email" => $email]);
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
                if ($result['password'] == $pw) {
                    $rv = ["id" => $result['id'], "status" => "ALL OK"];
                } else {
                    $rv = ["id" => $result['id'], "status" => "Wrong password"];
                }
            } else {
                $rv = ["id" => -1, "status" => "Email does not exist"];
            }
        } catch (PDOException $e) {
            // log error if needed
        }
        return $rv;
    }

    public function getStudentName($dbo, $studentId)
    {
        $name = '';
        $query = "SELECT name FROM student_details WHERE id = :id";
        $stmt = $dbo->conn->prepare($query);
        try {
            $stmt->execute([":id" => $studentId]);
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
                $name = $result['name'];
            }
        } catch (PDOException $e) {
            // log error
        }
        return $name;
    }

    // Optional method to fetch attendance
    public function getAttendance($dbo, $studentId)
    {
        $data = [];
        $query = "SELECT on_date AS date, status FROM attendance_details WHERE student_id = :id";
        $stmt = $dbo->conn->prepare($query);
        try {
            $stmt->execute([":id" => $studentId]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // log error
        }
        return $data;
    }
}
?>
