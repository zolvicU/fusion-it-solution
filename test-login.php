<?php
// test-login.php
require_once '../config/database.php';

// Test database connection
try {
    $stmt = $pdo->query("SELECT 1");
    echo "✓ Database connection: OK<br>";
} catch (Exception $e) {
    echo "✗ Database connection failed: " .