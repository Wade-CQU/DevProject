<?php
require_once("../session.php");
require_once("../dbConnect.php");

$unitId = intval($_POST['unitId']);
$code = $_POST['code'];
$name = $_POST['name'];
$description = $_POST['description'];

// Determine task togglablitity & perform toggle:
$sql = "UPDATE unit SET code = ?, name = ?, description = ? WHERE id = ?;";
$stmt = $dbh->prepare($sql);
$stmt->bind_param("sssi", $code, $name, $description, $unitId);
$stmt->execute();
$stmt->close();
$dbh->close();

$data = array(); // Create an empty associative array to confirm success.
echo json_encode($data);
 ?>
