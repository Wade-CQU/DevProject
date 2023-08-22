<?php
require("../session.php");
require("../dbConnect.php");
$tileId = intval($_POST['tileId']);
$contentId = intval($_POST['contentId']);
$state = intval(isset($_POST['taskState']));

// time cache feature !!!

// Determine task togglablitity & perform toggle:
$sql = "INSERT INTO taskCompletion (contentId, userId, isComplete, tileId)
        VALUES ($contentId, $userId, $state, $tileId)
        ON DUPLICATE KEY UPDATE isComplete = $state;";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$stmt->close();
$dbh->close();

$data = array(); // Create an empty associative array to confirm success.
echo json_encode($data);
?>
