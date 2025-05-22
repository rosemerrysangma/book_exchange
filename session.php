<?php
session_start();

// Set session timeout limit to 5 seconds
$timeout_duration = 90;

// Check for session timeout first
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php?message=Session expired. Please log in again.");
    exit();
}

// Update last activity time
$_SESSION['LAST_ACTIVITY'] = time();

// Then check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
