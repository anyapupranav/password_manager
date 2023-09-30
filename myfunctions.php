<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Encryption function
function encryptString($inputString, $encryptionKey) {

    // Check if the input string is within the specified length limit
    if (strlen($inputString) > 255) {
        return false;
    }

    // Generate a random initialization vector (IV)
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));

    // Encrypt the input string using AES-256 encryption
    $encryptedString = openssl_encrypt($inputString, 'aes-256-cbc', $encryptionKey, 0, $iv);

    // Combine IV and encrypted string
    $encryptedData = $iv . $encryptedString;

    // Encode the result to make it safe for storage or transmission
    return base64_encode($encryptedData);
}

// Decryption function
function decryptString($encryptedString, $encryptionKey) {

    // Decode the base64 encoded input
    $encryptedData = base64_decode($encryptedString);

    // Extract the IV (first 16 bytes)
    $iv = substr($encryptedData, 0, 16);

    // Extract the encrypted string (remaining bytes)
    $encryptedString = substr($encryptedData, 16);

    // Decrypt the string using AES-256 decryption
    $decryptedString = openssl_decrypt($encryptedString, 'aes-256-cbc', $encryptionKey, 0, $iv);

    // Return the decrypted string
    return $decryptedString;
}

//send email when an user signup
function sendSignupEmail($signUpEmailId, $SignUpLastName, $SignUpFirstName) {

    include 'sql_conn.php';

    $sqltemplates = "SELECT * FROM message_templates WHERE TemplateName = 'welcome mail' and DeleteFlag = 0 ";
    $resulttemplates = $conn->query($sqltemplates);

    if ($resulttemplates->num_rows > 0) {
        while($row = $resulttemplates->fetch_assoc()){
            $strsubject = $row['Subject'];
            $strmessage1 = $row['Body1'];
            $strmessage2 = $row['Body2'];
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

    $mail->addAddress($signUpEmailId);
    $mail->isHTML (true);
    $mail->Subject = $strsubject;
    $mail->Body =  $strmessage1 . $SignUpFirstName .'</li> <li><b>Last Name:</b> '. $SignUpLastName .'</li><li><b>Email:</b> '. $signUpEmailId . $strmessage2;
    $mail->send();

    $conn->close();
}

// send email when forgot password is initiated
function sendForgotPasswordEmail($userEmail, $emailSubject, $emailMessage) {

    include 'sql_conn.php';

    $sqlForgotPassword = "SELECT * FROM message_templates WHERE TemplateName = 'otp mail' and DeleteFlag = 0 ";
    $resultForgotPassword = $conn->query($sqlForgotPassword);

    if ($resultForgotPassword->num_rows > 0) {
        while($row = $resultForgotPassword->fetch_assoc()){
            $strsubject = $row['Subject'];
            $strmessagebody1 = $row['Body1'];
            $strmessagebody2 = $row['Body2'];
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

    $mail->addAddress($userEmail);
    $mail->isHTML (true);
    $mail->Subject = $emailSubject;
    $mail->Body =  $strmessagebody1 .''. $emailMessage .''. $strmessagebody2;
    $mail->send();

    $conn->close();

}

// Generate new encryption key for registerd new users
 function generateEncryptionkey() {
    $count = 0;
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#%&*-_=+?';
    $randomString = '';

    for ($i = 0; $i < 20; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    include 'sql_conn.php';

    $selectencstmt = $conn->prepare("SELECT * FROM encryption ");
    $selectencstmt->execute();
    $selectencresult = $selectencstmt->get_result();

    if ($selectencresult->num_rows === 1) {
      $selectencdata = $selectencresult->fetch_assoc();

      // check if this encryption key is being used by someone
      if ($selectencdata['EncryptionKey'] == $randomString){
        $count = $count + 1;
        } else{}
    }

    if ($count > 0 || strlen($randomString) < 20 ){
        // closing connection before calling function recursively to avoid reaching maximum sql connections 
        $selectencstmt->close();
        $conn->close();

        generateEncryptionkey();
    } else{
        return $randomString;
    }  
 }

?>