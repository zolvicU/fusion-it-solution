<?php require_once 'includes/auth.php'; ?>
<?php

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

$id = $_GET['id'] ?? 0;
if ($id <= 0) {
    header('Location: dashboard.php');
    exit;
}

// Fetch the product
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header('Location: dashboard.php?msg=notfound');
        exit;
    }
} catch (Exception $e) {
    die("Error loading product: " . $e->getMessage());
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title       = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $is_featured = isset($_POST["featured"]) ? 1 : 0;

    $errors = [];

    if (empty($title))       $errors[] = "Title is required.";
    if (empty($description)) $errors[] = "Description is required.";

    $image_name = $product['image'];  // Keep current image

    // Handle new image upload (optional)
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $target_dir = "../assets/uploads/products/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

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
            // Delete old image if different
            if ($product['image'] && $product['image'] !== $image_name && file_exists($target_dir . $product['image'])) {
                unlink($target_dir . $product['image']);
            }
        } else {
            $errors[] = "Failed to upload image.";
        }
    }

    if (empty($errors)) {
        try {
            $sql = "UPDATE products SET title = ?, description = ?, image = ?, is_featured = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $description, $image_name, $is_featured, $id]);

            $message = "<div class='alert success'>Product updated successfully!</div>";

            // Refresh product data for display
            $product['title']       = $title;
            $product['description'] = $description;
            $product['image']       = $image_name;
            $product['is_featured'] = $is_featured;
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
    <title>Edit Product | Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/edit-product.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>Edit Product</h1>
                <p>Update the details of "<?= htmlspecialchars($product['title']) ?>"</p>
            </div>

            <div class="card-body">
                <?php if ($message): ?>
                    <?php echo $message; ?>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <input type="text" name="title" id="title" value="<?= htmlspecialchars($product['title']) ?>" required>
                        <label for="title">Product Title</label>
                        <span class="icon">📦</span>
                    </div>

                    <div class="form-group">
                        <textarea name="description" id="description" required><?= htmlspecialchars($product['description']) ?></textarea>
                        <label for="description">Description</label>
                        <span class="icon">📝</span>
                    </div>

                    <?php if (!empty($product['image'])): ?>
                        <div class="current-image">
                            <p>Current Image</p>
                            <img src="../assets/uploads/products/<?= htmlspecialchars($product['image']) ?>"
                                 alt="<?= htmlspecialchars($product['title']) ?>"
                                 onerror="this.style.display='none'; this.parentElement.querySelector('small').style.display='block';">
                            <small>Upload a new image below to replace it.</small>
                        </div>
                    <?php endif; ?>

                    <div class="file-group">
                        <label class="file-input-label">New Image <small>(Optional • Max 5MB • JPG/PNG/GIF)</small></label>
                        <input type="file" name="image" id="image" accept="image/*" class="file-input">
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" name="featured" id="featured" value="1" <?= $product['is_featured'] ? 'checked' : '' ?>>
                        <label for="featured">Feature this product on homepage</label>
                    </div>

                    <button type="submit" class="btn">Update Product</button>
                </form>

                <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>