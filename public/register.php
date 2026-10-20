
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

$error = '';
$success = get_flash('success');
$fullName = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!verify_csrf_token()) {
        $error = 'Please refresh the page and try again.';
    } elseif ($fullName === '' || strlen($fullName) > 150) {
        $error = 'Please enter your full name.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!is_string($password) || strlen($password) < 8) {
        $error = 'Password must contain at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Your passwords do not match.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO users (full_name, email, password_hash, role)
                 VALUES (:full_name, :email, :password_hash, :role)'
            );

            $stmt->execute([
                'full_name' => $fullName,
                'email' => $email,
                'password_hash' => password_hash(
                    $password,
                    PASSWORD_DEFAULT
                ),
                'role' => 'student'
            ]);

            set_flash(
                'success',
                'Registration successful! You can now log in.'
            );

            redirect('/school-event-system/public/login.php');

        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $error = 'This email address is already registered.';
            } else {
                error_log('Registration error: ' . $e->getMessage());
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Registration</title>
</head>
<body>

    <h1>School Event Management System</h1>
    <h2>Student Registration</h2>

    <?php if ($success): ?>
        <p style="color:green;"><?= e($success) ?></p>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <p style="color:red;"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= e(csrf_token()) ?>"
        >

        <p>
            <label for="full_name">Full Name</label><br>
            <input
                type="text"
                id="full_name"
                name="full_name"
                maxlength="150"
                value="<?= e($fullName) ?>"
                required
            >
        </p>

        <p>
            <label for="email">Email Address</label><br>
            <input
                type="email"
                id="email"
                name="email"
                value="<?= e($email) ?>"
                required
            >
        </p>

        <p>
            <label for="password">Password</label><br>
            <input
                type="password"
                id="password"
                name="password"
                minlength="8"
                required
            >
        </p>

        <p>
            <label for="confirm_password">Confirm Password</label><br>
            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                minlength="8"
                required
            >
        </p>

        <button type="submit">Create Account</button>

    </form>

    <p>Already registered? <a href="login.php">Login here</a></p>

</body>
</html>
