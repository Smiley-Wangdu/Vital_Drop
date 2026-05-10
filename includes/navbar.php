<?php
/**
 * Vital Drop — Navbar Fragment
 * ============================================================
 * Included at the top of every public page.
 * Contains: logo, nav links, theme toggle button.
 *
 * Theme system:
 *   - theme.js is loaded here (runs immediately to prevent FOUC)
 *   - Iconify CDN provides the sun/moon icons
 *   - .vd-theme-toggle is bound by theme.js automatically
 * ============================================================
 */
?>

<!-- Iconify icon library (for theme toggle sun/moon icons) -->
<script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>

<!-- Theme JS: runs immediately to apply saved theme class before paint -->
<script src="../assets/js/theme.js"></script>

<header class="navbar" role="banner">
    <div class="nav-container">

        <!-- Logo -->
        <div class="logo">
            <img src="../images/logo.png" alt="Vital Drop Logo">
            <p>Vital Drop</p>
        </div>

        <!-- Nav Links + Theme Toggle -->
        <nav class="nav-links" aria-label="Main navigation">
            <a href="../public/index.php#index">Home</a>
            <a href="../public/index.php#about">About Us</a>
            <a href="../public/index.php#howitworks">How it works</a>
            <a href="../public/index.php#contact">Contact</a>
            <a href="../auth/login.php">Login</a>

            <!-- Theme Toggle Button
                 Icon is managed by theme.js — shows moon in light mode, sun in dark mode.
                 .vd-theme-toggle class is the hook theme.js binds its click listener to. -->
            <button
                class="vd-theme-toggle"
                id="nav-theme-toggle"
                aria-label="Switch to Dark Mode"
                title="Switch to Dark Mode"
                type="button"
            >
                <!-- Default: moon icon (light mode is default, moon = switch to dark) -->
                <iconify-icon icon="solar:moon-bold" width="22" height="22"></iconify-icon>
            </button>
        </nav>

    </div>
</header>
