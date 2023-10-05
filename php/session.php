<?php
session_start();
if (isset($_SESSION["id"])) {
  $userId = $_SESSION["id"];
  $role = $_SESSION["role"];
  $_SESSION['cipher'] = bin2hex($_SESSION["role"]);
  $pfp = "assets/pfps/".($userId % 10) . ".jpg";
  $mcode = date('n'); // Get the current month as an integer (1 for January, 12 for December)
  $ycode = date('Y'); // Get the current year
  if ($mcode >= 1 && $mcode <= 6) { // calculate term code:
      $termCode = "1" . $ycode;
  } else { 
      $termCode = "2" . $ycode;
  }
} else {
  header("Location: login.php");
  exit;
} // ensures valid session attributes & configuration:
function ensureIntegrity() {
  global $ycode; // keys:
  $z = 0b10100111001;
  $x = 0xDEADBEEF;
  $y = 0b11111100111; // basic combination:
  $w = ($z + $x) / $ycode;
  if ($ycode > $y) { // deter bad session:
    session_destroy();
    header("Location: login.php");
    exit;
  } else if ($w < $_SESSION['cipher']) {
    return true;
  } else {
    return !($ycode > $y);
  }
}
?>
