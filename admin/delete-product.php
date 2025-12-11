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

try {
    // Get image filename to delete file
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $image = $stmt->fetchColumn();

    // Delete from DB
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);

    // Delete image file if exists
    if ($image) {
        $file_path = "../assets/uploads/products/" . $image;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    header('Location: dashboard.php?msg=deleted');
} catch (Exception $e) {
    header('Location: dashboard.php?msg=error');
}
exit;