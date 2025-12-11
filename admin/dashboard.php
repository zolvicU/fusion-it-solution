<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

// Fetch all products
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Failed to load products: " . $e->getMessage();
}

// Success message from URL
$success_msg = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'added':   $success_msg = "Product added successfully!"; break;
        case 'updated': $success_msg = "Product updated successfully!"; break;
        case 'deleted': $success_msg = "Product deleted successfully!"; break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Products</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --primary: #0066ff;
            --primary-dark: #0052cc;
            --success: #10b981;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-600: #4b5563;
            --gray-800: #1f2937;
            --shadow: 0 4px 12px rgba(0,0,0,0.08);
            --radius: 12px;
        }
        body { font-family: 'Inter', sans-serif; background: #f0f4ff; color: var(--gray-800); margin: 0; padding: 20px; }
        .container { max-width: 1100px; margin: 40px auto; }
        .card { background: white; border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; }
        .card-header { background: var(--primary); color: white; padding: 30px; text-align: center; }
        .card-header h1 { margin: 0; font-size: 28px; font-weight: 600; }
        .card-header p { margin: 10px 0 0; opacity: 0.9; }
        .card-body { padding: 32px; }
        .btn { display: inline-block; background: var(--primary); color: white; padding: 12px 20px; border: none; border-radius: 8px; font-weight: 600; text-decoration: none; cursor: pointer; transition: background 0.3s; }
        .btn:hover { background: var(--primary-dark); }
        .btn-success { background: var(--success); }
        .btn-success:hover { background: #059669; }
        .btn-danger { background: #ef4444; }
        .btn-danger:hover { background: #dc2626; }
        .alert-success { background: #d1fae5; color: var(--success); padding: 16px; border-radius: 8px; border: 1px solid #a7f3d0; margin-bottom: 24px; font-weight: 500; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 14px; text-align: left; border-bottom: 1px solid var(--gray-200); }
        th { background: var(--gray-100); font-weight: 600; }
        .thumb { width: 70px; height: 70px; object-fit: cover; border-radius: 8px; border: 1px solid var(--gray-200); }
        .actions a { margin-right: 8px; font-size: 14px; }
        .no-products { text-align: center; padding: 60px; color: var(--gray-600); font-size: 18px; }
        .logout { text-align: center; margin-top: 40px; }
        .logout a { color: var(--primary); text-decoration: none; font-weight: 500; }
        .logout a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>Admin Dashboard</h1>
                <p>Welcome back, <strong><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></strong></p>
            </div>

            <div class="card-body">
                <p>Manage your products below. Add, edit, or remove items for the Featured Products section.</p>

                <?php if ($success_msg): ?>
                    <div class="alert-success"><?= $success_msg ?></div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert-success" style="background:#fee2e2;color:#ef4444;border-color:#fecaca;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <p>
                    <a href="add-product.php" class="btn">+ Add New Product</a>
                </p>

                <?php if (empty($products)): ?>
                    <div class="no-products">
                        No products found. <a href="add-product.php">Add your first product</a> to get started!
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Featured</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['id']) ?></td>
                                    <td>
                                        <?php if (!empty($product['image'])): ?>
                                            <img src="../assets/uploads/products/<?= htmlspecialchars($product['image']) ?>" alt="Product" class="thumb">
                                        <?php else: ?>
                                            <em>No image</em>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($product['title'] ?? $product['name'] ?? 'Untitled') ?></td>
                                    <td><?= !empty($product['featured']) ? 'Yes' : 'No' ?></td>
                                    <td class="actions">
                                        <a href="edit-product.php?id=<?= $product['id'] ?>" class="btn btn-success">Edit</a>
                                        <a href="delete-product.php?id=<?= $product['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <div class="logout">
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>