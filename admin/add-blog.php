<?php
session_start();

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
    <link rel="stylesheet" href="../assets/css/style.css">
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