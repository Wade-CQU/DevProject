<?php
require("../session.php");
require("../dbConnect.php");
header('Content-Type: application/json');
if (!isset($_POST['tileUpdateJSON'])) { // !!! session checks
  exit;
}

// Decode the data:
$data = json_decode($_POST['tileUpdateJSON']);

// foreach ($data as $table) {
//     foreach ($table as $column) {
//         echo "Name: " . $item->name . ", Value: " . $item->value . "\n";
//     }
// }


// update all components:
if (count($data[0]) > 0) {
  // prepare component data:
  $compNameSQL = "";
  $compDescSQL = "";
  foreach ($data[0] as $component) {
    $compId = intval($component->compId);
    if (isset($component->name)) {
      $compNameSQL .= ($compNameSQL == "" ? "name = CASE" : "") . " WHEN id = " . $compId . " THEN '" . str_replace("'", "\'", $component->name) . "'";
    }
    if (isset($component->description)) {
      $compDescSQL .= ($compDescSQL == "" ? "description = CASE" : "") . " WHEN id = " . $compId . " THEN '" . str_replace("'", "\'", $component->description) . "'";
    }
  }

  // construct & run query:
  $sql = "UPDATE component SET " . ($compNameSQL == "" ? "" : $compNameSQL . " ELSE name END") . ($compDescSQL == "" ? "" : ($compNameSQL == "" ? " " : ", ") . $compDescSQL . " ELSE description END") . ";";
  $stmt = $dbh->prepare($sql);
  $result = $stmt->execute();

  if (!$result) {
    $stmt->close();
    $dbh->close();
    exit;
  }
  $stmt->close();
}

// update all content:
$attr = array("", "", "", "");
if (count($data[1]) > 0) {
  // prepare content data:
  foreach ($data[1] as $content) {
    $contId = intval($content->contId);
    if (isset($content->name)) {
      $attr[0] .= ($attr[0] == "" ? "name = CASE" : "") . " WHEN id = " . $contId . " THEN '" . str_replace("'", "\'", $content->name) . "'";
    }
    if (isset($content->url)) {
      $attr[1] .= ($attr[1] == "" ? "url = CASE" : "") . " WHEN id = " . $contId . " THEN '" . str_replace("'", "\'", $content->url) . "'";
    }
    if (isset($content->status)) {
      $attr[2] .= ($attr[2] == "" ? "isTask = CASE" : "") . " WHEN id = " . $contId . " THEN '" . str_replace("'", "\'", $content->status) . "'";
    }
    if (isset($content->type)) {
      $attr[3] .= ($attr[3] == "" ? "type = CASE" : "") . " WHEN id = " . $contId . " THEN '" . str_replace("'", "\'", $content->type) . "'";
    }
  } // comma management:
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
}
$return = array(); // Create an empty associative array to confirm success.
echo json_encode($return);
$dbh->close();
?>
