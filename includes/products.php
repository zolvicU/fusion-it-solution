<?php
// includes/products.php → OPTIMIZED VERSION
require_once dirname(__DIR__) . '/config/database.php';

$stmt = $pdo->query("SELECT * FROM products WHERE is_featured = 1 ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section id="products" class="section products-section">
    <div class="container">
        <h2>Our Featured Products</h2>
        <p class="section-subtitle">High-quality hardware and software solutions to power your business.</p>

        <?php if (empty($products)): ?>
            <p class="no-products-message">
                No featured products yet. Add some in the admin panel!
            </p>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $p): 
                    // Build image paths
                    $image_filename = !empty($p['image']) ? htmlspecialchars($p['image']) : 'placeholder.jpg';
                    $webp_path = 'assets/uploads/products/' . pathinfo($image_filename, PATHINFO_FILENAME) . '.webp';
                    $fallback_path = 'assets/uploads/products/' . $image_filename;
                    $placeholder_path = 'assets/images/placeholder.jpg';

                    // Use meaningful alt text
                    $alt_text = htmlspecialchars($p['title']) . " - " . strip_tags($p['description']);
                    $alt_text = substr($alt_text, 0, 125); // Limit length for alt
                ?>
                    <article class="product-card">
                        <picture>
                            <!-- WebP version first (modern browsers) -->
                            <source srcset="<?= $webp_path ?>" type="image/webp">
                            
                            <!-- Fallback to original (JPG/PNG) -->
                            <img src="<?= $fallback_path ?>"
                                 srcset="<?= $fallback_path ?> 1x"
                                 alt="<?= $alt_text ?>"
                                 width="600"
                                 height="400"
                                 loading="lazy"
                                 class="product-image"
                                 onerror="this.src='<?= $placeholder_path ?>'; this.onerror=null;">
                        </picture>

                        <div class="product-content">
                            <h3 class="product-title"><?= htmlspecialchars($p['title']) ?></h3>
                            <p class="product-description">
                                <?= nl2br(htmlspecialchars(substr(strip_tags($p['description']), 0, 150))) ?>
                                <?= strlen(strip_tags($p['description'])) > 150 ? '...' : '' ?>
                            </p>
                            <a href="#contact" 
                               class="btn-product-details" 
                               aria-label="Inquire about <?= htmlspecialchars($p['title']) ?>">
                                Inquire Now
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>