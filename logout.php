<?php
session_start();

// Unset all of the session variables
$_SESSION = array();

// If the session cookie exists, delete it
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Remove the "Remember Me" cookie if it exists
if (isset($_COOKIE['rememberme'])) {
    // Clear the rememberme cookie
    setcookie('rememberme', '', time() - 3600, '/', 'fdm91.net', true, true);
}

// Redirect to the login page
header("location: ../login.php");
exit;
?>
