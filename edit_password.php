<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Error reporting and session start
error_reporting(E_ALL ^ E_WARNING);
error_reporting(E_ERROR | E_PARSE);
session_start();

// Redirect to login if not logged in
if ($_SESSION['passed_user_email'] === NULL) {
    header('Location: login.php');
    exit;
}

// Include database connection
include 'sql_conn.php';
include 'myfunctions.php';

// Check if the ID is provided in the query parameter
if (isset($_GET['id'])) {
    $UniqueId = $_GET['id'];
    $_SESSION['session_UniqueId'] = $UniqueId;

    $loggedinusermailid = $_SESSION['passed_user_email'];

    // Fetch existing password data
    $sql = "SELECT * FROM vault WHERE UniqueId = '$UniqueId' AND UserEmailId = '$loggedinusermailid'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $oldgroupname = $row['GroupName'];
            $oldappname = $row['AppName'];
            $oldusername = $row['UserName'];
            $FetchedPostOldPassword = $row['Password'];
            $oldCurrentEncryptionKeyId = $row['EncryptionKeyId'];
            $oldurl = $row['Url'];
            $oldnotes = $row['Notes'];
            $oldCurrentPasswordVersion = $row['CurrentPasswordVersion'];
            $_SESSION['oldCurrentPasswordVersion'] = $oldCurrentPasswordVersion;
        }

        $sqlfetchenckey = "SELECT * FROM encryption WHERE UserEmailId = '$loggedinusermailid' and EncryptionKeyVersion = '$oldCurrentEncryptionKeyId'; ";
        $resultfetchenckey = $conn->query($sqlfetchenckey);

        if ($resultfetchenckey->num_rows > 0) {
            while ($rowfetchenckey = $resultfetchenckey->fetch_assoc()) {
                $FetchedDecryptionKey = $rowfetchenckey['EncryptionKey'];
            }
        }
        $oldPassword = decryptString($FetchedPostOldPassword, $FetchedDecryptionKey);

    } else {
        echo "Password page not found.";
        exit;
    }
} else {
    echo "Invalid request. No ID provided.";
    exit;
}

