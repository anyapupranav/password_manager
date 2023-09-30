<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
if (isset($_POST['UpdateAccountInfo'])) {
    $newFirstName = $_POST['newFirstName'];
    $newLastName = $_POST['newLastName'];
    $newMobileNumber = $_POST['newMobileNumber'];

    // Update user information in the database
    $updateStmt = $conn->prepare("UPDATE users SET FirstName = ?, LastName = ?, MobileNumber = ? WHERE EmailId = ?");
    $updateStmt->bind_param("ssss", $newFirstName, $newLastName, $newMobileNumber, $userEmail);

    if ($updateStmt->execute()) {

        // Send notification(s) via mail if email notifications are enabled.
        $CheckUserEmailId = $_SESSION['passed_user_email'];

        $checknotificationssql = "SELECT * FROM notifications WHERE UserEmailId = '$CheckUserEmailId' ";
        $checknotificationsresult = $conn->query($checknotificationssql);
        if ($checknotificationsresult->num_rows > 0){
            while ($checknotificationsrow = $checknotificationsresult->fetch_assoc()){
                $NewAccountInfoUpdateFlag = $checknotificationsrow['AccountInfoUpdate'];
            }
        }

        if ($NewAccountInfoUpdateFlag == 1){

            // send mail if notifications is enabled
            $sqlAccountInfoUpdate = "SELECT * FROM message_templates WHERE TemplateName = 'update account details' and DeleteFlag = 0 ";
            $resultAccountInfoUpdate = $conn->query($sqlAccountInfoUpdate);
        
            if ($resultAccountInfoUpdate->num_rows > 0) {
                while($rowAccountInfoUpdate = $resultAccountInfoUpdate->fetch_assoc()){
                    $strsubject = $rowAccountInfoUpdate['Subject'];
                    $strmessagebody1 = $rowAccountInfoUpdate['Body1'];
                    $strmessagebody2 = $rowAccountInfoUpdate['Body2'];
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

        // Update successful, refresh user data
        $userData['FirstName'] = $newFirstName;
        $userData['LastName'] = $newLastName;
        $userData['MobileNumber'] = $newMobileNumber;
        $success = "User information update successful.";
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

// Handle Update 2FA to database Logic
if (isset($_POST['Save2fa'])) {

    $newFetchedTwoFactorAuthentication = $_POST['EnableDisable2FA'];
    if ($newFetchedTwoFactorAuthentication == "on")
        $newFetchedTwoFactorAuthentication = 1;
    else 
        $newFetchedTwoFactorAuthentication = 0;

    // Update 2fa information in the database
    $twofaupdateStmt = $conn->prepare("UPDATE login SET TwoFactorAuthentication = ? WHERE EmailId = ?");
    $twofaupdateStmt->bind_param("ss", $newFetchedTwoFactorAuthentication, $userEmail);
        
    if ($twofaupdateStmt->execute()) {
        // Update successful, refresh user data
        if ($newFetchedTwoFactorAuthentication == 1)
            $newFetchedTwoFactorAuthentication = "checked";
        else
            $newFetchedTwoFactorAuthentication = "unchecked";
            $twofauserData['TwoFactorAuthentication'] = $newFetchedTwoFactorAuthentication;
    }
}


// Handle Update Lockout Policy to database Logic
if (isset($_POST['SaveLockoutPolicy'])) {

    $newFetchedLockoutFlag = $_POST['EnableDisableLockoutPolicy'];
    if ($newFetchedLockoutFlag == "on")
        $newFetchedLockoutFlag = 1;
    else 
        $newFetchedLockoutFlag = 0;

    // Update 2fa information in the database
    $LockoutFlagupdateStmt = $conn->prepare("UPDATE login SET LockoutFlag = ? WHERE EmailId = ?");
    $LockoutFlagupdateStmt->bind_param("ss", $newFetchedLockoutFlag, $userEmail);
        
    if ($LockoutFlagupdateStmt->execute()) {
        // Update successful, refresh user data
        if ($newFetchedLockoutFlag == 1)
            $newFetchedLockoutFlag = "checked";
        else
            $newFetchedLockoutFlag = "unchecked";
            $LockoutFlaguserData['TwoFactorAuthentication'] = $newFetchedLockoutFlag;
    }
}


// Handle Update notifications
if (isset($_POST['SaveNotifications'])) {

    $newFetchedAccountInfoUpdate = $_POST['AccountInfoUpdate'];
    if ($newFetchedAccountInfoUpdate == "on")
        $newFetchedAccountInfoUpdate = 1;
    else 
        $newFetchedAccountInfoUpdate = 0;

    $newFetchedAccountLogin = $_POST['AccountLogin'];
    if ($newFetchedAccountLogin == "on")
        $newFetchedAccountLogin = 1;
    else 
        $newFetchedAccountLogin = 0;

    $newFetchedNewAccountAdded = $_POST['NewAccountAdded'];
    if ($newFetchedNewAccountAdded == "on")
        $newFetchedNewAccountAdded = 1;
    else 
        $newFetchedNewAccountAdded = 0;

    $newFetchedSharedWithOthers = $_POST['SharedWithOthers'];
    if ($newFetchedSharedWithOthers == "on")
        $newFetchedSharedWithOthers = 1;
    else 
        $newFetchedSharedWithOthers = 0;

    $newFetchedSharedWithYou = $_POST['SharedWithYou'];
    if ($newFetchedSharedWithYou == "on")
        $newFetchedSharedWithYou = 1;
    else 
        $newFetchedSharedWithYou = 0;

    // Update notifications information in the database
    $notificationsupdateStmt = $conn->prepare("UPDATE notifications SET AccountInfoUpdate = ?, AccountLogin = ?, NewAccountAdded = ?, SharedWithOthers = ?, SharedWithYou = ? WHERE UserEmailId = ?");
    $notificationsupdateStmt->bind_param("ssssss", $newFetchedAccountInfoUpdate, $newFetchedAccountLogin, $newFetchedNewAccountAdded, $newFetchedSharedWithOthers, $newFetchedSharedWithYou, $userEmail);
    
    if ($notificationsupdateStmt->execute()) {
         // Update successful, refresh user data
        if ($newFetchedAccountInfoUpdate == 1)
            $newFetchedAccountInfoUpdate = "checked";
        else
            $newFetchedAccountInfoUpdate = "unchecked";
        $notificationsuserData['AccountInfoUpdate'] = $newFetchedAccountInfoUpdate;

        if ($newFetchedAccountLogin == 1)
            $newFetchedAccountLogin = "checked";
        else
            $newFetchedAccountLogin = "unchecked";
        $notificationsuserData['AccountLogin'] = $newFetchedAccountLogin;

        if ($newFetchedNewAccountAdded == 1)
            $newFetchedNewAccountAdded = "checked";
        else
            $newFetchedAccountInfoUpdate = "unchecked";
        $notificationsuserData['NewAccountAdded'] = $newFetchedNewAccountAdded;

        if ($newFetchedSharedWithOthers == 1)
            $newFetchedSharedWithOthers = "checked";
        else
            $newFetchedAccountInfoUpdate = "unchecked";
        $notificationsuserData['SharedWithOthers'] = $newFetchedSharedWithOthers;

        if ($newFetchedSharedWithYou == 1)
            $newFetchedSharedWithYou = "checked";
        else
            $newFetchedSharedWithYou = "unchecked";
        $notificationsuserData['SharedWithYou'] = $newFetchedSharedWithYou;

        $success = "Preferences saved successfully.";
    } else {
        // Handle the case where the update fails
        $error = "Failed to update preferences.";
    }

}

// Handle change password logic
if (isset($_POST['changeacountpasswordsubmit'])) {

    $userEmail = $_SESSION['passed_user_email'];

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
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | Password Manager</title>
    <link rel = "icon" href = "img/titleicon.png" type = "image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .navbar-nav {
            margin-left: auto;
        }

        /* Custom CSS for layout */
        .container {
            display: flex;
            justify-content: flex-start;
        }

        .nav-pills {
            flex: 0 0 auto;
            width: 250px; /* Adjust the width as needed */
        }

        .content {
            flex-grow: 1;
            padding: 15px;
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
</head>
<body>

<?php include 'themenav0.php'; ?>

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

<?php include 'themenav1.php'; ?>

    <hr>
    <div class="container">
        <!-- Vertical nav-pills -->
        <ul class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
            <li class="nav-item">
                <a class="nav-link active" id="v-pills-profile-tab" data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="true">Profile</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="v-pills-messages-tab" data-toggle="pill" href="#v-pills-messages" role="tab" aria-controls="v-pills-messages" aria-selected="false">Notifications</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="v-pills-DataTakeOut-tab" data-toggle="pill" href="#v-pills-DataTakeOut" role="tab" aria-controls="v-pills-DataTakeOut" aria-selected="false">Data Takeout</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="v-pills-settings-tab" data-toggle="pill" href="#v-pills-settings" role="tab" aria-controls="v-pills-settings" aria-selected="false">Security</a>
            </li>
        </ul>
        <!-- End of vertical nav-pills -->

        <!-- Content area -->
        <div class="content">
            <?php if (isset($error)) { ?>
            <div class="alert alert-danger alert-dismissible" role="alert"><?php echo $error; ?></div>
            <?php } ?>
            <?php if (isset($success)) { ?>
            <div class="alert alert-success alert-dismissible" role="alert"><?php echo $success; ?></div>
            <?php } ?>

            <!-- Profile tab content -->
            <div class="tab-pane fade show active" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                    <div class="card-header d-flex align-items-center justify-content-center">
                        <h3>My Account</h3>
                    </div>
                    <div class="card-body">
                        <!-- Profile content here -->
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
                                <button type="submit" name="UpdateAccountInfo" class="btn btn-primary mt-3">Update Information</button>
                            </div>
                        </form>
                        <!-- End of Profile content -->
                    </div>
            </div>

            <!-- Notifications tab content -->
            <div class="tab-pane fade" id="v-pills-messages" role="tabpanel" aria-labelledby="v-pills-messages-tab">
                <h2> Notifications </h2>    
                
                <?php

                    $userEmail = $_SESSION['passed_user_email'];

                    $Notificationstmt = $conn->prepare("SELECT * FROM notifications WHERE UserEmailId = ? ");
                    $Notificationstmt->bind_param("s", $userEmail);
                    $Notificationstmt->execute();
                    $Notificationresult = $Notificationstmt->get_result();

                    if ($Notificationresult->num_rows === 1) {
                        $NotificationuserData = $Notificationresult->fetch_assoc();

                        if ($NotificationuserData['AccountInfoUpdate'] == 1){
                            $FetchedAccountInfoUpdate = "checked";
                        }
                        else {
                            $FetchedAccountInfoUpdate = "unchecked";
                        }

                        if ($NotificationuserData['AccountLogin'] == 1){
                            $FetchedAccountLogin = "checked";
                        }
                        else {
                            $FetchedAccountLogin = "unchecked";
                        }

                        if ($NotificationuserData['NewAccountAdded'] == 1){
                            $FetchedNewAccountAdded = "checked";
                        }
                        else {
                            $FetchedNewAccountAdded = "unchecked";
                        }

                        if ($NotificationuserData['SharedWithOthers'] == 1){
                            $FetchedSharedWithOthers = "checked";
                        }
                        else {
                            $FetchedSharedWithOthers = "unchecked";
                        }

                        if ($NotificationuserData['SharedWithYou'] == 1){
                            $FetchedSharedWithYou = "checked";
                        }
                        else {
                            $FetchedSharedWithYou = "unchecked";
                        }

                    } else {
                        // Handle the case where user data is not found
                        echo "data not found.";
                        exit();
                    }

                ?>

                <form action="myaccount.php" method="post">
                <h5 style="margin-left: 3%;"> Account Security </h5>

                    <input style="margin-left: 6%;" name="AccountInfoUpdate" type="checkbox" <?php echo $FetchedAccountInfoUpdate; ?> > Recieve notifications if account information has change/modified or deleted. <br>
                    <input style="margin-left: 6%;" name="AccountLogin" type="checkbox" <?php echo $FetchedAccountLogin; ?> > Recieve notifications everytime you login into your account. <br>

                <h5 style="margin-left: 3%;"> Other Notifications </h5>

                    <input style="margin-left: 6%;" name="NewAccountAdded" type="checkbox" <?php echo $FetchedNewAccountAdded; ?> > Recieve notifications if a new account/password is added/modified/deleted. <br>
                    <input style="margin-left: 6%;" name="SharedWithOthers" type="checkbox" <?php echo $FetchedSharedWithOthers; ?> > Recieve notifications when you share any account/password with others. <br>
                    <input style="margin-left: 6%;" name="SharedWithYou" type="checkbox" <?php echo $FetchedSharedWithYou; ?> > Recieve notifications when others share account/password with you. <br></br>
                    <input style="margin-left: 3%;" name="SaveNotifications" type="submit" value="Save" class="btn btn-primary">
                    <a href="" class="btn btn-secondary" style="margin-left: 1%;" >Cancel</a>
                </form>
                    <!-- End of Notifications form -->
            </div>

            <!-- DataTakeOut tab content -->
            <div class="tab-pane fade" id="v-pills-DataTakeOut" role="tabpanel" aria-labelledby="v-pills-DataTakeOut-tab">
                <!-- Content for DataTakeOut tab -->
                <h4> This feature is under developement </h4>
            </div>

            <!-- Security tab content -->
            <div class="tab-pane fade" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">
                <!-- Content for Security tab -->
                <h2> Security </h2>
                <hr> 
                <h5> Change Account Password </h5>
                <div class="changeaccountpassword">
                    <form action="myaccount.php" method="POST">
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
                            <button type="submit" name="changeacountpasswordsubmit" class="btn btn-primary">Change Password</button>
                            <a href='myaccount.php' class='btn btn-light' style='margin-left: 30px;'>Cancel</a>
                        </div>
                    </form>
                </div>

                <hr> 
                <h5> Manage Encryption </h5>
                <div class="ManageEncryption">
                    Manage encryption is under development.
                </div>
                <hr>
                <h5> Two Factor Authentication (2FA) </h5>

                <?php

                    $userEmail = $_SESSION['passed_user_email'];

                    $twofastmt = $conn->prepare("SELECT * FROM login WHERE EmailId = ? ");
                    $twofastmt->bind_param("s", $userEmail);
                    $twofastmt->execute();
                    $twofaresult = $twofastmt->get_result();

                    if ($twofaresult->num_rows === 1) {
                        $twofauserData = $twofaresult->fetch_assoc();

                        if ($twofauserData['TwoFactorAuthentication'] == 1){
                            $FetchedTwoFactorAuthentication = "checked";
                        }
                        else {
                            $FetchedTwoFactorAuthentication = "unchecked";
                        }
                    } else {
                        // Handle the case where user data is not found
                        echo "data not found.";
                        exit();
                    }
                ?>

                <div class="2fa">
                    <form action="myaccount.php" method="POST">
                        <input style="margin-left: 4%;" name="EnableDisable2FA" type="checkbox" <?php echo $FetchedTwoFactorAuthentication; ?> > Enable Two Factor Authentication (2FA) via mail <br>
                        <input style="margin-left: 6%;" name="Save2fa" type="submit" value="Save" class="btn btn-primary">
                    </form>
                </div>
                <hr>
                <h5> Lockout Policy </h5>
                <?php

                    if ($twofauserData['LockoutFlag'] == 1){
                        $FetchedLockoutFlag = "checked";
                    }
                    else {
                        $FetchedLockoutFlag = "unchecked";
                    }

                ?>
                <div class="LockoutPolicy">
                    <form action="myaccount.php" method="POST">
                        <input style="margin-left: 4%;" name="EnableDisableLockoutPolicy" type="checkbox" <?php echo $FetchedLockoutFlag; ?> > Enable Account Lockout Policy <i style="font-size:16px; color:blue;" class="fa">&#xf05a;</i> <br>
                        <input style="margin-left: 6%;" name="SaveLockoutPolicy" type="submit" value="Save" class="btn btn-primary">
                    </form>
                </div>
                <hr>
            </div>
        </div>
        <!-- End of content area -->
    </div>
    <!-- End of container -->


    <footer>
        <div class="row">
            <div class="col-md-6">
                <p style="margin-left: 25%;"> Password Manager </p>
            </div>
            <div class="col-md-6">
                <p style="margin-left: 65%;">
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
    </footer>

    <!-- Overlay and spinner elements -->
    <div class="overlay" id="overlay"></div>
    <div class="spinner" id="spinner">
        <!-- You can use an actual spinner image or icon here -->
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function () {
            // Hide the Notifications tab content initially
            $("#v-pills-messages").hide();
            $("#v-pills-DataTakeOut").hide();
            $("#v-pills-settings").hide();

            // Add click event handlers for the tab links
            $("#v-pills-profile-tab").click(function () {
                $("#v-pills-profile").show();
                $("#v-pills-messages").hide();
                $("#v-pills-DataTakeOut").hide();
                $("#v-pills-settings").hide();
            });

            $("#v-pills-messages-tab").click(function () {
                $("#v-pills-profile").hide();
                $("#v-pills-DataTakeOut").hide();
                $("#v-pills-settings").hide();
                $("#v-pills-messages").show();
            });

            $("#v-pills-DataTakeOut-tab").click(function () {
                $("#v-pills-profile").hide();
                $("#v-pills-messages").hide();
                $("#v-pills-DataTakeOut").show();
                $("#v-pills-settings").hide();
            });

            $("#v-pills-settings-tab").click(function () {
                $("#v-pills-profile").hide();
                $("#v-pills-messages").hide();
                $("#v-pills-DataTakeOut").hide();
                $("#v-pills-settings").show();
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
