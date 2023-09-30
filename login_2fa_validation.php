<?php

if (isset($_SESSION['twofa_check_user_email'])) {

include 'sql_conn.php';
// Handle 2FA logic

if (isset($_POST['submitloginotp'])) {

    $otp2fa = $_POST['otp2fa'];

    $check_passed_email = $_SESSION['twofa_check_user_email'];

    // Check for Two Factor Authentication Code in database

    $checkotpsql = "SELECT * FROM login WHERE EmailId = '$check_passed_email' ";
    $checkotpresult = $conn->query($checkotpsql);
    if ($checkotpresult->num_rows > 0){
        while ($checkotprow = $checkotpresult->fetch_assoc()){
            $LoginCode = $checkotprow['TwoFactorAuthenticationCode'];
        }
    }

    if($LoginCode == $otp2fa){

        // Login, start session and redirect to home
        $_SESSION['passed_user_email'] = $check_passed_email;
        header('Location: home.php');

        // Update OtpCode in the Database 

        $otpupdateStmt = $conn->prepare("UPDATE login SET TwoFactorAuthenticationCode = NULL WHERE EmailId = ?");
        $otpupdateStmt->bind_param("s", $check_passed_email);
        $otpupdateStmt->execute();

        exit();

    } else{
        $error = "Entered Two Factor Authentication Code is Invalid";
    }
} else{}

} else{
    header('Location: login.php');
    exit();
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
</head>
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
        
        .login-container h4 {
            color: #333;
        }
        
        .login-container input[type="text"] {
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
        <h4>Enter Login OTP</h4>
        <form action="login_2fa_validation.php" method="POST">            
            <?php if (isset($error)) { ?>
            <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
            <?php } ?>
            <input type="text" name="otp2fa" placeholder="12345678..." required>
            <input type="submit" name="submitloginotp" value="Login">
        </form>
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
    </script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
