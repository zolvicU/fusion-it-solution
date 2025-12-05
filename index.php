<?php include 'includes/header.php'; ?>

<!-- PCC Builders Inspired Parallax Hero -->
<section class="hero" id="home">
    <!-- Layer 1: Deep Background (slow) -->
    <div class="hero-bg-deep rellax" data-rellax-speed="-8" data-rellax-xs-speed="0">
        <img src="assets/images/hero-bg2.png" alt="Fusion IT Skyline">
    </div>

    <!-- Layer 2: Midground Overlay (medium speed) -->
    <div class="hero-bg-mid rellax" data-rellax-speed="-4" data-rellax-xs-speed="0">
        <img src="assets/images/hero-fg.png" alt="Tech Pattern">
    </div>

    <!-- Navigation -->
    <nav class="hero-nav">
        <div class="container nav-container">
            <a href="#home" class="logo">
                <img src="assets/images/fusionlogo2.png" alt="Fusion IT Logo" class="nav-logo">
                
            </a>

            <ul class="nav-links" id="navLinks">
                <li><a href="#home">Home</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>

            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Content -->
    <div class="hero-content rellax" data-rellax-speed="2" data-rellax-xs-speed="0">
        <div class="hero-text">
            <h1 class="fade-in">Building Tomorrow's Tech Today</h1>
            <p class="hero-subtitle fade-in-delay">Enterprise Solutions • Cloud Innovation • Secure Foundations • Unmatched Support</p>
            <div class="hero-buttons fade-in-delay2">
                <a href="#services" class="btn-primary">Explore Services</a>
                <a href="#contact" class="btn-secondary">Start Project</a>
            </div>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="scroll-indicator">
        <span>Scroll to Discover</span>
        <div class="arrow up"></div>
    </div>
</section>

<!-- Other Sections -->
<section id="about" class="section">
    <div class="container">
        <h2>About Fusion IT</h2>
        <p>We deliver cutting-edge IT solutions that scale with your business. From cloud infrastructure to 24/7 support, we've got you covered.</p>
    </div>
</section>

<section id="services" class="section">
    <div class="container">
        <h2>Our Services</h2>
        <p>Cloud Migration • Cybersecurity • Managed IT • Custom Development • 24/7 Support</p>
    </div>
</section>

<section id="contact" class="section">
    <div class="container">
        <h2>Contact Us</h2>
        <p>Email: info@fusionit.com | Phone: +1 (555) 123-4567</p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>