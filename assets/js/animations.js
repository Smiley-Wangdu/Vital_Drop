/* VITAL DROP — Shared Cinematic Animations
   Loading video (home only, first visit) +
   Scroll reveal animations (all pages) */

(function () {
    'use strict';


    // LOADING SCREEN (only on home page)
    const loadingScreen = document.getElementById('loading-screen');
    const loadingVideo = document.getElementById('loading-video');
    const pageContent = document.getElementById('page-content');

    // Check if this is the first visit using sessionStorage
    const hasSeenLoader = sessionStorage.getItem('vitaldrop_loader_seen');

    if (loadingScreen && loadingVideo) {
        if (hasSeenLoader) {
            // Already seen the loader this session — skip it
            loadingScreen.style.display = 'none';
            if (pageContent) pageContent.classList.add('visible');
            onContentReady();
        } else {
            // First visit — play the video, everything stays hidden
            function hideLoader() {
                if (loadingScreen.classList.contains('fade-out')) return;
                loadingScreen.classList.add('fade-out');
                if (pageContent) pageContent.classList.add('visible');
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
        if (pageContent) pageContent.classList.add('visible');
        onContentReady();
    }

    // ON CONTENT READY — Start animations
    function onContentReady() {
        initScrollAnimations();
        initNavbarScroll();
        initParallax();
        triggerVisibleElements();
    }

    // CINEMATIC SCROLL REVEAL ANIMATIONS
    var observer;

    function initScrollAnimations() {
        var elements = document.querySelectorAll('[data-animate]');
        var containers = document.querySelectorAll('.section-container');
        if (!elements.length && !containers.length) return;
 
        observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var el = entry.target;
                    
                    if (el.classList.contains('section-container')) {
                        el.classList.add('revealed');
                    } else {
                        var delay = parseInt(el.getAttribute('data-delay')) || 0;
                        setTimeout(function () {
                            el.classList.add('animated');
                        }, delay);
                    }
 
                    observer.unobserve(el);
                }
            });
        }, {
            root: null,
            rootMargin: '0px 0px -100px 0px',
            threshold: 0.1
        });
 
        elements.forEach(function (el) { observer.observe(el); });
        containers.forEach(function (el) { observer.observe(el); });
    }

    function triggerVisibleElements() {
        var elements = document.querySelectorAll('[data-animate]:not(.animated)');
        var containers = document.querySelectorAll('.section-container:not(.revealed)');
        
        elements.forEach(function (el) {
            var rect = el.getBoundingClientRect();
            if (rect.top < window.innerHeight && rect.bottom > 0) {
                el.classList.add('animated');
            }
        });

        containers.forEach(function (el) {
            var rect = el.getBoundingClientRect();
            if (rect.top < window.innerHeight && rect.bottom > 0) {
                el.classList.add('revealed');
            }
        });
    }

    // NAVBAR SCROLL SHADOW
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

    // SUBTLE PARALLAX ON IMAGES
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
