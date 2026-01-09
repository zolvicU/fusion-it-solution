<?php require_once 'includes/auth.php'; ?>
<?php

require_once '../config/database.php';

// Pagination
$products_per_page = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $products_per_page;

// Search and Filter
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all'; // all, featured, regular

// Build query
$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter === 'featured') {
    $where[] = "is_featured = 1";
} elseif ($filter === 'regular') {
    $where[] = "is_featured = 0";
}

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Count total products
try {
    $count_sql = "SELECT COUNT(*) FROM products $where_clause";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_products = $stmt->fetchColumn();
    $total_pages = ceil($total_products / $products_per_page);
    
    // Fetch products
    $sql = "SELECT * FROM products $where_clause ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    
    // Bind parameters
    $param_count = count($params);
    for ($i = 0; $i < $param_count; $i++) {
        $stmt->bindValue($i + 1, $params[$i]);
    }
    
    $stmt->bindValue(':limit', $products_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count featured products for stats
    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE is_featured = 1");
    $featured_count = $stmt->fetchColumn();
    
} catch (Exception $e) {
    $error = "Failed to load products: " . $e->getMessage();
}

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $bulk_action = $_POST['bulk_action'];
    $selected_ids = $_POST['selected_ids'] ?? [];
    
    if (!empty($selected_ids)) {
        try {
            $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
            
            if ($bulk_action === 'delete') {
                // First, get images to delete
                $stmt = $pdo->prepare("SELECT image FROM products WHERE id IN ($placeholders)");
                $stmt->execute($selected_ids);
                $images_to_delete = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                // Delete products
                $stmt = $pdo->prepare("DELETE FROM products WHERE id IN ($placeholders)");
                $stmt->execute($selected_ids);
                
                // Delete image files
                foreach ($images_to_delete as $image) {
                    if ($image && file_exists("../assets/uploads/products/$image")) {
                        unlink("../assets/uploads/products/$image");
                    }
                }
                
                $success_msg = "Deleted " . count($selected_ids) . " product(s)";
                
            } elseif ($bulk_action === 'feature') {
                $stmt = $pdo->prepare("UPDATE products SET is_featured = 1 WHERE id IN ($placeholders)");
                $stmt->execute($selected_ids);
                $success_msg = "Marked " . count($selected_ids) . " product(s) as featured";
                
            } elseif ($bulk_action === 'unfeature') {
                $stmt = $pdo->prepare("UPDATE products SET is_featured = 0 WHERE id IN ($placeholders)");
                $stmt->execute($selected_ids);
                $success_msg = "Removed " . count($selected_ids) . " product(s) from featured";
            }
            
            // Refresh page to show updated list
            header("Location: products.php?page=$page&search=" . urlencode($search) . "&filter=$filter&msg=" . urlencode($success_msg));
            exit;
            
        } catch (Exception $e) {
            $error = "Bulk action failed: " . $e->getMessage();
        }
    }
}

