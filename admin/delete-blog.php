<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';

$id = $_GET['id'] ?? 0;
if ($id <= 0) {
    header('Location: blog-list.php');
    exit;
}

try {
    // Fetch image to delete the file
    $stmt = $pdo->prepare("SELECT image FROM blog_posts WHERE id = ?");
    $stmt->execute([$id]);
    $image = $stmt->fetchColumn();

    // Delete the post from database
    $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
    $stmt->execute([$id]);

    // Delete image file if exists
    if ($image) {
        $file_path = "../assets/uploads/blog/" . $image;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // Redirect with success
    header('Location: blog-list.php?msg=deleted');
    exit;
} catch (Exception $e) {
    // Redirect with error if something fails
    header('Location: blog-list.php?msg=error');
    exit;
}
?>