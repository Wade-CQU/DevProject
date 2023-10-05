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
    <link href="css/profilePage.css?d=<?php echo time(); ?>" rel="stylesheet" />
    <title>View Profile</title>
    <?php if (isset($_COOKIE['lightTheme'])) { ?>
        <link rel="stylesheet" href="css/cringeTheme.css">
    <?php } ?>
</head>
<body>
    <?php require("php/header.php"); ?>
    <div class="split left">
        <div class="centered">
            <?php // Get user based on userid:
                $sql = "SELECT id, firstName, lastName, email FROM user WHERE id = $userId";
                $stmt = $dbh->prepare($sql);
                $stmt->execute();
                $result = $stmt->get_result();
                if (!$result) { // if query or database connection fails:
                    echo "404 Unit Not Found";
                    $stmt->close();
                    $dbh->close();
                    exit;
                }
                while($user = $result->fetch_assoc()) { ?>
                  <img src="<?php echo $pfp; ?>" alt="Profile Icon">
                  <h1><?php echo  $user["firstName"]. "   " . $user["lastName"]; ?></h1>
                  <h2><?php echo  $user["email"]; ?></h2>
          <?php } ?>
        </div>
    </div>
    <div class="split right">
        <div class="ranks">
            <h1>Class Ranks:</h1>
            <div class="list-container">
                <?php // Get unit's based on user:
                    $sql = "SELECT uId, `name`, termCode FROM unit u RIGHT JOIN (SELECT uu.unitId as uId FROM unitUser uu WHERE userId = $userId) uu ON uId = u.id ORDER BY termCode DESC";
                    $stmt = $dbh->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if (!$result) { // if query or database connection fails:
                        echo "404 Unit Not Found"; 
                        $stmt->close();
                        $dbh->close();
                        exit;
                    }
                    while ($unit = $result->fetch_assoc()) {
                        //Get total nbr of tasks in unit
                        $sql = "SELECT SUM(totalTasks) FROM tile where unitId=?;";
                        $stmt = $dbh->prepare($sql);
                        $stmt->bind_param("i", $unit['uId']);
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
                        $stmt->bind_param("ii", $unit['uId'], $userId);
                        $stmt->execute();
                        $stmt->bind_result($unitTaskCompleted);
                        $stmt->fetch();
                        $stmt->close();
                        //calculate total unit xp percentage for current user
                        if ($unitTaskCount == 0) {
                          $unitXpPercentage = 0;
                        } else {
                          $unitXpPercentage = ($unitTaskCompleted / $unitTaskCount) * 100;
                          $unitXpPercentage = floor($unitXpPercentage);
                        }
                        //assign rank
                        if($unitXpPercentage < 25){
                          $rank = 1;
                        } else if($unitXpPercentage < 50){
                          $rank = 2;
                        } else if($unitXpPercentage < 75){
                          $rank = 3;
                        } else {
                          $rank = 4;
                        }
                        ?>
                    <div class="list-unit-card" id="<?php echo $unit['uId']; ?>"<?php echo $unit['termCode'] != $termCode ? "style='display: none;'" : ""; ?>>
                        <div class="profile-title"><?php echo substr($unit['name'], 0, 32); ?></div>
                        <img class="rank-icon-container" src="assets/<?php echo $rank; ?>.svg"/>
                    </div>
                  <?php }

                    $dbh->close();
                  ?>
            </div>
        </div>
    </div>
</body>
</html>
