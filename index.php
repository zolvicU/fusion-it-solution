<?php include 'includes/header.php'; ?>

<section class="hero" id="home">
    <div class="hero-bg-deep rellax" data-rellax-speed="-8" data-rellax-xs-speed="0">
        <img src="assets/images/hero-bg2.png" alt="Modern city skyline with digital technology overlay representing Fusion IT Solutions">
    </div>

    <div class="hero-bg-mid rellax" data-rellax-speed="-4" data-rellax-xs-speed="0">
        <img src="assets/images/hero-fg.png" alt="Abstract tech pattern overlay for innovative IT services">
    </div>

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

            <div class="hamburger" id="hamburger" tabindex="0">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <div class="hero-content rellax" data-rellax-speed="2" data-rellax-xs-speed="0">
        <div class="hero-text">
            <h1 class="fade-in"><b>Your #1</b> Go-To Partner for All Your IT Needs</h1>
            <p class="hero-subtitle fade-in-delay"> IT Network Solutions • Software Development • Surveillance and Security Technologies • Peace of mind • System Services • IT Products</p>
            <div class="hero-buttons fade-in-delay2">
                <a href="#services" class="btn-primary">Explore Services</a>
                <a href="#contact" class="btn-secondary">Start Project</a>
            </div>
        </div>
    </div>

    <div class="scroll-indicator">
        <span>Scroll to Discover</span>
        <div class="arrow"></div>
    </div>
</section>

<?php include 'includes/products.php'; ?>

<section id="services" class="services-section">
    <div class="container">
        <div class="services-intro">
            <h2>Our Services</h2>
            <p>Comprehensive technology solutions tailored to modernize and secure your business.</p>
        </div>

        <h3 class="category-title">General Services</h3>
        <div class="services-grid">

            <div class="service-card">
                <div class="icon-wrapper" aria-hidden="true">🛡️</div>
                <span class="visually-hidden">Security</span>
                <h3>Security</h3>
                <ul>
                    <li>CCTV Security (Wired & Wireless)</li>
                    <li>Intercom Systems</li>
                    <li>Fire Alarm Systems</li>
                </ul>
            </div>

            <div class="service-card">
                <div class="icon-wrapper" aria-hidden="true">📡</div>
                <h3>Networking</h3>
                <ul>
                    <li>LAN & WAN Setup</li>
                    <li>Wireless Networking (Long Range)</li>
                    <li>PABX Systems</li>
                    <li>Internet Services</li>
                </ul>
            </div>

            <div class="service-card">
                <div class="icon-wrapper" aria-hidden="true">⚡</div>
                <h3>Power & Infrastructure</h3>
                <ul>
                    <li>Solar Panel Systems (Grid/Off-Grid)</li>
                    <li>Mono Pole Design</li>
                    <li>Construction Services</li>
                </ul>
            </div>
        </div>

        <h3 class="category-title">System Services</h3>
        <div class="services-grid">

            <div class="service-card">
                <div class="icon-wrapper" aria-hidden="true">💻</div>
                <h3>Development</h3>
                <ul>
                    <li>Website Development</li>
                    <li>Custom Software/Apps</li>
                    <li>Accounting & Payroll Systems</li>
                </ul>
            </div>

            <div class="service-card">
                <div class="icon-wrapper" aria-hidden="true">🚀</div>
                <h3>Business Solutions</h3>
                <ul>
                    <li>Company Server Setup</li>
                    <li>Business Email Solutions</li>
                    <li>Database Management</li>
                </ul>
            </div>

        </div>
    </div>
</section>

