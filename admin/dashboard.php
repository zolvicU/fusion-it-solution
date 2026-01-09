<?php require_once 'includes/auth.php'; ?>
<?php

require_once '../config/database.php';

/* Fetch all statistics in one go */
try {
    // Products
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $total_products = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE is_featured = 1");
    $featured_products = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 5");
    $recent_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Blog posts
    $stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts");
    $total_posts = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT * FROM blog_posts ORDER BY created_at DESC LIMIT 3");
    $recent_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Messages
    $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0");
    $unread_messages = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages");
    $total_messages = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 3");
    $recent_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error = "Failed to load data: " . $e->getMessage();
}

/* Success messages */
$success_msg = '';
if (isset($_GET['msg'])) {
    $messages = [
        'added' => "Product added successfully!",
        'updated' => "Product updated successfully!",
        'deleted' => "Product deleted successfully!",
        'blog_added' => "Blog post added successfully!",
        'blog_updated' => "Blog post updated successfully!",
        'blog_deleted' => "Blog post deleted successfully!"
    ];
    $success_msg = $messages[$_GET['msg']] ?? '';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Fusion I.T. Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #dc2626;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            --shadow-md: 0 10px 15px -3px rgba(0,0,0,0.1);
            --radius-sm: 8px;
            --radius: 12px;
            --radius-lg: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: var(--gray-800);
            line-height: 1.5;
            min-height: 100vh;
            padding: 24px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--gray-200);
        }

        .welcome h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 4px;
        }

        .welcome p {
            color: var(--gray-600);
            font-size: 14px;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .avatar {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 18px;
        }

        .logout-btn {
            background: var(--error);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: opacity 0.2s;
        }

        .logout-btn:hover {
            opacity: 0.9;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius);
            padding: 24px;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-200);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .stat-icon.products { background: #eff6ff; color: var(--primary); }
        .stat-icon.blog { background: #f0fdf4; color: var(--success); }
        .stat-icon.messages { background: #fef3c7; color: var(--warning); }
        .stat-icon.featured { background: #fce7f3; color: #ec4899; }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--gray-600);
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .section {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .section-header {
            background: #f8fafc;
            padding: 20px 24px;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-header h3 {
            font-size: 16px;
            font-weight: 600;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-header a {
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .section-body {
            padding: 24px;
        }

        /* Lists */
        .item-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .list-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: var(--radius-sm);
            transition: background 0.2s;
        }

        .list-item:hover {
            background: var(--gray-50);
        }

        .item-thumb {
            width: 60px;
            height: 40px;
            border-radius: 6px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .item-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .no-thumb {
            background: var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-500);
            font-size: 12px;
        }

        .item-info {
            flex: 1;
            min-width: 0;
        }

        .item-title {
            font-weight: 500;
            color: var(--gray-800);
            margin-bottom: 4px;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .item-meta {
            font-size: 12px;
            color: var(--gray-500);
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge.featured {
            background: #d1fae5;
            color: var(--success);
        }

        .badge.unread {
            background: #fee2e2;
            color: var(--error);
        }

        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 16px;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }

        .action-btn {
            flex: 1;
            min-width: 160px;
            background: white;
            border: 1px solid var(--gray-200);
            padding: 20px;
            border-radius: var(--radius);
            text-decoration: none;
            color: var(--gray-800);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .action-btn:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .action-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-sm);
            background: var(--gray-100);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .action-text h4 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .action-text p {
            font-size: 13px;
            color: var(--gray-600);
        }

        /* Alert */
        .alert {
            padding: 16px 20px;
            border-radius: var(--radius-sm);
            margin-bottom: 24px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert.success {
            background: #f0fdf4;
            color: var(--success);
            border: 1px solid #a7f3d0;
        }

        .alert.error {
            background: #fee2e2;
            color: var(--error);
            border: 1px solid #fecaca;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding-top: 24px;
            border-top: 1px solid var(--gray-200);
            color: var(--gray-500);
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 16px;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
            
            .admin-info {
                width: 100%;
                justify-content: space-between;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                flex-direction: column;
            }
            
            .action-btn {
                min-width: 100%;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="welcome">
                <h1>Welcome, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></h1>
                <p>Dashboard Overview</p>
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

        <!-- Success Message -->
        <?php if ($success_msg): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success_msg) ?>
            </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="add-product.php" class="action-btn">
                <div class="action-icon">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="action-text">
                    <h4>Add Product</h4>
                    <p>Create new product listing</p>
                </div>
            </a>
            
            <a href="add-blog.php" class="action-btn">
                <div class="action-icon">
                    <i class="fas fa-edit"></i>
                </div>
                <div class="action-text">
                    <h4>Write Post</h4>
                    <p>Create new blog content</p>
                </div>
            </a>
            
            <a href="messages.php" class="action-btn">
                <div class="action-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="action-text">
                    <h4>Messages</h4>
                    <p><?= $unread_messages > 0 ? "{$unread_messages} unread" : "View messages" ?></p>
                </div>
            </a>
            
            <a href="blog-list.php" class="action-btn">
                <div class="action-icon">
                    <i class="fas fa-blog"></i>
                </div>
                <div class="action-text">
                    <h4>Manage Blog</h4>
                    <p>View all blog posts</p>
                </div>
            </a>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon products">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
                <div class="stat-value"><?= $total_products ?></div>
                <div class="stat-label">Total Products</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon featured">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div class="stat-value"><?= $featured_products ?></div>
                <div class="stat-label">Featured Products</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon blog">
                        <i class="fas fa-newspaper"></i>
                    </div>
                </div>
                <div class="stat-value"><?= $total_posts ?></div>
                <div class="stat-label">Blog Posts</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon messages">
                        <i class="fas fa-comments"></i>
                    </div>
                </div>
                <div class="stat-value"><?= $total_messages ?></div>
                <div class="stat-label">Total Messages</div>
            </div>
        </div>

        <!-- Content Sections -->
        <div class="content-grid">
            <!-- Recent Products -->
            <div class="section">
                <div class="section-header">
                    <h3><i class="fas fa-box-open"></i> Recent Products</h3>
                    <a href="products.php">View All</a>
                </div>
                <div class="section-body">
                    <?php if (empty($recent_products)): ?>
                        <p style="color: var(--gray-500); text-align: center; padding: 20px 0;">
                            No products yet. <a href="add-product.php" style="color: var(--primary);">Add your first product</a>
                        </p>
                    <?php else: ?>
                        <div class="item-list">
                            <?php foreach ($recent_products as $product): ?>
                                <a href="edit-product.php?id=<?= $product['id'] ?>" class="list-item">
                                    <div class="item-thumb">
                                        <?php if (!empty($product['image'])): ?>
                                            <img src="../assets/uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                                                 alt="<?= htmlspecialchars($product['title']) ?>">
                                        <?php else: ?>
                                            <div class="no-thumb">No Image</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="item-info">
                                        <div class="item-title"><?= htmlspecialchars($product['title']) ?></div>
                                        <div class="item-meta">ID: #<?= $product['id'] ?></div>
                                    </div>
                                    <?php if ($product['is_featured']): ?>
                                        <span class="badge featured">Featured</span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Blog Posts -->
            <div class="section">
                <div class="section-header">
                    <h3><i class="fas fa-newspaper"></i> Recent Posts</h3>
                    <a href="blog-list.php">View All</a>
                </div>
                <div class="section-body">
                    <?php if (empty($recent_posts)): ?>
                        <p style="color: var(--gray-500); text-align: center; padding: 20px 0;">
                            No posts yet. <a href="add-blog.php" style="color: var(--primary);">Write your first post</a>
                        </p>
                    <?php else: ?>
                        <div class="item-list">
                            <?php foreach ($recent_posts as $post): ?>
                                <a href="edit-blog.php?id=<?= $post['id'] ?>" class="list-item">
                                    <div class="item-thumb">
                                        <?php if (!empty($post['image'])): ?>
                                            <img src="../assets/uploads/blog/<?= htmlspecialchars($post['image']) ?>" 
                                                 alt="<?= htmlspecialchars($post['title']) ?>">
                                        <?php else: ?>
                                            <div class="no-thumb">No Image</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="item-info">
                                        <div class="item-title"><?= htmlspecialchars($post['title']) ?></div>
                                        <div class="item-meta"><?= date('M j, Y', strtotime($post['created_at'])) ?></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Messages -->
            <div class="section">
                <div class="section-header">
                    <h3><i class="fas fa-envelope"></i> Recent Messages</h3>
                    <a href="messages.php">View All</a>
                </div>
                <div class="section-body">
                    <?php if (empty($recent_messages)): ?>
                        <p style="color: var(--gray-500); text-align: center; padding: 20px 0;">
                            No messages yet.
                        </p>
                    <?php else: ?>
                        <div class="item-list">
                            <?php foreach ($recent_messages as $message): ?>
                                <a href="messages.php#message-<?= $message['id'] ?>" class="list-item">
                                    <div class="item-info" style="flex: 1;">
                                        <div class="item-title"><?= htmlspecialchars($message['name']) ?></div>
                                        <div class="item-meta">
                                            <?= htmlspecialchars(substr($message['message'], 0, 50)) . (strlen($message['message']) > 50 ? '...' : '') ?>
                                        </div>
                                    </div>
                                    <div class="item-meta" style="font-size: 11px;">
                                        <?= date('M j', strtotime($message['created_at'])) ?>
                                    </div>
                                    <?php if (!$message['is_read']): ?>
                                        <span class="badge unread">New</span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Fusion I.T. Solutions Admin Panel • <?= date('Y') ?></p>
        </div>
    </div>

    <script>
        // Simple hover effects
        document.querySelectorAll('.list-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f8fafc';
            });
            item.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });

        // Update message count periodically
        function updateMessageCount() {
            // Simulate checking for new messages
            const messageCount = document.querySelector('.action-btn:nth-child(3) .action-text p');
            if (messageCount) {
                const currentText = messageCount.textContent;
                if (currentText.includes('unread')) {
                    // Simulate increment (in real app, fetch from server)
                    const match = currentText.match(/(\d+)/);
                    if (match) {
                        const count = parseInt(match[0]) + 1;
                        messageCount.textContent = `${count} unread`;
                    }
                }
            }
        }

        // Check for new messages every 30 seconds (simulated)
        // setInterval(updateMessageCount, 30000);

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Alt + P for products
            if (e.altKey && e.key === 'p') {
                e.preventDefault();
                window.location.href = 'products.php';
            }
            
            // Alt + B for blog
            if (e.altKey && e.key === 'b') {
                e.preventDefault();
                window.location.href = 'blog-list.php';
            }
            
            // Alt + M for messages
            if (e.altKey && e.key === 'm') {
                e.preventDefault();
                window.location.href = 'messages.php';
            }
            
            // Alt + N for new product
            if (e.altKey && e.key === 'n') {
                e.preventDefault();
                window.location.href = 'add-product.php';
            }
        });

        // Add click animations
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                // Add ripple effect
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(37, 99, 235, 0.2);
                    transform: scale(0);
                    animation: ripple 0.6s linear;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                `;
                
                this.style.position = 'relative';
                this.style.overflow = 'hidden';
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Add CSS for ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>