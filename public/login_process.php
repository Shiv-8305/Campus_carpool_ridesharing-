<?php
session_start();
require_once '../includes/db_connection.php';

// Helper function to sanitize input
function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admission_no = sanitize($_POST['admission_no'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($admission_no) || empty($password)) {
        $_SESSION['login_error'] = "Both fields are required.";
        header('Location: login.php');
        exit;
    }

    try {
        // Look up user by admission_no
        $stmt = $pdo->prepare("SELECT id, name, password_hash FROM users WHERE admission_no = ?");
        $stmt->execute([$admission_no]);
        $user = $stmt->fetch();

        if (!$user) {
            $_SESSION['login_error'] = "Invalid admission number or password.";
            header('Location: login.php');
            exit;
        }

        if (password_verify($password, $user['password_hash'])) {
            // Password matches, create session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            // Redirect to dashboard
            header('Location: dashboard.php');
            exit;
        } else {
            $_SESSION['login_error'] = "Invalid admission number or password.";
            header('Location: login.php');
            exit;
        }

    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['login_error'] = "An unexpected error occurred. Please try again later.";
        header('Location: login.php');
        exit;
    }
} else {
    // Prevent access through GET request
    header('Location: login.php');
    exit;
}
?>
