<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . "/attendanceapp/database/database.php";
function clearTable($dbo, $tabName)
{
  $c = "delete from ".$tabName;
  $s = $dbo->conn->prepare($c);
  try {
    $s->execute();
    echo($tabName." cleared");
  } catch (PDOException $oo) {
    echo($oo->getMessage());
  }
}
$dbo = new Database();
$c = "create table student_details
(
    id int auto_increment primary key,
    roll_no varchar(20) unique,
    name varchar(50),
    email_id varchar(100)
)";
$s = $dbo->conn->prepare($c);
try {
  $s->execute();
  echo ("<br>student_details created");
} catch (PDOException $o) {
  echo ("<br>student_details not created");
}

$c = "create table course_details
(
    id int auto_increment primary key,
    code varchar(20) unique,
    title varchar(50),
    credit int
)";
$s = $dbo->conn->prepare($c);
try {
  $s->execute();
  echo ("<br>course_details created");
} catch (PDOException $o) {
  echo ("<br>course_details not created");
}


$c = "create table faculty_details
(
    id int auto_increment primary key,
    user_name varchar(20) unique,
    name varchar(100),
    password varchar(50)
)";
$s = $dbo->conn->prepare($c);
try {
  $s->execute();
  echo ("<br>faculty_details created");
} catch (PDOException $o) {
  echo ("<br>faculty_details not created");
}


$c = "create table session_details
(
    id int auto_increment primary key,
    year int,
    term varchar(50),
    unique (year,term)
)";
$s = $dbo->conn->prepare($c);
try {
  $s->execute();
  echo ("<br>session_details created");
} catch (PDOException $o) {
  echo ("<br>session_details not created");
}



$c = "create table course_registration
(
    student_id int,
    course_id int,
    session_id int,
    primary key (student_id,course_id,session_id)
)";
$s = $dbo->conn->prepare($c);
try {
  $s->execute();
  echo ("<br>course_registration created");
} catch (PDOException $o) {
  echo ("<br>course_registration not created");
}
clearTable($dbo, "course_registration");

$c = "create table course_allotment
(
    faculty_id int,
    course_id int,
    session_id int,
    primary key (faculty_id,course_id,session_id)
)";
$s = $dbo->conn->prepare($c);
try {
  $s->execute();
  echo ("<br>course_allotment created");
} catch (PDOException $o) {
  echo ("<br>course_allotment not created");
}
clearTable($dbo, "course_allotment");

$c = "create table attendance_details
(
    faculty_id int,
    course_id int,
    session_id int,
    student_id int,
    on_date date,
    status varchar(10),
    primary key (faculty_id,course_id,session_id,student_id,on_date)
)";
$s = $dbo->conn->prepare($c);
try {
  $s->execute();
  echo ("<br>attendance_details created");
} catch (PDOException $o) {
  echo ("<br>attendance_details not created");
}
clearTable($dbo, "attendance_details");

$c = "create table sent_email_details
(
    faculty_id int,
    course_id int,
    session_id int,
    student_id int,
    on_date date,
    id int auto_increment primary key,
    message varchar(200),
    to_email varchar(100)
)";
$s = $dbo->conn->prepare($c);
try {
  $s->execute();
  echo ("<br>sent_email_details created");
} catch (PDOException $o) {
  echo ("<br>sent_email_details not created");
}
clearTable($dbo, "sent_email_details");

clearTable($dbo, "student_details");
$c = "insert into student_details
(id,roll_no,name,email_id)
values
(1,'21E31A6601','Aditya Chauhan','chauhanadityac6@gmail.com'),
(2,'21E31A6602','Ahmed Mohi Uddin','Ahmedmohiuddinzubair1006@gmail.com'),
(3,'21E31A6603','Navya Sri','navyasrialapati0@gmail.com'),
(4,'21E31A6604','Bhanu Prasad','bhanuprasadgoudanegouni@gmail.com'),
(5,'21E31A6606','Arif Mallick','arifmallick1432@gmail.com'),
(6,'21E31A6607','Asfiya Naaz','Naazasfiya5@gmail.com'),
(7,'21E31A6608','Bharath Simha Reddy','daggulabharathsimhareddy@gmail.com'),
(8,'21E31A6609','B.Harshitha','harshithabodhu09@gmail.com'),
(9,'21E31A6610','B.Hitesh','hitheshbommani@gmail.com'),
(10,'21E31A6612','Eda.Srinath','esrinath98@gmail.com'),
(11,'21E31A6613','G.Sathvick','saisathvick29@gmail.com'),
(12,'21E31A6614','K.Surath','Surathsurath570@gmail.com'),
(13,'21E31A6615','K.Karthik','karthikriders244@gmail.com'),

