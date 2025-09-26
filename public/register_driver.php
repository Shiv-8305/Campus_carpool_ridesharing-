<?php
session_start();
require_once '../includes/db_connection.php';

$errors = [];
$success = '';
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $aadhar = trim($_POST['aadhar'] ?? '');
    $license = trim($_POST['license'] ?? '');
    $languages = trim($_POST['languages'] ?? '');
    $car_model = trim($_POST['car_model'] ?? '');
    $car_capacity = trim($_POST['car_capacity'] ?? '');
    $car_number = trim($_POST['car_number'] ?? '');
    $price_per_km = trim($_POST['price_per_km'] ?? '');
    $outstation = ($_POST['outstation'] ?? '') === 'yes' ? 'yes' : 'no';

    // Validation
    if (empty($name)) $errors[] = "Driver name is required";
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = "Phone number must be exactly 10 digits";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($aadhar)) {
        $errors[] = "Aadhar number is required";
    } elseif (!preg_match('/^[0-9]{12}$/', $aadhar)) {
        $errors[] = "Aadhar number must be exactly 12 digits";
    }
    
    if (empty($license)) $errors[] = "Driving license number is required";
    if (empty($car_model)) $errors[] = "Car model is required";
    if (empty($car_capacity)) $errors[] = "Car capacity is required";
    if (empty($car_number)) $errors[] = "Car number is required";
    if (empty($price_per_km)) $errors[] = "Price per KM is required";

    // Check if phone, aadhar, license, or car number already exist
    if (empty($errors)) {
        try {
            $check_stmt = $pdo->prepare("SELECT id FROM drivers WHERE phone = ? OR aadhar = ? OR license = ? OR car_number = ?");
            $check_stmt->execute([$phone, $aadhar, $license, $car_number]);
            $existing = $check_stmt->fetch();
            
            if ($existing) {
                $errors[] = "Driver with same phone, aadhar, license, or car number already exists";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }

    // If no errors, register the driver
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO drivers (name, phone, password, aadhar, license, languages, car_model, car_capacity, car_number, price_per_km, outstation) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $name, 
                $phone, 
                $password,
                $aadhar, 
                $license, 
                $languages, 
                $car_model, 
                $car_capacity, 
                $car_number, 
                $price_per_km, 
                $outstation
            ]);
            
            $success = "Driver registered successfully! You can now login with your credentials.";
            
            // Clear form data on success
            $form_data = [];
            
        } catch (PDOException $e) {
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    } else {
        // Keep form data for re-population
        $form_data = $_POST;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Registration - Ride Sharing</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .registration-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 1000px;
            overflow: hidden;
        }

        .registration-header {
            background: linear-gradient(135deg, #7c45a9 0%, #5c2487 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .registration-header h1 {
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
        }

        .registration-header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .registration-body {
            padding: 2rem;
            max-height: 70vh;
            overflow-y: auto;
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            text-align: center;
        }

        .alert-error {
            background: #fee;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        input, select {
            padding: 0.75rem 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #7c45a9;
            background: white;
            box-shadow: 0 0 0 3px rgba(124, 69, 169, 0.1);
        }

        .password-hint {
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.25rem;
            font-style: italic;
        }

        .btn-group {
            grid-column: 1 / -1;
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1rem;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            min-width: 150px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #7c45a9 0%, #5c2487 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(124, 69, 169, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
            transform: translateY(-2px);
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
        }

        .login-link a {
            color: #7c45a9;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .registration-body {
                padding: 1.5rem;
                max-height: none;
                overflow-y: visible;
            }
            
            .registration-header {
                padding: 1.5rem;
            }
            
            .registration-header h1 {
                font-size: 1.8rem;
            }
            
            .btn-group {
                flex-direction: column;
            }
        }

        .registration-body::-webkit-scrollbar {
            width: 8px;
        }

        .registration-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .registration-body::-webkit-scrollbar-thumb {
            background: #7c45a9;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="registration-header">
            <h1>Join Our Driver Team</h1>
            <p>Register now and start earning with our ride-sharing platform</p>
        </div>
        
        <div class="registration-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul style="list-style: none;">
                        <?php foreach ($errors as $error): ?>
                            <li>• <?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    ✅ <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-grid">
                    <!-- Personal Information -->
                    <div class="form-group">
                        <label for="name">Driver Name *</label>
                        <input type="text" id="name" name="name" required 
                               value="<?= htmlspecialchars($form_data['name'] ?? '') ?>"
                               placeholder="Enter full name">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" pattern="[0-9]{10}" required
                               value="<?= htmlspecialchars($form_data['phone'] ?? '') ?>"
                               placeholder="10-digit number">
                    </div>

                    <!-- Password Section -->
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required minlength="6"
                               placeholder="Minimum 6 characters">
                        <small class="password-hint">Choose a secure password</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required
                               placeholder="Re-enter password">
                    </div>

                    <!-- Documentation -->
                    <div class="form-group">
                        <label for="aadhar">Aadhar Number *</label>
                        <input type="text" id="aadhar" name="aadhar" pattern="[0-9]{12}" required
                               value="<?= htmlspecialchars($form_data['aadhar'] ?? '') ?>"
                               placeholder="12-digit number">
                    </div>

                    <div class="form-group">
                        <label for="license">Driving License Number *</label>
                        <input type="text" id="license" name="license" required
                               value="<?= htmlspecialchars($form_data['license'] ?? '') ?>"
                               placeholder="License number">
                    </div>

                    <div class="form-group">
                        <label for="languages">Spoken Languages</label>
                        <input type="text" id="languages" name="languages"
                               value="<?= htmlspecialchars($form_data['languages'] ?? '') ?>"
                               placeholder="English, Hindi, etc.">
                    </div>

                    <!-- Vehicle Information -->
                    <div class="form-group">
                        <label for="car_model">Car Model *</label>
                        <input type="text" id="car_model" name="car_model" required
                               value="<?= htmlspecialchars($form_data['car_model'] ?? '') ?>"
                               placeholder="e.g., Toyota Innova">
                    </div>

                    <div class="form-group">
                        <label for="car_capacity">Car Capacity *</label>
                        <input type="number" id="car_capacity" name="car_capacity" min="1" max="15" required
                               value="<?= htmlspecialchars($form_data['car_capacity'] ?? '4') ?>"
                               placeholder="Number of seats">
                    </div>

                    <div class="form-group">
                        <label for="car_number">Car Number *</label>
                        <input type="text" id="car_number" name="car_number" required
                               value="<?= htmlspecialchars($form_data['car_number'] ?? '') ?>"
                               placeholder="Vehicle registration number">
                    </div>

                    <div class="form-group">
                        <label for="price_per_km">Price per KM (₹) *</label>
                        <input type="number" id="price_per_km" name="price_per_km" min="1" step="0.01" required
                               value="<?= htmlspecialchars($form_data['price_per_km'] ?? '12.00') ?>"
                               placeholder="Fare per kilometer">
                    </div>

                    <!-- Preferences -->
                    <div class="form-group full-width">
                        <label for="outstation">Outstation Rides Allowed? *</label>
                        <select id="outstation" name="outstation" required>
                            <option value="no" <?= (isset($form_data['outstation']) && $form_data['outstation'] == 'no') ? 'selected' : '' ?>>No</option>
                            <option value="yes" <?= (isset($form_data['outstation']) && $form_data['outstation'] == 'yes') ? 'selected' : '' ?>>Yes</option>
                        </select>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Register as Driver</button>
                    <a href="drivers.php" class="btn btn-secondary">Go Back to Drivers</a>
                </div>
            </form>

            <div class="login-link">
                Already have an account? <a href="driver_login.php">Login here</a>
            </div>
        </div>
    </div>

    <script>
        // Real-time password confirmation check
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');

        function validatePassword() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.style.borderColor = '#dc3545';
            } else {
                confirmPassword.style.borderColor = '#28a745';
            }
        }

        password.addEventListener('input', validatePassword);
        confirmPassword.addEventListener('input', validatePassword);

        // Clear form after successful submission
        <?php if ($success): ?>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelector('form').reset();
            });
        <?php endif; ?>
    </script>
</body>
</html>