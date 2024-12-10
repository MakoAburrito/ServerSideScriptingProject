<?php
try {
    // Include database.php to create the database and tables if they don't exist
    include 'database.php';  

    // use the database from db.php
    $pdo->exec("USE excursions"); // Make sure we are using the correct database

    // Insert demo data into the 'holidays' table
    $pdo->exec("INSERT INTO holidays (holiday_date, name) VALUES 
        ('2024-01-01', 'New Year'),
        ('2024-07-04', 'Independence Day'),
        ('2024-12-25', 'Christmas')
    ");
    echo "<p>Data inserted into holidays table.</p>";

    // Insert demo data into the 'excursions' table
    $pdo->exec("INSERT INTO excursions (name, timeframe, start_datetime) VALUES
        ('Beach Vacation', '1 week', '2024-06-01 10:00:00'),
        ('Mountain Hike', '5 days', '2024-07-10 08:00:00')
    ");
    echo "<p>Data inserted into excursions table.</p>";

    // Insert demo data into the 'events' table
    $pdo->exec("INSERT INTO events (excursion_id, name, start_offset, duration) VALUES
        (1, 'Arrival and Check-In', 0, 420), -- 7 hours after excursion start
        (1, 'Beach Volleyball', 1, 180), -- 3 hours on the next day
        (2, 'Trail Hiking', 0, 600), -- 10 hours on excursion start day
        (2, 'Campfire Night', 1, 300) -- 5 hours on the next day
    ");
    echo "<p>Data inserted into events table.</p>";

    // Insert demo data into the 'scheduled_excursions' table
    $pdo->exec("INSERT INTO scheduled_excursions (excursion_id, start_datetime, end_datetime) VALUES
        (1, '2024-06-01 10:00:00', '2024-06-08 10:00:00'),
        (2, '2024-07-10 08:00:00', '2024-07-15 08:00:00')
    ");
    echo "<p>Data inserted into scheduled_excursions table.</p>";

    // Insert demo data into the 'scheduled_events' table
    $pdo->exec("INSERT INTO scheduled_events (scheduled_excursion_id, event_id, start_datetime, end_datetime, holiday_warning) VALUES
        (1, 1, '2024-06-01 10:00:00', '2024-06-01 17:00:00', 0), -- No holiday conflict
        (1, 2, '2024-06-02 10:00:00', '2024-06-02 13:00:00', 0), -- No holiday conflict
        (2, 3, '2024-07-10 08:00:00', '2024-07-10 18:00:00', 1), -- Overlaps with Independence Day
        (2, 4, '2024-07-11 18:00:00', '2024-07-11 23:00:00', 0) -- No holiday conflict
    ");
    echo "<p>Data inserted into scheduled_events table.</p>";

} catch (PDOException $e) {
    // If an error occurs during the database operations, show the error message
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
