<?php
require("../session.php");
require("../dbConnect.php");
header('Content-Type: application/json');
if (!isset($_POST['tilesUpdate'])) { // !!! session checks
  exit;
}
// Decode the data:
$data = json_decode($_POST['tilesUpdate']);
$unitId = intval($data[2]);

// create new records:
foreach ($data[0] as $key => $value) {
  $icon = $value->icon;
  $name = $value->name;
  $label = $value->label;
  $order = $value->order;
  $sql = "INSERT INTO tile (`unitId`, `icon`, `name`, `label`, `order`)
          VALUES (?,?,?,?,?);";
  $stmt = $dbh->prepare($sql);
  $stmt->bind_param("iissi", $unitId, $icon, $name, $label, $order);
  $stmt->execute();
  $stmt->close();
}
// update existing records:
foreach ($data[1] as $key => $value) {
  $icon = $value->icon;
  $name = $value->name;
  $label = $value->label;
  $order = $value->order;
  $tileId = intval($value->tileId);
  $sql = "UPDATE tile SET `unitId` = ?, `icon` = ?, `name` = ?, `label` = ?, `order` = ? WHERE `id` = ?;";
  $stmt = $dbh->prepare($sql);
  $stmt->bind_param("iissii", $unitId, $icon, $name, $label, $order, $tileId);
  $stmt->execute();
  $stmt->close();
}

$dbh->close();
$data = array(); // Create an empty associative array to confirm success.
echo json_encode($data);
?>
