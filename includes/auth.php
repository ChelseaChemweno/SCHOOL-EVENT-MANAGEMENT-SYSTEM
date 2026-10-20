
<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    // Use safer session cookie settings.
    ini_set('session.use_strict_mode', '1');

    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);

    session_start();
}

require_once __DIR__ . '/functions.php';

// Ensure a user is logged in.
function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        set_flash('error', 'Please log in to continue.');
        redirect('/school-event-system/public/login.php');
    }
}

// Ensure the logged-in user is an administrator.
function require_admin(): void
{
    require_login();

    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Access denied. Administrator permission is required.');
    }
}

// Ensure the logged-in user is a student.
function require_student(): void
{
    require_login();

    if (($_SESSION['role'] ?? '') !== 'student') {
        http_response_code(403);
        exit('Access denied. Student permission is required.');
    }
}
