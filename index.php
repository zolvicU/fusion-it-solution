<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fusion I.T. Solution</title>
    <meta name="description" content="Professional IT solutions and services">

    <!-- Google Fonts - will be decided later, using Inter for now -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Our CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

</head>
<body>

    <!-- Navigation Bar (fixed at the top) -->
    <nav>
        <div class="logo">
            <img src="assets/images/fusionlogo.png" alt="Fusion I.T. Solution Logo" class="nav-logo">
            <span>Fusion I.T. Solution</span>
        </div>

        <!-- Hamburger icon (only shows on mobile) -->
        <div class="hamburger">☰</div>

        <!-- Navigation links wrapper -->
        <div class="nav-links">
            <ul>
                <li><a href="#about">About</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#products">Products</a></li>
                <li><a href="#certifications">Certifications</a></li>
                <li><a href="#contact">Contact</a></li>
                <li><a href="/blog">Blog</a></li>
            </ul>
        </div>
    </nav>

    <!-- Parallax Hero Section -->
    <section class="hero">
        <!-- Parallax background (moves slowly) -->
        <div class="hero-bg rellax" data-rellax-speed="-5">
            <img src="assets/images/hero-bg2.jpg" alt="Fusion IT Hero Background">
        </div>

        <!-- Foreground content (moves faster) -->
        <div class="hero-content rellax" data-rellax-speed="2">
            <h1>Welcome to Fusion I.T. Solution</h1>
            <p>This is a placeholder page. Website is under construction!</p>
            <p>Day 2 – Project structure created successfully! Keep going!</p>
            <div class="hero-buttons">
                <a href="#services" class="btn-primary">Our Services</a>
                <a href="#contact" class="btn-secondary">Get in Touch</a>
            </div>
        </div>
    </section>

    <!-- About -->
    <section id="about" class="section">
        <h2>About Us</h2>
        <p>Content coming soon...</p>
    </section>

    <!-- Services -->
    <section id="services" class="section">
        <h2>Our Services</h2>
        <p>Content coming soon...</p>
    </section>

    <!-- Featured Products -->
    <section id="products" class="section">
        <h2>Featured Products</h2>
        <p>Content coming soon...</p>
    </section>

    <!-- Certifications -->
    <section id="certifications" class="section">
        <h2>Certifications</h2>
        <p>Content coming soon...</p>
    </section>

    <!-- Contact -->
    <section id="contact" class="section">
        <h2>Contact Us</h2>
        <p>Form coming soon...</p>
    </section>

    <!-- Footer -->
    <footer>
        <p>© 2025 Fusion I.T. Solution. All rights reserved.</p>
    </footer>

    <!-- Rellax JS -->
    <script src="assets/js/rellax.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var rellax = new Rellax('.rellax', {
                center: true
            });
        });
    </script>

    <!-- Mobile Menu Toggle -->
    <script>
        document.querySelector('.hamburger').addEventListener('click', function() {
            document.querySelector('.nav-links').classList.toggle('active');
        });
    </script>

</body>
</html>
