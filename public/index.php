<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Vital Drop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/about.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/contact.css">
    <link rel="stylesheet" href="../assets/css/howitworks.css">
</head>

<body>

    <!-- LOADING SCREEN (plays BEFORE anything else) -->
    <div id="loading-screen">
        <video id="loading-video" autoplay muted playsinline>
            <source src="../videos/loading.mp4" type="video/mp4">
        </video>
    </div>

    <!-- PAGE CONTENT (hidden until loading finishes) -->
    <div id="page-content">

        <?php include '../includes/navbar.php'; ?>

        <!-- Main Section -->
        <section id="index" class="section-container">
            <section class="main">
                <div class="left">
                    <span class="section-subtitle">Empowering Humanity</span>
                    <h1 class="section-header">Vital Drop</h1>
                    <h2 style="font-size: 24px; color: #af0000; font-weight: 500; margin-bottom: 30px;">where every drop counts</h2>
                    <p>
                        Join our community of life-savers.<br>
                        Connect with donors, organize campaigns, and make a difference when it matters most.<br>
                        Your contribution can save lives.
                    </p>

                    <div style="margin-top: 40px; display: flex; gap: 15px;">
                        <a href="../auth/login.php" class="button">JOIN US</a>
                        <a href="#about" class="btn-secondary">Learn More</a>
                    </div>

                    <!-- AESTHETIC REPLACEMENT: COMPATIBILITY SPOTLIGHT -->
                    <div class="compatibility-spotlight" style="margin-top: 60px; max-width: 600px;">
                        <h3 style="font-size: 16px; color: #af0000; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px; font-weight: 800;">
                            Did you know?
                        </h3>
                        <div style="display: flex; gap: 20px; background: rgba(175, 0, 0, 0.05); border: 1px solid rgba(175, 0, 0, 0.15); padding: 25px; border-radius: 20px; backdrop-filter: blur(10px);">
                            <div style="flex: 1;">
                                <span style="display: block; font-size: 32px; font-weight: 900; color: #af0000; line-height: 1;">O-</span>
                                <span style="font-size: 13px; color: #888; font-weight: 600;">Universal Donor</span>
                            </div>
                            <div style="flex: 2; border-left: 1px solid rgba(175, 0, 0, 0.2); padding-left: 20px;">
                                <p style="font-size: 15px; color: inherit; line-height: 1.5; margin: 0;">
                                    Type <strong style="color: #af0000;">O Negative</strong> can be given to patients of any blood type. It's often the first thing used in emergencies.
                                </p>
                            </div>
                        </div>
                        <div style="margin-top: 15px; display: flex; align-items: center; gap: 10px;">
                            <iconify-icon icon="solar:info-circle-bold" style="color: #af0000; font-size: 18px;"></iconify-icon>
                            <span style="font-size: 13px; color: #666;">Every 2 seconds, someone in the world needs blood.</span>
                        </div>
                    </div>

                    <!-- QUICK STATS -->
                    <div class="hero-stats" style="display: flex; gap: 30px; margin-top: 50px;">
                        <div class="h-stat">
                            <span class="h-stat-val" style="font-size: 24px; font-weight: 800; color: #af0000; display: block;">2.5k+</span>
                            <span class="h-stat-lab" style="font-size: 14px; color: #888;">Active Donors</span>
                        </div>
                        <div class="h-stat">
                            <span class="h-stat-val" style="font-size: 24px; font-weight: 800; color: #af0000; display: block;">15+</span>
                            <span class="h-stat-lab" style="font-size: 14px; color: #888;">Cities Covered</span>
                        </div>
                        <div class="h-stat">
                            <span class="h-stat-val" style="font-size: 24px; font-weight: 800; color: #af0000; display: block;">500+</span>
                            <span class="h-stat-lab" style="font-size: 14px; color: #888;">Lives Saved</span>
                        </div>
                    </div>
                </div>

                <div class="right">
                    <div class="hero-video-container" style="position: relative;">
                        <video autoplay muted loop playsinline width="100%" style="border-radius: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.5);">
                            <source src="../videos/home.mp4" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <!-- FLOATING EMERGENCY ALERT -->
                        <div class="floating-alert" style="position: absolute; bottom: 30px; left: -20px; background: #fff; padding: 15px 20px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); display: flex; align-items: center; gap: 12px; border-left: 4px solid #af0000; animation: bounce 2s infinite; z-index: 10;">
                            <div style="width: 10px; height: 10px; background: #af0000; border-radius: 50%; animation: pulse 1s infinite;"></div>
                            <div>
                                <span style="display: block; font-size: 11px; font-weight: bold; color: #af0000; text-transform: uppercase; font-family: Inter;">Urgent Request</span>
                                <span style="display: block; font-size: 14px; font-weight: 800; color: #222; font-family: Inter;">B+ Needed @ City Hosp.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section>

        <!-- How It Works Section -->
        <?php include 'about.php'; ?>

        <!-- How It Works Section -->
        <?php include 'howitworks.php'; ?>

        <!-- Contact Section -->
        <?php include 'contact.php'; ?>

    </div> <!-- End of page-content -->
    <?php include '../includes/footor.php'; ?>

    <script src="../assets/js/animations.js"></script>
</body>

</html>