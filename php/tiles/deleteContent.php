<?php
require("../session.php");
require("../dbConnect.php");

$contentId = intval($_POST['contentId']);

// Delete the component's children:
$sql = "DELETE FROM content WHERE ID = $contentId;";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$stmt->close();
$dbh->close();

$data = array(); // Create an empty associative array to confirm success.
echo json_encode($data);
?>
