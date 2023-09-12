<?php
include("session.php");
include("dbConnect.php");

//Get post data

echo "  Student ID: = " . $_POST['userId'];
echo "  AssignmentId: = " . $_POST['assignmentId'];
echo "  UnitId = " . $_POST['unitId'];
echo "  Grade = " . $_POST['grade'];

$userId = $_POST['userId'];
$assignmentId = $_POST['assignmentId'];
$unitId = $_POST['unitId'];
$grade = $_POST['grade'];

//SQL to make grade
$sql = "UPDATE submission SET grade = $grade WHERE (assignmentsId = $assignmentId AND userId = $userId)";
echo $sql;
$stmt = $dbh->prepare($sql);
$stmt->execute();
$stmt->close();


header("location: assigmentMark.php?unitId=". $unitId . "&assignmentId=" . $assignmentId);
