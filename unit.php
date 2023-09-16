<?php // Page responsible for displaying an individual unit and it's content //
include("php/session.php");
include("php/dbConnect.php");

$unitId = intval($_GET['id']);
$userRole = $role; // !!!

//Get total nbr of tasks in unit
$sql = "SELECT SUM(totalTasks) FROM tile where unitId = ?;";
$stmt = $dbh->prepare($sql);
$stmt->bind_param("i", $unitId);
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
$stmt->bind_param("ii", $unitId, $userId);
$stmt->execute();
$stmt->bind_result($unitTaskCompleted);
$stmt->fetch();
$stmt->close();

//Get data for the assignment tile
$assCount = 0;
$sql = "SELECT id, unitId, due, total, description, specification FROM assignments WHERE unitId = ?";
$stmt = $dbh->prepare($sql);
$stmt->bind_param("i", $unitId);
$stmt->execute();
$assResult = $stmt->get_result();
$stmt->close();

//Get data for the Teacher assignment tile
$assTCount = 0;
$sql = "SELECT id, unitId, due, total, description, specification FROM assignments WHERE unitId = ?";
$stmt = $dbh->prepare($sql);
$stmt->bind_param("i", $unitId);
$stmt->execute();
$assTResult = $stmt->get_result();
$stmt->close();

//Get the other thing for the assignment tile
$sqlUnit = "SELECT id, code, name, termCode FROM unit where id = ?";
$stmt = $dbh->prepare($sqlUnit);
$stmt->bind_param("i", $unitId);
$stmt->execute();
$resultUnit = $stmt->get_result();
$unit = $resultUnit->fetch_assoc();
$stmt->close();

//calculate total unit xp percentage for current user
if ($unitTaskCount == 0) {
  $unitXpPercentage = 0;
} else {
  $unitXpPercentage = ($unitTaskCompleted / $unitTaskCount) * 100;
  $unitXpPercentage = floor($unitXpPercentage);
}


// Get unit record:
$sql = "SELECT id, code, name, description FROM unit WHERE EXISTS (SELECT 1 FROM unitUser WHERE unitId = ? AND userId = $userId) AND ID = ? LIMIT 1;";
$stmt = $dbh->prepare($sql);
$stmt->bind_param("ii", $unitId, $unitId);
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
  <script src="frameworks/jquery-3.7.0.min.js"></script>
  <script src="js/ajax.js"></script> <!-- !!! perhaps in header ^^? -->
  <?php if (isset($_COOKIE['lightTheme'])) { ?>
    <link rel="stylesheet" href="css/cringeTheme.css">
  <?php } ?>
</head>

<body>
  <?php require("php/header.php"); ?>

  <!-- invis div for STUDENT Assignment tile ------ Links to assHolder.append($("#assContent").show()); on like line 420 -->
  <div id="assContent" style="display:none;">
    <div class="modal-unit-heading">Assignments for <?php echo $unit['name']; ?></div>
    <?php
    while ($assignment = $assResult->fetch_assoc()) {
      $assCount++;
    ?>
      <div>
        <table class="assignment-table">
          <tr>
            <th>Assignment #</th>
            <th><?php echo $assCount; ?></th>
          </tr>
          <tr>
            <th>Description: </th>
            <th><?php echo $assignment['description']; ?></th>
          </tr>
          <tr>
            <th>Due Date: </th>
            <th><?php echo $assignment['due']; ?></th>
          </tr>
          <tr>
            <th>Specification: </th>
            <th><?php echo $assignment['specification']; ?></th>
          </tr>
          <tr>
            <th>Mark: </th>
            <th><?php echo $assignment['total']; ?></th>
          </tr>
          <tr>
            <th>Upload your assignment:</th>
            <th>
              <form action="/DevProject/upload.php?assignmentId=<?php echo $assCount; ?>&unitId=<?php echo $unitId; ?>&userId=<?php echo $userId ?>" method="post" enctype="multipart/form-data">
                <input type="file" name="fileToUpload" id="fileToUpload">
                <input type="submit" value="Submit" name="submit">
              </form>
            </th>
          </tr>
        </table>
      </div>
    <?php } ?>
  </div>

  <!-- Invisible div for TEACHER assignment view -->
  <div id="assTeacherContent" style="display:none;">
  <div class="modal-unit-heading">Assignments for <?php echo $unit['name']; ?></div>
    <?php
    while ($assignment = $assTResult->fetch_assoc()) {
      $assTCount++;
    ?>
      <div>
        <table class="assignment-table">
          <tr>
            <th>Assignment #</th>
            <th><?php echo $assTCount; ?></th>
          </tr>
          <tr>
            <th>Description: </th>
            <th><?php echo $assignment['description']; ?></th>
          </tr>
          <tr>
            <th>Due Date: </th>
            <th><?php echo $assignment['due']; ?></th>
          </tr>
          <tr>
            <th>Specification: </th>
            <th><?php echo $assignment['specification']; ?></th>
          </tr>
          <tr>
            <th>Mark: </th>
            <th><?php echo $assignment['total']; ?></th>
          </tr>
          <tr>
            <th>Mark Assignment: </th>
            <th><a href="/devproject/php/assigmentMark.php?unitId=<?php echo $unitId; ?>&assignmentId=<?php echo $assTCount; ?>">Mark Assignment</a></th>
          </tr>
        </table>
      </div>
    <?php } ?>
  </div>
