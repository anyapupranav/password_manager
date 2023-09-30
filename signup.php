<?php   
error_reporting(E_ALL ^ E_WARNING);
error_reporting(E_ERROR | E_PARSE);
?>

<?php
// Database connection 
include "sql_conn.php";

// Handle registration logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Retrieve submitted form data
  $firstname = $_POST['registerFirstname'];
  $lastname = $_POST['registerLastname'];
  $email = $_POST['registerEmail'];
  $password = $_POST['registerPassword'];

  // Check if email is already registered
  $stmt = $conn->prepare("SELECT * FROM users WHERE EmailId = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    // Email is already registered
    $error = "Email is already registered";
  } else {
    // Email is available, proceed with user registration
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Get current date and time 
    date_default_timezone_set("Asia/Kolkata");
    $currentdatetimestamp = date("Y-m-d H:i:s");

    // Insert user details into database table users
    $stmt = $conn->prepare("INSERT INTO users (FirstName, LastName, EmailId, CreatedOn) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $firstname, $lastname, $email, $currentdatetimestamp);
    $stmt->execute();

    // Insert user details into database table login
    $fpstmt = $conn->prepare("INSERT INTO login (EmailId, Password, CreatedOn) VALUES (?, ?, ?)");
    $fpstmt->bind_param("sss", $email, $hashedPassword, $currentdatetimestamp);
    $fpstmt->execute();

    // Insert into notifications table in database
    $nstmt = $conn->prepare("INSERT INTO notifications (UserEmailId) VALUES (?)");
    $nstmt->bind_param("s", $email);
    $nstmt->execute();

    // Generate a new encryption key for signed up user
    include 'myfunctions.php';
    $GeneratedNewEncryptionKey = generateEncryptionkey();

    // Insert into encryption table
    $encstmt = $conn->prepare("INSERT INTO encryption (EncryptionKey, UserEmailId) VALUES (?, ?)");
    $encstmt->bind_param("ss", $GeneratedNewEncryptionKey, $email);
    $encstmt->execute();

    // Redirect to login page after successful registration
    $success = "User Registration successfull!";

    //send mail
    sendSignupEmail($email, $lastname, $firstname);

  }

  // Close statement and database connection
  $stmt->close();
  $fpstmt->close();
  $nstmt->close();
  $encstmt->close();
  $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGNUP | Password Manager</title>
    <link rel = "icon" href = "img/titleicon.png" type = "image/x-icon">
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
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="index.html"><i style="font-size:24px" class="fa">&#xf023;</i> Password Manager</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="signup.php">Register</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="body">
        <div class="login-container">
            <h1> User Sign-Up </h1>
            <form action="signup.php" method="POST">
                <?php if (isset($error)) { ?>
                <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
                <?php } ?>
                <?php if (isset($success)) { ?>
                <div class="alert alert-success" role="alert"><?php echo $success; ?></div>
                <?php } ?>

                <input type="text" class="form-control" id="registerFirstname" placeholder="First Name" name="registerFirstname" required>

                <input type="text" class="form-control" id="registerLastname" placeholder="Last Name" name="registerLastname" required>

                <input type="text" class="form-control" id="registerEmail" placeholder="Email" name="registerEmail" required>

                <input type="password" class="form-control" id="registerPassword" placeholder="Password" name="registerPassword" required>
                <input type="checkbox" class="password-toggle" onclick="togglePasswordVisibility()"> Show Password
                <input type="submit" class="btn btn-primary" value="Register">
                <div class="login-link">
                    <p>Already have an account? <a href="login.php">Login</a></p>
                </div>
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
        function togglePasswordVisibility() {
            var passwordField = document.getElementById("registerPassword");
            if (passwordField.type === "password") {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        }
        
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
