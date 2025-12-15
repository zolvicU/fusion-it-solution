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
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --success: #10b981;
            --error: #dc2626;
            --gray-100: #f8fafc;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-600: #475569;
            --gray-800: #1e293b;
            --shadow: 0 4px 12px rgba(0,0,0,0.08);
            --shadow-lg: 0 20px 40px rgba(0,0,0,0.08);
            --radius: 16px;
            --radius-sm: 12px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e0f2fe 0%, #f8fbff 100%);
            color: var(--gray-800);
            min-height: 100vh;
            padding: 24px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        .card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(16px);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 40px 32px;
            text-align: center;
        }

        .card-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .card-header p {
            font-size: 15px;
            opacity: 0.95;
        }

        .card-body {
            padding: 40px 32px;
        }

        .form-group {
            position: relative;
            margin-bottom: 28px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 16px 16px 16px 48px;
            font-size: 16px;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-sm);
            background: white;
            transition: all 0.3s ease;
        }

        .form-group textarea {
            min-height: 140px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.15);
        }

        .form-group label {
            position: absolute;
            left: 48px;
            top: 16px;
            font-size: 16px;
            color: var(--gray-600);
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .form-group input:focus ~ label,
        .form-group input:valid ~ label,
        .form-group textarea:focus ~ label,
        .form-group textarea:valid ~ label {
            top: -10px;
            left: 16px;
            font-size: 13px;
            background: white;
            padding: 0 8px;
            color: var(--primary);
            font-weight: 600;
        }

        .icon {
            position: absolute;
            left: 16px;
            top: 16px;
            color: var(--gray-600);
            font-size: 20px;
        }

        .current-image {
            margin: 32px 0;
            text-align: center;
        }

        .current-image p {
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--gray-800);
        }

        .current-image img {
            max-width: 100%;
            height: auto;
            max-height: 300px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--gray-300);
            box-shadow: var(--shadow);
        }

        .current-image small {
            display: block;
            margin-top: 12px;
            color: var(--gray-600);
            font-size: 14px;
        }

        .file-group {
            margin-bottom: 32px;
        }

        .file-input-label {
            display: block;
            font-weight: 500;
            margin-bottom: 12px;
            color: var(--gray-800);
        }

        .file-input {
            display: block;
            width: 100%;
            padding: 32px;
            background: white;
            border: 2px dashed var(--gray-300);
            border-radius: var(--radius-sm);
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

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 32px 0;
        }

        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: var(--primary);
        }

        .checkbox-group label {
            font-size: 15px;
            font-weight: 500;
            color: var(--gray-800);
        }

        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.3);
        }

        .alert {
            padding: 16px 20px;
            border-radius: var(--radius-sm);
            margin-bottom: 28px;
            font-size: 15px;
            font-weight: 500;
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

        .back-link {
            display: block;
            text-align: center;
            margin-top: 40px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
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