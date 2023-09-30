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
    <link rel = "icon" href = "img/titleicon.png" type = "image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<style>
    .navbar-nav {
        margin-left: auto;
    }
</style>
<body>

<?php include 'themenav0.php'; ?>

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

<?php include 'themenav1.php'; ?>

<h2 style="text-align:center">Password History</h2>
    <div class="container mt-5">
        <?php
        if ($result->num_rows > 0) {
            echo '<table class="table w-auto ">';
            echo '<thead>';
            echo '<tr style="background-color: rgb(60, 210, 255);">';
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
                echo '<tr style="background-color: #D6EEEE;">';
                echo '<td>' . $row['PasswordVersion'] . '</td>';
                echo '<td>' . $row['GroupName'] . '</td>';
                echo '<td>' . $row['AppName'] . '</td>';
                echo '<td>' . $row['UserName'] . '</td>';

                $oldCurrentEncryptionKeyId = $row['EncryptionKeyId'];

                $sqlfetchenckey = "SELECT * FROM encryption WHERE EncryptionKeyVersion = '$oldCurrentEncryptionKeyId'; ";
                $resultfetchenckey = $conn->query($sqlfetchenckey);
        
                if ($resultfetchenckey->num_rows > 0) {
                    while ($rowfetchenckey = $resultfetchenckey->fetch_assoc()) {
                        $FetchedDecryptionKey = $rowfetchenckey['EncryptionKey'];
                    }
                }

                echo '<td>' . decryptString($row['Password'], $FetchedDecryptionKey) . '</td>';
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

<hr>

<footer>
    <div class="container">
    <hr style="background-color:gray">
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
