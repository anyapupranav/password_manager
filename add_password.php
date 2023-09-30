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
    <link rel = "icon" href = "img/titleicon.png" type = "image/x-icon">

</head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-combobox/1.1.8/js/bootstrap-combobox.min.js"></script>
<script>
$(document).ready(function(){
  $('.combobox').combobox();
});
</script>
<style>
    .navbar-nav {
        margin-left: auto;
    }
        /* Styles for the overlay */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
            z-index: 1000; /* Make sure it's above other content */
        }

        /* Styles for the spinner */
        .spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1001; /* Make sure it's above the overlay */
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

<?php include 'themenav0.php'; ?>

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

<?php include 'themenav1.php'; ?>

        <h2 style="text-align:center">Add Password</h2>

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

    <?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
        $url = $_POST['Url'];
        $notes = $_POST['Notes'];

        include 'sql_conn.php';

        $loggedinusermailid = $_SESSION['passed_user_email'];

        $sqlfetchencid = "SELECT * FROM encryption WHERE UserEmailId = '$loggedinusermailid' ORDER BY EncryptionKeyVersion DESC LIMIT 1;"; 

        $resultsqlfetchencid = $conn->query($sqlfetchencid);

        if ($resultsqlfetchencid->num_rows > 0) {
            while($rowsqlfetchencid = $resultsqlfetchencid->fetch_assoc()){
                $fetchedEncryptionKeyVersion = $rowsqlfetchencid['EncryptionKeyVersion'];
                $fetchedEncryptionKey = $rowsqlfetchencid['EncryptionKey'];
            }
        }

        $newpassword = encryptString($postPassword, $fetchedEncryptionKey);


        $sql = "INSERT INTO vault (UniqueId, GroupName, AppName, UserName, Password, Url, Notes, UserEmailId, EncryptionKeyId) VALUES ('$uniqueid', '$groupname', '$appname', '$newusername', '$newpassword', '$url', '$notes', '$loggedinusermailid', '$fetchedEncryptionKeyVersion')";
        $result_sql = $conn->query($sql);
        $sqlfetch = "SELECT * FROM vault where UserName = '$newusername' ";
        $resultsqlfetch = $conn->query($sqlfetch);

        if ($resultsqlfetch->num_rows > 0) {
            while($row = $resultsqlfetch->fetch_assoc()){
                $fetcheduniqueid = $row['UniqueId'];
                $CurrentPasswordVersion = $row['CurrentPasswordVersion'];
            }
        }

        $sql1 = "INSERT INTO vault_history (UniqueId, GroupName, AppName, UserName, Password, Url, Notes, PasswordVersion, EncryptionKeyId) VALUES ('$fetcheduniqueid', '$groupname', '$appname', '$newusername',  '$newpassword', '$url', '$notes', '$CurrentPasswordVersion', '$fetchedEncryptionKeyVersion')";
        $result_sql1 = $conn->query($sql1);

        // Send notification(s) via mail if email notifications are enabled.
        $CheckUserEmailId = $_SESSION['passed_user_email'];

        $checknotificationssql = "SELECT * FROM notifications WHERE UserEmailId = '$CheckUserEmailId' ";
        $checknotificationsresult = $conn->query($checknotificationssql);
        if ($checknotificationsresult->num_rows > 0){
            while ($checknotificationsrow = $checknotificationsresult->fetch_assoc()){
                $NewAccountAddedFlag = $checknotificationsrow['NewAccountAdded'];
            }
        }

        if ($NewAccountAddedFlag == 1){

            // send mail if notifications is enabled
            $sqlNewAccountAdded = "SELECT * FROM message_templates WHERE TemplateName = 'add account' and DeleteFlag = 0 ";
            $resultNewAccountAdded = $conn->query($sqlNewAccountAdded);
        
            if ($resultNewAccountAdded->num_rows > 0) {
                while($rowNewAccountAdded = $resultNewAccountAdded->fetch_assoc()){
                    $strsubject = $rowNewAccountAdded['Subject'];
                    $strmessagebody1 = $rowNewAccountAdded['Body1'];
                    $strmessagebody2 = $rowNewAccountAdded['Body2'];
                }
            }
        
            // send mail
        
            require 'phpmailer/src/Exception.php';
            require 'phpmailer/src/PHPMailer.php';
            require 'phpmailer/src/SMTP.php';
        
            $mail = new PHPMailer (true);
        
            $sqlmailslug = "SELECT * FROM mailslug where DeleteFlag = 0 ORDER BY Sno DESC LIMIT 1";
            $resultmailslug = $conn->query($sqlmailslug);
        
            if ($resultmailslug->num_rows > 0) {
                while ($rowmailslug = $resultmailslug->fetch_assoc()) {
                    $FetchedMailId = $rowmailslug['EmailId'];
                    $FetchedMailAppPassword = $rowmailslug['EmailAppPassword'];
                }
            }
        
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
        
            $mail->Username = $FetchedMailId; // Your gmail
            $mail->Password = $FetchedMailAppPassword; // Your gmail app password
        
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;
        
            $mail->setFrom($FetchedMailId); // Your gmail
        
            $mail->addAddress($CheckUserEmailId);
            $mail->isHTML (true);
            $mail->Subject = $strsubject;
            $mail->Body =  $strmessagebody1 .''. $strmessagebody2;
            $mail->send();


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
        else{
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
    }
    ?>


<footer>
    <div class="container">
    <hr style="background-color:gray">
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

    <!-- Overlay and spinner elements -->
    <div class="overlay" id="overlay"></div>
    <div class="spinner" id="spinner">
        <!-- You can use an actual spinner image or icon here -->
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
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

        // Function to show the overlay and spinner
        function showSpinner() {
            document.getElementById("overlay").style.display = "block";
            document.getElementById("spinner").style.display = "block";
        }

        // Function to hide the overlay and spinner
        function hideSpinner() {
            document.getElementById("overlay").style.display = "none";
            document.getElementById("spinner").style.display = "none";
        }

        // Attach an event listener to the form submission
        document.querySelector("form").addEventListener("submit", function () {
            showSpinner(); // Show the spinner when the form is submitted
        });
        
    </script>
</body>
</html>
