<?php
//TODO Delete contents on upload for data purging
include("php/session.php");
include("php/dbConnect.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="/devproject/css/default.css" rel="stylesheet" />
  <link href="/devproject/css/term.css" rel="stylesheet" />
  <title></title>
</head>

<body>
  <?php require("php/header.php"); ?>

  <?php
  $unitId = $_GET['unitId'];
  $studentId = $_GET['userId'];
  $assignmentId = $_GET['assignmentId'];
  $date = date("Y-m-d H:i:s");
  $grade = 0;
  $status = 1;
  echo $date;
  echo "Assignment ID = " . $assignmentId . " ----- ";
  echo "Unit ID = " . $unitId . " ----- ";

  $target_dir = "Assignments/$unitId/$assignmentId/$userId/";
  $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
  $uploadOk = 1;
  $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

  //Creates sql to add a submission
  $sql = "INSERT INTO submission (assignmentsId, userId, grade, status, submitDate) VALUES (?, ?, ?, ?, ?);";
  $stmt = $dbh->prepare($sql);
  $stmt->bind_param("iiiis", $assignmentId, $studentId, $grade, $status, $date);
  $stmt->execute();
  $stmt->close();

  //TODO check if user has uploaded already then update submitDate

  if (is_dir("Assignments/") == false) {
    mkdir("Assignments/", 0777);
    echo "here 1 ";
  }
  if (is_dir("Assignments/$unitId/") == false) {
    mkdir("Assignments/$unitId/", 0777);
    echo "here 2 ";
  }
  if (is_dir("Assignments/$unitId/$assignmentId/") == false) {
    mkdir("Assignments/$unitId/$assignmentId/", 0777);
    echo "here 3 ";
  }
  if (is_dir("Assignments/$unitId/$assignmentId/$userId/") == false) {
    mkdir("Assignments/$unitId/$assignmentId/$userId/", 0777);
    echo "here 4 ";
  } else {
    echo "here else ";
  }

  // Check if $uploadOk is set to 0 by an error
  if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
  } else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
      echo "The file " . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . " has been uploaded.\n";
    } else {
      echo "Sorry, there was an error uploading your file.\n";
    }
  }

  ?>


</body>

</html>