// Check if the modification form is submitted
if (isset($_POST['submitsave'])) {
    // Retrieve modified data from the form
    $prigroupname = $_POST['group'];
    $altgroupname = $_POST['altgroup'];
    if ($prigroupname == 'Other'){
        $newgroupname = $altgroupname;
    }
    else{
        $newgroupname = $prigroupname;
    }
    $newappname = $_POST['AppName'];
    $newusername = $_POST['UserName'];
    $postnewPassword = $_POST['Password'];
    $newPassword = encryptString($postnewPassword, $FetchedDecryptionKey);
    $newurl = $_POST['Url'];
    $newnotes = $_POST['Notes'];

    $UniqueId = $_SESSION['session_UniqueId'];
    $oldCurrentPasswordVersion = $_SESSION['oldCurrentPasswordVersion'];
    $modifiedCurrentPasswordVersion = $oldCurrentPasswordVersion + 1;

    // Perform the update operation
    $updateSql = "UPDATE vault SET 
                  GroupName = '$newgroupname',
                  AppName = '$newappname',
                  UserName = '$newusername',
                  Password = '$newPassword',
                  Url = '$newurl',
                  Notes = '$newnotes',
                  CurrentPasswordVersion = '$modifiedCurrentPasswordVersion'
                  WHERE UniqueId = '$UniqueId' ";

    $result1 = $conn->query($updateSql);

    $insertsql = "INSERT INTO vault_history (UniqueId, Password, PasswordVersion, GroupName, AppName, UserName, Url, Notes, EncryptionKeyId) VALUES
                    ('$UniqueId','$newPassword','$modifiedCurrentPasswordVersion', '$newgroupname', '$newappname', '$newusername', '$newurl', '$newnotes', '$oldCurrentEncryptionKeyId')";

    $result2 = $conn->query($insertsql);

    if ($result1 == TRUE && $result2 == TRUE) {

        // Send notification(s) via mail if email notifications are enabled.
        $CheckUserEmailId = $_SESSION['passed_user_email'];

        $checknotificationssql = "SELECT * FROM notifications WHERE UserEmailId = '$CheckUserEmailId' ";
        $checknotificationsresult = $conn->query($checknotificationssql);
        if ($checknotificationsresult->num_rows > 0){
            while ($checknotificationsrow = $checknotificationsresult->fetch_assoc()){
                $NewAccountAddedFlag = $checknotificationsrow['NewAccountAdded'];
            }
        }

        if ($NewAccountAddedFlag == 1){

            // send mail if notifications is enabled
            $sqlNewAccountAdded = "SELECT * FROM message_templates WHERE TemplateName = 'edit account' and DeleteFlag = 0 ";
            $resultNewAccountAdded = $conn->query($sqlNewAccountAdded);
        
            if ($resultNewAccountAdded->num_rows > 0) {
                while($rowNewAccountAdded = $resultNewAccountAdded->fetch_assoc()){
                    $strsubject = $rowNewAccountAdded['Subject'];
                    $strmessagebody1 = $rowNewAccountAdded['Body1'];
                    $strmessagebody2 = $rowNewAccountAdded['Body2'];
                }
            }
        
            // send mail
        
            require 'phpmailer/src/Exception.php';
            require 'phpmailer/src/PHPMailer.php';
            require 'phpmailer/src/SMTP.php';
        
            $mail = new PHPMailer (true);
        
            $sqlmailslug = "SELECT * FROM mailslug where DeleteFlag = 0 ORDER BY Sno DESC LIMIT 1";
            $resultmailslug = $conn->query($sqlmailslug);
        
            if ($resultmailslug->num_rows > 0) {
                while ($rowmailslug = $resultmailslug->fetch_assoc()) {
                    $FetchedMailId = $rowmailslug['EmailId'];
                    $FetchedMailAppPassword = $rowmailslug['EmailAppPassword'];
                }
            }
        
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
        
            $mail->Username = $FetchedMailId; // Your gmail
            $mail->Password = $FetchedMailAppPassword; // Your gmail app password
        
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;
        
            $mail->setFrom($FetchedMailId); // Your gmail
        
            $mail->addAddress($CheckUserEmailId);
            $mail->isHTML (true);
            $mail->Subject = $strsubject;
            $mail->Body =  $strmessagebody1 .''. $strmessagebody2;
            $mail->send();
        } else{}

        echo '<script type="text/javascript">';
        echo 'alert("Changes have been modified successfully.");';
        echo "window.location.href = 'edit_password.php?id=$UniqueId'";
        echo '</script>';
    } else {
        echo "Error updating Changes: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>

    <title>Edit Password</title>
    <link rel = "icon" href = "img/titleicon.png" type = "image/x-icon">
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
                  if (isset($_SESSION['passed_user_email'])) {

                    // User is logged in, display navigation links
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

    <h2 style="text-align:center">Edit Password</h2>
        <div class="container mt-5">
            <form action="edit_password.php?id=<?php echo $UniqueId; ?>" method="post">
                <div class="form-group">
                    <label for="group">Group:</label>
                    <select name="group" class="combobox form-control" id="groupSelect">
                        <option></option>
                        <?php
                        // Populate the dropdown with group options from the database
                        $sql = "SELECT DISTINCT GroupName FROM vault WHERE UserEmailId = '$loggedinusermailid' AND DeleteFlag = 0 ";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            $groupOptions = $row['GroupName'];
                            $selected = ($oldgroupname == $groupOptions) ? 'selected' : '';
                            echo "<option value='$groupOptions' $selected>$groupOptions</option>";
                        }
                        ?>
                        <option>Other</option>
                    </select>
                </div>
                <div id="otherGroupInput" style="display: none;">
                    <div class="form-group">
                        <label for="altgroup">Enter New Group Name:</label>
                        <input type="text" class="form-control" id="altgroup" name="altgroup">
                    </div>
                </div>
                <div class="form-group">
                    <label for="AppName">App Name:</label>
                    <input type="text" class="form-control" id="AppName" name="AppName"
                        value="<?php echo $oldappname; ?>" required>
                </div>
                <div class="form-group">
                    <label for="UserName">User Name:</label>
                    <input type="text" class="form-control" id="UserName" name="UserName"
                        value="<?php echo $oldusername; ?>" required>
                </div>
                <div class="form-group">
                    <label for="Password">Password:</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="Password" name="Password"
                            value="<?php echo $oldPassword; ?>" required>
                        <a href='password_generator.php' style="margin-left: 10px;"> Generate Password </a>
                    </div>
                    <div class="input-group-append">
                        <input type="checkbox" id="showPasswordCheckbox"> <span>Show Password</span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="Url">Url:</label>
                    <input type="text" class="form-control" id="Url" name="Url" value="<?php echo $oldurl; ?>">
                </div>
                <div class="form-group">
                    <label for="Notes">Notes:</label>
                    <textarea class="form-control" id="Notes" name="Notes" rows="3"><?php echo $oldnotes; ?></textarea>
                </div>
                <br>
                <button type="submit" class="btn btn-primary" name="submitsave">Save</button>
                <a href='home.php' class='btn btn-light border border-dark'>Cancel</a>
            </form>
            <br>
            <a href='show_history.php'> Show History </a>
            <a href="#" data-toggle="modal" data-target="#shareAccountModal" style="margin-left: 20px;"> Share Account </a>
        </div>

    <!-- Share Account Modal -->
    <div class="modal fade" id="shareAccountModal" tabindex="-1" role="dialog"
        aria-labelledby="shareAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shareAccountModalLabel">Share Account with Users</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-striped">
                        <thead class="table-primary">
                            <tr>
                                <th class="bg-info">Email</th>
                                <th class="bg-info">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch and display user data from the database
                            $fetchsqlusers = "SELECT * FROM users WHERE EmailId != '$loggedinusermailid' AND ActiveFlag = '1' AND DeleteFlag = '0' ";
                            $fetchsqlusersresult = $conn->query($fetchsqlusers);

                            if ($fetchsqlusersresult->num_rows > 0) {
                                while ($users = $fetchsqlusersresult->fetch_assoc()) {
                                    $fetchedusedEmailId = $users["EmailId"];

                                    $fetchsqlshared = "SELECT * FROM shared_accounts WHERE sharedaccountuniqueid = '$UniqueId' AND fromsharedemailid = '$loggedinusermailid' AND tosharedemailid = '$fetchedusedEmailId' and deleteflag = 0 ";
                                    $fetchsqlsharedresult = $conn->query($fetchsqlshared);

                                    if ($fetchsqlsharedresult->num_rows > 0) {
                                        // User is already shared with, so display a message
                                        echo '<div class="form-check">';
                                        echo "<tr>";
                                        echo "<td>" . $fetchedusedEmailId . "</td>";
                                        echo '<td><button type="button" class="btn btn-secondary" disabled>Already Shared</button></td>';
                                        echo "</tr>";
                                        echo '</div>';
                                    } else {
                                        // User is not shared with, so display a Share button
                                        echo '<div class="form-check">';
                                        echo "<tr>";
                                        echo "<td>" . $fetchedusedEmailId . "</td>";
                                        echo "<td> <span> <a href = 'share_account.php?id=$UniqueId$fetchedusedEmailId' class='btn btn-success'> Share </a> </span> </td>";
                                        echo "</tr>";
                                        echo '</div>';
                                    }
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
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

    <!-- Overlay and spinner elements -->
    <div class="overlay" id="overlay"></div>
    <div class="spinner" id="spinner">
        <!-- You can use an actual spinner image or icon here -->
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

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

            // Handle sharing when a "Share" button is clicked
            $(".shareButton").click(function () {
                const toSharedEmail = $(this).data("email");

                // Make an AJAX request to share the account
                $.ajax({
                    type: "POST",
                    url: "share_account.php",
                    data: {
                        UniqueId: "<?php echo $UniqueId; ?>",
                        FromSharedEmailId: "<?php echo $loggedinusermailid; ?>",
                        ToSharedEmailId: toSharedEmail
                    },
                    success: function (response) {
                        if (response === "success") {
                            alert("Account shared successfully.");
                            $("#shareAccountModal").modal("hide");
                            window.location.href = 'edit_password.php?id=<?php echo $UniqueId; ?>';
                        } else {
                            alert("Error sharing account: " + response);
                        }
                    },
                    error: function (xhr, status, error) {
                        alert("Error sharing account: " + error);
                    }
                });
            });

            // Show or hide the "Other Group" input field based on the selected option
            const groupSelect = document.getElementById("groupSelect");
            const otherGroupInput = document.getElementById("otherGroupInput");
            groupSelect.addEventListener("change", function () {
                if (groupSelect.value === "Other") {
                    otherGroupInput.style.display = "block";
                } else {
                    otherGroupInput.style.display = "none";
                }
            });
        });

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
</body>

</html>
