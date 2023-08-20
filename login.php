<?php
include("php/dbConnect.php");

//Initiazlize the session
session_start();

//Check user is already logged in, if yes then redirect them to welcome page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: term.php");
    exit;
}

//Define variables and initalize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

//Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    //Check username empty
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter Username.";
    }else{
        $username = trim($_POST["username"]);
    }

    //Check password empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter Password.";
    }else{
        $password = trim($_POST["password"]);
    }

    //Validation
    if(empty($username_err) && empty($password_err)){
        //SQL statement
        $sql = "SELECT id, email, password FROM user WHERE email = ?";
        if($stmt = mysqli_prepare($dbh, $sql)){
            //Bind variables to the prepared statement
            mysqli_stmt_bind_param($stmt, "s", $username);

            //Set Paramerters
            $param_username = $username;

            //Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                //Store Result
                mysqli_stmt_store_result($stmt);
                echo "Stuff got " . $username . " " . $password . " ---";
                //Check if username exists, if yes then verify password
                if(mysqli_stmt_num_rows($stmt) == 1){
                    //BIND RESULT


                    echo "Stuff got 2 " . $username . " " . $password . " ---";


                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){

                        echo "Stuff got 3 " . $username . " " . $password . " ---";

                        if(password_verify($password, $hashed_password)){
                            //password is correct, so start a new session
                            session_start();
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;

                            //redirect user to welcome page
                            header("location: term.php");
                        } else{
                            //Password is net valid, display a generic error message
                            $login_err = "Passwordo no goodo\n";
                        }
                    } 
                } else {
                    //Username doesn't exist, display a generic error message
                    $login_err = "invalid username or password";
                }
            } else {
                echo "Opps! something went poo poo. Try again later, chum.";
            }

            //Close statement
            mysqli_stmt_close($stmt);

        }

    }

    //Close connection
    mysqli_close($dbh);

}

?>

<!DOCTYPE html>
<html>

<head>
  <title>NU | Pluto Login</title>
  <link rel="stylesheet" href="/devproject/css/login.css">
</head>

<body style="background-image: url('/devproject/assets/NeptuneBackground.png');">

    <?php 
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }        
    ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

        <div class="label">
            <h1>PLUTO</h1>
        </div>

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
            <span class="invalid-feedback"><?php echo $username_err; ?></span>
        </div>    
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
            <span class="invalid-feedback"><?php echo $password_err; ?></span>
        </div>
        <div class="form-group">
            <div class="centre-button">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
        </div>
        <p>DEV USE ONLY - <a href="php/register.php">register account</a>.</p>
  </form>

</body>

</html>