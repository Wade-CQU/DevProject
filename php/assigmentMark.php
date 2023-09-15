<?php
include("session.php");
include("dbConnect.php");

//TODO submission Date

//Variables
$unitId = $_GET['unitId'];
$assignmentId = $_GET['assignmentId'];
$count = 0;

//Get data for Assignment
$sql = "SELECT id, unitId, due, total, description, specification FROM assignments WHERE unitId = ?";
$stmt = $dbh->prepare($sql);
$stmt->bind_param("i", $unitId);
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
            <?php
            foreach ($files1 as $key => $value) {
                if (in_array($key, $skipped)) {
                    continue;
                }
            ?>
                <tr>
                    <th> ------- </th>
                    <th> ------- </th>
                </tr>
                <tr>
                    <th>Student ID & Name: </th>
                    <th>
                        <?php
                        $sql = "SELECT firstName, lastName FROM user WHERE id = $value";
                        $stmt = $dbh->prepare($sql);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $stmt->close();
                        while ($user = $result->fetch_assoc()) {
                            echo $value . " | " . $user['firstName'] . " " . $user['lastName'];
                        }
                        ?>
                    </th>
                </tr>
                <tr>
                    <th>Grade Student</th>
                    <th>
                    <form action="assignmentStudent.php" method="post">
                            <input type="text" name="userId" value="<?php echo $value ?>" hidden>
                            <input type="text" name="assignmentId" value="<?php echo $assignmentId ?>" hidden>
                            <input type="text" name="unitId" value="<?php echo $unitId ?>" hidden>
                            <input type="submit" value="Grade Student">
                        </form>
                    </th>
                </tr>
            <?php } ?>
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
