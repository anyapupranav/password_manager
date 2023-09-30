<?php
error_reporting(E_ALL ^ E_WARNING);
error_reporting(E_ERROR | E_PARSE);
?>

<?php
session_start();
if ($_SESSION['passed_user_email'] === NULL) {
    header('Location: login.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Manager - Home</title>
    <link rel = "icon" href = "img/titleicon.png" type = "image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<style>
    .navbar-nav {
        margin-left: auto;
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

<?php include 'themenav0.php'; ?>

<?php
    // Check if the user is logged in
    session_start();
    if (isset($_SESSION['passed_user_email'])) {

        // User is logged in, display "My Account" and "Logout"
        echo '
        <li class="nav-item">
            <a class="nav-link active" href="home.php">Home</a>
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


    <div class="container-fluid">
      
    <span>
    <h2 style="margin-left: 1%;">Search Passwords
    <a href='add_password.php' class='btn btn-success' style="margin-left: 62.5%;"><strong>+ ADD NEW</strong></a> </h2>
    </span>

        <form action="home.php" method="POST">
            <table class="table table-striped ">
                <thead>
                    <tr>
                        <th colspan="4"><input type="text" class="form-control" id="search" name="search"
                                placeholder="Search..." required></th>
                        <th>
                        <button type="submit" class='btn btn-primary' name="submit">Search</button>
                        <a href="" class='btn btn-secondary' style="margin-left: 1%;">clear</a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- table body -->
                    <?php

                    include 'sql_conn.php';
                    include 'loading_spinner.php';

                    $loggedinusermailid = $_SESSION['passed_user_email'];

                    if (isset($_POST['submit'])) {
                        $searchQuery = $_POST['search'];
                        $sql = "SELECT * FROM vault WHERE UserEmailId = '$loggedinusermailid' AND DeleteFlag = 0 AND (GroupName LIKE '%$searchQuery%' OR AppName LIKE '%$searchQuery%' OR UserName LIKE '%$searchQuery%' OR Url LIKE '%$searchQuery%')";
                    } else {
                        $sql = "SELECT * FROM vault WHERE UserEmailId = '$loggedinusermailid' AND DeleteFlag = 0";
                    }

                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        
                        echo "<tr style='background-color: rgb(60, 210, 255);'> ";
                        echo "<th>Group</th> ";
                        echo "<th>App</th> ";
                        echo "<th>User</th> ";
                        echo "<th>Website Url</th> ";
                        echo "<th style='text-align: center'>Actions</th> ";
                        echo "</tr> ";
                         
                        while ($row = $result->fetch_assoc()) {

                            echo "<tr style='background-color: #D6EEEE;'>";
                            echo "<td>" . $row['GroupName'] . "</td>";
                            echo "<td>" . $row['AppName'] . "</td>";
                            echo "<td>" . $row['UserName'] . "</td>";
                            echo "<td> <a href=' " . $row['Url'] . " '> " . $row['Url'] . "</a></td>";
                            echo "<td style='text-align: center'> <a href='view_password.php?id=" . $row['UniqueId'] . "' class='btn btn-white border border-dark'>View</a>";
                            echo "&nbsp; <a href='edit_password.php?id=" . $row['UniqueId'] . "' class='btn btn-primary'>Edit</a>";
                            echo "&nbsp; <a href='delete_password.php?id=" . $row['UniqueId'] . "' class='btn btn-danger'>Delete</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'> <div class='alert alert-danger alert-dismissible' role='alert'> No Accounts found. </div> </td></tr>";
                    }

                    ?>
                </tbody>
            </table>
        </form>
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
