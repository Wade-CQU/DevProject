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
            <h1>Time Table:</h1>
            <?php // Get timetable based on unit
                $unitId = $_GET['unitId'];
                $sql = "SELECT unitId, classTime, link, details FROM timetable WHERE unitId = ?";
                $stmt = $dbh->prepare($sql);
                $stmt->bind_param("i", $unitId);
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
                        Time
                    </th>
                    <th>
                        Link
                    </th>
                    <th>
                        Details
                    </th>
                </tr>
                <?php
                while ($timetable = $result->fetch_assoc()) {
                ?>
            
                <tr>
                    <th>
                        <?php echo $timetable['classTime']; ?>
                    </th>
                    <th>
                        <a href="https://www.<?php echo $timetable['link']; ?>"><?php echo $timetable['link']; ?></a>
                    </th>
                    <th>
                        <?php echo $timetable['details']; ":"?>
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
a:link {
  text-decoration: none;
}

</style>