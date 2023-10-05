<?php
include("session.php");
include("dbConnect.php");

$unitId = $_GET['unitId'];
$userId = $_GET['userId'];
$assignmentId = $_GET['assignmentId'];

echo $assignmentId;

$target_dir = "../Assignments/$unitId/$assignmentId/$userId/markingsheet/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

if (is_dir($target_dir) == false) {
  mkdir($target_dir, 0777);
} else {
  $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
  echo "<h1>Sorry, there was an error uploading your file.</h1>";
  // if everything is ok, try to upload file
} else {
  if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
    echo "<h1>Marking sheet '" . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . "' has been uploaded.\n</h1>";
  } else {
    echo "<h1>Sorry, there was an error uploading your file.\n<h1>";
  }
}



echo "  Student ID: = " . $_POST['userId'];
echo "  AssignmentId: = " . $_POST['assignmentId'];
echo "  UnitId = " . $_POST['unitId'];
echo "  Grade = " . $_POST['grade'];
echo "  Comment = " . $_POST['comment'];


$userId = $_POST['userId'];
$assignmentId = $_POST['assignmentId'];
$unitId = $_POST['unitId'];
$grade = $_POST['grade'];
$comment = $_POST['comment'];
$status = 2;

//SQL to make grade
$sql = "UPDATE submission SET grade = $grade, comment = '$comment', status = $status WHERE (assignmentsId = $assignmentId AND userId = $userId)";
echo $sql;
$stmt = $dbh->prepare($sql);
$stmt->execute();
$stmt->close();


header("location: ../assigmentMark.php?unitId=" . $unitId . "&assignmentId=" . $assignmentId);
