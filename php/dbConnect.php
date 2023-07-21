<?php // Responsible for all database connections:
$dbAddress = 'localhost';
$dbUser = ''; // !!! get details
$dbPass = '';
$dbName = '';

$dbh = new mysqli($dbAddress, $dbUser, $dbPass, $dbName);

if ($dbh->connect_error) {
  echo "Something went wrong. Please refresh the page, or try again later.";
  exit;
}

// // EXAMPLE:
// $sql = "SELECT * FROM WHERE ID = ? AND NAME = ?;";
// $stmt = $dbh->prepare($sql);
//
// $stmt->bind_param("is", $_POST['ID'], $_POST['username']);
// $stmt->execute();
// $result = $stmt->get_result();
//
// $result->fetch_assoc();
//
// foreach ($result as $r) {
//   echo $r['ID'];
// }
//
// $stmt->close();
// $db->close();

function (string $query, string $dataTypes, array $parameters) {
  $stmt = $dbh->prepare($query);

  $stmt->bind_param($dataTypes, $parameters); // !!! currently takes array but IDK if that works?
  $stmt->execute();
  $result = $stmt->get_result();

  $result->fetch_assoc();

  $stmt->close();
  $db->close();

  return $result;
}?>
