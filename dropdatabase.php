<?php

// 11/19 rename the php file to specified name in requirements
try {
    include 'database.php';

    // Create a variable with the database name to be dropped
    $database = 'excursions';

    // Check if database exists, if so, Drop the database 
    $pdo->exec("DROP DATABASE IF EXISTS excursions");
    echo "<p>Database 'excursions' has been dropped.</p>";

    // Drop the 'admin' user from MySQL (if they exist)
    $pdo->exec("DROP USER IF EXISTS 'admin'@'localhost'");
    echo "<p>User 'admin' has been dropped.</p>";

} catch (PDOException $e) {
    // Output error 
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>


