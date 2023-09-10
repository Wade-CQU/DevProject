<?php
include("session.php");
include("dbConnect.php");

//Get post data

echo "Student ID: = " . $_POST['userId'];

$studentId = $_POST['userId'];
$assignmentId = $_POST['assignmentId'];
$unitId = $_POST['unitId'];
$grade = $_POST['grade'];

//SQL to make grade
$sql = "UPDATE submission SET grade = $grade WHERE (assignmentsId = $assignmentId AND userId = $userId)";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$stmt->close();


header("location: assigmentMark.php?unitId=". $unitId . "&assignmentId=" . $assignmentId);
