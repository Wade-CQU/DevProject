<?php
require("../session.php");
require("../dbConnect.php");
header('Content-Type: application/json');
if (!isset($_POST['tileId']) || !isset($userId)) { // !!! more & sessions
  exit;
}

// Get comments:
$sql = "SELECT c.id as cid, c.text as comment, u.userName as name, u.role as role FROM comment c LEFT JOIN (SELECT id, CONCAT(firstName,' ',lastName) as userName, role FROM user) u ON c.userId = u.id WHERE tileId = ? ORDER BY c.id DESC;";
$stmt = $dbh->prepare($sql);
$stmt->bind_param("i", $_POST['tileId']);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
  $stmt->close();
  $dbh->close();
  exit;
}

// store & format comment results:
$comments = array();
while ($comm = $result->fetch_assoc()) {
  array_push($comments, $comm);
}
echo json_encode($comments);
?>
