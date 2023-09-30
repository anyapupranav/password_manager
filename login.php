<?php   
error_reporting(E_ALL ^ E_WARNING);
error_reporting(E_ERROR | E_PARSE);
?>

<?php
        session_start();
        if ($_SESSION['passed_user_email'] === NULL){}
        else{
            echo '<script type="text/javascript">'; 
            echo 'window.location.href = "home.php";';
            echo '</script>';
        }
?>

<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection 
include "sql_conn.php";

// Handle login logic
if (isset($_POST['loginpost'])) {
  // Retrieve submitted form data
  $email = $_POST['loginUsername'];
  $password = $_POST['loginPassword'];

  // Prepare SQL statement to fetch user details based on email
  $stmt = $conn->prepare("SELECT * FROM login WHERE EmailId = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    // User found, verify password
    $user = $result->fetch_assoc();

    if($user['DeleteFlag'] == 0){

      if (password_verify($password, $user['Password'])) {

        // Send notification(s) via mail if email notifications are enabled.
        $CheckUserEmailId = $email;

        $checknotificationssql = "SELECT * FROM notifications WHERE UserEmailId = '$CheckUserEmailId' ";
        $checknotificationsresult = $conn->query($checknotificationssql);
        if ($checknotificationsresult->num_rows > 0){
            while ($checknotificationsrow = $checknotificationsresult->fetch_assoc()){
                $AccountLoginFlag = $checknotificationsrow['AccountLogin'];
            }
        }

        $TwoFactorAuthenticationFlag = $user['TwoFactorAuthentication'];

        if ($user['LockoutFlag'] == 1 && $user['LockoutCount'] > 5){

            $error = "Your account has been Locked out, Please reset your password using forgot password.";

        }else{
            if ($user['LockoutFlag'] == 1){
                $stmtUpdateLockOutFlag = $conn->prepare("UPDATE login SET LockoutCount = 0 WHERE EmailId = ?");
                $stmtUpdateLockOutFlag->bind_param("s", $CheckUserEmailId);
                $stmtUpdateLockOutFlag->execute();
            } else{}
        
        if ($TwoFactorAuthenticationFlag == '1'){
            // Generate Otp
            $OtpCode = mt_rand(10000000, 99999999);

            // Insert OtpCode into Database 

            $otpupdateStmt = $conn->prepare("UPDATE login SET TwoFactorAuthenticationCode = ? WHERE EmailId = ?");
            $otpupdateStmt->bind_param("ss", $OtpCode, $CheckUserEmailId);
            $otpupdateStmt->execute();

            //Send 2FA Otp mail

            $sqlForgotPassword = "SELECT * FROM message_templates WHERE TemplateName = '2fa otp mail' and DeleteFlag = 0 ";
            $resultForgotPassword = $conn->query($sqlForgotPassword);
        
            if ($resultForgotPassword->num_rows > 0) {
                while($row = $resultForgotPassword->fetch_assoc()){
                    $strsubject = $row['Subject'];
                    $strmessagebody1 = $row['Body1'];
                    $strmessagebody2 = $row['Body2'];
                }
            }

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
            $mail->Body =  $strmessagebody1 .''. $emailMessage .''. $OtpCode .''. $strmessagebody2;
            $mail->send();

            $_SESSION['twofa_check_user_email'] = $CheckUserEmailId;
            header('Location: login_2fa_validation.php');
            exit();

        } else{
            if ($AccountLoginFlag == 1){

                // send mail if notifications is enabled
                $sqlAccountLogin = "SELECT * FROM message_templates WHERE TemplateName = 'login' and DeleteFlag = 0 ";
                $resultAccountLogin = $conn->query($sqlAccountLogin);
            
                if ($resultAccountLogin->num_rows > 0) {
                    while($rowAccountLogin = $resultAccountLogin->fetch_assoc()){
                        $strsubject = $rowAccountLogin['Subject'];
                        $strmessagebody1 = $rowAccountLogin['Body1'];
                        $strmessagebody2 = $rowAccountLogin['Body2'];
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

                session_start();
                $_SESSION['passed_user_email'] = $email;            
                header('Location: home.php');
                exit();
            } else{            
                // IF notifications for login alert is disabled
                session_start();
                $_SESSION['passed_user_email'] = $email;
                header('Location: home.php');
                exit();
                }
            // IF 2FA for login is disabled
            session_start();
            $_SESSION['passed_user_email'] = $email;
            header('Location: home.php');
            exit();
            }
        }


    } 
      else {
        // Invalid password

        $NewLockOutCount = $user['LockoutCount'] + 1;

        $LockoutCountStmt = $conn->prepare("UPDATE login SET LockoutCount = ? WHERE EmailId = ?");
        $LockoutCountStmt->bind_param("ss", $NewLockOutCount, $email);
        $LockoutCountStmt->execute();

        $error = "Invalid password";
      }
      }
    else {
      // User is deleted
      $error = "User Deleted";
    }
  } 
  else {
    // User not found
    $error = "User not found";
  }

}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Password Manager</title>
    <link rel = "icon" href = "img/titleicon.png" type = "image/x-icon">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .navbar-nav {
            margin-left: auto;
        }
        .body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #fff;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            padding: 20px;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        .login-container h1 {
            color: #333;
        }
        .login-container input[type="text"],
        .login-container input[type="password"] {
            background-color: rgb(255, 255, 230);
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .login-container input[type="submit"] {
            background-color: #007BFF;
            color: #fff;
            width: 100%;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .login-container input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .password-toggle {
            text-align: left;
            align: left;
            align-items: left;
            margin-top: 10px;
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
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="index.html"><i style="font-size:24px" class="fa">&#xf023;</i> Password Manager</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" href="login.php">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="signup.php">Register</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="body">
        <div class="login-container">
            <h1>Login</h1>
            <form action="login.php" method="POST">
                <?php if (isset($error)) { ?>
                <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
                <?php } ?>
                <input type="text" name="loginUsername" placeholder="Username" required>
                <input type="password" name="loginPassword" id="password" placeholder="Password" required> <br>
                <input type="checkbox" class="password-toggle" onclick="togglePasswordVisibility()"> Show Password
                <input type="submit" name="loginpost" value="Login">
            </form>
            <a href="forgot_password.php">Forgot password?</a>
        </div>
    </div>

    <!-- Overlay and spinner elements -->
    <div class="overlay" id="overlay"></div>
    <div class="spinner" id="spinner">
        <!-- You can use an actual spinner image or icon here -->
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <script>
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

        // Function to toggle password visibility
        function togglePasswordVisibility() {
            var passwordField = document.getElementById("password");
            if (passwordField.type === "password") {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        }
    </script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

