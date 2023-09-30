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
            </ul>
        </div>
    </nav>

    <div class="container-fluid">
        <form action="home.php" method="POST">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th colspan="3">
                            <h2>Search Passwords</h2>
                        </th>
                        <th><a href='add_password.php' class='btn btn-success'><strong>+ ADD NEW</strong></a></th>
                    </tr>
                    <tr>
                        <th colspan="3"><input type="text" class="form-control" id="search" name="search"
                                placeholder="Search..." required></th>
                        <th><button type="submit" class='btn btn-primary' name="submit">Search</button></th>
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

                    if (isset($_POST['submit'])) {
                        $searchQuery = $_POST['search'];
                        $sql = "SELECT * FROM vault WHERE UserEmailId = '$loggedinusermailid' AND DeleteFlag = 0 AND (GroupName LIKE '%$searchQuery%' OR AppName LIKE '%$searchQuery%' OR UserName LIKE '%$searchQuery%')";
                    } else {
                        $sql = "SELECT * FROM vault WHERE UserEmailId = '$loggedinusermailid' AND DeleteFlag = 0";
                    }

                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {

                            echo "<tr>";
                            echo "<td>" . $row['GroupName'] . "</td>";
                            echo "<td>" . $row['AppName'] . "</td>";
                            echo "<td>" . $row['UserName'] . "</td>";
                            echo "<td><a href='edit_password.php?id=" . $row['UniqueId'] . "' class='btn btn-primary'>Edit</a>";
                            echo "&nbsp; <a href='delete_password.php?id=" . $row['UniqueId'] . "' class='btn btn-danger'>Delete</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No Accounts found.</td></tr>";
                    }

                    ?>
                </tbody>
            </table>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
