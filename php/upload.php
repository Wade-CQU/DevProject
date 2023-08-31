<?php


//TODO Upload multiple files

include("session.php");
include("dbConnect.php");

$id = 2;
$dir = "uploads/";
$target_dir = "uploads/$id/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
// Check if image file is a actual image or fake image

if (is_dir($dir) == false){
    mkdir("uploads", 0777);
    if (is_dir($target_dir) == false){
        mkdir("uploads\\$id", 0777);
    }
} else if (is_dir($target_dir) == false){
    mkdir("uploads\\$id", 0777);
} else{
    echo "dir already exists, no need to make it.\n";
}

if(isset($_POST["submit"])) {
  $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
  if($check !== false) {
    echo "File is an image - " . $check["mime"] . ".";
    $uploadOk = 1;
  } else {
    echo "File is not an image.";
    $uploadOk = 0;
  }
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
  // if everything is ok, try to upload file
  } else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
      echo "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.\n";
    } else {
      echo "Sorry, there was an error uploading your file.\n";
    }
  }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="/devproject/css/default.css" rel="stylesheet" />
        <link href="/devproject/css/term.css" rel="stylesheet" />
        <title></title>
    </head>
    <body>
        <?php require("header.php"); ?>
    </body>
</html>
