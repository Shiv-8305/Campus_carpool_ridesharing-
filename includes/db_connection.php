<?php
// includes/db_connection.php

// Database credentials
$host = 'localhost';
$dbname = 'ride_sharing_db';
$username = 'root';
$password = '';

// Set PDO options for error handling and UTF-8 encoding
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_PERSISTENT => true
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
} catch (PDOException $e) {
    // In production, don't echo error details; log instead
    echo "Database connection failed: " . $e->getMessage();
    exit;
}
?>
