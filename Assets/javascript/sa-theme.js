/**
 * Thème admin clair / sombre — script autonome (head), indépendant de superAdmin.ui.js
 */
(function () {
    'use strict';

    var KEY = 'tcf_superadmin_theme_v2';

    function readTheme() {
        try {
            var saved = localStorage.getItem(KEY);
            if (saved === 'dark' || saved === 'light') {
                return saved;
            }
            if (localStorage.getItem('tcf_superadmin_theme') === 'dark') {
                return 'dark';
            }
        } catch (e) {}
        return 'light';
    }

    function applyTheme(theme) {
        theme = theme === 'dark' ? 'dark' : 'light';
        var html = document.documentElement;
        html.setAttribute('data-sa-theme', theme);
        html.style.colorScheme = theme;

        if (document.body) {
            document.body.setAttribute('data-sa-theme', theme);
            document.body.classList.remove('sa-theme-light', 'sa-theme-dark');
            document.body.classList.add(theme === 'dark' ? 'sa-theme-dark' : 'sa-theme-light');
        }

        try {
            localStorage.setItem(KEY, theme);
        } catch (e2) {}

        var meta = document.getElementById('tcf-theme-color-meta');
        if (meta) {
            meta.setAttribute('content', theme === 'dark' ? '#12141c' : '#ffffff');
        }

        var toggle = document.getElementById('sa-theme-toggle');
        if (toggle) {
            var isDark = theme === 'dark';
            toggle.setAttribute('aria-checked', isDark ? 'true' : 'false');
            toggle.setAttribute('aria-label', isDark ? 'Passer au thème clair' : 'Passer au thème sombre');
            toggle.setAttribute('title', isDark ? 'Thème clair' : 'Thème sombre');
            var label = toggle.querySelector('.sa-theme-switch__label');
            if (label) {
                label.textContent = isDark ? 'Sombre' : 'Clair';
            }
            var thumbIcon = toggle.querySelector('.sa-theme-switch__thumb i');
            if (thumbIcon) {
                thumbIcon.className = isDark ? 'bx bx-moon' : 'bx bx-sun';
            }
        }

        if (typeof Chart !== 'undefined') {
            Chart.defaults.color = theme === 'dark' ? '#eef0f4' : '#141622';
            Chart.defaults.borderColor = theme === 'dark' ? 'rgba(255,255,255,0.12)' : 'rgba(20,22,34,0.08)';
        }

        try {
            window.dispatchEvent(new CustomEvent('tcf-sa-theme-change', { detail: { theme: theme } }));
        } catch (e3) {}
    }

    function wireToggle() {
        var toggle = document.getElementById('sa-theme-toggle');
        if (!toggle || toggle.getAttribute('data-sa-theme-wired') === '1') {
            return;
        }
        toggle.setAttribute('data-sa-theme-wired', '1');
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            var cur = document.documentElement.getAttribute('data-sa-theme') || 'light';
            applyTheme(cur === 'dark' ? 'light' : 'dark');
        });
    }

    applyTheme(readTheme());
    window.__tcfSaApplyTheme = applyTheme;
    window.__tcfSaReadTheme = readTheme;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', wireToggle);
    } else {
        wireToggle();
    }
})();
