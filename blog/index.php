<?php
require_once '../config/database.php';

// Pagination
$posts_per_page = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $posts_per_page;

// Fetch total posts for pagination
try {
    $total_stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts");
    $total_posts = $total_stmt->fetchColumn();
    $total_pages = ceil($total_posts / $posts_per_page);
    
    $stmt = $pdo->prepare("SELECT * FROM blog_posts ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $posts_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Failed to load posts.";
}

// Function to estimate reading time
function get_reading_time($content) {
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200);
    return $reading_time . " min";
}

// Function to generate excerpt
function generate_excerpt($content, $length = 120) {
    $excerpt = strip_tags($content);
    if (strlen($excerpt) > $length) {
        $excerpt = substr($excerpt, 0, $length);
        $excerpt = substr($excerpt, 0, strrpos($excerpt, ' ')) . '...';
    }
    return $excerpt;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog | Fusion I.T. Solutions - Insights & Innovation</title>
    <meta name="description" content="Expert perspectives on I.T. solutions, networking, and digital transformation from Fusion I.T. Solutions team.">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="Blog | Fusion I.T. Solutions">
    <meta property="og:description" content="Expert insights on technology and digital solutions">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #3b82f6;
            --primary-50: #eff6ff;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #dc2626;
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-500: #64748b;
            --slate-400: #94a3b8;
            --slate-300: #cbd5e1;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50: #f8fafc;
            --radius-sm: 8px;
            --radius: 12px;
            --radius-md: 16px;
            --radius-lg: 20px;
            --radius-xl: 24px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            line-height: 1.7;
            font-weight: 300; /* Inter Light */
            color: var(--slate-700);
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* All text elements use Light weight by default */
        h1, h2, h3, h4, h5, h6,
        p, span, div, a, li,
        .blog-title, .blog-subtitle,
        .post-title, .post-excerpt,
        .read-more, .meta-item,
        .stat-number, .stat-label,
        .page-btn, .page-info,
        .cta-title, .cta-text,
        .cta-button, .back-button,
        .empty-title, .empty-text {
            font-family: 'Inter', sans-serif;
            font-weight: 300; /* Inter Light */
        }

        /* Make specific elements slightly bolder for hierarchy */
        .blog-title,
        .post-title,
        .cta-title,
        .empty-title {
            font-weight: 400; /* Slightly heavier for titles */
        }

        .stat-number,
        .read-more,
        .cta-button,
        .back-button {
            font-weight: 400; /* Medium weight for interactive elements */
        }

        .post-category {
            font-weight: 400; /* Medium for badges */
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 20px 80px;
        }

        /* Header */
        .blog-header {
            text-align: center;
            padding: 80px 0 60px;
            position: relative;
        }

        .blog-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 400px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 2rem;
        }

        .header-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }

        .blog-title {
            font-size: clamp(40px, 5vw, 56px);
            color: white;
            margin-bottom: 20px;
            line-height: 1.2;
            letter-spacing: -0.02em;
        }

        .blog-subtitle {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 32px;
            line-height: 1.6;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .stats-bar {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 48px;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 32px;
            color: white;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Blog Grid */
        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 32px;
            margin-bottom: 60px;
        }

        @media (max-width: 768px) {
            .blog-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Post Card */
        .post-card {
            background: white;
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--slate-200);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .post-card:hover {
            transform: translateY(-12px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary);
        }

        /* Image Container */
        .image-container {
            position: relative;
            width: 100%;
            height: 240px;
            overflow: hidden;
        }

        .post-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s ease;
        }

        .post-card:hover .post-image {
            transform: scale(1.08);
        }

        .image-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.3), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .post-card:hover .image-overlay {
            opacity: 1;
        }

        .post-category {
            position: absolute;
            top: 20px;
            left: 20px;
            background: var(--primary);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 2;
        }

        /* Post Content */
        .post-content {
            padding: 28px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .post-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
            font-size: 14px;
            color: var(--slate-500);
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .meta-item i {
            font-size: 14px;
            color: var(--primary);
        }

        .post-title {
            font-size: 22px;
            color: var(--slate-900);
            margin-bottom: 16px;
            line-height: 1.4;
            transition: color 0.2s ease;
        }

        .post-title a {
            color: inherit;
            text-decoration: none;
        }

        .post-card:hover .post-title {
            color: var(--primary);
        }

        .post-excerpt {
            font-size: 16px;
            color: var(--slate-600);
            margin-bottom: 24px;
            line-height: 1.6;
            flex: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .post-footer {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid var(--slate-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .read-more {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            font-size: 15px;
            text-decoration: none;
            transition: all 0.3s ease;
            padding: 8px 16px;
            border-radius: var(--radius);
            background: var(--primary-50);
        }

        .read-more:hover {
            background: var(--primary);
            color: white;
            transform: translateX(4px);
        }

        .reading-time {
            font-size: 13px;
            color: var(--slate-500);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-top: 60px;
            flex-wrap: wrap;
        }

        .page-btn {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius);
            background: white;
            border: 1px solid var(--slate-300);
            color: var(--slate-700);
            text-decoration: none;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .page-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .page-btn.active {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .page-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .page-info {
            font-size: 15px;
            color: var(--slate-600);
            margin: 0 20px;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 60px 40px;
            border-radius: var(--radius-xl);
            text-align: center;
            margin: 80px 0;
            box-shadow: var(--shadow-xl);
        }

        .cta-title {
            font-size: 32px;
            margin-bottom: 16px;
        }

        .cta-text {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 32px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-button {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: white;
            color: var(--primary);
            padding: 16px 32px;
            border-radius: var(--radius);
            text-decoration: none;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            background: var(--slate-100);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 100px 20px;
        }

        .empty-icon {
            font-size: 64px;
            color: var(--slate-300);
            margin-bottom: 24px;
            opacity: 0.5;
        }

        .empty-title {
            font-size: 24px;
            color: var(--slate-700);
            margin-bottom: 12px;
        }

        .empty-text {
            font-size: 16px;
            color: var(--slate-600);
            margin-bottom: 32px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Back Navigation */
        .back-nav {
            text-align: center;
            margin-top: 60px;
            padding-top: 40px;
            border-top: 1px solid var(--slate-200);
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 14px 28px;
            background: var(--slate-100);
            color: var(--slate-700);
            border-radius: var(--radius);
            text-decoration: none;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background: var(--slate-200);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 0 16px 60px;
            }
            
            .blog-header {
                padding: 60px 0 40px;
            }
            
            .blog-title {
                font-size: 32px;
            }
            
            .blog-subtitle {
                font-size: 16px;
            }
            
            .stats-bar {
                gap: 24px;
            }
            
            .stat-number {
                font-size: 24px;
            }
            
            .post-content {
                padding: 24px;
            }
            
            .post-title {
                font-size: 20px;
            }
            
            .cta-section {
                padding: 40px 24px;
            }
            
            .cta-title {
                font-size: 24px;
            }
            
            .cta-text {
                font-size: 16px;
            }
        }

        @media (max-width: 480px) {
            .blog-grid {
                gap: 24px;
            }
            
            .image-container {
                height: 200px;
            }
            
            .post-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .post-footer {
                flex-direction: column;
                gap: 16px;
                align-items: stretch;
            }
            
            .read-more {
                justify-content: center;
            }
        }

        /* Additional Light Font Styling */
        input, textarea, select, button {
            font-family: 'Inter', sans-serif;
            font-weight: 300;
        }

        /* Make numbers in stats slightly heavier */
        .stat-number {
            font-weight: 400;
        }

        /* Ensure all text has consistent light weight */
        small, .label-hint, .helper-text {
            font-weight: 300;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <header class="blog-header">
            <div class="header-content">
                <h1 class="blog-title">Insights & Innovation</h1>
                <p class="blog-subtitle">
                    Expert perspectives on I.T. solutions, networking, and digital transformation 
                    from our team of technology specialists.
                </p>
                
                <div class="stats-bar">
                    <div class="stat-item">
                        <div class="stat-number"><?= $total_posts ?? 0 ?></div>
                        <div class="stat-label">Articles Published</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?= date('Y') ?></div>
                        <div class="stat-label">Current Year</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Expert Support</div>
                    </div>
                </div>
            </div>
        </header>

        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <h2 class="empty-title">No Articles Yet</h2>
                <p class="empty-text">We're currently preparing insightful content. Check back soon for expert perspectives on I.T. solutions!</p>
                <a href="../index.php" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                    Return to Homepage
                </a>
            </div>
        <?php else: ?>
            <!-- Blog Grid -->
            <div class="blog-grid">
                <?php foreach ($posts as $post): ?>
                    <article class="post-card">
                        <div class="image-container">
                            <?php if (!empty($post['image'])): ?>
                                <img src="../assets/uploads/blog/<?= htmlspecialchars($post['image']) ?>" 
                                     alt="<?= htmlspecialchars($post['title']) ?>"
                                     class="post-image">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); 
                                     display: flex; align-items: center; justify-content: center; color: white; font-size: 20px;">
                                    <i class="fas fa-newspaper" style="font-size: 48px; opacity: 0.8;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="image-overlay"></div>
                            <span class="post-category">Technology</span>
                        </div>
                        
                        <div class="post-content">
                            <div class="post-meta">
                                <span class="meta-item">
                                    <i class="far fa-calendar"></i>
                                    <?= date('F j, Y', strtotime($post['created_at'])) ?>
                                </span>
                                <span class="meta-item">
                                    <i class="far fa-clock"></i>
                                    <?= get_reading_time($post['content']) ?>
                                </span>
                            </div>
                            
                            <h2 class="post-title">
                                <a href="post.php?slug=<?= htmlspecialchars($post['slug']) ?>">
                                    <?= htmlspecialchars($post['title']) ?>
                                </a>
                            </h2>
                            
                            <p class="post-excerpt">
                                <?= generate_excerpt($post['content'], 150) ?>
                            </p>
                            
                            <div class="post-footer">
                                <a href="post.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="read-more">
                                    Read Article
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                                <span class="reading-time">
                                    <i class="fas fa-book-open"></i>
                                    <?= get_reading_time($post['content']) ?>
                                </span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <a href="?page=<?= max(1, $page - 1) ?>" 
                       class="page-btn <?= $page <= 1 ? 'disabled' : '' ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                            <a href="?page=<?= $i ?>" 
                               class="page-btn <?= $i == $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                            <span class="page-btn" style="border: none; background: transparent; cursor: default;">...</span>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <a href="?page=<?= min($total_pages, $page + 1) ?>" 
                       class="page-btn <?= $page >= $total_pages ? 'disabled' : '' ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    
                    <div class="page-info">
                        Page <?= $page ?> of <?= $total_pages ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- CTA Section -->
            <div class="cta-section">
                <h2 class="cta-title">Looking for Reliable I.T. Solutions?</h2>
                <p class="cta-text">
                    Our team provides cutting-edge networking, security, and digital infrastructure 
                    services for businesses of all sizes. Let's discuss your technology needs.
                </p>
                <a href="../contact.php" class="cta-button">
                    <i class="fas fa-phone-alt"></i>
                    Contact Our Team
                </a>
            </div>
        <?php endif; ?>

        <!-- Back Navigation -->
        <div class="back-nav">
            <a href="../index.php" class="back-button">
                <i class="fas fa-home"></i>
                Return to Homepage
            </a>
        </div>
    </div>

    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add hover effect to post cards
        document.querySelectorAll('.post-card').forEach(card => {
            const image = card.querySelector('.post-image');
            
            card.addEventListener('mouseenter', function() {
                if (image && image.tagName === 'IMG') {
                    image.style.transition = 'transform 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.1)';
                }
            });
            
            card.addEventListener('mouseleave', function() {
                if (image && image.tagName === 'IMG') {
                    image.style.transition = 'transform 0.6s ease';
                }
            });
        });

        // Lazy loading for images
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.post-image');
            
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.getAttribute('src');
                        observer.unobserve(img);
                    }
                });
            }, { rootMargin: '100px 0px' });
            
            images.forEach(img => imageObserver.observe(img));
        });

        // Add scroll animation to header
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.blog-header');
            const scrolled = window.pageYOffset;
            
            if (header && scrolled > 50) {
                header.style.transform = `translateY(${scrolled * 0.1}px)`;
                header.style.opacity = 1 - (scrolled / 500);
            }
        });

        // Keyboard navigation for posts
        document.addEventListener('keydown', function(e) {
            // Left/Right arrows for pagination
            if (e.key === 'ArrowLeft' && <?= $page > 1 ? 'true' : 'false' ?>) {
                window.location.href = `?page=<?= $page - 1 ?>`;
            }
            
            if (e.key === 'ArrowRight' && <?= $page < $total_pages ? 'true' : 'false' ?>) {
                window.location.href = `?page=<?= $page + 1 ?>`;
            }
        });

        // Add animation to stat numbers
        function animateStats() {
            const statNumbers = document.querySelectorAll('.stat-number');
            
            statNumbers.forEach(stat => {
                const target = parseInt(stat.textContent);
                let current = 0;
                const increment = target / 100;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    stat.textContent = Math.floor(current);
                }, 20);
            });
        }

        // Trigger animation when stats come into view
        const statsBar = document.querySelector('.stats-bar');
        if (statsBar) {
            const observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) {
                    animateStats();
                    observer.disconnect();
                }
            }, { threshold: 0.5 });
            
            observer.observe(statsBar);
        }
    </script>
</body>
</html>