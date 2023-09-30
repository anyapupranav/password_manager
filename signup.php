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

    // Redirect to login page after successful registration
    $success = "User Registration successfull!";

    //send mail
    include 'myfunctions.php';
    sendSignupEmail($email, $lastname, $firstname);

  }

  // Close statement and database connection
  $stmt->close();
  $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGNUP | Password Manager</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<style>
    .navbar-nav {
        margin-left: auto;
    }
</style>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="index.html">Password Manager</a>
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

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        User Sign-Up Registration
                    </div>
                    <div class="card-body">
                        <form action="signup.php" method="POST">
                        <?php if (isset($error)) { ?>
                        <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
                        <?php } ?>
                        <?php if (isset($success)) { ?>
                        <div class="alert alert-success" role="alert"><?php echo $success; ?></div>
                        <?php } ?>
                            <div class="form-group">
                                <label for="registerFirstname">First Name</label>
                                <input type="text" class="form-control" id="registerFirstname" name="registerFirstname" required>
                            </div>
                            <div class="form-group">
                                <label for="registerLastname">Last Name</label>
                                <input type="text" class="form-control" id="registerLastname" name="registerLastname" required>
                            </div>
                            <div class="form-group">
                                <label for="registerEmail">Email</label>
                                <input type="text" class="form-control" id="registerEmail" name="registerEmail" required>
                            </div>
                            <div class="form-group">
                                <label for="registerPassword">Password</label>
                                <input type="password" class="form-control" id="registerPassword" name="registerPassword" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Register</button>
                            <div class="login-link">
                                <p>Already have an account? <a href="login.php">Login</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
