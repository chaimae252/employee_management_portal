<?php
require_once 'database.php'; // Include your database connection

if ($pdo) {
    echo "Database connection successful!";
} else {
    echo "Database connection failed!";
}
?>
