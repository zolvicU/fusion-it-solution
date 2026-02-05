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
    <link rel="stylesheet" href="../assets/css/blog-list.css">
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