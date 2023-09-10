<?php 

//Get total nbr of tasks in unit
$sql = "SELECT SUM(totalTasks) FROM tile t 
RIGHT JOIN unitUser uu on t.unitId = uu.unitId WHERE uu.userId=?;";
$stmt = $dbh->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($termTaskCount);
$stmt->fetch();
$stmt->close();

//get number of tasks this user has completed
$sql = "SELECT COUNT(tc.id) FROM taskcompletion tc 
RIGHT JOIN tile t ON tc.tileId = t.id 
RIGHT JOIN unit u ON t.unitId = u.id 
where tc.userId = ? AND tc.isComplete = 1;";
$stmt = $dbh->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($termTaskCompleted);
$stmt->fetch();
$stmt->close();

//calculate total unit xp percentage for current user
if($termTaskCount == 0){
  $termTaskCompleted = 0;
} else {
  $termTaskCompleted = ($termTaskCompleted / $termTaskCount) * 100;
  $termTaskCompleted = floor($termTaskCompleted);
}

?>
<header>
  <a href="/devproject/term.php">
    <img class="nav-logo" src="/devproject/assets/Logo.svg" alt="Pluto logo" onclick="document.location = '';">
  </a>
    <div class="term-xp-label">Term XP:</div>
    <div class="term-xp-bar">
        <div class="term-xp-progress" style="width:<?php echo $termTaskCompleted; ?>%;"></div>
    </div>
    <div class="dropdown-container">
      <button onclick="displayProfileDropdown()" class="dropbtn"><img class="profile-icon" src="https://cdn-icons-png.flaticon.com/512/64/64572.png"></button>
      <div id="profileDropDown" class="dropdown-content">
        <a href="/devproject/profilePage.php">View Profile</a>
        <form action="php/Misc/toggleTheme.php" method="post">
            <button type="submit">Toggle Theme</button>
        </form>
        <a href="/devproject/php/logout.php">Logout</a>
      </div>
    </div>
</header>
<script>
  const dropDownBox = document.getElementById("profileDropDown");
  
  function displayProfileDropdown() {
    dropDownBox.style.display = "block";
  }

  //Close the dropdown menu if the user clicks outside of it
  window.onclick = function(event) {
    if (!event.target.matches('.dropbtn') && !event.target.matches('.profile-icon')) {
      dropDownBox.style.display = "none";
    }
  }

</script>
