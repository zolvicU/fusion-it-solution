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
            color: #1e293b;
            margin: 0;
            line-height: 1.8;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 80px 20px 120px;
        }

        header {
            text-align: center;
            margin-bottom: 60px;
        }

        header h1 {
            font-size: 35px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 16px;
            line-height: 1.2;
        }

        .post-meta {
            font-size: 15px;
            color: #64748b;
            margin-bottom: 40px;
        }

        .post-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 16px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .post-content {
            font-size: 18px;
            color: #475569;
        }

        .post-content p {
            margin-bottom: 24px;
        }

        .post-content h2, .post-content h3 {
            color: #1e293b;
            margin: 40px 0 20px;
        }

        .error {
            text-align: center;
            padding: 120px 20px;
            color: #dc2626;
            font-size: 20px;
        }

        .navigation {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-top: 60px;
            flex-wrap: wrap;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: #4361ee;
            color: white;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
        }

        .back-btn:hover {
            background: #3651d4;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.3);
        }

        footer {
            text-align: center;
            margin-top: 60px;
            padding-top: 40px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 14px;
        }

        footer a {
            color: #4361ee;
            text-decoration: none;
            font-weight: 500;
        }

        footer a:hover {
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
            </div>
        <?php else: ?>
            <header>
                <h1><?= htmlspecialchars($post['title']) ?></h1>
                <div class="post-meta">
                    Published on <?= date('F j, Y', strtotime($post['created_at'])) ?>
                </div>
            </header>

            <?php if (!empty($post['image'])): ?>
                <img src="../assets/uploads/blog/<?= htmlspecialchars($post['image']) ?>" 
                     alt="<?= htmlspecialchars($post['title']) ?>" 
                     class="post-image">
            <?php endif; ?>

            <div class="post-content">
                <?= nl2br(htmlspecialchars_decode($post['content'])) ?>
            </div>

            <div class="navigation">
                <a href="index.php" class="back-btn">← Back to Blog</a>
                <a href="../index.php" class="back-btn">← Back to Home</a>
            </div>
        <?php endif; ?>

        
    </div>
</body>
</html>