<?php
session_start();
require_once '../includes/db_connection.php';

// Acquire POST inputs and sanitize
$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';

// Validate
if (!$name || !$phone || !$password) {
    $_SESSION['driver_login_error'] = 'Please fill in all fields.';
    header('Location: driver_login.php');
    exit;
}

// Lookup driver by name and phone
$stmt = $pdo->prepare("SELECT * FROM drivers WHERE name = ? AND phone = ?");
$stmt->execute([$name, $phone]);
$driver = $stmt->fetch();

if (!$driver) {
    $_SESSION['driver_login_error'] = 'Invalid name or phone.';
    header('Location: driver_login.php');
    exit;
}

// Password check (password stored in plain text)
if ($password !== $driver['password']) {
    $_SESSION['driver_login_error'] = 'Incorrect password.';
    header('Location: driver_login.php');
    exit;
}

// Login success: save driver info into session
$_SESSION['driver_id'] = $driver['id'];
$_SESSION['driver_name'] = $driver['name'];

// Redirect to driver dashboard or profile page
header('Location: driver_dashboard.php');
exit;
