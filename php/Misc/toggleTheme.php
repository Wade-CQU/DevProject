<?php // set/expire cookie to toggle theme:
$target = $_POST['redirect'];
if (!isset($_COOKIE['lightTheme'])) {
  setcookie("lightTheme", "why", time() + 999999999, "/");
} else {
  setcookie("lightTheme", "why", time() - 3600, "/");
}
header("Location: ".$target);
?>
