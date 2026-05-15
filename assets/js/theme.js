/**
 * VitalDrop — Theme System (theme.js)
 * ============================================================
 * Light = default | Dark = opt-in
 * Persisted via localStorage across all pages.
 *
 * Icons: Iconify (solar icon set) — no emojis, no inline SVG.
 *   Light mode → shows moon icon  (click to go dark)
 *   Dark  mode → shows sun  icon  (click to go light)
 *
 * Applied to body via class:
 *   Public / user pages : body.light-mode | body.dark-mode
 *   Admin pages         : body.light       | body.dark
 *
 * FOUC prevention: theme class is applied synchronously as soon
 * as this script is encountered (before DOMContentLoaded).
 * ============================================================
 */

(function () {
    'use strict';

    var STORAGE_KEY = 'vitaldrop_theme';

    /* Iconify icon names */
    var ICON_MOON = 'solar:moon-bold';   /* shown in light mode → click to go dark  */
    var ICON_SUN  = 'solar:sun-bold';    /* shown in dark  mode → click to go light */

    /* ── Get saved theme (light is the default) ── */
    function getSavedTheme() {
        return localStorage.getItem(STORAGE_KEY) || 'light';
    }

    /* ── Apply theme class to <body> ── */
    function applyTheme(theme) {
        var body = document.body;
        if (!body) return;

        if (body.classList.contains('admin-body')) {
            /* Admin pages use 'light' / 'dark' classes */
            if (theme === 'light') {
                body.classList.add('light');
                body.classList.remove('dark');
            } else {
                body.classList.add('dark');
                body.classList.remove('light');
            }
        } else {
            /* Public & user pages use 'light-mode' / 'dark-mode' classes */
            if (theme === 'light') {
                body.classList.add('light-mode');
                body.classList.remove('dark-mode');
            } else {
                body.classList.add('dark-mode');
                body.classList.remove('light-mode');
            }
        }

        localStorage.setItem(STORAGE_KEY, theme);
        updateAllToggles(theme);
    }

    /* ── Update every .vd-theme-toggle button's icon and label ── */
    function updateAllToggles(theme) {
        var isLight  = theme === 'light';
        var icon     = isLight ? ICON_MOON : ICON_SUN;
        var label    = isLight ? 'Switch to Dark Mode' : 'Switch to Light Mode';

        document.querySelectorAll('.vd-theme-toggle').forEach(function (btn) {
            /* Update the iconify-icon element inside the button */
            var iconEl = btn.querySelector('iconify-icon');
            if (iconEl) {
                iconEl.setAttribute('icon', icon);
            } else {
                /* Fallback: create the iconify-icon element if missing */
                if (btn.tagName === 'A' || btn.tagName === 'LI') {
                    btn.innerHTML = '<iconify-icon icon="' + icon + '" width="22" height="22" style="vertical-align: middle; margin-right: 8px;"></iconify-icon> Theme';
                } else {
                    btn.innerHTML = '<iconify-icon icon="' + icon + '" width="22" height="22"></iconify-icon>';
                }
            }
            btn.setAttribute('aria-label', label);
            btn.setAttribute('title', label);
        });
    }

    /* ── Toggle between light and dark ── */
    function toggleTheme() {
        var next = getSavedTheme() === 'light' ? 'dark' : 'light';
        applyTheme(next);
    }

    /* ── FOUC Prevention ──────────────────────────────────── */
    (function preventFOUC() {
        var body = document.body;
        var saved = getSavedTheme();
        if (!body) return;

        if (body.classList.contains('admin-body')) {
            if (saved === 'light') {
                body.classList.add('light');
                body.classList.remove('dark');
            } else {
                body.classList.add('dark');
                body.classList.remove('light');
            }
        } else {
            if (saved === 'light') {
                body.classList.add('light-mode');
                body.classList.remove('dark-mode');
            } else {
                body.classList.add('dark-mode');
                body.classList.remove('light-mode');
            }
        }
    })();

    /* ── Bind Event Delegation ── */
    document.addEventListener('click', function(e) {
        var toggleBtn = e.target.closest('.vd-theme-toggle');
        if (toggleBtn) {
            e.preventDefault();
            e.stopPropagation();
            toggleTheme();
        }
    });

    /* ── Full init (update icons) once DOM ready ── */
    function init() {
        applyTheme(getSavedTheme());
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
