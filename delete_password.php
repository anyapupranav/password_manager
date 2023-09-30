<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
include 'sql_conn.php';

// Check if the ID is provided in the query parameter
if (isset($_GET['id'])) {
    $UniqueId = $_GET['id'];

    $loggedinusermailid = $_SESSION['passed_user_email'];

    // Perform the deletion
    $sql = "update vault set deleteflag = 1 where UniqueId = '$UniqueId' and UserEmailId = '$loggedinusermailid' ";

    if ($conn->query($sql) === TRUE) {

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
            $sqlNewAccountAdded = "SELECT * FROM message_templates WHERE TemplateName = 'delete account' and DeleteFlag = 0 ";
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

        // Deletion successful
        echo '<script type="text/javascript">'; 
        echo 'alert("Account has been deleted successfully.");'; 
        echo 'window.location.href = "home.php";';
        echo '</script>';
    } else {
        // Error during deletion
        echo '<script type="text/javascript">'; 
        echo 'alert("An error occured while deleting Account.");'; 
        echo 'window.location.href = "home.php";';
        echo '</script>';
    }
} else {
    echo include 'error/400.html';
    exit;
}

// Close the database connection
$conn->close();
?>


