<?php // Page responsible for displaying an individual unit and it's content //
include("php/session.php");
include("php/dbConnect.php");

// Get unit record:
$sql = "SELECT * FROM unit WHERE ID = ? LIMIT 1;";
$stmt = $dbh->prepare($sql);

$stmt->bind_param("i", $_GET['id']);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows == 0) { // if query or database connection fails, or unit not found:
  echo "404 Unit Not Found";
  $stmt->close();
  $dbh->close();
  exit;
}

$unit = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?php echo $unit['code'] . ": " . $unit['name']; ?></title>
  <link href="css/unit.css" rel="stylesheet" />
  <link href="css/default.css" rel="stylesheet" />
</head>
<body>
    <?php require("php/header.php"); ?>
    <div class="body-content">
      <div class="unit-heading">
        <div class="unit-title">
            <p1>
                <?php echo $unit['code'] . ": " . $unit['name']; ?>
            </p1>
        </div>
        <div class="unit-description">
            <p2>
                <?php echo $unit['description']; ?>
            </p2>
        </div>
      </div>
        <div class="weekly-quest">
          <div class="quest-title">
            WEEKLY QUEST
          </div>
          <div class="quest-description">
            <p>
              This is the details of the weekly quest. Aye woah look, lookkyy here this is a whole lotta week 1 content. Wow week 1, looky here week 1 content header.
            </p>
          </div>
        </div>
        <div class="weekly-content-container">
          <?php
            // Get unit's tiles (!!! if not cached):
            $sql = "SELECT * FROM tile WHERE unitId = ?;";
            $stmt = $dbh->prepare($sql);

            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
            $result = $stmt->get_result();

            if (!$result) { // if query or database connection fails:
              echo "404 Unit Not Found"; // !!! review?
              $stmt->close();
              $dbh->close();
              exit;
            }

            while ($tile = $result->fetch_assoc()) { ?>
              <div class="unitTileDiv">
                <div class="unitTileHolder">
                  <div class="unitTile">
                    <div class="unitTileIconHolder">
                      <img src="" alt="">
                    </div>
                    <div class="unitTileContents">
                      <p class="unitTileTitle"><?php echo $tile['name']; ?></p>
                      <p class="unitTileLabel"><?php echo $tile['label']; ?></p>
                    </div>
                  </div>
                  <div class="unitTileXpHolder">
                    <p class="unitTileXpLabel">EXP:</p>
                    <div class="unitTileXpBar">
                      <div class="unitTileXpProgress">

                      </div>
                    </div>
                  </div>
                </div>
                <div class="unitTileDescription">
                  <?php echo $tile['description']; ?>
                </div>
              </div>
          <?php }
            $stmt->close();
            $dbh->close();
           ?>
        </div>
    </div>
  <script>
    // const weeklyContentButton = document.querySelector(".test-button");

    // weeklyContentButton.addEventListener("click", () => {
    //   weeklyContentButton.classList.toggle("button-openned");
    // });

    // !!! Old tile opening code (wade would be very upset if you removed it):

    // const weeklyContentButton = document.querySelectorAll(".test-button");
    // var i;
    //
    // for (i = 0; i < weeklyContentButton.length; i++) {
    //   weeklyContentButton[i].addEventListener("click", function() {
    //     this.classList.toggle("button-openned");
    //     var content = this.nextElementSibling;
    //     if (content.style.maxHeight) {
    //       content.style.maxHeight = null;
    //       content.style.display = "none";
    //     } else {
    //       content.style.display = "block";
    //       content.style.maxHeight = content.scrollHeight + "px";
    //     }
    //   });
    // }
  </script>
</body>
</html>
