<?php
require("../session.php");
require("../dbConnect.php");

$componentId = intval($_POST['componentId']);

// Delete the component's children:
$sql = "DELETE FROM content WHERE componentId = $componentId;"; // !!! delete constrain should do this properly.
$stmt = $dbh->prepare($sql);
$stmt->execute();
$stmt->close();

// Delete the component:
$sql = "DELETE FROM component WHERE ID = $componentId;";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$stmt->close();
$dbh->close();

$data = array(); // Create an empty associative array to confirm success.
echo json_encode($data);
?>
