<?php
ob_start();
session_start();

// Define the allowed username (partner email)
$allowed_user = 'partner1@gmail.com';

// Check if the user is logged in and is the allowed user
if (!isset($_SESSION['username']) || $_SESSION['username'] !== $allowed_user) {
    // If not logged in or the username doesn't match, redirect to the login page
    header("Location: /dev/100/index.php");
    exit();
}

// Optional: Regenerate session ID periodically for added security
session_regenerate_id(true);
?>