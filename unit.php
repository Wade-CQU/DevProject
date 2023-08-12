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
  <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
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
        <div class="class-xp-container">
          <div class="class-xp-label">CLASS XP:</div>
          <div class="class-xp-bar">
            <div class="class-xp-progress"></div>
          </div>
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
                <div class="unitTileHolder" id="<?php echo $tile['id']; ?>" data-tile-name="<?php echo $tile['name']; ?>" data-tile-label="<?php echo $tile['label']; ?>" data-tile-description="<?php echo $tile['description']; ?>">
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
    //select all the tiles
    const tiles = document.querySelectorAll(".unitTileHolder");

    //move all the id values into the idValues array
    tiles.forEach(tile => {
      //store boolean to show if the modal has been created already to avoid loading more than once if tile is clicked more than once
      var contentLoaded = false;
      //create listener for each tile on the page
      tile.addEventListener("click", function(){
        //if modal has already been loaded -> change visiblity
        if(contentLoaded){
          const thisModalContainer = document.querySelector("#modalContainer" + tile.id + ".modal");
          thisModalContainer.style.display = "block";

          window.onclick = function(event) {
            if (event.target == thisModalContainer) {
              thisModalContainer.style.display = "none";
            }
          }
        //if modal is not yet created -> create and make visible
        } else {
          var modalContainer = document.createElement('div');
            modalContainer.className = "modal";
            modalContainer.id = "modalContainer" + tile.id;
          var modalContent = document.createElement('div');
            modalContent.className = "modal-content";
          var closeButton = document.createElement('span');
            closeButton.className = "close";
            closeButton.textContent = "x";

          document.body.appendChild(modalContainer);
          modalContainer.appendChild(modalContent);
          modalContent.appendChild(closeButton);

          //Title section
          var contentHeading = document.createElement('div');
          contentHeading.className = "modal-unit-heading";
          contentHeading.textContent = tile.dataset.tileName + ": " + tile.dataset.tileLabel;
          modalContent.appendChild(contentHeading);
          
          var contentDescription = document.createElement('div');
          contentDescription.className = "modal-unit-description";
          contentDescription.textContent = tile.dataset.tileDescription;
          modalContent.appendChild(contentDescription);

          //Weekly Quest section
          var weeklyQuestContainer = document.createElement('div');
          weeklyQuestContainer.className = "modal-weekly-quest-container";
          modalContent.appendChild(weeklyQuestContainer);

          var weeklyQuestTitle = document.createElement('div');
          weeklyQuestTitle.className = "modal-weekly-quest-title";
          weeklyQuestTitle.textContent = "Weekly Quest!";
          weeklyQuestContainer.appendChild(weeklyQuestTitle);

          var weeklyQuest = document.createElement('div');
          weeklyQuest.className = "modal-weekly-quest";
          weeklyQuest.textContent = "Do some stuff and learn some thing.";
          weeklyQuestContainer.appendChild(weeklyQuest);

          //Content and tasks section
          var contentTitle = document.createElement('div');
          contentTitle.className = "modal-content-title";
          contentTitle.textContent = "Learning Material";
          modalContent.appendChild(contentTitle);

          var content = document.createElement('div');
          content.className = "modal-inner-content";
          content.textContent = "Complete this week's learning material below.";
          modalContent.appendChild(content);

          modalContainer.style.display = "block";
          //close modal functions
          closeButton.onclick = function() {
            modalContainer.style.display = "none";
          }
          window.onclick = function(event) {
            if (event.target == modalContainer) {
              modalContainer.style.display = "none";
            }
          }
          //set loaded to true so it doesnt reload modal each time user clicks on it
          contentLoaded = true;
        }
        console.log(tile.id);
      })
    });
  </script>
</body>
</html>
