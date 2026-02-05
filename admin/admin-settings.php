<?php
require_once 'includes/auth.php';
require_once '../config/database.php';

// Initialize all variables at the start
$error = '';
$success = '';
$is_super_admin = false;
$admin_id = null;
$admin_data = [];
$all_admins = [];

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Only allow super_admin to create other admins
$current_user_role = $_SESSION['admin_role'] ?? 'admin';
$is_super_admin = ($current_user_role === 'super_admin');

// Get admin ID
$admin_id = $_SESSION['admin_id'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update current admin info
    if (isset($_POST['update_profile'])) {
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        try {
            // Get current admin data
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
            $stmt->execute([$admin_id]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$admin) {
                $error = "Admin not found!";
            } else {
                // Verify current password if changing password
                if (!empty($new_password)) {
                    if (empty($current_password)) {
                        $error = "Current password is required to change password";
                    } elseif (!password_verify($current_password, $admin['password_hash'])) {
                        $error = "Current password is incorrect";
                    } elseif ($new_password !== $confirm_password) {
                        $error = "New passwords do not match";
                    } elseif (strlen($new_password) < 6) {
                        $error = "New password must be at least 6 characters";
                    } else {
                        // Update password
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$hashed_password, $admin_id]);
                        $success .= "Password updated successfully!<br>";
                    }
                }
                
                // Update email
                if (empty($error) && $email !== $admin['email']) {
                    $stmt = $pdo->prepare("UPDATE admin_users SET email = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$email, $admin_id]);
                    $success .= "Email updated successfully!<br>";
                }
                
                // Update username if super admin
                if ($is_super_admin && isset($_POST['username'])) {
                    $username = $_POST['username'] ?? '';
                    if ($username !== $admin['username']) {
                        $stmt = $pdo->prepare("UPDATE admin_users SET username = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$username, $admin_id]);
                        $success .= "Username updated successfully!<br>";
                        $_SESSION['admin_username'] = $username;
                    }
                }
            }
        } catch (Exception $e) {
            $error = "Update failed: " . $e->getMessage();
        }
    }
    
    // Create new admin account
    if (isset($_POST['create_admin']) && $is_super_admin) {
        $new_username = $_POST['new_username'] ?? '';
        $new_email = $_POST['new_email'] ?? '';
        $new_password = $_POST['new_password_admin'] ?? '';
        $confirm_password = $_POST['confirm_password_admin'] ?? '';
        $role = $_POST['role'] ?? 'admin';
        
        // Validate inputs
        if (empty($new_username) || empty($new_email) || empty($new_password)) {
            $error = "All fields are required";
        } elseif ($new_password !== $confirm_password) {
            $error = "Passwords do not match";
        } elseif (strlen($new_password) < 6) {
            $error = "Password must be at least 6 characters";
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format";
        } else {
            try {
                // Check if username or email already exists
                $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ? OR email = ?");
                $stmt->execute([$new_username, $new_email]);
                if ($stmt->rowCount() > 0) {
                    $error = "Username or email already exists";
                } else {
                    // Create new admin
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO admin_users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$new_username, $new_email, $hashed_password, $role]);
                    $success = "New admin account created successfully!";
                }
            } catch (Exception $e) {
                $error = "Failed to create admin: " . $e->getMessage();
            }
        }
    }
    
    // Delete admin account
    if (isset($_POST['delete_admin']) && $is_super_admin) {
        $delete_id = $_POST['delete_id'] ?? '';
        
        if ($delete_id == $admin_id) {
            $error = "You cannot delete your own account!";
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM admin_users WHERE id = ?");
                $stmt->execute([$delete_id]);
                $success = "Admin account deleted successfully!";
            } catch (Exception $e) {
                $error = "Failed to delete admin: " . $e->getMessage();
            }
        }
    }
}

// Get current admin data
if ($admin_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin_data = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) {
        $error = "Failed to load admin data: " . $e->getMessage();
        $admin_data = [];
    }
}

