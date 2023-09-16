<?php
// Include database connection
include 'sql_conn.php';

if (isset($_GET['id'])) {

    $posteddata = $_GET['id'];

    $FromSharedEmailId = $_SESSION['passed_user_email'];
    $ToSharedEmailId = substr($posteddata,36);
    $UniqueId = str_replace($ToSharedEmailId,"",$posteddata);

    // Check if the account is already shared
    $checkSql = "SELECT * FROM shared_accounts WHERE sharedaccountuniqueid = '$UniqueId' AND fromsharedemailid = '$FromSharedEmailId' AND tosharedemailid = '$ToSharedEmailId' and deleteflag = 0 ";
    $checkResult = $conn->query($checkSql);

    if ($checkResult->num_rows == 0) {
        // Insert into shared_accounts table
        $insertSql = "INSERT INTO shared_accounts (sharedaccountuniqueid, fromsharedemailid, tosharedemailid) VALUES ('$UniqueId', '$FromSharedEmailId', '$ToSharedEmailId')";
        
        if ($conn->query($insertSql) === TRUE) {
            echo '<script type="text/javascript">'; 
            echo 'alert("Account shared successfully!");'; 
            echo "window.location.href = 'edit_password.php?id=$UniqueId'";
            echo '</script>';
        } else {
            echo '<script type="text/javascript">'; 
            echo 'alert("Error sharing account.");'; 
            echo "window.location.href = 'edit_password.php?id=$UniqueId'";
            echo '</script>';
        }
    } else {
        echo '<script type="text/javascript">'; 
        echo 'alert("Account is already shared with this user.");'; 
        echo "window.location.href = 'edit_password.php?id=$UniqueId'";
        echo '</script>';
    }
} else {
    echo "Invalid request.";
}

// Close the database connection
$conn->close();
?>
