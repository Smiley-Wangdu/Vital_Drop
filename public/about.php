<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us — Vital Drop</title>
    <meta name="description" content="Learn about Vital Drop — connecting life-savers with those in need through fast, secure, and transparent blood donation management.">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/about.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>

    <?php include '../includes/navbar.php'; ?>

    <!-- Hero Section -->
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

    <!-- About Us Section -->
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

    <script src="../js/animations.js"></script>
</body>
</html>
