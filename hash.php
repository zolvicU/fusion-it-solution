<?php
// hash.php
$password = "admin123";  // Change this to your password
echo "Hashed password: " . password_hash($password, PASSWORD_DEFAULT);
?>