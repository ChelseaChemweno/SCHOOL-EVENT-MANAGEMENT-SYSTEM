
<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

require_student();

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['register_event'])) {

    if (!verify_csrf_token()) {
        exit('Invalid security token. Please refresh the page and try again.');
    }

    $eventId = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);

    if (!$eventId || $eventId < 1) {
        exit('Invalid event selected.');
    }

    // Check that the event exists and is published
    $stmt = $pdo->prepare("
        SELECT *
        FROM events
        WHERE id = ? AND status = 'published'
    ");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch();

    if (!$event) {
        exit('This event is not available for registration.');
    }

    // Check the registration deadline
    if (strtotime($event['registration_deadline']) < time()) {
        exit('The registration deadline for this event has passed.');
    }

    // Check whether the student has already registered
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM registrations
        WHERE user_id = ? AND event_id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $eventId]);

    if ($stmt->fetchColumn() > 0) {
        exit('You have already registered for this event.');
    }

    // Check available capacity
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM registrations
        WHERE event_id = ?
    ");
    $stmt->execute([$eventId]);
    $registeredCount = (int) $stmt->fetchColumn();

    if ($registeredCount >= (int) $event['capacity']) {
        exit('Sorry, this event is already full.');
    }

    // Save the registration
    $stmt = $pdo->prepare("
        INSERT INTO registrations (user_id, event_id, registered_at)
        VALUES (?, ?, NOW())
    ");

    $stmt->execute([$_SESSION['user_id'], $eventId]);

    redirect('/school-event-system/student/dashboard.php?registered=1');
}


// Get the logged-in student's name
$studentName = $_SESSION['full_name'] ?? 'Student';

// Get published events
$stmt = $pdo->prepare("
    SELECT *
    FROM events
    WHERE status = 'published'
    ORDER BY start_date ASC
");
$stmt->execute();

$events = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
</head>
<body>

    <h1>Student Dashboard</h1>

    <p>Welcome, <?= e($studentName) ?>!</p>

    <form action="/school-event-system/public/logout.php" method="POST">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= e(csrf_token()) ?>"
        >
        <button type="submit">Logout</button>
    </form>

    <hr>

    <h2>Available Events</h2>
    <?php if (isset($_GET['registered'])): ?>
    <p>You have successfully registered for the event!</p>
<?php endif; ?>

    <?php if (empty($events)): ?>

        <p>There are currently no published events available.</p>

    <?php else: ?>

        <?php foreach ($events as $event): ?>

            <div>
                <h3><?= e($event['title']) ?></h3>

                <p>
                    <?= e($event['description']) ?>
                </p>

                <p>
                    <strong>Venue:</strong>
                    <?= e($event['venue']) ?>
                </p>

                <p>
                    <strong>Starts:</strong>
                    <?= e($event['start_date']) ?>
                </p>

                <p>
                    <strong>Ends:</strong>
                    <?= e($event['end_date']) ?>
                </p>

                <p>
                    <strong>Registration deadline:</strong>
                    <?= e($event['registration_deadline']) ?>
                </p>

<form method="POST" action="">
    <input
        type="hidden"
        name="csrf_token"
        value="<?= e(csrf_token()) ?>"
    >

    <input
        type="hidden"
        name="event_id"
        value="<?= (int) $event['id'] ?>"
    >

    <button type="submit" name="register_event">
        Register for Event
    </button>
</form>


                <hr>
            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</body>
</html>



