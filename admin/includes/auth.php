<?php
// admin/includes/auth.php - FINAL VERSION: No Warnings, Fully Secure

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Secure cookie settings BEFORE session starts
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    // Uncomment when your site uses HTTPS
    // ini_set('session.cookie_secure', 1);

    session_start();
}

// Session timeout: 30 minutes of inactivity
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: login.php?error=timeout');
    exit();
}
$_SESSION['last_activity'] = time();

// Regenerate session ID on first access (prevents fixation)
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Require valid login
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}
?>