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
// Database connection 
include "sql_conn.php";

// Handle login logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    if($user['ActiveFlag'] == 1 and $user['DeleteFlag'] == 0){

      if (password_verify($password, $user['Password'])) {
        // Password matches, create session and redirect to home page
        session_start();
        $_SESSION['passed_user_email'] = $email;
        header('Location: home.php');
        exit();
    } 
      else {
        // Invalid password
        $error = "Invalid password";
      }
      }
    else {
      // User is deleted or inactive
      $error = "User Deleted or Inactive";
    }
  } 
  else {
    // User not found
    $error = "User not found";
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
    <title>Login | Password Manager</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
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
                    <a class="nav-link active" href="login.php">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="signup.php">Register</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        User Login
                    </div>
                    <div class="card-body">
                        <form action="login.php" method="POST">
                        <?php if (isset($error)) { ?>
                        <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
                        <?php } ?>
                            <div class="form-group">
                                <label for="loginUsername">Username</label>
                                <input type="text" class="form-control" id="loginUsername" name="loginUsername" required>
                            </div>
                            <div class="form-group">
                                <label for="loginPassword">Password</label>
                                <input type="password" class="form-control" id="loginPassword" name="loginPassword" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Login</button><br></br>
                            <div class="forgot-password-link">
                                <p><a href="forgot_password.php">Forgot password?</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
