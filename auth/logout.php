<?php
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Clear any remember me cookies if they exist
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to login page with logout message
header("Location: login.php?message=You have been logged out successfully");
exit;
?>
