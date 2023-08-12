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
    <link href="css/term.css" rel="stylesheet" />
    <title></title>
</head>
<body>
    <?php require("php/header.php"); ?>
    <h1>WELCOME TO TERM 1, WEEK 1</h1>


    <div class="side-scroll-container">

    <?php
            // Get unit's (!!! if not cached):
            $sql = "SELECT * FROM unit";
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
                $fakeRank = rand(1, 4); ?>
              <div class="unit-card" id="<?php echo $unit['id']; ?>">
                <div class="icon unit-icon-container"></div>
                <div class="xp-container"></div>
                <img class="rank-icon-container" src="assets/<?php echo $fakeRank; ?>.svg"/>
                <div class="unit-title"><?php echo $unit['name']; ?></div>
                <div class="rank-highlight rank-<?php echo $fakeRank; ?>"></div>
            </div>
          <?php }
            $stmt->close();
            $dbh->close();
           ?>
        
    </div>

    <script>
        //select all the tiles
        const cards = document.querySelectorAll(".unit-card");
        //!!! change this later        
        cards.forEach(card => {
            card.addEventListener("click", function(){
                window.location.href = "unit.php?id=" + card.id;
            });
        });
    </script>
</body>
</html>
