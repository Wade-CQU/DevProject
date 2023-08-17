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
    <title></title>
</head>
<body>
    <?php require("php/header.php"); ?>

    <div class="split left">
        <div class="centered">
            <?php
                // This one I made myself. Didn't steal.
                // Get user based on userid:
                $sql = "SELECT id, firstName, lastName, email FROM user WHERE id = $userId";
                $stmt = $dbh->prepare($sql);
                $stmt->execute();
                $result = $stmt->get_result();

                if (!$result) { // if query or database connection fails:
                    echo "404 Unit Not Found"; // !!! review?
                    $stmt->close();
                    $dbh->close();
                    exit;
                }

                    while($user = $result->fetch_assoc()) {

                    ?>
                    <img src="https://cdn-icons-png.flaticon.com/512/64/64572.png" alt="Profile Icon">
                    <h1><?php echo  $user["firstName"]. "   " . $user["lastName"]; ?></h1>
                    <h2><?php echo  $user["email"]; ?></h2>
            <?php 
                    }
            ?>
        </div>
    </div>

    <div class="split right">
        <div class="ranks">
            <h1>Ranks:</h1>
            <div class="list-container">
                <?php
                    // Stole this from term.php with minor modifications. please help
                    // Get unit's based on user:
                    $sql = "SELECT uId, name, termCode FROM unit u RIGHT JOIN (SELECT uu.unitId as uId FROM unitUser uu WHERE userId = $userId) uu ON uId = u.id ORDER BY termCode DESC";
                    $stmt = $dbh->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if (!$result) { // if query or database connection fails:
                        echo "404 Unit Not Found"; // !!! review?
                        $stmt->close();
                        $dbh->close();
                        exit;
                    }
                    while ($unit = $result->fetch_assoc()) {
                        $fakeRank = rand(1, 4);
                ?>
                <div class="list-unit-card" id="<?php echo $unit['uId']; ?>"<?php echo $unit['termCode'] != $termCode ? "style='display: none;'" : ""; ?>>
                    <div class="unit-title"><?php echo $unit['name']; ?></div>
                    <img class="rank-icon-container" src="assets/<?php echo $fakeRank; ?>.svg"/>
                    <div class="rank-highlight rank-<?php echo $fakeRank; ?>"></div>
                </div>
                <?php }
                    $stmt->close();
                    $dbh->close();
                ?>
            </div>
        </div>
    </div>



</body>
</html>