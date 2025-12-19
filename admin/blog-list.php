<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';

// Fetch all blog posts
try {
    $stmt = $pdo->query("SELECT * FROM blog_posts ORDER BY created_at DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Failed to load posts: " . $e->getMessage();
}

$success_msg = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'added':
            $success_msg = "Blog post added successfully!";
            break;
        case 'updated':
            $success_msg = "Blog post updated successfully!";
            break;
        case 'deleted':
            $success_msg = "Blog post deleted successfully!";
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Posts | Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --primary-dark: #3651d4;
            --success: #10b981;
            --error: #dc2626;
            --gray-100: #f8fafc;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 20px 40px rgba(0, 0, 0, 0.08);
            --radius: 16px;
            --radius-sm: 12px;
        }

        body {
            font-family: 'Inter', sans-serif;
            font-weight: 300;
            line-height: 1.6;
            color: var(--gray-800);
            background: #f9fafb;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .card-header {
            background: var(--primary);
            color: white;
            padding: 40px 32px;
            text-align: center;
        }

        .card-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin: 0;
        }

        .card-header p {
            margin: 12px 0 0;
            font-size: 15px;
            opacity: 0.9;
            font-weight: 400;
        }

        .card-body {
            padding: 40px 32px;
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 32px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border-radius: var(--radius-sm);
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }

        .btn.primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
        }

        .btn.primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn.secondary {
            background: #f1f5f9;
            color: var(--primary);
            border: 1px solid var(--primary-light);
        }

        .btn.secondary:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }

        .btn.edit {
            background: var(--success);
            color: white;
            padding: 8px 16px;
            font-size: 14px;
        }

        .btn.edit:hover {
            background: #059669;
        }

        .btn.delete {
            background: var(--error);
            color: white;
            padding: 8px 16px;
            font-size: 14px;
        }

        .btn.delete:hover {
            background: #b91c1c;
        }

        .alert {
            padding: 16px 20px;
            border-radius: var(--radius-sm);
            margin-bottom: 28px;
            font-size: 15px;
            font-weight: 500;
            text-align: center;
            border: 1px solid transparent;
        }

        .alert.success {
            background: #d1fae5;
            color: var(--success);
            border-color: #a7f3d0;
        }

        .alert.error {
            background: #fee2e2;
            color: var(--error);
            border-color: #fecaca;
        }

        .posts-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-top: 32px;
        }

        .post-card {
            background: white;
            border-radius: var(--radius-sm);
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 24px;
            box-shadow: var(--shadow);
            transition: all 0.2s ease;
        }

        .post-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .post-id {
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-600);
            min-width: 50px;
        }

        .post-thumb {
            width: 100px;
            height: 80px;
            border-radius: var(--radius-sm);
            overflow: hidden;
            border: 1px solid var(--gray-300);
            flex-shrink: 0;
        }

        .post-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .placeholder {
            width: 100%;
            height: 100%;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-600);
            font-size: 13px;
        }

        .post-info {
            flex: 1;
        }

        .post-title {
            font-size: 17px;
            font-weight: 500;
            color: var(--gray-800);
            margin-bottom: 8px;
        }

        .post-meta {
            display: flex;
            gap: 16px;
            font-size: 14px;
            color: var(--gray-600);
        }

        .post-slug {
            font-family: monospace;
            background: var(--gray-100);
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 13px;
        }

        .post-actions {
            display: flex;
            gap: 12px;
        }

        .no-items {
            text-align: center;
            padding: 80px 20px;
            color: var(--gray-600);
            font-size: 16px;
        }

        .no-items .link {
            color: var(--primary);
            font-weight: 500;
            text-decoration: none;
        }

        .no-items .link:hover {
            text-decoration: underline;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 40px;
            color: var(--primary);
            font-weight: 500;
            text-decoration: none;
            font-size: 15px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .back-to-dashboard-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: #5270f3ff;
            color: white;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
        }

        .back-to-dashboard-btn:hover {
            background: #3651d4;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.3);
        }

        @media (max-width: 768px) {
            .post-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .post-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>Blog Posts Management</h1>
                <p>Manage all your blog content in one place</p>
            </div>

            <div class="card-body">
                <?php if ($success_msg): ?>
                    <div class="alert success"><?= $success_msg ?></div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="action-buttons">
                    <a href="add-blog.php" class="btn primary">+ Add New Post</a>
                </div>

                <?php if (empty($posts)): ?>
                    <div class="no-items">
                        <p>No blog posts yet.</p>
                        <a href="add-blog.php" class="link">Create your first post</a>
                    </div>
                <?php else: ?>
                    <div class="posts-list">
                        <?php foreach ($posts as $post): ?>
                            <div class="post-card">
                                <div class="post-id">#<?= $post['id'] ?></div>

                                <div class="post-thumb">
                                    <?php if (!empty($post['image'])): ?>
                                        <img src="../assets/uploads/blog/<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                                    <?php else: ?>
                                        <div class="placeholder">No Image</div>
                                    <?php endif; ?>
                                </div>

                                <div class="post-info">
                                    <h3 class="post-title"><?= htmlspecialchars($post['title']) ?></h3>
                                    <div class="post-meta">
                                        <span class="post-slug"><?= htmlspecialchars($post['slug']) ?></span>
                                        <span class="post-date"><?= date('M j, Y', strtotime($post['created_at'])) ?></span>
                                    </div>
                                </div>

                                <div class="post-actions">
                                    <a href="edit-blog.php?id=<?= $post['id'] ?>" class="btn edit">Edit</a>
                                    <a href="delete-blog.php?id=<?= $post['id'] ?>" class="btn delete" onclick="return confirm('Delete this post permanently?')">Delete</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Back to Dashboard Button at bottom (replacing Logout) -->
                <div style="text-align: center; margin-top: 40px;">
                    <a href="dashboard.php" class="back-to-dashboard-btn">← Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>