<?php
    include("php/session.php");
    include("php/dbConnect.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/default.css" rel="stylesheet" />
    <link href="css/term.css" rel="stylesheet" />
    <title>Your Units</title>
    <?php if (isset($_COOKIE['lightTheme'])) { ?>
      <link rel="stylesheet" href="css/cringeTheme.css">
    <?php } ?>
</head>
<body>
    <?php require("php/header.php"); ?>
    <h1>WELCOME TO TERM <?php echo strval($termCode)[0]; ?>, WEEK 1</h1>
    <div class="side-scroll-container">
    <?php
      // Get unit's based on user:
      $sql = "SELECT uId, name, termCode FROM unit u RIGHT JOIN (SELECT uu.unitId as uId FROM unitUser uu WHERE userId = $userId) uu ON uId = u.id ORDER BY termCode DESC";
      $stmt = $dbh->prepare($sql);
      $stmt->execute();
      $result = $stmt->get_result();

      if (!$result) { // if query or database connection fails:
        echo "404 Unit Not Found"; // !!! review?
        $stmt->close();
        $dbh->close();
        exit;
      }

      while ($unit = $result->fetch_assoc()) {
        //Get total nbr of tasks in unit
        $sql = "SELECT SUM(totalTasks) FROM tile where unitId=?;";
        $stmt = $dbh->prepare($sql);
        $stmt->bind_param("i", $unit['uId']);
        $stmt->execute();
        $stmt->bind_result($unitTaskCount);
        $stmt->fetch();
        $stmt->close();

        //get number of tasks this user has completed
        $sql = "SELECT COUNT(tc.id) FROM taskcompletion tc
        RIGHT JOIN tile t ON tc.tileId = t.id
        RIGHT JOIN unit u ON t.unitId = u.id
        where u.id = ? AND tc.userId = ? AND tc.isComplete = 1;";
        $stmt = $dbh->prepare($sql);
        $stmt->bind_param("ii", $unit['uId'], $userId);
        $stmt->execute();
        $stmt->bind_result($unitTaskCompleted);
        $stmt->fetch();
        $stmt->close();

        //calculate total unit xp percentage for current user
        if ($unitTaskCount == 0) {
          $unitXpPercentage = 0;
        } else {
          $unitXpPercentage = ($unitTaskCompleted / $unitTaskCount) * 100;
          $unitXpPercentage = floor($unitXpPercentage);
        }
        //assign rank
        if($unitXpPercentage < 25){
          $rank = 1;
        } else if($unitXpPercentage < 50){
          $rank = 2;
        } else if($unitXpPercentage < 75){
          $rank = 3;
        } else {
          $rank = 4;
        }

        ?>
        <a href="unit.php?id=<?php echo $unit['uId']; ?>">
          <div class="unit-card" id="<?php echo $unit['uId']; ?>"<?php echo $unit['termCode'] != $termCode ? "style='display: none;'" : ""; ?>>
            <div class="term-code-label">Term Code: <?php echo $unit['termCode'];?></div>
            <div class="xp-container">
              <div class="xp-label">XP:</div>
              <div class="xp-bar"><div class="xp-progress" style="width: <?php echo $unitXpPercentage; ?>%;"></div></div>
            </div>
            <img class="rank-icon-<?php echo $rank; ?>" src="assets/<?php echo $rank; ?>.svg"/>
            <div class="unit-title"><?php echo $unit['name']; ?></div>
            <div class="rank-highlight rank-<?php echo $rank; ?>"></div>
          </div>
        </a>

      <?php }
      ?>
    </div>
    <div class="show-prev-units">Show all previous units</div>

    <script>
        const cards = document.querySelectorAll(".unit-card"); //select all the tiles.
        const prevUnitButton = document.querySelector(".show-prev-units");
        prevUnitButton.addEventListener("click", function(){
            cards.forEach(card => {
              card.style.display = "block";
            });
          });
    </script>
</body>
</html>
