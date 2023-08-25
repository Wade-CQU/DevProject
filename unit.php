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
            $stmt->close();

            if (!$result) { // if query or database connection fails:
              echo "404 Unit Not Found"; // !!! review?
              $stmt->close();
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

              $sql = "SELECT COUNT(id) FROM taskcompletion where tileId=? and isComplete = 1;";
              $stmt = $dbh->prepare($sql);
              $stmt->bind_param("i", $tile['id']);
              $stmt->execute();
              $stmt->bind_result($completedTaskCount); // Bind the result to the $count variable
              $stmt->fetch(); // Fetch the value
              $stmt->close();

              //get percentage of task completion
              if($taskCount == 0) {
                $xpPercentage = 100;
              } else {
                $xpPercentage = ($completedTaskCount / $taskCount) * 100;
                $xpPercentage = floor($xpPercentage);
              }

              ?>
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
                      <div class="unitTileXpProgress" style="width:<?php echo $xpPercentage; ?>%;">

                      </div>
                    </div>
                  </div>
                </div>
                <div class="unitTileDescription">
                  <?php echo $tile['description']; ?>
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
          if(<?php echo $userRole['role']; ?> == 2 ){ // perform role management in session.php and never send these functions to the students !!!
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
          weeklyQuest.textContent = "Do some stuff and learn some thing.";
          weeklyQuestContainer.appendChild(weeklyQuest);

          modalContainer.style.display = "block";
          //close modal functions
          if(isEdit){
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

    // fetch tile component & contents:
    function getTileContents(id, parent, isEdit) {
      var formData = new FormData();
      formData.append("tileId", id);
      var promise = postAJAX("php/tiles/loadTileContent.php", formData);
      promise.then(function(data) {
        if(isEdit){
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
          taskBtn.attr("onclick", "requestTaskToggle(" + ele.id + ","+tileId+");");
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
    function createEditableComponent(holder, data = null) {
      let di = data == null; // used to differentiate new component's values when applicable.
      if (di) {
        data = {
          id: currentCompId,
          name: "",
          description: ""
        };
        currentCompId--;
      }
      let component = $("<div>").addClass("modal-component edit-field").attr("id", "editComp" + data.id);
      holder.append(component);
      let componentHead = $("<div>").addClass("component-head");
      componentHead.append($("<input type='text'>").addClass("modal-component-title").val(data.name).data("initial", !di ? data.name : "𓁔𓃸").prop("placeholder", "Enter a heading here..."));
      componentHead.append($("<div>").addClass("edit-modal-delete-component").html("Delete").attr("onclick", "deleteComponent("+ data.id +");"));
      component.append(componentHead);
      component.append($("<textarea>").addClass("modal-component-description").val(data.description).data("initial", !di ? data.description : "𓁔𓃸").prop("placeholder", "Write a description here..."));
      component.append($("<div>").addClass("modal-inner-content").attr("id", "editCompContent" + data.id));
      let addBtnHolder = $("<div>").on("click", function () {
        createEditableContent(null, data.id);
      });
      component.append(addBtnHolder);
      addBtnHolder.append($("<div>").addClass("add-content-btn").attr("id", "add-content-btn" + data.id).html("+"));
      addBtnHolder.append($("<div>").addClass("add-content-btn-label").html("Add Content"));
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
          componentId: compId
        };
        currentCompId--;
      }

      let contentHolder = $("<div>").attr("id", "editContent" + ele.id).addClass("edit-content-holder");
      let typeRow = $("<div>").addClass("edit-content-row-type");
      typeRow.append($("<div>").addClass("edit-content-label").html("Type:"));
      var typeSelect = $("<select>").addClass("edit-content-field");
        $("<option />", {value: "0", text: "Dot Point"}).appendTo(typeSelect);
        $("<option />", {value: "1", text: "Numbered Point"}).appendTo(typeSelect);
        $("<option />", {value: "2", text: "Download Link"}).appendTo(typeSelect);
        $("<option />", {value: "3", text: "Clickable Link"}).appendTo(typeSelect);
      typeSelect.val(ele.type).data("initial", !di ? ele.type : "𓁔𓃸");
      typeRow.append(typeSelect);
      typeRow.append($("<img>").attr("src", "assets/deleteIcon.svg").attr("alt", "Delete").addClass("edit-content-delete-icon").attr("onclick", "deleteContent("+ ele.id +");"));
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
      if(ele.isTask == 1){
        taskCheckbox.attr("checked", "true");
      }
      contentHolder.append(taskRow);

      $("#editCompContent" + ele.componentId).append(contentHolder);
    }
    //Load modal content and components in edit mode
    function unpackTileJSONEdit(data, parent, tileId) {
      var holder = $(parent);
      let buttonHolder = $("<div>").addClass("save-cancel-btn-container");
      buttonHolder.append($("<div>").addClass("save-cancel-btn").html("Save").attr("onclick", "saveTile("+ tileId +");"));
      buttonHolder.append($("<div>").addClass("save-cancel-btn").html("Cancel").attr("onclick", "$('#modalContainerEdit"+ tileId +"').remove(); document.querySelector('#modalContainer"+tileId+"').style.display = 'block';"));
      buttonHolder.append($("<div>").addClass("save-cancel-btn").html("Add Component").attr("onclick", "createEditableComponent($('#editModalCont"+ tileId +"'));").css("float", "right"));
      holder.append(buttonHolder);
      var componentsArray = JSON.parse(data.components);
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

    <?php if ($userRole['role'] == 2) { // lecturer only functions: ?>
      function deleteComponent(compId) {
        if (!confirm("Are you sure you want to delete this component? All its associated content will be deleted with it.")) {
          return;
        }

        if (compId < 0) { // if a newly client-created component, delete from client-side:
            $("#editComp" + compId).remove();
            return;
        }
        var formData = new FormData();
        formData.append("componentId", compId);
        var promise = postAJAX("php/tiles/deleteComponent.php", formData);
        promise.then(function(data) {
          $("#editComp" + compId).remove();
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
          document.querySelector('#modalContainer'+tileId).style.display = 'block';
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
