<?php
session_start();
$loggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholar Inn</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="LandingStyle.css">
    <style>
        .contact-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            padding: 72px 40px;
            max-width: 1180px;
            margin: 0 auto;
            width: 100%;
        }

        .contact-staff {
            background: rgba(20, 20, 20, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 28px;
            padding: 32px;
            display: grid;
            gap: 20px;
            box-shadow: 0 18px 50px rgba(0, 0, 0, 0.35);
            transition: transform 0.25s ease, background 0.25s ease;
            text-align: center;
            backdrop-filter: blur(12px);
        }

        .contact-staff:hover {
            transform: translateY(-6px);
            background: rgba(20, 20, 20, 0.75);
        }

        .contact-staff img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            object-position: top center;
            border-radius: 16px;
            margin: 0 auto;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .contact-info {
            display: grid;
            gap: 12px;
        }

        .staff-name {
            font-size: 1.35rem;
            color: #ffffff;
            font-weight: 700;
            margin: 0;
        }

        .staff-phone {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: #f9f6ef;
            font-size: 1rem;
            font-weight: 600;
        }

        .staff-phone i {
            color: #f3e6ce;
            font-size: 1.1rem;
        }

        .contact-header {
            grid-column: 1 / -1;
            text-align: center;
            margin-bottom: 24px;
            background: rgba(20, 20, 20, 0.65);
            padding: 32px;
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(12px);
        }

        .contact-header h2 {
            margin: 0;
            font-size: 2.2rem;
            color: #ffffff;
            margin-bottom: 12px;
            font-weight: 700;
        }

        .contact-header p {
            color: #f9f6ef;
            font-size: 1.05rem;
            margin: 0;
            font-weight: 500;
        }

        @media (max-width: 900px) {
            .contact-section {
                grid-template-columns: 1fr 1fr;
                padding: 48px 24px;
            }

            .contact-header {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 600px) {
            .contact-section {
                grid-template-columns: 1fr;
            }
        }

        .location-selector {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(247, 244, 238, 0.85);
            font-size: 0.95rem;
        }

        .hero-location-selector {
            margin-left: auto;
            display: grid;
            grid-template-columns: 1fr;
            align-items: start;
            gap: 10px;
            background: linear-gradient(180deg, rgba(255,255,255,0.22), rgba(255,255,255,0.10));
            border: 1px solid rgba(255, 255, 255, 0.34);
            padding: 18px 22px;
            border-radius: 24px;
            color: #ffffff;
            min-width: 240px;
            box-shadow: 0 24px 50px rgba(0, 0, 0, 0.22);
            backdrop-filter: blur(14px);
            position: relative;
            overflow: hidden;
        }

        .hero-location-selector::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, rgba(243,230,206,0.9), rgba(212,184,111,0.9));
        }

        .location-label {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            color: #ffffff;
            font-size: 0.92rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-top: 6px;
            z-index: 1;
        }

        .location-label i {
            color: #f3e6ce;
            font-size: 1rem;
            width: 18px;
            text-align: center;
        }

        .location-select {
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.44);
            background: rgba(255, 255, 255, 0.96);
            color: #111111;
            padding: 12px 16px;
            font-size: 1rem;
            min-width: 100%;
            box-shadow: inset 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            z-index: 1;
        }

        .location-select:hover,
        .location-select:focus {
            border-color: rgba(0, 0, 0, 0.16);
            box-shadow: inset 0 4px 14px rgba(0, 0, 0, 0.12);
        }

        .location-select:focus {
            outline: none;
            border-color: rgba(243, 230, 206, 0.6);
            background: rgba(255, 255, 255, 0.18);
        }

        .hero-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 18px;
        }

        .hero-row h1 {
            flex: 1 1 0;
            min-width: 0;
        }

        .hero-layout {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 26px;
            position: relative;
        }

        .hero-copy {
            flex: 1 1 auto;
            min-width: 0;
            max-width: 700px;
        }

        .hero-visual-card {
            flex: 0 0 380px;
            width: 380px;
            margin-right: 4px;
            background: rgba(12, 12, 12, 0.44);
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 28px;
            padding: 12px;
            box-shadow: 0 18px 42px rgba(0, 0, 0, 0.28);
            backdrop-filter: blur(12px);
            align-self: flex-start;
        }

        .hero-visual-card img {
            width: 100%;
            height: 260px;
            object-fit: cover;
            border-radius: 18px;
            display: block;
        }

        .hero-visual-caption {
            display: block;
            color: #f7f4ee;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .hero-location-selector {
            margin-left: 0;
            width: 100%;
            min-width: 0;
            padding: 12px 14px;
            border-radius: 18px;
        }

        @media (max-width: 1100px) {
            .hero-layout {
                flex-direction: column;
            }

            .hero-visual-card {
                width: 100%;
                max-width: 460px;
            }
        }

        @media (max-width: 900px) {
            .hero-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .hero-location-selector {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <div class="landing-page">
        <div class="hero-overlay"></div>

        <header class="landing-header">
            <div class="branding">
                <img src="utm logo.png" alt="UTM Logo" class="utm-logo">
                <div class="logo" id="brandLogo">Scholar Inn JB</div>
            </div>
            <nav class="landing-nav">
                <a href="#about">About</a>
                <a href="#contact">Contact</a>
                <a href="<?php echo $loggedIn ? 'view_rooms.php' : 'login.php'; ?>">Rooms</a>
                <a href="<?php echo $loggedIn ? 'rating_review.php' : 'login.php'; ?>">Review</a>
            </nav>
            <div class="header-menu">
                <a href="<?php echo $loggedIn ? '#' : 'login.php'; ?>" class="button login-button"><?php echo $loggedIn ? 'Menu' : 'Login'; ?></a>
                <div class="menu-dropdown">
                    <?php if($loggedIn): ?>
                        <a class="menu-item" href="customer_home.php">Customer Dashboard</a>
                        <a class="menu-item logout-link" href="logout.php">Sign Out</a>
                    <?php else: ?>
                        <a class="menu-item" href="login.php">Login</a>
                        <a class="menu-item" href="register.php">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <main class="hero-content">
            <div class="hero-layout">
                <div class="hero-copy">
                    <p class="eyebrow">Luxury stay, refined comfort</p>
                    <h1>Experience Scholar Inn with warm hospitality and elegant design.</h1>
                    <p class="hero-text">Discover beautiful rooms, authentic local service, and a serene escape from city life. Start your journey with a short introduction before moving into your account.</p>
                    <div class="hero-actions">
                        <a href="<?php echo $loggedIn ? 'view_rooms.php' : 'login.php'; ?>" class="button primary-button"><?php echo $loggedIn ? 'Start Booking' : 'Login to Book'; ?></a>
                        <a href="#about" class="button secondary-button">Learn More</a>
                    </div>
                </div>

                <aside class="hero-visual-card" aria-label="Location preview image">
                    <img id="location-preview" src="Scholar inn image out side.jpg" alt="Scholar Inn preview image">
                    <span class="hero-visual-caption">Current location view</span>
                    <div class="location-selector hero-location-selector">
                        <div class="location-label">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Location</span>
                        </div>
                        <select id="location-select" class="location-select" onchange="updateLocation()">
                            <option value="JB">JB</option>
                            <option value="KL">KL</option>
                        </select>
                    </div>
                </aside>
            </div>
        </main>

        <section class="info-panel" id="about">
            <div class="info-card">
                <span class="info-title">About Us</span>
                <p>Scholar Inn blends comfort and style with personalized service for every guest. We offer a peaceful stay with easy access to local attractions, cozy rooms, and curated hospitality.</p>
            </div>
            <div class="info-card" id="rooms">
                <span class="info-title">Our Rooms</span>
                <p>Choose from spacious accommodations, modern amenities, and elegant decor that reflects the charm of Scholar Inn. Ideal for business travelers, families, and couples.</p>
            </div>
            <div class="info-card">
                <span class="info-title">Join Us</span>
                <p>Ready to explore? Click login to sign in or register, then book your stay from the customer dashboard.</p>
            </div>
        </section>

        <section class="contact-section" id="contact">
            <div class="contact-header">
                <h2>Our Staff</h2>
                <p>Reach out to our dedicated team for any inquiries or assistance</p>
            </div>
            <article class="contact-staff">
                <img src="uploads/Chong.jpg" alt="Chong">
                <div class="contact-info">
                    <h3 class="staff-name">Chong</h3>
                    <div class="staff-phone">
                        <i class="fas fa-phone"></i>
                        <span>0125645828</span>
                    </div>
                </div>
            </article>
            <article class="contact-staff">
                <img src="uploads/Mirul.jpg" alt="Mirul">
                <div class="contact-info">
                    <h3 class="staff-name">Mirul</h3>
                    <div class="staff-phone">
                        <i class="fas fa-phone"></i>
                        <span>60 11-5169 5650</span>
                    </div>
                </div>
            </article>
        </section>
    </div>
    <script>
        function applyLocation(locationValue) {
            const brandLogo = document.getElementById('brandLogo');
            const landingPage = document.querySelector('.landing-page');
            const previewImage = document.getElementById('location-preview');
            const imageUrl = locationValue === 'KL' ? 'SCHOLAR INN KL.webp' : 'Scholar inn image out side.jpg';
            brandLogo.textContent = 'Scholar Inn ' + locationValue;
            landingPage.style.backgroundImage = `url('${imageUrl}')`;
            if (previewImage) {
                previewImage.src = imageUrl;
                previewImage.alt = 'Scholar Inn ' + locationValue + ' preview image';
            }
        }

        function updateLocation() {
            const select = document.getElementById('location-select');
            const newLocation = select.value;
            applyLocation(newLocation);
            
            // Save to database if user is logged in
            if (<?php echo $loggedIn ? 'true' : 'false'; ?>) {
                fetch('save_location.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'location=' + encodeURIComponent(newLocation)
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            let locationValue = 'JB';
            
            // If user is logged in, try to get their saved location
            <?php if($loggedIn): ?>
                fetch('get_user_location.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.location) {
                            locationValue = data.location;
                            document.getElementById('location-select').value = locationValue;
                            applyLocation(locationValue);
                        }
                    });
            <?php else: ?>
                document.getElementById('location-select').value = locationValue;
                applyLocation(locationValue);
            <?php endif; ?>
        });
    </script>
</body>
</html>
