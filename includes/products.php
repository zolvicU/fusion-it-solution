<?php
// includes/products.php → FINAL WORKING VERSION
require_once dirname(__DIR__) . '/config/database.php';

$stmt = $pdo->query("SELECT * FROM products WHERE is_featured = 1 ORDER BY id DESC");  // Changed to id DESC for newest first
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section id="products" class="section products-section">
    <div class="container">
        <h2>Our Featured Products</h2>
        <p class="section-subtitle">High-quality hardware and software solutions to power your business.</p>

        <?php if (empty($products)): ?>
            <p style="text-align:center; padding:80px; color:#777; font-size:18px;">
                No featured products yet. Add some in the admin panel!
            </p>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $p): ?>
                    <div class="product-card">
                        <?php
                        // Correct full path from web root
                        $image_path = !empty($p['image'])
                            ? 'assets/uploads/products/' . htmlspecialchars($p['image'])
                            : 'assets/images/placeholder.jpg';
                        ?>
                        <img src="<?= $image_path ?>"
                             alt="<?= htmlspecialchars($p['title']) ?>"
                             class="product-image"
                             onerror="this.onerror=null; this.src='assets/images/placeholder.jpg';">

                        <div class="product-content">
                            <h3 class="product-title"><?= htmlspecialchars($p['title']) ?></h3>
                            <p class="product-description"><?= nl2br(htmlspecialchars($p['description'])) ?></p>
                            <a href="#contact" class="btn-product-details">Inquire Now</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>