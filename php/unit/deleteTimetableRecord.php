<?php
require("../session.php");
require("../dbConnect.php");

$classId = intval($_POST['classId']);

// Delete the class from the timetable:
$sql = "DELETE FROM timetable WHERE id = $classId;";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$stmt->close();
$dbh->close();

$data = array(); // Create an empty associative array to confirm success.
echo json_encode($data);
?>
