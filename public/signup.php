<?php
session_start();
include '../includes/header_public.php';

// Capture signup errors and previous form data if set
$signup_errors = $_SESSION['signup_errors'] ?? [];
unset($_SESSION['signup_errors']);

$signup_data = $_SESSION['signup_data'] ?? [];
unset($_SESSION['signup_data']);
?>

<section class="form-container">
    <h2>Create an Account</h2>

    <?php if (count($signup_errors) > 0): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($signup_errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="signup_process.php" method="POST" class="auth-form" novalidate>
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" placeholder="Enter your full name" required 
               value="<?= htmlspecialchars($signup_data['name'] ?? '') ?>" />

        <label for="admission_no">Admission Number</label>
        <input type="text" id="admission_no" name="admission_no" placeholder="Enter your admission number" required
               value="<?= htmlspecialchars($signup_data['admission_no'] ?? '') ?>" />

        <label for="gender">Gender</label>
        <select id="gender" name="gender" required>
            <option value="" disabled <?= !isset($signup_data['gender']) ? 'selected' : '' ?>>Select your gender</option>
            <option value="Male" <?= (isset($signup_data['gender']) && $signup_data['gender'] === 'Male') ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= (isset($signup_data['gender']) && $signup_data['gender'] === 'Female') ? 'selected' : '' ?>>Female</option>
            <option value="Other" <?= (isset($signup_data['gender']) && $signup_data['gender'] === 'Other') ? 'selected' : '' ?>>Other</option>
        </select>

        <label for="department">Department</label>
        <input type="text" id="department" name="department" placeholder="Enter your department" required
               value="<?= htmlspecialchars($signup_data['department'] ?? '') ?>" />

        <label for="year">Year</label>
        <input type="number" id="year" name="year" min="1" max="5" placeholder="Enter your year" required
               value="<?= htmlspecialchars($signup_data['year'] ?? '') ?>" />

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Create a password" required />

        <label for="password_confirm">Confirm Password</label>
        <input type="password" id="password_confirm" name="password_confirm" placeholder="Confirm password" required />

        <button type="submit" class="btn btn-primary">Sign Up</button>
    </form>
    <p class="register-link">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</section>

<?php
include '../includes/footer_public.php';
?>
