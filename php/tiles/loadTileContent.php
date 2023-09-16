<?php
require("../session.php");
require("../dbConnect.php");
header('Content-Type: application/json');
if (!isset($_POST['tileId'])) { // !!! more & sessions
  exit;
}

// if cache code matches, load user's cache unit data: !!!
if (isset($_POST['userCache'])) {

}

// Get components:
$sql = "SELECT `id`, `name`, `icon`, `description`, `order` FROM component WHERE tileId = ? ORDER BY `order` ASC;";
$stmt = $dbh->prepare($sql);
$stmt->bind_param("i", $_POST['tileId']);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
  trigger_error("COMPONENT ACQUISITION FAILED", E_USER_ERROR);
  $stmt->close();
  $dbh->close();
  exit;
} else if ($result->num_rows == 0) {
  echo json_encode(array(
    "components" => 0
  ));
  exit;
}

// store & format component results:
$components = array();
$compIds = "";
while ($comp = $result->fetch_assoc()) {
  array_push($components, $comp);
  $compIds .= ($compIds == "" ? "" : ",") . $comp['id'];
}
$components = json_encode($components);

// Get content for components:
$sql = "SELECT JSON_ARRAYAGG(
          JSON_OBJECT(
            'id', `id`,
            'componentId', `componentId`,
            'type', `type`,
            'name', `name`,
            'url', `url`,
            'order', `order`,
            'isTask', `isTask`,
            'isComplete', `isComplete`
          )
        ) AS json_data FROM content c LEFT JOIN (SELECT isComplete, contentId FROM taskCompletion WHERE userId = $userId) t ON c.id = t.contentId WHERE componentId IN ($compIds) ORDER BY `order` ASC;";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

// Format data and send back to client:
$response = array(
  "components" => $components
);
if ($result) {
  if ($content = $result->fetch_assoc()) {
    $response["content"] = $content['json_data'];
  } else {
    $response["content"] = json_encode([]); // include empty array if no content.
  }
}
echo json_encode($response);
$stmt->close();
$dbh->close();
?>