<!-- Latest Blog Posts Section -->
<section id="latest-blog" style="padding: 100px 20px; background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); position: relative; overflow: hidden;">
    <!-- Background accent -->
    <div style="position: absolute; top: 0; right: 0; width: 300px; height: 300px; background: radial-gradient(circle, rgba(37, 99, 235, 0.08) 0%, transparent 70%); transform: translate(30%, -30%); z-index: 0;"></div>
    
    <div class="container" style="max-width: 1280px; margin: 0 auto; position: relative; z-index: 1;">
        <!-- Section Header -->
        <div style="text-align: center; margin-bottom: 64px;">
            <span style="display: inline-block; font-family: 'Inter', sans-serif; font-weight: 400; font-size: 14px; color: #2563eb; background: #eff6ff; padding: 8px 20px; border-radius: 20px; margin-bottom: 16px; letter-spacing: 0.5px; text-transform: uppercase;">
                Insights & Updates
            </span>
            <h2 style="font-family: 'Inter', sans-serif; font-weight: 300; font-size: clamp(32px, 4vw, 42px); color: #0f172a; margin: 0 0 16px; line-height: 1.2; letter-spacing: -0.02em;">
                Our Blog
            </h2>
            <p style="font-family: 'Inter', sans-serif; font-weight: 300; font-size: 18px; color: #64748b; max-width: 600px; margin: 0 auto; line-height: 1.6;">
                Expert perspectives on technology, networking, and digital transformation
            </p>
        </div>

        <!-- Blog Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 32px; margin-bottom: 60px;">
            <?php
            require_once 'config/database.php';

            try {
                $stmt = $pdo->query("SELECT * FROM blog_posts ORDER BY created_at DESC LIMIT 3");
                $latest_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($latest_posts)) {
                    echo '<div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                            <div style="font-size: 48px; color: #cbd5e1; margin-bottom: 20px;">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <p style="font-family: \'Inter\', sans-serif; font-weight: 300; font-size: 18px; color: #64748b;">
                                We are currently preparing insightful content. Check back soon!
                            </p>
                          </div>';
                } else {
                    foreach ($latest_posts as $post) {
                        $excerpt = strip_tags($post['content']);
                        if (strlen($excerpt) > 120) {
                            $excerpt = substr($excerpt, 0, 120);
                            $excerpt = substr($excerpt, 0, strrpos($excerpt, ' ')) . '...';
                        }
                        
                        // Calculate reading time
                        $word_count = str_word_count(strip_tags($post['content']));
                        $reading_time = ceil($word_count / 200);
            ?>
                        <article style="background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1); position: relative; height: 100%; display: flex; flex-direction: column;">
                            <!-- Hover effect -->
                            <div style="position: absolute; inset: 0; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); opacity: 0; transition: opacity 0.3s ease; z-index: 1; pointer-events: none;"></div>
                            
                            <!-- Featured badge -->
                            <div style="position: absolute; top: 20px; left: 20px; z-index: 2;">
                                <span style="font-family: 'Inter', sans-serif; font-weight: 400; font-size: 11px; color: white; background: #2563eb; padding: 6px 14px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block;">
                                    Featured
                                </span>
                            </div>
                            
                            <!-- Image Container -->
                            <div style="height: 220px; overflow: hidden; position: relative;">
                                <?php if (!empty($post['image'])): ?>
                                    <img src="assets/uploads/blog/<?php echo htmlspecialchars($post['image']); ?>"
                                        alt="<?php echo htmlspecialchars($post['title']); ?>"
                                        style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.8s ease;"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.1), transparent);"></div>
                                <?php endif; ?>
                                
                                <!-- Fallback image -->
                                <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); display: <?php echo empty($post['image']) ? 'flex' : 'none'; ?>; align-items: center; justify-content: center; color: white; position: relative;">
                                    <i class="fas fa-newspaper" style="font-size: 48px; opacity: 0.8;"></i>
                                    <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.1), transparent);"></div>
                                </div>
                            </div>

                            <!-- Content -->
                            <div style="padding: 28px; flex: 1; display: flex; flex-direction: column; position: relative; z-index: 2; background: white;">
                                <!-- Meta info -->
                                <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px; font-family: 'Inter', sans-serif; font-weight: 300; font-size: 14px; color: #64748b;">
                                    <span style="display: flex; align-items: center; gap: 6px;">
                                        <i class="far fa-calendar" style="font-size: 14px; color: #2563eb;"></i>
                                        <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                                    </span>
                                    <span style="display: flex; align-items: center; gap: 6px;">
                                        <i class="far fa-clock" style="font-size: 14px; color: #2563eb;"></i>
                                        <?php echo $reading_time; ?> min read
                                    </span>
                                </div>

                                <!-- Title -->
                                <h3 style="font-family: 'Inter', sans-serif; font-weight: 400; font-size: 20px; color: #0f172a; margin: 0 0 16px; line-height: 1.4; flex: 1;">
                                    <a href="blog/post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>"
                                        style="color: inherit; text-decoration: none; transition: color 0.2s ease;">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </a>
                                </h3>

                                <!-- Excerpt -->
                                <p style="font-family: 'Inter', sans-serif; font-weight: 300; font-size: 15px; color: #475569; margin-bottom: 24px; line-height: 1.6; flex: 1;">
                                    <?php echo htmlspecialchars($excerpt); ?>
                                </p>

                                <!-- Footer -->
                                <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                                    <a href="blog/post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>"
                                        style="font-family: 'Inter', sans-serif; font-weight: 400; font-size: 15px; color: #2563eb; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s ease; padding: 8px 16px; border-radius: 8px; background: #eff6ff;">
                                        Read Article
                                        <i class="fas fa-arrow-right" style="font-size: 14px; transition: transform 0.3s ease;"></i>
                                    </a>
                                    <span style="font-family: 'Inter', sans-serif; font-weight: 300; font-size: 13px; color: #94a3b8; display: flex; align-items: center; gap: 6px;">
                                        <i class="fas fa-book-open" style="font-size: 13px;"></i>
                                        <?php echo $reading_time; ?> min
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Hover styles -->
                            <style>
                                article:hover {
                                    transform: translateY(-12px);
                                    box-shadow: 0 25px 50px -12px rgba(37, 99, 235, 0.25);
                                    border-color: #2563eb;
                                }
                                
                                article:hover::before {
                                    opacity: 1;
                                }
                                
                                article:hover h3 a {
                                    color: #2563eb;
                                }
                                
                                article:hover a[style*="color: #2563eb"] {
                                    background: #2563eb;
                                    color: white;
                                    transform: translateX(4px);
                                }
                                
                                article:hover a[style*="color: #2563eb"] i {
                                    transform: translateX(4px);
                                }
                                
                                article:hover img {
                                    transform: scale(1.08);
                                }
                            </style>
                        </article>
            <?php
                    }
                }
            } catch (Exception $e) {
                echo '<div style="grid-column: 1 / -1; text-align: center; padding: 40px 20px;">
                        <div style="font-size: 32px; color: #f87171; margin-bottom: 16px;">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <p style="font-family: \'Inter\', sans-serif; font-weight: 300; font-size: 16px; color: #64748b;">
                            Unable to load blog posts at the moment. Please try again later.
                        </p>
                      </div>';
            }
            ?>
        </div>

        <?php if (!empty($latest_posts)): ?>
            <!-- CTA Section -->
            <div style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border-radius: 20px; padding: 60px 40px; text-align: center; box-shadow: 0 20px 40px rgba(37, 99, 235, 0.2); margin-top: 20px;">
                <h3 style="font-family: 'Inter', sans-serif; font-weight: 400; font-size: 28px; color: white; margin: 0 0 16px;">
                    Want More Insights?
                </h3>
                <p style="font-family: 'Inter', sans-serif; font-weight: 300; font-size: 18px; color: rgba(255, 255, 255, 0.9); margin-bottom: 32px; max-width: 600px; margin-left: auto; margin-right: auto; line-height: 1.6;">
                    Explore our complete collection of articles on technology, networking, and digital solutions.
                </p>
                <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                    <a href="blog/index.php" 
                       style="font-family: 'Inter', sans-serif; font-weight: 400; font-size: 16px; color: #2563eb; background: white; padding: 16px 32px; border-radius: 12px; text-decoration: none; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);">
                        <i class="fas fa-newspaper"></i>
                        View All Posts
                    </a>
                    <a href="blog/index.php" 
                       style="font-family: 'Inter', sans-serif; font-weight: 400; font-size: 16px; color: white; background: rgba(255, 255, 255, 0.1); padding: 16px 32px; border-radius: 12px; text-decoration: none; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 12px; border: 2px solid rgba(255, 255, 255, 0.3);">
                        <i class="fas fa-rss"></i>
                        Subscribe
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Add Font Awesome if not already included -->
    <script>
        if (!document.querySelector('link[href*="font-awesome"]')) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
            document.head.appendChild(link);
        }
        
        // Add hover effects
        document.addEventListener('DOMContentLoaded', function() {
            const articles = document.querySelectorAll('#latest-blog article');
            
            articles.forEach(article => {
                article.addEventListener('mouseenter', function() {
                    this.style.zIndex = '10';
                });
                
                article.addEventListener('mouseleave', function() {
                    this.style.zIndex = '';
                });
            });
            
            // Lazy load images
            const images = document.querySelectorAll('#latest-blog img');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.getAttribute('src');
                        observer.unobserve(img);
                    }
                });
            }, { rootMargin: '100px 0px' });
            
            images.forEach(img => imageObserver.observe(img));
        });
    </script>
    
    <!-- Add Inter font if not already included -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    </style>
