<?php
// admin/delete-product.php - Secure & Clean Version

require_once 'includes/auth.php';  // This handles session + login check

require_once '../config/database.php';

$id = $_GET['id'] ?? 0;
if ($id <= 0) {
    header('Location: dashboard.php');
    exit;
}

try {
    // Get image filename first
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $image = $stmt->fetchColumn();

    // Delete the product from database
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);

    // Delete the image file if it exists
    if ($image) {
        $file_path = "../assets/uploads/products/" . $image;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    header('Location: dashboard.php?msg=deleted');
    exit;

} catch (Exception $e) {
    // Optional: log error in production, show generic message
    header('Location: dashboard.php?msg=error');
    exit;
}