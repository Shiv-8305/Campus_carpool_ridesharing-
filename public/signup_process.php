<?php
session_start();
require_once '../includes/db_connection.php';

// Helper function to sanitize input
function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = sanitize($_POST['name'] ?? '');
    $admission_no = sanitize($_POST['admission_no'] ?? '');
    $gender = sanitize($_POST['gender'] ?? '');
    $department = sanitize($_POST['department'] ?? '');
    $year = intval($_POST['year'] ?? 0);
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Basic validation
    $errors = [];
    if (empty($name)) $errors[] = "Name is required.";
    if (empty($admission_no)) $errors[] = "Admission number is required.";
    if (!in_array($gender, ['Male', 'Female', 'Other'])) $errors[] = "Invalid gender selection.";
    if (empty($department)) $errors[] = "Department is required.";
    if ($year < 1 || $year > 5) $errors[] = "Year must be between 1 and 5.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $password_confirm) $errors[] = "Passwords do not match.";

    if (count($errors) > 0) {
        $_SESSION['signup_errors'] = $errors;
        $_SESSION['signup_data'] = $_POST;
        header('Location: signup.php');
        exit;
    }

    try {
        // Check if admission number already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE admission_no = ?");
        $stmt->execute([$admission_no]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['signup_errors'] = ["Admission number already registered."];
            $_SESSION['signup_data'] = $_POST;
            header('Location: signup.php');
            exit;
        }

        // Hash password securely
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $pdo->prepare("
            INSERT INTO users (name, admission_no, gender, department, year, password_hash)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $admission_no, $gender, $department, $year, $password_hash]);

        // Registration successful - redirect to login page with success message
        $_SESSION['signup_success'] = "Registration successful! Please login.";
        header('Location: login.php');
        exit;

    } catch (PDOException $e) {
        // Log the error and show generic message in production
        error_log("Signup error: " . $e->getMessage());
        $_SESSION['signup_errors'] = ["An unexpected error occurred. Please try again later."];
        header('Location: signup.php');
        exit;
    }
} else {
    // Invalid access method
    header('Location: signup.php');
    exit;
}
?>
