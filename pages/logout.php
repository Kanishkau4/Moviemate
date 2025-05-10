<?php
// Start the session
session_start();

// Destroy all session data to log the user out
session_unset();
session_destroy();

// Redirect to the homepage or login page
header("Location: index.php"); // You can change this to login.php if you prefer
exit();
?>
