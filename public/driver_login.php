<?php
session_start();
// Redirect if already logged in
if (isset($_SESSION['driver_id'])) {
    header('Location: driver_dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Login - CampusCarPool</title>
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

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #7c45a9 0%, #5c2487 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .login-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            opacity: 0.9;
        }

        .login-body {
            padding: 2rem;
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

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
            display: block;
            font-size: 0.95rem;
        }

        input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        input:focus {
            outline: none;
            border-color: #7c45a9;
            background: white;
            box-shadow: 0 0 0 3px rgba(124, 69, 169, 0.1);
        }

        .btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #7c45a9 0%, #5c2487 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(124, 69, 169, 0.4);
        }

        .links {
            text-align: center;
            margin-top: 1.5rem;
        }

        .links a {
            color: #7c45a9;
            text-decoration: none;
            font-weight: 600;
            margin: 0 0.5rem;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .register-link {
            text-align: center;
            margin-top: 1rem;
            color: #666;
        }

        .register-link a {
            color: #7c45a9;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Driver Login</h1>
            <p>Access your driver dashboard</p>
        </div>
        
        <div class="login-body">
            <?php if (isset($_SESSION['login_error'])): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($_SESSION['login_error']) ?>
                </div>
                <?php unset($_SESSION['login_error']); ?>
            <?php endif; ?>

            <form method="POST" action="driver_login_process.php">
                <div class="form-group">
                    <label for="name">Driver Name</label>
                    <input type="text" id="name" name="name" required 
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                           placeholder="Enter your full name">
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" pattern="[0-9]{10}" required
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                           placeholder="10-digit phone number">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required
                           placeholder="Enter your password">
                </div>

                <button type="submit" class="btn btn-primary">Login</button>
            </form>

            <div class="register-link">
                Don't have an account? <a href="register_driver.php">Register as Driver</a>
            </div>

            <div class="links">
                <a href="../public/index.php">← Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>