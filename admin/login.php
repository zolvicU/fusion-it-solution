<?php
// admin/login.php
session_start();

// Database connection - use correct relative path
require_once __DIR__ . '/../config/database.php';

// Login throttling
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['lockout_time'] = 0;
}

$error = '';

if ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['lockout_time'] < 900)) {
    $error = "Too many failed attempts. Please try again in 15 minutes.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Username and password are required.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password_hash, role, status FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Check if account is active
                if ($user['status'] !== 'active') {
                    $error = "Account is inactive. Please contact administrator.";
                } elseif (password_verify($password, $user['password_hash'])) {
                    // Success
                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_role'] = $user['role'];
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['last_activity'] = time();

                    // Reset attempts
                    unset($_SESSION['login_attempts'], $_SESSION['lockout_time']);

                    header('Location: dashboard.php');
                    exit();
                } else {
                    $_SESSION['login_attempts']++;
                    if ($_SESSION['login_attempts'] >= 5) {
                        $_SESSION['lockout_time'] = time();
                        $error = "Too many failed attempts. Locked for 15 minutes.";
                    } else {
                        $error = "Invalid username or password.";
                    }
                }
            } else {
                $_SESSION['login_attempts']++;
                if ($_SESSION['login_attempts'] >= 5) {
                    $_SESSION['lockout_time'] = time();
                    $error = "Too many failed attempts. Locked for 15 minutes.";
                } else {
                    $error = "Invalid username or password.";
                }
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = "Database error. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Fusion IT Solution</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --bg-gradient-start: #e0f2fe;
            --bg-gradient-end: #f8fbff;
            --card-bg: rgba(255, 255, 255, 0.85);
            --text: #1e293b;
            --text-light: #64748b;
            --border: #e2e8f0;
            --error: #dc2626;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text);
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            padding: 48px 40px;
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .login-header p {
            color: var(--text-light);
            font-size: 15px;
        }

        .form-group {
            position: relative;
            margin-bottom: 28px;
        }

        .form-group input {
            width: 100%;
            padding: 16px 16px 16px 48px;
            font-size: 16px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: white;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.15);
        }

        .form-group label {
            position: absolute;
            left: 48px;
            top: 16px;
            font-size: 16px;
            color: var(--text-light);
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .form-group input:focus ~ label,
        .form-group input:valid ~ label {
            top: -10px;
            left: 16px;
            font-size: 13px;
            background: white;
            padding: 0 8px;
            color: var(--primary);
            font-weight: 500;
        }

        .icon {
            position: absolute;
            left: 16px;
            top: 16px;
            color: var(--text-light);
            font-size: 20px;
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.3);
        }

        .error {
            background: #fee2e2;
            color: var(--error);
            padding: 14px;
            border-radius: 10px;
            text-align: center;
            font-size: 14px;
            margin-bottom: 24px;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h2>Admin Login</h2>
            <p>Welcome back! Please enter your credentials.</p>
        </div>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <input type="text" name="username" required>
                <label>Username</label>
                <span class="icon">👤</span>
            </div>

            <div class="form-group">
                <input type="password" name="password" required>
                <label>Password</label>
                <span class="icon">🔒</span>
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>
    </div>
</body>
</html>