<?php // Page responsible for displaying an individual unit and it's content //
include("php/session.php");
include("php/dbConnect.php");

// Get user's role:
$sql = "SELECT role FROM user WHERE id = ?";
$stmt = $dbh->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userRole = $result->fetch_assoc();

// Get unit record:
$sql = "SELECT id, code, name, description FROM unit WHERE EXISTS (SELECT 1 FROM unitUser WHERE unitId = ? AND userId = $userId) AND ID = ? LIMIT 1;";
$stmt = $dbh->prepare($sql);
$stmt->bind_param("ii", $_GET['id'], $_GET['id']);
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
            $sql = "SELECT id, icon, name, label, description FROM tile WHERE unitId = ? ORDER BY `order` ASC;";
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
          loadModalFrame(tile, false);

          //Add edit button for lecturer
          if(<?php echo $userRole['role']; ?> == 2 ){
            const thisModalContainer = document.querySelector("#modalContainer" + tile.id + ".modal");
            const thisModalContent = thisModalContainer.childNodes[0];
            var editButton = document.createElement('div');
            editButton.className = "modal-edit-button";
            editButton.textContent = "✎ EDIT";
            thisModalContent.appendChild(editButton);

            editButton.addEventListener("click", function() {
              thisModalContainer.style.display = "none";
<<<<<<< Updated upstream
              loadModalFrame(tile, true)
              getTileContents(tile.id, "#editModalCont" + tile.id, true);
=======
              loadModalFrame(tile, true);
              getTileContents(tile.id, "#editModalCont" + tile.id, true);  
>>>>>>> Stashed changes
            })
          }
          contentLoaded = true;
        }
        getTileContents(tile.id, "#modalCont" + tile.id, false);
      })
    });

    function loadModalFrame(tile, isEdit) {
      var modalContainer = document.createElement('div');
            modalContainer.className = "modal";
            modalContainer.id = "modalContainer" + tile.id;
          var modalContent = document.createElement('div');
            if(isEdit){
              modalContent.id = "editModalCont" + tile.id;
            } else {
              modalContent.id = "modalCont" + tile.id;
            }
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

          modalContainer.style.display = "block";
          //close modal functions
          if(isEdit){
            closeButton.onclick = function() {
            modalContainer.innerHTML = '';
            modalContainer.remove();
            var originalModal = document.querySelector("#modalContainer" + tile.id);
            originalModal.style.display = "block";
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

    // fetch tile component & contents:
    function getTileContents(id, parent, isEdit) {
      var formData = new FormData();
      formData.append("tileId", id);
      var promise = postAJAX("php/tiles/loadTileContent.php", formData);
      promise.then(function(data) {
        if(isEdit){
          unpackTileJSONEdit(data, parent);
        } else {
          unpackTileJSON(data, parent);
        }
      }).catch(function(error) {
        console.error('Error:', error); // !!! better solution
      });
    }

    // constructs the tile's components & content from JSON objects:
    function unpackTileJSON(data, parent) {
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
          let taskBtn = $("<button>").addClass("modal-content-task").attr("id", "task" + ele.id);
          taskBtn.attr("onclick", "requestTaskToggle(" + ele.id + ");");
          if (ele.isComplete) {
            taskBtn.html("✓");
            taskBtn.addClass("completed");
          }
          contentHolder.append(taskBtn);
        }

        if (ele.type == 2) {
          content.attr('href', 'files/<?php echo $unit['id']; ?>/content/' + ele.url);
          content.attr('download', ele.url);
        } else if (ele.type == 3) {
          content.attr('href', "https://" + ele.url); // !!! this should be more adaptable
          content.attr('target', '_blank');
        }
        $("#compContent" + ele.componentId).append(contentHolder);
      });
    }

    // Task ticking & unticking:
    function toggleTask(id) {
      let task = $("#task" + id);
      task.prop("disabled", true);
      if (task.html() == "") {
        task.html("✓");
        task.addClass("completed");
        return true;
      } else if (task.html() == "✓"){
        task.html("");
        task.removeClass("completed");
        return false;
      }
    }
    function requestTaskToggle(id) {
      let state = toggleTask(id);
      var formData = new FormData();
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

    //Load modal content and components in edit mode
    function unpackTileJSONEdit(data, parent) {
      var holder = $(parent);
      let buttonHolder = $("<div>").addClass("save-cancel-btn-container");
      buttonHolder.append($("<div>").addClass("save-cancel-btn").html("Save"));
      buttonHolder.append($("<div>").addClass("save-cancel-btn").html("Cancel"));
      holder.append(buttonHolder);
      var componentsArray = JSON.parse(data.components);
      if (!componentsArray) {
        return;
      }
      componentsArray.forEach(function(ele) {
        let component = $("<div>").addClass("modal-component edit-field").attr("id", "comp" + ele.id).attr("contenteditable", true);
        holder.append(component);
        let componentHead = $("<div>").addClass("component-head");
        componentHead.append($("<div>").addClass("modal-component-title").html(ele.name));
        componentHead.append($("<div>").addClass("edit-modal-delete-component").html("Delete").attr("contenteditable", false));
        component.append(componentHead);
        component.append($("<div>").addClass("modal-component-description").html(ele.description));
        component.append($("<div>").addClass("modal-inner-content").attr("id", "editCompContent" + ele.id));
        component.append($("<div>").addClass("add-content-btn").attr("id", "add-content-btn" + ele.id).html("+"));
        component.append($("<div>").addClass("add-content-btn-label").html("Add Content"));
      });

      var contentArray = JSON.parse(data.content);
      if (!contentArray) {
        return;
      }
      contentArray.forEach(function(ele) {
        let contentHolder = $("<div>").attr("id", "content" + ele.id).addClass("edit-content-holder");

        let typeRow = $("<div>").addClass("edit-content-row-type");
        typeRow.append($("<div>").addClass("edit-content-label").html("Type:"))
        var typeSelect = $("<select id=\"componentTypeId\" name=\"componentType\" />").addClass("edit-content-field");
          $("<option />", {value: "1", text: "Ordered list"}).appendTo(typeSelect);
          $("<option />", {value: "2", text: "Unordered list"}).appendTo(typeSelect);
          $("<option />", {value: "3", text: "Link"}).appendTo(typeSelect);
        typeSelect.val(ele.type);
        typeRow.append(typeSelect);
        typeRow.append($("<img>").attr("src", "assets/deleteIcon.svg").attr("alt", "Delete").addClass("edit-content-delete-icon"));
        contentHolder.append(typeRow);

        let textRow = $("<div>").addClass("edit-content-row");
        textRow.append($("<div>").addClass("edit-content-label").html("Text:"));
        textRow.append($("<div>").addClass("edit-content-text").html(ele.name));
        contentHolder.append(textRow);

        let urlRow = $("<div>").addClass("edit-content-row-url");
        urlRow.append($("<div>").addClass("edit-content-label").html("url:"));
        let urlCheckbox = $("<button>").addClass("modal-content-task");
        urlRow.append(urlCheckbox);
        urlRow.append($("<div>").addClass("edit-content-url").html(ele.url));
        if(!ele.url == ""){
          urlCheckbox.html("✓");
          urlCheckbox.addClass("completed");
        }
        contentHolder.append(urlRow);

        let taskRow = $("<div>").addClass("edit-content-row");
        taskRow.append($("<div>").addClass("edit-content-label").html("Assign as task:"));
        let taskCheckbox = $("<button>").addClass("modal-content-task")
        taskRow.append(taskCheckbox);
        if(ele.isTask == 1){
          taskCheckbox.html("✓");
          taskCheckbox.addClass("completed");
        }
        contentHolder.append(taskRow);

        $("#editCompContent" + ele.componentId).append(contentHolder);
      });

      }
  </script>
</body>
</html>
