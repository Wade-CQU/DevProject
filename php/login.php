<?php

    $data = ["Jack","password"];

    $username = $_POST['username'];
    $password = $_POST['password'];

    if($username == $data[0] && $password == $data[1]){
        header("Location: /devproject/unit.php");
    }
    else {
        header("Location: /devproject/login.html");
    }

?>