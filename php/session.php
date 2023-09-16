<?php
// Establish Session Variables & Cookie Configuration:
// session_set_cookie_params(86400);
// ini_set('session.gc_maxlifetime', 86400);
// session_start();
// date_default_timezone_set("Australia/Brisbane");
//
// if (isset($_COOKIE['activeSession'])) {
//     if (!isset($_SESSION['id'])) {
//         $sql = "SELECT * FROM users WHERE ID = " . $_COOKIE['activeSession'] . ";";
//         $result = $dbh->query($sql);
//
//         $_SESSION['email']  = $result['EMAIL'];
//
//     }
// }
session_start();
if (isset($_SESSION["id"])) {
  $userId = $_SESSION["id"];
  $role = $_SESSION["role"];
  // Get the current month as an integer (1 for January, 12 for December)
$currentMonth = date('n');
// Get the current year
$currentYear = date('Y');

if ($currentMonth >= 1 && $currentMonth <= 6) {
    $termCode = "1" . $currentYear;
} else {
    $termCode = "2" . $currentYear;
}
} else {
  header("Location: login.php");
  exit;
}
?>
