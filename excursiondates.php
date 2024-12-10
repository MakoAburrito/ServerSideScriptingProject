<?php
include 'database.php';
$pdo->exec("USE excursions");

//used to fetch table data from the named tables in our database
function fetchTableData($pdo, $tableName) {
    return $pdo->query("SELECT * FROM $tableName")->fetchAll(PDO::FETCH_ASSOC);
}

// used to format the date and time to a more readable format 
function formatDateTime($datetime) {
    return (new DateTime($datetime))->format('m/d/Y h:i A');
}

function formatDate($date) {
    return (new DateTime($date))->format('m/d/Y');
}

function calculateEndDateTime($start, $duration) {
    $end = new DateTime($start);
    $end->modify("+{$duration} minutes");
    return $end;
}

function isHolidayConflict($start, $end, $holidays) {
    foreach ($holidays as $holiday) {
        $holidayDate = new DateTime($holiday);
        if ($holidayDate >= new DateTime($start) && $holidayDate <= new DateTime($end)) {
            return true;
        }
    }
    return false;
}

// Fetch available excursions and holidays
$excursions = fetchTableData($pdo, 'excursions');
$holidays = $pdo->query("SELECT holiday_date FROM holidays")->fetchAll(PDO::FETCH_COLUMN);

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $excursionId = $_POST['excursion_id'];
    $eventName = $_POST['event_name'];
    $eventStartOffset = (int)$_POST['event_start_offset'];
    $eventDuration = (int)$_POST['event_duration'];

    // Fetch Excursion Details
    $excursion = $pdo->prepare("SELECT name, start_datetime, timeframe FROM excursions WHERE id = :id");
    $excursion->execute(['id' => $excursionId]);
    $excursion = $excursion->fetch(PDO::FETCH_ASSOC);

    $excursionStart = new DateTime($excursion['start_datetime']);
    $excursionEnd = new DateTime($excursion['start_datetime']);
    $excursionEnd->modify("+{$excursion['timeframe']}");

    // Insert Scheduled Excursion
    $pdo->prepare("INSERT INTO scheduled_excursions (excursion_id, start_datetime, end_datetime) VALUES (?, ?, ?)")
        ->execute([$excursionId, $excursionStart->format('Y-m-d H:i:s'), $excursionEnd->format('Y-m-d H:i:s')]);
    $scheduledExcursionId = $pdo->lastInsertId();

    // Calculate Event Start and End
    $eventStart = clone $excursionStart;
    $eventStart->modify("+{$eventStartOffset} days");
    $eventEnd = calculateEndDateTime($eventStart->format('Y-m-d H:i:s'), $eventDuration);

    // Check for Warnings
    $holidayWarning = isHolidayConflict($eventStart->format('Y-m-d H:i:s'), $eventEnd->format('Y-m-d H:i:s'), $holidays);
    $timeframeWarning = $eventEnd > $excursionEnd;

    // Insert Event
    $pdo->prepare("INSERT INTO events (excursion_id, name, start_offset, duration) VALUES (?, ?, ?, ?)")
        ->execute([$excursionId, $eventName, $eventStartOffset, $eventDuration]);
    $eventId = $pdo->lastInsertId();

    // Insert Scheduled Event
    $pdo->prepare(
        "INSERT INTO scheduled_events (scheduled_excursion_id, event_id, start_datetime, end_datetime, holiday_warning)
         VALUES (?, ?, ?, ?, ?)"
    )->execute([
        $scheduledExcursionId,
        $eventId,
        $eventStart->format('Y-m-d H:i:s'),
        $eventEnd->format('Y-m-d H:i:s'),
        $holidayWarning
    ]);

    // Event Details for Display
    $generatedEvent = [
        'Excursion Name' => $excursion['name'],
        'Excursion Start' => formatDateTime($excursionStart->format('Y-m-d H:i:s')),
        'Excursion End' => formatDateTime($excursionEnd->format('Y-m-d H:i:s')),
        'Event Name' => $eventName,
        'Event Start' => formatDateTime($eventStart->format('Y-m-d H:i:s')),
        'Event End' => formatDateTime($eventEnd->format('Y-m-d H:i:s')),
        'Holiday Conflict' => $holidayWarning ? 'Yes' : 'No',
        'Timeframe Conflict' => $timeframeWarning ? 'Yes' : 'No',
    ];

    // Fetch all table data
    $allTables = [
        'Excursions' => fetchTableData($pdo, 'excursions'),
        'Events' => fetchTableData($pdo, 'events'),
        'Holidays' => fetchTableData($pdo, 'holidays'),
        'Scheduled Excursions' => fetchTableData($pdo, 'scheduled_excursions'),
        'Scheduled Events' => fetchTableData($pdo, 'scheduled_events'),
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Generate Excursion Dates</title>
</head>
<body>
    <h1>Generate Excursion Dates</h1>
    <a href="index.php">Back to Home</a>

    <!-- Form for Event Creation -->
    <form method="POST">
        <label for="excursion_id">Select Excursion:</label>
        <select name="excursion_id" id="excursion_id" required>
            <option value="">-- Select --</option>
            <?php foreach ($excursions as $excursion): ?>
                <option value="<?= $excursion['id'] ?>">
                    <?= htmlspecialchars($excursion['name']) ?> (Starts: <?= formatDateTime($excursion['start_datetime']) ?>)
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="event_name">Event Name:</label>
        <input type="text" name="event_name" required><br><br>

        <label for="event_start_offset">Event Start Offset (days):</label>
        <input type="number" name="event_start_offset" required><br><br>

        <label for="event_duration">Event Duration (minutes):</label>
        <input type="number" name="event_duration" required><br><br>

        <input type="submit" value="Generate Scheduled Events">
    </form>

    <!-- Display Generated Event -->
    <?php if (!empty($generatedEvent)): ?>
        <h2>Scheduled Event</h2>
        <table border="1">
            <?php foreach ($generatedEvent as $key => $value): ?>
                <tr>
                    <th><?= $key ?></th>
                    <td><?= htmlspecialchars($value) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <!-- Display All Tables -->
    <?php if (!empty($allTables)): ?>
        <h2>All Database Tables</h2>
        <?php foreach ($allTables as $tableName => $rows): ?>
            <h3><?= $tableName ?></h3>
            <?php if ($rows): ?>
                <table border="1">
                    <tr>
                        <!--Array_keys allows for us to grab the name of the stored variable in the array rows.
                        Ex: id => 0 , name => "Beach trip", when it grabs the first [0] it will display id, 
                        not the value, displays where its stored. -->
                        <?php foreach (array_keys($rows[0]) as $column): ?>
                            <th><?= $column ?></th>
                        <?php endforeach; ?>
                    </tr>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php foreach ($row as $key => $value): ?>
                                <td><?= htmlspecialchars(in_array($key, ['start_datetime', 'end_datetime']) ? formatDateTime($value) : ($key === 'holiday_date' ? formatDate($value) : $value)) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No records found in <?= $tableName ?>.</p>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>