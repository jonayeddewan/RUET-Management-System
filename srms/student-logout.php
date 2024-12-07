<?php
session_start(); 
$_SESSION = array(); // Clear all session variables

// Check if cookies are being used and delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 60*60, // Set expiration in the past
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Remove the specific session variable if it exists
unset($_SESSION['login']); 

// Destroy the session
session_destroy(); 

// Redirect to the student login page
header("location:index.php"); 
exit();
?>
