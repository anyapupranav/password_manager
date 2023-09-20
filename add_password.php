<?php   
error_reporting(E_ALL ^ E_WARNING);
error_reporting(E_ERROR | E_PARSE);
?>

<?php
        session_start();
        if ($_SESSION['passed_user_email'] === NULL){
        header('Location: login.php');
        }
	    else
	        header('add_password.php');
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Add Password</title>

</head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-combobox/1.1.8/js/bootstrap-combobox.min.js"></script>
<script>
$(document).ready(function(){
  $('.combobox').combobox();
});
</script>
<style>
    .login-container {
      max-width: 80%;
      margin: 0 auto;
      padding: 10px;
      background-color: rgb(232, 242, 242);
      border-radius: 5px;
      box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    }
    .navbar-nav {
        margin-left: auto;
    }
</style>
<body>

<script>
    $(document).ready(function(){
    $('select').change(function(){
        if($('select option:selected').text() == "Other"){
        $('html select').after("<label>Enter New Group Name<input type='text' name='altgroup'></input></label>");
        }
    })
});
</script>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="index.html"><i style="font-size:24px" class="fa">&#xf023;</i> Password Manager</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">

                <?php
                  // Check if the user is logged in
                  session_start();
                  if (isset($_SESSION['passed_user_email'])) {

                    // User is logged in, display "My Account" and "Logout"
                    echo '
                    <li class="nav-item">
                    <a class="nav-link" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                    <a class="nav-link" href="password_generator.php">Password Generator</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="myaccount.php">My Account</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                    ';
                  }
                ?>
            </ul>
        </div>
    </nav>

        <h2 style="text-align:center">Add Password</h2>
<div class="login-container">
    <div class="container mt-5">
        <form action="add_password.php" method="post">
            <div class="form-group">
                <label for="group">Group:</label>
                <select name="group" class="combobox form-control" id="groupSelect">
                    <option></option>
                    <?php
                        include 'sql_conn.php';
                        // SQL query to fetch group options from your database
                        $passeduseridSqltofetch = $_SESSION['passed_user_email'];
                        $sql = "SELECT distinct GroupName FROM vault WHERE UserEmailId = '$passeduseridSqltofetch' and DeleteFlag = 0 ";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            // Fetch and store the group options
                            while ($row = $result->fetch_assoc()) {
                                $groupOptions = $row['GroupName'];
                                echo "<option value='$groupOptions'>$groupOptions</option>";
                            }
                        }
                    ?>
                    <option>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="AppName">App Name:</label>
                <input type="text" class="form-control" id="AppName" name="AppName" required>
            </div>
            <div class="form-group">
                <label for="UserName">User Name:</label>
                <input type="text" class="form-control" id="UserName" name="UserName" required>
            </div>
            <div class="form-group">
                <label for="Password">Password:</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="Password" name="Password" required>
                        <a href='password_generator.php' style="margin-left: 10px;"> Generate Password </a>
                    </div>
            </div>
            <div class="input-group-append">
                <input type="checkbox" id="showPasswordCheckbox"> <span>Show Password</span>
            </div>
            <div class="form-group">
                <label for="Url">Url:</label>
                <input type="text" class="form-control" id="Url" name="Url" >
            </div>
            <div class="form-group">
                <label for="Notes">Notes:</label>
                <textarea class="form-control" id="Notes" name="Notes" rows="3" ></textarea>
            </div>
            <br>
            <button type="submit" class="btn btn-primary" name="submit">Save</button>
            <a href='home.php' class='btn btn-light'>Cancel</a>
        </form>
    </div>
</div>

    <?php

include 'myfunctions.php';

    // Check if the form has been submitted
    if (isset($_POST['submit'])) {
        // Process the form data here
        $prigroupname = $_POST['group'];
        $altgroupname = $_POST['altgroup'];
        if ($prigroupname == 'Other'){
            $groupname = $altgroupname;
        }
        else{
            $groupname = $prigroupname;
        }
        $appname = $_POST['AppName'];
        $newusername = $_POST['UserName'];
        $postPassword = $_POST['Password'];
        $newpassword = encryptString($postPassword);
        $url = $_POST['Url'];
        $notes = $_POST['Notes'];

        include 'sql_conn.php';

        $loggedinusermailid = $_SESSION['passed_user_email'];
        $sql = "INSERT INTO vault (UniqueId, GroupName, AppName, UserName, Password, Url, Notes, UserEmailId) VALUES ('$uniqueid', '$groupname', '$appname', '$newusername', '$newpassword', '$url', '$notes', '$loggedinusermailid')";
        $result_sql = $conn->query($sql);
        $sqlfetch = "SELECT * FROM vault where UserName = '$newusername' ";
        $resultsqlfetch = $conn->query($sqlfetch);

        if ($resultsqlfetch->num_rows > 0) {
            while($row = $resultsqlfetch->fetch_assoc()){
                $fetcheduniqueid = $row['UniqueId'];
                $CurrentPasswordVersion = $row['CurrentPasswordVersion'];
            }
        }

        $sql1 = "INSERT INTO vault_history (UniqueId, Password, PasswordVersion) VALUES ('$fetcheduniqueid', '$newpassword', '$CurrentPasswordVersion')";
        $result_sql1 = $conn->query($sql1);
        if ($result_sql === TRUE && $result_sql1 === TRUE) {
            echo '<script type="text/javascript">'; 
            echo 'alert("Account added successfully!");'; 
            echo "window.location.href = 'edit_password.php?id=$fetcheduniqueid'";
            echo '</script>';
        } else {
            echo '<script type="text/javascript">'; 
            echo 'alert("An error occurred while adding the account.");'; 
            echo 'window.location.href = "home.php";';
            echo '</script>';
        }
    }
    ?>
<hr>

<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p> Password Manager </p>
            </div>
            <div class="col-md-6">
            <p>
                <?php
                    $sqlversion = "SELECT AppVersion FROM version ORDER BY AppVersion DESC LIMIT 1";
                    $resultversion = $conn->query($sqlversion);

                    if ($resultversion->num_rows > 0) {
                        while ($row = $resultversion->fetch_assoc()) {
                            $AppVersion = $row['AppVersion'];
                        }
                    }
                    echo $AppVersion;
                ?>
            </p>
            </div>
        </div>
    </div>
</footer>


    <!-- Include Bootstrap JS and jQuery -->



    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const passwordInput = document.getElementById("Password");
            const showPasswordCheckbox = document.getElementById("showPasswordCheckbox");

            showPasswordCheckbox.addEventListener("change", function () {
                if (showPasswordCheckbox.checked) {
                    passwordInput.type = "text";
                } else {
                    passwordInput.type = "password";
                }
            });
        });

        
    </script>
</body>
</html>
