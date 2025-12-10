<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

include '../config.php'; // Your DB connection file (e.g., $conn = new mysqli(...))

// Fetch all products from DB
$stmt = $conn->prepare("SELECT * FROM products");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Products</title>
    <link rel="stylesheet" href="../css/style.css"> <!-- Your main CSS -->
</head>
<body>
    <h1>Manage Products</h1>
    <a href="add_product.php">Add New Product</a>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
            <th>Image</th>
            <th>Featured</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td> <!-- htmlspecialchars prevents XSS attacks -->
            <td><?php echo htmlspecialchars($row['description']); ?></td>
            <td><img src="../<?php echo $row['image']; ?>" alt="Product Image" width="100"></td>
            <td><?php echo $row['featured'] ? 'Yes' : 'No'; ?></td>
            <td>
                <a href="edit_product.php?id=<?php echo $row['id']; ?>">Edit</a>
                <a href="delete_product.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>

<?php $stmt->close(); $conn->close(); ?>