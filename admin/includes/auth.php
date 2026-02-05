<?php
// admin/includes/auth.php

// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection - use correct relative path
require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Redirect to login if not authenticated
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
}

// Check session timeout (30 minutes)
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity'])) {
        $inactive = 1800; // 30 minutes in seconds
        $session_life = time() - $_SESSION['last_activity'];
        
        if ($session_life > $inactive) {
            session_unset();
            session_destroy();
            header('Location: ../login.php?timeout=1');
            exit();
        }
    }
    $_SESSION['last_activity'] = time();
}

// Admin login function
function adminLogin($username, $password) {
    global $pdo;  // Use the database connection from database.php
    
    try {
        // Prepare SQL statement
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? AND status = 'active'");
        
        // Execute with the username
        $stmt->execute([$username]);
        
        // Fetch the admin data
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if admin exists and password is correct
        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Login successful - set session variables
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['last_activity'] = time();
            return true;
        }
        
        return false;  // Login failed
    } catch (Exception $e) {
        // Log error (for debugging)
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

// Check if user is super admin
function isSuperAdmin() {
    return ($_SESSION['admin_role'] ?? '') === 'super_admin';
}
?>