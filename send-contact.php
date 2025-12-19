<?php
session_start();

require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] != $_SESSION['csrf_token']) {
        $_SESSION['contact_error'] = "Invalid form submission.";
        header("Location: contact.php");
        exit;
    }

    // Validation
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    if (empty($name) || empty($email) || empty($message)) {
        $_SESSION['contact_error'] = "All fields are required.";
        header("Location: contact.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['contact_error'] = "Invalid email format.";
        header("Location: contact.php");
        exit;
    }

    // Sanitize input
    $name = htmlspecialchars($name);
    $email = htmlspecialchars($email);
    $message = htmlspecialchars($message);

    // CAPTCHA verification
    $secret = "6LeaXC8sAAAAAPxpI4igqbOQxZYZs9MsYQpyfG_U"; // Replace with Secret key
    $response = $_POST['g-recaptcha-response'] ?? '';
    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$response");
    $captcha_success = json_decode($verify);
    if ($captcha_success->success == false) {
        $_SESSION['contact_error'] = "CAPTCHA verification failed. Please try again.";
        header("Location: contact.php");
        exit;
    }

    // Store in database
    try {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $message]);
    } catch (Exception $e) {
        $_SESSION['contact_error'] = "Error saving message.";
        header("Location: contact.php");
        exit;
    }

    // Send email (optional, replace with your email)
    $to = "victormalunesumali@email.com"; // Change to your email
    $subject = "New Contact Message from " . $name;
    $body = "Name: $name\nEmail: $email\nMessage: $message";
    $headers = "From: $email";
    mail($to, $subject, $body, $headers); // Use PHPMailer for production

    $_SESSION['contact_success'] = "Message sent successfully!";
    header("Location: contact.php");
    exit;
} else {
    header("Location: contact.php");
    exit;
}
