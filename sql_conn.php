<?php
date_default_timezone_set('Asia/Kolkata');
// Database connection (replace with your credentials)
    $servername = "127.0.0.1";
    $username = "kishoreweb";
    $password = "password@123";
    $dbname = "password_manager";

    $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
?>