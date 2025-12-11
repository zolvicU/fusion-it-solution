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
    $title       = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $is_featured = isset($_POST["featured"]) ? 1 : 0;   // correct variable name

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

    // === INSERT with correct column name: is_featured ===
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO products (title, description, image, is_featured) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $description, $image_name, $is_featured]);

            $message = "<div class='alert success'>Product added successfully!</div>";
            $title = $description = ""; // clear form
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root { --primary: #0066ff; --success: #10b981; --error: #ef4444; --gray-100: #f3f4f6; --gray-200: #e5e7eb; --gray-800: #1f2937; --shadow: 0 4px 12px rgba(0,0,0,0.08); --radius: 12px; }
        body { font-family: 'Inter', sans-serif; background: #f9fafb; color: var(--gray-800); margin: 0; padding: 20px; }
        .container { max-width: 700px; margin: 40px auto; padding: 0 20px; }
        .card { background: white; border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; }
        .card-header { background: var(--primary); color: white; padding: 24px; text-align: center; }
        .card-header h1 { font-size: 24px; font-weight: 600; }
        .card-body { padding: 32px; }
        .form-group { margin-bottom: 24px; }
        label { display: block; font-weight: 500; margin-bottom: 8px; }
        input[type="text"], textarea, input[type="file"] { width: 100%; padding: 12px 16px; border: 1px solid var(--gray-200); border-radius: 8px; font-size: 16px; }
        textarea { min-height: 120px; resize: vertical; }
        .checkbox-group { display: flex; align-items: center; gap: 10px; margin-top: 20px; }
        .btn { background: var(--primary); color: white; padding: 14px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; width: 100%; }
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
                <h1>Add New Product</h1>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <?php echo $message; ?>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="image">Product Image</label>
                        <input type="file" id="image" name="image" accept="image/*" required>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="featured" name="featured" value="1">
                        <label for="featured">Feature this product on homepage</label>
                    </div>

                    <button type="submit" class="btn">Add Product</button>
                </form>

                <a href="dashboard.php" class="back-link">Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>