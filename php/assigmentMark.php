<?php
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

$assignment = $result->fetch_assoc();

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
                <th> ------- </th>
                <th> ------- </th>
            </tr>
            <tr>
                <th>Student Name and ID: </th>
                <th>(display of student name and ID)</th>
            </tr>
            <tr>
                <th>Student submission: </th>
                <th> Provide a link to the submission </th>
            </tr>
            <tr>
                <th>Student Mark: </th>
                <th> Button that will let you set a mark </th>
            </tr>
        </table>
    </div>
</body>



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

    .th {
        padding: 10px;
        border-bottom: 2px solid #8ebf42;
        text-align: center;
    }
</style>
