<?php require_once 'includes/auth.php'; ?>
<?php

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';

$id = $_GET['id'] ?? 0;
if ($id <= 0) {
    header('Location: blog-list.php');
    exit;
}

// Fetch the post
try {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        header('Location: blog-list.php');
        exit;
    }
} catch (Exception $e) {
    die("Error loading post.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title       = trim($_POST["title"]);
    $content     = trim($_POST["content"]);
    $slug        = trim($_POST["slug"]); // Allow editing slug

    $errors = [];

    if (empty($title))   $errors[] = "Title is required.";
    if (empty($content)) $errors[] = "Content is required.";
    if (empty($slug))    $errors[] = "Slug is required.";

    $image_name = $post['image']; // Keep current image by default

    // New image upload (optional)
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
        } elseif (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Delete old image if exists and different
            if ($post['image'] && $post['image'] !== $image_name && file_exists($target_dir . $post['image'])) {
                unlink($target_dir . $post['image']);
            }
        } else {
            $errors[] = "Failed to upload image.";
        }
    }

    if (empty($errors)) {
        try {
            $sql = "UPDATE blog_posts SET title = ?, slug = ?, content = ?, image = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $slug, $content, $image_name, $id]);

            $message = "<div class='alert success'>Post updated successfully!</div>";
            // Refresh post data
            $post['title'] = $title;
            $post['slug'] = $slug;
            $post['content'] = $content;
            $post['image'] = $image_name;
        } catch (Exception $e) {
            $message = "<div class='alert error'>Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
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
    <title>Edit Blog Post | Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --primary: #0066ff;
            --success: #10b981;
            --error: #ef4444;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-800: #1f2937;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --radius: 12px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f9fafb;
            color: var(--gray-800);
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
        }

        .card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .card-header {
            background: var(--primary);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .card-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }

        .card-body {
            padding: 32px;
        }

        label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
        }

        input[type="text"],
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            font-size: 16px;
        }

        textarea {
            min-height: 250px;
            resize: vertical;
        }

        .current-image {
            margin: 20px 0;
            text-align: center;
        }

        .current-image img {
            max-width: 400px;
            border-radius: 8px;
            border: 1px solid var(--gray-200);
        }

        .btn {
            background: var(--primary);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
        }

        .btn:hover {
            background: #0052cc;
        }

        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-weight: 500;
        }

        .alert.success {
            background: #d1fae5;
            color: var(--success);
            border: 1px solid #a7f3d0;
        }

        .alert.error {
            background: #fee2e2;
            color: var(--error);
            border: 1px solid #fecaca;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 32px;
            color: var(--gray-600);
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            color: var(--primary);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>Edit Blog Post</h1>
            </div>
            <div class="card-body">
                <?php echo $message; ?>

                <form method="post" enctype="multipart/form-data">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>

                    <label for="slug">Slug (URL-friendly)</label>
                    <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($post['slug']); ?>" required>

                    <label for="content">Content</label>
                    <textarea id="content" name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>

                    <?php if (!empty($post['image'])): ?>
                        <div class="current-image">
                            <p><strong>Current Image:</strong></p>
                            <img src="../assets/uploads/blog/<?php echo htmlspecialchars($post['image']); ?>" alt="Current image">
                            <p><small>Upload a new image to replace it.</small></p>
                        </div>
                    <?php endif; ?>

                    <label for="image">New Image (optional)</label>
                    <input type="file" id="image" name="image" accept="image/*">

                    <button type="submit" class="btn">Update Post</button>
                </form>

                <a href="blog-list.php" class="back-link">← Back to Blog List</a>
            </div>
        </div>
    </div>
</body>

</html>