<!-- Invisible div for class info modal  -->
  <div id="classInfoContent" style="display: none;">
    <div class="centre" id="classInfoDiv">
      <h1>Unit Info</h1>
      <h1 style="float: left;" id="classInfoHeader"><?php echo $unit["code"]. ": " . $unit["name"]; ?></h1>
      <?php if ($role == 2) { ?>
      <button type="button" class="modal-edit-button" onclick="toggleClassInfoEdit();" style="float: right;">✎ EDIT</button>
    <?php } ?>
      <p style="clear: both; margin-top: 12px;" id="classInfoDescr"><?php echo  $unit["description"]; ?></p>
    </div>
    <?php if ($role == 2) { ?>
    <div class="centre" style="display: none;" id="classInfoEditDiv">
      <h1>Unit Info</h1>
      <input type="text" id="unitCodeEdit" value="<?php echo $unit["code"]; ?>" class="modal-component-title" style="width: 25%;" placeholder="Unit Code..." data-initial="<?php echo $unit["code"]; ?>">
      <input type="text" id="unitNameEdit" value="<?php echo $unit["name"]; ?>" class="modal-component-title" style="width: 75%;" placeholder="Unit Name..." data-initial="<?php echo $unit["name"]; ?>">
      <textarea class="modal-component-description" id="unitDescriptionEdit" style="height: 200px;" data-initial="<?php echo $unit["description"]; ?>"><?php echo  $unit["description"]; ?></textarea>
      <div style="display: flex;">
        <button type="button" class="save-cancel-btn" onclick="cancelClassInfoEdit();">Cancel</button>
        <button type="button" class="save-cancel-btn" onclick="saveUnitInfo();">Save Unit</button>
      </div>
    </div>
    <script>
      function toggleClassInfoEdit() {
        $("#classInfoDiv").toggle();
        $("#classInfoEditDiv").toggle();
      }
      function cancelClassInfoEdit() {
        let codeEdit = document.getElementById("unitCodeEdit");
        codeEdit.value = codeEdit.dataset.initial;
        let nameEdit = document.getElementById("unitNameEdit");
        nameEdit.value = nameEdit.dataset.initial;
        let descEdit = document.getElementById("unitDescriptionEdit");
        descEdit.value = descEdit.dataset.initial;
        toggleClassInfoEdit();
      }
      function saveUnitInfo() {
        let codeEdit = document.getElementById("unitCodeEdit");
        let nameEdit = document.getElementById("unitNameEdit");
        let descEdit = document.getElementById("unitDescriptionEdit");
        let formData = new FormData();
        formData.append("unitId", <?php echo $unitId; ?>);
        formData.append("code", codeEdit.value);
        formData.append("name", nameEdit.value);
        formData.append("description", descEdit.value);
        postAJAX("php/unit/updateUnit.php", formData).then(()=>{
          codeEdit.dataset.initial = codeEdit.value;
          nameEdit.dataset.initial = nameEdit.value;
          descEdit.dataset.initial = descEdit.value;
          toggleClassInfoEdit();
          let unitNameString = codeEdit.value + ": " + nameEdit.value;
          document.title = codeEdit.value + ": " + nameEdit.value;
          document.getElementById("classInfoHeader").innerHTML = codeEdit.value + ": " + nameEdit.value;
          document.getElementById("classInfoDescr").innerHTML = descEdit.value;
        }, ()=>{
          alert("There was an error updating this unit...");
        });
      }
    </script>
    <?php } ?>
    <div class="centre">
      <h1>Participants:</h1>
      <p style="margin: 12px 0;">Below are all of the students and lecturers enrolled in this unit.</p>
      <?php // Get users based on unit:
        $sql = "SELECT uId, firstName, lastName, role, email FROM user u RIGHT JOIN (SELECT uu.userId as uId FROM unitUser uu WHERE unitId = $unitId) uu ON uId = u.id ORDER BY role DESC";
        $stmt = $dbh->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result) { // if query or database connection fails:
            echo "404 Unit Not Found";
            $stmt->close();
            $dbh->close();
            exit;
        } ?>
      <table class="studentsTable">
        <thead>
          <tr>
            <th> Name </th>
            <th> Email </th>
            <th> Role </th>
          </tr>
        </thead>
        <tbody>
          <?php
          while ($user = $result->fetch_assoc()) { ?>
          <tr>
            <td><?php echo $user['firstName']; ?> <?php echo $user['lastName']; ?></td>
            <td><?php echo $user['email']; ?></td>
            <td><?php echo ($user['role'] == 2) ? 'Teacher' : 'Student'; ?></td>
          </tr>
          <?php } $stmt->close(); ?>
        </tbody>
      </table>
    </div>
  </div>
