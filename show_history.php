<?php
error_reporting(E_ALL ^ E_WARNING);
error_reporting(E_ERROR | E_PARSE);
?>

<?php
session_start();
if ($_SESSION['passed_user_email'] === NULL) {
    header('Location: login.php');
}

// Include database connection
include 'sql_conn.php';
include 'myfunctions.php';

// Retrieve UniqueId and UserEmailId from session or previous page
$UniqueId = $_SESSION['session_UniqueId'];
$loggedinusermailid = $_SESSION['passed_user_email'];

// Query to fetch password history
$sql = "SELECT * FROM vault_history WHERE UniqueId = '$UniqueId' order by PasswordVersion desc";

// Execute the query
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password History</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<style>
    .login-container {
        max-width: 90%;
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
    <a class="navbar-brand" href="index.html"><i style="font-size:24px" class="fa">&#xf023;</i> Password Manager</a>
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

<h2 style="text-align:center">Password History</h2>
<div class="login-container">
    <div class="container mt-5">
        <?php
        if ($result->num_rows > 0) {
            echo '<table class="table w-auto ">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Password Version</th>';
            echo '<th>Group</th>';
            echo '<th>App</th>';
            echo '<th>User Name</th>';
            echo '<th>Password</th>';
            echo '<th>Url</th>';
            echo '<th>Notes</th>';
            echo '<th>Date Modified</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $row['PasswordVersion'] . '</td>';
                echo '<td>' . $row['GroupName'] . '</td>';
                echo '<td>' . $row['AppName'] . '</td>';
                echo '<td>' . $row['UserName'] . '</td>';
                echo '<td>' . decryptString($row['Password']) . '</td>';
                echo '<td><a href=" '. $row['Url'] . ' "> ' . $row['Url'] . ' </a></td>';
                echo '<td>' . $row['Notes'] . '</td>';
                echo '<td>' . $row['datecreated'] . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No password history available.</p>';
        }
        ?>
    </div>
</div>

<hr>

<footer>
    <div class="container">
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

</body>
</html>
