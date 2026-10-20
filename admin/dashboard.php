
<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Allow administrators only.
require_admin();

// Get the total number of events.
$stmt = $pdo->query('SELECT COUNT(*) FROM events');
$totalEvents = $stmt->fetchColumn();

// Get the total number of registrations.
$stmt = $pdo->query('SELECT COUNT(*) FROM registrations');
$totalRegistrations = $stmt->fetchColumn();

// Get the total number of students.
$stmt = $pdo->query(
    "SELECT COUNT(*) FROM users WHERE role = 'student'"
);
$totalStudents = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
</head>
<body>

    <h1>School Event Management System</h1>
    <h2>Administrator Dashboard</h2>

    <p>
        Welcome, <?= e($_SESSION['full_name']) ?>!
    </p>

    <hr>

    <h3>System Overview</h3>

    <p>Total Events: <?= (int) $totalEvents ?></p>

    <p>Total Registrations: <?= (int) $totalRegistrations ?></p>

    <p>Total Students: <?= (int) $totalStudents ?></p>

    <hr>

    <h3>Event Management</h3>

    <p><a href="events.php">Manage Events</a></p>

    <h3>Registration Management</h3>

    <p><a href="registrations.php">View Student Registrations</a></p>

    <hr>

    <form method="POST" action="/school-event-system/public/logout.php">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= e(csrf_token()) ?>"
        >
        <button type="submit">Log Out</button>
    </form>

</body>
</html>






<form method="POST" action="/school-event-system/public/logout.php">
    <input type="hidden" name="csrf_token"
           value="<?= e(csrf_token()) ?>">
    <button type="submit" class="btn btn-danger">Log Out</button>
</form>