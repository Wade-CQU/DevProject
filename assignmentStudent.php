<?php

//TODO :( https://stackoverflow.com/questions/7843355/submit-two-forms-with-one-button

include("php/session.php");
include("php/dbConnect.php");

$userId = $_POST['userId'];
$assignmentId = $_POST['assignmentId'];
$unitId = $_POST['unitId'];

//Get data for Assignment
$sql = "SELECT id, unitId, due, total, description, specification FROM assignments WHERE unitId = ?";
$stmt = $dbh->prepare($sql);
$stmt->bind_param("i", $unitId);
$stmt->execute();
$result = $stmt->get_result();
$assignment = $result->fetch_assoc();
$stmt->close();

//Get Data for Unit
$sqlUnit = "SELECT id, code, name, termCode FROM unit where id = ?";
$stmt = $dbh->prepare($sqlUnit);
$stmt->bind_param("i", $unitId);
$stmt->execute();
$resultUnit = $stmt->get_result();
$unit = $resultUnit->fetch_assoc();
$stmt->close();

//Get Data for Submission
$sqlUnit = "SELECT id, assignmentsId, userId, grade, status, submitDate, comment FROM submission where userId = ? AND assignmentsId = ?";
$stmt = $dbh->prepare($sqlUnit);
$stmt->bind_param("ii", $userId, $assignmentId);
$stmt->execute();
$resultSub = $stmt->get_result();
$submission = $resultSub->fetch_assoc();
$stmt->close();

//Get files for population
$skipped = array('0', '1');
$dir = "Assignments/$unitId/$assignmentId/";
$files1 = scandir($dir);

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
    </div>

    
    <div class="centre">
        <table class="centre-allign" style="color:white">
            <tr>
                <th>Student ID & Name: </th>
                <th>
                    <?php
                    $sql = "SELECT firstName, lastName FROM user WHERE id = $userId";
                    $stmt = $dbh->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $stmt->close();
                    while ($user = $result->fetch_assoc()) {
                        echo $userId . " | " . $user['firstName'] . " " . $user['lastName'];
                    }
                    ?>
                </th>
            </tr>
            <tr>
                <th>SubmissionDate: </th>
                <th><?php echo $submission['submitDate'] ?></th>
            </tr>
            <tr>
                <th>Student submission: </th>
                <th>
                    <a href="<?php
                    $download = scandir("$dir/$userId/");
                    foreach ($download as $key => $assgnmentName) {
                        if ($assgnmentName == "markingsheet" || in_array($key, $skipped)) {
                            continue;
                        }
                        echo "$dir/$userId/$assgnmentName";
                    }
                    ?>">Download Submission</a>
                </th>
            </tr>
            <tr>
                <th>Mark Assignment:</th>
                <th>
                    <!-- <form action="assignmentGrade.php" method="post" id="gradeForm"> -->
                    <form action="/DevProject/php/uploadMarkSheet.php?assignmentId=<?php echo $assignmentId; ?>&unitId=<?php echo $unitId ?>&userId=<?php echo $userId ?>" method="post" id="markingSheet" enctype="multipart/form-data">
                        <input type="text" name="userId" value="<?php echo $userId ?>" hidden>
                        <input type="text" name="assignmentId" value="<?php echo $assignmentId ?>" hidden>
                        <input type="text" name="unitId" value="<?php echo $unitId ?>" hidden>
                        <input type="text" name="grade" size="3" minlength="1" maxlength="3" value="<?php echo $submission['grade'] ?>">
                </th>
            </tr>
            <tr>
                <th>Leave a comment:</th>
                <th>
                    <label>Leave a comment for the Student:</label></br>
                    <textarea id="comment" name="comment" rows="6" cols="50"><?php echo $submission['comment'] ?></textarea>
                    <!-- </form> -->
                </th>
            </tr>
            <tr>
                <th>Upload Marking Sheet:</th>
                <th>
                    <!-- <form action="php/uploadMarkSheet.php?assignmentId=<?php echo $assignmentId; ?>&unitId=<?php echo $unitId ?>&userId=<?php echo $userId ?>" method="post" id="markingSheet" enctype="multipart/form-data"> -->
                    <input type="file" name="fileToUpload" id="fileToUpload">
                    </form>
                </th>
            </tr>
            <tr class="blank_row">
                <td colspan="3"></td>
            </tr>
            <tr>
                <th><button onclick="document.location='assigmentMark.php?unitId=<?php echo $unitId; ?>&assignmentId=<?php echo $assignmentId ?>'">⬅ Go back</button> </th>
                <th><button onclick="submitForms()">Submit</button></th>
            </tr>


        </table>
    </div>


</body>

</html>





<script>
    submitForms = function() {
        //document.getElementById("gradeForm").submit();
        document.getElementById("markingSheet").submit();
    }
</script>


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

</style>
