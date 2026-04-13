/* ========================================
   VITAL DROP — Shared Cinematic Animations
   Loading video (home only, first visit) +
   Scroll reveal animations (all pages)
   ======================================== */

(function () {
    'use strict';

    // ========================================
    // 0. DARK MODE INITIALIZATION
    // ========================================
    (function initDarkMode() {
        // Apply dark mode immediately based on local storage
        if (localStorage.getItem('vitaldrop_theme') === 'dark') {
            document.body.classList.add('dark-mode');
        }

        var themeToggleBtn = document.getElementById('theme-toggle');
        if (!themeToggleBtn) return;

        // Set initial icon
        if (document.body.classList.contains('dark-mode')) {
            themeToggleBtn.textContent = 'Light';
        } else {
            themeToggleBtn.textContent = 'Dark';
        }

        themeToggleBtn.addEventListener('click', function () {
            document.body.classList.toggle('dark-mode');
            var isDark = document.body.classList.contains('dark-mode');
            
            if (isDark) {
                themeToggleBtn.textContent = 'Light';
                localStorage.setItem('vitaldrop_theme', 'dark');
            } else {
                themeToggleBtn.textContent = 'Dark';
                localStorage.setItem('vitaldrop_theme', 'light');
            }
        });
    })();

    // ========================================
    // 1. LOADING SCREEN (only on home page)
    // ========================================
    const loadingScreen = document.getElementById('loading-screen');
    const loadingVideo = document.getElementById('loading-video');
    const pageContent = document.getElementById('page-content');

    // Check if this is the first visit using sessionStorage
    const hasSeenLoader = sessionStorage.getItem('vitaldrop_loader_seen');

    if (loadingScreen && loadingVideo) {
        if (hasSeenLoader) {
            // Already seen the loader this session — skip it
            loadingScreen.style.display = 'none';
            if (pageContent) pageContent.style.opacity = '1';
            onContentReady();
        } else {
            // First visit — play the video
            function hideLoader() {
                if (loadingScreen.classList.contains('fade-out')) return;
                loadingScreen.classList.add('fade-out');
                if (pageContent) pageContent.style.opacity = '1';
                sessionStorage.setItem('vitaldrop_loader_seen', 'true');

                setTimeout(function () {
                    loadingScreen.classList.add('hidden');
                    onContentReady();
                }, 1000);
            }

            loadingVideo.addEventListener('ended', hideLoader);
            loadingVideo.addEventListener('error', function () {
                setTimeout(hideLoader, 300);
            });

            // Safety: max 10 seconds
            setTimeout(function () {
                if (!loadingScreen.classList.contains('fade-out')) {
                    hideLoader();
                }
            }, 10000);
        }
    } else {
        // No loading screen on this page (e.g., about.php)
        if (pageContent) pageContent.style.opacity = '1';
        onContentReady();
    }

    // ========================================
    // 2. ON CONTENT READY — Start animations
    // ========================================
    function onContentReady() {
        initScrollAnimations();
        initNavbarScroll();
        initParallax();
        triggerVisibleElements();
    }

    // ========================================
    // 3. CINEMATIC SCROLL REVEAL ANIMATIONS
    // ========================================
    var observer;

    function initScrollAnimations() {
        var elements = document.querySelectorAll('[data-animate]');
        if (!elements.length) return;

        observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var el = entry.target;
                    var delay = parseInt(el.getAttribute('data-delay')) || 0;

                    setTimeout(function () {
                        el.classList.add('animated');
                    }, delay);

                    observer.unobserve(el);
                }
            });
        }, {
            root: null,
            rootMargin: '0px 0px -50px 0px',
            threshold: 0.12
        });

        elements.forEach(function (el) {
            observer.observe(el);
        });
    }

    function triggerVisibleElements() {
        var elements = document.querySelectorAll('[data-animate]:not(.animated)');
        elements.forEach(function (el) {
            var rect = el.getBoundingClientRect();
            if (rect.top < window.innerHeight && rect.bottom > 0) {
                var delay = parseInt(el.getAttribute('data-delay')) || 0;
                setTimeout(function () {
                    el.classList.add('animated');
                    if (observer) observer.unobserve(el);
                }, delay);
            }
        });
    }

    // ========================================
    // 4. NAVBAR SCROLL SHADOW
    // ========================================
    function initNavbarScroll() {
        var navbar = document.querySelector('.navbar');
        if (!navbar) return;

        window.addEventListener('scroll', function () {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }, { passive: true });
    }

    // ========================================
    // 5. SUBTLE PARALLAX ON IMAGES
    // ========================================
    function initParallax() {
        var heroImg = document.querySelector('.hero-image img') || document.querySelector('.right img');
        if (!heroImg) return;

        var ticking = false;
        window.addEventListener('scroll', function () {
            if (!ticking) {
                requestAnimationFrame(function () {
                    var scrollY = window.scrollY;
                    if (scrollY < window.innerHeight) {
                        heroImg.style.transform = 'translateY(' + (scrollY * 0.08) + 'px)';
                    }
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });
    }

})();
