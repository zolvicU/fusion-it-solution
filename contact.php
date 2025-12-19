<?php
session_start();

// Generate CSRF token if not set
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

    <!-- Google Font -->
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
        }

        /* HERO */
        .contact-hero {
            background: linear-gradient(135deg, #0047ff, #007bff);
            color: #fff;
            padding: 120px 20px 140px;
            text-align: center;
        }

        .contact-hero h1 {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .contact-hero p {
            font-size: 18px;
            max-width: 720px;
            margin: 0 auto;
        }

        /* MAIN */
        .contact-wrapper {
            max-width: 1100px;
            margin: -90px auto 80px;
            padding: 0 20px;
        }

        .contact-card {
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.12);
            padding: 50px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        /* LEFT INFO */
        .contact-info h2 {
            font-size: 26px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .contact-info p {
            font-size: 15px;
            color: #475569;
            margin-bottom: 30px;
            line-height: 1.7;
        }

        .info-item {
            margin-bottom: 18px;
            font-size: 15px;
        }

        .info-item strong {
            display: block;
            font-weight: 600;
        }

        /* FORM */
        form {
            display: grid;
            gap: 18px;
        }

        label {
            font-size: 14px;
            font-weight: 500;
        }

        input,
        textarea {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 15px;
        }

        input:focus,
        textarea:focus {
            border-color: #0066ff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.15);
        }

        textarea {
            min-height: 140px;
            resize: vertical;
        }

        .btn {
            background: linear-gradient(135deg, #0047ff, #007bff);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 15px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn:hover {
            box-shadow: 0 10px 20px rgba(0, 71, 255, 0.25);
        }

        /* ALERTS */
        .success {
            background: #dcfce7;
            color: #166534;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        /* BACK BUTTON POSITION */
        .contact-form-wrapper {
            position: relative;
            padding-bottom: 5px;
        }

        .back-home-fixed {
            position: absolute;
            bottom: -19%;
            left: 20%;
            padding: 12px 22px;
            font-size: 14px;
            font-weight: 600;
            color: #0047ff;
            background: #ffffff;
            border: 2px solid #0047ff;
            border-radius: 10px;
            text-decoration: none;
        }

        .back-home-fixed:hover {
            background: #0047ff;
            color: #ffffff;
            box-shadow: 0 8px 20px rgba(0, 71, 255, 0.25);
        }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .contact-card {
                grid-template-columns: 1fr;
            }
        }

        .nav-logo {
            height: 70px;
            transition: all 0.3s;
            border-radius: 3px;
            margin-top: 2%;
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

            <!-- LEFT INFO -->
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
                    Monday – Saturday, 8:00 AM – 5:00 PM
                </div><br>
                <div>
                    <!-- BACK TO HOME -->
                    <a href="./index.php" class="back-home-fixed">← Back to Home</a>

                </div>
            </div>

            <!-- FORM -->
            <div class="contact-form-wrapper">

                <?php if (isset($_SESSION['contact_success'])): ?>
                    <div class="success"><?php echo $_SESSION['contact_success'];
                                            unset($_SESSION['contact_success']); ?></div>
                <?php endif; ?>

                <?php if (isset($_SESSION['contact_error'])): ?>
                    <div class="error"><?php echo $_SESSION['contact_error'];
                                        unset($_SESSION['contact_error']); ?></div>
                <?php endif; ?>

                <form action="send-contact.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <label>Full Name</label>
                    <input type="text" name="name" required>

                    <label>Email Address</label>
                    <input type="email" name="email" required>

                    <label>Message</label>
                    <textarea name="message" required></textarea>

                    <div class="g-recaptcha" data-sitekey="6LeaXC8sAAAAAHvZAwYmurwYEf6MKPqD1rTWU34u"></div>

                    <button type="submit" class="btn">Send Message</button>
                </form>
            </div>

        </div>
    </section>

</body>

</html>