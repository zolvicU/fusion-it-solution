<?php
// config/database.php

// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'fusionit');
define('DB_USER', 'root');
define('DB_PASS', ''); // Empty for XAMPP default
define('DB_CHARSET', 'utf8mb4');

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Set timezone if needed
    $pdo->exec("SET time_zone = '+00:00'");
    
} catch (PDOException $e) {
    // Log error and show generic message
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}
?>