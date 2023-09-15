<?php
include("session.php");
include("dbConnect.php");

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
$sqlUnit = "SELECT id, assignmentsId, userId, grade, status, submitDate FROM submission where userId = ? AND assignmentsId = ?";
$stmt = $dbh->prepare($sqlUnit);
$stmt->bind_param("ii", $userId, $assignmentId);
$stmt->execute();
$resultSub = $stmt->get_result();
$submission = $resultSub->fetch_assoc();
$stmt->close();

//Get files for population
$skipped = array('0', '1');
$dir = "../Assignments/$unitId/$assignmentId/";
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
                <th>-</th>
            </tr>
            <tr>
                <th>SubmissionDate: </th>
                <th><?php echo $submission['submitDate'] ?></th>
            </tr>
            <tr>
                <th>-</th>
            </tr>
            <tr>
                <th>Link to Submission: </th>
                <th> DOWNLOAD </th>
            </tr>
            <tr>
                <th>-</th>
            </tr>
            <tr>
                <th>Mark Assignment</th>
                <th>
                    <form action="assignmentGrade.php" method="post">
                        <input type="text" name="grade" size="3" minlength="1" maxlength="3" value="<?php echo $submission['grade'] ?>">
                    </form>
                </th>
            </tr>
            <tr>
                <th>-</th>
            </tr>
            <tr>
                <th>Leave a comment</th>
                <th>

                    <textarea id="comment" name="comment" rows="6" cols="50">
                        Leave a comment for the student.
                    </textarea>   

                </th>
            </tr>
            <tr>
                <th>-</th>
            </tr>
            <tr>
                <th>Upload Marking Sheet:</th>
                <th>
                    <form action="/DevProject/upload.php?assignmentId" method="post" enctype="multipart/form-data">
                        <input type="file" name="fileToUpload" id="fileToUpload">
                    </form>
                </th>
            </tr>
            <tr class="blank_row">
                <td colspan="3"></td>
            </tr>
            <tr>
                <th>Button</th>
                <th>Button</th>
            </tr>
            

        </table>
    </div>


</body>

</html>



<style>
    .centre {
        margin: auto;
        width: 50%;
        border: 3px solid orange;
        padding: 10px;
    }

    .centre-allign {
        margin: auto;
        width: 90%;
    }

    .boarder {
        padding: 10px;
        border-bottom: 2px solid #8ebf42;
    }

    .th {
        padding: 10px;
        border-bottom: 2px solid #8ebf42;
        text-align: center;
    }

    .blank_row {
    height: 100px !important; /* overwrites any other rules */
    }
</style>
