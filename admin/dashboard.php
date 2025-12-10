<?php
// admin/dashboard.php
session_start();  // Start session (must be first)

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// If logged in, show dashboard
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: #f0f4ff; font-family: 'Inter', sans-serif; }
        .dashboard-container { max-width: 800px; margin: 50px auto; padding: 30px; background: white; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        h2 { color: #003087; }
        p { color: #555; }
        .logout { text-align: center; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h2>Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?>!</h2>
        <p>This is your admin dashboard. Here you can manage products, blog posts, and more (coming soon).</p>
        <div class="logout">
            <a href="logout.php" style="color: #0066ff;">Logout</a>
        </div>
    </div>
</body>
</html>