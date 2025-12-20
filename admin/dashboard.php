<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

/* Fetch products */
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Failed to load products: " . $e->getMessage();
}

/* Fetch message COUNT only */
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0");
    $unread_count = $stmt->fetchColumn();
} catch (Exception $e) {
    $unread_count = 0;
}

/* Success messages */
$success_msg = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'added') $success_msg = "Product added successfully!";
    if ($_GET['msg'] === 'updated') $success_msg = "Product updated successfully!";
    if ($_GET['msg'] === 'deleted') $success_msg = "Product deleted successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Products</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --success: #10b981;
            --danger: #ef4444;
            --gray-100: #f8fafc;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-600: #475569;
            --gray-800: #1e293b;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 20px 40px rgba(0, 0, 0, 0.08);
            --radius: 16px;
            --radius-sm: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e0f2fe 0%, #f8fbff 100%);
            color: var(--gray-800);
            min-height: 100vh;
            padding: 24px;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            position: relative;
        }

        .header {
            background: var(--primary);
            color: white;
            padding: 40px 32px;
            text-align: center;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            margin-bottom: 32px;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .header p {
            font-size: 16px;
            opacity: 0.95;
        }

        .section-title {
            font-size: 16px;
            color: var(--gray-600);
            margin-bottom: 24px;
            font-weight: 500;
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 32px;
        }

        .add-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 15px;
            text-decoration: none;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.3);
        }

        .alert-success {
            background: #d1fae5;
            color: var(--success);
            padding: 14px 20px;
            border-radius: var(--radius-sm);
            border: 1px solid #a7f3d0;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 24px;
        }

        .products-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .product-card {
            background: white;
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .product-id {
            font-weight: 600;
            color: var(--gray-600);
            font-size: 14px;
            min-width: 40px;
        }

        .product-thumb {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: var(--radius-sm);
            border: 1px solid var(--gray-200);
            flex-shrink: 0;
        }

        .product-thumb.placeholder {
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-600);
            font-size: 12px;
            text-align: center;
        }

        .product-info {
            flex: 1;
            min-width: 0;
        }

        .product-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-featured {
            font-size: 13px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 500;
        }

        .featured-yes {
            background: #d1fae5;
            color: var(--success);
        }

        .featured-no {
            background: var(--gray-200);
            color: var(--gray-600);
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-edit {
            background: var(--success);
            color: white;
        }

        .btn-edit:hover {
            background: #059669;
        }

        .btn-delete {
            background: var(--danger);
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
        }

        .no-products {
            text-align: center;
            padding: 80px 20px;
            color: var(--gray-600);
            font-size: 16px;
        }

        .no-products a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .no-products a:hover {
            text-decoration: underline;
        }

        /* Logout Button */
        .logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 15px;
            text-decoration: none;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: var(--danger);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3);
        }

        .logout {
            text-align: center;
            margin-top: 48px;
        }

        /* Messages Icon & Panel */
        .top-bar {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .messages-icon img {
            width: 50px;
            height: 55px;
            object-fit: contain;
            cursor: pointer;
            transition: transform 0.2s ease;
            margin-top: 12rem;
        }

        .messages-icon img:hover {
            transform: scale(1.08);
        }


        .badge {
            position: absolute;
            top: 186px;
            right: -4px;
            background: var(--danger);
            color: white;
            font-size: 12px;
            font-weight: bold;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .messages-panel {
            position: fixed;
            top: 0;
            right: -400px;
            width: 400px;
            height: 100%;
            background: white;
            box-shadow: -10px 0 30px rgba(0, 0, 0, 0.2);
            transition: right 0.4s ease;
            z-index: 1000;
            overflow-y: auto;
            padding: 20px;
        }

        .messages-panel.open {
            right: 0;
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .panel-header h3 {
            font-size: 20px;
            margin: 0;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--gray-600);
        }

        .message-item {
            background: var(--gray-100);
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 16px;
        }

        .message-item strong {
            color: var(--primary);
        }

        .message-item {
            background: var(--gray-100);
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 16px;

            /* FIX FOR LONG TEXT */
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }

        .message-item p,
        .message-item div {
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }
    </style>
</head>

<body>

    <div class="container">

        <div class="header">
            <h1>Admin Dashboard</h1>
            <p>Welcome back, <strong><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></strong></p>
            <!-- Message Icon -->
            <div class="top-bar">
                <a href="messages.php" class="messages-icon" title="View Messages">
                    <img src="../assets/icons/messages.png" alt="Messages">
                    <?php if ($unread_count > 0): ?>
                        <span class="badge"><?= $unread_count ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <div class="section-title">
            Manage your products below. Add, edit, or remove items for the Featured Products section.
        </div>

        <?php if ($success_msg): ?>
            <div class="alert-success"><?= htmlspecialchars($success_msg) ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert-success" style="background:#fee2e2;color:#ef4444;border-color:#fecaca;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="action-buttons">
            <a href="add-product.php" class="add-btn">+ Add New Product</a>
            <a href="blog-list.php" class="add-btn" style="background:linear-gradient(135deg,#10b981,#059669);">
                Manage Blog Posts
            </a>
        </div>

        <?php if (empty($products)): ?>
            <div class="no-products">
                No products found yet.<br><br>
                <a href="add-product.php">Add your first product</a>
            </div>
        <?php else: ?>
            <div class="products-list">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-id">#<?= $product['id'] ?></div>

                        <?php if (!empty($product['image'])): ?>
                            <img src="../assets/uploads/products/<?= htmlspecialchars($product['image']) ?>"
                                class="product-thumb">
                        <?php else: ?>
                            <div class="product-thumb placeholder">No Image</div>
                        <?php endif; ?>

                        <div class="product-info">
                            <div class="product-title"><?= htmlspecialchars($product['title']) ?></div>
                            <span class="product-featured <?= $product['is_featured'] ? 'featured-yes' : 'featured-no' ?>">
                                <?= $product['is_featured'] ? 'Yes' : 'No' ?>
                            </span>
                        </div>

                        <div class="actions">
                            <a href="edit-product.php?id=<?= $product['id'] ?>" class="btn btn-edit">Edit</a>
                            <a href="delete-product.php?id=<?= $product['id'] ?>" class="btn btn-delete"
                                onclick="return confirm('Delete this product?')">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="logout">
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

    </div>

</body>

</html>