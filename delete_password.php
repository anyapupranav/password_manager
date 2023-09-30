<?php
// Database connection
include 'sql_conn.php';

// Check if the ID is provided in the query parameter
if (isset($_GET['id'])) {
    $UniqueId = $_GET['id'];

    $loggedinusermailid = $_SESSION['passed_user_email'];

    // Perform the deletion
    $sql = "update vault set deleteflag = 1 where UniqueId = '$UniqueId' and UserEmailId = '$loggedinusermailid' ";

    if ($conn->query($sql) === TRUE) {
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
    echo "Invalid request. No ID provided.";
}

// Close the database connection
$conn->close();
?>
