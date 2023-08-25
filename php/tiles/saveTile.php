<?php
require("../session.php");
require("../dbConnect.php");
header('Content-Type: application/json');
if (!isset($_POST['tileUpdateJSON'])) { // !!! session checks
  exit;
}
// Decode the data:
$data = json_decode($_POST['tileUpdateJSON']);
$dataToInsert = array(array(), array(), array()); // [0] components to insert, [1] content to insert into existing components, [2] content to insert into new components.

// update all existing components:
if (count($data[0]) > 0) {
  // prepare component data:
  $compNameSQL = "";
  $compDescSQL = "";
  foreach ($data[0] as $component) {
    $compId = intval($component->compId);
    if ($compId < 0) { // insert new components instead of updating:
      array_push($dataToInsert[0], $component);
      continue;
    } else if ($compId == 0) {
      continue; // skip all components with an invalid Id.
    }
    if (isset($component->name)) {
      $compNameSQL .= ($compNameSQL == "" ? "name = CASE" : "") . " WHEN id = " . $compId . " THEN '" . str_replace("'", "\'", $component->name) . "'";
      $runQuery = true;
    }
    if (isset($component->description)) {
      $compDescSQL .= ($compDescSQL == "" ? "description = CASE" : "") . " WHEN id = " . $compId . " THEN '" . str_replace("'", "\'", $component->description) . "'";
      $runQuery = true;
    }
  }
  if (isset($runQuery)) { // construct & run query if any data needs updating:
    $sql = "UPDATE component SET " . ($compNameSQL == "" ? "" : $compNameSQL . " ELSE name END") . ($compDescSQL == "" ? "" : ($compNameSQL == "" ? " " : ", ") . $compDescSQL . " ELSE description END") . ";";
    $stmt = $dbh->prepare($sql);
    $result = $stmt->execute();

    if (!$result) {
      $stmt->close();
      $dbh->close();
      exit;
    }
    $stmt->close();
    unset($runQuery);
  }
}

// update all content:
$attr = array("", "", "", "");
if (count($data[1]) > 0) {
  // prepare content data:
  foreach ($data[1] as $content) {
    $contId = intval($content->contId);
    if ($contId < 0) { // insert new content instead of updating:
      $arrayId = $content->componentId > 0 ? 1 : 2; // separate by parenting component's existence.
      if (!isset($dataToInsert[$arrayId][$content->componentId])) {
        $dataToInsert[$arrayId][$content->componentId] = array(); // if array doesn't exist, make one.
      } // put content into it's respective array based on its component Id:
      array_push($dataToInsert[$arrayId][$content->componentId], $content);
      continue;
    } else if ($contId == 0) {
      continue; // skip all content with an invalid Id.
    }
    if (isset($content->name)) {
      $attr[0] .= ($attr[0] == "" ? "name = CASE" : "") . " WHEN id = " . $contId . " THEN '" . str_replace("'", "\'", $content->name) . "'";
      $runQuery = true;
    }
    if (isset($content->url)) {
      $attr[1] .= ($attr[1] == "" ? "url = CASE" : "") . " WHEN id = " . $contId . " THEN '" . str_replace("'", "\'", $content->url) . "'";
      $runQuery = true;
    }
    if (isset($content->status)) {
      $attr[2] .= ($attr[2] == "" ? "isTask = CASE" : "") . " WHEN id = " . $contId . " THEN " . intval($content->status);
      $runQuery = true;
    }
    if (isset($content->type)) {
      $attr[3] .= ($attr[3] == "" ? "type = CASE" : "") . " WHEN id = " . $contId . " THEN " . intval($content->type);
      $runQuery = true;
    }
  }
  if (isset($runQuery)) { // only update if there are updates to be made:
    // comma management:
    if ($attr[0] != "") {
      $attr[0] .= " ELSE name END";
    } if ($attr[1] != "") {
      $attr[1] .= " ELSE url END";
    } if ($attr[2] != "") {
      $attr[2] .= " ELSE isTask END";
    } if ($attr[3] != "") {
      $attr[3] .= " ELSE type END";
    }
    $attributeSQL = implode(', ', array_filter(array_map('trim', $attr)));

    // construct & run query:
    $sql = "UPDATE content SET " . $attributeSQL . ";";
    $stmt = $dbh->prepare($sql);
    $result = $stmt->execute();

    if (!$result) {
      $stmt->close();
      $dbh->close();
      exit;
    }
    $stmt->close();
    unset($runQuery);
  }
}

// Inserting all new components & their new content:
foreach ($dataToInsert[0] as $component) {
  $sql = "INSERT INTO component (`tileId`, `name`, `icon`, `description`, `order`) VALUES (?,?,6,?,69);"; // !!! Re-evaluate the ordering once the drag & dropping is done. !!! icon not implemented either
  $stmt = $dbh->prepare($sql);
  $stmt->bind_param("iss", $component->tileId, $component->name, $component->description);
  $stmt->execute();

  // Insert new content in new component if applicable:
  if (array_key_exists($component->compId, $dataToInsert[2])) {
    $newCompId = $dbh->insert_id;
    $sql = "INSERT INTO content (`componentId`, `type`, `name`, `url`, `order`, `isTask`) VALUES ";
    $inserts = array();
    foreach ($dataToInsert[2][$component->compId] as $content) {
      array_push($inserts, "($newCompId,". intval($content->type) . ",'". str_replace("'", "\'", $content->name) ."','". str_replace("'", "\'", $content->url) ."',69,". intval($content->status) .")"); // !!! add string fixers
    }
    if (count($inserts) > 0) {
      $attributeSQL = implode(', ', array_filter(array_map('trim', $inserts))) . ";";
      $stmt = $dbh->prepare($sql . $attributeSQL);
      $stmt->execute();
    }
  }
}
// Inserting all new content into their existing components:
$sql = "INSERT INTO content (`componentId`, `type`, `name`, `url`, `order`, `isTask`) VALUES ";
$inserts = array();
foreach ($dataToInsert[1] as $compId => $contentArray) {
  foreach ($contentArray as $content) {
    array_push($inserts, "(". intval($compId) .",". intval($content->type) . ",'". str_replace("'", "\'", $content->name) ."','". str_replace("'", "\'", $content->url) ."',69,". intval($content->status) .")"); // !!! add string fixers
  }
}
if (count($inserts) > 0) {
  $attributeSQL = implode(', ', array_filter(array_map('trim', $inserts))) . ";";
  $stmt = $dbh->prepare($sql . $attributeSQL);
  $stmt->execute();
}

$return = array(); // Create an empty associative array to confirm success.
echo json_encode($return);
$dbh->close();
?>
