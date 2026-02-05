<?php require_once 'includes/auth.php'; ?>
<?php

require_once '../config/database.php';

if (!isset($pdo)) {
    die("Database connection failed.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title       = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $is_featured = isset($_POST["featured"]) ? 1 : 0;

    $errors = [];

    if (empty($title))       $errors[] = "Title is required.";
    if (empty($description)) $errors[] = "Description is required.";

    // === Image Upload ===
    $image_name = "";
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $target_dir = "../assets/uploads/products/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $image_name    = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file   = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            $errors[] = "File is not a valid image.";
        } elseif ($_FILES["image"]["size"] > 5000000) {
            $errors[] = "Image too large (max 5MB).";
        } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $errors[] = "Only JPG, JPEG, PNG & GIF allowed.";
        } elseif (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Success
        } else {
            $errors[] = "Failed to upload image.";
        }
    } else {
        $errors[] = "Image is required.";
    }

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO products (title, description, image, is_featured) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $description, $image_name, $is_featured]);

            $message = "<div class='alert success'>Product added successfully!</div>";
            $title = $description = "";
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
    <title>Add Product | Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/add-product.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>Add New Product</h1>
                <p>Fill in the details to feature a product on your homepage</p>
            </div>

            <div class="card-body">
                <?php if ($message): ?>
                    <?php echo $message; ?>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <input type="text" name="title" id="title" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" required>
                        <label for="title">Product Title</label>
                        <span class="icon">📦</span>
                    </div>

                    <div class="form-group">
                        <textarea name="description" id="description" required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                        <label for="description">Description</label>
                        <span class="icon">📝</span>
                    </div>

                    <div class="file-group">
                        <label class="file-input-label">Product Image <small>(Required • Max 5MB • JPG/PNG/GIF)</small></label>
                        <input type="file" name="image" id="image" accept="image/*" class="file-input" required>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" name="featured" id="featured" value="1">
                        <label for="featured">Feature this product on homepage</label>
                    </div>

                    <button type="submit" class="btn">Add Product</button>
                </form>

                <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>