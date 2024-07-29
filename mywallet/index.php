<?php
session_start();

// Check if the user is already logged in, if yes then redirect to dashboard
if (isset($_SESSION['username'])) {
    header("location: dashboard.php");
    exit;
}

// Otherwise, you can include content or redirection to login page
header("location: ../login.php");
exit;
?>

