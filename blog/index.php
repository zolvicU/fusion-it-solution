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
            color: #1e293b;
            margin: 0;
            line-height: 1.7;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 80px 20px 120px;
        }

        header {
            text-align: center;
            margin-bottom: 80px;
        }

        header h2 {
            font-size: 35px;
            font-weight: 700;
            color: #000000ff;
            margin: 0 0 16px;
        }

        header p {
            font-size: 20px;
            color: #64748b;
            max-width: 600px;
            margin: 0 auto;
        }

        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
            gap: 40px;
        }

        .post-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            transition: all 0.4s ease;
            border: 1px solid #e2e8f0;
        }

        .post-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }

        .post-image {
            width: 100%;
            height: 240px;
            object-fit: cover;
        }

        .post-image-placeholder {
            height: 240px;
            background: linear-gradient(135deg, #e0f2fe 0%, #f8fbff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 16px;
            font-weight: 500;
        }

        .post-content {
            padding: 32px;
        }

        .post-title {
            font-size: 24px;
            font-weight: 600;
            margin: 0 0 16px;
            line-height: 1.3;
        }

        .post-title a {
            color: #1e293b;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .post-title a:hover {
            color: #4361ee;
        }

        .post-meta {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 16px;
        }

        .post-excerpt {
            font-size: 16px;
            color: #475569;
            margin-bottom: 24px;
            line-height: 1.6;
        }

        .read-more {
            color: #4361ee;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .read-more:hover {
            color: #3651d4;
            transform: translateX(4px);
        }

        .read-more span {
            font-size: 18px;
        }

        .no-posts {
            text-align: center;
            padding: 120px 20px;
            color: #64748b;
            font-size: 18px;
        }

        /* Container for alignment */
        .nav-back-wrapper {
            text-align: center;
            /* Keeps it centered like your previous .back-home class */
            margin-top: 60px;
            /* Adjusted spacing */
            padding-top: 10px;
        }

        /* Updated Styling to match the white-bordered theme */
        .nav-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 22px;
            font-size: 14px;
            font-weight: 600;
            color: #0047ff;
            background: #ffffff;
            border: 2px solid #0047ff;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        /* Hover State */
        .nav-back-btn:hover {
            background: #0047ff;
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 71, 255, 0.25);
        }

        /* Active State */
        .nav-back-btn:active {
            transform: translateY(0px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 60px 16px 100px;
            }

            header h2 {
                font-size: 30px;
            }

            header p {
                font-size: 18px;
            }

            .blog-grid {
                grid-template-columns: 1fr;
            }

            .post-image,
            .post-image-placeholder {
                height: 220px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h2>Our Blog</h2>
            <p>Latest insights, tips, and news from Fusion I.T. Solution</p>
        </header>

        <?php if (empty($posts)): ?>
            <div class="no-posts">
                <p>No blog posts yet. Check back soon!</p>
            </div>
        <?php else: ?>
            <div class="blog-grid">
                <?php foreach ($posts as $post): ?>
                    <article class="post-card">
                        <?php if (!empty($post['image'])): ?>
                            <img src="../assets/uploads/blog/<?= htmlspecialchars($post['image']) ?>"
                                alt="<?= htmlspecialchars($post['title']) ?>"
                                class="post-image">
                        <?php else: ?>
                            <div class="post-image-placeholder">Featured Image</div>
                        <?php endif; ?>

                        <div class="post-content">
                            <h2 class="post-title">
                                <a href="post.php?slug=<?= htmlspecialchars($post['slug']) ?>">
                                    <?= htmlspecialchars($post['title']) ?>
                                </a>
                            </h2>
                            <div class="post-meta">
                                <?= date('F j, Y', strtotime($post['created_at'])) ?>
                            </div>
                            <p class="post-excerpt">
                                <?php
                                $excerpt = strip_tags($post['content']);
                                echo htmlspecialchars(substr($excerpt, 0, 150)) . (strlen($excerpt) > 150 ? '...' : '');
                                ?>
                            </p>
                            <a href="post.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="read-more">
                                Read More <span>→</span>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="nav-back-wrapper">
            <a href="../index.php" class="nav-back-btn">← Back to Home</a>
        </div>
    </div>
</body>

</html>