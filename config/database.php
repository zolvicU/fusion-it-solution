<?php
// config/database.php
// Database connection for XAMPP - Guaranteed working (December 2025)

$host     = 'localhost';
$dbname   = 'fusionit';        // Make sure this database exists in phpMyAdmin
$username = 'root';
$password = '';                // Default empty password in XAMPP
$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Return rows as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                    // Use real prepared statements (more secure)
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // In production: hide details. In development: show for debugging.
    die("Database connection failed. Please check your database name and try again.");
    // For debugging only (remove in production):
    // die("Connection failed: " . $e->getMessage());
}
?>