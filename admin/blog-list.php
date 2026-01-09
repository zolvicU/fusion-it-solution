<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';

// Pagination variables
$posts_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $posts_per_page;

// Fetch total number of posts
try {
    $total_stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts");
    $total_posts = $total_stmt->fetchColumn();
    $total_pages = ceil($total_posts / $posts_per_page);
    
    // Fetch paginated posts
    $stmt = $pdo->prepare("SELECT * FROM blog_posts ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $posts_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
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
    <title>Blog Posts | Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #3b82f6;
            --primary-50: #eff6ff;
            --success: #10b981;
            --success-dark: #059669;
            --warning: #f59e0b;
            --error: #dc2626;
            --error-dark: #b91c1c;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --radius-sm: 6px;
            --radius: 10px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            font-weight: 400;
            color: var(--gray-700);
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
        .dashboard-header {
            margin-bottom: 32px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .header-title h1 {
            font-size: 24px;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 4px;
        }

        .header-title p {
            font-size: 14px;
            color: var(--gray-600);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-200);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 16px;
        }

        .stat-icon.posts { background: var(--primary-50); color: var(--primary); }
        .stat-icon.new { background: #f0fdf4; color: var(--success); }
        .stat-icon.draft { background: #fefce8; color: var(--warning); }

        .stat-value {
            font-size: 32px;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--gray-600);
        }

        /* Main Card */
        .main-card {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            border: 1px solid var(--gray-200);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 32px 40px;
        }

        .card-header h2 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-header h2::before {
            content: "📝";
            font-size: 18px;
        }

        .card-header p {
            font-size: 14px;
            opacity: 0.9;
            font-weight: 400;
        }

        .card-body {
            padding: 40px;
        }

        /* Action Bar */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: var(--radius);
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: var(--shadow);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary {
            background: white;
            color: var(--gray-700);
            border-color: var(--gray-300);
        }

        .btn-secondary:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
            transform: translateY(-2px);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--error) 0%, var(--error-dark) 100%);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Alerts */
        .alert {
            padding: 16px 20px;
            border-radius: var(--radius);
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 4px solid;
        }

        .alert.success {
            background: #f0fdf4;
            color: var(--success);
            border-color: var(--success);
        }

        .alert.error {
            background: #fef2f2;
            color: var(--error);
            border-color: var(--error);
        }

        .alert i {
            font-size: 16px;
        }

        /* Posts Table */
        .posts-table-container {
            overflow-x: auto;
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
            background: white;
        }

        .posts-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        .posts-table thead {
            background: var(--gray-50);
            border-bottom: 2px solid var(--gray-200);
        }

        .posts-table th {
            padding: 16px 20px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: var(--gray-700);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--gray-200);
        }

        .posts-table tbody tr {
            border-bottom: 1px solid var(--gray-100);
            transition: all 0.2s ease;
        }

        .posts-table tbody tr:hover {
            background: var(--gray-50);
        }

        .posts-table td {
            padding: 20px;
            font-size: 14px;
            color: var(--gray-700);
            vertical-align: middle;
        }

        .post-thumb {
            width: 60px;
            height: 40px;
            border-radius: var(--radius-sm);
            overflow: hidden;
            border: 1px solid var(--gray-300);
        }

        .post-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .no-image {
            width: 100%;
            height: 100%;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-500);
            font-size: 10px;
        }

        .post-title-cell {
            min-width: 250px;
        }

        .post-title {
            font-weight: 500;
            color: var(--gray-800);
            margin-bottom: 4px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .post-slug {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: 12px;
            color: var(--gray-500);
            background: var(--gray-50);
            padding: 4px 8px;
            border-radius: var(--radius-sm);
            display: inline-block;
            margin-top: 4px;
        }

        .post-date {
            font-size: 13px;
            color: var(--gray-600);
            white-space: nowrap;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-published {
            background: #d1fae5;
            color: var(--success-dark);
        }

        .status-draft {
            background: #fef3c7;
            color: #d97706;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            white-space: nowrap;
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s ease;
            font-size: 14px;
        }

        .btn-edit {
            background: var(--primary-50);
            color: var(--primary);
        }

        .btn-edit:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: #fee2e2;
            color: var(--error);
        }

        .btn-delete:hover {
            background: var(--error);
            color: white;
            transform: translateY(-2px);
        }

        .btn-view {
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .btn-view:hover {
            background: var(--gray-300);
            transform: translateY(-2px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 40px;
        }

        .empty-state i {
            font-size: 64px;
            color: var(--gray-300);
            margin-bottom: 24px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 12px;
        }

        .empty-state p {
            font-size: 14px;
            color: var(--gray-600);
            margin-bottom: 24px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .pagination-btn {
            width: 36px;
            height: 36px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border: 1px solid var(--gray-300);
            color: var(--gray-700);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .pagination-btn:hover {
            background: var(--primary-50);
            border-color: var(--primary);
            color: var(--primary);
        }

        .pagination-btn.active {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .pagination-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .page-info {
            font-size: 14px;
            color: var(--gray-600);
            margin: 0 12px;
        }

        /* Footer Actions */
        .footer-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 40px;
            padding-top: 32px;
            border-top: 1px solid var(--gray-200);
            flex-wrap: wrap;
            gap: 20px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 12px;
            }
            
            .container {
                padding: 0 12px;
            }
            
            .card-body {
                padding: 24px;
            }
            
            .card-header {
                padding: 24px;
            }
            
            .posts-table th,
            .posts-table td {
                padding: 12px 16px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 6px;
            }
            
            .btn-icon {
                width: 32px;
                height: 32px;
            }
            
            .header-top {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
            
            .action-bar {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .footer-actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* Search and Filter */
        .search-filter-bar {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 200px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 16px;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
        }

        .filter-select {
            min-width: 150px;
        }

        .filter-select select {
            width: 100%;
            padding: 10px 16px;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            background: white;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="dashboard-header">
            <div class="header-top">
                <div class="header-title">
                    <h1>Blog Management</h1>
                    <p>Manage and publish your blog content</p>
                </div>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon posts">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="stat-value"><?= $total_posts ?? 0 ?></div>
                    <div class="stat-label">Total Posts</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon new">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div class="stat-value"><?= isset($posts) ? count($posts) : 0 ?></div>
                    <div class="stat-label">Displayed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon draft">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value">0</div>
                    <div class="stat-label">Drafts</div>
                </div>
            </div>
        </div>

        <div class="main-card">
            <div class="card-header">
                <h2>All Blog Posts</h2>
                <p>Create, edit, and manage your blog posts</p>
            </div>

            <div class="card-body">
                <?php if ($success_msg): ?>
                    <div class="alert success">
                        <i class="fas fa-check-circle"></i>
                        <?= $success_msg ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <div class="action-bar">
                    <a href="add-blog.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add New Post
                    </a>
                    
                    <div class="search-filter-bar">
                        <div class="search-box">
                            <input type="text" placeholder="Search posts..." id="searchInput">
                        </div>
                        <div class="filter-select">
                            <select id="statusFilter">
                                <option value="">All Status</option>
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                    </div>
                </div>

                <?php if (empty($posts)): ?>
                    <div class="empty-state">
                        <i class="fas fa-newspaper"></i>
                        <h3>No Blog Posts Yet</h3>
                        <p>Start creating content for your audience. Your first blog post is just a click away.</p>
                        <a href="add-blog.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Create Your First Post
                        </a>
                    </div>
                <?php else: ?>
                    <div class="posts-table-container">
                        <table class="posts-table">
                            <thead>
                                <tr>
                                    <th width="60px">ID</th>
                                    <th width="80px">Image</th>
                                    <th>Title & Slug</th>
                                    <th width="120px">Status</th>
                                    <th width="120px">Date</th>
                                    <th width="140px">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="postsTableBody">
                                <?php foreach ($posts as $post): ?>
                                    <tr class="post-row" data-title="<?= strtolower(htmlspecialchars($post['title'])) ?>">
                                        <td>
                                            <span class="post-id">#<?= $post['id'] ?></span>
                                        </td>
                                        <td>
                                            <div class="post-thumb">
                                                <?php if (!empty($post['image'])): ?>
                                                    <img src="../assets/uploads/blog/<?= htmlspecialchars($post['image']) ?>" 
                                                        alt="<?= htmlspecialchars($post['title']) ?>"
                                                        onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2MCIgaGVpZ2h0PSI0MCIgdmlld0JveD0iMCAwIDYwIDQwIj48cmVjdCB3aWR0aD0iNjAiIGhlaWdodD0iNDAiIGZpbGw9IiNmM2Y0ZjYiLz48dGV4dCB4PSIzMCIgeT0iMjIiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxMiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzZiNzI4MCI+Tm8gSW1hZ2U8L3RleHQ+PC9zdmc+';">
                                                <?php else: ?>
                                                    <div class="no-image">No Image</div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="post-title-cell">
                                            <div class="post-title"><?= htmlspecialchars($post['title']) ?></div>
                                            <div class="post-slug">/<?= htmlspecialchars($post['slug']) ?></div>
                                        </td>
                                        <td>
                                            <span class="status-badge status-published">Published</span>
                                        </td>
                                        <td>
                                            <div class="post-date"><?= date('M j, Y', strtotime($post['created_at'])) ?></div>
                                            <small style="color: var(--gray-500); font-size: 12px;">
                                                <?= date('g:i A', strtotime($post['created_at'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="../blog/post.php?slug=<?= urlencode($post['slug']) ?>" 
                                                   target="_blank"
                                                   class="btn-icon btn-view" 
                                                   title="View Post">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit-blog.php?id=<?= $post['id'] ?>" 
                                                   class="btn-icon btn-edit" 
                                                   title="Edit Post">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete-blog.php?id=<?= $post['id'] ?>" 
                                                   class="btn-icon btn-delete" 
                                                   title="Delete Post"
                                                   onclick="return confirm('Are you sure you want to delete this post?\n\nTitle: <?= addslashes($post['title']) ?>\n\nThis action cannot be undone.');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <a href="?page=<?= max(1, $page - 1) ?>" 
                               class="pagination-btn <?= $page <= 1 ? 'disabled' : '' ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?= $i ?>" 
                                   class="pagination-btn <?= $i == $page ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                            
                            <a href="?page=<?= min($total_pages, $page + 1) ?>" 
                               class="pagination-btn <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                            
                            <div class="page-info">
                                Showing <?= count($posts) ?> of <?= $total_posts ?> posts
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="footer-actions">
                    <div>
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                    </div>
                    <div>
                        <a href="add-blog.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i>
                            Add Another Post
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#postsTableBody .post-row');
            
            rows.forEach(row => {
                const title = row.getAttribute('data-title');
                const slug = row.querySelector('.post-slug').textContent.toLowerCase();
                const isVisible = title.includes(searchTerm) || slug.includes(searchTerm);
                row.style.display = isVisible ? '' : 'none';
            });
        });

        // Status filter functionality
        document.getElementById('statusFilter').addEventListener('change', function(e) {
            const filterValue = e.target.value;
            const rows = document.querySelectorAll('#postsTableBody .post-row');
            
            rows.forEach(row => {
                if (!filterValue) {
                    row.style.display = '';
                    return;
                }
                
                const statusBadge = row.querySelector('.status-badge').textContent.toLowerCase();
                row.style.display = statusBadge.includes(filterValue) ? '' : 'none';
            });
        });

        // Add hover effect to table rows
        document.querySelectorAll('.posts-table tbody tr').forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(4px)';
                this.style.transition = 'transform 0.2s ease';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0)';
            });
        });

        // Quick actions on click
        document.querySelectorAll('.post-title').forEach(title => {
            title.style.cursor = 'pointer';
            title.addEventListener('click', function() {
                const editLink = this.closest('tr').querySelector('.btn-edit');
                if (editLink) {
                    window.location.href = editLink.href;
                }
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + N to add new post
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                window.location.href = 'add-blog.php';
            }
            
            // Escape to clear search
            if (e.key === 'Escape') {
                document.getElementById('searchInput').value = '';
                document.querySelectorAll('.post-row').forEach(row => {
                    row.style.display = '';
                });
            }
        });

        // Focus search input on / key press
        document.addEventListener('keydown', function(e) {
            if (e.key === '/' && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
                document.getElementById('searchInput').focus();
            }
        });

        // Initialize tooltips
        document.querySelectorAll('.btn-icon').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                const title = this.getAttribute('title');
                if (title) {
                    const tooltip = document.createElement('div');
                    tooltip.className = 'tooltip';
                    tooltip.textContent = title;
                    tooltip.style.cssText = `
                        position: fixed;
                        background: var(--gray-800);
                        color: white;
                        padding: 6px 12px;
                        border-radius: 4px;
                        font-size: 12px;
                        z-index: 10000;
                        pointer-events: none;
                    `;
                    document.body.appendChild(tooltip);
                    
                    const rect = this.getBoundingClientRect();
                    tooltip.style.left = (rect.left + rect.width/2 - tooltip.offsetWidth/2) + 'px';
                    tooltip.style.top = (rect.top - tooltip.offsetHeight - 8) + 'px';
                    
                    this.tooltip = tooltip;
                }
            });
            
            btn.addEventListener('mouseleave', function() {
                if (this.tooltip) {
                    this.tooltip.remove();
                    this.tooltip = null;
                }
            });
        });
    </script>
</body>
</html>