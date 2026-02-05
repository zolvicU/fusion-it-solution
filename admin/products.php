<?php require_once 'includes/auth.php'; ?>
<?php

require_once '../config/database.php';

// Pagination - Increased items per page for compact layout
$products_per_page = 30; // Increased from 20
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
    $sql = "SELECT * FROM products $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($sql);

    // Add LIMIT and OFFSET parameters to the params array
    $all_params = $params;
    $all_params[] = $products_per_page;
    $all_params[] = $offset;

    // Bind all parameters
    $stmt->execute($all_params);
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
    <title>Products | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/products.css">
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1>Products</h1>
                <p>Manage your product catalog efficiently</p>
            </div>
            <div class="header-right">
                <a href="dashboard.php" class="btn btn-outline btn-sm">
                    <i class="fas fa-arrow-left"></i>
                    Back
                </a>
                <a href="add-product.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i>
                    Add Product
                </a>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="stats-compact">
            <div class="stat-compact">
                <div class="stat-icon"><i class="fas fa-box"></i></div>
                <div>
                    <div class="stat-number"><?= $total_products ?? 0 ?></div>
                    <div class="stat-text">Total Products</div>
                </div>
            </div>
            <div class="stat-compact">
                <div class="stat-icon"><i class="fas fa-star"></i></div>
                <div>
                    <div class="stat-number"><?= $featured_count ?? 0 ?></div>
                    <div class="stat-text">Featured</div>
                </div>
            </div>
            <div class="stat-compact">
                <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
                <div>
                    <div class="stat-number"><?= $products_per_page ?></div>
                    <div class="stat-text">Per Page</div>
                </div>
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

        <!-- Search and Filter Bar -->
        <div class="search-filter-bar">
            <form method="GET" action="" id="searchForm">
                <input type="hidden" name="page" value="1">
                
                <div class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" 
                           name="search" 
                           placeholder="Search products..." 
                           value="<?= htmlspecialchars($search) ?>"
                           onkeydown="if(event.key === 'Enter') this.form.submit()">
                </div>
                
                <select name="filter" onchange="this.form.submit()" class="filter-select">
                    <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Products</option>
                    <option value="featured" <?= $filter === 'featured' ? 'selected' : '' ?>>Featured Only</option>
                    <option value="regular" <?= $filter === 'regular' ? 'selected' : '' ?>>Regular Only</option>
                </select>
                
                <button type="submit" class="btn btn-outline btn-sm">
                    <i class="fas fa-filter"></i>
                    Filter
                </button>
                
                <?php if ($search || $filter !== 'all'): ?>
                    <a href="products.php" class="btn btn-outline btn-sm">
                        <i class="fas fa-times"></i>
                        Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Bulk Actions Form - Moved to wrap everything -->
        <form method="POST" action="" id="bulkForm">
        <!-- Bulk Actions Bar -->
        <div class="bulk-actions-bar">
            <div class="bulk-select">
                <input type="checkbox" id="selectAll" class="checkbox">
                <label for="selectAll" class="checkbox-label"></label>
                <span class="select-text">Select All</span>
            </div>
            
            <div class="bulk-controls">
                <span class="selected-count" id="selectedCount">0 selected</span>
                <select name="bulk_action" class="bulk-action-select">
                    <option value="">Bulk Actions</option>
                    <option value="feature">Mark as Featured</option>
                    <option value="unfeature">Remove from Featured</option>
                    <option value="delete">Delete Selected</option>
                </select>
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fas fa-play"></i>
                    Apply
                </button>
            </div>
        </div>

        <!-- Bulk Actions Form -->
        <form method="POST" action="" id="bulkForm">
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
                <div class="products-grid-compact">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card-compact">
                            <input type="checkbox"
                                   name="selected_ids[]"
                                   value="<?= $product['id'] ?>"
                                   id="product_<?= $product['id'] ?>"
                                   class="checkbox product-checkbox">
                            <label for="product_<?= $product['id'] ?>" class="checkbox-label"></label>
                            
                            <div class="product-image-compact">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="../assets/uploads/products/<?= htmlspecialchars($product['image']) ?>"
                                         alt="<?= htmlspecialchars($product['title']) ?>"
                                         onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMjAgMTIwIj48cmVjdCB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgZmlsbD0iI2YzZjRmNiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTIiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIiBmaWxsPSIjNmI3MjgwIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4='">
                                <?php else: ?>
                                    <div class="no-image-compact">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($product['is_featured']): ?>
                                    <div class="featured-badge"><i class="fas fa-star"></i></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-info-compact">
                                <h4 class="product-title-compact"><?= htmlspecialchars($product['title']) ?></h4>
                                
                                <div class="product-meta-compact">
                                    <span class="product-id">#<?= $product['id'] ?></span>
                                    <div class="product-actions-compact">
                                        <a href="edit-product.php?id=<?= $product['id'] ?>" 
                                           class="action-btn edit" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete-product.php?id=<?= $product['id'] ?>"
                                           class="action-btn delete"
                                           title="Delete"
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
            <div class="pagination-compact">
                <div class="pagination-info">
                    Showing <?= min($products_per_page, count($products)) ?> of <?= $total_products ?> products
                </div>
                <div class="pagination-controls">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&filter=<?= $filter ?>"
                           class="page-btn prev">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <span class="page-current"><?= $page ?> / <?= $total_pages ?></span>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&filter=<?= $filter ?>"
                           class="page-btn next">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Bulk selection
        const selectAll = document.getElementById('selectAll');
        const productCheckboxes = document.querySelectorAll('.product-checkbox');
        const selectedCount = document.getElementById('selectedCount');
        const bulkForm = document.getElementById('bulkForm');
        const searchForm = document.getElementById('searchForm');
        const searchInput = document.querySelector('input[name="search"]');

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
            const actionSelect = this.querySelector('[name="bulk_action"]');
            const action = actionSelect ? actionSelect.value : '';

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

            const message = action === 'delete' ?
                `Are you sure you want to delete ${selected} product(s)? This action cannot be undone.` :
                `Are you sure you want to ${action} ${selected} product(s)?`;

            if (!confirm(message)) {
                e.preventDefault();
            }
        });

        // Quick search with debounce
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length === 0 || this.value.length >= 3) {
                    searchForm.querySelector('[name="page"]').value = 1;
                    searchForm.submit();
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
                searchForm.querySelector('[name="page"]').value = 1;
                searchForm.submit();
            }

            // Ctrl/Cmd + A to select all
            if ((e.ctrlKey || e.metaKey) && e.key === 'a' && !e.target.matches('input[type="text"], textarea')) {
                e.preventDefault();
                selectAll.checked = !selectAll.checked;
                selectAll.dispatchEvent(new Event('change'));
            }
        });

        // Add click event to entire product card (except actions)
        document.querySelectorAll('.product-card-compact').forEach(card => {
            card.addEventListener('click', function(e) {
                // Don't trigger if clicking on checkbox, actions, or links
                if (e.target.matches('input[type="checkbox"], .checkbox-label, .action-btn, .action-btn *')) {
                    return;
                }
                
                // Find the checkbox inside this card
                const checkbox = this.querySelector('.product-checkbox');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                }
            });
        });

        // Image lazy loading
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.product-image-compact img');

            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.getAttribute('src');
                        observer.unobserve(img);
                    }
                });
            }, {
                rootMargin: '100px 0px'
            });

            images.forEach(img => imageObserver.observe(img));
        });
    </script>
</body>

</html>