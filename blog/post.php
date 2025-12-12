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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f9fafb;
            color: #333;
            margin: 0;
            padding: 40px 20px;
            line-height: 1.8;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        header {
            text-align: center;
            margin-bottom: 50px;
        }
        header h1 {
            font-size: 36px;
            color: #003087;
            margin-bottom: 10px;
        }
        .post-meta {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }
        .post-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .post-content {
            font-size: 18px;
            color: #444;
        }
        .post-content p {
            margin-bottom: 20px;
        }
        .error {
            text-align: center;
            font-size: 20px;
            color: #ef4444;
            padding: 60px;
        }
        footer {
            text-align: center;
            margin-top: 80px;
            padding-top: 40px;
            border-top: 1px solid #eee;
            color: #888;
        }
        .back-link {
            display: inline-block;
            margin-top: 40px;
            color: #0066ff;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="error">
                <h2>Post Not Found</h2>
                <p><?= htmlspecialchars($error) ?></p>
                <a href="index.php">← Back to Blog!</a>
            </div>
        <?php else: ?>
            <header>
                <h1><?= htmlspecialchars($post['title']) ?></h1>
                <div class="post-meta">
                    Published on <?= date('F j, Y', strtotime($post['created_at'])) ?>
                </div>
            </header>

            <?php if (!empty($post['image'])): ?>
                <img src="../assets/uploads/blog/<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="post-image">
            <?php endif; ?>

            <div class="post-content">
                <?= nl2br(htmlspecialchars($post['content'])) ?>
            </div>

            <a href="index.php" class="back-link">← Back to Blog!</a>
        <?php endif; ?>

        <footer>
            <p><a href="../index.php">← Back to Home</a></p>
        </footer>
    </div>
</body>
</html>