// Success message
$success_msg = '';
if (isset($_GET['msg'])) {
    $success_msg = htmlspecialchars($_GET['msg']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products | Admin Dashboard</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #dc2626;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            --shadow-md: 0 10px 15px -3px rgba(0,0,0,0.1);
            --radius-sm: 8px;
            --radius: 12px;
            --radius-lg: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: var(--gray-800);
            line-height: 1.5;
            min-height: 100vh;
            padding: 24px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--gray-200);
        }

        .header-left h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 4px;
        }

        .header-left p {
            color: var(--gray-600);
            font-size: 14px;
        }

        .header-right {
            display: flex;
            gap: 16px;
            align-items: center;
        }

        /* Stats */
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .stat-item {
            background: white;
            padding: 16px 24px;
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
            min-width: 180px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--gray-600);
        }

        /* Action Bar */
        .action-bar {
            background: white;
            border-radius: var(--radius);
            padding: 20px 24px;
            margin-bottom: 24px;
            border: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid transparent;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-outline {
            background: white;
            color: var(--gray-700);
            border-color: var(--gray-300);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-danger {
            background: var(--error);
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        /* Search & Filter */
        .search-filter {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            min-width: 250px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 16px 10px 40px;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
        }

        .search-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-500);
        }

        .filter-select select {
            padding: 10px 16px;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            background: white;
            min-width: 150px;
        }

        /* Bulk Actions */
        .bulk-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .bulk-select {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .product-card {
            background: white;
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary);
        }

        .product-image {
            height: 200px;
            overflow: hidden;
            position: relative;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .no-image {
            height: 100%;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-500);
            font-size: 14px;
        }

        .product-badge {
            position: absolute;
            top: 16px;
            right: 16px;
            background: var(--success);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .product-info {
            padding: 24px;
        }

        .product-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-description {
            color: var(--gray-600);
            font-size: 14px;
            margin-bottom: 16px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.6;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid var(--gray-200);
        }

        .product-id {
            font-size: 12px;
            color: var(--gray-500);
            font-family: monospace;
        }

        .product-actions {
            display: flex;
            gap: 8px;
        }

        /* Checkbox */
        .checkbox-container {
            position: absolute;
            top: 16px;
            left: 16px;
            z-index: 2;
        }

        .checkbox {
            display: none;
        }

        .checkbox-label {
            display: inline-block;
            width: 24px;
            height: 24px;
            background: white;
            border: 2px solid var(--gray-400);
            border-radius: 4px;
            cursor: pointer;
            position: relative;
            transition: all 0.2s;
        }

        .checkbox:checked + .checkbox-label {
            background: var(--primary);
            border-color: var(--primary);
        }

        .checkbox:checked + .checkbox-label::after {
            content: '✓';
            position: absolute;
            color: white;
            font-size: 14px;
            font-weight: bold;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        /* Alerts */
        .alert {
            padding: 16px 20px;
            border-radius: var(--radius-sm);
            margin-bottom: 24px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert.success {
            background: #f0fdf4;
            color: var(--success);
            border: 1px solid #a7f3d0;
        }

        .alert.error {
            background: #fee2e2;
            color: var(--error);
            border: 1px solid #fecaca;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 32px;
            flex-wrap: wrap;
        }

        .page-btn {
            padding: 8px 16px;
            border: 1px solid var(--gray-300);
            background: white;
            color: var(--gray-700);
            text-decoration: none;
            border-radius: var(--radius-sm);
            font-size: 14px;
            transition: all 0.2s;
        }

        .page-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--gray-50);
        }

        .page-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .page-info {
            font-size: 14px;
            color: var(--gray-600);
            margin: 0 12px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
        }

        .empty-state i {
            font-size: 64px;
            color: var(--gray-300);
            margin-bottom: 24px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 12px;
        }

        .empty-state p {
            font-size: 14px;
            color: var(--gray-600);
            margin-bottom: 24px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 16px;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
            
            .header-right {
                width: 100%;
                justify-content: space-between;
            }
            
            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-filter {
                width: 100%;
            }
            
            .search-box {
                min-width: 100%;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .bulk-actions {
                width: 100%;
                justify-content: space-between;
            }
        }

        @media (max-width: 480px) {
            .stats {
                flex-direction: column;
            }
            
            .stat-item {
                min-width: 100%;
            }
            
            .product-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1>All Products</h1>
                <p>Manage your product catalog</p>
            </div>
            <div class="header-right">
                <a href="dashboard.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
                <a href="add-product.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Add New Product
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats">
            <div class="stat-item">
                <div class="stat-value"><?= $total_products ?? 0 ?></div>
                <div class="stat-label">Total Products</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= $featured_count ?? 0 ?></div>
                <div class="stat-label">Featured Products</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= $products_per_page ?></div>
                <div class="stat-label">Per Page</div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if ($success_msg): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?= $success_msg ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Action Bar -->
        <div class="action-bar">
            <div class="search-filter">
                <form method="GET" action="" class="search-box" id="searchForm">
                    <i class="fas fa-search"></i>
                    <input type="text" 
                           name="search" 
                           placeholder="Search products..." 
                           value="<?= htmlspecialchars($search) ?>"
                           onkeydown="if(event.key === 'Enter') this.form.submit()">
                </form>
                
                <div class="filter-select">
                    <select name="filter" onchange="document.getElementById('searchForm').submit()">
                        <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Products</option>
                        <option value="featured" <?= $filter === 'featured' ? 'selected' : '' ?>>Featured Only</option>
                        <option value="regular" <?= $filter === 'regular' ? 'selected' : '' ?>>Regular Only</option>
                    </select>
                </div>
                
                <button type="submit" form="searchForm" class="btn btn-outline">
                    <i class="fas fa-filter"></i>
                    Apply
                </button>
                
                <?php if ($search || $filter !== 'all'): ?>
                    <a href="products.php" class="btn btn-outline">
                        <i class="fas fa-times"></i>
                        Clear Filters
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bulk Actions Form -->
        <form method="POST" action="" id="bulkForm">
            <div class="action-bar">
                <div class="bulk-select">
                    <input type="checkbox" id="selectAll" class="checkbox">
                    <label for="selectAll" class="checkbox-label"></label>
                    <span style="margin-left: 8px; font-size: 14px; color: var(--gray-700);">Select All</span>
                </div>
                
                <div class="bulk-actions">
                    <span style="font-size: 14px; color: var(--gray-600);" id="selectedCount">
                        0 selected
                    </span>
                    
                    <select name="bulk_action" style="padding: 10px; border: 1px solid var(--gray-300); border-radius: var(--radius-sm); background: white; font-size: 14px;">
                        <option value="">Bulk Actions</option>
                        <option value="feature">Mark as Featured</option>
                        <option value="unfeature">Remove from Featured</option>
                        <option value="delete">Delete Selected</option>
                    </select>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-play"></i>
                        Apply
                    </button>
                </div>
            </div>

            <!-- Products Grid -->
            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>No Products Found</h3>
                    <p><?= $search || $filter !== 'all' ? 'Try adjusting your search or filters' : 'Start by adding your first product' ?></p>
                    <a href="add-product.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add New Product
                    </a>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="checkbox-container">
                                <input type="checkbox" 
                                       name="selected_ids[]" 
                                       value="<?= $product['id'] ?>" 
                                       id="product_<?= $product['id'] ?>" 
                                       class="checkbox product-checkbox">
                                <label for="product_<?= $product['id'] ?>" class="checkbox-label"></label>
                            </div>
                            
                            <div class="product-image">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="../assets/uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                                        alt="<?= htmlspecialchars($product['title']) ?>"
                                        onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0MDAgMzAwIj48cmVjdCB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgZmlsbD0iI2YzZjRmNiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIiBmaWxsPSIjNmI3MjgwIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4='">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-image"></i>
                                        <span style="margin-left: 8px;">No Image</span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($product['is_featured']): ?>
                                    <div class="product-badge">Featured</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-info">
                                <h3 class="product-title"><?= htmlspecialchars($product['title']) ?></h3>
                                
                                <?php if (!empty($product['description'])): ?>
                                    <p class="product-description">
                                        <?= htmlspecialchars(substr($product['description'], 0, 150)) ?>
                                        <?= strlen($product['description']) > 150 ? '...' : '' ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="product-meta">
                                    <div class="product-id">ID: #<?= $product['id'] ?></div>
                                    <div class="product-actions">
                                        <a href="edit-product.php?id=<?= $product['id'] ?>" class="btn btn-outline btn-sm">
                                            <i class="fas fa-edit"></i>
                                            Edit
                                        </a>
                                        <a href="delete-product.php?id=<?= $product['id'] ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Delete product: <?= addslashes($product['title']) ?>?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </form>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&filter=<?= $filter ?>" 
                       class="page-btn">
                        <i class="fas fa-chevron-left"></i> Prev
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&filter=<?= $filter ?>" 
                           class="page-btn <?= $i == $page ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                        <span class="page-btn" style="border: none; background: transparent;">...</span>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&filter=<?= $filter ?>" 
                       class="page-btn">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
                
                <div class="page-info">
                    Page <?= $page ?> of <?= $total_pages ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Bottom Action Bar -->
        <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--gray-200);">
            <a href="dashboard.php" class="btn btn-outline">
                <i class="fas fa-tachometer-alt"></i>
                Return to Dashboard
            </a>
        </div>
    </div>

    <script>
        // Bulk selection
        const selectAll = document.getElementById('selectAll');
        const productCheckboxes = document.querySelectorAll('.product-checkbox');
        const selectedCount = document.getElementById('selectedCount');
        const bulkForm = document.getElementById('bulkForm');

        // Select all functionality
        selectAll.addEventListener('change', function() {
            productCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });

        // Update selected count
        function updateSelectedCount() {
            const selected = document.querySelectorAll('.product-checkbox:checked').length;
            selectedCount.textContent = `${selected} selected`;
            selectAll.checked = selected > 0 && selected === productCheckboxes.length;
            selectAll.indeterminate = selected > 0 && selected < productCheckboxes.length;
        }

        // Update count when individual checkboxes change
        productCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedCount);
        });

        // Initialize count
        updateSelectedCount();

        // Bulk form confirmation
        bulkForm.addEventListener('submit', function(e) {
            const selected = document.querySelectorAll('.product-checkbox:checked').length;
            const action = this.querySelector('[name="bulk_action"]').value;
            
            if (selected === 0) {
                e.preventDefault();
                alert('Please select at least one product.');
                return;
            }
            
            if (!action) {
                e.preventDefault();
                alert('Please select a bulk action.');
                return;
            }
            
            const message = action === 'delete' 
                ? `Are you sure you want to delete ${selected} product(s)? This action cannot be undone.`
                : `Are you sure you want to ${action} ${selected} product(s)?`;
                
            if (!confirm(message)) {
                e.preventDefault();
            }
        });

        // Quick search with debounce
        let searchTimeout;
        const searchInput = document.querySelector('input[name="search"]');
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length === 0 || this.value.length >= 3) {
                    this.form.submit();
                }
            }, 500);
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + F to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
            
            // Escape to clear search
            if (e.key === 'Escape' && document.activeElement === searchInput) {
                searchInput.value = '';
                searchInput.form.submit();
            }
            
            // Ctrl/Cmd + A to select all
            if ((e.ctrlKey || e.metaKey) && e.key === 'a') {
                e.preventDefault();
                selectAll.checked = !selectAll.checked;
                selectAll.dispatchEvent(new Event('change'));
            }
        });

        // Add hover effect to product cards
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.zIndex = '1';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.zIndex = '';
            });
        });

        // Image lazy loading
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.product-image img');
            
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.getAttribute('src');
                        observer.unobserve(img);
                    }
                });
            }, { rootMargin: '100px 0px' });
            
            images.forEach(img => imageObserver.observe(img));
        });
    </script>
</body>
</html>