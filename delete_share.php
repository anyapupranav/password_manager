<?php
// Database connection
include 'sql_conn.php';

// Check if the ID is provided in the query parameter
if (isset($_GET['id'])) {
    $posteddata = $_GET['id'];
    $ToSharedEmailId = substr($posteddata,36);
    $UniqueId = str_replace($ToSharedEmailId,"",$posteddata);

    // Perform the deletion
    $sql = "update shared_accounts set deleteflag = 1 where sharedaccountuniqueid = '$UniqueId' and tosharedemailid = '$ToSharedEmailId' ";

    if ($conn->query($sql) === TRUE) {
        // Deletion successful
        echo '<script type="text/javascript">'; 
        echo 'alert("Share has been deleted successfully.");'; 
        echo 'window.location.href = "shared_passwords.php";';
        echo '</script>';
    } else {
        // Error during deletion
        echo '<script type="text/javascript">'; 
        echo 'alert("An error occured while removing shared account.");'; 
        echo 'window.location.href = "shared_passwords.php";';
        echo '</script>';
    }
} else {
    echo include 'error/400.html';
    exit;
}

// Close the database connection
$conn->close();
?>