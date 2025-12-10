<?php
// includes/products.php → FINAL BULLETPROOF VERSION (works forever)
require_once dirname(__DIR__) . '/config/database.php';

$stmt     = $pdo->query("SELECT * FROM products WHERE is_featured = 1 ORDER BY created_at DESC");
$products = $stmt->fetchAll();
?>

<section id="products" class="section products-section">
    <div class="container">
        <h2>Our Featured Products</h2>
        <p class="section-subtitle">High-quality hardware and software solutions to power your business.</p>

        <?php if (empty($products)): ?>
            <p style="text-align:center;padding:60px;c olor:#777;">
                No featured products yet — they will appear automatically when added!
            </p>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $p): ?>
                    <div class="product-card">
                        <img src="<?= $p['image'] ? 'uploads/' . $p['image'] : 'assets/images/placeholder.jpg' ?>"
                             alt="<?= htmlspecialchars($p['title']) ?>"
                             class="product-image">

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