<?php
    include("session.php");
    include("dbConnect.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/default.css" rel="stylesheet" />
    <title>Class Info</title>
</head>
<body>
        <div class="centre">
        <?php // Get unit details
            $id = $_GET['id'];
            $sql = "SELECT id, code, name, description FROM unit WHERE id = ?";
            $stmt = $dbh->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if (!$result) { // if query or database connection fails:
                echo "404 Unit Not Founddd";
                $stmt->close();
                $dbh->close();
                exit;
            }
            while($unit = $result->fetch_assoc()) { ?>
                <h1>Class Info</h1>
                <h1><?php echo  $unit["code"]. "   " . $unit["name"]; ?></h1>
                <h2><?php echo  $unit["description"]; ?></h2>
          <?php } ?>
            </div>

        <div class="centre">
            <h1>Participants:</h1>
            <?php // Get user's based on unit:
                $sql = "SELECT uId, firstName, lastName, role, email FROM user u RIGHT JOIN (SELECT uu.userId as uId FROM unitUser uu WHERE unitId = $id) uu ON uId = u.id ORDER BY role DESC";
                $stmt = $dbh->prepare($sql);
                $stmt->execute();
                $result = $stmt->get_result();

                if (!$result) { // if query or database connection fails:
                    echo "404 Unit Not Found"; 
                    $stmt->close();
                    $dbh->close();
                    exit;
                }
                ?>
            <table class="centre-allign">
                <tr>
                    <th>
                        Name
                    </th>
                    <th>
                        Email
                    </th>
                    <th>
                        Role
                    </th>
                </tr>
                <?php
                while ($user = $result->fetch_assoc()) {
                ?>
            
                <tr>
                    <th>
                        <?php echo $user['firstName']; ?> <?php echo $user['lastName']; ?>
                    </th>
                    <th>
                        <?php echo $user['email']; ?>
                    </th>
                    <th>
                        <?php if ($user['role'] == 2) {
                            echo 'Teacher';
                        }else{
                            echo 'Student';
                        } ?>
                    </th>
                </tr>
            
                <?php }
                $stmt->close();
                $dbh->close();
                ?>
            </table>
        </div>

    </body>

</html>


<style>
.centre{
  margin: auto;
  width: 50%;
  border: 3px solid orange;
  padding: 10px;
}

.centre-allign{
  margin: auto;
  width: 90%;
}

table, th, td {
  border:1px solid black;
}

th{
    width: 200px;
}
</style>