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
    <title></title>
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
        $fakeRank = rand(1, 4);
        ?>
        <div class="unit-card" id="<?php echo $unit['uId']; ?>"<?php echo $unit['termCode'] != $termCode ? "style='display: none;'" : ""; ?>>
          <div class="icon unit-icon-container"></div>
          <div class="xp-container"></div>
          <img class="rank-icon-container" src="assets/<?php echo $fakeRank; ?>.svg"/>
          <div class="unit-title"><?php echo $unit['name']; ?></div>
          <div class="rank-highlight rank-<?php echo $fakeRank; ?>"></div>
        </div>
      <?php }
        $stmt->close();
        $dbh->close();
      ?>
    </div>
    <script>
        const cards = document.querySelectorAll(".unit-card"); //select all the tiles.
        cards.forEach(card => { //!!! change this later
            card.addEventListener("click", function(){
                window.location.href = "unit.php?id=" + card.id;
            });
        });
    </script>
</body>
</html>
