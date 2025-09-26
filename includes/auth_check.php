<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // Not logged in, redirect to login page
    header('Location: ../public/login.php');
    exit;
}
?>
