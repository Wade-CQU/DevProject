<?php
require("../session.php");
require("../dbConnect.php");
header('Content-Type: application/json');
if (!isset($_POST['tileId']) || !isset($_POST['comment']) || trim($_POST['comment']) == "" || !isset($userId)) { // !!! more & sessions
  exit;
}

// Get comments:
$sql = "INSERT INTO comment (userId, tileId, text) VALUES (?,?,?);";
$stmt = $dbh->prepare($sql);
$stmt->bind_param("iis", $userId, $_POST['tileId'], $_POST['comment']);
$stmt->execute();
if ($stmt->affected_rows === -1) {
  $stmt->close();
  $dbh->close();
  exit;
}

// inform of success:
$comments = array();
echo json_encode($comments);
?>
