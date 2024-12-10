<?php
$title = 'Create Excursion Database';
// include('includes/htmlhead.php');
?>

<?php
try {
    // Connect to MySQL using the root user account
    $dsn = 'mysql:host=localhost'; // Data source name (host location)
    $pdo = new PDO($dsn, 'root', ''); // Create a connection to the database server
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set the connection to show detailed errors if something goes wrong

    // Name of the database we're going to work with
    $database = 'excursions';

    // Create the database if it doesn't exist already
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $database");
    echo "<p>Database $database created or already exists.</p>"; // Output that the database is ready

    // Switch to the database
    $pdo->exec("USE $database");

    // Create the user 'admin' if it doesn't exist and grant them full access to the database
    $pdo->exec("CREATE USER IF NOT EXISTS 'admin'@'localhost' IDENTIFIED BY 'Pa11word'");
    echo "<p>User 'admin' created or already exists.</p>";

    // Grant full access to the admin user
    $pdo->exec("GRANT ALL PRIVILEGES ON $database.* TO 'admin'@'localhost'");
    echo "<p>Full access granted to user 'admin' for database $database.</p>";

    // Create a table to store information about excursions
    $sqlQuery = 'CREATE TABLE IF NOT EXISTS excursions (
        id INT AUTO_INCREMENT PRIMARY KEY, -- A unique ID for each excursion
        name VARCHAR(255) NOT NULL, -- Name of the excursion
        timeframe VARCHAR(50), -- Timeframe: weeks, months, or years
        start_datetime DATETIME NOT NULL -- Planned start date and time
    )';
    $pdo->exec($sqlQuery); // Run the SQL command
    echo "<p>Table 'excursions' created successfully.</p>"; // Output that the table is ready

    // Create a table to store information about events
    $sqlQuery = 'CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY, -- A unique ID for each event
        excursion_id INT, -- Links this event to an excursion (logical reference)
        name VARCHAR(255) NOT NULL, -- Name of the event
        start_offset INT NOT NULL, -- Offset in days from excursion start
        duration INT NOT NULL, -- Duration in minutes
        INDEX (excursion_id) -- Index for better lookup
    )';
    $pdo->exec($sqlQuery); // Run the SQL command
    echo "<p>Table 'events' created successfully.</p>"; // Output that the table is ready

    // Create a table to store information about holidays
    $sqlQuery = 'CREATE TABLE IF NOT EXISTS holidays (
        holiday_id INT AUTO_INCREMENT PRIMARY KEY, -- A unique ID for each holiday
        name VARCHAR(10) NOT NULL, -- Name of the holiday 
        holiday_date DATE NOT NULL -- Date when the holiday occurs
    )';
    $pdo->exec($sqlQuery); // Run the SQL command
    echo "<p>Table 'holidays' created successfully.</p>"; // Output that the table is ready

    // Create a table to store scheduled excursions
    $sqlQuery = 'CREATE TABLE IF NOT EXISTS scheduled_excursions (
        id INT AUTO_INCREMENT PRIMARY KEY, -- A unique ID for each scheduled excursion
        excursion_id INT, -- Links to the excursion being scheduled (logical reference)
        start_datetime DATETIME NOT NULL, -- Specific start date and time
        end_datetime DATETIME NOT NULL, -- Calculated end date and time based on timeframe
        INDEX (excursion_id) -- Index for better lookup
    )';
    $pdo->exec($sqlQuery);
    echo "<p>Table 'scheduled_excursions' created successfully.</p>";

    // Create a table to store scheduled events
    $sqlQuery = 'CREATE TABLE IF NOT EXISTS scheduled_events (
        id INT AUTO_INCREMENT PRIMARY KEY, -- A unique ID for each scheduled event
        scheduled_excursion_id INT, -- Links to the scheduled excursion (logical reference)
        event_id INT, -- Links to the event template (logical reference)
        start_datetime DATETIME NOT NULL, -- Calculated start date and time for the event
        end_datetime DATETIME NOT NULL, -- Calculated end date and time for the event
        holiday_warning BOOLEAN NOT NULL DEFAULT 0, -- Whether the event overlaps with a holiday
        INDEX (scheduled_excursion_id), -- Index for better lookup
        INDEX (event_id) -- Index for better lookup
    )';
    $pdo->exec($sqlQuery);
    echo "<p>Table 'scheduled_events' created successfully.</p>";

} catch (PDOException $e) {
    // Show an error message if something goes wrong
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>