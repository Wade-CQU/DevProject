<?php
//TODO Upload multiple files
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

        //TODO's
        //$unitId = $_GET['unitId'];
        //$studentId = $_GET['userId'];
        //$assignmetnId = $_GET['assId'];

        $unitId = 1;
        $userId = 1;
        $assignmentId = 1;
        $target_dir = "Assignments/$unitId/$userId/$assignmentId/";
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));



        if (is_dir("Assignments/") == false){
          mkdir("Assignments/", 0777);
          echo "here 1 ";
        }
        if (is_dir("Assignments/$unitId/") == false){
          mkdir("Assignments/$unitId/", 0777);
          echo "here 2 ";
        }
        if(is_dir("Assignments/$unitId/$userId/") == false){
            mkdir("Assignments/$unitId/$userId/", 0777);
            echo "here 3 ";
        }
        if(is_dir("Assignments/$unitId/$userId/$assignmentId/") == false){
            mkdir("Assignments/$unitId/$userId/$assignmentId/", 0777);
            echo "here 4 ";
        } else{
          echo "here else ";
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
          // if everything is ok, try to upload file
          } else {
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
              echo "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.\n";
            } else {
              echo "Sorry, there was an error uploading your file.\n";
            }
          }

        ?>


    </body>
</html>
