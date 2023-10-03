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

//assign a class rank variable which is used to display the rank icon
$classRank = 1;
switch (true) {
  case $unitXpPercentage > 25 && $unitXpPercentage <= 50:
      $classRank = 2;
      break;
  case $unitXpPercentage > 50 && $unitXpPercentage <= 75:
      $classRank = 3;
      break;
  case $unitXpPercentage > 75:
      $classRank = 4;
      break;
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
    <div class="collapse-all-container">
      <div class="assignment-collapse-controller" onClick="collapseAssignents(true)">Collapse All ▲</div>
      <div class="assignment-collapse-controller" onClick="collapseAssignents()">Open all ▼</div>
    </div>
    <?php
    while ($assignment = $assResult->fetch_assoc()) {
      $assCount++;
    ?>
      <div id="assignmentTableContainer">
        <div class="assignment-label-container">
          <div class="assignment-label">Assignment <?php echo $assCount; ?></div>
          <div class="assignment-collapse-button" id="<?php echo $assCount; ?>">▲</div>
        </div>
        <table class="assignment-table" id="assignment-table-<?php echo $assCount; ?>">
          <tr>
            <th>Description: </th>
            <th><?php echo $assignment['description']; ?></th>
          </tr>
          <tr>
            <th>Due Date: </th>
            <th><?php
                $date = strtotime($assignment['due']);
                $remaining = $date - time();
                $days_remaining = floor($remaining / 86400);
                $hours_remaining = floor(($remaining % 86400) / 3600);
                if ($days_remaining > 0) {
                  echo $assignment['due'] . " | Days Left: " . $days_remaining;
                }
                if ($days_remaining == 0) {
                  echo $assignment['due'] . " | Hours Left: " . $hours_remaining . " DUE TODAY!";
                }
                if ($days_remaining < 0) {
                  echo $assignment['due'] . " | Past Due Date.";
                }


                ?></th>
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
              <form action="/DevProject/upload.php?assignmentId=<?php echo $assCount; ?>&unitId=<?php echo $_GET['id']; ?>&userId=<?php echo $userId ?>" method="post" enctype="multipart/form-data">
                <input type="file" name="fileToUpload" id="fileToUpload">
                <input type="submit" value="Submit" name="submit">
              </form>
            </th>
          </tr>
          <tr >
            <th>Status: </th>
            <th id="submission-status-<?php echo $assCount; ?>">
              <?php

              $sql = "SELECT status, grade, comment FROM submission WHERE userId = ? AND assignmentsId = ?";
              $stmt = $dbh->prepare($sql);
              //echo "USERID: " . $userId;
              //echo " ASSIGNMENT ID = " . $assCount;
              $stmt->bind_param("ii", $userId, $assCount);
              $stmt->execute();
              $resultSub = $stmt->get_result();
              $submission = $resultSub->fetch_assoc();
              $stmt->close();
              if (isset($submission['status'])) {
                if ($submission['status'] == 1) {
                  echo "Waiting for Grade.";
                } else if ($submission['status'] == 2) {
                  echo "Graded. | " . "Mark: " . $submission['grade'] . " | " . "\n<br>Comment: " . $submission['comment'] . "\n<br>";
                  //Marking sheet DIR
                  $mark_dir = "Assignments/$unitId/$assCount/$userId/markingsheet";
                  $skipped = array('0', '1');
              ?>
                  <a href="<?php
                            $download = scandir("$mark_dir/");
                            foreach ($download as $key => $assgnmentName) {
                              if (in_array($key, $skipped)) {
                                continue;
                              }
                              echo "$mark_dir/$assgnmentName";
                            }
                            ?>">Download Marking Sheet.</a>
            </th>
        <?php
                }
              } else {
                echo "Not submitted.";
              }

        ?>
        </th>
          </tr>
        </table>
      </div>
    <?php } ?>
  </div>

  <!-- Invisible div for TEACHER assignment view -->
  <div id="assTeacherContent" style="display:none;">
    <div class="modal-unit-heading">Assignments for <?php echo $unit['name']; ?></div>
    <div class="collapse-all-container">
      <div class="assignment-collapse-controller" onClick="collapseAssignents(true)">Collapse All ▲</div>
      <div class="assignment-collapse-controller" onClick="collapseAssignents()">Open all ▼</div>
    </div>
    <?php
    while ($assignment = $assTResult->fetch_assoc()) {
      $assTCount++;
    ?>
      <div>
      <div class="assignment-label-container">
          <div class="assignment-label">Assignment <?php echo $assTCount; ?></div>
          <div class="assignment-collapse-button" id="<?php echo $assTCount; ?>">▲</div>
        </div>
        <table class="assignment-table" id="lecturer-assignment-table-<?php echo $assTCount; ?>">
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
      <h1 style="float: left;" id="classInfoHeader"><?php echo $unit["code"] . ": " . $unit["name"]; ?></h1>
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
          postAJAX("php/unit/updateUnit.php", formData).then(() => {
            codeEdit.dataset.initial = codeEdit.value;
            nameEdit.dataset.initial = nameEdit.value;
            descEdit.dataset.initial = descEdit.value;
            toggleClassInfoEdit();
            let unitNameString = codeEdit.value + ": " + nameEdit.value;
            document.title = codeEdit.value + ": " + nameEdit.value;
            document.getElementById("classInfoHeader").innerHTML = codeEdit.value + ": " + nameEdit.value;
            document.getElementById("classInfoDescr").innerHTML = descEdit.value;
          }, () => {
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
          <?php }
          $stmt->close(); ?>
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
      $sql = "SELECT id, unitId, classTime, link, details FROM timetable WHERE unitId = ?";
      $stmt = $dbh->prepare($sql);
      $stmt->bind_param("i", $unitId);
      $stmt->execute();
      $result = $stmt->get_result();
      if (!$result) { // if query or database connection fails:
        $stmt->close();
        exit;
      } ?>
      <table class="studentsTable" id="timetableTable">
        <tr>
          <th>Class</th>
          <th>Time</th>
          <th>Link</th>
          <th class="classEdit" style="display:none;">Action</th>
        </tr>
        <?php while ($timetable = $result->fetch_assoc()) {
          $ttCheck = true; ?>
          <tr id="classRecord<?php echo $timetable['id']; ?>" class="classEdit">
            <td id="classRecordDetails<?php echo $timetable['id']; ?>"><?php echo $timetable['details']; ?></td>
            <td id="classRecordTime<?php echo $timetable['id']; ?>"><?php echo $timetable['classTime']; ?></td>
            <td><a href="<?php echo $timetable['link']; ?>" id="classRecordLink<?php echo $timetable['id']; ?>"><?php echo $timetable['link']; ?></a></td>
          </tr>
          <?php if ($userRole == 2) { ?>
            <tr id="classEditRecord<?php echo $timetable['id']; ?>" class="classEdit timetableEditRow" style="display: none;" data-class-id="<?php echo $timetable['id']; ?>">
              <td><input type="text" value="<?php echo $timetable['details'];
                                            ":" ?>" id="classEditDetails<?php echo $timetable['id']; ?>"></td>
              <td><input type="text" value="<?php echo $timetable['classTime'];
                                            ":" ?>" id="classEditTime<?php echo $timetable['id']; ?>"></td>
              <td><input type="text" value="<?php echo $timetable['link'];
                                            ":" ?>" id="classEditLink<?php echo $timetable['id']; ?>"></td>
              <td><button type="button" class="edit-modal-delete-component" onclick="deleteTimetableClass(this,<?php echo $timetable['id']; ?>);" style="float: none;">Delete</button></td>
            </tr>
          <?php } ?>
        <?php }
        if (!isset($ttCheck)) { ?>
          <tr class="classRecordRow">
            <td colspan="3">There are currently no classes for this unit.</td>
          </tr>
        <?php }
        $stmt->close(); ?>
      </table>
      <?php if ($userRole == 2) { ?>
        <button type="button" class="modal-edit-button classEdit" onclick="toggleTimetableEdit();" style="margin-top: 12px;">✎ Edit</button>
        <div class="classEdit" style="display: none;">
          <button type="button" onclick="createTimetableRow();" class="addClassRowBtn">+ Add Row</button>
          <hr>
          <button type="button" onclick="cancelTimetableEdit();" class="save-cancel-btn">Cancel</button>
          <button type="button" onclick="saveTimetable();" class="save-cancel-btn">Save</button>
        </div>
        <script>
          function toggleTimetableEdit() {
            $(".classEdit").toggle();
          }

          function deleteTimetableClass(btn, classId = null) {
            if (classId != null) {
              if (!confirm("Are you sure you want to delete this class from the unit timetable? This action will be permanent.")) {
                return;
              }
              let formData = new FormData();
              formData.append("classId", classId);
              postAJAX("php/unit/deleteTimetableRecord.php", formData).then(() => {
                $(btn).parent().parent().remove();
                $("#classRecord" + classId).remove();
              }, () => {
                alert("There was an error deleting this class from the timetable...");
              });
            } else {
              $(btn).parent().parent().remove();
            }
          }

          function cancelTimetableEdit() {
            $(".timetableEditRow").each(function(index) {
              const $this = $(this);
              if ($this.attr("data-class-id")) {
                let classId = $this.attr("data-class-id");
                $this.find("#classEditTime" + classId).val(document.getElementById("classRecordTime" + classId).innerHTML);
                $this.find("#classEditLink" + classId).val(document.getElementById("classRecordLink" + classId).innerHTML);
                $this.find("#classEditDetails" + classId).val(document.getElementById("classRecordDetails" + classId).innerHTML);
              }
            });
            toggleTimetableEdit();
          }

          function createTimetableRow() {
            var row = $('<tr>').addClass('classEdit timetableEditRow');
            var detailsInput = $('<input>').attr('class', 'classEditDetails');
            var timeInput = $('<input>').attr('type', 'text').attr('class', 'classEditTime');
            var linkInput = $('<input>').attr('type', 'text').attr('class', 'classEditLink');
            var deleteButton = $('<button>').attr('type', 'button')
              .addClass('edit-modal-delete-component').text('Delete').css('float', 'none')
              .on('click', function() {
                deleteTimetableClass(this);
              });
            row.append(
              $('<td>').append(detailsInput),
              $('<td>').append(timeInput),
              $('<td>').append(linkInput),
              $('<td>').append(deleteButton)
            );
            $("#timetableTable").append(row);
          }

          function saveTimetable() {
            let toUpdate = [];
            let toInsert = [];
            $(".timetableEditRow").each(function(index) {
              let data = {};
              const $this = $(this);
              if ($this.attr("data-class-id")) {
                data.classId = $this.attr("data-class-id");
                data.classTime = $this.find("#classEditTime" + data.classId).val();
                data.link = $this.find("#classEditLink" + data.classId).val();
                data.details = $this.find("#classEditDetails" + data.classId).val();
                let time = document.getElementById("classRecordTime" + data.classId).innerHTML;
                let link = document.getElementById("classRecordLink" + data.classId).innerHTML;
                let details = document.getElementById("classRecordDetails" + data.classId).innerHTML;
                if (time != data.classTime || link != data.link || details != data.details) {
                  toUpdate.push(data);
                }
              } else {
                data.classTime = $this.find(".classEditTime").val();
                data.link = $this.find(".classEditLink").val();
                data.details = $this.find(".classEditDetails").val();
                toInsert.push(data);
              }
            });
            let jsonObject = [toInsert, toUpdate, <?php echo $unitId; ?>];
            if (toInsert.length > 0 || toUpdate.length > 0) {
              if (!confirm("Are you sure you want to save these changes?")) {
                return;
              }
              var formData = new FormData();
              formData.append("timetableUpdate", JSON.stringify(jsonObject));
              var promise = postAJAX("php/unit/updateTimetable.php", formData);
              promise.then(function(data) {
                window.location.reload();
              }).catch(function(error) {
                alert("There was an error saving this unit's timetable...");
              });
            } else { // if no changes, just go back:
              cancelTimetableEdit();
            }
          }
        </script>
      <?php } ?>
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
      <div class="rank-display">
        <div class="tooltip">Current rank: <img class="tooltip-icon" src="assets/fontAwesomeIcons/info.svg">
          <div class="tooltip-text">As you complete learning material in this class your xp will increase and rank icon will improve</div></div>
        <div><img class="rank-display-icon fadesIn" src="assets/<?php echo $classRank; ?>.svg"></div>
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

      while ($tile = $result->fetch_assoc()) { //get the count of total tasks for this tile:
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
          <div class="unitTileHolder" id="unitTileId<?php echo $tile['id']; ?>" data-tile-id="<?php echo $tile['id']; ?>" data-tile-icon="<?php echo $tile['icon']; ?>" data-tile-name="<?php echo $tile['name']; ?>" data-tile-label="<?php echo $tile['label']; ?>" data-tile-description="<?php echo $tile['description']; ?>">
            <div class="unitTile">
              <div class="unitTileIconHolder">
                <span style="color: <?php echo ($xpPercentage == 100) ? "#007f17" : ($xpPercentage == 0 ? "#ff3d00" : "#00abff"); ?>;"><?php echo sprintf('%02d', $tile['icon']); ?></span>
              </div>
              <div class="unitTileContents">
                <p class="unitTileTitle"><?php echo $tile['name']; ?></p>
                <p class="unitTileLabel"><?php echo $tile['label']; ?></p>
              </div>
            </div>
            <div class="unitTileXpHolder">
              <p class="unitTileXpLabel">XP:</p>
              <div class="unitTileXpBar">
                <div class="unitTileXpProgress" style="width:<?php echo $xpPercentage; ?>%;"></div>
              </div>
            </div>
          </div>
          <div class="unitTileEditCell" data-tile-id="<?php echo $tile['id']; ?>" style="display: none;">
            <div class="unitTileIconHolder">
              <input type="number" value="<?php echo $tile['icon']; ?>" class="editTileIconInput" max="99" min="0">
            </div>
            <div class="unitTileContents">
              <input type="text" value="<?php echo $tile['name']; ?>" class="editTileNameInput" style="color: white;">
              <input type="text" value="<?php echo $tile['label']; ?>" class="editTileLabelInput">
            </div>
            <div class="unitTileGrip">
              <span>: : :</span>
            </div>
            <div class="deleteTileDiv">
              <span onclick="deleteTile(this, <?php echo $tile['id']; ?>);">✖</span>
            </div>
          </div>
        </div>
      <?php }
      $dbh->close(); ?>
      <button type="button" onclick="addEditTile(this);" class="addTileBtn" style="display: none;">Add Tile</button>
    </div>
    <?php if ($userRole == 2) { ?>
      <script>
        // Tile editing:
        function toggleTileEdit(close = false, cancel = false) {
          if (close) {
            if (cancel) {
              restoreTileOrder();
              $(".weekly-content-container").append($(".addTileBtn")); // re-append add tile button to end again.
            }
            $(".createdTile").remove();
            $("#tileEditBtn").show();
            $("#tileSaveBtn").hide();
            $("#tileCancelBtn").hide();
            $(".unitTileHolder").show();
            $(".unitTileEditCell").hide();
            $(".addTileBtn").hide();
          } else {
            $("#tileEditBtn").hide();
            $("#tileSaveBtn").show();
            $("#tileCancelBtn").show();
            $(".unitTileHolder").hide();
            $(".unitTileEditCell").show();
            $(".addTileBtn").show();
          }
        }

        function addEditTile(btn) {
          var $unitTileEditCell = $('<div>').addClass('unitTileEditCell');
          var $unitTileIconHolder = $('<div>').addClass('unitTileIconHolder');
          var $iconInput = $('<input>').attr('type', 'number').attr('value', 0).addClass('editTileIconInput').attr('max', '99').attr('min', '0');
          $unitTileIconHolder.append($iconInput);
          var $unitTileContents = $('<div>').addClass('unitTileContents');
          var $nameInput = $('<input>').attr('type', 'text').attr('placeholder', "Enter name...").addClass('editTileNameInput').css('color', 'white');
          var $labelInput = $('<input>').attr('type', 'text').attr('placeholder', "Enter label...").addClass('editTileLabelInput');
          $unitTileContents.append($nameInput, $labelInput);
          var $unitTileGrip = $('<div>').addClass('unitTileGrip').on("mousedown", function() {
            primeTileGrip(this);
          });
          var $span = $('<span>').text(': : :');
          $unitTileGrip.append($span);
          var $deleteTileDiv = $("<div>").addClass("deleteTileDiv");
          var $deleteSpan = $("<span>").attr("onclick", "deleteTile(this);").text("✖");
          $deleteTileDiv.append($deleteSpan);
          $unitTileEditCell.append($unitTileIconHolder, $unitTileContents, $unitTileGrip, $deleteTileDiv);
          var $unitTileDiv = $("<div>").addClass("unitTileDiv createdTile").on("mouseenter", function() {
            primeTileDiv(this);
          });
          $(".weekly-content-container").append($unitTileDiv.append($unitTileEditCell));
          $(".weekly-content-container").append($(btn)); // re-append add tile button to end again.
        }

        function deleteTile(btn, tileId = null) {
          if (tileId != null) {
            if (!confirm("Are you absolutely certain you want to delete this tile? All of its components, content, and tasks will be deleted IMMEDIATELY along with it.")) {
              return;
            } else if (!confirm("LAST WARNING. This action will be permanent.")) {
              return;
            }
            let formData = new FormData();
            formData.append("tileId", tileId);
            postAJAX("php/unit/deleteUnitTile.php", formData).then(() => {
              $("#unitTileId" + tileId).parent().remove();
            }, () => {
              alert("There was an error deleting this tile. Probably for the best.");
            });
          } else {
            $(btn).parent().parent().parent().remove();
          }
        }

        function saveTileArrangement() {
          let toUpdate = [];
          let toInsert = [];
          $(".unitTileEditCell").each(function(index) {
            let data = {};
            const $this = $(this);
            if ($this.attr("data-tile-id")) {
              data.tileId = $this.attr("data-tile-id");
              data.icon = $this.find(".editTileIconInput").val();
              data.name = $this.find(".editTileNameInput").val();
              data.label = $this.find(".editTileLabelInput").val();
              data.order = $this.parent().index();
              let tile = document.getElementById("unitTileId" + data.tileId);
              let order = tile.parentElement.dataset.initial;
              if (data.icon != tile.dataset.tileIcon || data.name != tile.dataset.tileName || data.label != tile.dataset.tileLabel || data.order != order) {
                toUpdate.push(data);
              }
            } else {
              data.icon = $this.find(".editTileIconInput").val();
              data.name = $this.find(".editTileNameInput").val();
              data.label = $this.find(".editTileLabelInput").val();
              data.order = $this.parent().index();
              toInsert.push(data);
            }
          });
          let jsonObject = [toInsert, toUpdate, <?php echo $unitId; ?>];
          if (toInsert.length > 0 || toUpdate.length > 0) {
            console.log(jsonObject);
            if (!confirm("Are you sure you want to save these changes?")) {
              return;
            }
            var formData = new FormData();
            formData.append("tilesUpdate", JSON.stringify(jsonObject));
            var promise = postAJAX("php/unit/updateTiles.php", formData);
            promise.then(function(data) {
              window.location.reload();
            }).catch(function(error) {
              alert("There was an error saving this unit's tiles...");
            });
          } else { // if no changes, just go back:
            toggleTileEdit(true);
          }
        }


        
        // tile drag & dropping:
        var tileDragging = false;
        var activeTile;
        var tileDragIndex;
        var tileOffset;

        function primeTileDiv(div) {
          let tile = $(div);
          if (tileDragging && tile.index() != tileDragIndex) {
            if (tile.index() < tileDragIndex) {
              tile.before(activeTile.parent());
              tileDragIndex = activeTile.parent().index();
            } else {
              tile.after(activeTile.parent());
              tileDragIndex = activeTile.parent().index();
            }
          }
        }

        function primeTileGrip(grip) {
          activeTile = $(grip).parent();
          activeTile.width(activeTile.width());
          tileDragIndex = activeTile.parent().index();
          activeTile.parent().width(activeTile.parent().width());
          activeTile.parent().height(activeTile.parent().height());
          let startPos = activeTile.offset();
          tileOffset = {
            x: event.pageX - startPos.left,
            y: event.pageY - startPos.top
          };
          activeTile.addClass('tileDragging');
          tileDragging = true;
        }
        $(function() {
          $(".unitTileDiv").on("mouseenter", function(event) {
            primeTileDiv(this);
          });
          $(".unitTileGrip").on('mousedown', function(event) {
            primeTileGrip(this);
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
  </div>
  <script>
    //Assignment modal dropdowns
    const assignmentTableCollapseButtons = document.querySelectorAll('.assignment-collapse-button');
    assignmentTableCollapseButtons.forEach(function(collapseButton) {
      const lecturerChildAssignmentTable = document.getElementById(`lecturer-assignment-table-${collapseButton.id}`);
      const childAssignmentTable = document.getElementById(`assignment-table-${collapseButton.id}`);
      const str = document.getElementById(`submission-status-${collapseButton.id}`).innerText.trim();
      if(str !== 'Not submitted.'){
        const parentDiv = childAssignmentTable.parentNode;
        parentDiv.classList.add('assignment-table-submitted');
        const assignmentLabel = childAssignmentTable.previousElementSibling.firstElementChild;
        assignmentLabel.style.color = '#1E1F22';
      } 
      collapseButton.addEventListener('click', function() {
        childAssignmentTable.classList.toggle('collapedContainer');
        lecturerChildAssignmentTable.classList.toggle('collapedContainer');
        if(collapseButton.textContent === '▲'){
          collapseButton.textContent = '▼';
        } else {
          collapseButton.textContent = '▲';
        }
      });
    });

    function collapseAssignents(collapse){
      const assignmentTables = document.querySelectorAll('.assignment-table');
      if(collapse){
        assignmentTables.forEach(function(table){
          table.classList.add('collapedContainer');
        })
      } else {
        assignmentTables.forEach(function(table){
          table.classList.remove('collapedContainer');
        })
      }
    }


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
          const thisModalContainer = document.querySelector("#modalContainer" + tile.dataset.tileId + ".modal");
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
            const thisModalContainer = document.querySelector("#modalContainer" + tile.dataset.tileId + ".modal");
            const thisModalContent = thisModalContainer.childNodes[0];
            var editButton = document.createElement('div');
            editButton.className = "modal-edit-button";
            editButton.textContent = "✎ EDIT";
            thisModalContent.appendChild(editButton);

            editButton.addEventListener("click", function() {
              thisModalContainer.style.display = "none";
              loadModalFrame(tile, true);
              getTileContents(tile.dataset.tileId, "#editModalCont" + tile.dataset.tileId, true);
            });
          }
          contentLoaded = true;
        }
        getTileContents(tile.dataset.tileId, "#modalCont" + tile.dataset.tileId, false);
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
      modalContainer.id = "modalContainer" + (isEdit ? "Edit" : "") + tile.dataset.tileId;
      var modalContent = document.createElement('div');
      modalContent.id = (isEdit ? "editModalCont" : "modalCont") + tile.dataset.tileId;
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

      if (!isEdit) {
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
        weeklyQuest.id = "wq" + tile.dataset.tileId;
        weeklyQuestContainer.appendChild(weeklyQuest);
      }
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
                if (<?php echo $userRole; ?> == 2){
                  assHolder.append($("#assTeacherContent").show());
                } else{
                  assHolder.append($("#assContent").show());
                }
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
      let date = data.postDate;
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
      let condensedCompHolder = $("<div>").addClass("dragCompHolder").attr("id", "dragCompHolder" + data.id).data("initial", data.order);
      let condensedComp = $("<div>").addClass("dragComp").attr("id", "dragComp" + data.id).data("id", data.id);
      condensedCompHolder.append(condensedComp);
      condensedCompHolder.on("mouseenter", function(event) {
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
        offset = {
          x: event.pageX - startPos.left,
          y: event.pageY - startPos.top
        };
        modalScroll = activeComp.parent().parent().parent().parent();
        activeComp.addClass('compDragging');
        dragging = true;
      });

      console.log("HUH");
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
      $("#dragComp" + id).toggle();
      $("#editComp" + id).toggle();
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
      holder.append($("<div id='compDragArea'>"));
      if (!componentsArray) {
        return;
      }
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
          let draggedComp = $("#dragCompHolder" + component.compId);
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
    <?php } ?>
  </script>
</body>

</html>
