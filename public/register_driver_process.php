<?php
session_start();
require_once '../includes/db_connection.php';

function sanitize($data) {
    return htmlspecialchars(trim($data));
}

// Allowed image mime types for upload
$allowed_mime_types = ['image/jpeg', 'image/png', 'image/jpg'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $aadhar = sanitize($_POST['aadhar'] ?? '');
    $license = sanitize($_POST['license'] ?? '');
    $languages = sanitize($_POST['languages'] ?? '');
    $car_model = sanitize($_POST['car_model'] ?? '');
    $car_capacity = intval($_POST['car_capacity'] ?? 0);
    $car_number = strtoupper(sanitize($_POST['car_number'] ?? ''));
    $price_per_km = floatval($_POST['price_per_km'] ?? 0);
    $outstation = intval($_POST['outstation'] ?? 0); // 0 or 1

    $errors = [];

    // Basic validations
    if (empty($name)) $errors[] = "Name is required.";
    if (!preg_match('/^\d{10}$/', $phone)) $errors[] = "Phone number must be 10 digits.";
    if (!preg_match('/^\d{12}$/', $aadhar)) $errors[] = "Aadhar number must be 12 digits.";
    if (empty($license)) $errors[] = "License number is required.";
    if (empty($car_model)) $errors[] = "Car model is required.";
    if ($car_capacity <= 0) $errors[] = "Car capacity must be greater than zero.";
    if (empty($car_number)) $errors[] = "Car number is required.";
    if ($price_per_km <= 0) $errors[] = "Price per KM must be greater than zero.";
    if ($outstation !== 0 && $outstation !== 1) $outstation = 0; // sanitize

    // Handle photo upload if provided
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['photo'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Error uploading photo.";
        } elseif (!in_array(mime_content_type($file['tmp_name']), $allowed_mime_types)) {
            $errors[] = "Invalid photo format. Only JPG and PNG allowed.";
        } else {
            // Generate a unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'driver_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
            $target_path = __DIR__ . '/../assets/images/' . $filename;

            if (!move_uploaded_file($file['tmp_name'], $target_path)) {
                $errors[] = "Failed to move uploaded photo.";
            } else {
                $photo_path = $filename;
            }
        }
    }

    if (count($errors) > 0) {
        $_SESSION['register_driver_errors'] = $errors;
        $_SESSION['register_driver_data'] = $_POST;
        header('Location: register_driver.php');
        exit;
    }

    try {
        // Prevent duplicate license registration
        $stmtCheck = $pdo->prepare("SELECT id FROM drivers WHERE license = ?");
        $stmtCheck->execute([$license]);
        if ($stmtCheck->rowCount() > 0) {
            $_SESSION['register_driver_errors'] = ["License number already registered."];
            $_SESSION['register_driver_data'] = $_POST;
            header('Location: register_driver.php');
            exit;
        }

        // Insert driver record
        $stmtInsert = $pdo->prepare("
            INSERT INTO drivers 
            (name, phone, photo_path, aadhar, license, languages, car_model, car_capacity, car_number, price_per_km, outstation) 
            VALUES 
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtInsert->execute([
            $name, $phone, $photo_path, $aadhar, $license, $languages, $car_model, $car_capacity, $car_number, $price_per_km, $outstation
        ]);

        $_SESSION['register_driver_success'] = "Driver registered successfully!";
        header('Location: drivers.php');
        exit;

    } catch (PDOException $e) {
        error_log("Register Driver error: " . $e->getMessage());
        $_SESSION['register_driver_errors'] = ["An unexpected error occurred. Please try again later."];
        $_SESSION['register_driver_data'] = $_POST;
        header('Location: register_driver.php');
        exit;
    }
} else {
    header('Location: register_driver.php');
    exit;
}
