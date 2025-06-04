<?php
session_start();

// Clear session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Unset the PHPSESSID cookie
if (isset($_COOKIE['PHPSESSID'])) {
    setcookie('PHPSESSID', '', time() - 3600, '/', 'comeback24.de', true, true);
}

// Redirect to login or homepage
header("Location: login.php");
exit();
?>