</section>


<section id="about" class="section about-section">
    <div class="container">
        <h2 class="about-main-title">About Fusion IT Solution</h2>

        <div class="about-grid">
            <div class="mission-column">
                <p class="about-text">
                    <b>Fusion IT Solution is an IT solutions provider in the Philippines, empowering
                        businesses with over 15 years of expertise in:</b>
                </p>
                <ul class="about-list">
                    <li>IT Network Solutions: From basic troubleshooting to complex network infrastructure design, implementation, and management.</li>
                    <li>Software Development: Custom software tailored to your business needs, including telemedicine platforms, joborder management systems, and more.</li>
                    <li>Surveillance and Security Technologies: Comprehensive CCTV and intercom solutions for enhanced security and peace of mind.</li>
                    <li>System Services: Set up servers, implement business systems, and develop websites or applications.</li>
                    <li>IT Products: Equip your business with reliable computer units, CCTV cameras, Wi-Fi solutions, and other essential hardware.</li>
                </ul>
            </div>

            <div class="why-column">
                <h3 class="about-subtitle">Core Values</h3>
                <ul class="why-list">
                    <li><strong>Honesty:</strong> Building trust through transparent communication and ethical practices.</li>
                    <li><strong>Reliability:</strong> Delivering solutions that consistently meet or exceed expectations.</li>
                    <li><strong>Affordability:</strong> Providing value-driven solutions that align with your budget.</li>
                </ul>
            </div>
        </div>

        <div class="vision-section">
            <h3 class="about-subtitle">Our Journey:</h3>
            <p class="about-text2 vision-text">
                Founded in 2006, Fusion IT Solution has grown from offering technical support and accounting software in Quezon City to serving clients across
                Luzon, Visayas, and Mindanao. Milestones include:<br>
                • Expanding operations: Reaching Cebu City, Davao City, and Laoag City.<br>
                • Establishing leadership: Recognized as a leader in CCTV security solutions in Southern Luzon.<br>
                • Serving global brands: Becoming a third-party supplier for a multinational diagnostic imaging leader.
            </p>
        </div>
        <div class="vision-section">
            <h3 class="about-subtitle">Our Vision and Commitment</h3>
            <p class="about-text vision-text">
                Our vision is to become a nationally recognized IT solutions provider, reaching more clients and becoming their go-to partner for all their IT needs.
            </p>
            <p class="about-text" style="margin-top: 20px; font-weight: 500;">
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



