<?php
$host = 'localhost';
$dbname = 'excursions';
$username = 'admin';
$password = 'Pa11word';

try {
    // Create a PDO instance
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Set PDO error mode to exception for debugging
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $error) {
    // Handle connection error
    die("Database connection failed: " . $error->getMessage());
}
?>
