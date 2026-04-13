<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vital Drop — Where Every Drop Counts</title>
    <meta name="description" content="Vital Drop — A safe place for blood donors and recipients. Fast, secure, and transparent blood donation management.">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/about.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Loading Screen (only on home page, first visit) -->
    <div id="loading-screen">
        <video id="loading-video" autoplay muted playsinline>
            <source src="../video/loading.mp4" type="video/mp4">
        </video>
    </div>

    <!-- Page Content -->
    <div id="page-content" style="opacity: 0;">

        <?php include '../includes/navbar.php'; ?>

        <!-- Main Section -->
        <section class="main">
            <div class="left">
                <h1 data-animate="fade-right" data-delay="0">VITAL DROP</h1>
                <h2 data-animate="fade-right" data-delay="150">where every drop counts</h2>

                <h3 data-animate="fade-up" data-delay="300">Together we are stronger</h3>
                <p data-animate="fade-up" data-delay="450">
                    A safe place for people willing to save and be saved. <br>
                    Each drop is vital.
                </p>

                <a href="register.php" class="button" data-animate="fade-up" data-delay="600">JOIN US</a>
            </div>

            <div class="right" data-animate="fade-left" data-delay="200">
                <img src="../images/home.jpg" alt="blood image">
            </div>
        </section>

        <!-- About Us Section (scroll target) -->
        <div id="about-us">
            <section class="about-hero">
                <div class="hero-content">
                    <div class="hero-text" data-animate="fade-right" data-delay="0">
                        <p class="hero-description">
                            Connecting life-savers with those
                            in need. Fast, secure, and transparent
                            blood donation management. A bridge
                            between the receivers and the donors.
                        </p>
                    </div>
                    <div class="hero-image" data-animate="fade-left" data-delay="200">
                        <img src="../images/hero-illustration.png" alt="Blood donation illustration">
                    </div>
                </div>
            </section>

            <section class="about-section">
                <h2 class="section-title" data-animate="fade-up" data-delay="0">About Us</h2>
                <div class="about-gallery">
                    <div class="gallery-item" data-animate="fade-up" data-delay="0">
                        <div class="gallery-img-wrapper">
                            <img src="../images/about1.png" alt="Blood donation camp">
                        </div>
                    </div>
                    <div class="gallery-item" data-animate="fade-up" data-delay="200">
                        <div class="gallery-img-wrapper">
                            <img src="../images/about2.png" alt="Blood processing">
                        </div>
                    </div>
                    <div class="gallery-item" data-animate="fade-up" data-delay="400">
                        <div class="gallery-img-wrapper">
                            <img src="../images/about3.png" alt="Blood bank storage">
                        </div>
                    </div>
                </div>
            </section>
        </div>

    </div>

    <script src="../js/animations.js"></script>
</body>
</html>