<?php
require("../session.php");
require("../dbConnect.php");
$contentId = intval($_POST['contentId']);
$state = intval(isset($_POST['taskState']));

// time cache feature !!!

// Determine task togglablitity & perform toggle:
$sql = "INSERT INTO taskCompletion (contentId, userId, isComplete)
        VALUES ($contentId, $userId, $state)
        ON DUPLICATE KEY UPDATE isComplete = $state;";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$stmt->close();
$dbh->close();

$data = array(); // Create an empty associative array to confirm success.
echo json_encode($data);
?>
