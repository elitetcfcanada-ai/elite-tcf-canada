/**
 * Navigation mobile admin — barre du bas, sidebar desktop, sous-menus déroulants
 */
(function () {
    'use strict';

    var MOBILE_MQ = window.matchMedia('(max-width: 900px)');

    var VIDEO_GROUP = ['videos', 'analytics'];
    var SUB_GROUP = ['subscription-plans', 'subscription-payments', 'subscription-revenue'];

    var SUBMENU_CONFIG = {
        topics: {
            title: 'Gestion des sujets',
            submenuId: 'topics-submenu'
        },
        videos: {
            title: 'Gestion Vidéos',
            submenuId: 'videos-submenu'
        },
        subscriptions: {
            title: 'Abonnements',
            submenuId: 'subscription-submenu'
        }
    };

    var SECTION_TITLES = {
        dashboard: 'Tableau de bord',
        'recent-activity': 'Activité récente',
        users: 'Gestion utilisateurs',
        admins: 'Membres administrateurs',
        videos: 'Gestion vidéos',
        analytics: 'Analyse vidéo',
        testimonials: 'Témoignages',
        'subscription-plans': 'Forfaits',
        'subscription-payments': 'Historique paiements',
        'subscription-revenue': 'Revenus & budget',
        messages: 'Annonces communautaires',
        'topics-section': 'Gestion des sujets',
        'topics-written': 'Compréhension écrite',
        'topics-oral': 'Compréhension orale',
        'topics-expression': 'Expression écrite',
        'topics-speaking': 'Expression orale'
    };

    var TOPIC_SECTIONS = ['topics-written', 'topics-oral', 'topics-expression', 'topics-speaking'];
    var openSubmenuKey = null;

    function $(sel, root) {
        return (root || document).querySelector(sel);
    }

    function $all(sel, root) {
        return Array.prototype.slice.call((root || document).querySelectorAll(sel));
    }

    function isMobile() {
        return MOBILE_MQ.matches;
    }

    function openSidebar() {
        closeSubnavSheet();
        var sidebar = $('.tcf-superadmin-app .sidebar');
        var backdrop = $('#saSidebarBackdrop');
        if (!sidebar) return;
        sidebar.classList.add('is-mobile-open');
        if (backdrop) backdrop.classList.add('is-visible');
        document.body.classList.add('tcf-sa-sidebar-open');
    }

    function closeSidebar() {
        var sidebar = $('.tcf-superadmin-app .sidebar');
        var backdrop = $('#saSidebarBackdrop');
        if (sidebar) sidebar.classList.remove('is-mobile-open');
        if (backdrop) backdrop.classList.remove('is-visible');
        document.body.classList.remove('tcf-sa-sidebar-open');
    }

    function toggleSidebar() {
        var sidebar = $('.tcf-superadmin-app .sidebar');
        if (sidebar && sidebar.classList.contains('is-mobile-open')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }

    function closeSubnavSheet() {
        var sheet = $('#saSubnavSheet');
        var overlay = $('#saSubnavSheetOverlay');
        if (sheet) sheet.classList.remove('is-open');
        if (overlay) overlay.classList.remove('is-visible');
        document.body.classList.remove('tcf-sa-sheet-open');
        openSubmenuKey = null;
        $all('.tcf-sa-mobile-nav__item[data-sa-submenu]').forEach(function (btn) {
            btn.classList.remove('is-submenu-open');
        });
    }

    function openSubnavSheet(key) {
        var cfg = SUBMENU_CONFIG[key];
        if (!cfg) return;

        if (openSubmenuKey === key && $('#saSubnavSheet') && $('#saSubnavSheet').classList.contains('is-open')) {
            closeSubnavSheet();
            return;
        }

        closeSidebar();

        var submenu = document.getElementById(cfg.submenuId);
        var body = $('#saSubnavSheetBody');
        var titleEl = $('#saSubnavSheetTitle');
        if (!submenu || !body) return;

        if (titleEl) titleEl.textContent = cfg.title;
        body.innerHTML = '';

        $all('.sub-item', submenu).forEach(function (item) {
            var target = item.getAttribute('data-target');
            if (!target) return;
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'tcf-sa-subnav-sheet__link';
            btn.setAttribute('data-sa-target', target);
            btn.textContent = item.textContent.trim();
            body.appendChild(btn);
        });

        $all('[data-sa-target]', body).forEach(function (btn) {
            btn.addEventListener('click', function () {
                navigateTo(btn.getAttribute('data-sa-target'));
            });
        });

        var sheet = $('#saSubnavSheet');
        var overlay = $('#saSubnavSheetOverlay');
        if (sheet) sheet.classList.add('is-open');
        if (overlay) overlay.classList.add('is-visible');
        document.body.classList.add('tcf-sa-sheet-open');
        openSubmenuKey = key;

        $all('.tcf-sa-mobile-nav__item[data-sa-submenu]').forEach(function (btn) {
            btn.classList.toggle('is-submenu-open', btn.getAttribute('data-sa-submenu') === key);
        });
    }

    function navigateTo(target) {
        if (!target) return;
        if (typeof window.tcfSaNavigate === 'function') {
            window.tcfSaNavigate(target);
        } else {
            var el = document.querySelector('.menu-item[data-target="' + target + '"], .sub-item[data-target="' + target + '"]');
            if (el) el.click();
        }
        closeSidebar();
        closeSubnavSheet();
    }

    function bottomNavKey(sectionId, topicsTarget) {
        if (sectionId === 'dashboard' || sectionId === 'recent-activity') return 'home';
        if (TOPIC_SECTIONS.indexOf(sectionId) >= 0) return 'epreuves';
        if (sectionId === 'topics-section' && topicsTarget && TOPIC_SECTIONS.indexOf(topicsTarget) >= 0) {
            return 'epreuves';
        }
        if (VIDEO_GROUP.indexOf(sectionId) >= 0) return 'videos';
        if (SUB_GROUP.indexOf(sectionId) >= 0 || sectionId === 'subscription-revenue') return 'subscriptions';
        return 'menu';
    }

    function resolveTitle(sectionId, topicsTarget) {
        if (sectionId === 'topics-section' && topicsTarget && SECTION_TITLES[topicsTarget]) {
            return SECTION_TITLES[topicsTarget];
        }
        return SECTION_TITLES[sectionId] || 'Administration';
    }

    function syncMobileNav(sectionId, topicsTarget) {
        var titleEl = $('#sa-page-title');
        if (titleEl) {
            titleEl.textContent = resolveTitle(sectionId, topicsTarget);
        }

        var key = bottomNavKey(sectionId, topicsTarget);
        $all('.tcf-sa-mobile-nav__item').forEach(function (btn) {
            var k = btn.getAttribute('data-sa-tab');
            btn.classList.toggle('is-active', k === key);
        });
    }

    window.tcfSaMobileNavSync = syncMobileNav;
    window.tcfSaCloseMobileSidebar = closeSidebar;
    window.tcfSaCloseMobileSheet = closeSubnavSheet;

    function init() {
        var body = document.body;
        if (!body || !body.classList.contains('tcf-superadmin-app')) return;

        body.classList.add('tcf-sa-has-mobile-nav');

        var menuBtn = $('#saMobileMenuBtn');
        var sidebarClose = $('#saSidebarClose');
        var backdrop = $('#saSidebarBackdrop');

        if (menuBtn) {
            menuBtn.addEventListener('click', function () {
                if (isMobile()) {
                    toggleSidebar();
                } else if ($('.sidebar.is-mobile-open')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });
        }

        if (sidebarClose) sidebarClose.addEventListener('click', closeSidebar);
        if (backdrop) backdrop.addEventListener('click', closeSidebar);

        $all('[data-sa-open-sidebar]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                closeSubnavSheet();
                toggleSidebar();
            });
        });

        $all('.tcf-sa-mobile-nav__item[data-sa-submenu]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                openSubnavSheet(btn.getAttribute('data-sa-submenu'));
            });
        });

        $all('.tcf-sa-mobile-nav__item[data-sa-target]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                navigateTo(btn.getAttribute('data-sa-target'));
            });
        });

        var sheetOverlay = $('#saSubnavSheetOverlay');
        var sheetClose = $('#saSubnavSheetClose');
        if (sheetOverlay) sheetOverlay.addEventListener('click', closeSubnavSheet);
        if (sheetClose) sheetClose.addEventListener('click', closeSubnavSheet);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeSidebar();
                closeSubnavSheet();
            }
        });

        MOBILE_MQ.addEventListener('change', function () {
            if (!isMobile()) {
                closeSidebar();
                closeSubnavSheet();
            }
        });

        window.addEventListener('resize', function () {
            if (!isMobile()) closeSidebar();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