(14,'21E31A6617','K.Rahul','Kodurirahulgoud@gmail.com'),
(15,'21E31A6618','M.Lakshmi Pathi','lakshmipathimangu1681861@gmail.com'),
(16,'21E31A6619','M.Mahesh','tonys2002mail@gmail.com'),
(17,'21E31A6620','Vinay Kumar','Vinaykumarmegadi@gmail.com'),
(18,'21E31A6622','Md.Asad','asadtafheem123@gmail.com'),
(19,'21E31A6623','Md.Mumshad','mohdabdulmumshad@gmail.com'),
(20,'21E31A6624','P.Guru Pyari','gurupyaripaduchuri@gmail.com'),
(21,'21E31A6625','T.Rohith','rohiththota572@gmail.com'),
(22,'21E31A6626','V.Koushik','koushikvadla555@gmail.com'),
(23,'22E35A6602','G.Karthik','karthikgadipelli1@gmail.com'),
(24,'22E35A6603','P.Santhosh','pegadapallisanthosh@gmail.com'),
(25,'22E35A6604','Venu Chary','venuchary057@gmail.com'),
(26,'22E35A6605','K.Puskal','tadikondapushkal@gmail.com')
";

$s = $dbo->conn->prepare($c);
try {
  $s->execute();
} catch (PDOException $o) {
  echo ("<br>duplicate entry");
}

clearTable($dbo, "faculty_details");
$c = "insert into faculty_details
(id,user_name,password,name)
values
(1,'mani','123','Mr.Manindhra Sir'),
(2,'arshad','123','Mr.Arshad Sir'),
(3,'anas','123','Mr.Anas Ali Sir'),
(4,'shirmila','123','Mrs.Shirmila Mam'),
(5,'sudha','123','Mr.Sudhakaran Sir'),
(6,'swapna','123','Mrs.Swapna Mam')";

$s = $dbo->conn->prepare($c);
try {
  $s->execute();
} catch (PDOException $o) {
  echo ("<br>duplicate entry");
}

clearTable($dbo, "session_details");
$c = "insert into session_details
(id,year,term)
values
(1,2024,'WINTER SEMESTER'),
(2,2025,'SUMMER SEMESTER')";

$s = $dbo->conn->prepare($c);
try {
  $s->execute();
} catch (PDOException $o) {
  echo ("<br>duplicate entry");
}

clearTable($dbo, "course_details");
$c = "insert into course_details
(id,title,code,credit)
values
  (1,'Deep Learning','DP41',3),
  (2,'Reinforcement Learning','RL41',2),
  (3,'Web Security','WS41',3),
  (4,'Cloud Computing','CC41',3),
  (5,'Electronic sensor','ES41',3),
  (6,'Project Stage','PS41',3)";
$s = $dbo->conn->prepare($c);
try {
  $s->execute();
} catch (PDOException $o) {
  echo ("<br>duplicate entry");
}

//if any record already there in the table delete them
clearTable($dbo, "course_registration");
$c = "insert into course_registration
  (student_id,course_id,session_id)
  values
  (:sid,:cid,:sessid)";
$s = $dbo->conn->prepare($c);

// Winter semester courses (1-3)
for ($i = 1; $i <= 26; $i++) {
    for ($j = 0; $j < 3; $j++) {
        $cid = $j + 1; // Courses 1-3 for winter
        try {
            $s->execute([":sid" => $i, ":cid" => $cid, ":sessid" => 1]);
        } catch (PDOException $pe) {
        }
    }
}

// Summer semester courses (4-6)
for ($i = 1; $i <= 26; $i++) {
    for ($j = 0; $j < 3; $j++) {
        $cid = $j + 4; // Courses 4-6 for summer
        try {
            $s->execute([":sid" => $i, ":cid" => $cid, ":sessid" => 2]);
        } catch (PDOException $pe) {
        }
    }
}

// Course allotment for faculty
clearTable($dbo, "course_allotment");
$c = "insert into course_allotment
  (faculty_id,course_id,session_id)
  values
  (:fid,:cid,:sessid)";
$s = $dbo->conn->prepare($c);

// Winter semester faculty allotment
for ($i = 1; $i <= 3; $i++) {
    for ($j = 0; $j < 3; $j++) {
        $cid = $j + 1; // Courses 1-3 for winter
        try {
            $s->execute([":fid" => $i, ":cid" => $cid, ":sessid" => 1]);
        } catch (PDOException $pe) {
        }
    }
}

// Summer semester faculty allotment
for ($i = 4; $i <= 6; $i++) {
    for ($j = 0; $j < 3; $j++) {
        $cid = $j + 4; // Courses 4-6 for summer
        try {
            $s->execute([":fid" => $i, ":cid" => $cid, ":sessid" => 2]);
        } catch (PDOException $pe) {
        }
    }
}
