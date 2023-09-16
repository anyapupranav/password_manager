<?php

  // User is logged in, perform logout
  session_unset(); // Unset all session variables
  session_destroy(); // Destroy the session

// Redirect the user to the login page
header("Location: index.html");
exit();
?>