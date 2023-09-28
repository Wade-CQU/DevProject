<?php
include("session.php");
include("dbConnect.php");

//TODO submission Date

//Variables
$unitId = $_GET['unitId'];
$assignmentId = $_GET['assignmentId'];
$count = 0;

//Get data for Assignment
$sql = "SELECT id, unitId, due, total, description, specification FROM assignments WHERE `order` = ?";
$stmt = $dbh->prepare($sql);
$stmt->bind_param("i", $assignmentId);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$assignment = $result->fetch_assoc();

//Get data for Unit
$sqlUnit = "SELECT id, code, name, termCode FROM unit where id = ?";
$stmt = $dbh->prepare($sqlUnit);
$stmt->bind_param("i", $unitId);
$stmt->execute();
$resultUnit = $stmt->get_result();
$unit = $resultUnit->fetch_assoc();
$stmt->close();

//Get all users for this unit
$sqlUnitUser = "SELECT id, unitId, userId FROM unituser WHERE `unitId` = ?";
$stmt = $dbh->prepare($sqlUnitUser);
$stmt->bind_param("i", $unitId);
$stmt->execute();
$resultUnitUser = $stmt->get_result();
$unitUser = $resultUnitUser->fetch_assoc();
$stmt->close();

//Get files for population
$skipped = array('0', '1');

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

    <div class="centre">
        <table class="centre-allign" style="color:white">
            <tr>
                <th>Description: </th>
                <th><?php echo $assignment['description']; ?></th>
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
                <th>Grade:</th>
                <th><?php echo $assignment['total']; ?></th>
            </tr>
        </table>

        <table class="centre-allign" style="color:white; border-collapse: collapse;">
            <tr style="border:1px solid white;">
                <th>Student Name</th>
                <th>Student ID</th>
                <th>Marked</th>
                <th></th>
            </tr>

        <?php

        $sql = "SELECT assignmentsId FROM submission WHERE assignmentsId = $assignmentId";
        $stmt = $dbh->prepare($sql);
        $stmt->execute();
        $checkResult = $stmt->get_result();
        $check = $checkResult->fetch_assoc();
        $stmt->close();

        if (isset($check)) {
            //Get files
            $dir = "../Assignments/$unitId/$assignmentId/";
            $files1 = scandir($dir);
            //Loop all students with submissions
            foreach ($files1 as $key => $value) {
                if (in_array($key, $skipped)) {
                    continue;
                }
        ?>
        <tr>        
            <th>
                <?php
                $sql = "SELECT firstName, lastName FROM user WHERE id = $value";
                $stmt = $dbh->prepare($sql);
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();
                while ($user = $result->fetch_assoc()) {
                echo $user['firstName'] . " " . $user['lastName'];
                }
                ?>
            </th>
            <th><?php echo $value; ?></th>
            <th>
                <?php
                $sql = "SELECT status FROM submission WHERE userId = ? AND assignmentsId = ?";
                $stmt = $dbh->prepare($sql);
                $stmt->bind_param("ii", $value, $assignmentId);
                $stmt->execute();
                $resultSub = $stmt->get_result();
                $submission = $resultSub->fetch_assoc();
                $stmt->close();
                if ($submission['status'] == 1) {
                    echo "Waiting for Grade.";
                } else if ($submission['status'] == 2) {
                    echo "Graded.";
                } else {
                    echo "Not submitted.";
                }
                ?>
            </th>
            <th>
                <form action="assignmentStudent.php" method="post">
                    <input type="text" name="userId" value="<?php echo $value ?>" hidden>
                    <input type="text" name="assignmentId" value="<?php echo $assignmentId ?>" hidden>
                    <input type="text" name="unitId" value="<?php echo $unitId ?>" hidden>
                    <input type="submit" value="Grade Student" class="grade-button">
                </form>
            </th>
        </tr>
        <?php }
        } else {
            echo "<h1>NO SUBMISSIONS YET</h1>";
        } ?>
                </table>
                <button class="back-button" onclick="document.location='/devproject/unit.php?id=<?php echo $unitId; ?>'">
                <img class="back-icon" src="../assets/fontAwesomeIcons/back.svg" /> Go back</button>
    </div>
</body>



</body>

</html>



<style>
    .centre {
        margin: auto;
        width: 70%;
        border: 3px solid orange;
        padding: 10px;
    }
    .centre tr {
        border-collapse: collapse;
        border: 3px solid orange;
    }
    .centre-allign {
        margin-right: auto;
        margin-left: auto;
        margin-top: 30px;
        margin-bottom: 30px;
        width: 90%;
    }
    .centre-allign tr:nth-child(odd) {
        background-color: rgba(255, 255, 255, 0.1);
    }
    .centre-allign tr:nth-child(even) {
        background-color: rgba(255, 255, 255, 0.2);
    }
    .grade-button {
        padding: 10px;
        margin: 4px;
    } 
    .back-button{
        padding: 5px;
        font-size: 15px;
        display: inline-flex;
        gap: 10px;
    }
</style>
