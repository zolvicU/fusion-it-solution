<?php
require_once '../config/database.php';

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    $error = "No post specified.";
} else {
    try {
        $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE slug = ?");
        $stmt->execute([$slug]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$post) {
            $error = "Post not found.";
        }
    } catch (Exception $e) {
        $error = "Error loading post.";
    }
}

// Calculate reading time
function get_reading_time($content) {
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200);
    return $reading_time . " min read";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post ? htmlspecialchars($post['title']) . ' | Fusion I.T. Solutions' : 'Post Not Found'; ?></title>
    
    <!-- Meta Description -->
    <meta name="description" content="<?php echo $post ? htmlspecialchars(substr(strip_tags($post['content']), 0, 160)) . '...' : 'Fusion I.T. Solutions blog post'; ?>">
    
    <!-- Open Graph Tags -->
    <?php if ($post): ?>
    <meta property="og:title" content="<?= htmlspecialchars($post['title']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars(substr(strip_tags($post['content']), 0, 200)) . '...' ?>">
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?= htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>">
    <?php if (!empty($post['image'])): ?>
    <meta property="og:image" content="<?= htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . '/assets/uploads/blog/' . $post['image']) ?>">
    <?php endif; ?>
    <?php endif; ?>
    
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

        /* Make all text use Light weight by default */
        h1, h2, h3, h4, h5, h6,
        p, span, div, a, li,
        .post-title, .post-content,
        .breadcrumb, .post-meta,
        .btn, .navigation {
            font-family: 'Inter', sans-serif;
            font-weight: 300;
        }

        /* Specific weight adjustments for hierarchy */
        .post-title,
        .btn,
        .breadcrumb {
            font-weight: 400;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px 80px;
        }

        /* Breadcrumb */
        .breadcrumb {
            margin-bottom: 48px;
            font-size: 14px;
        }

        .breadcrumb a {
            color: var(--slate-500);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .breadcrumb a:hover {
            color: var(--primary);
        }

        .breadcrumb span {
            color: var(--slate-400);
            margin: 0 12px;
        }

        /* Header */
        .post-header {
            margin-bottom: 48px;
        }

        .post-title {
            font-size: clamp(32px, 4vw, 44px);
            color: var(--slate-900);
            margin: 0 0 24px;
            line-height: 1.2;
            letter-spacing: -0.02em;
        }

        /* Post Meta */
        .post-meta {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-bottom: 24px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--slate-200);
            font-size: 14px;
            color: var(--slate-500);
        }

        .author-info {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .author-avatar {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: var(--shadow);
        }

        .author-details h4 {
            font-size: 16px;
            color: var(--slate-800);
            margin-bottom: 4px;
        }

        .author-details p {
            font-size: 14px;
            color: var(--slate-500);
        }

        .meta-stats {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--slate-500);
        }

        .meta-item i {
            color: var(--primary);
            font-size: 16px;
        }

        /* Featured Image */
        .featured-image {
            width: 100%;
            margin-bottom: 48px;
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            position: relative;
        }

        .featured-image img {
            width: 100%;
            height: auto;
            display: block;
            transition: transform 0.8s ease;
        }

        .featured-image:hover img {
            transform: scale(1.02);
        }

        .image-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.6));
            color: white;
            padding: 20px;
            font-size: 14px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .featured-image:hover .image-caption {
            opacity: 1;
        }

        /* Post Content */
        .post-content {
            font-size: 17px;
            color: var(--slate-700);
            margin-bottom: 60px;
        }

        .post-content > * {
            margin-bottom: 28px;
        }

        .post-content p {
            font-size: 17px;
            line-height: 1.8;
            color: var(--slate-700);
        }

        .post-content h2 {
            font-size: 28px;
            color: var(--slate-900);
            margin: 48px 0 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--slate-200);
        }

        .post-content h3 {
            font-size: 22px;
            color: var(--slate-800);
            margin: 40px 0 20px;
        }

        .post-content ul, .post-content ol {
            padding-left: 24px;
            margin: 24px 0;
        }

        .post-content li {
            margin-bottom: 12px;
            line-height: 1.6;
        }

        .post-content blockquote {
            border-left: 4px solid var(--primary);
            padding-left: 28px;
            margin: 40px 0;
            font-style: italic;
            color: var(--slate-600);
            background: var(--slate-50);
            padding: 32px;
            border-radius: var(--radius);
            font-size: 18px;
            line-height: 1.6;
        }

        .post-content a {
            color: var(--primary);
            text-decoration: none;
            border-bottom: 1px solid transparent;
            transition: border-color 0.2s ease;
        }

        .post-content a:hover {
            border-bottom: 1px solid var(--primary);
        }

        /* Share Section */
        .share-section {
            background: var(--slate-50);
            border-radius: var(--radius-lg);
            padding: 32px;
            margin: 60px 0;
            text-align: center;
        }

        .share-title {
            font-size: 18px;
            color: var(--slate-800);
            margin-bottom: 20px;
        }

        .share-buttons {
            display: flex;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .share-btn {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: transform 0.3s ease;
            font-size: 18px;
        }

        .share-btn:hover {
            transform: translateY(-4px);
        }

        .facebook { background: #1877f2; }
        .twitter { background: #1da1f2; }
        .linkedin { background: #0a66c2; }
        .link { background: var(--slate-600); }

        /* Navigation */
        .navigation {
            border-top: 1px solid var(--slate-200);
            padding-top: 40px;
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 14px 28px;
            border-radius: var(--radius);
            text-decoration: none;
            font-size: 15px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: var(--shadow);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .btn-outline {
            border-color: var(--slate-300);
            color: var(--slate-700);
            background: white;
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-3px);
            box-shadow: var(--shadow);
        }

        /* Error State */
        .error-state {
            text-align: center;
            padding: 120px 20px;
        }

        .error-icon {
            font-size: 64px;
            color: var(--slate-300);
            margin-bottom: 24px;
            opacity: 0.5;
        }

        .error-title {
            font-size: 32px;
            color: var(--slate-800);
            margin-bottom: 16px;
        }

        .error-text {
            font-size: 18px;
            color: var(--slate-600);
            margin-bottom: 40px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 32px 16px 60px;
            }
            
            .post-title {
                font-size: 28px;
            }
            
            .post-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }
            
            .meta-stats {
                width: 100%;
                justify-content: space-between;
            }
            
            .post-content {
                font-size: 16px;
            }
            
            .post-content h2 {
                font-size: 24px;
            }
            
            .post-content h3 {
                font-size: 20px;
            }
            
            .post-content blockquote {
                padding: 24px;
                font-size: 16px;
            }
            
            .navigation {
                flex-direction: column;
                gap: 16px;
                align-items: stretch;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .post-title {
                font-size: 24px;
            }
            
            .author-info {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .meta-stats {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .post-content h2 {
                font-size: 22px;
            }
            
            .share-buttons {
                gap: 12px;
            }
        }

        /* Reading Progress */
        .reading-progress {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--slate-200);
            z-index: 1000;
        }

        .reading-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%);
            width: 0%;
            transition: width 0.3s ease;
        }

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            box-shadow: var(--shadow-lg);
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 100;
            text-decoration: none;
            opacity: 0;
            transform: translateY(20px);
            border: none;
        }

        .back-to-top.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .back-to-top:hover {
            transform: translateY(-4px) scale(1.1);
            box-shadow: var(--shadow-xl);
        }
    </style>
</head>
<body>
    <!-- Reading Progress Bar -->
    <div class="reading-progress">
        <div class="reading-progress-bar" id="progressBar"></div>
    </div>

    <?php if (isset($error)): ?>
        <div class="container">
            <div class="error-state">
                <div class="error-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h1 class="error-title">Post Not Found</h1>
                <p class="error-text">The blog post you're looking for doesn't exist or has been moved.</p>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i>
                    Back to Blog
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="container">
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <a href="../index.php">
                    <i class="fas fa-home"></i>
                    Home
                </a>
                <span>/</span>
                <a href="index.php">
                    <i class="fas fa-newspaper"></i>
                    Blog
                </a>
                <span>/</span>
                <span><?= htmlspecialchars(substr($post['title'], 0, 30)) . (strlen($post['title']) > 30 ? '...' : '') ?></span>
            </nav>

            <!-- Header -->
            <header class="post-header">
                <h1 class="post-title"><?= htmlspecialchars($post['title']) ?></h1>
                
                <div class="post-meta">
                    <div class="author-info">
                        <div class="author-avatar">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="author-details">
                            <h4>Fusion I.T. Solutions Team</h4>
                            <p>Technology & Connectivity Experts</p>
                        </div>
                    </div>
                    <div class="meta-stats">
                        <div class="meta-item">
                            <i class="far fa-calendar"></i>
                            <span><?= date('F j, Y', strtotime($post['created_at'])) ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="far fa-clock"></i>
                            <span><?= get_reading_time($post['content']) ?></span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Featured Image -->
            <?php if (!empty($post['image'])): ?>
                <div class="featured-image">
                    <img src="../assets/uploads/blog/<?= htmlspecialchars($post['image']) ?>" 
                         alt="<?= htmlspecialchars($post['title']) ?>"
                         onerror="this.style.display='none';">
                    <div class="image-caption">
                        Featured image for "<?= htmlspecialchars($post['title']) ?>"
                    </div>
                </div>
            <?php endif; ?>

            <!-- Content -->
            <article class="post-content">
                <?= nl2br(htmlspecialchars_decode($post['content'])) ?>
            </article>

            <!-- Share Section -->
            <div class="share-section">
                <h3 class="share-title">Share this article</h3>
                <div class="share-buttons">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                       target="_blank" 
                       class="share-btn facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($post['title']) ?>" 
                       target="_blank" 
                       class="share-btn twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&title=<?= urlencode($post['title']) ?>" 
                       target="_blank" 
                       class="share-btn linkedin">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="#" 
                       onclick="copyToClipboard('<?= htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>')" 
                       class="share-btn link">
                        <i class="fas fa-link"></i>
                    </a>
                </div>
            </div>

            <!-- Navigation -->
            <div class="navigation">
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Back to Blog
                </a>
                <a href="../index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i>
                    Go to Homepage
                </a>
            </div>
        </div>

        <!-- Back to Top Button -->
        <button class="back-to-top" id="backToTop">
            <i class="fas fa-arrow-up"></i>
        </button>
    <?php endif; ?>

    <script>
        // Reading Progress Bar
        window.addEventListener('scroll', function() {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            document.getElementById("progressBar").style.width = scrolled + "%";
        });

        // Back to Top Button
        const backToTop = document.getElementById('backToTop');
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        });

        backToTop.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Copy to Clipboard
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                const shareBtn = event.target.closest('.share-btn');
                const originalIcon = shareBtn.innerHTML;
                shareBtn.innerHTML = '<i class="fas fa-check"></i>';
                shareBtn.style.background = '#10b981';
                
                setTimeout(() => {
                    shareBtn.innerHTML = originalIcon;
                    shareBtn.style.background = '';
                }, 2000);
            }).catch(function(err) {
                console.error('Failed to copy: ', err);
            });
            return false;
        }

        // Lazy Load Images
        document.addEventListener("DOMContentLoaded", function() {
            const images = document.querySelectorAll('.featured-image img');
            
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

        // Add subtle animation to post title
        const postTitle = document.querySelector('.post-title');
        if (postTitle) {
            postTitle.style.opacity = '0';
            postTitle.style.transform = 'translateY(20px)';
            postTitle.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            
            setTimeout(() => {
                postTitle.style.opacity = '1';
                postTitle.style.transform = 'translateY(0)';
            }, 100);
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Escape key to go back
            if (e.key === 'Escape') {
                window.history.back();
            }
            
            // Space bar to scroll down
            if (e.key === ' ' && !e.target.matches('input, textarea')) {
                e.preventDefault();
                window.scrollBy({
                    top: window.innerHeight * 0.8,
                    behavior: 'smooth'
                });
            }
        });

        // Highlight current section while scrolling
        const sections = document.querySelectorAll('h2, h3');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.color = 'var(--primary)';
                    entry.target.style.transition = 'color 0.3s ease';
                } else {
                    entry.target.style.color = '';
                }
            });
        }, { threshold: 0.5 });

        sections.forEach(section => observer.observe(section));
    </script>
</body>
</html>