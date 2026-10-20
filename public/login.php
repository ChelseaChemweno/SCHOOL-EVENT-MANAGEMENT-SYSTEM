
<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!verify_csrf_token()) {
        $error = 'Please refresh the page and try again.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'SELECT id, full_name, password_hash, role, is_active
                 FROM users WHERE email = :email LIMIT 1'
            );
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if (
                $user &&
                password_verify($password, $user['password_hash']) &&
                (bool) $user['is_active']
            ) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                $destination = $user['role'] === 'admin'
                    ? '/school-event-system/admin/dashboard.php'
                    : '/school-event-system/student/dashboard.php';

                header('Location: ' . $destination);
                exit;
            }

            $error = 'Incorrect email or password.';
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'Database error. Check your PHP error log.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>School Event System Login</title>
</head>
<body>
    <h1>School Event Management System</h1>
    <h2>Login</h2>

    <p style="color:red;"><?= e($error) ?></p>

    <form method="POST">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required>

        <br><br>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <br><br>

        <input type="hidden" name="csrf_token"
               value="<?= e(csrf_token()) ?>">

        <button type="submit">Login</button>
    </form>

    <p>New student? <a href="register.php">Sign Up</a></p>
</body>
</html>
