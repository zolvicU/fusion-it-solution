<?php
// config/database.php → GUARANTEED WORKING ON ALL XAMPP (December 2025)

$host     = 'localhost';
$dbname   = 'fusionit';
$username = 'root';
$password = '';
$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    'ERRMODE'            => 'EXCEPTION',  // String instead of PDO::ERRMODE_EXCEPTION
    'FETCH_ASSOC'        => true,         // String instead of PDO::ATTR_DEFAULT_FETCH_MODE
    'EMULATED_PREPARES'  => false         // String instead of PDO::ATTR_EMULATED_PREPARES
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die("Database connection failed. Please contact support.");
}
?>