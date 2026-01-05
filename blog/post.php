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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post ? htmlspecialchars($post['title']) . ' | Fusion I.T. Blog' : 'Post Not Found'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --slate-900: #0f172a;
            --slate-700: #334155;
            --slate-500: #64748b;
            --slate-100: #f1f5f9;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #ffffff;
            color: var(--slate-700);
            margin: 0;
            line-height: 1.8;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 800px; /* Slightly wider for better balance */
            margin: 0 auto;
            padding: 40px 20px 100px;
        }

        /* Top Breadcrumb */
        .breadcrumb {
            margin-bottom: 32px;
            font-size: 14px;
            font-weight: 500;
        }
        .breadcrumb a {
            color: var(--slate-500);
            text-decoration: none;
        }
        .breadcrumb a:hover { color: var(--primary); }
        .breadcrumb span { color: var(--slate-500); margin: 0 8px; }

        header {
            margin-bottom: 40px;
        }

        header h1 {
            font-size: clamp(28px, 5vw, 42px);
            font-weight: 800;
            color: var(--slate-900);
            margin: 0 0 20px;
            line-height: 1.2;
            letter-spacing: -0.02em;
        }

        .post-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
            color: var(--slate-500);
        }

        .author-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        /* FIXED IMAGE STYLING */
        .post-image-container {
            width: 100%;
            margin-bottom: 40px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .post-image {
            width: 100%;
            height: auto;
            display: block;
            object-fit: contain; /* Ensures the whole image is visible */
        }

        .post-content {
            font-size: 18px;
            color: var(--slate-700);
        }

        .post-content p { margin-bottom: 24px; }
        
        .post-content h2 { 
            font-size: 28px; 
            color: var(--slate-900); 
            margin: 40px 0 20px; 
        }

        .navigation {
            border-top: 1px solid var(--slate-100);
            padding-top: 40px;
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-primary { background: var(--primary); color: white; }
        .btn-outline { border: 1px solid var(--slate-100); color: var(--slate-500); }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }

    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($error)): ?>
            <div style="text-align: center; padding: 100px 0;">
                <h1>Post Not Found</h1>
                <a href="index.php" class="btn btn-primary">Back to Blog</a>
            </div>
        <?php else: ?>
            <nav class="breadcrumb">
                <a href="../index.php">Home</a><span>/</span><a href="index.php">Blog</a><span>/</span>Post
            </nav>

            <header>
                <h1><?= htmlspecialchars($post['title']) ?></h1>
                <div class="post-meta">
                    <div class="author-avatar">F</div>
                    <div>
                        <span style="color: var(--slate-900); font-weight: 600;">Fusion Team</span><br>
                        <span><?= date('F j, Y', strtotime($post['created_at'])) ?></span>
                    </div>
                </div>
            </header>

            <?php if (!empty($post['image'])): ?>
                <div class="post-image-container">
                    <img src="../assets/uploads/blog/<?= htmlspecialchars($post['image']) ?>" 
                         alt="<?= htmlspecialchars($post['title']) ?>" 
                         class="post-image">
                </div>
            <?php endif; ?>

            <div class="post-content">
                <?= nl2br(htmlspecialchars_decode($post['content'])) ?>
            </div>

            <div class="navigation">
                <a href="index.php" class="btn btn-outline">← All Posts</a>
                <a href="../index.php" class="btn btn-primary">Go Home</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>