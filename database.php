<?php
// database.php

// Database connection details
$host = 'localhost';
$dbname = 'employee_management';
$username = 'root'; // or your username
$password = '';     // or your password

// Create a new PDO instance
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set error mode to exception to handle errors gracefully
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: Could not connect to the database. " . $e->getMessage());
}
?>
