<?php
require_once '../config/database.php';

// Fetch all blog posts (latest first)
try {
    $stmt = $pdo->query("SELECT * FROM blog_posts ORDER BY created_at DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Failed to load posts.";
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
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f9fafb;
            color: #333;
            margin: 0;
            padding: 40px 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        header {
            text-align: center;
            margin-bottom: 60px;
        }
        header h1 {
            font-size: 36px;
            color: #003087;
            margin-bottom: 10px;
        }
        header p {
            font-size: 18px;
            color: #555;
        }
        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }
        .post-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .post-card:hover {
            transform: translateY(-5px);
        }
        .post-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .post-content {
            padding: 20px;
        }
        .post-title {
            font-size: 20px;
            font-weight: 600;
            margin: 0 0 10px;
            color: #003087;
        }
        .post-title a {
            text-decoration: none;
            color: inherit;
        }
        .post-title a:hover {
            color: #0066ff;
        }
        .post-meta {
            font-size: 14px;
            color: #888;
            margin-bottom: 10px;
        }
        .post-excerpt {
            color: #555;
            margin-bottom: 15px;
        }
        .read-more {
            color: #0066ff;
            font-weight: 500;
            text-decoration: none;
        }
        .read-more:hover {
            text-decoration: underline;
        }
        .no-posts {
            text-align: center;
            font-size: 18px;
            color: #666;
            padding: 60px;
        }
        footer {
            text-align: center;
            margin-top: 80px;
            padding-top: 40px;
            border-top: 1px solid #eee;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Our Blog</h1>
            <p>Latest insights, tips, and news from Fusion I.T. Solution</p>
        </header>

        <?php if (empty($posts)): ?>
            <p class="no-posts">No blog posts yet. Check back soon!</p>
        <?php else: ?>
            <div class="blog-grid">
                <?php foreach ($posts as $post): ?>
                    <article class="post-card">
                        <?php if (!empty($post['image'])): ?>
                            <img src="../assets/uploads/blog/<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="post-image">
                        <?php else: ?>
                            <div style="height:200px; background:#eee; display:flex; align-items:center; justify-content:center; color:#aaa;">No Image</div>
                        <?php endif; ?>

                        <div class="post-content">
                            <h2 class="post-title">
                                <a href="post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </h2>
                            <div class="post-meta">
                                <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                            </div>
                            <p class="post-excerpt">
                                <?php 
                                $excerpt = substr(strip_tags($post['content']), 0, 150);
                                echo htmlspecialchars($excerpt) . (strlen(strip_tags($post['content'])) > 150 ? '...' : '');
                                ?>
                            </p>
                            <a href="post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="read-more">Read More →</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <footer>
            <p><a href="../index.php">← Back to Home</a></p>
        </footer>
    </div>
</body>
</html>