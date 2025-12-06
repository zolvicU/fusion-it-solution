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
            <h1 class="fade-in"><b>Your #1</b> Go-To Partner for All Your IT Needs
</h1>
            <p class="hero-subtitle fade-in-delay"> IT Network Solutions •  Software Development • Surveillance and Security Technologies • eace of mind.
• System Services •  IT Products</p>
            <div class="hero-buttons fade-in-delay2">
                <a href="#services" class="btn-primary">Explore Services</a>
                <a href="#contact" class="btn-secondary">Start Project</a>
            </div>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="scroll-indicator">
        <span>Scroll to Discover</span>
        <div class="arrow"></div>
    </div>
</section>


<?php include 'includes/section-products.php'; ?>

<section id="services" class="section">
    <div class="container">
        <h2>Our Services</h2>ud Mig
        <h2>Our General Services</h2>
        <p>CCTV Security & Intercom (both wired and wireless)<br>
• LAN & WAN<br>
• Wireless Networking (including
long range wireless)<br>
• PABX<br>
• Internet Services<br>
• Solar Panel System (Grid, OffGrid, Hybrid)<br>
• Fire Alarm Systems • Mono Pole<br>
Design and Construction</p><br>
<h2>System Services</h2>
<p>Company or Business Server
Setup<br>
• Accounting Systems / Payroll
Systems<br>
• Website Development<br>
• Software/Application
Development<br>
• Business Email Solutions</p>
    </div>
</section>

<!-- About Section -->
<section id="about" class="section about-section">
    <div class="container">
        <h2 class="about-main-title">About Us</h2>

        <div class="about-grid">
            <!-- Left Column: Our Mission -->
            <div class="mission-column">
                <p class="about-text">
                   <b>Fusion IT Solution is an IT solutions provider in the Philippines, empowering 
                    businesses with over 15 years of expertise</b> in:
                </p>
                <ul class="about-list">
                    <li>IT Network Solutions: From basic troubleshooting to complex network infrastructure design, implementation, and management.</li>
                    <li>Software Development: Custom software tailored to your business needs, including telemedicine platforms, joborder management systems, and more.</li>
                    <li>Surveillance and Security Technologies: Comprehensive CCTV and intercom solutions for enhanced security and peace of mind.</li>
                    <li>System Services: Set up servers, implement business systems, and develop websites or applications.</li>
                    <li>IT Products: Equip your business with reliable computer units, CCTV cameras, Wi-Fi solutions, and other essential hardware.</li>
                </ul>
            </div>

            <!-- Right Column: Why We Do What We Do -->
            <div class="why-column">
                <h3 class="about-subtitle">Core Values</h3>
                <ul class="why-list">
                    <li>Honesty: Building trust through transparent communication and ethical practices.</li>
                    <li> Reliability: Delivering solutions that consistently meet or exceed expectations.</li>
                    <li>Affordability: Providing value-driven solutions that align with your budget.</li>
                    <li>Affordability: Providing value-driven solutions that align with your budget.</li>
                </ul>
            </div>
        </div>

        <!-- Vision Section -->
        <div class="vision-section">
            <h3 class="about-subtitle vision-title">Our Vision and Commitment</h3>
            <p class="about-text vision-text">
                Our vision is to become a nationally recognized IT solutions provider, reaching more clients and becoming their go-to partner for all their IT needs.
            </p>
            <p class="about-text" style="margin-top: 20px;">
                We are committed to:
            </p>
            <ul class="why-list vision-list">
                <li>Staying at the forefront of technology advancements to offer cutting-edge solutions.</li>
                <li>Delivering exceptional service to build lasting client relationships.</li>
                <li>Driving innovation and enabling businesses to thrive in the digital age.</li>
            </ul>
        </div>
    </div>
</section>

<section id="contact" class="section">
    <div class="container">
        <h2>Contact Us</h2>
        <p>admin@fusionitsolution.com | Phone: +0918 311 4656
</p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>