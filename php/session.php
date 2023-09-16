<?php
session_start();
if (isset($_SESSION["id"])) {
  $userId = $_SESSION["id"];
  $role = $_SESSION["role"];
  $currentMonth = date('n'); // Get the current month as an integer (1 for January, 12 for December)
  $currentYear = date('Y'); // Get the current year
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
