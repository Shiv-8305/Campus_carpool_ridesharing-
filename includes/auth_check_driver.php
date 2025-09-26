<?php
session_start();
if (!isset($_SESSION['driver_id'])) {
    // Not logged in as driver, redirect to driver login page
    header('Location: driver_login.php');
    exit;
}
// Optionally you can define $driver_id var here for convenience
$driver_id = $_SESSION['driver_id'];
?>
