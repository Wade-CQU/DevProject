<?php
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

  $target_dir = "Assignments/$unitId/$assignmentId/$userId/";
  $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
  $uploadOk = 1;
  $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

  //Checks if you selected a file to upload.
  if(!isset($_FILES["fileToUpload"]["name"][0])){
    $uploadOk = 3;
  }

  if($uploadOk != 3){
    if (is_dir("Assignments/") == false) {
      mkdir("Assignments/", 0777);
    }
    if (is_dir("Assignments/$unitId/") == false) {
      mkdir("Assignments/$unitId/", 0777);
    }
    if (is_dir("Assignments/$unitId/$assignmentId/") == false) {
      mkdir("Assignments/$unitId/$assignmentId/", 0777);
    }
    if (is_dir("Assignments/$unitId/$assignmentId/$userId/") == false) {
      mkdir("Assignments/$unitId/$assignmentId/$userId/", 0777);
    } else {
      $uploadOk = 0;
      //echo "Directory already exists";
    }
  }

  // Check if $uploadOk is set to 0 by an error
  if ($uploadOk == 0) {
    echo "<h1>Sorry, you have already submitted this assignment. Please contact NU support or your lecturer if this is a mistake.</h1>";
    // if everything is ok, try to upload file
  }
  if ($uploadOk == 3) {
    echo "<h1>Sorry, you have not selected a file to upload, please go back and try again.</h1>";
  } 
  if($uploadOk == 1) {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {

      //Creates sql to add a submission
      $sql = "INSERT INTO submission (assignmentsId, userId, grade, status, submitDate) VALUES (?, ?, ?, ?, ?);";
      $stmt = $dbh->prepare($sql);
      $stmt->bind_param("iiiis", $assignmentId, $studentId, $grade, $status, $date);
      $stmt->execute();
      $stmt->close();

      echo "<h1>Your assignment file '" . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . "' has been uploaded and is waiting for marking.\n</h1>";
    } else {
      echo "<h1>Sorry, there was an error uploading your file.\n<h1>";
    }
  }

  ?>
  <button class="back-button" onclick="document.location='/devproject/unit.php?id=<?php echo $unitId; ?>'">
  <img class="back-icon" src="assets/fontAwesomeIcons/back.svg" />Go back</button>
</body>
</html>
<style>
  .back-button{
        padding: 5px;
        font-size: 15px;
        display: inline-flex;
        gap: 10px;
        margin-top:20px;
        margin-left:20px;
    }
</style>