</section>

<section id="contact" class="section contact-section">
    <div class="container">
        <h2 class="contact-main-title">Get in Touch with Fusion IT</h2>

        <div class="contact-grid">

            <div class="contact-info">
                <p style="font-size: 1.1rem; color: #555; margin-bottom: 40px;">
                    Ready to start your project or need immediate IT support? Contact us today.
                </p>

                <div class="contact-item">
                    <div class="contact-icon">✉</div>
                    <div class="contact-details">
                        <strong>Email Address</strong>
                        <a href="mailto:admin@fusionitsolution.com">admin@fusionitsolution.com</a>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon">✆</div>
                    <div class="contact-details">
                        <strong>Phone Number</strong>
                        <a href="tel:+639183114656">+0918 311 4656</a>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon">⌂</div>
                    <div class="contact-details">
                        <strong>Office Location</strong>
                        <span class="spam">0354 Calitcalit, San Juan, Philippines</span>
                    </div>
                </div>

            </div>

            <div class="contact-map">
                <div class="map-placeholder">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3682.531920068295!2d121.39772347485733!3d13.82549498657407!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33bd3974baffa653%3A0xd23f9e8f1e18b597!2sFusion%20I.T.%20Solutions!5e1!3m2!1sen!2sph!4v1765165343291!5m2!1sen!2sph" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>

        </div>

        <div class="contact-cta">
            <a href="contact.php" class="contact-btn">
                Send us a Message
            </a>
        </div>


    </div>
</section>

<?php include 'includes/footer.php'; ?>