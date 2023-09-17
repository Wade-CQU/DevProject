<?php
require("../session.php");
require("../dbConnect.php");

$tileId = intval($_POST['tileId']);

// Delete the tile:
$sql = "DELETE FROM tile WHERE id = $tileId;";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$stmt->close();
$dbh->close();

$data = array(); // Create an empty associative array to confirm success.
echo json_encode($data);
?>
