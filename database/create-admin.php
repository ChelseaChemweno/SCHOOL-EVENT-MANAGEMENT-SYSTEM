
<?php

require_once __DIR__ . '/../config/database.php';

$name = 'System Administrator';
$email = 'admin@school.local';
$password = 'ChangeMe123!';

// Check whether this email already exists.
$stmt = $pdo->prepare(
    'SELECT id FROM users WHERE email = :email LIMIT 1'
);
$stmt->execute(['email' => $email]);

if ($stmt->fetch()) {
    exit("An account with this email already exists.\n");
}

// Create the administrator account securely.
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'INSERT INTO users (full_name, email, password_hash, role, is_active)
     VALUES (:name, :email, :password_hash, :role, 1)'
);

$stmt->execute([
    'name' => $name,
    'email' => $email,
    'password_hash' => $passwordHash,
    'role' => 'admin'
]);

echo "Administrator account created successfully!\n";
echo "Email: " . $email . "\n";
echo "Password: " . $password . "\n";
