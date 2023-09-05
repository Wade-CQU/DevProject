<?php

//TODO See list of files uploaded
//TODO Be able to delete files
//TODO See box to upload files
//TODO Show mark after it's been graded
//TODO PHP countdown for due date - date_diff()
//TODO Upload multiple files - https://www.tutorialspoint.com/how-to-upload-multiple-files-and-store-them-in-a-folder-with-php
//TODO Make sure the thingo gets the userID to work out the person's user levels

include("session.php");
include("dbConnect.php");

$unitId = $_GET['unitId'];
$count = 0;
$sql = "SELECT id, unitId, due, total, discription, specification FROM assignments WHERE unitId = ?";
$stmt = $dbh->prepare($sql);
$stmt->bind_param("i", $unitId);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$sqlUnit = "SELECT id, code, name, termCode FROM unit where id = ?";
$stmt = $dbh->prepare($sqlUnit);
$stmt->bind_param("i", $unitId);
$stmt->execute();
$resultUnit = $stmt->get_result();
$unit = $resultUnit->fetch_assoc();
$stmt->close();

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
        <?php require("header.php"); ?>
        
        <h1>Assignments for <?php echo $unit['name']; ?></h1>
        <br><br>

        <?php 
        while($assignment = $result->fetch_assoc()){ 
            $count++;
//just pass count lmao
        
        ?>

            <div class="centre">
                <table class="centre-allign" style="color:white">
                    <tr>
                        <th>Assignment #</th>
                        <th><?php echo $count; ?></th>
                    </tr>
                    <tr>
                        <th>Description: </th>
                        <th><?php echo $assignment['discription']; ?></th>
                    </tr>
                    <tr>
                        <th>Due Date: </th>
                        <th><?php echo $assignment['due']; ?></th>
                    </tr>
                    <tr>
                        <th>Specification: </th>
                        <th><?php echo $assignment['specification']; ?></th>
                    </tr>
                    <tr>
                        <th>Upload your assignment:</th>
                        <th>
                            <form action="/DevProject/upload.php" method="post" enctype="multipart/form-data">
                                <input type="file" name="fileToUpload" id="fileToUpload">
                                <input type="submit" value="Submit" name="submit">
                            </form> 
                        </th>
                    </tr>
                </table>
            </div>

        <?php } ?>

    </body>

</html>


<style>
.centre{
  margin: auto;
  width: 50%;
  border: 3px solid orange;
  padding: 10px;
}

.centre-allign{
  margin: auto;
  width: 90%;
}

.th {
  padding: 10px;
  border-bottom: 2px solid #8ebf42;
  text-align: center;
}

</style>
