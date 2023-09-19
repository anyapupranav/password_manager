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


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shared | Password Manager</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<style>
    .navbar-nav {
        margin-left: auto;
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
                    <a class="nav-link active" href="shared_passwords.php">Shared Passwords</a>
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



    <div class="container-fluid">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th colspan="5"> <h2>Shared with Me</h2> </th>
                </tr>
                <tr style="background-color: rgb(60, 210, 255);">
                    <th>Group</th>
                    <th>App</th>
                    <th>User</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- table body -->
                <?php

                include 'sql_conn.php';

                $loggedinusermailid = $_SESSION['passed_user_email'];
                $sql = "SELECT * from shared_accounts where tosharedemailid = '$loggedinusermailid' and deleteflag = 0";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()){
                        $vaultfetcheduniqueid = $row['sharedaccountuniqueid'];
                        
                        $sqlvault = "SELECT * from vault where UniqueId = '$vaultfetcheduniqueid' and DeleteFlag = 0";
                        $sqlvaultresult = $conn->query($sqlvault);

                        if ($sqlvaultresult->num_rows > 0) {
                            while($sqlvaultrow = $sqlvaultresult->fetch_assoc()){

                            echo "<tr>";
                            echo "<td>" . $sqlvaultrow['GroupName'] . "</td>";
                            echo "<td>" . $sqlvaultrow['AppName'] . "</td>";
                            echo "<td>" . $sqlvaultrow['UserName'] . "</td>";
                            echo "<td> <a href='view_password.php?id=" . $sqlvaultrow['UniqueId'] . "' class='btn btn-primary'>View</a> </td>";
                            echo "</tr>";
                            }
                        }
                    }
                } else {
                    echo "<tr><td colspan='5'>No Accounts found.</td></tr>";
                }

                ?>
            </tbody>
        </table>


        <table class="table table-striped">
            <thead>
                <tr>
                    <th colspan="6"> <h2>Shared with Others</h2> </th>
                </tr>
                <tr style="background-color: rgb(60, 210, 255);">
                    <th>Group</th>
                    <th>App</th>
                    <th>User</th>
                    <th>Shared with</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- table body -->
                <?php

                include 'sql_conn.php';

                $loggedinusermailid = $_SESSION['passed_user_email'];
                $sql1 = "SELECT * from shared_accounts where fromsharedemailid = '$loggedinusermailid' and deleteflag = 0";
                $result1 = $conn->query($sql1);

                if ($result1->num_rows > 0) {
                    while($roww = $result1->fetch_assoc()){
                        $vaultfetcheduniqueid = $roww['sharedaccountuniqueid'];
                        
                        $sqlvault1 = "SELECT * from vault where UniqueId = '$vaultfetcheduniqueid' and DeleteFlag = 0";
                        $sqlvaultresult1 = $conn->query($sqlvault1);

                        if ($sqlvaultresult1->num_rows > 0) {
                            while($sqlvaultroww = $sqlvaultresult1->fetch_assoc()){

                            echo "<tr>";
                            echo "<td>" . $sqlvaultroww['GroupName'] . "</td>";
                            echo "<td>" . $sqlvaultroww['AppName'] . "</td>";
                            echo "<td>" . $sqlvaultroww['UserName'] . "</td>";
                            echo "<td>" . $roww['tosharedemailid'] . "</td>";
                            echo "<td> <a href='delete_share.php?id=" . $sqlvaultroww['UniqueId'] . $roww['tosharedemailid'] . "' class='btn btn-danger'>Remove</a> </td>";
                            echo "</tr>";
                            }
                        }
                    }
                } else {
                    echo "<tr><td colspan='6'>No Accounts found.</td></tr>";
                }

                ?>
            </tbody>
        </table>

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

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
