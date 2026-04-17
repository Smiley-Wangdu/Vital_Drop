/* ========================================
   VITAL DROP — ABOUT US PAGE
   Cinematic Scroll Animations & Loader
   ======================================== */

(function () {
    'use strict';

    // ========================================
    // 1. LOADING SCREEN
    // ========================================
    const loadingScreen = document.getElementById('loading-screen');
    const loadingVideo = document.getElementById('loading-video');
    const pageContent = document.getElementById('page-content');

    function hideLoader() {
        if (!loadingScreen) return;
        loadingScreen.classList.add('fade-out');
        if (pageContent) {
            pageContent.style.opacity = '1';
        }
        setTimeout(() => {
            loadingScreen.style.display = 'none';
            // Start observing scroll animations after loader is gone
            initScrollAnimations();
            // Auto-trigger animations for elements already in viewport
            triggerVisibleElements();
        }, 800);
    }

    if (loadingVideo) {
        // When the video ends naturally, hide the loader
        loadingVideo.addEventListener('ended', hideLoader);

        // Fallback: if video fails to load, hide after 2 seconds
        loadingVideo.addEventListener('error', () => {
            setTimeout(hideLoader, 500);
        });

        // Safety maximum timeout — never show loader more than 8 seconds
        setTimeout(() => {
            if (loadingScreen && !loadingScreen.classList.contains('fade-out')) {
                hideLoader();
            }
        }, 8000);
    } else {
        // No video element — show content immediately
        if (loadingScreen) loadingScreen.style.display = 'none';
        if (pageContent) pageContent.style.opacity = '1';
        window.addEventListener('DOMContentLoaded', () => {
            initScrollAnimations();
            triggerVisibleElements();
        });
    }

    // ========================================
    // 2. CINEMATIC SCROLL ANIMATIONS
    // ========================================
    let observer;

    function initScrollAnimations() {
        const animatedElements = document.querySelectorAll('[data-animate]');

        if (!animatedElements.length) return;

        const observerOptions = {
            root: null,
            rootMargin: '0px 0px -60px 0px',
            threshold: 0.15
        };

        observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const delay = parseInt(el.getAttribute('data-delay')) || 0;

                    setTimeout(() => {
                        el.classList.add('animated');
                    }, delay);

                    // Unobserve after animating so it only triggers once
                    observer.unobserve(el);
                }
            });
        }, observerOptions);

        animatedElements.forEach((el) => {
            observer.observe(el);
        });
    }

    function triggerVisibleElements() {
        const animatedElements = document.querySelectorAll('[data-animate]');
        animatedElements.forEach((el) => {
            const rect = el.getBoundingClientRect();
            if (rect.top < window.innerHeight && rect.bottom > 0) {
                const delay = parseInt(el.getAttribute('data-delay')) || 0;
                setTimeout(() => {
                    el.classList.add('animated');
                    if (observer) observer.unobserve(el);
                }, delay);
            }
        });
    }

    // ========================================
    // 3. STATS COUNTER ANIMATION
    // ========================================
    function initStatsCounter() {
        const statNumbers = document.querySelectorAll('.stat-number[data-count]');
        if (!statNumbers.length) return;

        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = parseInt(el.getAttribute('data-count'));
                    animateCounter(el, target);
                    statsObserver.unobserve(el);
                }
            });
        }, { threshold: 0.5 });

        statNumbers.forEach((el) => {
            statsObserver.observe(el);
        });
    }

    function animateCounter(element, target) {
        const duration = 2000; // 2 seconds
        const startTime = performance.now();
        const startValue = 0;

        function easeOutExpo(t) {
            return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
        }

        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easedProgress = easeOutExpo(progress);
            const currentValue = Math.floor(startValue + (target - startValue) * easedProgress);

            element.textContent = currentValue.toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                element.textContent = target.toLocaleString();
            }
        }

        requestAnimationFrame(update);
    }

    // ========================================
    // 4. SMOOTH PARALLAX ON HERO IMAGE
    // ========================================
    function initParallax() {
        const heroImage = document.querySelector('.hero-image img');
        if (!heroImage) return;

        let ticking = false;

        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(() => {
                    const scrollY = window.scrollY;
                    const heroSection = document.querySelector('.about-hero');
                    if (!heroSection) return;

                    const heroRect = heroSection.getBoundingClientRect();
                    // Only apply parallax when hero is visible
                    if (heroRect.bottom > 0 && heroRect.top < window.innerHeight) {
                        const parallaxOffset = scrollY * 0.15;
                        heroImage.style.transform = `translateY(${parallaxOffset}px)`;
                    }

                    ticking = false;
                });
                ticking = true;
            }
        });
    }

    // ========================================
    // 5. NAVBAR SCROLL EFFECT (shadow on scroll)
    // ========================================
    function initNavbarScroll() {
        const navbar = document.querySelector('.navbar');
        if (!navbar) return;

        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.style.boxShadow = '0 4px 30px rgba(0, 0, 0, 0.08)';
            } else {
                navbar.style.boxShadow = 'none';
            }
        });
    }

    // ========================================
    // 6. SMOOTH ANCHOR SCROLLING
    // ========================================
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
            anchor.addEventListener('click', (e) => {
                const targetId = anchor.getAttribute('href');
                if (targetId === '#') return;

                const targetEl = document.querySelector(targetId);
                if (targetEl) {
                    e.preventDefault();
                    targetEl.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    // ========================================
    // INITIALIZE EVERYTHING
    // ========================================
    // These init immediately (don't depend on loader)
    initStatsCounter();
    initParallax();
    initNavbarScroll();
    initSmoothScroll();

})();
