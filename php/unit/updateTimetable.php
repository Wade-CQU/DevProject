<?php
require("../session.php");
require("../dbConnect.php");
header('Content-Type: application/json');
if (!isset($_POST['timetableUpdate'])) { // !!! session checks
  exit;
}
// Decode the data:
$data = json_decode($_POST['timetableUpdate']);
$unitId = intval($data[2]);

// create new records:
foreach ($data[0] as $key => $value) {
  $classTime = $value->classTime;
  $link = $value->link;
  $details = $value->details;
  $sql = "INSERT INTO timetable (unitId, classTime, link, details)
          VALUES (?,?,?,?);";
  $stmt = $dbh->prepare($sql);
  $stmt->bind_param("isss", $unitId, $classTime, $link, $details);
  $stmt->execute();
  $stmt->close();
}
// update existing records:
foreach ($data[1] as $key => $value) {
  $classTime = $value->classTime;
  $link = $value->link;
  $details = $value->details;
  $classId = intval($value->classId);
  $sql = "UPDATE timetable SET unitId = ?, classTime = ?, link = ?, details = ? WHERE id = ?;";
  $stmt = $dbh->prepare($sql);
  $stmt->bind_param("isssi", $unitId, $classTime, $link, $details, $classId);
  $stmt->execute();
  $stmt->close();
}

$dbh->close();
$data = array(); // Create an empty associative array to confirm success.
echo json_encode($data);
?>
