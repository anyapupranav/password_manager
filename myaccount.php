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

// Fetch user information from the database based on their email
$stmt = $conn->prepare("SELECT * FROM users WHERE EmailId = ? and DeleteFlag = 0 ");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $userData = $result->fetch_assoc();
} else {
    // Handle the case where user data is not found
    echo "User data not found.";
    exit();
}

// Handle form submissions for updating user information
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newFirstName = $_POST['newFirstName'];
    $newLastName = $_POST['newLastName'];
    $newMobileNumber = $_POST['newMobileNumber'];

    // Update user information in the database
    $updateStmt = $conn->prepare("UPDATE users SET FirstName = ?, LastName = ?, MobileNumber = ? WHERE EmailId = ?");
    $updateStmt->bind_param("ssss", $newFirstName, $newLastName, $newMobileNumber, $userEmail);
    
    if ($updateStmt->execute()) {
        // Update successful, refresh user data
        $userData['FirstName'] = $newFirstName;
        $userData['LastName'] = $newLastName;
        $userData['MobileNumber'] = $newMobileNumber;
        $success = "User information update sucessfull.";
    } else {
        // Handle the case where the update fails
        $error = "Failed to update user information.";
    }
}

// Handle changing password
if (isset($_POST['changePassword'])) {
    // Redirect to the change password page
    header("Location: change_password.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | Password Manager</title>
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

                        <?php if (isset($error)) { ?>
                        <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
                        <?php } ?>
                        <?php if (isset($success)) { ?>
                        <div class="alert alert-success" role="alert"><?php echo $success; ?></div>
                        <?php } ?>

                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-center">
                        My Account
                    </div>
                    <div class="card-body">
                        <form action="myaccount.php" method="POST">
                            <div class="form-group">
                                <label for="newFirstName">First Name</label>
                                <input type="text" class="form-control" id="newFirstName" name="newFirstName" value="<?php echo $userData['FirstName']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="newLastName">Last Name</label>
                                <input type="text" class="form-control" id="newLastName" name="newLastName" value="<?php echo $userData['LastName']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="newMobileNumber">Mobile Number</label>
                                <input type="text" class="form-control" id="newMobileNumber" name="newMobileNumber" value="<?php echo $userData['MobileNumber']; ?>" required>
                            </div>
                            <div class="d-flex align-items-center justify-content-center">
                                <button type="submit" class="btn btn-primary mt-3">Update Information</button>
                                <button type="submit" class="btn btn-secondary mt-3" name="changePassword" style="margin-left: 20px;">Change Password</button>
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