<!-- Invisible div for unit timetables -->
  <div id="timetableContent" style="display: none;">
    <div class="centre" style="text-align: center;">
        <h1><?php echo $unit['code']; ?> - Timetable</h1>
        <p style="margin: 12px;">All scheduled classes for this unit can be found below. Join the classes via the provided links below.</p>
        <?php // Get timetable based on unit
        $sql = "SELECT unitId, classTime, link, details FROM timetable WHERE unitId = ?";
        $stmt = $dbh->prepare($sql);
        $stmt->bind_param("i", $unitId);
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result) { // if query or database connection fails:
            $stmt->close();
            exit;
        } ?>
        <table class="studentsTable">
          <tr>
            <th>Class</th>
            <th>Time</th>
            <th>Link</th>
          </tr>
          <?php while ($timetable = $result->fetch_assoc()) { $ttCheck = true; ?>
          <tr>
            <td><?php echo $timetable['details']; ":"?></td>
            <td><?php echo $timetable['classTime']; ?></td>
            <td><a href="<?php echo $timetable['link']; ?>"><?php echo $timetable['link']; ?></a></td>
          </tr>
          <?php } if (!isset($ttCheck)) { ?>
              <tr>
                <td colspan="3">There are currently no classes for this unit.</td>
              </tr>
          <?php } $stmt->close(); ?>
        </table>
    </div>
  </div>

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
          <div class="class-xp-progress" style="width: <?php echo $unitXpPercentage; ?>%;"></div>
        </div>
      </div>
    </div>
    <div class="section-heading">RESOURCES</div>
    <div class="section-divider"></div>
    <div class="nav-tiles-container">
      <!--Assignment tile-->
      <div class="nav-tile" id="assignments">
        <div class="nav-tile-inner">
          <img class="nav-tile-icon" src="assets/fontAwesomeIcons/clipboard.svg" />
          <div class="nav-tile-label">ASSIGNMENTS</div>
        </div>
      </div>
      <!--Class Info tile-->
      <div class="nav-tile" id="classinfo">
        <div class="nav-tile-inner">
          <img class="nav-tile-icon" src="assets/fontAwesomeIcons/book.svg" />
          <div class="nav-tile-label">CLASS INFO</div>
        </div>
      </div>
      <!--TimeTable tile-->
      <div class="nav-tile" id="timetable">
        <div class="nav-tile-inner">
          <img class="nav-tile-icon" src="assets/fontAwesomeIcons/calender.svg" />
          <div class="nav-tile-label">TIMETABLE</div>
        </div>
      </div>
    </div>
    <div class="section-heading">
      <span>LEARNING</span>
      <?php if ($userRole == 2) { ?>
      <div id="tileEditBtn" onclick="toggleTileEdit();">✎ Edit</div>
      <div id="tileSaveBtn" onclick="saveTileArrangement();" style="display:none;">Save Changes</div>
      <div id="tileCancelBtn" onclick="toggleTileEdit(true, true);" style="display:none;">Cancel</div>
      <?php } ?>
    </div>
    <div class="section-divider"></div>

    <div class="weekly-content-container">
      <?php
      // Get unit's tiles (!!! if not cached):
      $sql = "SELECT id, icon, name, label, description, `order` FROM tile WHERE unitId = ? ORDER BY `order` ASC;";
      $stmt = $dbh->prepare($sql);

      $stmt->bind_param("i", $unitId);
      $stmt->execute();
      $result = $stmt->get_result();
      $stmt->close();

      if (!$result) { // if query or database connection fails:
        echo "404 Unit Not Found"; // !!! review?
        $dbh->close();
        exit;
      }

      while ($tile = $result->fetch_assoc()) {
        //get the count of total tasks for this tile
        $sql = "SELECT COUNT(cn.id) FROM Content cn
              RIGHT JOIN Component cm ON cn.componentId = cm.id
              WHERE cm.tileId = ? AND cn.isTask = 1";
        $stmt = $dbh->prepare($sql);
        $stmt->bind_param("i", $tile['id']);
        $stmt->execute();
        $stmt->bind_result($taskCount);
        $stmt->fetch();
        $stmt->close();

        // Update the totalTask count for the tile being loaded
        $sql = "UPDATE tile AS t SET t.totalTasks = ? WHERE t.id = ?;";
        $stmt = $dbh->prepare($sql);
        $stmt->bind_param("ii", $taskCount, $tile['id']);
        $stmt->execute();
        $stmt->close();

        //get the number of tasks in this tile this user has completed
        $sql = "SELECT COUNT(id) FROM taskcompletion WHERE tileId=? AND userId=? AND isComplete = 1;";
        $stmt = $dbh->prepare($sql);
        $stmt->bind_param("ii", $tile['id'], $userId);
        $stmt->execute();
        $stmt->bind_result($completedTaskCount);
        $stmt->fetch();
        $stmt->close();

        //get percentage of task completion
        if ($taskCount == 0) {
          $xpPercentage = 100;
        } else {
          $xpPercentage = ($completedTaskCount / $taskCount) * 100;
          $xpPercentage = floor($xpPercentage);
        }

      ?>
        <div class="unitTileDiv" data-initial="<?php echo $tile['order']; ?>">
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
              <p class="unitTileXpLabel">XP:</p>
              <div class="unitTileXpBar">
                <div class="unitTileXpProgress" style="width:<?php echo $xpPercentage; ?>%;">

                </div>
              </div>
            </div>
          </div>
          <div class="unitTileEditCell" style="display: none;">
            <div class="unitTileIconHolder">
              <img src="" alt="">
            </div>
            <div class="unitTileContents">
              <input type="text" value="<?php echo $tile['name']; ?>" style="color: white;">
              <input type="text" value="<?php echo $tile['label']; ?>">
            </div>
            <div class="unitTileGrip">
              <span>: : :</span>
            </div>
          </div>
        </div>
      <?php }

      $dbh->close();
      ?>
    </div>
  </div>
  <script>
    //select all the tiles
    const tiles = document.querySelectorAll(".unitTileHolder");
    const navTiles = document.querySelectorAll(".nav-tile");

    //loop through each tile and assign onclick function
    tiles.forEach(tile => {
      //store boolean to show if the modal has been created already to avoid loading more than once if tile is clicked more than once
      var contentLoaded = false;
      //create listener for each tile on the page
      tile.addEventListener("click", function() {
        //if modal has already been loaded -> change visiblity
        if (contentLoaded) {
          const thisModalContainer = document.querySelector("#modalContainer" + tile.id + ".modal");
          thisModalContainer.style.display = "block";

          window.onclick = function(event) {
            if (event.target == thisModalContainer) {
              thisModalContainer.style.display = "none";
            }
          }
          //if modal is not yet created -> create and make visible
        } else {
          loadModalFrame(tile, false);

          //Add edit button for lecturer
          if (<?php echo $userRole; ?> == 2) { // perform role management in session.php and never send these functions to the students !!!
            const thisModalContainer = document.querySelector("#modalContainer" + tile.id + ".modal");
            const thisModalContent = thisModalContainer.childNodes[0];
            var editButton = document.createElement('div');
            editButton.className = "modal-edit-button";
            editButton.textContent = "✎ EDIT";
            thisModalContent.appendChild(editButton);

            editButton.addEventListener("click", function() {
              thisModalContainer.style.display = "none";
              loadModalFrame(tile, true);
              getTileContents(tile.id, "#editModalCont" + tile.id, true);
            });
          }
          contentLoaded = true;
        }
        getTileContents(tile.id, "#modalCont" + tile.id, false);
      })
    });

    navTiles.forEach(navTile => {
      //store boolean to show if the modal has been created already to avoid loading more than once if tile is clicked more than once
      var contentLoaded = false;
      navTile.addEventListener("click", function() {
        //only load content if it has not already been loaded on the page
        if (contentLoaded) {
          const thisModalContainer = document.querySelector("#modalContainer" + navTile.id + ".modal");
          thisModalContainer.style.display = "block";

          window.onclick = function(event) {
            if (event.target == thisModalContainer) {
              thisModalContainer.style.display = "none";
            }
          }
        } else {
          loadNavTileModal(navTile);
          contentLoaded = true;
        }
      })
    });

    function loadModalFrame(tile, isEdit) {
      var modalContainer = document.createElement('div');
      modalContainer.className = "modal";
      modalContainer.id = "modalContainer" + (isEdit ? "Edit" : "") + tile.id;
      var modalContent = document.createElement('div');
      modalContent.id = (isEdit ? "editModalCont" : "modalCont") + tile.id;
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
      weeklyQuest.id = "wq" + tile.id;
      weeklyQuest.textContent = "Do some stuff and learn some thing.";
      weeklyQuestContainer.appendChild(weeklyQuest);

      modalContainer.style.display = "block";
      //close modal functions
      if (isEdit) {
        closeButton.onclick = function() {
          modalContainer.innerHTML = '';
          modalContainer.remove();
        }
      } else {
        closeButton.onclick = function() {
          modalContainer.style.display = "none";
        }
        window.onclick = function(event) {
          if (event.target == modalContainer) {
            modalContainer.style.display = "none";
          }
        }
      }

    }

    //load modal for each nav tile
    function loadNavTileModal(navTile) {
      //load empty modal
      var modalContainer = document.createElement('div');
      modalContainer.className = "modal";
      modalContainer.id = "modalContainer" + navTile.id;
      var modalContent = document.createElement('div');
      modalContent.id = "modalCont" + navTile.id;
      modalContent.className = "modal-content";
      var closeButton = document.createElement('span');
      closeButton.className = "close";
      closeButton.textContent = "x";

      document.body.appendChild(modalContainer);
      modalContainer.appendChild(modalContent);
      modalContent.appendChild(closeButton);
      modalContainer.style.display = "block";

      /*

      JACK AND CONNOR
      USE SECTION BELOW TO CREATE YOUR MODALS

        ||
        ||
        ||
      \ || /
        \/

      */


      //fill modal with content based on the nav tile id
      if (navTile.id == "assignments") {
        console.log("loadNavTile assignments");
        var assHolder = $("#modalContassignments");

        //TESTING STUFF
        assHolder.append($("#assContent").show());
        //assHolder.append($("#assTeacherContent").show());

        //PRODUCTION IF
        /*
        if (<?php echo $userRole; ?> == 2){
          assHolder.append($("#assTeacherContent").show());
        } else{
          assHolder.append($("#assContent").show());
        }
        */


      }
      if (navTile.id == "classinfo") {
        var cInfoHolder = $("#modalContclassinfo");
        cInfoHolder.append($("#classInfoContent").show());
      }
      if (navTile.id == "timetable") {
        var timetableHolder = $("#modalConttimetable");
        timetableHolder.append($("#timetableContent").show());
      }

      //make modal closeable
      closeButton.onclick = function() {
        modalContainer.style.display = "none";
      }
      window.onclick = function(event) {
        if (event.target == modalContainer) {
          modalContainer.style.display = "none";
        }
      }
    }

    // fetch tile component & contents:
    function getTileContents(id, parent, isEdit) {
      var formData = new FormData();
      formData.append("tileId", id);
      var promise = postAJAX("php/tiles/loadTileContent.php", formData);
      promise.then(function(data) {
        if (isEdit) {
          unpackTileJSONEdit(data, parent, id);
        } else {
          unpackTileJSON(data, parent, id);
        }
      }).catch(function(error) {
        console.error('Error:', error); // !!! better solution
      });
    }

    // constructs the tile's components & content from JSON objects:
    function unpackTileJSON(data, parent, tileId) {
      $("#wq" + tileId).html("");
      var taskCount = 0;
      var taskCompleteCount = 0;
      var holder = $(parent);
      var componentsArray = JSON.parse(data.components);
      if (!componentsArray) {
        return;
      }
      componentsArray.forEach(function(ele) {
        $("#comp" + ele.id + ".modal-component").remove();
        let component = $("<div>").addClass("modal-component").attr("id", "comp" + ele.id);
        holder.append(component);
        component.append($("<div>").addClass("modal-component-title").html(ele.name));
        component.append($("<div>").addClass("modal-component-description").html(ele.description));
        component.append($("<div>").addClass("modal-inner-content").attr("id", "compContent" + ele.id));
      });
      var contentArray = JSON.parse(data.content);
      if (!contentArray) {
        return;
      }
      contentArray.forEach(function(ele) {
        let contentType = "<p>"; // !!! insert type logic
        let contentClass = "unordered";
        if (ele.type == 1) {
          contentClass = "ordered";
        } else if (ele.type == 2) {
          contentType = "<a>";
        } else if (ele.type == 3) { // download server file or navigate to url:
          contentType = "<a>";
          contentClass = "external";
        }
        let contentHolder = $("<li>").addClass("modal-content-item " + contentClass).attr("id", "content" + ele.id);
        let content = $(contentType).html(ele.name);
        contentHolder.append(content);
        if (ele.isTask) {
          taskCount++;
          let weeklyQuestLi = $("<li>").addClass("weeklyQuestListItem").html(ele.name);
          $("#wq" + tileId).append(weeklyQuestLi);
          let taskBtn = $("<button>").addClass("modal-content-task").attr("id", "task" + ele.id);
          taskBtn.attr("onclick", "requestTaskToggle(" + ele.id + "," + tileId + ");");
          if (ele.isComplete) {
            taskCompleteCount++;
            weeklyQuestLi.addClass("weeklyQuestCompleted");
            taskBtn.html("✓");
            taskBtn.addClass("completed");
          }
          contentHolder.append(taskBtn);
        }

        if (ele.type == 2) {
          content.attr('href', 'files/<?php echo $unitId; ?>/content/' + ele.url);
          content.attr('download', ele.url);
        } else if (ele.type == 3) {
          content.attr('href', "https://" + ele.url); // !!! this should be more adaptable
          content.attr('target', '_blank');
        }
        $("#compContent" + ele.componentId).append(contentHolder);
      });
      let weeklyQuestTaskCount = $("<p>").html("Quest items completed for this week (" + taskCompleteCount + "/" + taskCount + ")");
      $("#wq" + tileId).prepend(weeklyQuestTaskCount);
      loadComments(parent, tileId);
    }
    // Loads the comments on each tile:
    function loadComments(parent, tileId) {
      $(".commentsDiv").remove();
      var holder = $(parent);
      var commentsDiv = $("<div class='commentsDiv'>");
      holder.append(commentsDiv);
      commentsDiv.append($("<hr>").css("margin", "16px 0"));
      commentsDiv.append($("<div class='modal-component-title'>").html("Add a Comment"));
      var commentInputDiv = $("<div class='commentsInputDiv'>");
      commentsDiv.append(commentInputDiv);
      var commentField = $("<textarea class='commentsInput' id='commentInput" + tileId + "'>");
      var commentBtn = $("<button class='commentBtn'>").html("SUBMIT").on("click", function() {
        createComment(parent, tileId);
      });
      commentInputDiv.append(commentField);
      commentInputDiv.append(commentBtn);

      var commentSection = $("<div class='commentSection' id='commentHolder" + tileId + "'>");
      commentsDiv.append(commentSection);
      commentSection.append($("<div class='modal-component-title'>").html("Comments"));

      var formData = new FormData();
      formData.append("tileId", tileId);
      var promise = postAJAX("php/tiles/loadComments.php", formData);
      promise.then(function(data) {
        let comments = data;
        if (!comments) {
          return;
        }
        comments.forEach(function(ele) {
          generateComment(tileId, ele);
        });
      });
    }

    function createComment(parent, tileId) {
      comment = $("#commentInput" + tileId).val();

      var formData = new FormData();
      formData.append("tileId", tileId);
      formData.append("comment", comment);
      var promise = postAJAX("php/tiles/createComment.php", formData);
      promise.then(function(data) {
        loadComments(parent, tileId);
      }).catch(function() {
        alert("There was an error submitting this comment.")
      });
    }

    function generateComment(tileId, data) {
      let name = data.name;
      let message = data.comment;
      let date = "!!! we need a date column";
      var comment = $("<div id='comment" + data.cid + "'>").addClass("comment");
      var iconElement = $("<div>").addClass("commentIcon");
      if (data.role == 2) {
        iconElement.addClass("lecturerIcon");
      }
      var nameElement = $("<div>").addClass("commentUserName").text(name);
      var dateElement = $("<div>").addClass("commentDate").text(date);
      var messageElement = $("<div>").addClass("commentMessage").text(message);
      comment.append(iconElement, nameElement, dateElement, messageElement);
      $("#commentHolder" + tileId).append(comment);
    }

    // Task ticking & unticking:
    function toggleTask(id) {
      let task = $("#task" + id);
      task.prop("disabled", true);
      if (task.html() == "") {
        task.html("✓");
        task.addClass("completed");
        return true;
      } else if (task.html() == "✓") {
        task.html("");
        task.removeClass("completed");
        return false;
      }
    }

    function requestTaskToggle(id, tileId) {
      let state = toggleTask(id);
      var formData = new FormData();
      formData.append("tileId", tileId);
      formData.append("contentId", id);
      if (state) {
        formData.append("taskState", true);
      }
      var promise = postAJAX("php/tiles/taskToggle.php", formData);
      promise.then(function(data) {
        $("#task" + id).prop("disabled", false);
      }).catch(function(error) {
        toggleTask();
        $("#task" + id).prop("disabled", false);
        console.error('Error:', error); // !!! better solution
      });
    }
    var currentCompId = -1; // Used for created components:
    var activeComp;
    var offset;
    var dragIndex;
    var modalScroll;
    function createEditableComponent(holder, data = null) {
      let di = data == null; // used to differentiate new component's values when applicable.
      if (di) {
        data = {
          id: currentCompId,
          name: "",
          description: "",
          order: -1
        };
        currentCompId--;
      }
      /// Create complete component:
      let component = $("<div>").addClass("modal-component edit-field").attr("id", "editComp" + data.id).hide();
      let componentHead = $("<div>").addClass("component-head");
      componentHead.append($("<input type='text'>").addClass("modal-component-title").val(data.name).data("initial", !di ? data.name : "𓁔𓃸").prop("placeholder", "Enter heading...").change(function() {
        $(this).parent().parent().parent().find(".dragTitle").html(this.value);
      }));
      componentHead.append($("<div>").addClass("componentCondenser").html("▲").attr("onclick", "condenseComponent(" + data.id + ");"));
      component.append(componentHead);
      component.append($("<textarea>").addClass("modal-component-description").val(data.description).data("initial", !di ? data.description : "𓁔𓃸").prop("placeholder", "Write a description here...").change(function() {
        $(this).parent().parent().find(".dragDescription").html(this.value);
      }));
      component.append($("<div>").addClass("modal-inner-content").attr("id", "editCompContent" + data.id));
      let addBtnHolder = $("<div>").on("click", function() {
        createEditableContent(null, data.id);
      });
      component.append(addBtnHolder);
      addBtnHolder.append($("<div>").addClass("add-content-btn").attr("id", "add-content-btn" + data.id).html("+"));
      addBtnHolder.append($("<div>").addClass("add-content-btn-label").html("Add Content"));
      addBtnHolder.append($("<div>").addClass("edit-modal-delete-component").html("Delete").attr("onclick", "deleteComponent(" + data.id + ");"));

      /// Create condensed modal:
      let dragArea = $("#compDragArea");
      let condensedCompHolder = $("<div>").addClass("dragCompHolder").attr("id", "dragCompHolder"+data.id).data("initial", data.order);
      let condensedComp = $("<div>").addClass("dragComp").attr("id", "dragComp"+data.id).data("id", data.id);
      condensedCompHolder.append(condensedComp);
      condensedCompHolder.on("mouseenter", function(event){
        let comp = $(this);
        if (dragging && comp.index() != dragIndex) {
          if (comp.index() < dragIndex) {
            comp.before(activeComp.parent());
            dragIndex = activeComp.parent().index();
          } else {
            comp.after(activeComp.parent());
            dragIndex = activeComp.parent().index();
          }
        }
      });
      // append the editable component to its condensed holder:
      condensedCompHolder.append(component);
      dragArea.append(condensedCompHolder);

      let dragGrip = $("<div>").addClass("modal-component-drag").html(": : :");
      condensedComp.append(dragGrip);
      condensedComp.append($("<div>").addClass("modal-component-title dragTitle").html(data.name));
      condensedComp.append($("<div>").addClass("modal-component-description dragDescription").html(data.description));
      condensedComp.append($("<div>").addClass("componentCondenser").html("▼").attr("onclick", "condenseComponent(" + data.id + ");"));

      dragGrip.on('mousedown', function(event) {
        activeComp = $(this).parent();
        activeComp.width(activeComp.width());
        dragIndex = activeComp.parent().index();
        let startPos = activeComp.offset();
        offset = {x: event.pageX - startPos.left, y: event.pageY - startPos.top};
        modalScroll = activeComp.parent().parent().parent().parent();
        activeComp.addClass('compDragging');
        dragging = true;
      });
    }
    var dragging = false;
    $(document).ready(function() {
      $(document).on('mouseup', function() {
        if (dragging) {
          activeComp.removeClass('compDragging');
          activeComp.css({
            width: "",
            left: "",
            top: ""
          });
          dragging = false;
        }
      });
      $(document).on("mousemove", function(event) {
        if (dragging) {
          // var mouseX = event.pageX - offset.x;
          var mouseY = event.pageY - offset.y + modalScroll.scrollTop() - $(document).scrollTop();
          activeComp.css({
              // left: mouseX + "px",
              top: mouseY + "px"
          });
        }
      });
    });
    function condenseComponent(id) {
      $("#dragComp"+id).toggle();
      $("#editComp"+id).toggle();
    }
    function condenseAll(open = false) {
      if (open) {
        $(".dragComp").hide();
        $(".modal-component.edit-field").show();
      } else {
        $(".dragComp").show();
        $(".modal-component.edit-field").hide();
      }
    }
    var currentContId = -1; // Used for creating content:
    function createEditableContent(ele = null, compId = null) {
      let di = ele == null && compId != null; // used to differentiate new content's values when applicable.
      if (di) {
        ele = {
          id: currentContId,
          type: 0,
          name: "",
          url: "",
          isTask: 0,
          order: -1,
          componentId: compId
        };
        currentCompId--;
      }

      let contentHolder = $("<div>").attr("id", "editContent" + ele.id).addClass("edit-content-holder").data("initial", ele.order);
      let typeRow = $("<div>").addClass("edit-content-row-type");
      typeRow.append($("<div>").addClass("edit-content-label").html("Type:"));
      var typeSelect = $("<select>").addClass("edit-content-field");
      $("<option />", {
        value: "0",
        text: "Dot Point"
      }).appendTo(typeSelect);
      $("<option />", {
        value: "1",
        text: "Numbered Point"
      }).appendTo(typeSelect);
      $("<option />", {
        value: "2",
        text: "Download Link"
      }).appendTo(typeSelect);
      $("<option />", {
        value: "3",
        text: "Clickable Link"
      }).appendTo(typeSelect);
      typeSelect.val(ele.type).data("initial", !di ? ele.type : "𓁔𓃸");
      typeRow.append(typeSelect);
      typeRow.append($("<img>").attr("src", "assets/deleteIcon.svg").attr("alt", "Delete").addClass("edit-content-delete-icon").attr("onclick", "deleteContent(" + ele.id + ");"));
      contentHolder.append(typeRow);

      let textRow = $("<div>").addClass("edit-content-row");
      textRow.append($("<div>").addClass("edit-content-label").html("Text:"));
      textRow.append($("<textarea>").addClass("edit-content-text").html(ele.name).data("initial", !di ? ele.name : "𓁔𓃸"));
      contentHolder.append(textRow);

      let urlRow = $("<div>").addClass("edit-content-row-url");
      urlRow.append($("<div>").addClass("edit-content-label").html("URL:"));
      urlRow.append($("<input type='text'>").addClass("edit-content-url").val(ele.url).data("initial", !di ? ele.url : "𓁔𓃸"));
      contentHolder.append(urlRow);

      let taskRow = $("<div>").addClass("edit-content-row");
      taskRow.append($("<div>").addClass("edit-content-label").html("Assign as task:"));
      let taskCheckbox = $("<input type='checkbox'>").addClass("modal-content-task edit-content-status").data("initial", !di ? ele.isTask : "𓁔𓃸");
      taskRow.append(taskCheckbox);
      if (ele.isTask == 1) {
        taskCheckbox.attr("checked", "true");
      }
      contentHolder.append(taskRow);

      $("#editCompContent" + ele.componentId).append(contentHolder);
    }
    //Load modal content and components in edit mode
    function unpackTileJSONEdit(data, parent, tileId) {
      var holder = $(parent);
      let buttonHolder = $("<div>").addClass("save-cancel-btn-container");
      buttonHolder.append($("<div>").addClass("save-cancel-btn").html("Save").attr("onclick", "saveTile(" + tileId + ");"));
      buttonHolder.append($("<div>").addClass("save-cancel-btn").html("Cancel").attr("onclick", "$('#modalContainerEdit" + tileId + "').remove(); document.querySelector('#modalContainer" + tileId + "').style.display = 'block';"));
      buttonHolder.append($("<div>").addClass("save-cancel-btn").html("Add Component").attr("onclick", "createEditableComponent($('#editModalCont" + tileId + "'));").css("float", "right"));
      holder.append(buttonHolder);

      let toggleHolder = $("<div>").addClass("collapseToggleContainer");
      toggleHolder.append($("<div>").addClass("componentCondenserToggle").html("Collapse All ▲").attr("onclick", "condenseAll();"));
      toggleHolder.append($("<div>").addClass("componentCondenserToggle").html("Open All ▼").attr("onclick", "condenseAll(true);"));
      holder.append(toggleHolder);

      var componentsArray = JSON.parse(data.components);
      if (!componentsArray) {
        return;
      }
      holder.append($("<div id='compDragArea'>"));
      componentsArray.forEach(function(ele) {
        createEditableComponent(holder, ele);
      });
      var contentArray = JSON.parse(data.content);
      if (!contentArray) {
        return;
      }
      contentArray.forEach(function(ele) {
        createEditableContent(ele);
      });
    }

    <?php if ($userRole == 2) { // lecturer only functions:
    ?>
      function deleteComponent(compId) {
        if (!confirm("Are you sure you want to delete this component? All its associated content will be deleted with it.")) {
          return;
        }

        if (compId < 0) { // if a newly client-created component, delete from client-side:
          $("#editComp" + compId).parent().remove();
          return;
        }
        var formData = new FormData();
        formData.append("componentId", compId);
        var promise = postAJAX("php/tiles/deleteComponent.php", formData);
        promise.then(function(data) {
          $("#editComp" + compId).parent().remove();
          alert("Component and its children were successfully deleted."); // !!! convert alerts and confirmations into proper displays/modals (talk with Ky) !!!
        }).catch(function(error) {
          alert("There was an error deleting this component, please try again later."); // !!! ^^^
        });
      }

      function deleteContent(contId) {
        if (!confirm("Are you sure you want to delete this content?")) {
          return;
        }

        if (contId < 0) { // if a newly client-created content, delete from client-side:
          $("#editContent" + contId).remove();
          return;
        }
        var formData = new FormData();
        formData.append("contentId", contId);
        var promise = postAJAX("php/tiles/deleteContent.php", formData);
        promise.then(function(data) {
          $("#editContent" + contId).remove();
          alert("Content successfully deleted.");
        }).catch(function(error) {
          alert("There was an error deleting this content, please try again later.");
        });
      }

      function saveTile(tileId) {
        // get all elements required:
        let modal = $("#editModalCont" + tileId);
        let componentHolders = modal.find(".modal-component.edit-field");
        let contentHolders = modal.find(".edit-content-holder");

        // manage component changes:
        var componentArray = [];
        componentHolders.each(function() {
          let ele = $(this);
          let component = {};
          let modified = false;
          component.tileId = tileId;
          component.compId = (ele.attr("id")).match(/-?\d+$/)[0]; // get component's id from the element's id string.

          // get other component attributes, but only if they've changed:
          let title = $(ele.find(".modal-component-title")[0]);
          if (title.val().trim() != ensureString(title.data("initial"))) {
            component.name = title.val().trim();
            modified = true;
          }
          let description = $(ele.find(".modal-component-description")[0]);
          if (description.val().trim() != ensureString(description.data("initial"))) {
            component.description = description.val().trim();
            modified = true;
          }
          let draggedComp = $("#dragCompHolder"+component.compId);
          if (draggedComp.data("initial") != draggedComp.index()) {
            component.order = draggedComp.index();
            modified = true;
          }

          // append to data for insertion:
          if (modified) {
            componentArray.push(component);
          }
        });
        // manage content attributes, but only if they've changed:
        var contentArray = [];
        contentHolders.each(function() {
          let ele = $(this);
          let content = {};
          let modified = false;
          content.contId = (ele.attr("id")).match(/-?\d+$/)[0]; // get component's id from the element's id string.

          // get other component attributes, but only if they've changed:
          let contentType = $(ele.find(".edit-content-field")[0]);
          if (contentType.val() != contentType.data("initial")) {
            content.type = contentType.val();
            modified = true;
          }
          let text = $(ele.find(".edit-content-text")[0]);
          if (text.val().trim() != ensureString(text.data("initial"))) {
            content.name = text.val().trim();
            modified = true;
          }
          let url = $(ele.find(".edit-content-url")[0]);
          if (url.val().trim() != ensureString(url.data("initial"))) {
            content.url = url.val().trim();
            modified = true;
          }
          let status = $(ele.find(".edit-content-status")[0]);
          if (status.prop("checked") != status.data("initial")) {
            content.status = (status.prop("checked") ? 1 : 0);
            modified = true;
          }
          let order = ele.index();
          if (order != ele.data("initial")) {
            content.order = order;
            modified = true;
          }
          content.componentId = (ele.parent().attr("id")).match(/-?\d+$/)[0];

          // append to data for insertion:
          if (modified) {
            contentArray.push(content);
          }
        });

        // convert to JSON and send for processing:
        if (componentArray.length > 0 || contentArray.length > 0) {
          if (!confirm("Are you sure you want to save these changes?")) {
            return;
          }

          let jsonData = [];
          jsonData.push(componentArray);
          jsonData.push(contentArray);

          var formData = new FormData();
          formData.append("tileUpdateJSON", JSON.stringify(jsonData));
          var promise = postAJAX("php/tiles/saveTile.php", formData);
          promise.then(function(data) {
            alert("Tile successfully updated.");
            $("#modalContainerEdit" + tileId).remove(); // !!! reload tile too
          }).catch(function(error) {
            alert("There was an error saving this tile's components & content, please try again later.");
          });
        } else { // if no changes, just go back:
          $("#modalContainerEdit" + tileId).remove();
          document.querySelector('#modalContainer' + tileId).style.display = 'block';
        }
      }

      // converts any null values to empty strings:
      function ensureString(input) {
        return input === null ? "" : String(input);
      }

      // Tile editing:
      function toggleTileEdit(close = false, cancel = false) {
        if (close) {
          if (cancel) {
            restoreTileOrder()
          }
          $("#tileEditBtn").show();
          $("#tileSaveBtn").hide();
          $("#tileCancelBtn").hide();
          $(".unitTileHolder").show();
          $(".unitTileEditCell").hide();
        } else {
          $("#tileEditBtn").hide();
          $("#tileSaveBtn").show();
          $("#tileCancelBtn").show();
          $(".unitTileHolder").hide();
          $(".unitTileEditCell").show();
        }
      }

      // tile saving:
      function saveTileArrangement() {
        if (!confirm("Are you sure you want to save these changes?")) {
          return;
        }
        $('.unitTileDiv').each(function() { // set data attributes to new defaults following save.
          $(this).attr("data-initial", $(this).index());
        });
        toggleTileEdit(true);
      }

      // tile drag & dropping:
      var tileDragging = false;
      var activeTile;
      var tileDragIndex;
      var tileOffset;
      $(function() {
        $(".unitTileDiv").on("mouseenter", function(event){
          let tile = $(this);
          if (tileDragging && tile.index() != tileDragIndex) {
            if (tile.index() < tileDragIndex) {
              tile.before(activeTile.parent());
              tileDragIndex = activeTile.parent().index();
            } else {
              tile.after(activeTile.parent());
              tileDragIndex = activeTile.parent().index();
            }
          }
        });
        $(".unitTileGrip").on('mousedown', function(event) {
          activeTile = $(this).parent();
          activeTile.width(activeTile.width());
          tileDragIndex = activeTile.parent().index();
          activeTile.parent().width(activeTile.parent().width());
          activeTile.parent().height(activeTile.parent().height());
          let startPos = activeTile.offset();
          tileOffset = {x: event.pageX - startPos.left, y: event.pageY - startPos.top};
          activeTile.addClass('tileDragging');
          tileDragging = true;
        });
      });
      $(document).on('mouseup', function() {
        if (tileDragging) {
          activeTile.removeClass('tileDragging');
          activeTile.css({
            width: "",
            left: "",
            top: ""
          });
          activeTile.parent().css({
            width: "",
            height: ""
          });
          tileDragging = false;
        }
      });
      $(document).on("mousemove", function(event) {
        if (tileDragging) {
          var mouseX = event.pageX - tileOffset.x;
          var mouseY = event.pageY - tileOffset.y;
          activeTile.css({
              left: mouseX + "px",
              top: mouseY + "px"
          });
        }
      });

      function restoreTileOrder() {
        let parent = $(".weekly-content-container");
        let items = $('.unitTileDiv').toArray();
        items.sort(function(a, b) {
            var indexA = $(a).attr('data-initial');
            var indexB = $(b).attr('data-initial');
            return indexA - indexB;
        });
        $.each(items, function(index, element) {
            parent.append(element);
        });
      }
    <?php } ?>
  </script>
</body>
</html>
