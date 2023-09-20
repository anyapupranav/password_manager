<?php
error_reporting(E_ALL ^ E_WARNING);
error_reporting(E_ERROR | E_PARSE);

// Start the session
session_start();

// Database connection
include "sql_conn.php";

// Handle form submission for requesting a password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userEmail = $_POST['resetEmail'];

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT * FROM login WHERE EmailId = ?");
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Generate a unique token for password reset (you can use a library for this)
        $resetToken = bin2hex(random_bytes(32));

        // Store the reset token and its expiration time in the database
        $resetExpiration = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $updateStmt = $conn->prepare("UPDATE login SET ResetToken = ?, ResetTokenExpiration = ? WHERE EmailId = ?");
        $updateStmt->bind_param("sss", $resetToken, $resetExpiration, $userEmail);

        if ($updateStmt->execute()) {
            // Send a password reset email with a link containing the resetToken
            $resetLink = "web-a.safesearch.co.in/password_manager/reset_password.php?token=" . $resetToken;
            $emailSubject = "Password Reset Request";
            $emailMessage = "To reset your password, click the following link:\n\n" . $resetLink;

            //send mail
            include 'myfunctions.php';
            sendForgotPasswordEmail($userEmail, $emailSubject, $emailMessage);

            // Redirect to a confirmation page
            header("Location: reset_password_request_confirm.php");
            exit();
        } else {
            $error = "Failed to generate a password reset token. Please try again later.";
        }
    } else {
        $error = "Email not found in our system.";
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
    <title>Forgot Password | Password Manager</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
                    <a class="nav-link" href="login.php">Login</a>
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
                        Forgot Password
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)) { ?>
                            <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
                        <?php } ?>
                        <form action="forgot_password.php" method="POST">
                            <div class="form-group">
                                <label for="resetEmail">Enter your email address:</label>
                                <input type="email" class="form-control" id="resetEmail" name="resetEmail" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Reset Password</button>
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
