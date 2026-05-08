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
    <div class="page-content">

        <?php include '../includes/navbar.php'; ?>

        <!-- Main Section -->
        <section id="index">
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
                        <source src="../videos/home.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
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