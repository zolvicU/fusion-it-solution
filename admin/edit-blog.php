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
    <link rel="stylesheet" href="../assets/css/edit-blog.css">

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