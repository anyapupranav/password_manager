<?php
error_reporting(E_ALL ^ E_WARNING);
error_reporting(E_ERROR | E_PARSE);
?>

<?php
session_start();
if ($_SESSION['passed_user_email'] === NULL){
    header('Location: login.php');
}
?>

<?php
// Database connection
include 'sql_conn.php';
include 'myfunctions.php';

// Check if the ID is provided in the query parameter
if (isset($_GET['id'])) {
    $UniqueId = $_GET['id'];
    $_SESSION['session_UniqueId'] = $UniqueId;

    $loggedinusermailid = $_SESSION['passed_user_email'];

    // Fetch existing password data

    $sharedsql = "SELECT * FROM shared_accounts WHERE sharedaccountuniqueid = '$UniqueId' and tosharedemailid = '$loggedinusermailid' ";
    $sharedsqlresult = $conn->query($sharedsql);
    if ($sharedsqlresult->num_rows > 0) {

    $sql = "SELECT * FROM vault WHERE UniqueId = '$UniqueId' ";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $oldgroupname = $row['GroupName'];
            $oldappname = $row['AppName'];
            $oldusername = $row['UserName'];
            $FetchedPostOldPassword = $row['Password'];
            $oldPassword = decryptString($FetchedPostOldPassword);
            $oldurl = $row['Url'];
            $oldnotes = $row['Notes'];
            $oldCurrentPasswordVersion = $row['CurrentPasswordVersion'];
            $_SESSION['oldCurrentPasswordVersion'] = $oldCurrentPasswordVersion;
        }
    } else {
        echo "Password page not found.";
        exit;
    }
    }else {
        echo "Sorry you don't have access to this page.";
        exit;
    }
} else {
    echo "Invalid request. No ID provided.";
}

// Close the database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>View Password</title>
</head>
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

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="index.html">Password Manager</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
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
                    <a class="nav-link" href="shared_passwords.php">Shared Passwords</a>
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

    <h2 style="text-align:center">View Password</h2>
    <div class="login-container">
        <div class="container mt-5">
            <form action="edit_password.php" method="post">
                <div class="form-group">
                    <label for="group">Group:</label>
                    <input type="text" class="form-control" id="GroupName" name="GroupName"
                        value="<?php echo $oldgroupname; ?>" >
                </div>
                <div class="form-group">
                    <label for="AppName">App Name:</label>
                    <input type="text" class="form-control" id="AppName" name="AppName"
                        value="<?php echo $oldappname; ?>" >
                </div>
                <div class="form-group">
                    <label for="UserName">User Name:</label>
                    <input type="text" class="form-control" id="UserName" name="UserName"
                        value="<?php echo $oldusername; ?>" >
                </div>
                <div class="form-group">
                    <label for="Password">Password:</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="Password" name="Password"
                            value="<?php echo $oldPassword; ?>" readonly>
                        <a href='password_generator.php' style="margin-left: 10px;"> Generate Password </a>
                    </div>
                    <div class="input-group-append">
                        <input type="checkbox" id="showPasswordCheckbox"> <span>Show Password</span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="Url">Url:</label>
                    <input type="text" class="form-control" id="Url" name="Url" value="<?php echo $oldurl; ?>"
                        >
                </div>
                <div class="form-group">
                    <label for="Notes">Notes:</label>
                    <textarea class="form-control" id="Notes" name="Notes" rows="3"
                    ><?php echo $oldnotes; ?></textarea>
                </div>
                <br>
                <a href='home.php' class='btn btn-light'>Cancel</a>
            </form>
        </div>
        <br></br>
        <a href='show_history.php'> Show History </a>
    </div>

    <hr>

    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p> Password Manager </p>
                </div>
                <div class="col-md-6">
                    <p> v1.0 </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Include Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

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
