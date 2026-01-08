<?php require_once 'includes/auth.php'; ?>
<?php

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';

if (!isset($pdo)) {
    die("Database connection failed.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title   = trim($_POST["title"]);
    $content = trim($_POST["content"]);

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

    $errors = [];

    if (empty($title))   $errors[] = "Title is required.";
    if (empty($content)) $errors[] = "Content is required.";
    if (empty($slug))    $errors[] = "Slug could not be generated from title.";

    $image_name = "";
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $target_dir = "../assets/uploads/blog/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            $errors[] = "File is not a valid image.";
        } elseif ($_FILES["image"]["size"] > 5000000) {
            $errors[] = "Image too large (max 5MB).";
        } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $errors[] = "Only JPG, JPEG, PNG & GIF allowed.";
        } elseif (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $errors[] = "Failed to upload image.";
        }
    }

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO blog_posts (title, slug, content, image) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $slug, $content, $image_name]);

            $message = "<div class='alert success'>Blog post added successfully!</div>";
            $title = $content = "";
        } catch (Exception $e) {
            if ($e->getCode() == 23000) {
                $errors[] = "A post with this title/slug already exists. Try a different title.";
            } else {
                $errors[] = "Database error: " . $e->getMessage();
            }
            $message = "<div class='alert error'>" . implode("<br>", $errors) . "</div>";
        }
    } else {
        $message = "<div class='alert error'>" . implode("<br>", $errors) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Blog Post | Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ==============================================
   Full style.css - Unified Professional Admin & Frontend Design
   Matches your Dashboard (solid blue header, clean cards, elegant spacing)
   Uses Inter font with Light (300) for body, Medium/Bold for emphasis
   ============================================== */

        /* Root Variables */
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --primary-dark: #3651d4;
            --success: #10b981;
            --error: #dc2626;
            --gray-100: #f8fafc;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 20px 40px rgba(0, 0, 0, 0.08);
            --radius: 16px;
            --radius-sm: 12px;
        }

        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            font-weight: 300;
            /* Light for elegant feel */
            line-height: 1.6;
            color: var(--gray-800);
            background: #f9fafb;
        }

        /* Typography */
        h1,
        h2,
        h3,
        h4 {
            font-weight: 600;
            line-height: 1.3;
        }

        p,
        small,
        .post-meta,
        .no-items p {
            font-weight: 300;
            color: var(--gray-700);
        }

        small {
            font-size: 14px;
        }

        /* Container */
        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* Card Base */
        .card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        /* Header (used in Dashboard, Add/Edit, Blog List) */
        .card-header {
            background: var(--primary);
            color: white;
            padding: 40px 32px;
            text-align: center;
        }

        .card-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin: 0;
        }

        .card-header p {
            margin: 12px 0 0;
            font-size: 15px;
            opacity: 0.9;
            font-weight: 400;
        }

        /* Body */
        .card-body {
            padding: 40px 32px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border-radius: var(--radius-sm);
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }

        .btn.primary {
            background: var(--primary);
            color: white;
        }

        .btn.primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn.edit {
            background: var(--success);
            color: white;
            padding: 8px 16px;
            font-size: 14px;
        }

        .btn.edit:hover {
            background: #059669;
        }

        .btn.delete {
            background: var(--error);
            color: white;
            padding: 8px 16px;
            font-size: 14px;
        }

        .btn.delete:hover {
            background: #b91c1c;
        }

        /* Alerts */
        .alert {
            padding: 16px 20px;
            border-radius: var(--radius-sm);
            margin-bottom: 28px;
            font-size: 15px;
            font-weight: 500;
            text-align: center;
            border: 1px solid transparent;
        }

        .alert.success {
            background: #d1fae5;
            color: var(--success);
            border-color: #a7f3d0;
        }

        .alert.error {
            background: #fee2e2;
            color: var(--error);
            border-color: #fecaca;
        }

        /* Form Styles (Add/Edit Blog/Product) */
        .form-group {
            margin-bottom: 28px;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 10px;
            color: var(--gray-800);
            font-size: 15px;
        }

        .form-group input[type="text"],
        .form-group textarea,
        .form-group input[type="file"] {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-sm);
            font-size: 15px;
            background: white;
            transition: border 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
        }

        .form-group textarea {
            min-height: 300px;
            resize: vertical;
        }

        /* File Upload */
        .file-group {
            margin: 40px 0;
        }

        .file-input-label {
            font-size: 15px;
            margin-bottom: 10px;
            color: var(--gray-800);
            font-weight: 500;
        }

        .file-input-label small {
            color: var(--gray-600);
        }

        .file-input {
            display: block;
            width: 100%;
            padding: 40px;
            background: white;
            border: 2px dashed var(--gray-300);
            border-radius: var(--radius);
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--gray-600);
            font-size: 15px;
        }

        .file-input:hover {
            border-color: var(--primary);
            background: #f0f7ff;
            color: var(--primary);
        }

        /* Product & Blog List Cards */
        .posts-list,
        .products-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-top: 32px;
        }

        .post-card,
        .product-card {
            background: white;
            border-radius: var(--radius-sm);
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 24px;
            box-shadow: var(--shadow);
            transition: all 0.2s ease;
        }

        .post-card:hover,
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .post-id,
        .product-id {
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-600);
            min-width: 50px;
        }

        .post-thumb,
        .product-thumb {
            width: 100px;
            height: 80px;
            border-radius: var(--radius-sm);
            overflow: hidden;
            border: 1px solid var(--gray-300);
            flex-shrink: 0;
        }

        .post-thumb img,
        .product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .placeholder {
            width: 100%;
            height: 100%;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-600);
            font-size: 13px;
        }

        .post-info,
        .product-info {
            flex: 1;
        }

        .post-title,
        .product-title {
            font-size: 17px;
            font-weight: 500;
            color: var(--gray-800);
            margin-bottom: 8px;
        }

        .post-meta {
            display: flex;
            gap: 16px;
            font-size: 14px;
            color: var(--gray-600);
        }

        .post-slug {
            font-family: monospace;
            background: var(--gray-100);
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 13px;
        }

        /* Actions */
        .post-actions,
        .product-actions {
            display: flex;
            gap: 12px;
        }

        /* No Items */
        .no-items {
            text-align: center;
            padding: 80px 20px;
            color: var(--gray-600);
            font-size: 16px;
        }

        .no-items .link {
            color: var(--primary);
            font-weight: 500;
            text-decoration: none;
        }

        .no-items .link:hover {
            text-decoration: underline;
        }

        /* Back / Logout Link */
        .back-link,
        .logout a {
            display: block;
            text-align: center;
            margin-top: 40px;
            color: var(--primary);
            font-weight: 500;
            text-decoration: none;
            font-size: 15px;
        }

        .back-link:hover,
        .logout a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 0 16px;
            }

            .card-header {
                padding: 32px 24px;
            }

            .card-body {
                padding: 32px 24px;
            }

            .post-card, 
            .product-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .post-actions,
            .product-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>Add New Blog Post</h1>
                <p>Create engaging content for your website visitors</p>
            </div>

            <div class="card-body">
                <?php if ($message): ?>
                    <?php echo $message; ?>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Blog Title</label>
                        <input type="text" name="title" id="title" value="<?= isset($title) ? htmlspecialchars($title) : '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="content">Blog Content</label>
                        <textarea name="content" id="content" required><?= isset($content) ? htmlspecialchars($content) : '' ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image">Featured Image (optional)</label>
                        <input type="file" name="image" id="image" accept="image/*">
                        <small style="color:#64748b; display:block; margin-top:6px; font-size: 1rem;">Max 5MB • JPG, PNG, GIF</small>
                    </div>

                    <button type="submit" class="btn">Publish Post</button>
                </form>

                <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>

</html>