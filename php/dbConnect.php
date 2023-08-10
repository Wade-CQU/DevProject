<?php // Responsible for all database connections:
$dbAddress = 'localhost';
$dbUser = 'root'; // !!! (these are my database credentials)
$dbPass = 'root'; 
$dbName = 'plutodb';

$dbh = new mysqli($dbAddress, $dbUser, $dbPass, $dbName);

if ($dbh->connect_error) {
  echo "Something went wrong. Please refresh the page, or try again later.";
  exit;
}
?>
