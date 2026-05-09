/* VITAL DROP — Unified Theme System
   Light = default | Dark = toggle
   Persisted via localStorage, shared across all pages */

(function () {
    'use strict';

    var STORAGE_KEY = 'vitaldrop_theme';

    // SVG Icons (not emoji)
    var SUN_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>';

    var MOON_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';

    /* Determine current theme — light is default */
    function getSavedTheme() {
        return localStorage.getItem(STORAGE_KEY) || 'light';
    }

    /* Apply theme to body */
    function applyTheme(theme) {
        var body = document.body;

        // Public pages use 'light-mode' / 'dark-mode' classes
        // Admin pages use 'light' / 'dark' classes on .admin-body

        if (body.classList.contains('admin-body')) {
            // Admin pages
            body.classList.toggle('light', theme === 'light');
            body.classList.toggle('dark', theme === 'dark');
        } else {
            // Public / user pages
            body.classList.toggle('light-mode', theme === 'light');
            body.classList.toggle('dark-mode', theme === 'dark');
        }

        localStorage.setItem(STORAGE_KEY, theme);

        // Update all toggle button icons on the page
        updateAllIcons(theme);
    }

    /* Update toggle button icons */
    function updateAllIcons(theme) {
        var toggleBtns = document.querySelectorAll('.vd-theme-toggle');
        toggleBtns.forEach(function (btn) {
            // In light mode, show moon (click to go dark)
            // In dark mode, show sun (click to go light)
            btn.innerHTML = theme === 'light' ? MOON_ICON : SUN_ICON;
            btn.setAttribute('title', theme === 'light' ? 'Switch to Dark Mode' : 'Switch to Light Mode');
            btn.setAttribute('aria-label', theme === 'light' ? 'Switch to Dark Mode' : 'Switch to Light Mode');
        });
    }

    /* Toggle theme */
    function toggleTheme() {
        var current = getSavedTheme();
        var next = current === 'light' ? 'dark' : 'light';
        applyTheme(next);
    }

    /* Initialize on DOMContentLoaded */
    function init() {
        // Apply saved theme immediately
        applyTheme(getSavedTheme());

        // Bind click events to all toggle buttons
        var toggleBtns = document.querySelectorAll('.vd-theme-toggle');
        toggleBtns.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                toggleTheme();
            });
        });
    }

    // Apply theme BEFORE DOMContentLoaded to prevent flash
    // (This runs immediately when script is loaded)
    var savedTheme = getSavedTheme();
    var body = document.body;
    if (body) {
        if (body.classList.contains('admin-body')) {
            body.classList.toggle('light', savedTheme === 'light');
            body.classList.toggle('dark', savedTheme === 'dark');
        } else {
            body.classList.toggle('light-mode', savedTheme === 'light');
            body.classList.toggle('dark-mode', savedTheme === 'dark');
        }
    }

    // Full init once DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
