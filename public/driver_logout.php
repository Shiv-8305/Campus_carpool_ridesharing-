<?php
session_start();

// Unset all driver session variables
unset($_SESSION['driver_id']);
unset($_SESSION['driver_name']);
unset($_SESSION['driver_phone']);
unset($_SESSION['driver_logged_in']);

// Destroy the session
session_destroy();

// Redirect to home page
header('Location: ../public/index.php');
exit;
?>