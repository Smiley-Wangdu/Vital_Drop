<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vital Drop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../css/about.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/contact.css">
    <link rel="stylesheet" href="../css/howitworks.css">
    <style>
        /* Loading Screen - inline so it loads instantly */
        #loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: #000;
            z-index: 99999;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        #loading-screen.fade-out {
            animation: loaderFadeOut 1s ease forwards;
        }
        #loading-screen.hidden {
            display: none !important;
        }
        #loading-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        #page-content {
            opacity: 0;
            transition: opacity 0.8s ease-in-out;
        }
        #page-content.visible {
            opacity: 1;
        }
        @keyframes loaderFadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    </style>
</head>
<body>

<!-- ===== LOADING SCREEN (plays BEFORE anything else) ===== -->
<div id="loading-screen">
    <video id="loading-video" autoplay muted playsinline>
        <source src="../video/loading.mp4" type="video/mp4">
    </video>
</div>

<!-- ===== PAGE CONTENT (hidden until loading finishes) ===== -->
<div id="page-content">

    <?php include '../includes/navbar.php'; ?>

    <!-- Main Section -->
    <section class="main">
        <div class="left">
            <h1>VITAL DROP</h1>
            <h2>where every drop counts</h2>
            <p>
                Join our community of life-savers.<br>
                Connect with donors, organize campaigns, and make a difference when it matters most.<br>
                Your contribution can save lives.
            </p>

            <a href="../auth/login.php" class="button">JOIN US</a>
        </div>

        <div class="right">
            <video autoplay muted loop playsinline width="100%">
                <source src="../video/home.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
    </section>

    <!-- About Us Section (Scrollable) -->
    <div id="about-us">
        <!-- Hero Section -->
        <section class="about-hero" style="padding-top: 60px; min-height: auto;">
            <div class="about-hero-content">
                <div class="about-hero-left" data-animate="fade-right" data-delay="0">
                    <h2 class="about-title" style="font-size: 52px;">ABOUT US</h2>
                    <p class="about-subtitle">our story, our mission, our impact</p>
                    <p class="about-description">
                        We are a community-driven platform
                        dedicated to bridging the gap between
                        blood donors and those in critical need.
                        Every connection we facilitate is a life
                        potentially saved.
                    </p>
                </div>
                <div class="about-hero-right" data-animate="fade-left" data-delay="200">
                    <div class="about-hero-img-wrapper">
                        <img src="../images/hero-illustration.png" alt="Blood donation illustration">
                    </div>
                </div>
            </div>
        </section>

        <!-- Info Cards -->
        <section class="about-cards-section">
            <div class="about-cards-grid">
                <div class="about-card" data-animate="fade-up" data-delay="0">
                    <div class="about-card-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="about-card-title">Our Mission</h3>
                    <p class="about-card-text">
                        Connecting donors with those in need,
                        creating a seamless bridge between life-
                        savers and lives to be saved.
                    </p>
                </div>
                <div class="about-card" data-animate="fade-up" data-delay="200">
                    <div class="about-card-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="about-card-title">Our Vision</h3>
                    <p class="about-card-text">
                        A world where no one suffers due to blood
                        shortage. Every request met, every life saved.
                    </p>
                </div>
                <div class="about-card" data-animate="fade-up" data-delay="400">
                    <div class="about-card-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="about-card-title">Our Impact</h3>
                    <p class="about-card-text">
                        Over 50,000 lives saved through our platform.
                        Join thousands of donors making a difference
                        every single day.
                    </p>
                </div>
            </div>
        </section>

        <!-- Our Story Section -->
        <section class="about-story-section">
            <div class="about-story-content">
                <h2 class="about-story-title" data-animate="fade-up" data-delay="0">Our Story</h2>
                <p class="about-story-text" data-animate="fade-up" data-delay="100">
                    Founded with a simple belief — that no life should be lost due to a shortage of blood.
                    VitalDrop started as a small initiative and has grown into a community of thousands of
                    donors and recipients connected through our platform. We leverage technology to make
                    blood donation more accessible, efficient, and life-saving.
                </p>
            </div>
            <div class="about-gallery" data-animate="fade-up" data-delay="200">
                <div class="about-gallery-item">
                    <img src="../images/about1.png" alt="Blood donation camp">
                </div>
                <div class="about-gallery-item">
                    <img src="../images/about2.png" alt="Blood processing">
                </div>
                <div class="about-gallery-item">
                    <img src="../images/about3.png" alt="Blood bank storage">
                </div>
            </div>
        </section>
    </div>

    <!-- How It Works Section -->
    <?php include 'howitworks.php'; ?>
    
    <!-- Contact Section -->
    <?php include 'contact.php'; ?>

</div> <!-- End of page-content -->

<script src="../js/animations.js"></script>
</body>
</html>