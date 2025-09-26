<?php
session_start();
include '../includes/header_public.php';

// Capture login error or success message if set
$login_error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

$signup_success = $_SESSION['signup_success'] ?? '';
unset($_SESSION['signup_success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Reset default styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Global styles */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5f7fa;
            color: #c62590ff;
            line-height: 1.6;
        }

        /* Form container */
        .form-container {
            max-width: 400px;
            margin: 3rem auto;
            padding: 2rem;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Heading */
        .form-container h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #1a2b49;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        /* Alert messages */
        .alert {
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
            font-size: 0.9rem;
            text-align: center;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #047857;
            border: 1px solid #6ee7b7;
        }

        /* Form styles */
        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }

        .auth-form label {
            font-size: 0.9rem;
            font-weight: 500;
            color: #1a2b49;
        }

        .auth-form input {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .auth-form input:focus {
            outline: none;
            border-color: #a47bb9;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Button styles */
        .btn {
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 500;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-primary {
            background-color: a47bb9;
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: #8a64a8;
        }

        /* Register link */
        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: #4b5563;
        }

        .register-link a {
            color: a47bb9;
            text-decoration: none;
            font-weight: 500;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        /* Responsive design */
        @media (max-width: 480px) {
            .form-container {
                margin: 1.5rem;
                padding: 1.5rem;
            }

            .form-container h2 {
                font-size: 1.5rem;
            }

            .auth-form input {
                padding: 0.6rem;
                font-size: 0.9rem;
            }

            .btn {
                padding: 0.6rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <section class="form-container">
        <h2>Student Login</h2>

        <?php if ($login_error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($login_error) ?></div>
        <?php endif; ?>

        <?php if ($signup_success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($signup_success) ?></div>
        <?php endif; ?>

        <form action="login_process.php" method="POST" class="auth-form">
            <label for="admission_no">Admission Number</label>
            <input type="text" id="admission_no" name="admission_no" placeholder="Enter your admission number" required />

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter password" required />

            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <p class="register-link">
            Don't have an account? <a href="signup.php">Sign up here</a>
        </p>
    </section>

    <?php
    include '../includes/footer_public.php';
    ?>
</body>
</html>