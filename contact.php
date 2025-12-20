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
    <title>Contact Us | Fusion I.T. Solution</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            line-height: 1.5;
        }

        /* HERO */
        .contact-hero {
            background: linear-gradient(135deg, #0047ff, #007bff);
            color: #fff;
            padding: 100px 20px 140px;
            text-align: center;
        }

        .contact-hero h1 {
            font-size: clamp(32px, 5vw, 42px);
            font-weight: 700;
            margin-bottom: 5px;
        }

        .contact-hero p {
            font-size: 18px;
            max-width: 720px;
            margin: 0 auto;
            opacity: 0.9;
        }

        /* MAIN WRAPPER */
        .contact-wrapper {
            max-width: 1100px;
            margin: -80px auto 80px;
            padding: 0 20px;
        }

        .contact-card {
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.12);
            padding: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
        }

        /* LEFT INFO section */
        .contact-info {
            display: flex;
            flex-direction: column;
        }

        .nav-logo {
            height: 60px;
            width: auto;
            align-self: flex-start;
            margin-bottom: 10px;
        }

        .contact-info h2 {
            font-size: 26px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .info-item {
            margin-bottom: 20px;
            font-size: 15px;
        }

        .info-item strong {
            display: block;
            color: #0047ff;
            margin-bottom: 4px;
        }

        /* BACK BUTTON */
        .back-btn-container {
            margin-top: auto;
            /* Pushes button to bottom of the flex column */
            padding-top: 10px;
        }

        .back-home-fixed {
            display: inline-block;
            padding: 12px 22px;
            font-size: 14px;
            font-weight: 600;
            color: #0047ff;
            background: #ffffff;
            border: 2px solid #0047ff;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .back-home-fixed:hover {
            background: #0047ff;
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 71, 255, 0.25);
        }

        /* FORM section */
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: -10px;
        }

        input,
        textarea {
            width: 100%;
            padding: 14px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 15px;
            font-family: inherit;
        }

        input:focus,
        textarea:focus {
            border-color: #0047ff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 71, 255, 0.1);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .btn {
            background: #0047ff;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 16px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn:hover {
            background: #0036cc;
        }

        /* RECAPTCHA Responsive Fix */
        .g-recaptcha {
            transform-origin: left top;
            -webkit-transform-origin: left top;
        }

        /* MOBILE RESPONSIVE */
        @media (max-width: 900px) {
            .contact-card {
                grid-template-columns: 1fr;
                /* Stack panels vertically */
                padding: 30px;
                gap: 40px;
            }

            .contact-hero {
                padding: 80px 20px 120px;
            }

            .back-btn-container {
                text-align: center;
                order: 3;
                /* Places button at the very bottom on mobile */
            }

            .back-home-fixed {
                width: 100%;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .g-recaptcha {
                transform: scale(0.77);
                /* Shrink recaptcha to fit narrow screens */
            }
        }

        .message-item {
            background: var(--gray-100);
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 16px;

            /* FIX FOR LONG TEXT */
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }
    </style>
</head>

<body>

    <section class="contact-hero">
        <h1>Contact Fusion I.T. Solution</h1>
        <p>Have a question or need IT support? Our team is ready to help.</p>
    </section>

    <section class="contact-wrapper">
        <div class="contact-card">

            <div class="contact-info">
                <img src="assets/images/fusionlogo2.png" alt="Fusion IT Logo" class="nav-logo">
                <h2>Let’s Talk</h2>
                <p>Reach out to us for CCTV installation, networking, system development, and IT services.</p>

                <div class="info-item">
                    <strong>Email</strong>
                    admin@fusionitsolution.com
                </div>

                <div class="info-item">
                    <strong>Phone</strong>
                    +63 9451464806<br>
                    +63 9183114656
                </div>

                <div class="info-item">
                    <strong>Office Hours</strong>
                    Mon – Sat, 8:00 AM – 5:00 PM
                </div>

                <div class="back-btn-container">
                    <a href="./index.php" class="back-home-fixed">← Back to Home</a>
                </div>
            </div>

            <div class="contact-form-wrapper">
                <?php if (isset($_SESSION['contact_success'])): ?>
                    <div style="background:#dcfce7; color:#166534; padding:14px; border-radius:10px; margin-bottom:20px;">
                        <?php echo $_SESSION['contact_success'];
                        unset($_SESSION['contact_success']); ?>
                    </div>
                <?php endif; ?>

                <form action="send-contact.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <label>Full Name</label>
                    <input type="text" name="name" placeholder="Juan DelaCruz" required>

                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="juan@example.com" required>

                    <label>Message</label>
                    <textarea name="message" placeholder="How can we help you?" required></textarea>

                    <div class="g-recaptcha" data-sitekey="6LeaXC8sAAAAAHvZAwYmurwYEf6MKPqD1rTWU34u"></div>

                    <button type="submit" class="btn">Send Message</button>
                </form>
            </div>

        </div>
    </section>

</body>

</html>