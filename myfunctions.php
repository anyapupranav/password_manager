<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


// Encryption function
function encryptString($inputString) {

    $encryptionKey = "YourEncryptionKey";

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
function decryptString($encryptedString) {

    $encryptionKey = "YourEncryptionKey";

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

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    $mail->Username = 'email@mail.com'; // Your gmail
    $mail->Password = 'password'; // Your gmail app password

    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    $mail->setFrom('email@mail.com'); // Your gmail

    $mail->addAddress($signUpEmailId);
    $mail->isHTML (true);
    $mail->Subject = $strsubject;
    $mail->Body =  $strmessage1 . $SignUpFirstName .'</li> <li><b>Last Name:</b> '. $SignUpLastName .'</li><li><b>Email:</b> '. $signUpEmailId . $strmessage2;
    $mail->send();
}

// send email when forgot password is initiated
function sendForgotPasswordEmail($userEmail, $emailSubject, $emailMessage) {

    include 'sql_conn.php';

    $sqlForgotPassword = "SELECT * FROM message_templates WHERE TemplateName = 'otp mail' and DeleteFlag = 0 ";
    $resultForgotPassword = $conn->query($sqlForgotPassword);

    if ($resultForgotPassword->num_rows > 0) {
        while($row = $resultForgotPassword->fetch_assoc()){
            $strmessagebody1 = $row['Body1'];
            $strmessagebody2 = $row['Body2'];
        }
    }

    // send mail

    require 'phpmailer/src/Exception.php';
    require 'phpmailer/src/PHPMailer.php';
    require 'phpmailer/src/SMTP.php';

    $mail = new PHPMailer (true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    $mail->Username = 'email@mail.com'; // Your gmail
    $mail->Password = 'password'; // Your gmail app password

    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    $mail->setFrom('email@mail.com'); // Your gmail

    $mail->addAddress($userEmail);
    $mail->isHTML (true);
    $mail->Subject = $emailSubject;
    $mail->Body =  $strmessagebody1 .''. $emailMessage .''. $strmessagebody2;
    $mail->send();

}
?>