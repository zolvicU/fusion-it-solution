<?php
session_start();

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
    $featured    = isset($_POST["featured"]) ? 1 : 0;

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
            if ($product['image'] && file_exists($target_dir . $product['image'])) {
                unlink($target_dir . $product['image']);
            }
        } else {
            $errors[] = "Failed to upload image.";
        }
    }

    if (empty($errors)) {
        try {
            $sql = "UPDATE products SET title = ?, description = ?, image = ?, featured = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $description, $image_name, $featured, $id]);

            $message = "<div class='alert success'>Product updated successfully!</div>";
            $product['title'] = $title;
            $product['description'] = $description;
            $product['image'] = $image_name;
            $product['featured'] = $featured;
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root { --primary: #0066ff; --success: #10b981; --error: #ef4444; --gray-100: #f3f4f6; --gray-200: #e5e7eb; --gray-800: #1f2937; --shadow: 0 4px 12px rgba(0,0,0,0.08); --radius: 12px; }
        body { font-family: 'Inter', sans-serif; background: #f0f4ff; color: var(--gray-800); margin: 0; padding: 20px; }
        .container { max-width: 700px; margin: 40px auto; }
        .card { background: white; border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; }
        .card-header { background: var(--primary); color: white; padding: 30px; text-align: center; }
        .card-header h1 { margin: 0; font-size: 28px; font-weight: 600; }
        .card-body { padding: 32px; }
        label { display: block; font-weight: 500; margin-bottom: 8px; }
        input[type="text"], textarea, input[type="file"] { width: 100%; padding: 12px 16px; border: 1px solid var(--gray-200); border-radius: 8px; font-size: 16px; }
        input:focus, textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(0,102,255,0.1); }
        textarea { min-height: 120px; resize: vertical; }
        .current-image { margin: 20px 0; text-align: center; }
        .current-image img { max-width: 300px; border-radius: 8px; border: 1px solid var(--gray-200); }
        .checkbox-group { display: flex; align-items: center; gap: 10px; margin: 20px 0; }
        .btn { background: var(--primary); color: white; padding: 14px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; width: 100%; }
        .btn:hover { background: #0052cc; }
        .alert { padding: 16px; border-radius: 8px; margin-bottom: 24px; font-weight: 500; }
        .alert.success { background: #d1fae5; color: var(--success); border: 1px solid #a7f3d0; }
        .alert.error { background: #fee2e2; color: var(--error); border: 1px solid #fecaca; }
        .back-link { display: block; text-align: center; margin-top: 32px; color: var(--gray-600); text-decoration: none; font-weight: 500; }
        .back-link:hover { color: var(--primary); }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>Edit Product</h1>
            </div>
            <div class="card-body">
                <?php echo $message; ?>

                <form method="post" enctype="multipart/form-data">
                    <label>Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($product['title'] ?? $product['name']); ?>" required>

                    <label>Description</label>
                    <textarea name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>

                    <?php if (!empty($product['image'])): ?>
                        <div class="current-image">
                            <p><strong>Current Image:</strong></p>
                            <img src="../assets/uploads/products/<?php echo htmlspecialchars($product['image']); ?>" alt="Current">
                            <p><small>Upload a new image to replace it.</small></p>
                        </div>
                    <?php endif; ?>

                    <label>New Image (optional)</label>
                    <input type="file" name="image" accept="image/*">

                    <div class="checkbox-group">
                        <input type="checkbox" name="featured" value="1" <?php echo !empty($product['featured']) ? 'checked' : ''; ?>>
                        <label>Feature this product on homepage</label>
                    </div>

                    <button type="submit" class="btn">Update Product</button>
                </form>

                <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>