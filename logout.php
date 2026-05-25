<?php
// Start session
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page with a message
$_SESSION['alert'] = "You have been logged out.";
header('Location: login.php');
exit();
?>
