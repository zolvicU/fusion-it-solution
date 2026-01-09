<?php
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Fusion I.T. Solutions - IT Support & Services</title>
    <meta name="description" content="Contact Fusion I.T. Solutions for professional IT support, CCTV installation, networking solutions, and system development services.">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #3b82f6;
            --primary-50: #eff6ff;
            --success: #10b981;
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-500: #64748b;
            --slate-400: #94a3b8;
            --slate-300: #cbd5e1;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50: #f8fafc;
            --radius-sm: 8px;
            --radius: 12px;
            --radius-md: 16px;
            --radius-lg: 20px;
            --radius-xl: 24px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            line-height: 1.7;
            font-weight: 300; /* Inter Light */
            color: var(--slate-700);
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Make all text use Light weight by default */
        h1, h2, h3, h4, h5, h6,
        p, span, div, a, li,
        label, input, textarea, button {
            font-family: 'Inter', sans-serif;
            font-weight: 300;
        }

        /* Specific weight adjustments */
        h1, h2, h3,
        .btn,
        .info-item strong {
            font-weight: 400;
        }

        /* Hero Section */
        .contact-hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 100px 20px 140px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-pattern {
            position: absolute;
            inset: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
        }

        .contact-hero h1 {
            font-size: clamp(36px, 5vw, 48px);
            margin-bottom: 16px;
            line-height: 1.2;
            letter-spacing: -0.02em;
            position: relative;
            z-index: 1;
        }

        .contact-hero p {
            font-size: 18px;
            max-width: 720px;
            margin: 0 auto;
            opacity: 0.9;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }

        /* Main Container */
        .contact-wrapper {
            max-width: 1200px;
            margin: -80px auto 100px;
            padding: 0 20px;
            position: relative;
            z-index: 2;
        }

        /* Contact Card */
        .contact-card {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            padding: 48px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            border: 1px solid var(--slate-200);
        }

        /* Left Info Section */
        .contact-info {
            display: flex;
            flex-direction: column;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 32px;
        }

        .nav-logo {
            height: 64px;
            width: auto;
        }

        .logo-text h2 {
            font-size: 20px;
            color: var(--slate-900);
            margin-bottom: 4px;
        }

        .logo-text p {
            font-size: 14px;
            color: var(--slate-500);
        }

        .info-section {
            margin-bottom: 40px;
        }

        .info-section h3 {
            font-size: 20px;
            color: var(--slate-900);
            margin-bottom: 24px;
        }

        .info-item {
            margin-bottom: 28px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .info-icon {
            width: 48px;
            height: 48px;
            background: var(--primary-50);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 20px;
            flex-shrink: 0;
        }

        .info-content h4 {
            font-size: 16px;
            color: var(--slate-900);
            margin-bottom: 6px;
        }

        .info-content p {
            font-size: 15px;
            color: var(--slate-600);
            line-height: 1.5;
        }

        .info-content a {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .info-content a:hover {
            color: var(--primary-dark);
        }

        /* Back Button */
        .back-btn-container {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid var(--slate-200);
        }

        .back-home {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 28px;
            font-size: 15px;
            color: var(--primary);
            background: var(--primary-50);
            border: 2px solid transparent;
            border-radius: var(--radius);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .back-home:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        /* Form Section */
        .contact-form-wrapper {
            position: relative;
        }

        .form-header {
            margin-bottom: 32px;
        }

        .form-header h2 {
            font-size: 24px;
            color: var(--slate-900);
            margin-bottom: 8px;
        }

        .form-header p {
            font-size: 15px;
            color: var(--slate-600);
        }

        /* Success Message */
        .success-message {
            background: #f0fdf4;
            color: #166534;
            padding: 18px 24px;
            border-radius: var(--radius);
            border: 1px solid #bbf7d0;
            margin-bottom: 32px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-message i {
            font-size: 20px;
            color: #10b981;
            margin-top: 2px;
        }

        /* Form Styles */
        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            position: relative;
        }

        label {
            display: block;
            font-size: 14px;
            color: var(--slate-700);
            margin-bottom: 8px;
            font-weight: 400;
        }

        .required::after {
            content: "*";
            color: #dc2626;
            margin-left: 4px;
        }

        input,
        textarea {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--slate-300);
            border-radius: var(--radius);
            font-size: 15px;
            font-family: inherit;
            background: white;
            transition: all 0.3s ease;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        input::placeholder,
        textarea::placeholder {
            color: var(--slate-400);
        }

        textarea {
            min-height: 140px;
            resize: vertical;
            line-height: 1.5;
        }

        /* Character Counter */
        .char-counter {
            position: absolute;
            right: 12px;
            bottom: 12px;
            font-size: 12px;
            color: var(--slate-500);
            background: white;
            padding: 2px 6px;
            border-radius: 10px;
        }

        /* Submit Button */
        .btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: var(--radius);
            padding: 16px 32px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: var(--shadow);
            margin-top: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* Loading State */
        .btn.loading {
            opacity: 0.8;
            cursor: wait;
        }

        .btn.loading i {
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Recaptcha */
        .g-recaptcha {
            margin: 10px 0;
            transform-origin: left top;
            -webkit-transform-origin: left top;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .contact-card {
                grid-template-columns: 1fr;
                padding: 32px;
                gap: 40px;
            }

            .contact-hero {
                padding: 80px 20px 120px;
            }

            .contact-wrapper {
                margin: -80px auto 60px;
            }

            .info-item {
                align-items: flex-start;
            }
        }

        @media (max-width: 600px) {
            .contact-card {
                padding: 24px;
            }

            .contact-hero h1 {
                font-size: 32px;
            }

            .contact-hero p {
                font-size: 16px;
            }

            .logo-section {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .info-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .info-icon {
                width: 40px;
                height: 40px;
                font-size: 18px;
            }

            .g-recaptcha {
                transform: scale(0.85);
                transform-origin: 0 0;
            }

            .btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .g-recaptcha {
                transform: scale(0.75);
            }
        }

        /* Form Validation */
        .error {
            border-color: #dc2626 !important;
        }

        .error-message {
            color: #dc2626;
            font-size: 13px;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Floating Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .floating {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>

<body>

    <section class="contact-hero">
        <div class="hero-pattern"></div>
        <h1>Contact Fusion I.T. Solutions</h1>
        <p>Get in touch with our expert team for IT support, networking solutions, and technology services.</p>
    </section>

    <section class="contact-wrapper">
        <div class="contact-card">

            <!-- Left Info Section -->
            <div class="contact-info">
                <div class="logo-section">
                    <img src="assets/images/fusionlogo2.png" alt="Fusion I.T. Solutions" class="nav-logo">
                    <div class="logo-text">
                        <h2>Fusion I.T. Solutions</h2>
                        <p>Technology & Connectivity Experts</p>
                    </div>
                </div>

                <div class="info-section">
                    <h3>Contact Information</h3>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <h4>Email Address</h4>
                            <p>
                                <a href="mailto:admin@fusionitsolution.com">
                                    admin@fusionitsolution.com
                                </a>
                            </p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="info-content">
                            <h4>Phone Numbers</h4>
                            <p>
                                <a href="tel:+639451464806">+63 945 146 4806</a><br>
                                <a href="tel:+639183114656">+63 918 311 4656</a>
                            </p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-content">
                            <h4>Office Hours</h4>
                            <p>
                                Monday – Saturday<br>
                                8:00 AM – 5:00 PM
                            </p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon floating">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div class="info-content">
                            <h4>Services Offered</h4>
                            <p>
                                CCTV Installation • Networking<br>
                                System Development • IT Support<br>
                                Security Solutions • Digital Infrastructure
                            </p>
                        </div>
                    </div>
                </div>

                <div class="back-btn-container">
                    <a href="./index.php" class="back-home">
                        <i class="fas fa-arrow-left"></i>
                        Back to Homepage
                    </a>
                </div>
            </div>

            <!-- Right Form Section -->
            <div class="contact-form-wrapper">
                <div class="form-header">
                    <h2>Send Us a Message</h2>
                    <p>Fill out the form below and our team will get back to you within 24 hours.</p>
                </div>

                <?php if (isset($_SESSION['contact_success'])): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <?php echo htmlspecialchars($_SESSION['contact_success']); ?>
                            <?php unset($_SESSION['contact_success']); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['contact_error'])): ?>
                    <div class="success-message" style="background: #fef2f2; color: #dc2626; border-color: #fecaca;">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            <?php echo htmlspecialchars($_SESSION['contact_error']); ?>
                            <?php unset($_SESSION['contact_error']); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="send-contact.php" method="POST" id="contactForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="form-group">
                        <label for="name" class="required">Full Name</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               placeholder="Enter your full name" 
                               required
                               maxlength="100"
                               oninput="updateCharCounter(this, 'nameCounter')">
                        <div class="char-counter" id="nameCounter">0/100</div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="required">Email Address</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               placeholder="your.email@example.com" 
                               required
                               maxlength="100"
                               oninput="updateCharCounter(this, 'emailCounter')">
                        <div class="char-counter" id="emailCounter">0/100</div>
                    </div>

                    <div class="form-group">
                        <label for="message" class="required">Your Message</label>
                        <textarea id="message" 
                                  name="message" 
                                  placeholder="How can we help you? Please provide details about your IT needs..." 
                                  required
                                  maxlength="1000"
                                  oninput="updateCharCounter(this, 'messageCounter')"></textarea>
                        <div class="char-counter" id="messageCounter">0/1000</div>
                    </div>

                    <div class="g-recaptcha" data-sitekey="6LeaXC8sAAAAAHvZAwYmurwYEf6MKPqD1rTWU34u"></div>

                    <button type="submit" class="btn" id="submitBtn">
                        <i class="fas fa-paper-plane"></i>
                        Send Message
                    </button>
                </form>
            </div>

        </div>
    </section>

    <script>
        // Character counter
        function updateCharCounter(input, counterId) {
            const counter = document.getElementById(counterId);
            const maxLength = input.getAttribute('maxlength');
            const currentLength = input.value.length;
            counter.textContent = `${currentLength}/${maxLength}`;
            
            // Add warning class when approaching limit
            if (currentLength > maxLength * 0.8) {
                counter.style.color = '#f59e0b';
            } else if (currentLength > maxLength * 0.9) {
                counter.style.color = '#dc2626';
            } else {
                counter.style.color = '';
            }
        }

        // Initialize counters
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = ['name', 'email', 'message'];
            inputs.forEach(id => {
                const input = document.getElementById(id);
                const counter = document.getElementById(id + 'Counter');
                if (input && counter) {
                    updateCharCounter(input, id + 'Counter');
                }
            });
        });

        // Form submission
        const contactForm = document.getElementById('contactForm');
        const submitBtn = document.getElementById('submitBtn');

        contactForm.addEventListener('submit', function(e) {
            // Basic validation
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const message = document.getElementById('message').value.trim();
            
            if (!name || !email || !message) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return;
            }

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return;
            }

            // Show loading state
            submitBtn.innerHTML = '<span class="loading"></span> Sending...';
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });

        // Auto-resize textarea
        const textarea = document.getElementById('message');
        if (textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        }

        // Form validation on blur
        const formInputs = document.querySelectorAll('input, textarea');
        formInputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                }
            });
        });

        // Smooth scroll for page load
        window.addEventListener('load', function() {
            if (window.location.hash === '#contact') {
                const contactSection = document.querySelector('.contact-wrapper');
                if (contactSection) {
                    contactSection.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });

        // Add focus effect to form inputs
        formInputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
                this.parentElement.style.transition = 'transform 0.2s ease';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = '';
            });
        });
    </script>
</body>
</html>