// Get all admins for super admin
if ($is_super_admin) {
    try {
        $stmt = $pdo->query("SELECT id, username, email, role, created_at, status FROM admin_users ORDER BY created_at DESC");
        $all_admins = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) {
        $error = "Failed to load admin list: " . $e->getMessage();
        $all_admins = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings | Fusion I.T. Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .settings-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .settings-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 32px;
        }
        
        .settings-card {
            background: white;
            border-radius: var(--radius);
            padding: 32px;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-200);
        }
        
        .settings-card h2 {
            font-size: 20px;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .toggle-button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 16px 24px;
            border-radius: var(--radius-sm);
            font-weight: 500;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            margin-bottom: 0;
            transition: all 0.3s ease;
        }
        
        .toggle-button:hover {
            background: var(--primary-dark);
        }
        
        .toggle-button i {
            transition: transform 0.3s ease;
        }
        
        .toggle-button.expanded i {
            transform: rotate(180deg);
        }
        
        .toggle-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease-out, padding 0.3s ease;
        }
        
        .toggle-content.expanded {
            max-height: 2000px;
            padding-top: 32px;
        }
        
        .form-section {
            background: var(--gray-50);
            border-radius: var(--radius);
            padding: 32px;
            margin-top: 24px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 8px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-sm);
            font-size: 14px;
            color: var(--gray-800);
            background: white;
            transition: border-color 0.2s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: var(--radius-sm);
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        .btn-danger {
            background: var(--error);
            color: white;
        }
        
        .btn-danger:hover {
            background: #b91c1c;
        }
        
        .btn-secondary {
            background: var(--gray-200);
            color: var(--gray-700);
        }
        
        .btn-secondary:hover {
            background: var(--gray-300);
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .admin-table th {
            text-align: left;
            padding: 12px 16px;
            background: var(--gray-50);
            border-bottom: 2px solid var(--gray-200);
            font-size: 12px;
            font-weight: 600;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .admin-table td {
            padding: 16px;
            border-bottom: 1px solid var(--gray-200);
            font-size: 14px;
        }
        
        .admin-table tr:hover {
            background: var(--gray-50);
        }
        
        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .role-super-admin {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .role-admin {
            background: #dbeafe;
            color: var(--primary);
        }
        
        .status-active {
            color: var(--success);
        }
        
        .status-inactive {
            color: var(--error);
        }
        
        .password-toggle {
            position: relative;
        }
        
        .password-toggle-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray-500);
            cursor: pointer;
            font-size: 18px;
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: var(--radius-sm);
            margin-bottom: 24px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-success {
            background: #f0fdf4;
            color: var(--success);
            border: 1px solid #a7f3d0;
        }
        
        .alert-error {
            background: #fee2e2;
            color: var(--error);
            border: 1px solid #fecaca;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--gray-600);
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 24px;
        }
        
        .back-link:hover {
            color: var(--gray-800);
        }
        
        @media (max-width: 768px) {
            .settings-card {
                padding: 24px 16px;
            }
            
            .form-section {
                padding: 24px 16px;
            }
            
            .toggle-button {
                padding: 14px 20px;
                font-size: 15px;
            }
            
            .admin-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="welcome">
                <h1>Super Admin Settings</h1>
                <p>Manage your account and admin users</p>
            </div>
            <div class="admin-info">
                <div class="avatar">
                    <?= strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)) ?>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>

        <!-- Back link -->
        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>

        <!-- Alerts -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <div class="settings-container">
            <div class="settings-grid">
                <!-- Profile Settings Toggle Button -->
                <div class="settings-card">
                    <button type="button" class="toggle-button" id="profileToggle">
                        <i class="fas fa-chevron-down"></i>
                        <span>Profile Settings</span>
                    </button>
                    
                    <div class="toggle-content" id="profileContent">
                        <div class="form-section">
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" 
                                           id="username" 
                                           name="username" 
                                           class="form-control" 
                                           value="<?= htmlspecialchars($admin_data['username'] ?? '') ?>"
                                           <?= !$is_super_admin ? 'readonly' : '' ?>
                                           required>
                                    <?php if (!$is_super_admin): ?>
                                        <small style="color: var(--gray-500); font-size: 12px;">Only super admin can change username</small>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           class="form-control" 
                                           value="<?= htmlspecialchars($admin_data['email'] ?? '') ?>"
                                           required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="current_password">Current Password (required for changes)</label>
                                    <div class="password-toggle">
                                        <input type="password" 
                                               id="current_password" 
                                               name="current_password" 
                                               class="form-control">
                                        <button type="button" class="password-toggle-btn" onclick="togglePassword('current_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_password">New Password (leave blank to keep current)</label>
                                    <div class="password-toggle">
                                        <input type="password" 
                                               id="new_password" 
                                               name="new_password" 
                                               class="form-control">
                                        <button type="button" class="password-toggle-btn" onclick="togglePassword('new_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <div class="password-toggle">
                                        <input type="password" 
                                               id="confirm_password" 
                                               name="confirm_password" 
                                               class="form-control">
                                        <button type="button" class="password-toggle-btn" onclick="togglePassword('confirm_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" name="update_profile" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Create New Admin Toggle Button (Only for Super Admin) -->
                <?php if ($is_super_admin): ?>
                    <div class="settings-card">
                        <button type="button" class="toggle-button" id="createAdminToggle">
                            <i class="fas fa-chevron-down"></i>
                            <span>Create New Admin</span>
                        </button>
                        
                        <div class="toggle-content" id="createAdminContent">
                            <div class="form-section">
                                <form method="POST" action="">
                                    <div class="form-group">
                                        <label for="new_username">Username</label>
                                        <input type="text" 
                                               id="new_username" 
                                               name="new_username" 
                                               class="form-control" 
                                               required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="new_email">Email Address</label>
                                        <input type="email" 
                                               id="new_email" 
                                               name="new_email" 
                                               class="form-control" 
                                               required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="new_password_admin">Password</label>
                                        <div class="password-toggle">
                                            <input type="password" 
                                                   id="new_password_admin" 
                                                   name="new_password_admin" 
                                                   class="form-control" 
                                                   required>
                                            <button type="button" class="password-toggle-btn" onclick="togglePassword('new_password_admin')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="confirm_password_admin">Confirm Password</label>
                                        <div class="password-toggle">
                                            <input type="password" 
                                                   id="confirm_password_admin" 
                                                   name="confirm_password_admin" 
                                                   class="form-control" 
                                                   required>
                                            <button type="button" class="password-toggle-btn" onclick="togglePassword('confirm_password_admin')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="role">Role</label>
                                        <select id="role" name="role" class="form-control">
                                            <option value="admin">Admin</option>
                                            <option value="super_admin">Super Admin</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <button type="submit" name="create_admin" class="btn btn-primary">
                                            <i class="fas fa-plus-circle"></i> Create Admin Account
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Admin List -->
                    <div class="settings-card">
                        <h2><i class="fas fa-users"></i> Admin Users</h2>
                        <?php if (empty($all_admins)): ?>
                            <p style="color: var(--gray-500); text-align: center; padding: 20px 0;">
                                No admin users found.
                            </p>
                        <?php else: ?>
                            <div style="overflow-x: auto;">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($all_admins as $admin): ?>
                                            <tr>
                                                <td>#<?= $admin['id'] ?></td>
                                                <td>
                                                    <?= htmlspecialchars($admin['username']) ?>
                                                    <?php if ($admin['id'] == $admin_id): ?>
                                                        <span style="color: var(--primary); font-size: 12px;">(You)</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($admin['email']) ?></td>
                                                <td>
                                                    <span class="role-badge role-<?= str_replace('_', '-', $admin['role']) ?>">
                                                        <?= ucfirst(str_replace('_', ' ', $admin['role'])) ?>
                                                    </span>
                                                </td>
                                                <td class="status-<?= $admin['status'] ?>">
                                                    <i class="fas fa-circle" style="font-size: 10px;"></i>
                                                    <?= ucfirst($admin['status']) ?>
                                                </td>
                                                <td><?= date('M j, Y', strtotime($admin['created_at'])) ?></td>
                                                <td>
                                                    <?php if ($admin['id'] != $admin_id): ?>
                                                        <form method="POST" action="" style="display: inline;">
                                                            <input type="hidden" name="delete_id" value="<?= $admin['id'] ?>">
                                                            <button type="submit" 
                                                                    name="delete_admin" 
                                                                    class="btn btn-danger" 
                                                                    onclick="return confirm('Are you sure you want to delete this admin?')">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span style="color: var(--gray-500); font-size: 12px;">Current user</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.parentNode.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Toggle functionality for collapsible sections
        document.addEventListener('DOMContentLoaded', function() {
            const profileToggle = document.getElementById('profileToggle');
            const profileContent = document.getElementById('profileContent');
            const createAdminToggle = document.getElementById('createAdminToggle');
            const createAdminContent = document.getElementById('createAdminContent');
            
            // Check if there are errors or success messages
            const hasError = document.querySelector('.alert-error') !== null;
            const hasSuccess = document.querySelector('.alert-success') !== null;
            
            // Auto-expand sections if there are messages
            if (hasError || hasSuccess) {
                // Expand profile section by default if there are messages
                if (profileToggle && profileContent) {
                    profileToggle.classList.add('expanded');
                    profileContent.classList.add('expanded');
                }
                
                // If there's a create admin error, expand that section too
                if (hasError && createAdminToggle && createAdminContent) {
                    createAdminToggle.classList.add('expanded');
                    createAdminContent.classList.add('expanded');
                }
            }
            
            // Profile settings toggle
            if (profileToggle) {
                profileToggle.addEventListener('click', function() {
                    toggleSection(this, profileContent);
                });
            }
            
            // Create admin toggle
            if (createAdminToggle) {
                createAdminToggle.addEventListener('click', function() {
                    toggleSection(this, createAdminContent);
                });
            }
        });
        
        function toggleSection(button, content) {
            const isExpanded = button.classList.contains('expanded');
            
            if (isExpanded) {
                // Collapse
                button.classList.remove('expanded');
                content.classList.remove('expanded');
            } else {
                // Expand
                button.classList.add('expanded');
                content.classList.add('expanded');
            }
        }
        
        // Password strength indicator
        document.getElementById('new_password')?.addEventListener('input', function(e) {
            const password = e.target.value;
            const strength = checkPasswordStrength(password);
            updatePasswordStrength(strength);
        });
        
        function checkPasswordStrength(password) {
            let score = 0;
            if (password.length >= 8) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;
            return score;
        }
        
        function updatePasswordStrength(strength) {
            const indicator = document.getElementById('password-strength') || createPasswordIndicator();
            const colors = ['#dc2626', '#f59e0b', '#10b981', '#2563eb'];
            const messages = ['Very Weak', 'Weak', 'Good', 'Strong'];
            
            indicator.style.width = (strength * 25) + '%';
            indicator.style.backgroundColor = colors[strength - 1] || '#dc2626';
            indicator.parentNode.querySelector('span').textContent = messages[strength - 1] || 'Very Weak';
        }
        
        function createPasswordIndicator() {
            const container = document.createElement('div');
            container.style.cssText = `
                margin-top: 8px;
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 12px;
            `;
            
            const bar = document.createElement('div');
            bar.style.cssText = `
                flex: 1;
                height: 4px;
                background: var(--gray-200);
                border-radius: 2px;
                overflow: hidden;
            `;
            
            const indicator = document.createElement('div');
            indicator.id = 'password-strength';
            indicator.style.cssText = `
                height: 100%;
                width: 0%;
                transition: all 0.3s;
            `;
            
            const text = document.createElement('span');
            text.style.color = 'var(--gray-600)';
            
            bar.appendChild(indicator);
            container.appendChild(bar);
            container.appendChild(text);
            
            const passwordInput = document.getElementById('new_password');
            if (passwordInput) {
                passwordInput.parentNode.appendChild(container);
            }
            
            return indicator;
        }
    </script>
</body>
</html>