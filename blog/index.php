<?php
require_once '../config/database.php';

// Fetch all blog posts (latest first)
try {
    $stmt = $pdo->query("SELECT * FROM blog_posts ORDER BY created_at DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Failed to load posts.";
}

// Function to estimate reading time
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
    <title>Blog | Fusion I.T. Solution</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3651d4;
            --accent: #f72585;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg: #f8fafc;
            --white: #ffffff;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            --shadow-hover: 0 20px 40px rgba(67, 97, 238, 0.15);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text-main);
            margin: 0;
            line-height: 1.7;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 20px 100px;
        }

        header {
            text-align: center;
            margin-bottom: 70px;
            position: relative;
        }

        header h2 {
            font-size: 42px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 16px;
            letter-spacing: -1px;
        }

        header p {
            font-size: 18px;
            color: var(--text-muted);
            max-width: 550px;
            margin: 0 auto;
        }

        header::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: var(--primary);
            margin: 24px auto 0;
            border-radius: 2px;
        }

        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 32px;
        }

        .post-card {
            background: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            border: 1px solid rgba(226, 232, 240, 0.8);
            display: flex;
            flex-direction: column;
        }

        .post-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
            border-color: rgba(67, 97, 238, 0.2);
        }

        /* Image Zoom Effect */
        .image-wrapper {
            position: relative;
            width: 100%;
            height: 240px;
            overflow: hidden;
        }

        .post-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .post-card:hover .post-image {
            transform: scale(1.08);
        }

        .category-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background: var(--primary);
            color: white;
            padding: 5px 14px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 2;
        }

        .post-content {
            padding: 28px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .post-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 12px;
        }

        .post-title {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 14px;
            line-height: 1.4;
        }

        .post-title a {
            color: #0f172a;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .post-title a:hover {
            color: var(--primary);
        }

        .post-excerpt {
            font-size: 15px;
            color: #475569;
            margin-bottom: 24px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-footer {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .read-more {
            color: var(--primary);
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: gap 0.3s ease;
        }

        .read-more:hover {
            gap: 10px;
        }

        .nav-back-wrapper {
            text-align: center;
            margin-top: 80px;
        }

        .nav-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 28px;
            font-size: 15px;
            font-weight: 600;
            color: var(--primary);
            background: var(--white);
            border: 2px solid var(--primary);
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .nav-back-btn:hover {
            background: var(--primary);
            color: var(--white);
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.2);
        }

        @media (max-width: 768px) {
            header h2 { font-size: 32px; }
            .blog-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h2>Insights & Innovation</h2>
            <p>Expert perspective on the future of I.T. and digital solutions.</p>
        </header>

        <?php if (empty($posts)): ?>
            <div class="no-posts">
                <p>We are currently writing new stories. Check back soon!</p>
            </div>
        <?php else: ?>
            <div class="blog-grid">
                <?php foreach ($posts as $post): ?>
                    <article class="post-card">
                        <div class="image-wrapper">
                            <?php if (!empty($post['image'])): ?>
                                <img src="../assets/uploads/blog/<?= htmlspecialchars($post['image']) ?>"
                                    alt="<?= htmlspecialchars($post['title']) ?>"
                                    class="post-image">
                            <?php else: ?>
                                <div style="height: 100%; background: #e2e8f0; display:flex; align-items:center; justify-content:center; color:#94a3b8;">
                                    Fusion I.T.
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="post-content">
                            <div class="post-meta">
                                <span><?= date('M j, Y', strtotime($post['created_at'])) ?></span>
                                <span>•</span>
                            </div>

                            <h2 class="post-title">
                                <a href="post.php?slug=<?= htmlspecialchars($post['slug']) ?>">
                                    <?= htmlspecialchars($post['title']) ?>
                                </a>
                            </h2>
                            
                            <p class="post-excerpt">
                                <?= htmlspecialchars(substr(strip_tags($post['content']), 0, 120)) ?>...
                            </p>

                            <div class="card-footer">
                                <a href="post.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="read-more">
                                    Continue Reading →
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="nav-back-wrapper">
            <a href="../index.php" class="nav-back-btn">← Return to Homepage</a>
        </div>
    </div>
</body>
</html>