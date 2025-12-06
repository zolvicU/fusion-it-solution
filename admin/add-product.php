<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Add Product</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    
    body {
        font-family: 'Inter', sans-serif;
        background: #f8f9fa;
        margin: 0;
        padding: 20px;
        min-height: 100vh;
    }
    .container {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        padding: 40px 30px;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    h1 {
        text-align: center;
        margin-bottom: 30px;
        color: #222;
    }
    label {
        display: block;
        margin: 20px 0 8px;
        font-weight: 600;
        color: #333;
    }
    input[type="text"],
    textarea,
    input[type="file"] {
        width: 100%;
        padding: 14px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 16px;
        box-sizing: border-box;
    }
    textarea {
        min-height: 120px;
        resize: vertical;
    }
    input[type="checkbox"] {
        transform: scale(1.2);
        margin-right: 10px;
    }
    button {
        background: #007bff;
        color: white;
        border: none;
        padding: 14px 32px;
        font-size: 16px;
        border-radius: 8px;
        cursor: pointer;
        width: 100%;
        margin-top: 20px;
        transition: background 0.3s;
    }
    button:hover {
        background: #0056b3;
    }
    .back-link {
        text-align: center;
        margin-top: 20px;
    }

    @media (max-width: 600px) {
        .container {
            padding: 30px 20px;
            margin: 10px;
            border-radius: 12px;
        }
        h1 { font-size: 1.8rem; }
    }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add New Product</h1>
        <form action="process-add-product.php" method="POST" enctype="multipart/form-data">
            <label for="title">Product Title</label>
            <input type="text" id="title" name="title" required>

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="6" required></textarea>

            <label for="image">Product Image</label>
            <input type="file" id="image" name="image" accept="image/*">

            <label>
                <input type="checkbox" name="featured" value="1">
                Mark as Featured (will show on homepage)
            </label>

            <button type="submit">Add Product</button>
        </form>
        <p><a href="../index.php">← Back to Homepage</a></p>
    </div>
</body>
</html>