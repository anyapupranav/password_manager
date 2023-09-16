<?php
error_reporting(E_ALL ^ E_WARNING);
error_reporting(E_ERROR | E_PARSE);

// Start the session
session_start();

// Check if the user is logged in; if not, redirect to the login page
if (!isset($_SESSION['passed_user_email'])) {
    header("Location: login.php");
    exit();
}

// Database connection
include "sql_conn.php";

$userEmail = $_SESSION['passed_user_email'];

// Handle form submission for changing password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmNewPassword = $_POST['confirmNewPassword'];

    // Fetch the user's hashed password from the database
    $stmt = $conn->prepare("SELECT Password FROM login WHERE EmailId = ?");
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $hashedPassword = $row['Password'];

        // Verify the current password
        if (password_verify($currentPassword, $hashedPassword)) {
            // Current password is correct, now update the password
            if ($newPassword === $confirmNewPassword) {
                // Hash the new password
                $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                // Update the password in the database
                $updateStmt = $conn->prepare("UPDATE login SET Password = ? WHERE EmailId = ?");
                $updateStmt->bind_param("ss", $newHashedPassword, $userEmail);
                
                if ($updateStmt->execute()) {
                    // Password update successful
                    $success = "Password updated successfully!";
                } else {
                    // Handle the case where the update fails
                    $error = "Failed to update the password.";
                }
            } else {
                // New password and confirm password do not match
                $error = "New password and confirm password do not match.";
            }
        } else {
            // Current password is incorrect
            $error = "Current password is incorrect.";
        }
    } else {
        // Handle the case where user data is not found
        $error = "User data not found.";
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
    <title>Change Password | Password Manager</title>
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
                        <a class="nav-link" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="shared_passwords.php">Shared Passwords</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="password_generator.php">Password Generator</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="myaccount.php">My Account</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-center">
                        Change Password
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)) { ?>
                            <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
                        <?php } ?>
                        <?php if (isset($success)) { ?>
                            <div class="alert alert-success" role="alert"><?php echo $success; ?></div>
                        <?php } ?>
                        <form action="change_password.php" method="POST">
                            <div class="form-group">
                                <label for="currentPassword">Current Password</label>
                                <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                            </div>
                            <div class="form-group">
                                <label for="newPassword">New Password</label>
                                <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                            </div>
                            <div class="form-group">
                                <label for="confirmNewPassword">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirmNewPassword" name="confirmNewPassword" required>
                            </div>
                            <div class="d-flex align-items-center justify-content-center">
                                <button type="submit" class="btn btn-primary">Change Password</button>
                                <a href='myaccount.php' class='btn btn-light' style='margin-left: 30px;'>Cancel</a>
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
