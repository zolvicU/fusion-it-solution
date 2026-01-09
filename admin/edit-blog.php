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
    <title>Edit Blog Post | Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --success: #059669;
            --error: #dc2626;
            --warning: #d97706;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius-sm: 6px;
            --radius: 10px;
            --radius-md: 12px;
            --radius-lg: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            font-weight: 400;
            color: var(--gray-800);
            background: linear-gradient(135deg, #f6f8ff 0%, #f0f4ff 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .header {
            margin-bottom: 24px;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 4px;
        }

        .header p {
            font-size: 14px;
            color: var(--gray-600);
        }

        .card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            border: 1px solid var(--gray-200);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 24px 32px;
        }

        .card-header h2 {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-header h2::before {
            content: "✏️";
            font-size: 16px;
        }

        .card-body {
            padding: 32px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 24px;
        }

        .form-group {
            margin-bottom: 0;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .label-hint {
            font-size: 12px;
            color: var(--gray-500);
            font-weight: 400;
            margin-top: 2px;
            text-transform: none;
            letter-spacing: normal;
        }

        input[type="text"],
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 400;
            color: var(--gray-800);
            background: white;
            transition: all 0.2s ease;
        }

        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        input[type="text"]::placeholder,
        textarea::placeholder {
            color: var(--gray-400);
        }

        textarea {
            min-height: 200px;
            resize: vertical;
            line-height: 1.6;
        }

        .current-image-section {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius);
            padding: 20px;
            margin-top: 8px;
        }

        .current-image-section p {
            font-size: 13px;
            color: var(--gray-600);
            margin-bottom: 12px;
        }

        .current-image-section p strong {
            font-weight: 600;
            color: var(--gray-800);
        }

        .current-image-container {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .current-image-container img {
            max-width: 200px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--gray-300);
            box-shadow: var(--shadow-sm);
        }

        .image-info {
            flex: 1;
            min-width: 200px;
        }

        .image-info small {
            display: block;
            font-size: 12px;
            color: var(--gray-500);
            margin-top: 6px;
        }

        .file-input-wrapper {
            position: relative;
            margin-top: 8px;
        }

        .file-input-wrapper input[type="file"] {
            padding: 12px;
            border: 2px dashed var(--gray-300);
            background: var(--gray-50);
            cursor: pointer;
        }

        .file-input-wrapper input[type="file"]:hover {
            border-color: var(--primary);
            background: #f8faff;
        }

        .file-input-wrapper::before {
            content: "📁 Choose Image";
            position: absolute;
            top: 50%;
            right: 14px;
            transform: translateY(-50%);
            background: var(--primary);
            color: white;
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            font-size: 12px;
            font-weight: 500;
            pointer-events: none;
        }

        .btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: var(--radius);
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-width: 140px;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-update {
            background: linear-gradient(135deg, var(--success) 0%, #047857 100%);
            width: 100%;
            padding: 14px;
            font-weight: 600;
            margin-top: 32px;
        }

        .alert {
            padding: 14px 18px;
            border-radius: var(--radius);
            margin-bottom: 24px;
            font-size: 13px;
            font-weight: 500;
            border-left: 4px solid;
        }

        .alert.success {
            background: #f0fdf4;
            color: var(--success);
            border-color: var(--success);
        }

        .alert.error {
            background: #fef2f2;
            color: var(--error);
            border-color: var(--error);
        }

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--gray-200);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--gray-600);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: var(--radius);
            transition: all 0.2s ease;
        }

        .back-link:hover {
            color: var(--primary);
            background: var(--gray-100);
            transform: translateX(-2px);
        }

        .back-link::before {
            content: "←";
            font-size: 14px;
        }

        @media (max-width: 768px) {
            body {
                padding: 12px;
            }
            
            .container {
                max-width: 100%;
            }
            
            .card-body {
                padding: 20px;
            }
            
            .card-header {
                padding: 20px;
            }
            
            .current-image-container {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .current-image-container img {
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Edit Blog Post</h1>
            <p>Update your blog post content, SEO slug, and featured image</p>
        </div>

        <?php echo $message; ?>

        <div class="card">
            <div class="card-header">
                <h2>Edit Post Details</h2>
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data" class="form-grid">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <div class="label-hint">Main heading of your blog post</div>
                        <input type="text" id="title" name="title" 
                               value="<?php echo htmlspecialchars($post['title']); ?>" 
                               required 
                               placeholder="Enter a compelling title">
                    </div>

                    <div class="form-group">
                        <label for="slug">URL Slug</label>
                        <div class="label-hint">SEO-friendly URL identifier (lowercase, hyphens)</div>
                        <input type="text" id="slug" name="slug" 
                               value="<?php echo htmlspecialchars($post['slug']); ?>" 
                               required 
                               placeholder="e.g., reliable-internet-solutions">
                    </div>

                    <div class="form-group">
                        <label for="content">Content</label>
                        <div class="label-hint">Main body of your blog post</div>
                        <textarea id="content" name="content" 
                                  required 
                                  placeholder="Write your blog content here..."><?php echo htmlspecialchars($post['content']); ?></textarea>
                    </div>

                    <?php if (!empty($post['image'])): ?>
                        <div class="form-group">
                            <label>Current Featured Image</label>
                            <div class="current-image-section">
                                <div class="current-image-container">
                                    <img src="../assets/uploads/blog/<?php echo htmlspecialchars($post['image']); ?>" 
                                         alt="Current featured image">
                                    <div class="image-info">
                                        <p><strong>Current Image Preview</strong></p>
                                        <small>Upload a new image below to replace this one</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="image">Update Featured Image</label>
                        <div class="label-hint">Optional - Upload JPG, PNG, or GIF (max 5MB)</div>
                        <div class="file-input-wrapper">
                            <input type="file" id="image" name="image" accept="image/*">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-update">
                        <span>Update Post</span>
                    </button>
                </form>

                <div class="action-bar">
                    <a href="blog-list.php" class="back-link">Back to Blog List</a>
                    <div style="font-size: 12px; color: var(--gray-500);">
                        Last updated: <?php echo date('M j, Y'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-generate slug from title
        document.getElementById('title').addEventListener('input', function() {
            const titleInput = this;
            const slugInput = document.getElementById('slug');
            
            if (slugInput.dataset.manual !== 'true') {
                const slug = titleInput.value
                    .toLowerCase()
                    .replace(/[^\w\s]/gi, '')
                    .replace(/\s+/g, '-')
                    .replace(/--+/g, '-')
                    .trim();
                slugInput.value = slug;
            }
        });
        
        // Mark slug as manually edited
        document.getElementById('slug').addEventListener('input', function() {
            this.dataset.manual = 'true';
        });
        
        // Show file name when selected
        document.getElementById('image').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            const wrapper = document.querySelector('.file-input-wrapper');
            
            if (fileName) {
                const existingHint = wrapper.querySelector('.file-hint');
                if (existingHint) existingHint.remove();
                
                const hint = document.createElement('div');
                hint.className = 'file-hint';
                hint.style.cssText = 'font-size: 12px; color: var(--gray-600); margin-top: 8px;';
                hint.textContent = `Selected: ${fileName}`;
                wrapper.appendChild(hint);
            }
        });
    </script>
</body>

</html>