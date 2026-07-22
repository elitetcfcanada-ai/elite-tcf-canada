/**
 * SuperAdmin UI (single controller)
 * - Router unique (sections + sous-menu sujets)
 * - Toast unique
 * - Charge/rend uniquement la section active
 *
 * Conserve les actions POST existantes de admin/superAdmin.php.
 */
(function () {
    'use strict';

    var ENDPOINT = 'superAdmin.php';
    var EE_ENDPOINT = '../ee_api.php';
    var EO_ENDPOINT = '../eo_api.php';
    var CE_ENDPOINT = '../ce_api.php';
    var CO_ENDPOINT = '../co_api.php';
    var VIDEOS_API = '../videos_api.php';
    var THEME_KEY = 'tcf_superadmin_theme_v2';

    var TOPIC_SECTIONS = {
        'topics-written': { type: 'Compréhension Écrite', title: 'Gestion des sujets — Compréhension écrite' },
        'topics-oral': { type: 'Compréhension Orale', title: 'Gestion des sujets — Compréhension orale' },
        'topics-expression': { type: 'Expression Écrite', title: 'Gestion des sujets — Expression écrite' },
        'topics-speaking': { type: 'Expression Orale', title: 'Gestion des sujets — Expression orale' }
    };

    var state = {
        active: 'dashboard',
        topicsTarget: 'topics-written',
        playlistCache: [],
        trace: { map: null, charts: {}, lastTraceability: null },
        subscriptionRevChart: null,
        eeConsignesCache: [],
        eoConsignesCache: [],
        /** 'ce' | 'co' lorsque le modal choix JSON/manuel est ouvert */
        quizPublishPending: null,
        /** Si défini, la section Analyse est filtrée sur cette vidéo (depuis une carte). */
        analyticsFocusVideoId: null,
        activityFeedRaw: []
    };

    function $(sel, root) {
        return (root || document).querySelector(sel);
    }
    function $all(sel, root) {
        return [].slice.call((root || document).querySelectorAll(sel));
    }
    function escHtml(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }
    function escAttr(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;');
    }

    /** Durée serveur : masquer zéro / 00:00:00.000 (valeur factice ou inconnue). */
    function tcfAdminDurationMeaningful(d) {
        var s = (d == null ? '' : String(d)).trim();
        if (!s) return false;
        var m = s.match(/^(?:(\d{1,3}):)?(\d{1,2}):(\d{1,2})(\.\d+)?$/);
        if (!m) return true;
        var h = m[1] ? parseInt(m[1], 10) : 0;
        var mi = parseInt(m[2], 10);
        var sec = parseInt(m[3], 10);
        var frac = m[4] ? parseFloat(m[4]) : 0;
        var total = h * 3600 + mi * 60 + sec + frac;
        return total > 0.001;
    }

    function saUserInitials(name) {
        name = name == null ? '' : String(name).trim();
        if (!name) return '?';
        var parts = name.split(/\s+/).filter(function (p) {
            return p.length > 0;
        });
        if (parts.length >= 2) {
            return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
        }
        return name.length >= 2 ? name.substring(0, 2).toUpperCase() : name.charAt(0).toUpperCase();
    }

    function saAvatarCell(avatarUrl, displayName) {
        var ini = escHtml(saUserInitials(displayName));
        if (avatarUrl) {
            return (
                '<td class="sa-user-photo-cell"><span class="sa-user-avatar-wrap"><img src="' +
                escAttr(String(avatarUrl)) +
                '" alt="" class="sa-user-avatar-img" loading="lazy"></span></td>'
            );
        }
        return (
            '<td class="sa-user-photo-cell"><span class="sa-user-avatar-wrap"><span class="sa-user-avatar-ph">' +
            ini +
            '</span></span></td>'
        );
    }

    function saActionsRow(innerHtml) {
        return '<td class="sa-table-actions"><div class="sa-actions-row">' + innerHtml + '</div></td>';
    }

    function toast(message, isError) {
        var toastEl = document.getElementById('notification-toast');
        var text = document.getElementById('notification-text');
        if (!toastEl || !text) {
            window.alert(message);
            return;
        }
        text.textContent = message;
        toastEl.style.background = isError ? '#7f1d1d' : '#14532d';
        toastEl.classList.add('show');
        window.clearTimeout(toast._t);
        toast._t = window.setTimeout(function () {
            toastEl.classList.remove('show');
        }, 3200);
    }
    window.TCF_ADMIN_TOAST = toast;

    function postForm(action, fields) {
        var fd = new FormData();
        fd.append('action', action);
        if (fields) {
            Object.keys(fields).forEach(function (k) {
                if (fields[k] !== undefined && fields[k] !== null) {
                    fd.append(k, fields[k]);
                }
            });
        }
        return fetch(ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) {
            return r.json();
        });
    }

    // Sections regroupées sous « Gestion Vidéos » (sidebar)
    var VIDEO_GROUP_SECTIONS = ['videos', 'analytics'];
    var SUBSCRIPTION_GROUP_SECTIONS = ['subscription-plans', 'subscription-payments', 'subscription-revenue'];
    var SITE_MANAGEMENT_GROUP_SECTIONS = ['admins'];
    var SA_IS_SUPER = typeof window.TCF_SA_IS_SUPER === 'boolean'
        ? window.TCF_SA_IS_SUPER
        : ((document.body && document.body.getAttribute('data-sa-role')) === 'super_admin');
    /* Sections accessibles à un administrateur (hors super_admin) */
    var SA_ADMIN_SECTIONS = {
        dashboard: true,
        videos: true,
        analytics: true,
        messages: true,
        'subscription-revenue': true,
        'topics-section': true,
        'topics-written': true,
        'topics-oral': true,
        'topics-expression': true,
        'topics-speaking': true
    };

    function saCanAccessSection(sectionId) {
        if (SA_IS_SUPER) {
            return true;
        }
        if (TOPIC_SECTIONS[sectionId]) {
            return true;
        }
        return !!SA_ADMIN_SECTIONS[sectionId];
    }

    // ---------------- Router ----------------
    function setActiveSection(sectionId, opts) {
        opts = opts || {};
        if (!sectionId) return;

        if (!saCanAccessSection(sectionId)) {
            sectionId = 'dashboard';
        }

        // Topics deep-link: topics-written etc. => show topics-section + set context
        if (TOPIC_SECTIONS[sectionId]) {
            state.topicsTarget = sectionId;
            sectionId = 'topics-section';
        }

        state.active = sectionId;

        $all('.content-section').forEach(function (sec) {
            var on = sec.id === sectionId;
            sec.classList.toggle('is-active', on);
            sec.style.display = on ? 'block' : 'none';
            sec.setAttribute('aria-hidden', on ? 'false' : 'true');
        });

        $all('.menu-item[data-target]').forEach(function (item) {
            item.classList.toggle('active', item.getAttribute('data-target') === sectionId);
        });

        var topicsMenu = document.getElementById('topics-menu');
        var topicsSub = document.getElementById('topics-submenu');
        var inTopicsGroup = sectionId === 'topics-section';
        if (topicsMenu) {
            topicsMenu.classList.toggle('active', inTopicsGroup);
        }
        if (topicsMenu && topicsSub && inTopicsGroup) {
            topicsMenu.classList.add('is-expanded');
            topicsSub.classList.add('is-open');
        }
        $all('#topics-submenu .sub-item').forEach(function (si) {
            var target = inTopicsGroup ? (state.topicsTarget || 'topics-written') : sectionId;
            si.classList.toggle('active', si.getAttribute('data-target') === target);
        });

        var inVideoGroup = VIDEO_GROUP_SECTIONS.indexOf(sectionId) >= 0;
        var vm = document.getElementById('videos-menu');
        var vs = document.getElementById('videos-submenu');
        if (vm) {
            vm.classList.toggle('active', inVideoGroup);
        }
        if (vm && vs && inVideoGroup) {
            vm.classList.add('is-expanded');
            vs.classList.add('is-open');
        }
        $all('#videos-submenu .sub-item').forEach(function (si) {
            si.classList.toggle('active', si.getAttribute('data-target') === sectionId);
        });

        var inSubGroup = SUBSCRIPTION_GROUP_SECTIONS.indexOf(sectionId) >= 0;
        var sm = document.getElementById('subscription-menu');
        var ss = document.getElementById('subscription-submenu');
        if (sm) {
            sm.classList.toggle('active', inSubGroup);
        }
        if (sm && ss && inSubGroup) {
            sm.classList.add('is-expanded');
            ss.classList.add('is-open');
        }
        $all('#subscription-submenu .sub-item').forEach(function (si) {
            si.classList.toggle('active', si.getAttribute('data-target') === sectionId);
        });

        var inSiteMgmt = SITE_MANAGEMENT_GROUP_SECTIONS.indexOf(sectionId) >= 0;
        var siteMm = document.getElementById('site-management-menu');
        var siteMs = document.getElementById('site-management-submenu');
        if (siteMm) {
            siteMm.classList.toggle('active', inSiteMgmt);
        }
        if (siteMm && siteMs && inSiteMgmt) {
            siteMm.classList.add('is-expanded');
            siteMs.classList.add('is-open');
        }
        $all('#site-management-submenu .sub-item').forEach(function (si) {
            si.classList.toggle('active', si.getAttribute('data-target') === sectionId);
        });

        if (!opts.skipHash) {
            try {
                var h = opts.hash || (sectionId === 'topics-section' ? (state.topicsTarget || 'topics-written') : sectionId);
                if (h) window.location.hash = '#' + encodeURIComponent(h);
            } catch (e) {}
        }

        if (typeof window.tcfSaMobileNavSync === 'function') {
            window.tcfSaMobileNavSync(sectionId, state.topicsTarget || null);
        }

        if (typeof window.tcfSaCloseMobileSidebar === 'function') {
            window.tcfSaCloseMobileSidebar();
        }
        if (typeof window.tcfSaCloseMobileSheet === 'function') {
            window.tcfSaCloseMobileSheet();
        }

        onEnterSection(sectionId);
    }

    window.tcfSaNavigate = function (sectionId) {
        setActiveSection(sectionId);
    };

    function initRouter() {
        // Sidebar main items
        $all('.menu-item[data-target]').forEach(function (el) {
            el.addEventListener('click', function () {
                var t = el.getAttribute('data-target');
                if (t) setActiveSection(t);
            });
        });
        // Topics sub items
        $all('.sub-item[data-target]').forEach(function (el) {
            el.addEventListener('click', function () {
                var t = el.getAttribute('data-target');
                if (!t) return;
                setActiveSection(t);
            });
        });

        // Init from hash
        var initial = null;
        try {
            initial = (window.location.hash || '').replace(/^#/, '');
            initial = decodeURIComponent(initial || '');
        } catch (e) {
            initial = null;
        }
        if (initial) {
            if (!document.getElementById(initial) && !TOPIC_SECTIONS[initial]) {
                initial = 'dashboard';
            }
            setActiveSection(initial, { skipHash: true });
        } else {
            setActiveSection('dashboard', { skipHash: true });
        }

    }

    // ---------------- Theme ----------------
    function applySaTheme(theme) {
        if (typeof window.__tcfSaApplyTheme === 'function') {
            window.__tcfSaApplyTheme(theme);
            return;
        }
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
            localStorage.setItem(THEME_KEY, theme);
        } catch (e) {}
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
            Chart.defaults.color = chartTextColor();
            Chart.defaults.borderColor = theme === 'dark' ? 'rgba(255,255,255,0.12)' : 'rgba(20,22,34,0.08)';
        }
        try {
            window.dispatchEvent(new CustomEvent('tcf-sa-theme-change', { detail: { theme: theme } }));
        } catch (e2) {}
    }

    function readSavedSaTheme() {
        var theme = 'light';
        try {
            var saved = localStorage.getItem(THEME_KEY);
            if (saved === 'dark' || saved === 'light') {
                theme = saved;
            } else if (localStorage.getItem('tcf_superadmin_theme') === 'dark') {
                theme = 'dark';
            } else if (localStorage.getItem('tcf_superadmin_theme') === 'light') {
                theme = 'light';
            }
        } catch (e) {}
        return theme;
    }

    function initTheme() {
        if (!document.body || !document.body.classList.contains('tcf-superadmin-app')) {
            return;
        }
        if (typeof window.__tcfSaApplyTheme === 'function') {
            window.__tcfSaApplyTheme(readSavedSaTheme());
            return;
        }
        applySaTheme(readSavedSaTheme());
        var toggle = document.getElementById('sa-theme-toggle');
        if (toggle && toggle.getAttribute('data-sa-theme-wired') !== '1') {
            toggle.setAttribute('data-sa-theme-wired', '1');
            toggle.addEventListener('click', function () {
                var next = document.documentElement.getAttribute('data-sa-theme') === 'dark' ? 'light' : 'dark';
                applySaTheme(next);
            });
        }
    }

    // ---------------- Sections: enter hooks ----------------
    function onEnterSection(sectionId) {
        if (sectionId === 'dashboard') {
            refreshStats();
            loadTraceability();
        }
        if (sectionId === 'recent-activity') {
            loadActivityFeed();
        }
        if (sectionId === 'users') reloadUsers();
        if (sectionId === 'admins') reloadAdmins();
        if (sectionId === 'messages') reloadMessages();
        if (sectionId === 'analytics') initAnalytics();
        if (sectionId === 'topics-section') {
            setTopicContext(state.topicsTarget || 'topics-written');
        }
        if (sectionId === 'videos') {
            ensureVideosBoot();
            fetchVideosFromServer(function (err, data) {
                if (!err && data) renderVideosGrid(data);
            });
        }
        if (sectionId === 'testimonials') {
            ensureTestimonialsBoot();
            loadTestimonialsAdmin();
        }
        if (sectionId === 'subscription-plans') {
            initSubscriptionPlansToolbarOnce();
            loadSubscriptionPlansAdmin();
        }
        if (sectionId === 'subscription-payments') {
            loadSubscriptionPaymentsAdmin();
        }
        if (sectionId === 'subscription-revenue') {
            loadSubscriptionRevenueStatsAdmin();
        }
        if (sectionId === 'notifications' || sectionId === 'messages') {
            // handled by initNotifications + reloadMessages
        }
    }

    // ---------------- Topics ----------------
    function initTopicsMenu() {
        var menu = document.getElementById('topics-menu');
        var sub = document.getElementById('topics-submenu');
        if (menu && sub) {
            menu.addEventListener('click', function (e) {
                e.stopPropagation();
                menu.classList.toggle('is-expanded');
                sub.classList.toggle('is-open');
            });
        }
    }

    function initVideosMenu() {
        var menu = document.getElementById('videos-menu');
        var sub = document.getElementById('videos-submenu');
        if (menu && sub) {
            menu.addEventListener('click', function (e) {
                e.stopPropagation();
                menu.classList.toggle('is-expanded');
                sub.classList.toggle('is-open');
            });
        }
    }

    function initSubscriptionMenu() {
        var menu = document.getElementById('subscription-menu');
        var sub = document.getElementById('subscription-submenu');
        if (menu && sub) {
            menu.addEventListener('click', function (e) {
                e.stopPropagation();
                menu.classList.toggle('is-expanded');
                sub.classList.toggle('is-open');
            });
        }
    }

    function initSiteManagementMenu() {
        /* Ancien sous-menu « Membres » retiré : entrée directe data-target="admins". */
    }

    var subscriptionPlansToolbarBound = false;
    function initSubscriptionPlansToolbarOnce() {
        if (subscriptionPlansToolbarBound) {
            return;
        }
        subscriptionPlansToolbarBound = true;
        var toggleBtn = document.getElementById('sa-sub-platform-toggle-btn');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', toggleSubscriptionsPlatformMode);
        }
        var btn = document.getElementById('sa-sub-add-plan-btn');
        if (!btn) {
            return;
        }
        btn.addEventListener('click', function () {
            postForm('create_subscription_plan', {})
                .then(function (j) {
                    if (j && j.success) {
                        toast(j.message || 'Forfait ajouté');
                        loadSubscriptionPlansAdmin();
                    } else {
                        toast((j && j.message) || 'Erreur', true);
                    }
                })
                .catch(function () {
                    toast('Erreur réseau', true);
                });
        });
    }

    function setTopicContext(targetId) {
        var cfg = TOPIC_SECTIONS[targetId];
        if (!cfg) return;
        state.topicsTarget = targetId;
        var titleEl = document.getElementById('topics-section-title');
        var typeInput = document.getElementById('topic-type');
        var legacyForm = document.getElementById('topic-form');
        var eeManager = document.getElementById('ee-admin-manager');
        var eoManager = document.getElementById('eo-admin-manager');
        var ceManager = document.getElementById('ce-admin-manager');
        var coManager = document.getElementById('co-admin-manager');
        var addBtn = document.getElementById('add-topic-btn');
        var topSaveBtn = document.getElementById('topic-save-top-btn');
        var topCancelBtn = document.getElementById('topic-cancel-top-btn');
        if (titleEl) titleEl.textContent = cfg.title;
        if (typeInput) typeInput.value = cfg.type;
        if (topSaveBtn) topSaveBtn.style.display = 'none';
        if (topCancelBtn) topCancelBtn.style.display = 'none';
        if (cfg.type === 'Expression Écrite') {
            if (legacyForm) legacyForm.style.display = 'none';
            if (eeManager) eeManager.style.display = 'block';
            if (eoManager) eoManager.style.display = 'none';
            if (ceManager) ceManager.style.display = 'none';
            if (coManager) coManager.style.display = 'none';
            if (addBtn) addBtn.innerHTML = "<i class='bx bx-plus'></i> Ajouter une épreuve";
            loadEeExamsTable();
            loadEeConsignesTable();
        } else if (cfg.type === 'Expression Orale') {
            if (legacyForm) legacyForm.style.display = 'none';
            if (eeManager) eeManager.style.display = 'none';
            if (eoManager) eoManager.style.display = 'block';
            if (ceManager) ceManager.style.display = 'none';
            if (coManager) coManager.style.display = 'none';
            if (addBtn) addBtn.innerHTML = "<i class='bx bx-plus'></i> Ajouter une épreuve";
            loadEoExamsTable();
            loadEoConsignesTable();
        } else if (cfg.type === 'Compréhension Écrite') {
            if (legacyForm) legacyForm.style.display = 'none';
            if (eeManager) eeManager.style.display = 'none';
            if (eoManager) eoManager.style.display = 'none';
            if (ceManager) ceManager.style.display = 'block';
            if (coManager) coManager.style.display = 'none';
            if (addBtn) addBtn.innerHTML = "<i class='bx bx-plus'></i> Ajouter une épreuve";
            loadCeExamsTable();
        } else if (cfg.type === 'Compréhension Orale') {
            if (legacyForm) legacyForm.style.display = 'none';
            if (eeManager) eeManager.style.display = 'none';
            if (eoManager) eoManager.style.display = 'none';
            if (ceManager) ceManager.style.display = 'none';
            if (coManager) coManager.style.display = 'block';
            if (addBtn) addBtn.innerHTML = "<i class='bx bx-plus'></i> Ajouter une épreuve";
            loadCoExamsTable();
        } else {
            if (eeManager) eeManager.style.display = 'none';
            if (eoManager) eoManager.style.display = 'none';
            if (ceManager) ceManager.style.display = 'none';
            if (coManager) coManager.style.display = 'none';
            if (addBtn) addBtn.innerHTML = "<i class='bx bx-plus'></i> Ajouter un sujet";
            loadTopicsTable(cfg.type);
        }
        updateTopicTopActions();
    }

    function updateTopicTopActions() {
        var topSaveBtn = document.getElementById('topic-save-top-btn');
        var topCancelBtn = document.getElementById('topic-cancel-top-btn');
        var addBtn = document.getElementById('add-topic-btn');
        if (!topSaveBtn || !topCancelBtn || !addBtn) return;
        var cfg = TOPIC_SECTIONS[state.topicsTarget || 'topics-written'];
        var isEe = cfg && cfg.type === 'Expression Écrite';
        var isEo = cfg && cfg.type === 'Expression Orale';
        var isCe = cfg && cfg.type === 'Compréhension Écrite';
        var isCo = cfg && cfg.type === 'Compréhension Orale';
        var eeForm = document.getElementById('ee-exam-form');
        var eoForm = document.getElementById('eo-exam-form');
        var ceForm = document.getElementById('ce-exam-form');
        var coForm = document.getElementById('co-exam-form');
        var ceJsonForm = document.getElementById('ce-exam-json-form');
        var coJsonForm = document.getElementById('co-exam-json-form');
        var formOpen =
            (isEe && eeForm && eeForm.style.display !== 'none') ||
            (isEo && eoForm && eoForm.style.display !== 'none') ||
            (isCe && ceForm && ceForm.style.display !== 'none') ||
            (isCo && coForm && coForm.style.display !== 'none') ||
            (isCe && ceJsonForm && ceJsonForm.style.display !== 'none') ||
            (isCo && coJsonForm && coJsonForm.style.display !== 'none');
        topSaveBtn.style.display = formOpen ? 'inline-flex' : 'none';
        topCancelBtn.style.display = formOpen ? 'inline-flex' : 'none';
        addBtn.innerHTML =
            isEe || isEo || isCe || isCo
                ? "<i class='bx bx-plus'></i> Ajouter une épreuve"
                : "<i class='bx bx-plus'></i> Ajouter un sujet";
    }

    function loadTopicsTable(type) {
        postForm('get_topics', { type: type })
            .then(function (j) {
                if (!j || !j.success || !j.data) return;
                renderTopicsTable(j.data);
            })
            .catch(function () {
                toast('Erreur chargement sujets', true);
            });
    }

    function renderTopicsTable(rows) {
        var tb = document.querySelector('#topics-table tbody');
        if (!tb) return;
        if (!rows || !rows.length) {
            tb.innerHTML = '<tr><td colspan="6" style="padding:12px;color:var(--sa-muted);">Aucun sujet.</td></tr>';
            return;
        }
        tb.innerHTML = rows
            .map(function (t) {
                return (
                    '<tr data-id="' +
                    escAttr(String(t.id)) +
                    '"><td>' +
                    escHtml(t.title) +
                    '</td><td>' +
                    escHtml(t.type) +
                    '</td><td><span class="sa-badge sa-badge--' +
                    (t.visibility === 'premium' ? 'premium' : 'gratuit') +
                    '">' +
                    escHtml(t.visibility) +
                    '</span></td><td>' +
                    escHtml(t.created_at) +
                    '</td><td>' +
                    escHtml(String(t.uses != null ? t.uses : 0)) +
                    '</td>' +
                    saActionsRow(
                        '<button type="button" class="btn btn-outline btn-sm sa-btn-icon sa-topic-edit" aria-label="Modifier"><i class="bx bx-edit-alt" aria-hidden="true"></i></button><button type="button" class="btn btn-outline btn-sm sa-btn-icon btn-danger-outline sa-topic-del" aria-label="Supprimer"><i class="bx bx-trash" aria-hidden="true"></i></button>'
                    ) +
                    '</tr>'
                );
            })
            .join('');
    }

    function initTopicsForms() {
        var addBtn = document.getElementById('add-topic-btn');
        var cancelBtn = document.getElementById('cancel-topic-btn');
        var form = document.getElementById('topic-form');
        if (addBtn && form) {
            addBtn.addEventListener('click', function () {
                var currentCfg = TOPIC_SECTIONS[state.topicsTarget || 'topics-written'];
                if (currentCfg && currentCfg.type === 'Expression Écrite') {
                    openEeExamForm();
                    return;
                }
                if (currentCfg && currentCfg.type === 'Expression Orale') {
                    openEoExamForm();
                    return;
                }
                if (currentCfg && currentCfg.type === 'Compréhension Écrite') {
                    openQuizPublishMethodModal('ce');
                    return;
                }
                if (currentCfg && currentCfg.type === 'Compréhension Orale') {
                    openQuizPublishMethodModal('co');
                    return;
                }
                document.getElementById('topic-edit-id').value = '';
                document.getElementById('topic-title').value = '';
                var jl = document.getElementById('json-file-label');
                if (jl) jl.textContent = 'Fichier optionnel';
                form.style.display = 'block';
            });
        }
        if (cancelBtn && form) {
            cancelBtn.addEventListener('click', function () {
                form.style.display = 'none';
            });
        }
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var fd = new FormData(form);
                var editId = document.getElementById('topic-edit-id').value;
                fd.append('action', editId ? 'update_topic' : 'add_topic');
                if (editId) fd.append('id', editId);
                fd.append('type', document.getElementById('topic-type').value);
                fd.append('title', document.getElementById('topic-title').value);
                fd.append('visibility', document.getElementById('topic-visibility').value);
                fetch(ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) {
                        return r.json();
                    })
                    .then(function (j) {
                        if (j && j.success) {
                            toast(j.message || 'Enregistré');
                            form.style.display = 'none';
                            setTopicContext(state.topicsTarget);
                        } else {
                            toast((j && j.message) || 'Erreur', true);
                        }
                    })
                    .catch(function () {
                        toast('Erreur réseau', true);
                    });
            });
        }
        document.body.addEventListener('click', function (e) {
            var tr = e.target.closest && e.target.closest('#topics-table tr[data-id]');
            if (!tr) return;
            var currentCfg = TOPIC_SECTIONS[state.topicsTarget || 'topics-written'];
            if (currentCfg && currentCfg.type === 'Expression Écrite') {
                if (e.target.closest('.sa-topic-edit')) {
                    var eeId = tr.getAttribute('data-id');
                    if (eeId) openEeExamForm(eeId);
                }
                if (e.target.closest('.sa-topic-del')) {
                    var eeId2 = tr.getAttribute('data-id');
                    if (!eeId2 || !window.confirm("Supprimer cette épreuve d'expression écrite ?")) return;
                    var fd = new FormData();
                    fd.append('action', 'delete_exam');
                    fd.append('exam_id', eeId2);
                    fetch(EE_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
                        .then(function (r) { return r.json(); })
                        .then(function (j) {
                            if (j && j.success) {
                                toast(j.message || 'Épreuve supprimée');
                                loadEeExamsTable();
                            } else {
                                toast((j && j.message) || 'Erreur', true);
                            }
                        })
                        .catch(function () { toast('Erreur réseau', true); });
                }
                return;
            }
            if (currentCfg && currentCfg.type === 'Expression Orale') {
                if (e.target.closest('.sa-topic-edit')) {
                    var eoId = tr.getAttribute('data-id');
                    if (eoId) openEoExamForm(eoId);
                }
                if (e.target.closest('.sa-topic-del')) {
                    var eoId2 = tr.getAttribute('data-id');
                    if (!eoId2 || !window.confirm("Supprimer cette épreuve d'expression orale ?")) return;
                    var fde = new FormData();
                    fde.append('action', 'delete_exam');
                    fde.append('exam_id', eoId2);
                    fetch(EO_ENDPOINT, { method: 'POST', body: fde, credentials: 'same-origin' })
                        .then(function (r) { return r.json(); })
                        .then(function (j) {
                            if (j && j.success) {
                                toast(j.message || 'Épreuve supprimée');
                                loadEoExamsTable();
                            } else {
                                toast((j && j.message) || 'Erreur', true);
                            }
                        })
                        .catch(function () { toast('Erreur réseau', true); });
                }
                return;
            }
            if (currentCfg && currentCfg.type === 'Compréhension Écrite') {
                if (e.target.closest('.sa-topic-edit')) {
                    var ceId = tr.getAttribute('data-id');
                    if (ceId) openCeExamForm(ceId);
                }
                if (e.target.closest('.sa-topic-del')) {
                    var ceId2 = tr.getAttribute('data-id');
                    if (!ceId2 || !window.confirm("Supprimer cette épreuve de compréhension écrite ?")) return;
                    var fdc = new FormData();
                    fdc.append('action', 'delete_exam');
                    fdc.append('exam_id', ceId2);
                    fetch(CE_ENDPOINT, { method: 'POST', body: fdc, credentials: 'same-origin' })
                        .then(function (r) { return r.json(); })
                        .then(function (j) {
                            if (j && j.success) {
                                toast(j.message || 'Épreuve supprimée');
                                loadCeExamsTable();
                            } else {
                                toast((j && j.message) || 'Erreur', true);
                            }
                        })
                        .catch(function () { toast('Erreur réseau', true); });
                }
                return;
            }
            if (currentCfg && currentCfg.type === 'Compréhension Orale') {
                if (e.target.closest('.sa-topic-edit')) {
                    var coid = tr.getAttribute('data-id');
                    if (coid) openCoExamForm(coid);
                }
                if (e.target.closest('.sa-topic-del')) {
                    var coid2 = tr.getAttribute('data-id');
                    if (!coid2 || !window.confirm("Supprimer cette épreuve de compréhension orale ?")) return;
                    var fdco = new FormData();
                    fdco.append('action', 'delete_exam');
                    fdco.append('exam_id', coid2);
                    fetch(CO_ENDPOINT, { method: 'POST', body: fdco, credentials: 'same-origin' })
                        .then(function (r) { return r.json(); })
                        .then(function (j) {
                            if (j && j.success) {
                                toast(j.message || 'Épreuve supprimée');
                                loadCoExamsTable();
                            } else {
                                toast((j && j.message) || 'Erreur', true);
                            }
                        })
                        .catch(function () { toast('Erreur réseau', true); });
                }
                return;
            }
            if (e.target.closest('.sa-topic-edit')) {
                var id = tr.getAttribute('data-id');
                var tds = tr.querySelectorAll('td');
                if (!id || !tds || tds.length < 3) return;
                document.getElementById('topic-edit-id').value = id;
                document.getElementById('topic-title').value = tds[0].textContent || '';
                document.getElementById('topic-type').value = tds[1].textContent || '';
                document.getElementById('topic-visibility').value = (tds[2].textContent || '').trim() || 'gratuit';
                var form = document.getElementById('topic-form');
                if (form) form.style.display = 'block';
            }
            if (e.target.closest('.sa-topic-del')) {
                var id2 = tr.getAttribute('data-id');
                if (!id2 || !window.confirm('Supprimer ce sujet ?')) return;
                postForm('delete_topic', { id: id2 })
                    .then(function (j) {
                        if (j && j.success) {
                            toast(j.message || 'Supprimé');
                            setTopicContext(state.topicsTarget);
                        } else {
                            toast((j && j.message) || 'Erreur', true);
                        }
                    })
                    .catch(function () {
                        toast('Erreur réseau', true);
                    });
            }
        });
        initEeExamFormUi();
        initEeConsignesUi();
        initEoExamFormUi();
        initEoConsignesUi();
        initQuizPublishMethodModal();
        initCeExamFormUi();
        initCeJsonImportUi();
        initCoExamFormUi();
        initCoJsonImportUi();
        initCeConsignesUi();
        initCoConsignesUi();
    }

    function loadEeExamsTable() {
        var fd = new FormData();
        fd.append('action', 'get_exams_admin');
        fetch(EE_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j || !j.success || !j.data) {
                    toast((j && j.message) || 'Erreur chargement épreuves', true);
                    return;
                }
                renderEeExamsTable(j.data);
            })
            .catch(function () {
                toast('Erreur chargement épreuves', true);
            });
    }

    function renderEeExamsTable(rows) {
        var tb = document.querySelector('#topics-table tbody');
        if (!tb) return;
        if (!rows || !rows.length) {
            tb.innerHTML = '<tr><td colspan="6" style="padding:12px;color:var(--sa-muted);">Aucune épreuve.</td></tr>';
            return;
        }
        tb.innerHTML = rows.map(function (e) {
            var effectiveVis = String(e.effective_visibility || e.visibility || 'gratuit');
            var vis = Number(e.is_published || 0) === 1 ? effectiveVis : 'brouillon';
            var uses = Number(e.view_count || 0);
            return '<tr data-id="' + escAttr(String(e.id)) + '">' +
                '<td>' + escHtml(e.title || '') + '</td>' +
                '<td>Expression Écrite</td>' +
                '<td><span class="sa-badge sa-badge--' + (Number(e.is_published || 0) === 1 ? (effectiveVis === 'premium' ? 'premium' : 'gratuit') : 'premium') + '">' + escHtml(vis) + '</span></td>' +
                '<td>' + escHtml(e.published_at || e.created_at || '') + '</td>' +
                '<td>' + escHtml(String(uses)) + '</td>' +
                saActionsRow('<button type="button" class="btn btn-outline btn-sm sa-btn-icon sa-topic-edit" aria-label="Modifier"><i class="bx bx-edit-alt" aria-hidden="true"></i></button><button type="button" class="btn btn-outline btn-sm sa-btn-icon btn-danger-outline sa-topic-del" aria-label="Supprimer"><i class="bx bx-trash" aria-hidden="true"></i></button>') +
                '</tr>';
        }).join('');
    }

    function loadEeConsignesTable() {
        var fd = new FormData();
        fd.append('action', 'get_consignes_bundle_admin');
        fetch(EE_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j || !j.success || !j.data) {
                    toast((j && j.message) || 'Erreur chargement consignes', true);
                    return;
                }
                state.eeConsignesCache = j.data;
                fillEeConsignesBundleForm(j.data);
            })
            .catch(function () {
                toast('Erreur chargement consignes', true);
            });
    }

    function fillEeConsignesBundleForm(data) {
        var f = document.getElementById('ee-consignes-bundle-form');
        if (!f) return;
        var d = data || {};
        var t1 = document.getElementById('ee-consigne-tache1');
        var t2 = document.getElementById('ee-consigne-tache2');
        var t3 = document.getElementById('ee-consigne-tache3');
        var status = document.getElementById('ee-consigne-status');
        if (t1) t1.value = d.tache1 || '';
        if (t2) t2.value = d.tache2 || '';
        if (t3) t3.value = d.tache3 || '';
        if (status) status.value = Number(d.is_published || 0) === 1 ? '1' : '0';
        refreshEeConsigneSubmitLabel();
    }

    function refreshEeConsigneSubmitLabel() {
        var status = document.getElementById('ee-consigne-status');
        var submitBtn = document.getElementById('ee-consigne-submit-btn');
        if (!status || !submitBtn) return;
        submitBtn.textContent = status.value === '1' ? 'Publier' : 'Non publier';
    }

    function initEeConsignesUi() {
        var form = document.getElementById('ee-consignes-bundle-form');
        var openBtn = document.getElementById('ee-open-consignes-btn');
        var cancelBtn = document.getElementById('ee-consigne-cancel-btn');
        var status = document.getElementById('ee-consigne-status');
        if (!form) return;

        if (openBtn) {
            openBtn.addEventListener('click', function () {
                form.style.display = 'block';
                loadEeConsignesTable();
            });
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function () {
                form.style.display = 'none';
            });
        }
        if (status) {
            status.addEventListener('change', refreshEeConsigneSubmitLabel);
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var t1 = (document.getElementById('ee-consigne-tache1').value || '').trim();
            var t2 = (document.getElementById('ee-consigne-tache2').value || '').trim();
            var t3 = (document.getElementById('ee-consigne-tache3').value || '').trim();
            if (!t1 || !t2 || !t3) {
                toast('Veuillez renseigner les consignes des 3 tâches.', true);
                return;
            }
            var fd = new FormData();
            fd.append('action', 'save_consignes_bundle');
            fd.append('tache1', t1);
            fd.append('tache2', t2);
            fd.append('tache3', t3);
            fd.append('is_published', (document.getElementById('ee-consigne-status').value || '1') === '1' ? '1' : '0');
            fetch(EE_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (j) {
                    if (j && j.success) {
                        toast(j.message || 'Consignes enregistrées');
                        loadEeConsignesTable();
                    } else {
                        toast((j && j.message) || 'Erreur', true);
                    }
                })
                .catch(function () { toast('Erreur réseau', true); });
        });
    }

    function eeComboTemplate(n) {
        return '<div class="dashboard-section" data-ee-combo style="margin-top:14px;padding:12px;border:1px solid var(--sa-border);border-radius:12px;">' +
            '<div class="section-header" style="margin-bottom:8px;"><div class="section-title" style="font-size:1rem;">Combinaison <span data-ee-combo-label>' + n + '</span></div><button type="button" class="btn btn-outline btn-sm" data-ee-remove-combo>Retirer</button></div>' +
            '<input type="hidden" data-ee-combo-number value="' + n + '">' +
            eeTaskTemplate(1) + eeTaskTemplate(2) + eeTaskTemplate(3) +
            '</div>';
    }

    function eeTaskTemplate(taskNo) {
        var docs = taskNo === 3
            ? '<div class="form-group"><label class="form-label">Document 1</label><textarea class="form-control" rows="4" data-ee-doc1></textarea></div>' +
              '<div class="form-group"><label class="form-label">Document 2</label><textarea class="form-control" rows="4" data-ee-doc2></textarea></div>'
            : '';
        return '<div style="margin-top:10px;padding:10px;border:1px dashed #cbd5e1;border-radius:10px;">' +
            '<h4 style="margin:0 0 8px;">Tâche ' + taskNo + '</h4>' +
            '<div class="form-group"><label class="form-label">Énoncé</label><textarea class="form-control" rows="4" data-ee-prompt="' + taskNo + '"></textarea></div>' +
            docs +
            '<div class="form-group"><label class="form-label">Correction</label><textarea class="form-control" rows="5" data-ee-correction="' + taskNo + '"></textarea></div>' +
            '<div class="form-group"><label class="form-label">Mots min / max (optionnel)</label><div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;"><input type="number" class="form-control" data-ee-word-min="' + taskNo + '" placeholder="min"><input type="number" class="form-control" data-ee-word-max="' + taskNo + '" placeholder="max"></div></div>' +
            '</div>';
    }

    function resetEeCombos(defaultCount) {
        var wrap = document.getElementById('ee-combos-wrap');
        if (!wrap) return;
        wrap.innerHTML = '';
        var n = defaultCount || 1;
        for (var i = 1; i <= n; i++) {
            wrap.insertAdjacentHTML('beforeend', eeComboTemplate(i));
        }
        refreshEeComboLabels();
    }

    function refreshEeComboLabels() {
        var combos = $all('[data-ee-combo]');
        combos.forEach(function (c, idx) {
            var num = idx + 1;
            var lab = c.querySelector('[data-ee-combo-label]');
            var hidden = c.querySelector('[data-ee-combo-number]');
            if (lab) lab.textContent = String(num);
            if (hidden) hidden.value = String(num);
        });
    }

    function openEeExamForm(examId) {
        var form = document.getElementById('ee-exam-form');
        var mgr = document.getElementById('ee-admin-manager');
        if (!form || !mgr) return;
        form.style.display = 'block';
        updateTopicTopActions();
        document.getElementById('ee-exam-id').value = '';
        document.getElementById('ee-exam-title').value = '';
        document.getElementById('ee-exam-subtitle').value = '';
        document.getElementById('ee-exam-visibility').value = 'gratuit';
        document.getElementById('ee-exam-published').checked = true;
        resetEeCombos(1);
        if (!examId) return;
        var fd = new FormData();
        fd.append('action', 'get_exam_for_edit');
        fd.append('exam_id', examId);
        fetch(EE_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j || !j.success || !j.data) {
                    toast((j && j.message) || 'Erreur chargement épreuve', true);
                    return;
                }
                var e = j.data;
                document.getElementById('ee-exam-id').value = e.id || '';
                document.getElementById('ee-exam-title').value = e.title || '';
                document.getElementById('ee-exam-subtitle').value = e.subtitle || '';
                document.getElementById('ee-exam-visibility').value = (e.visibility === 'premium' ? 'premium' : 'gratuit');
                document.getElementById('ee-exam-published').checked = Number(e.is_published || 0) === 1;
                var combos = Array.isArray(e.combinations) && e.combinations.length ? e.combinations : [{}];
                resetEeCombos(combos.length);
                var comboEls = $all('[data-ee-combo]');
                combos.forEach(function (combo, ci) {
                    var el = comboEls[ci];
                    if (!el) return;
                    var tasks = Array.isArray(combo.tasks) ? combo.tasks : [];
                    tasks.forEach(function (t) {
                        var tn = Number(t.task_number || 0);
                        if (tn < 1 || tn > 3) return;
                        var p = el.querySelector('[data-ee-prompt="' + tn + '"]');
                        var c = el.querySelector('[data-ee-correction="' + tn + '"]');
                        var mn = el.querySelector('[data-ee-word-min="' + tn + '"]');
                        var mx = el.querySelector('[data-ee-word-max="' + tn + '"]');
                        if (p) p.value = t.prompt || '';
                        if (c) c.value = t.correction || '';
                        if (mn) mn.value = t.word_min || '';
                        if (mx) mx.value = t.word_max || '';
                        if (tn === 3) {
                            var d1 = el.querySelector('[data-ee-doc1]');
                            var d2 = el.querySelector('[data-ee-doc2]');
                            var docs = Array.isArray(t.documents) ? t.documents : [];
                            if (d1) d1.value = docs[0] && docs[0].content ? docs[0].content : '';
                            if (d2) d2.value = docs[1] && docs[1].content ? docs[1].content : '';
                        }
                    });
                });
            })
            .catch(function () { toast('Erreur chargement épreuve', true); });
    }

    function collectEeCombinations() {
        var combos = [];
        $all('[data-ee-combo]').forEach(function (el, idx) {
            var comboNumber = idx + 1;
            var tasks = [1, 2, 3].map(function (tn) {
                var promptEl = el.querySelector('[data-ee-prompt="' + tn + '"]');
                var corrEl = el.querySelector('[data-ee-correction="' + tn + '"]');
                var minEl = el.querySelector('[data-ee-word-min="' + tn + '"]');
                var maxEl = el.querySelector('[data-ee-word-max="' + tn + '"]');
                var task = {
                    task_number: tn,
                    prompt: (promptEl && promptEl.value ? promptEl.value : '').trim(),
                    correction: corrEl && corrEl.value ? corrEl.value : '',
                    word_min: minEl && minEl.value ? Number(minEl.value) : null,
                    word_max: maxEl && maxEl.value ? Number(maxEl.value) : null,
                    documents: []
                };
                if (tn === 3) {
                    var d1 = el.querySelector('[data-ee-doc1]');
                    var d2 = el.querySelector('[data-ee-doc2]');
                    if (d1 && d1.value.trim()) task.documents.push({ doc_number: 1, title: 'Document 1', content: d1.value.trim() });
                    if (d2 && d2.value.trim()) task.documents.push({ doc_number: 2, title: 'Document 2', content: d2.value.trim() });
                }
                return task;
            });
            combos.push({ combo_number: comboNumber, sort_order: comboNumber, title: 'Combinaison ' + comboNumber, tasks: tasks });
        });
        return combos;
    }

    function initEeExamFormUi() {
        var form = document.getElementById('ee-exam-form');
        var addBtn = document.getElementById('ee-add-combo-btn');
        var cancelBtn = document.getElementById('ee-cancel-btn');
        var topSaveBtn = document.getElementById('topic-save-top-btn');
        var topCancelBtn = document.getElementById('topic-cancel-top-btn');
        var wrap = document.getElementById('ee-combos-wrap');
        if (!form || !addBtn || !wrap) return;

        addBtn.addEventListener('click', function () {
            wrap.insertAdjacentHTML('beforeend', eeComboTemplate($all('[data-ee-combo]').length + 1));
            refreshEeComboLabels();
        });

        wrap.addEventListener('click', function (e) {
            var rm = e.target.closest('[data-ee-remove-combo]');
            if (!rm) return;
            var combo = e.target.closest('[data-ee-combo]');
            if (!combo) return;
            combo.remove();
            if (!$all('[data-ee-combo]').length) {
                resetEeCombos(1);
            } else {
                refreshEeComboLabels();
            }
        });

        if (cancelBtn) {
            cancelBtn.addEventListener('click', function () {
                form.style.display = 'none';
                updateTopicTopActions();
            });
        }
        if (topCancelBtn) {
            topCancelBtn.addEventListener('click', function () {
                if ((TOPIC_SECTIONS[state.topicsTarget || 'topics-written'] || {}).type !== 'Expression Écrite') return;
                form.style.display = 'none';
                updateTopicTopActions();
            });
        }
        if (topSaveBtn) {
            topSaveBtn.addEventListener('click', function () {
                if ((TOPIC_SECTIONS[state.topicsTarget || 'topics-written'] || {}).type !== 'Expression Écrite') return;
                form.requestSubmit();
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var examId = document.getElementById('ee-exam-id').value;
            var title = (document.getElementById('ee-exam-title').value || '').trim();
            if (!title) {
                toast("Le titre de l'épreuve est requis.", true);
                return;
            }
            var combos = collectEeCombinations();
            var fd = new FormData();
            fd.append('action', examId ? 'update_exam' : 'create_exam');
            if (examId) fd.append('exam_id', examId);
            fd.append('title', title);
            fd.append('subtitle', document.getElementById('ee-exam-subtitle').value || '');
            fd.append('visibility', document.getElementById('ee-exam-visibility').value || 'gratuit');
            fd.append('is_published', document.getElementById('ee-exam-published').checked ? '1' : '0');
            fd.append('combinations', JSON.stringify(combos));
            fetch(EE_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (j) {
                    if (j && j.success) {
                        toast(j.message || 'Épreuve enregistrée');
                        form.style.display = 'none';
                        updateTopicTopActions();
                        loadEeExamsTable();
                    } else {
                        toast((j && j.message) || 'Erreur', true);
                    }
                })
                .catch(function () { toast('Erreur réseau', true); });
        });
    }

    function loadEoExamsTable() {
        var fd = new FormData();
        fd.append('action', 'get_exams_admin');
        fetch(EO_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j || !j.success || !j.data) {
                    toast((j && j.message) || 'Erreur chargement épreuves orales', true);
                    return;
                }
                renderEoExamsTable(j.data);
            })
            .catch(function () { toast('Erreur chargement épreuves orales', true); });
    }

    function renderEoExamsTable(rows) {
        var tb = document.querySelector('#topics-table tbody');
        if (!tb) return;
        if (!rows || !rows.length) {
            tb.innerHTML = '<tr><td colspan="6" style="padding:12px;color:var(--sa-muted);">Aucune épreuve.</td></tr>';
            return;
        }
        tb.innerHTML = rows.map(function (e) {
            var effectiveVis = String(e.effective_visibility || e.visibility || 'gratuit');
            var vis = Number(e.is_published || 0) === 1 ? effectiveVis : 'brouillon';
            var uses = Number(e.view_count || 0);
            return '<tr data-id="' + escAttr(String(e.id)) + '">' +
                '<td>' + escHtml(e.title || '') + '</td>' +
                '<td>Expression Orale</td>' +
                '<td><span class="sa-badge sa-badge--' + (Number(e.is_published || 0) === 1 ? (effectiveVis === 'premium' ? 'premium' : 'gratuit') : 'premium') + '">' + escHtml(vis) + '</span></td>' +
                '<td>' + escHtml(e.published_at || e.created_at || '') + '</td>' +
                '<td>' + escHtml(String(uses)) + '</td>' +
                saActionsRow('<button type="button" class="btn btn-outline btn-sm sa-btn-icon sa-topic-edit" aria-label="Modifier"><i class="bx bx-edit-alt" aria-hidden="true"></i></button><button type="button" class="btn btn-outline btn-sm sa-btn-icon btn-danger-outline sa-topic-del" aria-label="Supprimer"><i class="bx bx-trash" aria-hidden="true"></i></button>') +
                '</tr>';
        }).join('');
    }

    function eoSubjectFieldsHtml(taskKey, n) {
        var html = '';
        for (var i = 1; i <= 5; i++) {
            html += '<div style="margin-top:8px;padding:8px;border:1px dashed #cbd5e1;border-radius:10px;">' +
                '<h5 style="margin:0 0 6px;">Sujet ' + i + '</h5>' +
                '<div class="form-group"><label class="form-label">Titre</label><input class="form-control" data-eo-subject-title="' + i + '" data-eo-task-key="' + taskKey + '" type="text"></div>' +
                '<div class="form-group"><label class="form-label">Sujet (énoncé)</label><textarea class="form-control" rows="3" data-eo-subject-prompt="' + i + '" data-eo-task-key="' + taskKey + '"></textarea></div>' +
                '<div class="form-group"><label class="form-label">Correction (exemple de réponse)</label><textarea class="form-control" rows="4" data-eo-subject-correction="' + i + '" data-eo-task-key="' + taskKey + '" placeholder="Exemple de réponse — bouton « Voir / Masquer la correction » côté site"></textarea></div>' +
                '</div>';
        }
        return html;
    }

    function eoPartieTemplate(n) {
        var taskDefs = [
            { key: 'tache1', label: 'Tâche 1', hint: 'Présentation' },
            { key: 'tache2', label: 'Tâche 2', hint: 'Interaction' },
            { key: 'tache3', label: 'Tâche 3', hint: 'Point de vue' }
        ];
        var nav = taskDefs.map(function (t, idx) {
            return '<button type="button" class="btn btn-outline eo-partie-task-btn' + (idx === 1 ? ' is-active' : '') + '" data-eo-partie-task-tab="' + t.key + '">' + t.label + ' <small style="opacity:.85;">(' + t.hint + ')</small></button>';
        }).join('');
        var panels = taskDefs.map(function (t, idx) {
            return '<div class="eo-partie-task-panel" data-eo-partie-task-panel="' + t.key + '" style="margin-top:10px;' + (idx === 1 ? '' : 'display:none;') + '">' + eoSubjectFieldsHtml(t.key, n) + '</div>';
        }).join('');
        return '<div class="dashboard-section" data-eo-partie style="margin-top:14px;padding:12px;border:1px solid var(--sa-border);border-radius:12px;">' +
            '<div class="section-header" style="margin-bottom:8px;"><div class="section-title" style="font-size:1rem;">Partie <span data-eo-partie-label>' + n + '</span></div><button type="button" class="btn btn-outline btn-sm" data-eo-remove-partie>Retirer</button></div>' +
            '<div class="form-group"><label class="form-label">Numéro de partie</label><input type="number" min="1" class="form-control" data-eo-part-number value="' + n + '"></div>' +
            '<div class="form-group"><label class="form-label">Titre partie (optionnel)</label><input type="text" class="form-control" data-eo-part-title placeholder="Ex: Partie 1"></div>' +
            '<div class="form-group"><label class="form-label">Tâches de cette partie</label><div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:4px;">' + nav + '</div></div>' +
            panels +
            '</div>';
    }

    function resetEoParts(count) {
        var wrap = document.getElementById('eo-parts-wrap');
        if (!wrap) return;
        wrap.innerHTML = '';
        var n = count || 1;
        for (var i = 1; i <= n; i++) wrap.insertAdjacentHTML('beforeend', eoPartieTemplate(i));
        refreshEoPartieLabels();
    }

    function refreshEoPartieLabels() {
        $all('[data-eo-partie]').forEach(function (p, idx) {
            var n = idx + 1;
            var lab = p.querySelector('[data-eo-partie-label]');
            if (lab) lab.textContent = String(n);
        });
    }

    function eoGroupPartsForAdmin(parts) {
        var map = {};
        (parts || []).forEach(function (part) {
            var num = Number(part.part_number || 0) || 0;
            var key = num > 0 ? String(num) : ('id-' + (part.id || Math.random()));
            if (!map[key]) {
                map[key] = {
                    part_number: num || 1,
                    part_title: part.part_title || '',
                    tasks: {}
                };
            }
            if (part.part_title && !map[key].part_title) map[key].part_title = part.part_title;
            var tk = (part.task_key === 'tache1' || part.task_key === 'tache3') ? part.task_key : 'tache2';
            map[key].tasks[tk] = part;
        });
        return Object.keys(map).map(function (k) { return map[k]; }).sort(function (a, b) {
            return Number(b.part_number || 0) - Number(a.part_number || 0);
        });
    }

    function fillEoPartieEl(el, grouped) {
        if (!el || !grouped) return;
        var pn = el.querySelector('[data-eo-part-number]');
        if (pn) pn.value = grouped.part_number || 1;
        var pt = el.querySelector('[data-eo-part-title]');
        if (pt) pt.value = grouped.part_title || '';
        ['tache1', 'tache2', 'tache3'].forEach(function (tk) {
            var part = grouped.tasks[tk];
            if (!part) return;
            var subjects = Array.isArray(part.subjects) ? part.subjects : [];
            subjects.slice(0, 5).forEach(function (s, si) {
                var n = si + 1;
                var t = el.querySelector('[data-eo-subject-title="' + n + '"][data-eo-task-key="' + tk + '"]');
                var pr = el.querySelector('[data-eo-subject-prompt="' + n + '"][data-eo-task-key="' + tk + '"]');
                var co = el.querySelector('[data-eo-subject-correction="' + n + '"][data-eo-task-key="' + tk + '"]');
                if (t) t.value = s.title || '';
                if (pr) pr.value = s.prompt || '';
                if (co) co.value = s.correction || '';
            });
        });
    }

    function collectEoPartsPayload() {
        var out = [];
        $all('[data-eo-partie]').forEach(function (p, idx) {
            var partNumber = Number((p.querySelector('[data-eo-part-number]') || {}).value || (idx + 1));
            var partTitle = (p.querySelector('[data-eo-part-title]') || {}).value || '';
            ['tache1', 'tache2', 'tache3'].forEach(function (tk) {
                var subjects = [];
                var hasContent = false;
                for (var i = 1; i <= 5; i++) {
                    var t = p.querySelector('[data-eo-subject-title="' + i + '"][data-eo-task-key="' + tk + '"]');
                    var pr = p.querySelector('[data-eo-subject-prompt="' + i + '"][data-eo-task-key="' + tk + '"]');
                    var co = p.querySelector('[data-eo-subject-correction="' + i + '"][data-eo-task-key="' + tk + '"]');
                    var title = (t && t.value ? t.value : '').trim();
                    var prompt = (pr && pr.value ? pr.value : '').trim();
                    var correction = (co && co.value ? co.value : '').trim();
                    if (title || prompt || correction) hasContent = true;
                    subjects.push({
                        title: title,
                        prompt: prompt,
                        correction: correction,
                        icon_class: 'bx bx-message-detail'
                    });
                }
                if (!hasContent) return;
                out.push({
                    task_key: tk,
                    part_number: partNumber,
                    part_title: String(partTitle).trim(),
                    subjects: subjects
                });
            });
        });
        return out;
    }

    function openEoExamForm(examId) {
        var form = document.getElementById('eo-exam-form');
        if (!form) return;
        form.style.display = 'block';
        updateTopicTopActions();
        document.getElementById('eo-exam-id').value = '';
        document.getElementById('eo-exam-title').value = '';
        document.getElementById('eo-exam-subtitle').value = '';
        document.getElementById('eo-exam-visibility').value = 'gratuit';
        document.getElementById('eo-exam-published').checked = true;
        resetEoParts(1);
        if (!examId) return;
        var fd = new FormData();
        fd.append('action', 'get_exam_for_edit');
        fd.append('exam_id', examId);
        fetch(EO_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j || !j.success || !j.data) {
                    toast((j && j.message) || 'Erreur chargement épreuve orale', true);
                    return;
                }
                var e = j.data;
                document.getElementById('eo-exam-id').value = e.id || '';
                document.getElementById('eo-exam-title').value = e.title || '';
                document.getElementById('eo-exam-subtitle').value = e.subtitle || '';
                document.getElementById('eo-exam-visibility').value = (e.visibility === 'premium' ? 'premium' : 'gratuit');
                document.getElementById('eo-exam-published').checked = Number(e.is_published || 0) === 1;
                var grouped = eoGroupPartsForAdmin(Array.isArray(e.parts) ? e.parts : []);
                if (!grouped.length) grouped = [{ part_number: 1, part_title: '', tasks: {} }];
                resetEoParts(grouped.length);
                var partieEls = $all('[data-eo-partie]');
                grouped.forEach(function (g, pi) {
                    fillEoPartieEl(partieEls[pi], g);
                });
            })
            .catch(function () { toast('Erreur chargement épreuve orale', true); });
    }

    function initEoExamFormUi() {
        var form = document.getElementById('eo-exam-form');
        var addBtn = document.getElementById('eo-add-part-btn');
        var cancelBtn = document.getElementById('eo-cancel-btn');
        var topSaveBtn = document.getElementById('topic-save-top-btn');
        var topCancelBtn = document.getElementById('topic-cancel-top-btn');
        var wrap = document.getElementById('eo-parts-wrap');
        if (!form || !addBtn || !wrap) return;
        addBtn.addEventListener('click', function () {
            wrap.insertAdjacentHTML('beforeend', eoPartieTemplate($all('[data-eo-partie]').length + 1));
            refreshEoPartieLabels();
        });
        wrap.addEventListener('click', function (e) {
            var taskTab = e.target.closest('[data-eo-partie-task-tab]');
            if (taskTab) {
                var partieWrap = taskTab.closest('[data-eo-partie]');
                if (partieWrap) {
                    var tk = taskTab.getAttribute('data-eo-partie-task-tab') || 'tache2';
                    partieWrap.querySelectorAll('[data-eo-partie-task-btn]').forEach(function (b) {
                        b.classList.toggle('is-active', b === taskTab);
                    });
                    partieWrap.querySelectorAll('[data-eo-partie-task-panel]').forEach(function (panel) {
                        panel.style.display = (panel.getAttribute('data-eo-partie-task-panel') === tk) ? 'block' : 'none';
                    });
                }
                return;
            }
            var rm = e.target.closest('[data-eo-remove-partie]');
            if (!rm) return;
            var partie = rm.closest('[data-eo-partie]');
            if (!partie) return;
            partie.remove();
            if (!$all('[data-eo-partie]').length) resetEoParts(1);
            else refreshEoPartieLabels();
        });
        if (cancelBtn) cancelBtn.addEventListener('click', function () { form.style.display = 'none'; updateTopicTopActions(); });
        if (topCancelBtn) topCancelBtn.addEventListener('click', function () {
            if ((TOPIC_SECTIONS[state.topicsTarget || 'topics-written'] || {}).type !== 'Expression Orale') return;
            form.style.display = 'none';
            updateTopicTopActions();
        });
        if (topSaveBtn) topSaveBtn.addEventListener('click', function () {
            if ((TOPIC_SECTIONS[state.topicsTarget || 'topics-written'] || {}).type !== 'Expression Orale') return;
            form.requestSubmit();
        });
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var examId = document.getElementById('eo-exam-id').value;
            var title = (document.getElementById('eo-exam-title').value || '').trim();
            if (!title) return toast("Le titre de l'épreuve est requis.", true);
            var parts = collectEoPartsPayload();
            var fd = new FormData();
            fd.append('action', 'save_exam');
            if (examId) fd.append('exam_id', examId);
            fd.append('title', title);
            fd.append('subtitle', document.getElementById('eo-exam-subtitle').value || '');
            fd.append('visibility', document.getElementById('eo-exam-visibility').value || 'gratuit');
            fd.append('is_published', document.getElementById('eo-exam-published').checked ? '1' : '0');
            fd.append('parts_json', JSON.stringify(parts));
            fetch(EO_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (j) {
                    if (j && j.success) {
                        toast(j.message || 'Épreuve orale enregistrée');
                        form.style.display = 'none';
                        updateTopicTopActions();
                        loadEoExamsTable();
                    } else {
                        toast((j && j.message) || 'Erreur', true);
                    }
                })
                .catch(function () { toast('Erreur réseau', true); });
        });
    }

    function loadCeExamsTable() {
        var fd = new FormData();
        fd.append('action', 'get_exams_admin');
        fetch(CE_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j || !j.success || !j.data) {
                    toast((j && j.message) || 'Erreur chargement compréhension écrite', true);
                    return;
                }
                renderCeExamsTable(j.data);
            })
            .catch(function () { toast('Erreur chargement compréhension écrite', true); });
    }

    function renderCeExamsTable(rows) {
        var tb = document.querySelector('#topics-table tbody');
        if (!tb) return;
        if (!rows || !rows.length) {
            tb.innerHTML = '<tr><td colspan="6" style="padding:12px;color:var(--sa-muted);">Aucune épreuve.</td></tr>';
            return;
        }
        tb.innerHTML = rows.map(function (e) {
            var effectiveVis = String(e.effective_visibility || e.visibility || 'gratuit');
            var vis = Number(e.is_published || 0) === 1 ? effectiveVis : 'brouillon';
            var uses = Number(e.view_count || 0);
            return '<tr data-id="' + escAttr(String(e.id)) + '">' +
                '<td>' + escHtml(e.title || '') + '</td>' +
                '<td>Compréhension Écrite (quiz)</td>' +
                '<td><span class="sa-badge sa-badge--' + (Number(e.is_published || 0) === 1 ? (effectiveVis === 'premium' ? 'premium' : 'gratuit') : 'premium') + '">' + escHtml(vis) + '</span></td>' +
                '<td>' + escHtml(e.published_at || e.created_at || '') + '</td>' +
                '<td>' + escHtml(String(uses)) + '</td>' +
                saActionsRow('<button type="button" class="btn btn-outline btn-sm sa-btn-icon sa-topic-edit" aria-label="Modifier"><i class="bx bx-edit-alt" aria-hidden="true"></i></button><button type="button" class="btn btn-outline btn-sm sa-btn-icon btn-danger-outline sa-topic-del" aria-label="Supprimer"><i class="bx bx-trash" aria-hidden="true"></i></button>') +
                '</tr>';
        }).join('');
    }

    function openQuizPublishMethodModal(kind) {
        state.quizPublishPending = kind;
        var m = document.getElementById('quiz-publish-method-modal');
        if (!m) return;
        m.classList.add('is-open');
        m.setAttribute('aria-hidden', 'false');
    }

    function closeQuizPublishMethodModal() {
        state.quizPublishPending = null;
        var m = document.getElementById('quiz-publish-method-modal');
        if (!m) return;
        m.classList.remove('is-open');
        m.setAttribute('aria-hidden', 'true');
    }

    function resetCeJsonForm() {
        var fields = [
            ['ce-json-exam-id', ''],
            ['ce-json-exam-title', ''],
            ['ce-json-paste', '']
        ];
        fields.forEach(function (pair) {
            var el = document.getElementById(pair[0]);
            if (el) el.value = pair[1];
        });
        var vis = document.getElementById('ce-json-exam-visibility');
        if (vis) vis.value = 'gratuit';
        var pub = document.getElementById('ce-json-exam-published');
        if (pub) pub.checked = true;
        var dur = document.getElementById('ce-json-duration-minutes');
        if (dur) dur.value = '60';
        var fi = document.getElementById('ce-json-file');
        if (fi) fi.value = '';
    }

    function resetCoJsonForm() {
        var fields = [
            ['co-json-exam-id', ''],
            ['co-json-exam-title', ''],
            ['co-json-paste', '']
        ];
        fields.forEach(function (pair) {
            var el = document.getElementById(pair[0]);
            if (el) el.value = pair[1];
        });
        var vis = document.getElementById('co-json-exam-visibility');
        if (vis) vis.value = 'gratuit';
        var pub = document.getElementById('co-json-exam-published');
        if (pub) pub.checked = true;
        var dur = document.getElementById('co-json-duration-minutes');
        if (dur) dur.value = '30';
        var fi = document.getElementById('co-json-file');
        if (fi) fi.value = '';
    }

    function initQuizPublishMethodModal() {
        var m = document.getElementById('quiz-publish-method-modal');
        if (!m) return;
        var closeBtn = document.getElementById('quiz-publish-method-close');
        var manual = document.getElementById('quiz-publish-method-manual');
        var jsonBtn = document.getElementById('quiz-publish-method-json');
        if (closeBtn) {
            closeBtn.addEventListener('click', closeQuizPublishMethodModal);
        }
        m.addEventListener('click', function (e) {
            if (e.target === m) closeQuizPublishMethodModal();
        });
        if (manual) {
            manual.addEventListener('click', function () {
                var k = state.quizPublishPending;
                closeQuizPublishMethodModal();
                if (k === 'ce') {
                    var jf = document.getElementById('ce-exam-json-form');
                    if (jf) jf.style.display = 'none';
                    openCeExamForm(null);
                } else if (k === 'co') {
                    var jf2 = document.getElementById('co-exam-json-form');
                    if (jf2) jf2.style.display = 'none';
                    openCoExamForm(null);
                }
            });
        }
        if (jsonBtn) {
            jsonBtn.addEventListener('click', function () {
                var k = state.quizPublishPending;
                closeQuizPublishMethodModal();
                if (k === 'ce') {
                    var mf = document.getElementById('ce-exam-form');
                    if (mf) mf.style.display = 'none';
                    resetCeJsonForm();
                    var jf = document.getElementById('ce-exam-json-form');
                    if (jf) jf.style.display = 'block';
                    updateTopicTopActions();
                } else if (k === 'co') {
                    var mf2 = document.getElementById('co-exam-form');
                    if (mf2) mf2.style.display = 'none';
                    resetCoJsonForm();
                    var jf2 = document.getElementById('co-exam-json-form');
                    if (jf2) jf2.style.display = 'block';
                    updateTopicTopActions();
                }
            });
        }
    }

    function loadCeConsignesBundleAdmin() {
        var fd = new FormData();
        fd.append('action', 'get_consignes_bundle_admin');
        fetch(CE_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j || !j.success || !j.data) return toast((j && j.message) || 'Erreur chargement consignes CE', true);
                var d = j.data || {};
                if (document.getElementById('ce-consigne-structure')) document.getElementById('ce-consigne-structure').value = d.structure || '';
                if (document.getElementById('ce-consigne-techniques')) document.getElementById('ce-consigne-techniques').value = d.techniques || '';
                if (document.getElementById('ce-consigne-erreurs')) document.getElementById('ce-consigne-erreurs').value = d.erreurs || '';
                if (document.getElementById('ce-consigne-status')) document.getElementById('ce-consigne-status').value = Number(d.is_published || 0) === 1 ? '1' : '0';
            })
            .catch(function () { toast('Erreur chargement consignes CE', true); });
    }

    function loadCoConsignesBundleAdmin() {
        var fd = new FormData();
        fd.append('action', 'get_consignes_bundle_admin');
        fetch(CO_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j || !j.success || !j.data) return toast((j && j.message) || 'Erreur chargement consignes CO', true);
                var d = j.data || {};
                if (document.getElementById('co-consigne-structure')) document.getElementById('co-consigne-structure').value = d.structure || '';
                if (document.getElementById('co-consigne-techniques')) document.getElementById('co-consigne-techniques').value = d.techniques || '';
                if (document.getElementById('co-consigne-erreurs')) document.getElementById('co-consigne-erreurs').value = d.erreurs || '';
                if (document.getElementById('co-consigne-status')) document.getElementById('co-consigne-status').value = Number(d.is_published || 0) === 1 ? '1' : '0';
            })
            .catch(function () { toast('Erreur chargement consignes CO', true); });
    }

    function initCeConsignesUi() {
        var form = document.getElementById('ce-consignes-bundle-form');
        var openBtn = document.getElementById('ce-open-consignes-btn');
        var cancelBtn = document.getElementById('ce-consigne-cancel-btn');
        var submitBtn = document.getElementById('ce-consigne-submit-btn');
        var status = document.getElementById('ce-consigne-status');
        if (!form) return;
        function refreshLabel() {
            if (!submitBtn || !status) return;
            submitBtn.textContent = status.value === '1' ? 'Publier' : 'Enregistrer brouillon';
        }
        if (openBtn) openBtn.addEventListener('click', function () { form.style.display = 'block'; loadCeConsignesBundleAdmin(); });
        if (cancelBtn) cancelBtn.addEventListener('click', function () { form.style.display = 'none'; });
        if (status) status.addEventListener('change', refreshLabel);
        refreshLabel();
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var structure = (document.getElementById('ce-consigne-structure').value || '').trim();
            var techniques = (document.getElementById('ce-consigne-techniques').value || '').trim();
            var erreurs = (document.getElementById('ce-consigne-erreurs').value || '').trim();
            if (!structure || !techniques || !erreurs) return toast('Veuillez renseigner les 3 sections de consignes.', true);
            var fd = new FormData();
            fd.append('action', 'save_consignes_bundle');
            fd.append('structure', document.getElementById('ce-consigne-structure').value || '');
            fd.append('techniques', document.getElementById('ce-consigne-techniques').value || '');
            fd.append('erreurs', document.getElementById('ce-consigne-erreurs').value || '');
            fd.append('is_published', document.getElementById('ce-consigne-status').value === '1' ? '1' : '0');
            fetch(CE_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (j) {
                    if (j && j.success) {
                        toast(j.message || 'Consignes enregistrées');
                        form.style.display = 'none';
                    } else {
                        toast((j && j.message) || 'Erreur', true);
                    }
                })
                .catch(function () { toast('Erreur réseau', true); });
        });
    }

    function initCoConsignesUi() {
        var form = document.getElementById('co-consignes-bundle-form');
        var openBtn = document.getElementById('co-open-consignes-btn');
        var cancelBtn = document.getElementById('co-consigne-cancel-btn');
        var submitBtn = document.getElementById('co-consigne-submit-btn');
        var status = document.getElementById('co-consigne-status');
        if (!form) return;
        function refreshLabel() {
            if (!submitBtn || !status) return;
            submitBtn.textContent = status.value === '1' ? 'Publier' : 'Enregistrer brouillon';
        }
        if (openBtn) openBtn.addEventListener('click', function () { form.style.display = 'block'; loadCoConsignesBundleAdmin(); });
        if (cancelBtn) cancelBtn.addEventListener('click', function () { form.style.display = 'none'; });
        if (status) status.addEventListener('change', refreshLabel);
        refreshLabel();
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var structure = (document.getElementById('co-consigne-structure').value || '').trim();
            var techniques = (document.getElementById('co-consigne-techniques').value || '').trim();
            var erreurs = (document.getElementById('co-consigne-erreurs').value || '').trim();
            if (!structure || !techniques || !erreurs) return toast('Veuillez renseigner les 3 sections de consignes.', true);
            var fd = new FormData();
            fd.append('action', 'save_consignes_bundle');
            fd.append('structure', document.getElementById('co-consigne-structure').value || '');
            fd.append('techniques', document.getElementById('co-consigne-techniques').value || '');
            fd.append('erreurs', document.getElementById('co-consigne-erreurs').value || '');
            fd.append('is_published', document.getElementById('co-consigne-status').value === '1' ? '1' : '0');
            fetch(CO_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (j) {
                    if (j && j.success) {
                        toast(j.message || 'Consignes enregistrées');
                        form.style.display = 'none';
                    } else {
                        toast((j && j.message) || 'Erreur', true);
                    }
                })
                .catch(function () { toast('Erreur réseau', true); });
        });
    }

    function initCeJsonImportUi() {
        var form = document.getElementById('ce-exam-json-form');
        var cancelBtn = document.getElementById('ce-json-cancel-btn');
        if (!form) return;
        function hideJson() {
            form.style.display = 'none';
            updateTopicTopActions();
        }
        if (cancelBtn) cancelBtn.addEventListener('click', hideJson);
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var title = (document.getElementById('ce-json-exam-title').value || '').trim();
            if (!title) return toast("Le titre de l'épreuve est requis.", true);
            var paste = (document.getElementById('ce-json-paste').value || '').trim();
            var fileInput = document.getElementById('ce-json-file');

            function submitJsonPayload(jsonStr) {
                var mins = parseInt(document.getElementById('ce-json-duration-minutes').value, 10) || 60;
                var durSec = Math.min(86400, Math.max(60, mins * 60));
                var fd = new FormData();
                fd.append('action', 'save_exam');
                var examId = (document.getElementById('ce-json-exam-id').value || '').trim();
                if (examId) fd.append('exam_id', examId);
                fd.append('title', title);
                fd.append('subtitle', '');
                fd.append('intro_html', '');
                fd.append('visibility', document.getElementById('ce-json-exam-visibility').value || 'gratuit');
                fd.append('is_published', document.getElementById('ce-json-exam-published').checked ? '1' : '0');
                fd.append('duration_seconds', String(durSec));
                fd.append('questions_json', jsonStr);
                fetch(CE_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (j) {
                        if (j && j.success) {
                            toast(j.message || 'Épreuve enregistrée');
                            hideJson();
                            loadCeExamsTable();
                        } else {
                            toast((j && j.message) || 'Erreur JSON ou validation', true);
                        }
                    })
                    .catch(function () { toast('Erreur réseau', true); });
            }

            if (fileInput && fileInput.files && fileInput.files[0]) {
                var fr = new FileReader();
                fr.onload = function () {
                    submitJsonPayload(String(fr.result || ''));
                };
                fr.onerror = function () {
                    toast('Lecture du fichier impossible.', true);
                };
                fr.readAsText(fileInput.files[0]);
                return;
            }
            if (!paste) return toast('Choisissez un fichier JSON ou collez le contenu.', true);
            submitJsonPayload(paste);
        });
    }

    function initCoJsonImportUi() {
        var form = document.getElementById('co-exam-json-form');
        var cancelBtn = document.getElementById('co-json-cancel-btn');
        if (!form) return;
        function hideJson() {
            form.style.display = 'none';
            updateTopicTopActions();
        }
        if (cancelBtn) cancelBtn.addEventListener('click', hideJson);
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var title = (document.getElementById('co-json-exam-title').value || '').trim();
            if (!title) return toast("Le titre de l'épreuve est requis.", true);
            var paste = (document.getElementById('co-json-paste').value || '').trim();
            var fileInput = document.getElementById('co-json-file');

            function submitJsonPayload(jsonStr) {
                var mins = parseInt(document.getElementById('co-json-duration-minutes').value, 10) || 30;
                var durSec = Math.min(86400, Math.max(60, mins * 60));
                var fd = new FormData();
                fd.append('action', 'save_exam');
                var examId = (document.getElementById('co-json-exam-id').value || '').trim();
                if (examId) fd.append('exam_id', examId);
                fd.append('title', title);
                fd.append('subtitle', '');
                fd.append('intro_html', '');
                fd.append('visibility', document.getElementById('co-json-exam-visibility').value || 'gratuit');
                fd.append('is_published', document.getElementById('co-json-exam-published').checked ? '1' : '0');
                fd.append('duration_seconds', String(durSec));
                fd.append('questions_json', jsonStr);
                fetch(CO_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (j) {
                        if (j && j.success) {
                            toast(j.message || 'Épreuve enregistrée');
                            hideJson();
                            loadCoExamsTable();
                        } else {
                            toast((j && j.message) || 'Erreur JSON ou validation', true);
                        }
                    })
                    .catch(function () { toast('Erreur réseau', true); });
            }

            if (fileInput && fileInput.files && fileInput.files[0]) {
                var fr = new FileReader();
                fr.onload = function () {
                    submitJsonPayload(String(fr.result || ''));
                };
                fr.onerror = function () {
                    toast('Lecture du fichier impossible.', true);
                };
                fr.readAsText(fileInput.files[0]);
                return;
            }
            if (!paste) return toast('Choisissez un fichier JSON ou collez le contenu.', true);
            submitJsonPayload(paste);
        });
    }

    function ceQuestionTemplate(num) {
        return (
            '<div class="dashboard-section ce-q-block" data-ce-q style="margin-top:12px;padding:14px;border:1px solid rgba(148,163,184,.35);border-radius:10px;background:rgba(248,250,252,.6);">' +
            '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">' +
            '<strong data-ce-q-label>Question ' +
            num +
            '</strong>' +
            '<button type="button" class="btn btn-outline btn-sm" data-ce-remove-q><i class="bx bx-trash"></i></button>' +
            '</div>' +
            '<div class="form-group"><label class="form-label">Situation / contexte (optionnel)</label>' +
            '<textarea class="form-control" rows="2" data-ce-situation placeholder="Texte de mise en contexte avant l’énoncé"></textarea></div>' +
            '<div class="form-group"><label class="form-label">Énoncé de la question</label>' +
            '<textarea class="form-control" rows="2" data-ce-q-text required></textarea></div>' +
            '<div class="form-group"><label class="form-label">Nombre de points (cette question)</label>' +
            '<input type="number" class="form-control" data-ce-q-points value="3" min="1" max="99"></div>' +
            '<div class="form-group"><label class="form-label">Réponses (au moins 2 renseignées)</label>' +
            '<textarea class="form-control" rows="2" style="margin-bottom:6px;" data-ce-ans="0" placeholder="Réponse A (HTML autorisé : &lt;br&gt;, &lt;strong&gt;, &lt;em&gt;)"></textarea>' +
            '<textarea class="form-control" rows="2" style="margin-bottom:6px;" data-ce-ans="1" placeholder="Réponse B (HTML autorisé : &lt;br&gt;, &lt;strong&gt;, &lt;em&gt;)"></textarea>' +
            '<textarea class="form-control" rows="2" style="margin-bottom:6px;" data-ce-ans="2" placeholder="Réponse C (HTML autorisé : &lt;br&gt;, &lt;strong&gt;, &lt;em&gt;)"></textarea>' +
            '<textarea class="form-control" rows="2" data-ce-ans="3" placeholder="Réponse D (HTML autorisé : &lt;br&gt;, &lt;strong&gt;, &lt;em&gt;)"></textarea></div>' +
            '<div class="form-group"><label class="form-label">Bonne réponse</label>' +
            '<select class="form-control" data-ce-correct>' +
            '<option value="0">A</option><option value="1">B</option><option value="2">C</option><option value="3">D</option>' +
            '</select></div>' +
            '</div>'
        );
    }

    function refreshCeQuestionLabels() {
        $all('[data-ce-q]').forEach(function (blk, idx) {
            var lab = blk.querySelector('[data-ce-q-label]');
            if (lab) lab.textContent = 'Question ' + (idx + 1);
        });
    }

    function resetCeQuestions(count) {
        var wrap = document.getElementById('ce-questions-wrap');
        if (!wrap) return;
        wrap.innerHTML = '';
        var n = Math.max(1, count || 1);
        for (var i = 0; i < n; i++) {
            wrap.insertAdjacentHTML('beforeend', ceQuestionTemplate(i + 1));
        }
        refreshCeQuestionLabels();
    }

    function collectCeQuestionsPayload() {
        var list = [];
        $all('[data-ce-q]').forEach(function (blk) {
            var sit = (blk.querySelector('[data-ce-situation]') && blk.querySelector('[data-ce-situation]').value) || '';
            var text = (blk.querySelector('[data-ce-q-text]') && blk.querySelector('[data-ce-q-text]').value) || '';
            var pts = parseInt(blk.querySelector('[data-ce-q-points]').value, 10) || 3;
            var correctIdx = parseInt(blk.querySelector('[data-ce-correct]').value, 10) || 0;
            var answers = [];
            for (var ai = 0; ai < 4; ai++) {
                var inp = blk.querySelector('[data-ce-ans="' + ai + '"]');
                answers.push({ text: inp ? inp.value.trim() : '' });
            }
            list.push({
                situation: sit.trim(),
                question_text: text.trim(),
                points: Math.max(1, pts),
                correct_index: Math.min(3, Math.max(0, correctIdx)),
                answers: answers
            });
        });
        return list;
    }

    function appendCeQuestionsToFormData(fd, questions) {
        questions.forEach(function (q, i) {
            fd.append('questions[' + i + '][situation]', q.situation);
            fd.append('questions[' + i + '][question_text]', q.question_text);
            fd.append('questions[' + i + '][points]', String(q.points));
            fd.append('questions[' + i + '][correct_index]', String(q.correct_index));
            q.answers.forEach(function (a, j) {
                fd.append('questions[' + i + '][answers][' + j + '][text]', a.text);
            });
        });
    }

    function openCeExamForm(examId) {
        var jsonForm = document.getElementById('ce-exam-json-form');
        if (jsonForm) jsonForm.style.display = 'none';
        var form = document.getElementById('ce-exam-form');
        if (!form) return;
        form.style.display = 'block';
        updateTopicTopActions();
        try {
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (e1) {}
        var idEl = document.getElementById('ce-exam-id');
        var titleEl = document.getElementById('ce-exam-title');
        var visEl = document.getElementById('ce-exam-visibility');
        var pubEl = document.getElementById('ce-exam-published');
        var durEl = document.getElementById('ce-duration-minutes');
        var wrap = document.getElementById('ce-questions-wrap');
        if (!idEl || !titleEl || !wrap) return;
        if (!examId) {
            idEl.value = '';
            titleEl.value = '';
            if (visEl) visEl.value = 'gratuit';
            if (pubEl) pubEl.checked = true;
            if (durEl) durEl.value = '60';
            resetCeQuestions(1);
            return;
        }
        idEl.value = String(examId);
        var fd = new FormData();
        fd.append('action', 'get_exam_for_edit');
        fd.append('exam_id', examId);
        fetch(CE_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j || !j.success || !j.data) {
                    toast((j && j.message) || 'Chargement impossible', true);
                    return;
                }
                var d = j.data;
                titleEl.value = d.title || '';
                if (visEl) visEl.value = (d.visibility === 'premium' ? 'premium' : 'gratuit');
                if (pubEl) pubEl.checked = Number(d.is_published || 0) === 1;
                var sec = Number(d.duration_seconds || 3600);
                if (durEl) durEl.value = String(Math.max(1, Math.round(sec / 60)));
                var qs = d.quiz_questions || [];
                wrap.innerHTML = '';
                if (!qs.length) {
                    resetCeQuestions(1);
                    return;
                }
                qs.forEach(function (q, idx) {
                    wrap.insertAdjacentHTML('beforeend', ceQuestionTemplate(idx + 1));
                    var blk = wrap.querySelectorAll('[data-ce-q]')[idx];
                    if (!blk) return;
                    var st = blk.querySelector('[data-ce-situation]');
                    if (st) st.value = (q.situation || '').trim() ? q.situation : '';
                    var tx = blk.querySelector('[data-ce-q-text]');
                    if (tx) tx.value = q.question_text || '';
                    var pt = blk.querySelector('[data-ce-q-points]');
                    if (pt) pt.value = String(q.points != null ? q.points : 3);
                    var sel = blk.querySelector('[data-ce-correct]');
                    if (sel) sel.value = String(Math.min(3, Math.max(0, Number(q.correct_index != null ? q.correct_index : 0))));
                    var ans = q.answers || [];
                    for (var ai = 0; ai < 4; ai++) {
                        var inp = blk.querySelector('[data-ce-ans="' + ai + '"]');
                        if (inp && ans[ai]) inp.value = ans[ai].text || '';
                    }
                });
                refreshCeQuestionLabels();
            })
            .catch(function () { toast('Erreur réseau', true); });
    }

    function initCeExamFormUi() {
        var form = document.getElementById('ce-exam-form');
        var addBtn = document.getElementById('ce-add-question-btn');
        var wrap = document.getElementById('ce-questions-wrap');
        var cancelBtn = document.getElementById('ce-cancel-btn');
        var topSaveBtn = document.getElementById('topic-save-top-btn');
        var topCancelBtn = document.getElementById('topic-cancel-top-btn');
        if (!form || !wrap) return;
        if (addBtn) {
            addBtn.addEventListener('click', function () {
                wrap.insertAdjacentHTML('beforeend', ceQuestionTemplate($all('[data-ce-q]').length + 1));
                refreshCeQuestionLabels();
            });
        }
        wrap.addEventListener('click', function (e) {
            var rm = e.target.closest && e.target.closest('[data-ce-remove-q]');
            if (!rm) return;
            var blk = rm.closest('[data-ce-q]');
            if (!blk) return;
            if ($all('[data-ce-q]').length <= 1) {
                toast('Au moins une question est requise.', true);
                return;
            }
            blk.remove();
            refreshCeQuestionLabels();
        });
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function () {
                form.style.display = 'none';
                var jf = document.getElementById('ce-exam-json-form');
                if (jf) jf.style.display = 'none';
                updateTopicTopActions();
            });
        }
        if (topCancelBtn) {
            topCancelBtn.addEventListener('click', function () {
                if ((TOPIC_SECTIONS[state.topicsTarget || 'topics-written'] || {}).type !== 'Compréhension Écrite') return;
                form.style.display = 'none';
                var jf = document.getElementById('ce-exam-json-form');
                if (jf) jf.style.display = 'none';
                updateTopicTopActions();
            });
        }
        if (topSaveBtn) {
            topSaveBtn.addEventListener('click', function () {
                if ((TOPIC_SECTIONS[state.topicsTarget || 'topics-written'] || {}).type !== 'Compréhension Écrite') return;
                var jf = document.getElementById('ce-exam-json-form');
                if (jf && jf.style.display !== 'none') {
                    jf.requestSubmit();
                    return;
                }
                form.requestSubmit();
            });
        }
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var examId = (document.getElementById('ce-exam-id').value || '').trim();
            var title = (document.getElementById('ce-exam-title').value || '').trim();
            if (!title) {
                toast("Le titre de l'épreuve est requis.", true);
                return;
            }
            var payload = collectCeQuestionsPayload();
            var ok = true;
            payload.forEach(function (q, qi) {
                if (!q.question_text) {
                    toast('Question ' + (qi + 1) + ' : énoncé obligatoire.', true);
                    ok = false;
                    return;
                }
                var filled = q.answers.filter(function (a) { return a.text.length > 0; }).length;
                if (filled < 2) {
                    toast('Question ' + (qi + 1) + ' : au moins deux réponses renseignées.', true);
                    ok = false;
                    return;
                }
                var ci = q.correct_index;
                if (!q.answers[ci] || !q.answers[ci].text.trim()) {
                    toast('Question ' + (qi + 1) + ' : la bonne réponse doit avoir un texte.', true);
                    ok = false;
                }
            });
            if (!ok) return;
            var mins = parseInt(document.getElementById('ce-duration-minutes').value, 10);
            if (!mins || mins < 1) mins = 60;
            var durSec = Math.min(86400, Math.max(60, mins * 60));
            var fd = new FormData();
            fd.append('action', 'save_exam');
            if (examId) fd.append('exam_id', examId);
            fd.append('title', title);
            fd.append('subtitle', '');
            fd.append('intro_html', '');
            fd.append('visibility', document.getElementById('ce-exam-visibility').value || 'gratuit');
            fd.append('is_published', document.getElementById('ce-exam-published').checked ? '1' : '0');
            fd.append('duration_seconds', String(durSec));
            appendCeQuestionsToFormData(fd, payload);
            fetch(CE_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (j) {
                    if (j && j.success) {
                        toast(j.message || 'Épreuve enregistrée');
                        form.style.display = 'none';
                        updateTopicTopActions();
                        loadCeExamsTable();
                    } else {
                        toast((j && j.message) || 'Erreur', true);
                    }
                })
                .catch(function () { toast('Erreur réseau', true); });
        });
    }

    function tcfCoPublicUrl(rel) {
        if (rel == null || rel === '') return '';
        var s = String(rel).trim();
        if (/^https?:\/\//i.test(s)) return s;
        var base = typeof window.TCF_SITE_PUBLIC !== 'undefined' ? String(window.TCF_SITE_PUBLIC) : '';
        s = s.replace(/^\/+/, '');
        if (!base) return '/' + s;
        return base + '/' + s;
    }

    function refreshCoQuestionMediaPreviews(blk) {
        if (!blk) return;
        var imgPath = (blk.querySelector('[data-co-q-image]') && blk.querySelector('[data-co-q-image]').value) || '';
        var audPath = (blk.querySelector('[data-co-q-audio]') && blk.querySelector('[data-co-q-audio]').value) || '';
        var imgWrap = blk.querySelector('[data-co-img-preview-wrap]');
        var imgEl = blk.querySelector('[data-co-img-preview]');
        var audWrap = blk.querySelector('[data-co-aud-preview-wrap]');
        var audEl = blk.querySelector('[data-co-aud-preview]');
        if (imgEl && imgWrap) {
            if (imgPath.trim()) {
                imgEl.src = tcfCoPublicUrl(imgPath.trim());
                imgEl.style.display = '';
                imgWrap.style.display = 'block';
            } else {
                imgEl.removeAttribute('src');
                imgWrap.style.display = 'none';
            }
        }
        if (audEl && audWrap) {
            if (audPath.trim()) {
                audEl.src = tcfCoPublicUrl(audPath.trim());
                audWrap.style.display = 'block';
            } else {
                audEl.removeAttribute('src');
                audWrap.style.display = 'none';
            }
        }
    }

    function uploadCoMediaFile(file, kind, onDone) {
        var fd = new FormData();
        fd.append('action', 'upload_co_media');
        fd.append('kind', kind);
        fd.append('file', file);
        fetch(CO_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (onDone) onDone(j);
            })
            .catch(function () {
                if (onDone) onDone(null);
            });
    }

    function coQuestionTemplate(num) {
        return (
            '<div class="dashboard-section co-q-block" data-co-q style="margin-top:12px;padding:14px;border:1px solid rgba(148,163,184,.35);border-radius:10px;background:rgba(248,250,252,.6);">' +
            '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">' +
            '<strong data-co-q-label>Question ' +
            num +
            '</strong>' +
            '<button type="button" class="btn btn-outline btn-sm" data-co-remove-q><i class="bx bx-trash"></i></button>' +
            '</div>' +
            '<div class="form-group"><label class="form-label">Énoncé de la question</label>' +
            '<textarea class="form-control" rows="2" data-co-q-text required placeholder="Texte affiché au candidat"></textarea></div>' +
            '<div class="form-group"><label class="form-label">Nombre de points (cette question)</label>' +
            '<input type="number" class="form-control" data-co-q-points value="1" min="1" max="99"></div>' +
            '<div class="form-group"><label class="form-label">Image (facultatif) — importer un fichier</label>' +
            '<input type="file" class="form-control" data-co-img-file accept="image/jpeg,image/png,image/gif,image/webp">' +
            '<div data-co-img-preview-wrap style="display:none;margin-top:10px;">' +
            '<div style="font-size:0.85rem;color:#64748b;margin-bottom:6px;">Aperçu</div>' +
            '<img data-co-img-preview alt="Aperçu image" style="max-width:100%;max-height:200px;border-radius:8px;border:1px solid rgba(148,163,184,.5);object-fit:contain;background:#f1f5f9;">' +
            '</div></div>' +
            '<div class="form-group"><label class="form-label">Image (facultatif) — chemin ou URL</label>' +
            '<input type="text" class="form-control" data-co-q-image placeholder="ex. uploads/co_media/… ou URL https://…"></div>' +
            '<div class="form-group"><label class="form-label">Audio — importer un fichier</label>' +
            '<input type="file" class="form-control" data-co-aud-file accept="audio/*,.mp3,.wav,.m4a,.ogg,.aac">' +
            '<div data-co-aud-preview-wrap style="display:none;margin-top:10px;">' +
            '<div style="font-size:0.85rem;color:#64748b;margin-bottom:6px;">Écoute</div>' +
            '<audio data-co-aud-preview controls preload="metadata" style="width:100%;max-width:480px;"></audio>' +
            '</div></div>' +
            '<div class="form-group"><label class="form-label">Audio — chemin ou URL</label>' +
            '<input type="text" class="form-control" data-co-q-audio placeholder="ex. uploads/co_media/… ou URL https://…"></div>' +
            '<div class="form-group"><label class="form-label">Réponses (remplissez au moins 2)</label>' +
            '<textarea class="form-control" rows="2" style="margin-bottom:6px;" data-co-ans="0" placeholder="Réponse A (HTML autorisé : &lt;br&gt;, &lt;strong&gt;, &lt;em&gt;)"></textarea>' +
            '<textarea class="form-control" rows="2" style="margin-bottom:6px;" data-co-ans="1" placeholder="Réponse B (HTML autorisé : &lt;br&gt;, &lt;strong&gt;, &lt;em&gt;)"></textarea>' +
            '<textarea class="form-control" rows="2" style="margin-bottom:6px;" data-co-ans="2" placeholder="Réponse C (HTML autorisé : &lt;br&gt;, &lt;strong&gt;, &lt;em&gt;)"></textarea>' +
            '<textarea class="form-control" rows="2" data-co-ans="3" placeholder="Réponse D (HTML autorisé : &lt;br&gt;, &lt;strong&gt;, &lt;em&gt;)"></textarea></div>' +
            '<div class="form-group"><label class="form-label">Bonne réponse</label>' +
            '<select class="form-control" data-co-correct>' +
            '<option value="0">A</option><option value="1">B</option><option value="2">C</option><option value="3">D</option>' +
            '</select></div>' +
            '</div>'
        );
    }

    function refreshCoQuestionLabels() {
        $all('[data-co-q]').forEach(function (blk, idx) {
            var lab = blk.querySelector('[data-co-q-label]');
            if (lab) lab.textContent = 'Question ' + (idx + 1);
        });
    }

    function resetCoQuestions(count) {
        var wrap = document.getElementById('co-questions-wrap');
        if (!wrap) return;
        wrap.innerHTML = '';
        var n = Math.max(1, count || 1);
        for (var i = 0; i < n; i++) {
            wrap.insertAdjacentHTML('beforeend', coQuestionTemplate(i + 1));
        }
        refreshCoQuestionLabels();
        $all('[data-co-q]').forEach(function (b) {
            refreshCoQuestionMediaPreviews(b);
        });
    }

    function collectCoQuestionsPayload() {
        var list = [];
        $all('[data-co-q]').forEach(function (blk) {
            var text = (blk.querySelector('[data-co-q-text]') && blk.querySelector('[data-co-q-text]').value) || '';
            var pts = parseInt(blk.querySelector('[data-co-q-points]').value, 10) || 1;
            var img = (blk.querySelector('[data-co-q-image]') && blk.querySelector('[data-co-q-image]').value) || '';
            var aud = (blk.querySelector('[data-co-q-audio]') && blk.querySelector('[data-co-q-audio]').value) || '';
            var correctIdx = parseInt(blk.querySelector('[data-co-correct]').value, 10) || 0;
            var answers = [];
            for (var ai = 0; ai < 4; ai++) {
                var inp = blk.querySelector('[data-co-ans="' + ai + '"]');
                answers.push({ text: inp ? inp.value.trim() : '' });
            }
            list.push({
                question_text: text.trim(),
                points: Math.max(1, pts),
                image_src: img.trim(),
                audio_src: aud.trim(),
                correct_index: Math.min(3, Math.max(0, correctIdx)),
                answers: answers
            });
        });
        return list;
    }

    function appendCoQuestionsToFormData(fd, questions) {
        questions.forEach(function (q, i) {
            fd.append('questions[' + i + '][question_text]', q.question_text);
            fd.append('questions[' + i + '][points]', String(q.points));
            fd.append('questions[' + i + '][image_src]', q.image_src);
            fd.append('questions[' + i + '][audio_src]', q.audio_src);
            fd.append('questions[' + i + '][correct_index]', String(q.correct_index));
            q.answers.forEach(function (a, j) {
                fd.append('questions[' + i + '][answers][' + j + '][text]', a.text);
            });
        });
    }

    function loadCoExamsTable() {
        var fd = new FormData();
        fd.append('action', 'get_exams_admin');
        fetch(CO_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j || !j.success || !j.data) {
                    toast((j && j.message) || 'Erreur chargement compréhension orale', true);
                    return;
                }
                renderCoExamsTable(j.data);
            })
            .catch(function () { toast('Erreur chargement compréhension orale', true); });
    }

    function renderCoExamsTable(rows) {
        var tb = document.querySelector('#topics-table tbody');
        if (!tb) return;
        if (!rows || !rows.length) {
            tb.innerHTML = '<tr><td colspan="6" style="padding:12px;color:var(--sa-muted);">Aucune épreuve.</td></tr>';
            return;
        }
        tb.innerHTML = rows.map(function (e) {
            var effectiveVis = String(e.effective_visibility || e.visibility || 'gratuit');
            var vis = Number(e.is_published || 0) === 1 ? effectiveVis : 'brouillon';
            var uses = Number(e.view_count || 0);
            return '<tr data-id="' + escAttr(String(e.id)) + '">' +
                '<td>' + escHtml(e.title || '') + '</td>' +
                '<td>Compréhension Orale (quiz)</td>' +
                '<td><span class="sa-badge sa-badge--' + (Number(e.is_published || 0) === 1 ? (effectiveVis === 'premium' ? 'premium' : 'gratuit') : 'premium') + '">' + escHtml(vis) + '</span></td>' +
                '<td>' + escHtml(e.published_at || e.created_at || '') + '</td>' +
                '<td>' + escHtml(String(uses)) + '</td>' +
                saActionsRow('<button type="button" class="btn btn-outline btn-sm sa-btn-icon sa-topic-edit" aria-label="Modifier"><i class="bx bx-edit-alt" aria-hidden="true"></i></button><button type="button" class="btn btn-outline btn-sm sa-btn-icon btn-danger-outline sa-topic-del" aria-label="Supprimer"><i class="bx bx-trash" aria-hidden="true"></i></button>') +
                '</tr>';
        }).join('');
    }

    function openCoExamForm(examId) {
        var jsonForm = document.getElementById('co-exam-json-form');
        if (jsonForm) jsonForm.style.display = 'none';
        var form = document.getElementById('co-exam-form');
        if (!form) return;
        form.style.display = 'block';
        updateTopicTopActions();
        try {
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (e2) {}
        var idEl = document.getElementById('co-exam-id');
        var titleEl = document.getElementById('co-exam-title');
        var visEl = document.getElementById('co-exam-visibility');
        var pubEl = document.getElementById('co-exam-published');
        var durEl = document.getElementById('co-duration-minutes');
        if (!idEl || !titleEl) return;
        if (!examId) {
            idEl.value = '';
            titleEl.value = '';
            if (visEl) visEl.value = 'gratuit';
            if (pubEl) pubEl.checked = true;
            if (durEl) durEl.value = '30';
            resetCoQuestions(1);
            return;
        }
        idEl.value = String(examId);
        var fd = new FormData();
        fd.append('action', 'get_exam_for_edit');
        fd.append('exam_id', examId);
        fetch(CO_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j || !j.success || !j.data) {
                    toast((j && j.message) || 'Chargement impossible', true);
                    return;
                }
                var d = j.data;
                titleEl.value = d.title || '';
                if (visEl) visEl.value = (d.visibility === 'premium' ? 'premium' : 'gratuit');
                if (pubEl) pubEl.checked = Number(d.is_published || 0) === 1;
                var sec = Number(d.duration_seconds || 1800);
                if (durEl) durEl.value = String(Math.max(1, Math.round(sec / 60)));
                var qs = d.quiz_questions || [];
                var wrap = document.getElementById('co-questions-wrap');
                if (!wrap) return;
                wrap.innerHTML = '';
                if (!qs.length) {
                    resetCoQuestions(1);
                    return;
                }
                qs.forEach(function (q, idx) {
                    wrap.insertAdjacentHTML('beforeend', coQuestionTemplate(idx + 1));
                    var blk = wrap.querySelectorAll('[data-co-q]')[idx];
                    if (!blk) return;
                    var tx = blk.querySelector('[data-co-q-text]');
                    if (tx) tx.value = q.question_text || '';
                    var pt = blk.querySelector('[data-co-q-points]');
                    if (pt) pt.value = String(q.points != null ? q.points : 1);
                    var im = blk.querySelector('[data-co-q-image]');
                    if (im) im.value = q.image_src || '';
                    var au = blk.querySelector('[data-co-q-audio]');
                    if (au) au.value = q.audio_src || '';
                    var sel = blk.querySelector('[data-co-correct]');
                    if (sel) sel.value = String(Math.min(3, Math.max(0, Number(q.correct_index || 0))));
                    var ans = q.answers || [];
                    for (var ai = 0; ai < 4; ai++) {
                        var inp = blk.querySelector('[data-co-ans="' + ai + '"]');
                        if (inp && ans[ai]) inp.value = ans[ai].text || '';
                    }
                });
                refreshCoQuestionLabels();
                wrap.querySelectorAll('[data-co-q]').forEach(function (b) {
                    refreshCoQuestionMediaPreviews(b);
                });
            })
            .catch(function () { toast('Erreur réseau', true); });
    }

    function initCoExamFormUi() {
        var form = document.getElementById('co-exam-form');
        var addBtn = document.getElementById('co-add-question-btn');
        var wrap = document.getElementById('co-questions-wrap');
        var cancelBtn = document.getElementById('co-cancel-btn');
        var topSaveBtn = document.getElementById('topic-save-top-btn');
        var topCancelBtn = document.getElementById('topic-cancel-top-btn');
        if (!form || !wrap) return;
        if (addBtn) {
            addBtn.addEventListener('click', function () {
                wrap.insertAdjacentHTML('beforeend', coQuestionTemplate($all('[data-co-q]').length + 1));
                refreshCoQuestionLabels();
                var last = wrap.querySelector('[data-co-q]:last-of-type');
                if (last) refreshCoQuestionMediaPreviews(last);
            });
        }
        wrap.addEventListener('click', function (e) {
            var rm = e.target.closest && e.target.closest('[data-co-remove-q]');
            if (!rm) return;
            var blk = rm.closest('[data-co-q]');
            if (!blk) return;
            if ($all('[data-co-q]').length <= 1) {
                toast('Au moins une question est requise.', true);
                return;
            }
            blk.remove();
            refreshCoQuestionLabels();
        });
        wrap.addEventListener('change', function (e) {
            var imgIn = e.target.closest && e.target.closest('[data-co-img-file]');
            if (imgIn && imgIn.files && imgIn.files[0]) {
                var blk = imgIn.closest('[data-co-q]');
                var f = imgIn.files[0];
                uploadCoMediaFile(f, 'image', function (j) {
                    if (!j || !j.success) {
                        toast((j && j.message) || "Échec de l'import d'image", true);
                        imgIn.value = '';
                        return;
                    }
                    var t = blk && blk.querySelector('[data-co-q-image]');
                    if (t) t.value = j.path || '';
                    refreshCoQuestionMediaPreviews(blk);
                    toast('Image importée — aperçu ci-dessus');
                    imgIn.value = '';
                });
                return;
            }
            var audIn = e.target.closest && e.target.closest('[data-co-aud-file]');
            if (audIn && audIn.files && audIn.files[0]) {
                var blk2 = audIn.closest('[data-co-q]');
                var f2 = audIn.files[0];
                uploadCoMediaFile(f2, 'audio', function (j) {
                    if (!j || !j.success) {
                        toast((j && j.message) || "Échec de l'import audio", true);
                        audIn.value = '';
                        return;
                    }
                    var t2 = blk2 && blk2.querySelector('[data-co-q-audio]');
                    if (t2) t2.value = j.path || '';
                    refreshCoQuestionMediaPreviews(blk2);
                    toast('Audio importé — lecteur ci-dessous');
                    audIn.value = '';
                });
            }
        });
        wrap.addEventListener('input', function (e) {
            var t = e.target;
            if (t && t.matches && (t.matches('[data-co-q-image]') || t.matches('[data-co-q-audio]'))) {
                refreshCoQuestionMediaPreviews(t.closest('[data-co-q]'));
            }
        });
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function () {
                form.style.display = 'none';
                var jf = document.getElementById('co-exam-json-form');
                if (jf) jf.style.display = 'none';
                updateTopicTopActions();
            });
        }
        if (topCancelBtn) {
            topCancelBtn.addEventListener('click', function () {
                if ((TOPIC_SECTIONS[state.topicsTarget || 'topics-written'] || {}).type !== 'Compréhension Orale') return;
                form.style.display = 'none';
                var jf = document.getElementById('co-exam-json-form');
                if (jf) jf.style.display = 'none';
                updateTopicTopActions();
            });
        }
        if (topSaveBtn) {
            topSaveBtn.addEventListener('click', function () {
                if ((TOPIC_SECTIONS[state.topicsTarget || 'topics-written'] || {}).type !== 'Compréhension Orale') return;
                var jf = document.getElementById('co-exam-json-form');
                if (jf && jf.style.display !== 'none') {
                    jf.requestSubmit();
                    return;
                }
                form.requestSubmit();
            });
        }
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var examId = (document.getElementById('co-exam-id').value || '').trim();
            var title = (document.getElementById('co-exam-title').value || '').trim();
            if (!title) {
                toast("Le titre de l'épreuve est requis.", true);
                return;
            }
            var payload = collectCoQuestionsPayload();
            var ok = true;
            payload.forEach(function (q, qi) {
                if (!q.question_text) {
                    toast('Question ' + (qi + 1) + ' : énoncé obligatoire.', true);
                    ok = false;
                    return;
                }
                var filled = q.answers.filter(function (a) { return a.text.length > 0; }).length;
                if (filled < 2) {
                    toast('Question ' + (qi + 1) + ' : au moins deux réponses renseignées.', true);
                    ok = false;
                    return;
                }
                var ci = q.correct_index;
                if (!q.answers[ci] || !q.answers[ci].text.trim()) {
                    toast('Question ' + (qi + 1) + ' : la bonne réponse doit avoir un texte.', true);
                    ok = false;
                }
            });
            if (!ok) return;
            var mins = parseInt(document.getElementById('co-duration-minutes').value, 10);
            if (!mins || mins < 1) mins = 30;
            var durSec = Math.min(86400, Math.max(60, mins * 60));
            var fd = new FormData();
            fd.append('action', 'save_exam');
            if (examId) fd.append('exam_id', examId);
            fd.append('title', title);
            fd.append('subtitle', '');
            fd.append('intro_html', '');
            fd.append('visibility', document.getElementById('co-exam-visibility').value || 'gratuit');
            fd.append('is_published', document.getElementById('co-exam-published').checked ? '1' : '0');
            fd.append('duration_seconds', String(durSec));
            appendCoQuestionsToFormData(fd, payload);
            fetch(CO_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (j) {
                    if (j && j.success) {
                        toast(j.message || 'Épreuve enregistrée');
                        form.style.display = 'none';
                        updateTopicTopActions();
                        loadCoExamsTable();
                    } else {
                        toast((j && j.message) || 'Erreur', true);
                    }
                })
                .catch(function () { toast('Erreur réseau', true); });
        });
    }

    function loadEoConsignesTable() {
        var fd = new FormData();
        fd.append('action', 'get_consignes_bundle_admin');
        fetch(EO_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j || !j.success || !j.data) return toast((j && j.message) || 'Erreur chargement consignes orales', true);
                state.eoConsignesCache = j.data;
                var d = j.data || {};
                if (document.getElementById('eo-consigne-tache1')) document.getElementById('eo-consigne-tache1').value = d.tache1 || '';
                if (document.getElementById('eo-consigne-tache2')) document.getElementById('eo-consigne-tache2').value = d.tache2 || '';
                if (document.getElementById('eo-consigne-tache3')) document.getElementById('eo-consigne-tache3').value = d.tache3 || '';
                if (document.getElementById('eo-consigne-status')) document.getElementById('eo-consigne-status').value = Number(d.is_published || 0) === 1 ? '1' : '0';
            })
            .catch(function () { toast('Erreur chargement consignes orales', true); });
    }

    function initEoConsignesUi() {
        var form = document.getElementById('eo-consignes-bundle-form');
        var openBtn = document.getElementById('eo-open-consignes-btn');
        var cancelBtn = document.getElementById('eo-consigne-cancel-btn');
        var submitBtn = document.getElementById('eo-consigne-submit-btn');
        var status = document.getElementById('eo-consigne-status');
        if (!form) return;
        function refreshLabel() {
            if (!submitBtn || !status) return;
            submitBtn.textContent = status.value === '1' ? 'Publier' : 'Non publier';
        }
        if (openBtn) openBtn.addEventListener('click', function () { form.style.display = 'block'; loadEoConsignesTable(); });
        if (cancelBtn) cancelBtn.addEventListener('click', function () { form.style.display = 'none'; });
        if (status) status.addEventListener('change', refreshLabel);
        refreshLabel();
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var t1 = (document.getElementById('eo-consigne-tache1').value || '').trim();
            var t2 = (document.getElementById('eo-consigne-tache2').value || '').trim();
            var t3 = (document.getElementById('eo-consigne-tache3').value || '').trim();
            if (!t1 || !t2 || !t3) return toast('Veuillez renseigner les consignes des 3 tâches.', true);
            var fd = new FormData();
            fd.append('action', 'save_consignes_bundle');
            fd.append('tache1', t1);
            fd.append('tache2', t2);
            fd.append('tache3', t3);
            fd.append('is_published', (document.getElementById('eo-consigne-status').value || '1') === '1' ? '1' : '0');
            fetch(EO_ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (j) {
                    if (j && j.success) {
                        toast(j.message || 'Consignes orales enregistrées');
                        loadEoConsignesTable();
                    } else {
                        toast((j && j.message) || 'Erreur', true);
                    }
                })
                .catch(function () { toast('Erreur réseau', true); });
        });
    }

    // ---------------- Dashboard / Stats / Traceability / Activities ----------------
    // Note: on réutilise les IDs existants ; charts/leaflet sont initialisés uniquement à l’entrée dashboard.
    function chartTextColor() {
        var t = document.documentElement.getAttribute('data-sa-theme');
        if (t === 'dark') {
            return '#eef0f4';
        }
        return '#141622';
    }

    /** Même logique d’échelles que le graphique « Performances des vidéos » (barres verticales). */
    function traceVideoStyleScalesVertical(tc, yTickFormatter) {
        return {
            x: {
                ticks: { color: tc, maxRotation: 45, autoSkip: true, maxTicksLimit: 14 },
                grid: { display: false }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    color: tc,
                    maxTicksLimit: 8,
                    callback: yTickFormatter
                        ? function (val) {
                              return yTickFormatter(val);
                          }
                        : function (val) {
                              var n = Number(val);
                              if (!isFinite(n)) return '';
                              if (Math.abs(n - Math.round(n)) < 1e-6) return String(Math.round(n));
                              return String(Number(n.toFixed(1)));
                          }
                }
            }
        };
    }

    function traceFormatPeriodLabel(raw) {
        if (raw == null || raw === '') return '';
        var s = String(raw).trim();
        var dm = s.match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (dm) return dm[3] + '/' + dm[2];
        var mm = s.match(/^(\d{4})-(\d{2})$/);
        if (mm) {
            var months = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
            var mi = parseInt(mm[2], 10) - 1;
            return (months[mi] || mm[2]) + ' ' + mm[1];
        }
        return s;
    }

    function traceCoerceSeries(rawLabels, rawValues) {
        var la = rawLabels || [];
        var va = rawValues || [];
        var n = Math.max(la.length, va.length);
        var outL = [];
        var outV = [];
        var i;
        for (i = 0; i < n; i++) {
            outL.push(la[i] != null && la[i] !== '' ? traceFormatPeriodLabel(la[i]) : '—');
            var v = va[i];
            var num = v == null || v === '' ? 0 : Number(v);
            outV.push(isFinite(num) ? num : 0);
        }
        return { labels: outL, values: outV };
    }

    function traceMetricLegendKey(m) {
        if (m === 'users') return 'Inscriptions';
        if (m === 'subs') return 'Paiements complétés';
        if (m === 'revenue') return 'Revenus';
        return 'Visites';
    }

    function traceYFormatterForMetric(m) {
        if (m === 'revenue') {
            return function (val) {
                var n = Number(val);
                if (!isFinite(n)) return '';
                var a = Math.abs(n);
                if (a >= 1000) return '$' + (n / 1000).toFixed(a >= 10000 ? 0 : 1).replace(/\.0$/, '') + 'k';
                if (a >= 1) return '$' + Math.round(n).toString();
                return '$' + n.toFixed(2);
            };
        }
        return function (val) {
            var n = Number(val);
            if (!isFinite(n)) return '';
            if (Math.abs(n - Math.round(n)) < 1e-6) return String(Math.round(n));
            return String(Math.round(n * 10) / 10);
        };
    }

    function applyTraceMetric(d) {
        var metricEl = document.getElementById('trace-metric');
        var m = metricEl ? metricEl.value : 'visits';
        var rawL;
        var rawV;
        if (m === 'users') {
            rawL = d.users_labels;
            rawV = d.users_values;
        } else if (m === 'subs') {
            rawL = d.payments_count_labels;
            rawV = d.payments_count_values;
        } else if (m === 'revenue') {
            rawL = d.revenue_labels;
            rawV = d.revenue_values;
        } else {
            rawL = d.visits_labels;
            rawV = d.visits_values;
        }
        var series = traceCoerceSeries(rawL, rawV);
        var labels = series.labels;
        var values = series.values;
        if (!labels.length) {
            labels = ['—'];
            values = [0];
        }
        destroyTraceChart('time');
        var canvas = document.getElementById('traceTimeChart');
        if (!canvas || typeof Chart === 'undefined') return;
        var tc = chartTextColor();
        var maxVal = values.length ? Math.max.apply(null, values) : 0;
        var yScaleExtra = {};
        if (maxVal === 0) {
            yScaleExtra.suggestedMax = m === 'revenue' ? 10 : 5;
        }
        var yFmt = traceYFormatterForMetric(m);
        var scales = traceVideoStyleScalesVertical(tc, yFmt);
        scales.y = Object.assign({}, scales.y, yScaleExtra);
        state.trace.charts.time = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: traceMetricLegendKey(m),
                        data: values,
                        backgroundColor: 'rgba(211,13,13,0.7)',
                        borderColor: 'rgba(176,11,11,0.55)',
                        borderWidth: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: tc } } },
                scales: scales
            }
        });
    }

    function buildTraceabilityTrafficChart(canvasId, rows) {
        destroyTraceChart(canvasId);
        var canvas = document.getElementById(canvasId);
        if (!canvas || typeof Chart === 'undefined') return;
        var labels = (rows || []).map(function (r) {
            return (r.src && String(r.src).trim()) || '—';
        });
        var data = (rows || []).map(function (r) {
            var n = Number(r.c);
            return isFinite(n) ? n : parseInt(r.c, 10) || 0;
        });
        if (!labels.length) {
            labels = ['—'];
            data = [0];
        }
        var tc = chartTextColor();
        var maxD = data.length ? Math.max.apply(null, data) : 0;
        var yExtra = maxD === 0 ? { suggestedMax: 5 } : {};
        var scales = traceVideoStyleScalesVertical(tc, null);
        scales.y = Object.assign({}, scales.y, yExtra);
        state.trace.charts[canvasId] = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Nombre',
                        data: data,
                        backgroundColor: 'rgba(211,13,13,0.7)',
                        borderColor: 'rgba(176,11,11,0.55)',
                        borderWidth: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: scales
            }
        });
    }

    function buildTraceabilityCountriesChart(d, mode) {
        destroyTraceChart('countries');
        var list = mode === 'signups' ? d.signup_countries || [] : d.visit_countries || [];
        var title = document.getElementById('trace-countries-title');
        if (title) {
            title.textContent =
                mode === 'signups' ? 'Répartition par pays — inscriptions' : 'Répartition par pays — visites';
        }
        var canvas = document.getElementById('traceCountriesChart');
        if (!canvas || typeof Chart === 'undefined') return;
        var labels = list.slice(0, 12).map(function (r) {
            return r.name || r.code || '—';
        });
        var values = list.slice(0, 12).map(function (r) {
            var n = Number(r.c);
            return isFinite(n) ? n : parseInt(r.c, 10) || 0;
        });
        if (!labels.length) {
            labels = ['—'];
            values = [0];
        }
        var tc = chartTextColor();
        var maxV = values.length ? Math.max.apply(null, values) : 0;
        var xExtra = maxV === 0 ? { suggestedMax: 5 } : {};
        state.trace.charts.countries = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Nombre',
                        data: values,
                        backgroundColor: 'rgba(211,13,13,0.7)',
                        borderColor: 'rgba(176,11,11,0.55)',
                        borderWidth: 0
                    }
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: Object.assign(
                        {
                            beginAtZero: true,
                            ticks: {
                                color: tc,
                                maxTicksLimit: 8,
                                callback: function (val) {
                                    var n = Number(val);
                                    if (!isFinite(n)) return '';
                                    if (Math.abs(n - Math.round(n)) < 1e-6) return String(Math.round(n));
                                    return String(Math.round(n * 10) / 10);
                                }
                            },
                            grid: { display: false }
                        },
                        xExtra
                    ),
                    y: { ticks: { color: tc }, grid: { display: false } }
                }
            }
        });
    }

    function updateTraceabilityMap(d) {
        var el = document.getElementById('traceMapGeo');
        if (!el || typeof L === 'undefined') return;
        var geoSel = document.getElementById('trace-geo-mode');
        var mode = geoSel ? geoSel.value : 'visits';
        var list = mode === 'signups' ? d.signup_countries || [] : d.visit_countries || [];
        if (state.trace.map) {
            try {
                state.trace.map.remove();
            } catch (e) {}
            state.trace.map = null;
        }
        state.trace.map = L.map(el, { worldCopyJump: true }).setView([20, 0], 2);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(state.trace.map);
        var maxC = 1;
        list.forEach(function (r) {
            maxC = Math.max(maxC, parseInt(r.c, 10) || 0);
        });
        list.forEach(function (r) {
            var lat = parseFloat(r.lat);
            var lon = parseFloat(r.lon);
            if (!isFinite(lat) || !isFinite(lon)) return;
            var c = parseInt(r.c, 10) || 0;
            var rad = 6 + Math.min(28, (c / maxC) * 28);
            L.circleMarker([lat, lon], {
                radius: rad,
                color: '#d30d0d',
                fillColor: '#d30d0d',
                fillOpacity: 0.35,
                weight: 1
            })
                .addTo(state.trace.map)
                .bindPopup(escHtml(r.name || r.code || '') + ': <strong>' + c + '</strong>');
        });
        window.setTimeout(function () {
            if (state.trace.map) state.trace.map.invalidateSize();
        }, 200);
    }

    function refreshStats() {
        postForm('get_stats')
            .then(function (j) {
                if (!j || !j.success || !j.data) return;
                var d = j.data;
                var u = document.getElementById('users-count');
                var vis = document.getElementById('visitors-count');
                var subs = document.getElementById('subs-count');
                var rev = document.getElementById('revenue-count');
                if (u) u.textContent = String(d.users || 0);
                if (vis) vis.textContent = String(d.visitors || 0);
                if (subs) subs.textContent = String(d.subs || 0);
                if (rev) rev.textContent = '$' + (Number(d.revenue || 0).toFixed(2));
            })
            .catch(function () {});
    }

    function renderSaPlanCatalogCard(p) {
        var feats = Array.isArray(p.features) ? p.features.join('\n') : '';
        var id = escAttr(String(p.id));
        var key = escHtml(String(p.key || ''));
        var dur = p.duration_days != null ? String(p.duration_days) : '7';
        var price = p.price != null ? String(p.price) : '0';
        var active = p.is_active === 1 || p.is_active === true ? ' checked' : '';
        var isOn = p.is_active === 1 || p.is_active === true;
        return (
            '<article class="sa-plan-userlike" data-plan-id="' +
            id +
            '">' +
            '<div class="sa-plan-userlike__head">' +
            '<span class="sa-plan-userlike__key" title="Clé interne">' +
            key +
            '</span>' +
            '<span class="sa-plan-userlike__status' +
            (isOn ? ' is-on' : '') +
            '">' +
            (isOn ? 'Visible' : 'Masqué') +
            '</span>' +
            '<input type="text" class="sa-plan-userlike__tier sa-plan-tier-input" value="' +
            escAttr(String(p.tier || '')) +
            '" autocomplete="off" aria-label="Titre de la carte">' +
            '<input type="text" class="sa-plan-userlike__badge sa-plan-badge-input" value="' +
            escAttr(String(p.badge || '')) +
            '" autocomplete="off" aria-label="Badge durée">' +
            '<div class="sa-plan-userlike__price-row">' +
            '<input type="text" class="sa-plan-userlike__currency sa-plan-currency-input" maxlength="8" value="' +
            escAttr(String(p.currency || '$')) +
            '" aria-label="Devise">' +
            '<input type="number" class="sa-plan-userlike__price sa-plan-price-input" min="0" step="0.01" value="' +
            escAttr(price) +
            '" aria-label="Prix">' +
            '</div>' +
            '<div class="sa-plan-userlike__wave" aria-hidden="true">' +
            '<svg viewBox="0 0 400 40" preserveAspectRatio="none"><path d="M0,20 Q100,0 200,20 T400,20 L400,40 L0,40 Z" fill="#141622"/></svg>' +
            '</div></div>' +
            '<div class="sa-plan-userlike__body">' +
            '<div><span class="sa-plan-userlike__label">Avantages (une ligne = un point)</span>' +
            '<textarea class="sa-plan-userlike__features sa-plan-features-input" rows="5">' +
            escHtml(feats) +
            '</textarea></div>' +
            '<div class="sa-plan-userlike__meta">' +
            '<div><span class="sa-plan-userlike__label">Durée (jours)</span>' +
            '<input type="number" class="sa-plan-duration-input" min="1" max="730" step="1" value="' +
            escAttr(dur) +
            '"></div>' +
            '<div><span class="sa-plan-userlike__label">Ordre</span>' +
            '<input type="number" class="sa-plan-sort-input" step="1" value="' +
            escAttr(String(p.sort_order != null ? p.sort_order : 0)) +
            '"></div></div>' +
            '<label class="sa-plan-userlike__toggle"><input type="checkbox" class="sa-plan-active-input"' +
            active +
            '> Afficher sur Abonnement</label>' +
            '<div class="sa-plan-userlike__actions">' +
            '<button type="button" class="btn btn-outline btn-sm sa-plan-delete-btn"><i class="bx bx-trash"></i> Supprimer</button>' +
            '<button type="button" class="btn btn-primary sa-plan-save-btn"><i class="bx bx-save"></i> Enregistrer</button>' +
            '</div></div></article>'
        );
    }

    function collectSaPlanCardPayload(card) {
        var id = card.getAttribute('data-plan-id');
        if (!id) return null;
        var tierEl = card.querySelector('.sa-plan-tier-input');
        var badgeEl = card.querySelector('.sa-plan-badge-input');
        var priceEl = card.querySelector('.sa-plan-price-input');
        var curEl = card.querySelector('.sa-plan-currency-input');
        var durEl = card.querySelector('.sa-plan-duration-input');
        var sortEl = card.querySelector('.sa-plan-sort-input');
        var featEl = card.querySelector('.sa-plan-features-input');
        var actEl = card.querySelector('.sa-plan-active-input');
        return {
            id: id,
            tier: tierEl ? tierEl.value : '',
            badge: badgeEl ? badgeEl.value : '',
            price: priceEl ? priceEl.value : '0',
            currency: curEl ? curEl.value : '$',
            duration_days: durEl ? durEl.value : '7',
            sort_order: sortEl ? sortEl.value : '0',
            is_active: actEl && actEl.checked ? '1' : '0',
            features: featEl ? featEl.value : ''
        };
    }

    function saveSaPlanCard(card) {
        var payload = collectSaPlanCardPayload(card);
        if (!payload) return;
        var btn = card.querySelector('.sa-plan-save-btn');
        if (btn) btn.disabled = true;
        postForm('save_subscription_plan', payload)
            .then(function (j) {
                if (j && j.success) toast(j.message || 'Enregistré');
                else toast((j && j.message) || 'Erreur', true);
                loadSubscriptionPlansAdmin();
            })
            .catch(function () {
                toast('Erreur réseau', true);
            })
            .finally(function () {
                if (btn) btn.disabled = false;
            });
    }

    function loadSubscriptionsPlatformMode() {
        var wrap = document.getElementById('sa-sub-platform-toggle');
        var desc = document.getElementById('sa-sub-platform-toggle-desc');
        var label = document.getElementById('sa-sub-platform-toggle-label');
        var btn = document.getElementById('sa-sub-platform-toggle-btn');
        if (!wrap || !btn) return;
        postForm('get_subscriptions_platform_mode')
            .then(function (j) {
                if (!j || !j.success) {
                    if (desc) desc.textContent = (j && j.message) || 'Impossible de charger le mode abonnements.';
                    return;
                }
                var disabled = !!j.disabled;
                wrap.classList.toggle('is-free-mode', disabled);
                if (desc) desc.textContent = j.message || '';
                if (label) {
                    label.textContent = disabled ? 'Réactiver les abonnements' : 'Désactiver tous les abonnements';
                }
                btn.dataset.disabled = disabled ? '1' : '0';
            })
            .catch(function () {
                if (desc) desc.textContent = 'Erreur réseau.';
            });
    }

    function toggleSubscriptionsPlatformMode() {
        var btn = document.getElementById('sa-sub-platform-toggle-btn');
        if (!btn || btn.disabled) return;
        var currentlyDisabled = btn.dataset.disabled === '1';
        var nextDisabled = !currentlyDisabled;
        var confirmMsg = nextDisabled
            ? 'Désactiver tous les abonnements ? Le contenu premium deviendra gratuit et les cartes d’abonnement disparaîtront côté utilisateur.'
            : 'Réactiver les abonnements ? Les cartes et paiements réapparaîtront ; les abonnements des membres reprennent leur état habituel.';
        if (!window.confirm(confirmMsg)) return;
        btn.disabled = true;
        postForm('set_subscriptions_platform_mode', { disabled: nextDisabled ? '1' : '0' })
            .then(function (j) {
                if (j && j.success) {
                    toast(j.message || 'Mode mis à jour');
                    loadSubscriptionsPlatformMode();
                } else {
                    toast((j && j.message) || 'Erreur', true);
                }
            })
            .catch(function () {
                toast('Erreur réseau', true);
            })
            .finally(function () {
                btn.disabled = false;
            });
    }

    function loadSubscriptionPlansAdmin() {
        loadSubscriptionsPlatformMode();
        var grid = document.getElementById('sa-plan-catalog-grid');
        if (!grid) return;
        grid.innerHTML = '<div class="sa-plan-catalog-loading">Chargement des forfaits…</div>';
        postForm('get_subscription_plans_admin')
            .then(function (j) {
                if (!j || !j.success) {
                    grid.innerHTML =
                        '<div class="sa-plan-catalog-error">' +
                        escHtml((j && j.message) || 'Impossible de charger le catalogue.') +
                        '<span class="sa-plan-orbit-hint">Vérifiez que la base contient la table des forfaits (migration fournie avec le projet).</span></div>';
                    return;
                }
                var list = j.data || [];
                if (!list.length) {
                    grid.innerHTML =
                        '<div class="sa-sub-pro-empty"><p>Aucun forfait enregistré.</p><p class="sa-sub-pro-empty-hint">Utilisez « Ajouter un forfait » pour créer votre première offre.</p></div>';
                    return;
                }
                grid.innerHTML = list.map(renderSaPlanCatalogCard).join('');
                $all('.sa-plan-save-btn', grid).forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var c = btn.closest('.sa-plan-userlike, .sa-plan-pro-card, .sa-plan-orbit-card');
                        if (c) saveSaPlanCard(c);
                    });
                });
                $all('.sa-plan-delete-btn', grid).forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var c = btn.closest('.sa-plan-userlike, .sa-plan-pro-card, .sa-plan-orbit-card');
                        var id = c ? c.getAttribute('data-plan-id') : null;
                        if (!id) return;
                        if (
                            !window.confirm(
                                'Supprimer définitivement ce forfait ? Cette action est irréversible si aucun membre n’y est rattaché.'
                            )
                        ) {
                            return;
                        }
                        btn.disabled = true;
                        postForm('delete_subscription_plan', { id: id })
                            .then(function (dj) {
                                if (dj && dj.success) {
                                    toast(dj.message || 'Forfait supprimé');
                                    loadSubscriptionPlansAdmin();
                                } else {
                                    toast((dj && dj.message) || 'Erreur', true);
                                }
                            })
                            .catch(function () {
                                toast('Erreur réseau', true);
                            })
                            .finally(function () {
                                btn.disabled = false;
                            });
                    });
                });
            })
            .catch(function () {
                grid.innerHTML = '<div class="sa-plan-catalog-error">Erreur réseau.</div>';
            });
    }

    function buildSubscriptionRevenueChart(chartData) {
        var canvas = document.getElementById('sa-subrev-revenue-chart');
        if (!canvas || typeof Chart === 'undefined') {
            return;
        }
        if (state.subscriptionRevChart) {
            state.subscriptionRevChart.destroy();
            state.subscriptionRevChart = null;
        }
        var labels = (chartData && chartData.labels) || [];
        var values = (chartData && chartData.values) || [];
        if (!labels.length) {
            return;
        }
        var tc = chartTextColor();
        state.subscriptionRevChart = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Revenus ($)',
                        data: values,
                        borderColor: 'rgba(211, 13, 13, 0.95)',
                        backgroundColor: 'rgba(211, 13, 13, 0.12)',
                        fill: true,
                        tension: 0.25,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: {
                        display: true,
                        labels: { color: tc }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                var v = ctx.parsed.y;
                                return ' ' + (v != null ? '$' + Number(v).toFixed(2) : '');
                            }
                        }
                    }
                },
                scales: traceVideoStyleScalesVertical(tc, function (val) {
                    return '$' + Number(val).toFixed(0);
                })
            }
        });
    }

    function loadSubscriptionRevenueStatsAdmin() {
        postForm('get_subscription_revenue_stats')
            .then(function (j) {
                var d = j && j.data ? j.data : null;
                if (!d) {
                    if (j && j.message) toast(j.message, true);
                    return;
                }
                var t = document.getElementById('sa-subrev-total');
                var m = document.getElementById('sa-subrev-month');
                var c = document.getElementById('sa-subrev-count');
                if (t) t.textContent = '$' + Number(d.total_revenue || 0).toFixed(2);
                if (m) m.textContent = '$' + Number(d.month_revenue || 0).toFixed(2);
                if (c) c.textContent = String(d.transactions || 0);
                if (j && !j.success && j.message) {
                    toast(j.message, true);
                }
                buildSubscriptionRevenueChart(d.chart);
            })
            .catch(function () {});
    }

    function loadSubscriptionPaymentsAdmin() {
        postForm('get_subscription_payments')
            .then(function (j) {
                var tb = document.getElementById('sa-subscription-payments-tbody');
                if (!tb) return;
                if (!j || !j.success) {
                    tb.innerHTML =
                        '<tr><td colspan="6" style="padding:12px;color:#b91c1c;">' +
                        escHtml((j && j.message) || 'Impossible de charger l’historique des paiements.') +
                        '</td></tr>';
                    return;
                }
                var rows = j && j.success && j.data ? j.data : [];
                if (!rows.length) {
                    tb.innerHTML =
                        '<tr><td colspan="6" style="padding:12px;color:var(--sa-muted);">Aucune transaction pour le moment. Les paiements effectués depuis la page Abonnement apparaîtront ici.</td></tr>';
                    return;
                }
                tb.innerHTML = rows
                    .map(function (r) {
                        return (
                            '<tr><td>' +
                            escHtml(r.created_at || '') +
                            '</td><td>' +
                            escHtml(r.user_name || '—') +
                            '</td><td>' +
                            escHtml(r.user_email || '—') +
                            '</td><td>' +
                            escHtml((r.plan_label || r.plan_key || '') + '') +
                            '</td><td>' +
                            escHtml(String(r.amount != null ? r.amount : '')) +
                            ' ' +
                            escHtml(r.currency || 'USD') +
                            '</td><td>' +
                            escHtml(r.payment_method || '') +
                            '</td></tr>'
                        );
                    })
                    .join('');
            })
            .catch(function () {
                var tb2 = document.getElementById('sa-subscription-payments-tbody');
                if (tb2) {
                    tb2.innerHTML =
                        '<tr><td colspan="6" style="padding:12px;color:#b91c1c;">Impossible de charger l’historique.</td></tr>';
                }
            });
    }

    function activityTypeLabelFr(type) {
        var t = (type || '').toLowerCase();
        if (t === 'user') return 'Utilisateur';
        if (t === 'video') return 'Vidéo & chaîne';
        if (t === 'topic') return 'Sujet';
        if (t === 'message') return 'Message / publication';
        if (t === 'admin') return 'Administration';
        if (t === 'subscription') return 'Abonnements';
        return t || 'Autre';
    }

    function activityParseDate(iso) {
        if (!iso) return null;
        if (window.moment) {
            var m = moment(String(iso));
            return m.isValid() ? m : null;
        }
        var d = new Date(String(iso).replace(' ', 'T'));
        return isNaN(d.getTime()) ? null : d;
    }

    function activityDayKeyFromRow(a) {
        var m = activityParseDate(a.created_at);
        if (!m) return 'unknown';
        if (window.moment && m.format) {
            return m.format('YYYY-MM-DD');
        }
        var mo = m.getMonth() + 1;
        var da = m.getDate();
        return m.getFullYear() + '-' + (mo < 10 ? '0' : '') + mo + '-' + (da < 10 ? '0' : '') + da;
    }

    function activityDayTitleFromKey(key) {
        if (key === 'unknown') return 'Date inconnue';
        if (window.moment) {
            var d = moment(key, 'YYYY-MM-DD');
            if (!d.isValid()) return key;
            if (d.isSame(moment(), 'day')) return "Aujourd'hui";
            if (d.isSame(moment().subtract(1, 'day'), 'day')) return 'Hier';
            return d.format('dddd D MMMM YYYY');
        }
        var parts = key.split('-');
        if (parts.length !== 3) return key;
        var dt = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
        return dt.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    }

    function activityTimeOnly(iso) {
        var m = activityParseDate(iso);
        if (!m) return '';
        if (window.moment && m.format) {
            return m.format('HH:mm');
        }
        return String(iso).slice(11, 16) || '';
    }

    function activityActorLine(a) {
        var name = (a.user_name || '').trim();
        var em = (a.user_email || '').trim();
        if (name && em) return name + ' · ' + em;
        return name || em || 'Auteur inconnu';
    }

    function filterActivityRows(rows) {
        var typeF = (document.getElementById('sa-activity-filter-type') || {}).value || '';
        var q = ((document.getElementById('sa-activity-search') || {}).value || '').trim().toLowerCase();
        return (rows || []).filter(function (a) {
            if (typeF && String(a.type || '').toLowerCase() !== typeF) {
                return false;
            }
            if (!q) return true;
            var blob = (
                (a.title || '') +
                ' ' +
                (a.description || '') +
                ' ' +
                (a.user_name || '') +
                ' ' +
                (a.user_email || '') +
                ' ' +
                (a.type || '')
            ).toLowerCase();
            return blob.indexOf(q) !== -1;
        });
    }

    function renderActivitySummary(totalRaw, filtered) {
        var el = document.getElementById('sa-activity-summary');
        if (!el) return;
        if (!totalRaw) {
            el.innerHTML = '';
            return;
        }
        var counts = {};
        totalRaw.forEach(function (a) {
            var t = String(a.type || 'other').toLowerCase();
            counts[t] = (counts[t] || 0) + 1;
        });
        var chips = ['user', 'video', 'topic', 'message', 'admin', 'subscription']
            .map(function (t) {
                var n = counts[t] || 0;
                if (!n) return '';
                return (
                    '<span class="sa-activity-stat-chip sa-activity-stat-chip--' +
                    escAttr(t) +
                    '"><i class="bx bx-category-alt" aria-hidden="true"></i> ' +
                    escHtml(activityTypeLabelFr(t)) +
                    ' <strong>' +
                    escHtml(String(n)) +
                    '</strong></span>'
                );
            })
            .join('');
        var filtMsg =
            filtered.length !== totalRaw.length
                ? '<span class="sa-activity-stat-filtered">' +
                  escHtml(String(filtered.length)) +
                  ' affiché(s) sur ' +
                  escHtml(String(totalRaw.length)) +
                  '</span>'
                : '<span class="sa-activity-stat-filtered">' +
                  escHtml(String(totalRaw.length)) +
                  ' événement(s)</span>';
        el.innerHTML =
            '<div class="sa-activity-summary-inner">' +
            filtMsg +
            (chips ? '<div class="sa-activity-stat-chips">' + chips + '</div>' : '') +
            '</div>';
    }

    function renderActivityFeed(rows) {
        var feed = document.getElementById('activity-feed');
        if (!feed) return;
        renderActivitySummary(state.activityFeedRaw, rows);
        if (!rows || !rows.length) {
            feed.innerHTML =
                '<div class="sa-activity-empty"><i class="bx bx-info-circle" aria-hidden="true"></i> Aucune activité ne correspond à votre recherche.</div>';
            return;
        }
        var byDay = {};
        var order = [];
        rows.forEach(function (a) {
            var k = activityDayKeyFromRow(a);
            if (!byDay[k]) {
                byDay[k] = [];
                order.push(k);
            }
            byDay[k].push(a);
        });
        feed.innerHTML = order
            .map(function (dayKey) {
                var list = byDay[dayKey] || [];
                var title = activityDayTitleFromKey(dayKey);
                var cards = list
                    .map(function (a) {
                        var iconClass = (a.icon || 'bx bx-bell').trim();
                        if (iconClass.indexOf('bx') === -1) {
                            iconClass = 'bx ' + iconClass;
                        }
                        var type = String(a.type || 'other').toLowerCase();
                        var desc = (a.description || '').trim();
                        return (
                            '<article class="sa-activity-card sa-activity-card--' +
                            escAttr(type) +
                            '">' +
                            '<div class="sa-activity-card-icon" aria-hidden="true"><i class="' +
                            escAttr(iconClass) +
                            '"></i></div>' +
                            '<div class="sa-activity-card-body">' +
                            '<div class="sa-activity-card-row">' +
                            '<span class="sa-activity-type-pill">' +
                            escHtml(activityTypeLabelFr(type)) +
                            '</span>' +
                            '<time class="sa-activity-time" datetime="' +
                            escAttr(String(a.created_at || '')) +
                            '">' +
                            escHtml(activityTimeOnly(a.created_at)) +
                            '</time>' +
                            '</div>' +
                            '<h3 class="sa-activity-card-title">' +
                            escHtml(a.title || 'Sans titre') +
                            '</h3>' +
                            (desc
                                ? '<p class="sa-activity-card-desc">' + escHtml(desc) + '</p>'
                                : '') +
                            '<footer class="sa-activity-card-meta"><i class="bx bx-user-circle" aria-hidden="true"></i> ' +
                            escHtml(activityActorLine(a)) +
                            '</footer>' +
                            '</div></article>'
                        );
                    })
                    .join('');
                return (
                    '<section class="sa-activity-day" data-day="' +
                    escAttr(dayKey) +
                    '">' +
                    '<header class="sa-activity-day-head">' +
                    '<span class="sa-activity-day-dot" aria-hidden="true"></span>' +
                    '<h2 class="sa-activity-day-title">' +
                    escHtml(title) +
                    '</h2>' +
                    '<span class="sa-activity-day-badge">' +
                    escHtml(String(list.length)) +
                    '</span>' +
                    '</header>' +
                    '<div class="sa-activity-timeline-rail">' +
                    '<div class="sa-activity-timeline-line" aria-hidden="true"></div>' +
                    '<div class="sa-activity-day-cards">' +
                    cards +
                    '</div></div></section>'
                );
            })
            .join('');
    }

    function applyActivityFiltersAndRender() {
        var filtered = filterActivityRows(state.activityFeedRaw);
        renderActivityFeed(filtered);
    }

    function loadActivityFeed() {
        var feed = document.getElementById('activity-feed');
        if (!feed) return;
        feed.innerHTML =
            '<div class="sa-activity-loading"><i class="bx bx-loader-alt bx-spin" aria-hidden="true"></i> Chargement du journal…</div>';
        var sum = document.getElementById('sa-activity-summary');
        if (sum) sum.innerHTML = '';
        postForm('get_activities', { limit: 500 })
            .then(function (j) {
                if (!j || !j.success) {
                    feed.innerHTML =
                        '<div class="sa-activity-empty sa-activity-empty--error"><i class="bx bx-error" aria-hidden="true"></i> Impossible de charger les activités.</div>';
                    return;
                }
                state.activityFeedRaw = j.data || [];
                if (window.moment && moment.locale) {
                    try {
                        moment.locale('fr');
                    } catch (e) {}
                }
                applyActivityFiltersAndRender();
            })
            .catch(function () {
                feed.innerHTML =
                    '<div class="sa-activity-empty sa-activity-empty--error"><i class="bx bx-error" aria-hidden="true"></i> Erreur réseau.</div>';
            });
    }

    var _activitySearchTimer = null;
    function initActivityFeedControls() {
        var typeSel = document.getElementById('sa-activity-filter-type');
        var search = document.getElementById('sa-activity-search');
        var refresh = document.getElementById('sa-activity-refresh');
        if (typeSel && !typeSel._saBound) {
            typeSel._saBound = true;
            typeSel.addEventListener('change', applyActivityFiltersAndRender);
        }
        if (search && !search._saBound) {
            search._saBound = true;
            search.addEventListener('input', function () {
                window.clearTimeout(_activitySearchTimer);
                _activitySearchTimer = window.setTimeout(applyActivityFiltersAndRender, 200);
            });
        }
        if (refresh && !refresh._saBound) {
            refresh._saBound = true;
            refresh.addEventListener('click', function () {
                loadActivityFeed();
            });
        }
    }

    function renderTraceabilityFromData(d) {
        if (!d) return;
        applyTraceMetric(d);
        buildTraceabilityTrafficChart('traceTrafficVisitsChart', d.traffic_visits || []);
        buildTraceabilityTrafficChart('traceTrafficSignupsChart', d.traffic_signups || []);
        updateTraceabilityMap(d);
        var geo = document.getElementById('trace-geo-mode');
        buildTraceabilityCountriesChart(d, geo ? geo.value : 'visits');
    }

    function loadTraceability() {
        var wrap = document.querySelector('#dashboard .trace-panel');
        if (!wrap) return;
        var range = document.getElementById('trace-range');
        postForm('get_traceability', { range: range ? range.value : '30d' })
            .then(function (j) {
                if (!j || !j.success || !j.data) return;
                state.trace.lastTraceability = j.data;
                renderTraceabilityFromData(j.data);
            })
            .catch(function () {});
    }

    function initTraceListeners() {
        var range = document.getElementById('trace-range');
        if (range) range.addEventListener('change', loadTraceability);
        var metric = document.getElementById('trace-metric');
        if (metric) {
            metric.addEventListener('change', function () {
                if (state.trace.lastTraceability) {
                    applyTraceMetric(state.trace.lastTraceability);
                } else {
                    loadTraceability();
                }
            });
        }
        var geo = document.getElementById('trace-geo-mode');
        if (geo) {
            geo.addEventListener('change', function () {
                if (state.trace.lastTraceability) {
                    updateTraceabilityMap(state.trace.lastTraceability);
                    buildTraceabilityCountriesChart(state.trace.lastTraceability, geo.value || 'visits');
                } else {
                    loadTraceability();
                }
            });
        }
    }

    // ---------------- Users/Admins/Messages/Notifications/Analytics ----------------
    // For this implementation step we keep the existing DOM + behaviors by delegating to the legacy module logic
    // that was previously in superAdmin.app.js, but without its own router.
    // To avoid mixing, we re-implement only the needed functions here (CRUD stays functional).

    function renderUsersTable(rows) {
        var tb = document.querySelector('#users-table tbody');
        if (!tb) return;
        if (!rows || !rows.length) {
            tb.innerHTML = '<tr><td colspan="9" style="padding:12px;color:var(--sa-muted);">Aucun utilisateur.</td></tr>';
            return;
        }
        tb.innerHTML = rows
            .map(function (u) {
                var sub = u.subscription_type || 'free';
                var st = u.status || 'active';
                return (
                    '<tr data-id="' +
                    escAttr(String(u.id)) +
                    '" data-user-name="' +
                    escAttr(u.name || '') +
                    '" data-user-email="' +
                    escAttr(u.email || '') +
                    '" data-user-subscription="' +
                    escAttr(sub) +
                    '" data-user-status="' +
                    escAttr(st) +
                    '">' +
                    saAvatarCell(u.avatar_url || '', u.name || '') +
                    '<td>' +
                    escHtml(u.name || '') +
                    '</td><td>' +
                    escHtml(u.email || '') +
                    '</td><td><span class="sa-badge sa-badge--' +
                    escAttr(sub) +
                    '">' +
                    escHtml(sub) +
                    '</span></td><td><span class="sa-badge sa-badge--status-' +
                    escAttr(st) +
                    '">' +
                    escHtml(st) +
                    '</span></td><td>' +
                    escHtml(u.created_at || '') +
                    '</td><td>' +
                    escHtml(String(u.activity_days_count != null ? u.activity_days_count : '—')) +
                    '</td><td>' +
                    escHtml(u.activity_last_date || '—') +
                    '</td>' +
                    saActionsRow(
                        '<button type="button" class="btn btn-outline btn-sm sa-btn-icon sa-user-edit" aria-label="Modifier"><i class="bx bx-edit-alt" aria-hidden="true"></i></button><button type="button" class="btn btn-outline btn-sm sa-btn-icon btn-danger-outline sa-user-del" aria-label="Supprimer"><i class="bx bx-trash" aria-hidden="true"></i></button>'
                    ) +
                    '</tr>'
                );
            })
            .join('');
    }

    function reloadUsers() {
        postForm('get_users')
            .then(function (j) {
                if (!j || !j.success) return;
                renderUsersTable(j.data || []);
            })
            .catch(function () {});
    }

    function initUsersSection() {
        var addBtn = document.getElementById('add-user-btn');
        var modal = document.getElementById('user-modal');
        var form = document.getElementById('user-form-modal');
        if (addBtn && modal) {
            addBtn.addEventListener('click', function () {
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.getElementById('edit-user-id').value = '';
                document.getElementById('user-name').value = '';
                document.getElementById('user-email').value = '';
                document.getElementById('user-subscription').value = 'free';
                document.getElementById('user-status').value = 'active';
                var pwd = document.getElementById('user-password');
                var pwd2 = document.getElementById('user-password-confirm');
                if (pwd) {
                    pwd.value = '';
                    pwd.required = true;
                }
                if (pwd2) {
                    pwd2.value = '';
                    pwd2.required = true;
                }
                $all('.user-password-fields', modal).forEach(function (x) {
                    x.style.display = 'block';
                });
                var title = modal.querySelector('.modal-title');
                if (title) title.textContent = 'Ajouter un utilisateur';
            });
        }
        document.body.addEventListener('click', function (e) {
            var tr = e.target.closest && e.target.closest('#users-table tr[data-id]');
            if (!tr) return;
            var id = tr.getAttribute('data-id');
            if (e.target.closest('.sa-user-del')) {
                if (!id || !window.confirm('Supprimer cet utilisateur ?')) return;
                postForm('delete_user', { id: id })
                    .then(function (j) {
                        if (j && j.success) {
                            toast(j.message || 'Supprimé');
                            reloadUsers();
                        } else {
                            toast((j && j.message) || 'Erreur', true);
                        }
                    })
                    .catch(function () {
                        toast('Erreur réseau', true);
                    });
            }
            if (e.target.closest('.sa-user-edit')) {
                if (!modal) return;
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.getElementById('edit-user-id').value = id;
                document.getElementById('user-name').value = tr.getAttribute('data-user-name') || '';
                document.getElementById('user-email').value = tr.getAttribute('data-user-email') || '';
                var subEl = document.getElementById('user-subscription');
                if (subEl) subEl.value = tr.getAttribute('data-user-subscription') || 'free';
                var stEl = document.getElementById('user-status');
                if (stEl) stEl.value = tr.getAttribute('data-user-status') || 'active';
                var pwdE = document.getElementById('user-password');
                var pwdE2 = document.getElementById('user-password-confirm');
                if (pwdE) {
                    pwdE.value = '';
                    pwdE.required = false;
                }
                if (pwdE2) {
                    pwdE2.value = '';
                    pwdE2.required = false;
                }
                var title2 = modal.querySelector('.modal-title');
                if (title2) title2.textContent = 'Modifier utilisateur';
                $all('.user-password-fields', modal).forEach(function (x) {
                    x.style.display = 'block';
                });
            }
        });
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var editId = document.getElementById('edit-user-id').value;
                var fd = new FormData();
                fd.append('action', editId ? 'update_user' : 'add_user');
                if (editId) fd.append('id', editId);
                fd.append('name', document.getElementById('user-name').value);
                fd.append('email', document.getElementById('user-email').value);
                fd.append('subscription', document.getElementById('user-subscription').value);
                fd.append('status', document.getElementById('user-status').value);
                if (!editId) {
                    fd.append('password', document.getElementById('user-password').value);
                    fd.append('confirmPassword', document.getElementById('user-password-confirm').value);
                }
                fetch(ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) {
                        return r.json();
                    })
                    .then(function (j) {
                        if (j && j.success) {
                            toast(j.message || 'Enregistré');
                            if (modal) modal.classList.remove('is-open');
                            reloadUsers();
                        } else {
                            toast((j && j.message) || 'Erreur', true);
                        }
                    })
                    .catch(function () {
                        toast('Erreur réseau', true);
                    });
            });
        }
    }

    // Admins
    function renderAdminsTable(rows) {
        var tb = document.querySelector('#admins-table tbody');
        if (!tb) return;
        if (!rows || !rows.length) {
            tb.innerHTML = '<tr><td colspan="8" style="padding:12px;color:var(--sa-muted);">Aucun administrateur.</td></tr>';
            return;
        }
        var sid = window.TCF_ADMIN_SESSION_ID ? String(window.TCF_ADMIN_SESSION_ID) : '';
        tb.innerHTML = rows
            .map(function (a) {
                var st = a.status || 'active';
                var role = a.role || 'admin';
                var isSelf = sid && String(a.id) === sid;
                return (
                    '<tr data-id="' +
                    escAttr(String(a.id)) +
                    '" data-admin-name="' +
                    escAttr(a.name || '') +
                    '" data-admin-email="' +
                    escAttr(a.email || '') +
                    '" data-admin-role="' +
                    escAttr(role) +
                    '" data-admin-status="' +
                    escAttr(st) +
                    '">' +
                    saAvatarCell(a.avatar_url || '', a.name || '') +
                    '<td>' +
                    escHtml(a.name || '') +
                    '</td><td>' +
                    escHtml(a.email || '') +
                    '</td><td><span class="sa-badge sa-badge--role-' +
                    escAttr(role) +
                    '">' +
                    escHtml(role) +
                    '</span></td><td><span class="sa-badge sa-badge--status-' +
                    escAttr(st) +
                    '">' +
                    escHtml(st) +
                    '</span></td><td>' +
                    escHtml(a.created_at || '') +
                    '</td><td>' +
                    escHtml(a.last_login || '—') +
                    '</td>' +
                    (isSelf
                        ? saActionsRow('<span class="sa-actions-placeholder">Session</span>')
                        : saActionsRow(
                              '<button type="button" class="btn btn-outline btn-sm sa-btn-icon sa-admin-edit" aria-label="Modifier"><i class="bx bx-edit-alt" aria-hidden="true"></i></button><button type="button" class="btn btn-outline btn-sm sa-btn-icon btn-danger-outline sa-admin-del" aria-label="Supprimer"><i class="bx bx-trash" aria-hidden="true"></i></button><button type="button" class="btn btn-outline btn-sm sa-btn-icon sa-admin-demote" aria-label="Rétrograder"><i class="bx bx-user-minus" aria-hidden="true"></i></button>'
                          )) +
                    '</tr>'
                );
            })
            .join('');
    }

    function reloadAdmins() {
        postForm('get_admins')
            .then(function (j) {
                if (!j || !j.success) return;
                renderAdminsTable(j.data || []);
            })
            .catch(function () {});
    }

    function openAdminFormPanel(mode, tr) {
        var panel = document.getElementById('admin-form-panel');
        var form = document.getElementById('admin-form-modal');
        var title = document.getElementById('admin-form-title');
        if (!panel || !form) return;
        var isEdit = mode === 'edit' && tr;
        document.getElementById('admin-edit-id').value = isEdit ? tr.getAttribute('data-id') || '' : '';
        document.getElementById('admin-name').value = isEdit ? tr.getAttribute('data-admin-name') || '' : '';
        document.getElementById('admin-email').value = isEdit ? tr.getAttribute('data-admin-email') || '' : '';
        document.getElementById('admin-role').value = isEdit
            ? tr.getAttribute('data-admin-role') || 'admin'
            : 'admin';
        document.getElementById('admin-status').value = isEdit
            ? tr.getAttribute('data-admin-status') || 'active'
            : 'active';
        var adminPwd = document.getElementById('admin-password');
        var adminPwdConfirm = document.getElementById('admin-password-confirm');
        if (adminPwd) {
            adminPwd.value = '';
            adminPwd.required = !isEdit;
        }
        if (adminPwdConfirm) {
            adminPwdConfirm.value = '';
            adminPwdConfirm.required = !isEdit;
        }
        $all('.admin-password-fields', form).forEach(function (x) {
            x.style.display = isEdit ? 'none' : 'block';
        });
        if (title) {
            title.textContent = isEdit ? 'Modifier administrateur' : 'Ajouter un administrateur';
        }
        panel.hidden = false;
        try {
            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (e) {}
    }

    function closeAdminFormPanel() {
        var panel = document.getElementById('admin-form-panel');
        if (panel) panel.hidden = true;
        var form = document.getElementById('admin-form-modal');
        if (form) form.reset();
        var editId = document.getElementById('admin-edit-id');
        if (editId) editId.value = '';
    }

    function initAdminsSection() {
        var addBtn = document.getElementById('add-admin-btn');
        var form = document.getElementById('admin-form-modal');
        var cancelBtn = document.getElementById('admin-form-cancel');
        if (addBtn) {
            addBtn.addEventListener('click', function () {
                openAdminFormPanel('create');
            });
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', closeAdminFormPanel);
        }
        document.body.addEventListener('click', function (e) {
            var tr = e.target.closest && e.target.closest('#admins-table tr[data-id]');
            if (!tr) return;
            var id = tr.getAttribute('data-id');
            if (e.target.closest('.sa-admin-del')) {
                if (!id || !window.confirm('Supprimer cet administrateur ?')) return;
                postForm('delete_admin', { id: id })
                    .then(function (j) {
                        if (j && j.success) {
                            toast(j.message || 'Supprimé');
                            reloadAdmins();
                        } else {
                            toast((j && j.message) || 'Erreur', true);
                        }
                    })
                    .catch(function () {
                        toast('Erreur réseau', true);
                    });
            }
            if (e.target.closest('.sa-admin-demote')) {
                if (!id || !window.confirm('Rétrograder cet administrateur en utilisateur ?')) return;
                postForm('demote_to_user', { id: id })
                    .then(function (j) {
                        if (j && j.success) {
                            toast(j.message || 'OK');
                            reloadAdmins();
                        } else {
                            toast((j && j.message) || 'Erreur', true);
                        }
                    })
                    .catch(function () {
                        toast('Erreur réseau', true);
                    });
            }
            if (e.target.closest('.sa-admin-edit')) {
                openAdminFormPanel('edit', tr);
            }
        });
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var editId = document.getElementById('admin-edit-id').value;
                var fd = new FormData();
                fd.append('action', editId ? 'update_admin' : 'add_admin');
                if (editId) fd.append('id', editId);
                fd.append('name', document.getElementById('admin-name').value);
                fd.append('email', document.getElementById('admin-email').value);
                fd.append('role', document.getElementById('admin-role').value);
                fd.append('status', document.getElementById('admin-status').value);
                if (!editId) {
                    fd.append('password', document.getElementById('admin-password').value);
                    var adminPwdConfirmEl = document.getElementById('admin-password-confirm');
                    fd.append('confirmPassword', adminPwdConfirmEl ? adminPwdConfirmEl.value : '');
                }
                fetch(ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) {
                        return r.json();
                    })
                    .then(function (j) {
                        if (j && j.success) {
                            toast(j.message || 'Enregistré');
                            closeAdminFormPanel();
                            reloadAdmins();
                        } else {
                            toast((j && j.message) || 'Erreur', true);
                        }
                    })
                    .catch(function () {
                        toast('Erreur réseau', true);
                    });
            });
        }
    }

    // Annonces communautaires (image + texte + likes)
    var COMMUNITY_API =
        typeof window.TCF_COMMUNITY_API === 'string' && window.TCF_COMMUNITY_API
            ? window.TCF_COMMUNITY_API
            : '../community_api.php';

    function resetCommunityPostForm() {
        var form = document.getElementById('message-form');
        if (!form) return;
        document.getElementById('message-edit-id').value = '';
        document.getElementById('message-content').value = '';
        document.getElementById('message-visibility').value = 'registered';
        document.getElementById('message-published').value = '1';
        var img = document.getElementById('message-image');
        if (img) img.value = '';
        var prev = document.getElementById('message-image-preview');
        if (prev) {
            prev.style.display = 'none';
            prev.removeAttribute('src');
        }
        var clear = document.getElementById('message-clear-image');
        if (clear) clear.checked = false;
        var clearWrap = document.getElementById('message-clear-image-wrap');
        if (clearWrap) clearWrap.style.display = 'none';
    }

    function renderMessages(list) {
        var box = document.getElementById('messages-container');
        if (!box) return;
        if (!list || !list.length) {
            box.innerHTML = '<p style="color:var(--sa-muted);">Aucune annonce.</p>';
            return;
        }
        box.innerHTML = list
            .map(function (m) {
                var body = String(m.body || '');
                var excerpt = body.length > 220 ? body.substring(0, 220) + '…' : body;
                var thumb = m.image_href
                    ? '<img class="sa-msg-thumb" src="' +
                      escAttr(m.image_href) +
                      '" alt="" style="width:72px;height:72px;object-fit:cover;border-radius:10px;border:1px solid #e2e8f0;flex-shrink:0;">'
                    : '';
                return (
                    '<div class="sa-msg-card" data-msg=\'' +
                    escAttr(JSON.stringify(m)) +
                    '\' style="display:flex;gap:12px;align-items:flex-start;">' +
                    thumb +
                    '<div style="flex:1;min-width:0;">' +
                    '<p class="sa-msg-body" style="margin:0 0 8px;white-space:pre-wrap;">' +
                    escHtml(excerpt) +
                    '</p>' +
                    '<div class="sa-msg-stats" style="display:flex;flex-wrap:wrap;gap:0.55rem;margin-bottom:8px;">' +
                    '<span class="sa-badge" style="background:#f1f5f9;color:#334155;"><i class="bx bx-show"></i> ' +
                    escHtml(String(m.views_count || 0)) +
                    ' vues</span>' +
                    '<span class="sa-badge" style="background:#fef2f2;color:#b91c1c;"><i class="bx bxs-heart"></i> ' +
                    escHtml(String(m.likes_count || 0)) +
                    ' likes</span>' +
                    '</div>' +
                    '<div class="sa-msg-meta">' +
                    escHtml(m.visibility_label || m.visibility || '') +
                    ' · ' +
                    (m.is_published == 1 || m.is_published === true ? 'Publiée' : 'Brouillon') +
                    ' · ' +
                    escHtml(m.created_at || '') +
                    '</div>' +
                    '<div class="sa-msg-actions sa-msg-actions--end">' +
                    '<button type="button" class="btn btn-outline btn-sm sa-btn-icon sa-msg-edit" aria-label="Modifier"><i class="bx bx-edit-alt" aria-hidden="true"></i></button>' +
                    '<button type="button" class="btn btn-outline btn-sm sa-btn-icon btn-danger-outline sa-msg-del" aria-label="Supprimer"><i class="bx bx-trash" aria-hidden="true"></i></button>' +
                    '</div></div></div>'
                );
            })
            .join('');
    }

    function reloadMessages() {
        var fd = new FormData();
        fd.append('action', 'admin_list');
        fetch(COMMUNITY_API, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) {
                return r.json();
            })
            .then(function (j) {
                if (j && j.success && j.data) renderMessages(j.data);
            })
            .catch(function () {});
    }

    function initMessagesSection() {
        var form = document.getElementById('message-form');
        var addBtn = document.getElementById('add-message-btn');
        var cancelBtn = document.getElementById('cancel-message-btn');
        var imgInput = document.getElementById('message-image');
        if (imgInput) {
            imgInput.addEventListener('change', function () {
                var prev = document.getElementById('message-image-preview');
                if (!prev || !imgInput.files || !imgInput.files[0]) return;
                var url = URL.createObjectURL(imgInput.files[0]);
                prev.src = url;
                prev.style.display = 'block';
            });
        }
        if (addBtn && form) {
            addBtn.addEventListener('click', function () {
                resetCommunityPostForm();
                form.style.display = 'block';
            });
        }
        if (cancelBtn && form) {
            cancelBtn.addEventListener('click', function () {
                form.style.display = 'none';
                resetCommunityPostForm();
            });
        }
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var editId = document.getElementById('message-edit-id').value;
                var fd = new FormData();
                fd.append('action', 'admin_save');
                if (editId) fd.append('id', editId);
                fd.append('body', document.getElementById('message-content').value);
                fd.append('visibility', document.getElementById('message-visibility').value);
                fd.append('is_published', document.getElementById('message-published').value);
                if (imgInput && imgInput.files && imgInput.files[0]) {
                    fd.append('image', imgInput.files[0]);
                }
                var clear = document.getElementById('message-clear-image');
                if (clear && clear.checked) fd.append('clear_image', '1');
                fetch(COMMUNITY_API, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) {
                        return r.json();
                    })
                    .then(function (j) {
                        if (j && j.success) {
                            toast(j.message || 'OK');
                            form.style.display = 'none';
                            resetCommunityPostForm();
                            reloadMessages();
                        } else {
                            toast((j && j.message) || 'Erreur', true);
                        }
                    })
                    .catch(function () {
                        toast('Erreur réseau', true);
                    });
            });
        }
        document.body.addEventListener('click', function (e) {
            var ed = e.target.closest && e.target.closest('.sa-msg-edit');
            if (ed) {
                var card = ed.closest('.sa-msg-card');
                var raw = card && card.getAttribute('data-msg');
                if (!raw) return;
                try {
                    var m = JSON.parse(raw);
                    document.getElementById('message-edit-id').value = String(m.id);
                    document.getElementById('message-content').value = m.body || '';
                    document.getElementById('message-visibility').value = m.visibility || 'registered';
                    document.getElementById('message-published').value =
                        m.is_published == 1 || m.is_published === true ? '1' : '0';
                    if (imgInput) imgInput.value = '';
                    var prev = document.getElementById('message-image-preview');
                    var clearWrap = document.getElementById('message-clear-image-wrap');
                    var clear = document.getElementById('message-clear-image');
                    if (prev) {
                        if (m.image_href) {
                            prev.src = m.image_href;
                            prev.style.display = 'block';
                            if (clearWrap) clearWrap.style.display = 'block';
                        } else {
                            prev.style.display = 'none';
                            prev.removeAttribute('src');
                            if (clearWrap) clearWrap.style.display = 'none';
                        }
                    }
                    if (clear) clear.checked = false;
                    form.style.display = 'block';
                } catch (err) {}
                return;
            }
            var del = e.target.closest && e.target.closest('.sa-msg-del');
            if (del) {
                var card2 = del.closest('.sa-msg-card');
                var raw2 = card2 && card2.getAttribute('data-msg');
                if (!raw2 || !window.confirm('Supprimer cette annonce ?')) return;
                try {
                    var m2 = JSON.parse(raw2);
                    var fd2 = new FormData();
                    fd2.append('action', 'admin_delete');
                    fd2.append('id', String(m2.id));
                    fetch(COMMUNITY_API, { method: 'POST', body: fd2, credentials: 'same-origin' }).then(function (r) {
                        return r.json();
                    }).then(function (j) {
                        if (j && j.success) {
                            toast(j.message || 'OK');
                            reloadMessages();
                        } else {
                            toast((j && j.message) || 'Erreur', true);
                        }
                    });
                } catch (e2) {}
            }
        });
    }

    function initNotifications() {
        /* Panneau notifications = même UI que le site (includes/profile_panel_logged_in.php + profile_panel.js). */
        refreshNotifications();
    }

    function refreshNotifications() {
        postForm('get_notifications')
            .then(function (j) {
                if (!j || !j.success) return;
                var unread = (j.unread_count != null ? j.unread_count : 0) || 0;
                var c = document.getElementById('notification-count');
                if (c) {
                    c.textContent = String(unread);
                    c.style.display = unread > 0 ? '' : 'none';
                }
            })
            .catch(function () {});
    }

    function destroyTraceChart(key) {
        if (state.trace.charts[key]) {
            state.trace.charts[key].destroy();
            delete state.trace.charts[key];
        }
    }

    function buildAnalyticsUi(vids) {
        vids = vids || [];
        var focusId = state.analyticsFocusVideoId;
        var focusVideo = null;
        if (focusId != null && String(focusId).trim() !== '') {
            for (var fi = 0; fi < vids.length; fi++) {
                if (String(vids[fi].id) === String(focusId)) {
                    focusVideo = vids[fi];
                    break;
                }
            }
        }
        if (focusId != null && String(focusId).trim() !== '' && !focusVideo) {
            state.analyticsFocusVideoId = null;
        }
        var isVideoFocus = !!focusVideo;

        var banner = document.getElementById('analytics-focus-banner');
        var titleEl = document.getElementById('analytics-focus-title');
        var statsEl = document.getElementById('analytics-focus-stats');
        var audCard = document.getElementById('analytics-audience-card');
        var perfTitleEl = document.getElementById('analytics-chart-perf-title');
        var audTitleEl = document.getElementById('analytics-chart-aud-title');
        var popularTitleEl = document.getElementById('analytics-popular-title');
        if (banner && titleEl && statsEl) {
            if (isVideoFocus && focusVideo) {
                banner.style.display = '';
                titleEl.textContent = focusVideo.title || '—';
                statsEl.textContent =
                    ' · ' +
                    String(focusVideo.views != null ? focusVideo.views : 0) +
                    ' vues · ' +
                    String(focusVideo.likes != null ? focusVideo.likes : 0) +
                    ' j’aime';
            } else {
                banner.style.display = 'none';
                titleEl.textContent = '';
                statsEl.textContent = '';
            }
        }
        if (perfTitleEl) {
            perfTitleEl.textContent = isVideoFocus ? 'Performances de cette vidéo' : 'Performances des vidéos';
        }
        if (audTitleEl && !isVideoFocus) {
            audTitleEl.textContent = 'Audience';
        }
        if (popularTitleEl) {
            popularTitleEl.textContent = isVideoFocus ? 'Détail de la vidéo' : 'Liste des vidéos';
        }
        if (audCard) {
            audCard.style.display = isVideoFocus ? 'none' : '';
        }

        var sorted;
        if (isVideoFocus && focusVideo) {
            sorted = [focusVideo];
        } else {
            sorted = []
                .concat(vids)
                .sort(function (a, b) {
                    var bt = new Date(b.created_at || 0).getTime() || 0;
                    var at = new Date(a.created_at || 0).getTime() || 0;
                    return bt - at;
                })
                .slice(0, 30);
        }
        var tb = document.querySelector('#popular-videos-table tbody');
        if (tb) {
            function formatPubDate(raw) {
                if (!raw) return '—';
                var d = new Date(raw);
                if (Number.isNaN(d.getTime())) return escHtml(String(raw));
                return d.toLocaleDateString('fr-FR', { year: 'numeric', month: 'short', day: '2-digit' });
            }
            function visibilityLabel(v) {
                if (v === 'public') return 'Publique';
                if (v === 'private') return 'Privée';
                if (v === 'premium') return 'Premium';
                return v || '—';
            }
            tb.innerHTML = sorted.length
                ? sorted
                      .map(function (v) {
                          var thumb = v.thumbnail_href ? '<img src="' + escHtml(v.thumbnail_href) + '" alt="" style="width:48px;height:28px;object-fit:cover;border-radius:6px;flex:0 0 auto;">' : '';
                          var comments = parseInt(v.comments_count, 10) || 0;
                          var vis = String(v.visibility || '').toLowerCase();
                          var visClass = vis === 'public' ? 'success' : vis === 'private' ? 'warning' : vis === 'premium' ? 'danger' : '';
                          return (
                              '<tr><td><div style="display:flex;align-items:center;gap:10px;min-width:0;">' +
                              thumb +
                              '<span style="min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' +
                              escHtml(v.title || '') +
                              '</span></div></td><td>' +
                              escHtml(String(v.views != null ? v.views : 0)) +
                              '</td><td>' +
                              escHtml(String(v.likes != null ? v.likes : 0)) +
                              '</td><td>' +
                              escHtml(String(comments)) +
                              '</td><td><span class="sa-badge ' +
                              escHtml(visClass) +
                              '">' +
                              escHtml(visibilityLabel(v.visibility || '')) +
                              '</span></td><td>' +
                              escHtml(formatPubDate(v.created_at || '')) +
                              '</td></tr>'
                          );
                      })
                      .join('')
                : '<tr><td colspan="6" style="padding:12px;">Aucune donnée.</td></tr>';
        }
        destroyTraceChart('videoPerf');
        destroyTraceChart('audience');
        var c1 = document.getElementById('video-performance-chart');
        if (c1 && typeof Chart !== 'undefined' && sorted.length) {
            var tc = chartTextColor();
            state.trace.charts.videoPerf = new Chart(c1, {
                type: 'bar',
                data: {
                    labels: sorted.map(function (v) {
                        return (v.title || '').substring(0, 24);
                    }),
                    datasets: [
                        {
                            label: 'Vues',
                            data: sorted.map(function (v) {
                                return parseInt(v.views, 10) || 0;
                            }),
                            backgroundColor: 'rgba(211,13,13,0.7)'
                        },
                        {
                            label: 'J’aime',
                            data: sorted.map(function (v) {
                                return parseInt(v.likes, 10) || 0;
                            }),
                            backgroundColor: 'rgba(176,11,11,0.55)'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: tc } } },
                    scales: {
                        x: { ticks: { color: tc, maxRotation: isVideoFocus ? 0 : 45 }, grid: { display: false } },
                        y: { ticks: { color: tc }, beginAtZero: true }
                    }
                }
            });
        }
        var c2 = document.getElementById('audience-chart');
        if (!isVideoFocus && c2 && typeof Chart !== 'undefined' && vids.length) {
            var tc2 = chartTextColor();
            var pub = vids.filter(function (x) {
                return x.visibility === 'public';
            }).length;
            var prv = vids.filter(function (x) {
                return x.visibility === 'private';
            }).length;
            var prem = vids.filter(function (x) {
                return x.visibility === 'premium';
            }).length;
            state.trace.charts.audience = new Chart(c2, {
                type: 'pie',
                data: {
                    labels: ['Public', 'Privé', 'Premium'],
                    datasets: [{ data: [pub, prv, prem], backgroundColor: ['#d30d0d', '#b00b0b', 'rgba(20,22,34,0.35)'] }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: tc2 } } } }
            });
        }
    }

    function initAnalytics() {
        if (typeof Chart === 'undefined') return;
        postForm('get_videos')
            .then(function (j) {
                var vids = j && j.success && j.data ? j.data : [];
                if (!vids.length && typeof window.videosFromDB !== 'undefined' && window.videosFromDB) {
                    vids = window.videosFromDB;
                }
                buildAnalyticsUi(vids);
                wireAnalyticsFocusClearOnce();
            })
            .catch(function () {
                var fb = typeof window.videosFromDB !== 'undefined' ? window.videosFromDB : [];
                buildAnalyticsUi(fb || []);
                wireAnalyticsFocusClearOnce();
            });
    }

    function wireAnalyticsFocusClearOnce() {
        var btn = document.getElementById('analytics-focus-clear-btn');
        if (!btn || btn.getAttribute('data-sa-wired') === '1') return;
        btn.setAttribute('data-sa-wired', '1');
        btn.addEventListener('click', function () {
            state.analyticsFocusVideoId = null;
            initAnalytics();
        });
    }

    function initAnalyticsPeriodListener() {
        var sel = document.getElementById('analytics-period');
        if (!sel) return;
        sel.addEventListener('change', function () {
            initAnalytics();
        });
    }

    // ================ Testimonials (v2 — cards + modal) ================
    var bootTestimonials = false;
    var saTestiAllData   = [];  // cache des données brutes
    var saTestiOpenId    = null; // id du témoignage ouvert dans le modal

    // --- Helpers ---
    function saTestiStars(rating) {
        var r = parseInt(rating, 10) || 0;
        var html = '';
        for (var i = 1; i <= 5; i++) {
            html += '<i class="bx ' + (i <= r ? 'bxs-star sa-testi-star--full' : 'bx-star sa-testi-star--empty') + '"></i>';
        }
        return html;
    }
    function saTestiAvatar(name) {
        var s = (name || '?').trim();
        return s.charAt(0).toUpperCase();
    }
    function saTestiAvatarColor(name) {
        var colors = ['#e53e3e','#dd6b20','#d69e2e','#38a169','#3182ce','#805ad5','#d53f8c','#2b6cb0'];
        var n = 0;
        for (var ci = 0; ci < (name || '').length; ci++) { n += name.charCodeAt(ci); }
        return colors[n % colors.length];
    }
    function saTestiFmtDate(raw) {
        if (!raw) return '';
        try {
            var d = new Date(raw.replace(' ', 'T'));
            if (isNaN(d)) return raw;
            return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });
        } catch (e) { return raw; }
    }

    // --- Stats bar ---
    function saTestiUpdateStats(data) {
        var total = data.length;
        var sum = 0, five = 0, rated = 0;
        data.forEach(function (t) {
            var r = parseInt(t.rating, 10);
            if (r > 0) { sum += r; rated++; }
            if (r === 5) five++;
        });
        var avg = rated > 0 ? (sum / rated).toFixed(1) : '—';
        var elCount = document.getElementById('sa-testi-count');
        var elAvg   = document.getElementById('sa-testi-avg');
        var elFive  = document.getElementById('sa-testi-five');
        if (elCount) elCount.textContent = total;
        if (elAvg)   elAvg.textContent   = rated > 0 ? avg + ' / 5' : '—';
        if (elFive)  elFive.textContent  = five;
    }

    // --- Render cards ---
    function saTestiRenderGrid(data) {
        var grid = document.getElementById('sa-testi-grid');
        if (!grid) return;
        if (!data || !data.length) {
            grid.innerHTML = '<div class="sa-testi-empty"><i class="bx bx-comment-x"></i><p>Aucun témoignage trouvé.</p></div>';
            var rc = document.getElementById('sa-testi-result-count');
            if (rc) rc.textContent = '';
            return;
        }
        grid.innerHTML = data.map(function (t) {
            var preview = (t.content || '').length > 120
                ? (t.content || '').substring(0, 120) + '…'
                : (t.content || '');
            var col = saTestiAvatarColor(t.author_name);
            return (
                '<div class="sa-testi-card" id="sa-testimonial-' + escAttr(String(t.id)) + '" ' +
                    'data-id="' + escAttr(String(t.id)) + '" role="button" tabindex="0" ' +
                    'aria-label="Voir le témoignage de ' + escAttr(t.author_name) + '">' +
                '  <div class="sa-testi-card__top">' +
                '    <div class="sa-testi-card__avatar" style="background:' + escAttr(col) + '">' +
                       escHtml(saTestiAvatar(t.author_name)) +
                '    </div>' +
                '    <div class="sa-testi-card__meta">' +
                '      <strong class="sa-testi-card__name">' + escHtml(t.author_name) + '</strong>' +
                '      <div class="sa-testi-card__stars">' + saTestiStars(t.rating) + '</div>' +
                '    </div>' +
                '    <button type="button" class="sa-testi-card__del js-del-testimonial-card" ' +
                '      data-id="' + escAttr(String(t.id)) + '" aria-label="Supprimer">' +
                '      <i class="bx bx-trash"></i>' +
                '    </button>' +
                '  </div>' +
                '  <p class="sa-testi-card__preview">' + escHtml(preview) + '</p>' +
                '  <div class="sa-testi-card__footer">' +
                '    <span class="sa-testi-card__date"><i class="bx bx-calendar"></i> ' + escHtml(saTestiFmtDate(t.created_at)) + '</span>' +
                '    <span class="sa-testi-card__readmore">Voir tout <i class="bx bx-chevron-right"></i></span>' +
                '  </div>' +
                '</div>'
            );
        }).join('');
        var rc2 = document.getElementById('sa-testi-result-count');
        if (rc2) rc2.textContent = data.length + ' témoignage' + (data.length > 1 ? 's' : '') + ' affiché' + (data.length > 1 ? 's' : '');
    }

    // --- Filter ---
    function saTestiApplyFilter() {
        var q      = (document.getElementById('sa-testi-search')    ? document.getElementById('sa-testi-search').value    : '').toLowerCase().trim();
        var rating = (document.getElementById('sa-testi-filter-rating') ? document.getElementById('sa-testi-filter-rating').value : '');
        var filtered = saTestiAllData.filter(function (t) {
            var matchQ = !q || (t.author_name || '').toLowerCase().indexOf(q) > -1 || (t.content || '').toLowerCase().indexOf(q) > -1;
            var matchR = rating === '' ? true :
                         rating === '0' ? (!t.rating || parseInt(t.rating,10) === 0) :
                         (parseInt(t.rating,10) === parseInt(rating,10));
            return matchQ && matchR;
        });
        saTestiRenderGrid(filtered);
    }

    // --- Modal ---
    function saTestiOpenModal(id) {
        var t = null;
        for (var i = 0; i < saTestiAllData.length; i++) {
            if (String(saTestiAllData[i].id) === String(id)) { t = saTestiAllData[i]; break; }
        }
        if (!t) return;
        saTestiOpenId = String(t.id);
        var modal  = document.getElementById('sa-testi-modal');
        var avatar = document.getElementById('sa-testi-modal-avatar');
        var title  = document.getElementById('sa-testi-modal-title');
        var stars  = document.getElementById('sa-testi-modal-stars');
        var date   = document.getElementById('sa-testi-modal-date');
        var body   = document.getElementById('sa-testi-modal-body');
        if (avatar) { avatar.textContent = saTestiAvatar(t.author_name); avatar.style.background = saTestiAvatarColor(t.author_name); }
        if (title)  title.textContent  = t.author_name || '';
        if (stars)  stars.innerHTML    = saTestiStars(t.rating);
        if (date)   date.textContent   = saTestiFmtDate(t.created_at);
        if (body)   body.textContent   = t.content || '';
        if (modal)  { modal.removeAttribute('hidden'); modal.setAttribute('aria-hidden','false'); document.body.classList.add('sa-testi-modal-open'); }
    }
    function saTestiCloseModal() {
        var modal = document.getElementById('sa-testi-modal');
        if (modal) { modal.setAttribute('hidden',''); modal.setAttribute('aria-hidden','true'); }
        document.body.classList.remove('sa-testi-modal-open');
        saTestiOpenId = null;
    }

    // --- Boot (wire events once) ---
    function ensureTestimonialsBoot() {
        if (bootTestimonials) return;
        bootTestimonials = true;

        // Open modal on card click (but not delete button)
        document.body.addEventListener('click', function (e) {
            // Delete button inside card
            var delBtn = e.target.closest && e.target.closest('.js-del-testimonial-card');
            if (delBtn) {
                e.stopPropagation();
                var id = delBtn.getAttribute('data-id');
                if (!id || !window.confirm('Supprimer ce témoignage ?')) return;
                deleteTestimonialById(id);
                return;
            }
            // Delete inside modal
            var modalDel = e.target.closest && e.target.closest('#sa-testi-modal-delete');
            if (modalDel) {
                if (!saTestiOpenId || !window.confirm('Supprimer ce témoignage ?')) return;
                deleteTestimonialById(saTestiOpenId);
                return;
            }
            // Edit button
            var modalEdit = e.target.closest && e.target.closest('#sa-testi-modal-edit');
            if (modalEdit) {
                if (!saTestiOpenId) return;
                saTestiOpenEditMode(saTestiOpenId);
                return;
            }
            // Cancel edit
            var editCancel = e.target.closest && e.target.closest('#sa-testi-edit-cancel');
            if (editCancel) {
                saTestiCloseEditMode();
                return;
            }
            // Close modal
            if (e.target.closest && (e.target.closest('#sa-testi-modal-close') || e.target.closest('#sa-testi-modal-close2'))) {
                saTestiCloseModal(); return;
            }
            // Backdrop click
            if (e.target.id === 'sa-testi-modal') { saTestiCloseModal(); return; }
            // Open card
            var card = e.target.closest && e.target.closest('.sa-testi-card');
            if (card) {
                saTestiOpenModal(card.getAttribute('data-id'));
            }
        });

        // Keyboard on cards
        document.body.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                var card = e.target.closest && e.target.closest('.sa-testi-card');
                if (card) { e.preventDefault(); saTestiOpenModal(card.getAttribute('data-id')); }
            }
            if (e.key === 'Escape') { saTestiCloseModal(); }
        });

        // Refresh button
        var refBtn = document.getElementById('sa-testi-refresh-btn');
        if (refBtn) refBtn.addEventListener('click', function () { loadTestimonialsAdmin(); });

        // Search / filter
        var searchEl  = document.getElementById('sa-testi-search');
        var filterEl  = document.getElementById('sa-testi-filter-rating');
        if (searchEl) searchEl.addEventListener('input', saTestiApplyFilter);
        if (filterEl) filterEl.addEventListener('change', saTestiApplyFilter);
        
        // Edit form submit
        var editForm = document.getElementById('sa-testi-edit-form');
        if (editForm) {
            editForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var id = document.getElementById('sa-testi-edit-id').value;
                var author = document.getElementById('sa-testi-edit-author').value;
                var rating = document.getElementById('sa-testi-edit-rating').value;
                var content = document.getElementById('sa-testi-edit-content').value;
                
                if (!id || !author || !content) {
                    toast('Veuillez remplir tous les champs', true);
                    return;
                }
                
                postForm('update_testimonial', {
                    id: id,
                    author_name: author,
                    rating: rating,
                    content: content
                })
                .then(function (j) {
                    if (j && j.success) {
                        toast(j.message || 'Modifié');
                        saTestiCloseEditMode();
                        saTestiCloseModal();
                        loadTestimonialsAdmin();
                    } else {
                        toast((j && j.message) || 'Erreur', true);
                    }
                })
                .catch(function () {
                    toast('Erreur réseau', true);
                });
            });
        }
    }

    // --- Load ---
    function loadTestimonialsAdmin(scrollToId) {
        var grid = document.getElementById('sa-testi-grid');
        if (!grid) return;
        grid.innerHTML = '<div class="sa-testi-loading"><i class="bx bx-loader-alt bx-spin"></i> Chargement…</div>';
        postForm('get_testimonials')
            .then(function (j) {
                if (!j || !j.success || !j.data) {
                    grid.innerHTML = '<div class="sa-testi-empty"><i class="bx bx-comment-x"></i><p>Aucun témoignage.</p></div>';
                    return;
                }
                saTestiAllData = j.data;
                saTestiUpdateStats(saTestiAllData);
                saTestiRenderGrid(saTestiAllData);
                // Reset filtres
                var se = document.getElementById('sa-testi-search');   if (se) se.value = '';
                var fe = document.getElementById('sa-testi-filter-rating'); if (fe) fe.value = '';
                // Scroll/highlight si deep-link
                if (scrollToId) {
                    window.setTimeout(function () {
                        var card = document.getElementById('sa-testimonial-' + String(scrollToId));
                        if (card) {
                            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            card.classList.add('sa-testi-card--highlight');
                            window.setTimeout(function () { card.classList.remove('sa-testi-card--highlight'); }, 2400);
                            // Ouvrir le modal directement
                            saTestiOpenModal(scrollToId);
                        }
                    }, 160);
                }
            })
            .catch(function () {
                grid.innerHTML = '<div class="sa-testi-empty sa-testi-empty--error"><i class="bx bx-error"></i><p>Erreur de chargement.</p></div>';
            });
    }

    // ---------------- Identité chaîne ----------------
    var bootChannelBranding = false;
    var channelBrandingCropper = null;
    var channelBrandingCropObjectUrl = '';
    var channelBrandingCropKind = '';

    function channelBrandingCropEscape(ev) {
        if (ev.key === 'Escape') {
            ev.preventDefault();
            cancelChannelBrandingCrop();
        }
    }

    function disposeChannelBrandingCrop() {
        if (channelBrandingCropper) {
            channelBrandingCropper.destroy();
            channelBrandingCropper = null;
        }
        if (channelBrandingCropObjectUrl) {
            try {
                URL.revokeObjectURL(channelBrandingCropObjectUrl);
            } catch (e1) {
                /* ignore */
            }
            channelBrandingCropObjectUrl = '';
        }
        var cimg = document.getElementById('sa-channel-crop-img');
        if (cimg) {
            cimg.src = '';
        }
    }

    function closeChannelBrandingCropModal() {
        var modal = document.getElementById('sa-channel-crop-modal');
        if (modal) {
            modal.classList.remove('is-open');
            modal.setAttribute('hidden', '');
            modal.setAttribute('aria-hidden', 'true');
        }
        document.removeEventListener('keydown', channelBrandingCropEscape);
        disposeChannelBrandingCrop();
    }

    function cancelChannelBrandingCrop() {
        var kind = channelBrandingCropKind;
        channelBrandingCropKind = '';
        if (kind === 'logo') {
            var inp = document.getElementById('channel-branding-logo');
            if (inp) {
                inp.value = '';
            }
        } else if (kind === 'banner') {
            var inpB = document.getElementById('channel-branding-banner');
            if (inpB) {
                inpB.value = '';
            }
        }
        closeChannelBrandingCropModal();
        syncBrandingPreview();
    }

    function assignCroppedFileToInput(inputId, file) {
        var input = document.getElementById(inputId);
        if (!input) {
            return;
        }
        var dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
    }

    function applyChannelBrandingCrop() {
        if (!channelBrandingCropper || typeof window.Cropper === 'undefined') {
            toast('Recadrage indisponible.', true);
            return;
        }
        var kind = channelBrandingCropKind;
        var w;
        var h;
        var mime;
        var quality;
        var outName;
        if (kind === 'logo') {
            w = 512;
            h = 512;
            mime = 'image/jpeg';
            quality = 0.92;
            outName = 'channel-logo.jpg';
        } else if (kind === 'banner') {
            w = 1600;
            h = 400;
            mime = 'image/jpeg';
            quality = 0.88;
            outName = 'channel-banner.jpg';
        } else {
            return;
        }
        var canvas = channelBrandingCropper.getCroppedCanvas({
            width: w,
            height: h,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });
        if (!canvas) {
            toast('Impossible de préparer l’image.', true);
            return;
        }
        canvas.toBlob(
            function (blob) {
                if (!blob) {
                    toast('Export de l’image échoué.', true);
                    return;
                }
                var file = new File([blob], outName, { type: mime });
                if (kind === 'logo') {
                    var rl = document.getElementById('channel-branding-remove-logo');
                    if (rl) {
                        rl.value = '0';
                    }
                    assignCroppedFileToInput('channel-branding-logo', file);
                } else {
                    var rb = document.getElementById('channel-branding-remove-banner');
                    if (rb) {
                        rb.value = '0';
                    }
                    assignCroppedFileToInput('channel-branding-banner', file);
                }
                channelBrandingCropKind = '';
                closeChannelBrandingCropModal();
                syncBrandingPreview();
            },
            mime,
            quality
        );
    }

    function onChannelBrandingFileChange(kind, ev) {
        var input = ev.target;
        var f = input.files && input.files[0];
        if (!f) {
            return;
        }
        if (!/^image\//.test(f.type)) {
            toast('Veuillez choisir une image (JPG, PNG, WebP, GIF).', true);
            input.value = '';
            return;
        }
        if (kind === 'logo') {
            var rl0 = document.getElementById('channel-branding-remove-logo');
            if (rl0) {
                rl0.value = '0';
            }
        } else {
            var rb0 = document.getElementById('channel-branding-remove-banner');
            if (rb0) {
                rb0.value = '0';
            }
        }
        if (typeof window.Cropper === 'undefined') {
            syncBrandingPreview();
            return;
        }
        channelBrandingCropKind = kind;
        if (channelBrandingCropper) {
            channelBrandingCropper.destroy();
            channelBrandingCropper = null;
        }
        if (channelBrandingCropObjectUrl) {
            try {
                URL.revokeObjectURL(channelBrandingCropObjectUrl);
            } catch (e2) {
                /* ignore */
            }
            channelBrandingCropObjectUrl = '';
        }
        channelBrandingCropObjectUrl = URL.createObjectURL(f);
        var img = document.getElementById('sa-channel-crop-img');
        var modal = document.getElementById('sa-channel-crop-modal');
        var title = document.getElementById('sa-channel-crop-title');
        var hint = document.getElementById('sa-channel-crop-hint');
        if (!img || !modal) {
            try {
                URL.revokeObjectURL(channelBrandingCropObjectUrl);
            } catch (e3) {
                /* ignore */
            }
            channelBrandingCropObjectUrl = '';
            channelBrandingCropKind = '';
            syncBrandingPreview();
            return;
        }
        img.onload = function () {
            img.onload = null;
            if (channelBrandingCropper) {
                channelBrandingCropper.destroy();
                channelBrandingCropper = null;
            }
            modal.classList.add('is-open');
            modal.removeAttribute('hidden');
            modal.setAttribute('aria-hidden', 'false');
            if (title) {
                title.textContent = kind === 'logo' ? 'Recadrer la photo de profil' : 'Recadrer la bannière';
            }
            if (hint) {
                hint.textContent =
                    kind === 'logo'
                        ? 'Cadre carré — déplacez et zoomez, puis validez.'
                        : 'Bandeau large au ratio 4:1 — déplacez et zoomez pour cadrer comme sur la page Vidéos.';
            }
            channelBrandingCropper = new window.Cropper(img, {
                aspectRatio: kind === 'logo' ? 1 : 4,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.92,
                responsive: true,
                background: false
            });
            document.addEventListener('keydown', channelBrandingCropEscape);
        };
        img.src = channelBrandingCropObjectUrl;
    }

    function ensureChannelBrandingBoot() {
        if (bootChannelBranding) {
            return;
        }
        bootChannelBranding = true;
        var form = document.getElementById('channel-branding-form');
        if (form) {
            form.addEventListener('submit', saveChannelBrandingForm);
        }
        var cl = document.getElementById('channel-branding-clear-logo');
        if (cl) {
            cl.addEventListener('click', function () {
                var h = document.getElementById('channel-branding-remove-logo');
                var inp = document.getElementById('channel-branding-logo');
                if (h) {
                    h.value = '1';
                }
                if (inp) {
                    inp.value = '';
                }
                var modal = document.getElementById('sa-channel-crop-modal');
                if (modal && modal.classList.contains('is-open') && channelBrandingCropKind === 'logo') {
                    channelBrandingCropKind = '';
                    closeChannelBrandingCropModal();
                }
                syncBrandingPreview();
            });
        }
        var cb = document.getElementById('channel-branding-clear-banner');
        if (cb) {
            cb.addEventListener('click', function () {
                var h = document.getElementById('channel-branding-remove-banner');
                var inp = document.getElementById('channel-branding-banner');
                if (h) {
                    h.value = '1';
                }
                if (inp) {
                    inp.value = '';
                }
                var modalB = document.getElementById('sa-channel-crop-modal');
                if (modalB && modalB.classList.contains('is-open') && channelBrandingCropKind === 'banner') {
                    channelBrandingCropKind = '';
                    closeChannelBrandingCropModal();
                }
                syncBrandingPreview();
            });
        }
        var cropCancel = document.getElementById('sa-channel-crop-cancel');
        var cropApply = document.getElementById('sa-channel-crop-apply');
        var cropBackdrop = document.getElementById('sa-channel-crop-backdrop');
        if (cropCancel) {
            cropCancel.addEventListener('click', cancelChannelBrandingCrop);
        }
        if (cropApply) {
            cropApply.addEventListener('click', applyChannelBrandingCrop);
        }
        if (cropBackdrop) {
            cropBackdrop.addEventListener('click', cancelChannelBrandingCrop);
        }
        var t = document.getElementById('channel-branding-title');
        var g = document.getElementById('channel-branding-tagline');
        if (t) {
            t.addEventListener('input', syncBrandingPreview);
        }
        if (g) {
            g.addEventListener('input', syncBrandingPreview);
        }
        var li = document.getElementById('channel-branding-logo');
        var ba = document.getElementById('channel-branding-banner');
        if (li) {
            li.addEventListener('change', function (e) {
                onChannelBrandingFileChange('logo', e);
            });
        }
        if (ba) {
            ba.addEventListener('change', function (e) {
                onChannelBrandingFileChange('banner', e);
            });
        }
    }

    function brandingDefaults() {
        var d = typeof window.TCF_SA_BRANDING_DEFAULTS !== 'undefined' ? window.TCF_SA_BRANDING_DEFAULTS : {};
        return {
            title: d.title || 'TCF Canada',
            tag: d.tag || ''
        };
    }

    function syncBrandingPreview() {
        var def = brandingDefaults();
        var titleIn = document.getElementById('channel-branding-title');
        var tagIn = document.getElementById('channel-branding-tagline');
        var titleTxt = titleIn ? (titleIn.value || '').trim() : '';
        var tagTxt = tagIn ? (tagIn.value || '').trim() : '';
        var tEl = document.getElementById('sa-branding-preview-title-txt');
        var tagEl = document.getElementById('sa-branding-preview-tag');
        if (tEl) tEl.textContent = titleTxt || def.title;
        if (tagEl) tagEl.textContent = tagTxt || def.tag;

        var cov = document.getElementById('sa-branding-preview-cover');
        var banIn = document.getElementById('channel-branding-banner');
        var remBan = document.getElementById('channel-branding-remove-banner');
        if (cov) {
            if (banIn && banIn.files && banIn.files[0]) {
                try {
                    cov.style.backgroundImage = 'url(' + JSON.stringify(URL.createObjectURL(banIn.files[0])) + ')';
                } catch (e) {
                    cov.style.backgroundImage = '';
                }
            } else if (remBan && remBan.value === '1') {
                cov.style.backgroundImage = '';
                cov.removeAttribute('data-current-banner');
            } else {
                var bcur = cov.getAttribute('data-current-banner');
                cov.style.backgroundImage = bcur ? 'url(' + JSON.stringify(bcur) + ')' : '';
            }
        }

        var av = document.getElementById('sa-branding-preview-avatar');
        var logoIn = document.getElementById('channel-branding-logo');
        var remLogo = document.getElementById('channel-branding-remove-logo');
        var fb = typeof window.TCF_SA_FALLBACK_LOGO !== 'undefined' ? window.TCF_SA_FALLBACK_LOGO : '';
        if (av) {
            if (logoIn && logoIn.files && logoIn.files[0]) {
                try {
                    av.src = URL.createObjectURL(logoIn.files[0]);
                } catch (e2) {
                    av.src = fb;
                }
            } else if (remLogo && remLogo.value === '1') {
                av.src = fb || '';
                av.removeAttribute('data-current-logo');
            } else {
                var lcur = av.getAttribute('data-current-logo');
                av.src = lcur || fb || '';
            }
        }
    }

    function applyBrandingData(data) {
        data = data || {};
        var t = document.getElementById('channel-branding-title');
        var g = document.getElementById('channel-branding-tagline');
        if (t) t.value = data.title != null ? String(data.title) : '';
        if (g) g.value = data.tagline != null ? String(data.tagline) : '';
        var remL = document.getElementById('channel-branding-remove-logo');
        var remB = document.getElementById('channel-branding-remove-banner');
        if (remL) remL.value = '0';
        if (remB) remB.value = '0';
        var li = document.getElementById('channel-branding-logo');
        var ba = document.getElementById('channel-branding-banner');
        if (li) li.value = '';
        if (ba) ba.value = '';

        var cov = document.getElementById('sa-branding-preview-cover');
        var av = document.getElementById('sa-branding-preview-avatar');
        var fb = typeof window.TCF_SA_FALLBACK_LOGO !== 'undefined' ? window.TCF_SA_FALLBACK_LOGO : '';
        if (cov) {
            if (data.banner_href) {
                cov.setAttribute('data-current-banner', String(data.banner_href));
                cov.style.backgroundImage = 'url(' + JSON.stringify(String(data.banner_href)) + ')';
            } else {
                cov.removeAttribute('data-current-banner');
                cov.style.backgroundImage = '';
            }
        }
        if (av) {
            if (data.logo_href) {
                av.setAttribute('data-current-logo', String(data.logo_href));
                av.src = String(data.logo_href);
            } else {
                av.removeAttribute('data-current-logo');
                av.src = fb || '';
            }
        }
        syncBrandingPreview();
    }

    function loadChannelBrandingAdmin() {
        postForm('get_channel_branding')
            .then(function (j) {
                if (j && j.success && j.data) {
                    applyBrandingData(j.data);
                } else {
                    applyBrandingData({});
                }
            })
            .catch(function () {
                applyBrandingData({});
            });
    }

    function saveChannelBrandingForm(ev) {
        ev.preventDefault();
        var form = document.getElementById('channel-branding-form');
        if (!form) return;
        var fd = new FormData(form);
        fd.append('action', 'save_channel_branding');
        var btn = document.getElementById('channel-branding-save-btn');
        if (btn) btn.disabled = true;
        fetch(ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) {
                return r.json();
            })
            .then(function (j) {
                if (j && j.success) {
                    toast(j.message || 'Enregistré');
                    if (j.data) applyBrandingData(j.data);
                } else {
                    toast((j && j.message) || 'Erreur', true);
                }
            })
            .catch(function () {
                toast('Erreur réseau', true);
            })
            .finally(function () {
                if (btn) btn.disabled = false;
            });
    }

    function deleteTestimonialById(id) {
        postForm('delete_testimonial', { id: id })
            .then(function (j) {
                if (j && j.success) {
                    toast(j.message || 'Supprimé');
                    saTestiCloseModal();
                    loadTestimonialsAdmin();
                } else {
                    toast((j && j.message) || 'Erreur', true);
                }
            })
            .catch(function () {
                toast('Erreur réseau', true);
            });
    }

    function saTestiOpenEditMode(id) {
        var item = saTestiAllData.find(function (t) { return String(t.id) === String(id); });
        if (!item) return;
        
        document.getElementById('sa-testi-edit-id').value = item.id;
        document.getElementById('sa-testi-edit-author').value = item.author_name || '';
        document.getElementById('sa-testi-edit-rating').value = item.rating || 0;
        document.getElementById('sa-testi-edit-content').value = item.content || '';
        
        document.getElementById('sa-testi-view-mode').style.display = 'none';
        document.getElementById('sa-testi-edit-mode').style.display = 'block';
    }

    function saTestiCloseEditMode() {
        document.getElementById('sa-testi-edit-mode').style.display = 'none';
        document.getElementById('sa-testi-view-mode').style.display = 'block';
    }

    // ---------------- Videos / Playlists / Posts (ported from legacy superAdmin.js) ----------------
    var bootVideos = false;
    function ensureVideosBoot() {
        if (bootVideos) return;
        bootVideos = true;
        initVideoCommentsModalUi();
        var initial = typeof videosFromDB !== 'undefined' ? videosFromDB : [];
        renderVideosGrid(initial);
        var addBtn = document.getElementById('add-video-btn');
        var form = document.getElementById('video-form');
        if (addBtn && form) {
            addBtn.addEventListener('click', function () {
                resetVideoForm();
                form.style.display = 'block';
                try {
                    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } catch (e) {}
            });
        }
        initVideoForm();
        initVideoModal();

        document.body.addEventListener('click', function (e) {
            var del = e.target.closest && e.target.closest('.js-delete-video');
            if (del) {
                if (!SA_IS_SUPER) {
                    toast('Seul le super-administrateur peut supprimer une vidéo.', true);
                    return;
                }
                var card = del.closest('.tcf-admin-video-card');
                var id = card ? card.getAttribute('data-id') : '';
                if (!id || !window.confirm('Supprimer cette vidéo ?')) return;
                deleteVideoById(id);
                return;
            }
            var edit = e.target.closest && e.target.closest('.js-edit-video');
            if (edit) {
                if (!SA_IS_SUPER) {
                    toast('Seul le super-administrateur peut modifier une vidéo.', true);
                    return;
                }
                var card2 = edit.closest('.tcf-admin-video-card');
                var id2 = card2 ? card2.getAttribute('data-id') : '';
                if (!id2) return;
                openVideoEditById(id2);
            }
            var play = e.target.closest && e.target.closest('.js-admin-play-video');
            if (play) {
                openVideoModal(play.getAttribute('data-video-src'), play.getAttribute('data-video-title'));
                return;
            }
            var cbtn = e.target.closest && e.target.closest('.js-admin-video-comments');
            if (cbtn) {
                var cardc = cbtn.closest('.tcf-admin-video-card');
                var idc = cardc ? cardc.getAttribute('data-id') : '';
                if (idc) openAdminVideoCommentsModal(idc, null);
                return;
            }
            var abtn = e.target.closest && e.target.closest('.js-admin-video-analytics');
            if (abtn) {
                var carda = abtn.closest('.tcf-admin-video-card');
                var ida = carda ? carda.getAttribute('data-id') : '';
                if (ida) {
                    state.analyticsFocusVideoId = ida;
                    setActiveSection('analytics');
                }
                return;
            }
        });
    }

    function normalizeVis(v) {
        var x = (v || '').toString().trim();
        if (!x) return 'public';
        return x;
    }

    function renderVideosGrid(videos) {
        var grid = document.getElementById('videos-grid');
        if (!grid) return;
        grid.className = 'video-grid sa-videos-orbit-grid';
        if (!videos || !videos.length) {
            grid.innerHTML = '<p class="tcf-admin-video-empty">Aucune vidéo publiée.</p>';
            return;
        }
        grid.innerHTML = videos
            .map(function (v) {
                var title = escHtml(v.title || '');
                var vis = escHtml(normalizeVis(v.visibility));
                var visSlug = normalizeVis(v.visibility).toLowerCase().replace(/\s+/g, '-');
                var th = v.thumbnail_href || v.thumbnail_url || '';
                var vurl = v.video_href || v.video_url || '';
                var durRaw = (v.duration || '').toString().trim();
                var durShow = tcfAdminDurationMeaningful(durRaw) ? durRaw : '';
                var img = th
                    ? '<img src="' + escAttr(th) + '" alt="" loading="lazy">'
                    : '<div class="tcf-admin-video-thumb-placeholder"></div>';
                return (
                    '<article class="tcf-admin-video-card" data-id="' +
                    escAttr(String(v.id)) +
                    '">' +
                    '<button type="button" class="tcf-admin-video-thumb js-admin-play-video" data-video-src="' +
                    escAttr(vurl) +
                    '" data-video-title="' +
                    escAttr(v.title || '') +
                    '" title="Lire la vidéo">' +
                    img +
                    (durShow ? '<span class="tcf-duration">' + escHtml(durShow) + '</span>' : '') +
                    '<span class="tcf-play-ic"><i class="bx bx-play-circle"></i></span>' +
                    '</button>' +
                    '<div class="tcf-admin-video-body">' +
                    '<h4>' +
                    title +
                    '</h4>' +
                    '<p class="tcf-admin-video-meta"><span class="sa-badge sa-badge--video-' +
                    escAttr(visSlug) +
                    '">' +
                    vis +
                    '</span> · ' +
                    escHtml(String(v.views != null ? v.views : 0)) +
                    ' vues</p>' +
                    '<div class="tcf-admin-video-actions">' +
                    '<button type="button" class="btn btn-outline btn-sm sa-btn-icon js-admin-video-comments" aria-label="Commentaires" title="Commentaires"><i class="bx bx-message-dots" aria-hidden="true"></i></button>' +
                    '<button type="button" class="btn btn-outline btn-sm sa-btn-icon js-admin-video-analytics" aria-label="Analyse" title="Analyse de la vidéo"><i class="bx bx-bar-chart-alt-2" aria-hidden="true"></i></button>' +
                    (SA_IS_SUPER
                        ? '<button type="button" class="btn btn-outline btn-sm sa-btn-icon js-edit-video" aria-label="Modifier"><i class="bx bx-edit-alt" aria-hidden="true"></i></button>' +
                          '<button type="button" class="btn btn-outline btn-sm sa-btn-icon btn-danger-outline js-delete-video" aria-label="Supprimer"><i class="bx bx-trash" aria-hidden="true"></i></button>'
                        : '') +
                    '</div></div></article>'
                );
            })
            .join('');
    }

    function fetchVideosFromServer(cb) {
        postForm('get_videos')
            .then(function (j) {
                if (j && j.success) cb && cb(null, j.data || []);
                else cb && cb(new Error('bad'));
            })
            .catch(function () {
                cb && cb(new Error('net'));
            });
    }

    function deleteVideoById(id) {
        postForm('delete_video', { id: id })
            .then(function (j) {
                if (j && j.success) {
                    toast(j.message || 'Supprimé');
                    fetchVideosFromServer(function (err, data) {
                        if (!err && data) renderVideosGrid(data);
                    });
                } else {
                    toast((j && j.message) || 'Erreur', true);
                }
            })
            .catch(function () {
                toast('Erreur réseau', true);
            });
    }

    function thumbHref(v) {
        return v.thumbnail_href || v.thumbnail_url || '';
    }
    function videoHref(v) {
        return v.video_href || v.video_url || '';
    }

    function resetVideoForm() {
        var form = document.getElementById('video-form');
        if (!form) return;
        form.reset();
        var editId = document.getElementById('video-edit-id');
        if (editId) editId.value = '';
        var thPrev = document.getElementById('thumbnail-preview');
        var vidPrev = document.getElementById('video-preview');
        if (thPrev) thPrev.classList.remove('is-visible');
        if (vidPrev) vidPrev.classList.remove('is-visible');
        var thImg = document.getElementById('thumbnail-preview-img');
        var vidPlayer = document.getElementById('video-preview-player');
        if (thImg) thImg.removeAttribute('src');
        if (vidPlayer) {
            vidPlayer.removeAttribute('src');
            try {
                vidPlayer.load();
            } catch (e) {}
        }
        var thLabel = document.getElementById('thumbnail-label');
        var vidLabel = document.getElementById('video-file-label');
        if (thLabel) thLabel.textContent = 'Sélectionner une miniature';
        if (vidLabel) vidLabel.textContent = 'Sélectionner une vidéo';
        var subBtn = document.getElementById('video-form-submit');
        if (subBtn) subBtn.textContent = 'Enregistrer';
        var plBox = document.getElementById('video-playlist-checkboxes');
        if (plBox) renderVideoPlaylistCheckboxes([]);
    }

    function openVideoEditById(id) {
        var arr = typeof videosFromDB !== 'undefined' ? videosFromDB : [];
        var v = null;
        for (var i = 0; i < arr.length; i++) {
            if (String(arr[i].id) === String(id)) {
                v = arr[i];
                break;
            }
        }
        if (!v) {
            fetchVideosFromServer(function (err, data) {
                if (err) return;
                (data || []).some(function (x) {
                    if (String(x.id) === String(id)) {
                        v = x;
                        return true;
                    }
                    return false;
                });
                if (v) fillVideoForm(v);
            });
            return;
        }
        fillVideoForm(v);
    }

    function fillVideoForm(v) {
        var form = document.getElementById('video-form');
        if (!form) return;
        form.style.display = 'block';
        document.getElementById('video-edit-id').value = String(v.id || '');
        document.getElementById('video-title').value = v.title || '';
        document.getElementById('video-description').value = v.description || '';
        document.getElementById('video-visibility').value = normalizeVis(v.visibility || 'public');

        var thInput = document.getElementById('thumbnail-file');
        var vidInput = document.getElementById('video-file');
        if (thInput) thInput.value = '';
        if (vidInput) vidInput.value = '';
        var thLabel = document.getElementById('thumbnail-label');
        var vidLabel = document.getElementById('video-file-label');
        var thu = thumbHref(v);
        var vu = videoHref(v);
        if (thLabel) {
            thLabel.textContent = thu ? 'Remplacer la miniature (optionnel)' : 'Sélectionner une miniature';
        }
        if (vidLabel) {
            vidLabel.textContent = vu ? 'Remplacer le fichier vidéo (optionnel)' : 'Sélectionner une vidéo';
        }

        var thPrev = document.getElementById('thumbnail-preview');
        var thImg = document.getElementById('thumbnail-preview-img');
        if (thPrev && thImg) {
            if (thu) {
                thImg.src = thu;
                thPrev.classList.add('is-visible');
            } else {
                thPrev.classList.remove('is-visible');
            }
        }
        var vidPrev = document.getElementById('video-preview');
        var vidPlayer = document.getElementById('video-preview-player');
        if (vidPrev && vidPlayer) {
            if (vu) {
                try {
                    if (vidPlayer.src && vidPlayer.src.indexOf('blob:') === 0) URL.revokeObjectURL(vidPlayer.src);
                } catch (e) {}
                vidPlayer.src = vu;
                vidPrev.classList.add('is-visible');
            } else {
                vidPrev.classList.remove('is-visible');
                vidPlayer.removeAttribute('src');
                try {
                    vidPlayer.load();
                } catch (e2) {}
            }
        }

        var subBtn = document.getElementById('video-form-submit');
        if (subBtn) subBtn.textContent = 'Enregistrer les modifications';

        var ids = v.playlist_ids || [];
        renderVideoPlaylistCheckboxes(ids);
        try {
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (e) {}
    }

    function renderVideoPlaylistCheckboxes(selectedIds) {
        selectedIds = selectedIds || [];
        var box = document.getElementById('video-playlist-checkboxes');
        if (!box) return;
        if (!state.playlistCache || !state.playlistCache.length) {
            box.innerHTML = '<p style="margin:0;color:var(--sa-muted);font-size:12px;">Aucune playlist.</p>';
            return;
        }
        box.innerHTML = state.playlistCache
            .map(function (pl) {
                var on = selectedIds.indexOf(pl.id) >= 0;
                return (
                    '<label class="tcf-pl-check"><input type="checkbox" name="playlist_ids[]" value="' +
                    escAttr(String(pl.id)) +
                    '"' +
                    (on ? ' checked' : '') +
                    '> <span>' +
                    escHtml(pl.title || '') +
                    '</span></label>'
                );
            })
            .join('');
    }

    function loadPlaylistsCache(cb) {
        postForm('get_playlists')
            .then(function (j) {
                state.playlistCache = (j && j.success && j.data) ? j.data : [];
                cb && cb();
            })
            .catch(function () {
                state.playlistCache = [];
                cb && cb();
            });
    }

    function initVideoForm() {
        var form = document.getElementById('video-form');
        if (!form) return;
        var thInput = document.getElementById('thumbnail-file');
        var vidInput = document.getElementById('video-file');
        var thLabel = document.getElementById('thumbnail-label');
        var vidLabel = document.getElementById('video-file-label');
        var thPrev = document.getElementById('thumbnail-preview');
        var thImg = document.getElementById('thumbnail-preview-img');
        var vidPrev = document.getElementById('video-preview');
        var vidPlayer = document.getElementById('video-preview-player');

        if (thInput && thPrev && thImg) {
            thInput.addEventListener('change', function () {
                var f = thInput.files && thInput.files[0];
                if (!f) {
                    thPrev.classList.remove('is-visible');
                    if (thLabel) thLabel.textContent = 'Sélectionner une miniature';
                    return;
                }
                if (thLabel) thLabel.textContent = f.name || 'Miniature sélectionnée';
                thImg.src = URL.createObjectURL(f);
                thPrev.classList.add('is-visible');
            });
        }
        if (vidInput && vidPrev && vidPlayer) {
            vidInput.addEventListener('change', function () {
                var f = vidInput.files && vidInput.files[0];
                if (!f) {
                    vidPrev.classList.remove('is-visible');
                    vidPlayer.removeAttribute('src');
                    vidPlayer.load();
                    if (vidLabel) vidLabel.textContent = 'Sélectionner une vidéo';
                    return;
                }
                if (vidLabel) vidLabel.textContent = f.name || 'Vidéo sélectionnée';
                try {
                    if (vidPlayer.src && vidPlayer.src.indexOf('blob:') === 0) URL.revokeObjectURL(vidPlayer.src);
                } catch (e) {}
                vidPlayer.src = URL.createObjectURL(f);
                vidPrev.classList.add('is-visible');
            });
        }

        var cancel = document.getElementById('cancel-video-btn');
        if (cancel) cancel.addEventListener('click', resetVideoForm);

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var submitBtn = document.getElementById('video-form-submit');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.5';
                submitBtn.style.cursor = 'not-allowed';
            }
            var fd = new FormData(form);
            var editId = document.getElementById('video-edit-id').value;
            fd.append('action', editId ? 'update_video' : 'add_video');
            if (editId) fd.append('id', editId);
            fetch(ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) {
                    return r.json();
                })
                .then(function (j) {
                    if (j && j.success) {
                        toast(j.message || 'Enregistré');
                        resetVideoForm();
                        fetchVideosFromServer(function (err, data) {
                            if (!err && data) {
                                window.videosFromDB = data;
                                renderVideosGrid(data);
                            }
                        });
                    } else {
                        toast((j && j.message) || 'Erreur', true);
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.style.opacity = '1';
                        submitBtn.style.cursor = 'pointer';
                    }
                })
                .catch(function () {
                    toast('Erreur réseau', true);
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.style.opacity = '1';
                        submitBtn.style.cursor = 'pointer';
                    }
                });
        });
    }

    // Video play modal
    function initVideoModal() {
        var modal = document.getElementById('admin-video-play-modal');
        if (!modal) return;
        modal.querySelectorAll('[data-close-video-modal]').forEach(function (el) {
            el.addEventListener('click', closeVideoModal);
        });
        document.addEventListener('keydown', function (ev) {
            if (ev.key === 'Escape') {
                closeVideoModal();
                closeAdminVideoCommentsModal();
            }
        });
    }

    var stateVcm = { videoId: null };
    var bootVcmModal = false;

    function initVideoCommentsModalUi() {
        if (bootVcmModal) return;
        bootVcmModal = true;
        var modal = document.getElementById('admin-video-comments-modal');
        if (!modal) return;
        modal.querySelectorAll('[data-close-vcmodal]').forEach(function (el) {
            el.addEventListener('click', closeAdminVideoCommentsModal);
        });
    }

    function closeAdminVideoCommentsModal() {
        var modal = document.getElementById('admin-video-comments-modal');
        var player = document.getElementById('admin-vcm-player');
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        if (player) {
            try {
                player.pause();
            } catch (e1) {}
            player.removeAttribute('src');
            player.load();
        }
        var threads = document.getElementById('admin-vcm-threads');
        if (threads) threads.innerHTML = '';
        stateVcm.videoId = null;
    }

    function openAdminVideoCommentsModal(videoId, highlightCommentId) {
        videoId = String(videoId || '').trim();
        if (!videoId) return;
        initVideoCommentsModalUi();
        stateVcm.videoId = videoId;
        var arr = typeof videosFromDB !== 'undefined' ? videosFromDB : [];
        var v = null;
        var i;
        for (i = 0; i < arr.length; i++) {
            if (String(arr[i].id) === String(videoId)) {
                v = arr[i];
                break;
            }
        }
        if (!v) {
            fetchVideosFromServer(function (err, data) {
                if (err) return;
                (data || []).some(function (x) {
                    if (String(x.id) === String(videoId)) {
                        v = x;
                        return true;
                    }
                    return false;
                });
                openAdminVideoCommentsModalFill(v, videoId, highlightCommentId);
            });
            return;
        }
        openAdminVideoCommentsModalFill(v, videoId, highlightCommentId);
    }

    function openAdminVideoCommentsModalFill(v, videoId, highlightCommentId) {
        var modal = document.getElementById('admin-video-comments-modal');
        var player = document.getElementById('admin-vcm-player');
        var title = document.getElementById('admin-vcm-title');
        if (!modal || !player) return;
        var src = v ? videoHref(v) : '';
        var tit = v ? v.title || 'Vidéo' : 'Vidéo #' + videoId;
        if (title) title.textContent = tit;
        player.src = src || '';
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        loadAdminVideoCommentsThread(videoId, highlightCommentId);
    }

    function escHtmlBr(s) {
        return escHtml(s).replace(/\n/g, '<br>');
    }

    function loadAdminVideoCommentsThread(videoId, highlightCommentId) {
        var threadsEl = document.getElementById('admin-vcm-threads');
        if (!threadsEl) return;
        threadsEl.innerHTML = '<p style="padding:12px;color:#64748b;">Chargement…</p>';
        fetch(VIDEOS_API + '?action=comments&video_id=' + encodeURIComponent(videoId), { credentials: 'same-origin' })
            .then(function (r) {
                return r.json();
            })
            .then(function (j) {
                if (!j || !j.ok) {
                    threadsEl.innerHTML = '<p style="padding:12px;color:#b91c1c;">Impossible de charger les commentaires.</p>';
                    return;
                }
                threadsEl.innerHTML = renderAdminCommentHtml(j.comments || [], videoId);
                initAdminVideoCommentReplyHandlers(videoId);
                if (highlightCommentId) {
                    window.setTimeout(function () {
                        var el = document.getElementById('sa-vc-root-' + String(highlightCommentId));
                        if (el) {
                            el.classList.add('sa-vc-thread--highlight');
                            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            window.setTimeout(function () {
                                el.classList.remove('sa-vc-thread--highlight');
                            }, 2200);
                        }
                    }, 80);
                }
            })
            .catch(function () {
                threadsEl.innerHTML = '<p style="padding:12px;color:#b91c1c;">Erreur réseau.</p>';
            });
    }

    function renderAdminCommentHtml(comments, videoId) {
        if (!comments || !comments.length) {
            return '<p style="padding:12px;color:#64748b;">Aucun commentaire pour le moment.</p>';
        }
        return comments
            .map(function (c) {
                var staff = c.is_staff
                    ? ' <span class="sa-badge sa-badge--video-public" style="font-size:0.72rem;">Équipe</span>'
                    : '';
                var replies = (c.replies || [])
                    .map(function (r) {
                        var rs = r.is_staff
                            ? ' <span class="sa-badge sa-badge--video-public" style="font-size:0.72rem;">Équipe</span>'
                            : '';
                        return (
                            '<div class="sa-vc-reply-line"><strong>' +
                            escHtml(r.user_name) +
                            '</strong>' +
                            rs +
                            '<div>' +
                            escHtmlBr(r.body) +
                            '</div></div>'
                        );
                    })
                    .join('');
                return (
                    '<div class="sa-vc-thread" id="sa-vc-root-' +
                    escAttr(String(c.id)) +
                    '">' +
                    '<div class="sa-vc-root-meta"><strong>' +
                    escHtml(c.user_name) +
                    '</strong>' +
                    staff +
                    ' · <span>' +
                    escHtml(c.created_at || '') +
                    '</span></div>' +
                    '<div>' +
                    escHtmlBr(c.body) +
                    '</div>' +
                    replies +
                    '<div class="sa-vc-reply">' +
                    '<textarea class="form-control sa-vc-reply-ta" rows="2" placeholder="Répondre…"></textarea>' +
                    '<button type="button" class="btn btn-primary btn-sm js-sa-vc-send" data-video-id="' +
                    escAttr(String(videoId)) +
                    '" data-parent-id="' +
                    escAttr(String(c.id)) +
                    '">Répondre</button>' +
                    '</div></div>'
                );
            })
            .join('');
    }

    function initAdminVideoCommentReplyHandlers(videoId) {
        var threadsEl = document.getElementById('admin-vcm-threads');
        if (!threadsEl) return;
        threadsEl.querySelectorAll('.js-sa-vc-send').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var wrap = btn.closest('.sa-vc-reply');
                var ta = wrap ? wrap.querySelector('.sa-vc-reply-ta') : null;
                var body = ta ? (ta.value || '').trim() : '';
                if (!body) {
                    toast('Saisissez une réponse.', true);
                    return;
                }
                var pid = btn.getAttribute('data-parent-id');
                btn.disabled = true;
                fetch(VIDEOS_API, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        action: 'reply',
                        video_id: parseInt(videoId, 10),
                        parent_id: parseInt(pid, 10),
                        body: body
                    })
                })
                    .then(function (r) {
                        return r.json();
                    })
                    .then(function (j) {
                        if (!j || !j.ok) {
                            toast((j && j.message) || 'Erreur', true);
                            return;
                        }
                        toast(j.message || 'Réponse publiée.');
                        if (ta) ta.value = '';
                        loadAdminVideoCommentsThread(videoId, null);
                    })
                    .catch(function () {
                        toast('Erreur réseau', true);
                    })
                    .finally(function () {
                        btn.disabled = false;
                    });
            });
        });
    }

    function initSaQueryFocus() {
        var p;
        try {
            p = new URLSearchParams(window.location.search);
        } catch (e) {
            p = null;
        }
        if (!p || !p.get('sa_focus')) return;
        var focus = p.get('sa_focus');
        if (focus === 'video_comment') {
            var vid = p.get('video_id');
            var cid = p.get('comment_id');
            setActiveSection('videos', { skipHash: true });
            ensureVideosBoot();
            initVideoCommentsModalUi();
            fetchVideosFromServer(function (err, data) {
                if (!err && data) renderVideosGrid(data);
                openAdminVideoCommentsModal(vid, cid);
            });
        } else if (focus === 'testimonial') {
            var tid = p.get('id');
            setActiveSection('testimonials', { skipHash: true });
            ensureTestimonialsBoot();
            loadTestimonialsAdmin(tid);
        }
        try {
            var u = new URL(window.location.href);
            u.searchParams.delete('sa_focus');
            u.searchParams.delete('video_id');
            u.searchParams.delete('comment_id');
            u.searchParams.delete('id');
            window.history.replaceState({}, '', u.pathname + u.search + u.hash);
        } catch (e2) {}
    }

    function openVideoModal(src, title) {
        var modal = document.getElementById('admin-video-play-modal');
        var player = document.getElementById('admin-video-play-player');
        var t = document.getElementById('admin-video-play-title');
        if (!modal || !player) return;
        if (t) t.textContent = title || '';
        player.src = src || '';
        modal.classList.add('is-open');
        try {
            player.play();
        } catch (e) {}
    }

    function closeVideoModal() {
        var modal = document.getElementById('admin-video-play-modal');
        var player = document.getElementById('admin-video-play-player');
        if (!modal) return;
        modal.classList.remove('is-open');
        if (player) {
            try {
                player.pause();
            } catch (e) {}
            player.removeAttribute('src');
            player.load();
        }
    }

    // Channel playlists
    var bootPl = false;

    function renderPlaylistVideoCheckboxesAdmin(videos, selectedIds) {
        var el = document.getElementById('channel-playlist-video-checkboxes');
        if (!el) return;
        var sel = (selectedIds || []).map(function (x) {
            return parseInt(String(x), 10);
        });
        if (!videos || !videos.length) {
            el.innerHTML =
                '<p style="margin:0;font-size:13px;color:#64748b;">Aucune vidéo. Publiez d’abord des vidéos dans « Vidéos ».</p>';
            return;
        }
        el.innerHTML = videos
            .map(function (v) {
                var id = parseInt(String(v.id), 10);
                var checked = sel.indexOf(id) >= 0;
                return (
                    '<label style="display:flex;align-items:center;gap:8px;padding:4px 0;cursor:pointer;font-size:13px;">' +
                    '<input type="checkbox" class="js-pl-vid" value="' +
                    escAttr(String(id)) +
                    '"' +
                    (checked ? ' checked' : '') +
                    '> <span>' +
                    escHtml(v.title || 'Vidéo #' + id) +
                    '</span></label>'
                );
            })
            .join('');
    }

    function getSelectedPlaylistVideoIdsAdmin() {
        var el = document.getElementById('channel-playlist-video-checkboxes');
        if (!el) return [];
        var ids = [];
        el.querySelectorAll('.js-pl-vid:checked').forEach(function (cb) {
            ids.push(parseInt(cb.value, 10));
        });
        return ids;
    }

    function resetChannelPlaylistFormUi() {
        var f = document.getElementById('channel-playlist-form');
        if (f) f.style.display = 'none';
        var hid = document.getElementById('channel-playlist-edit-id');
        if (hid) hid.value = '';
        var t = document.getElementById('channel-playlist-title');
        if (t) t.value = '';
        var d = document.getElementById('channel-playlist-description');
        if (d) d.value = '';
        var v = document.getElementById('channel-playlist-visibility');
        if (v) v.value = 'public';
        var box = document.getElementById('channel-playlist-video-checkboxes');
        if (box) box.innerHTML = '';
    }

    function ensureChannelPlaylistsBoot() {
        if (bootPl) return;
        bootPl = true;
        initChannelPlaylistForm();
        document.body.addEventListener('click', function (e) {
            var del = e.target.closest && e.target.closest('.js-del-channel-playlist');
            if (!del) return;
            var id = del.getAttribute('data-id');
            if (!id || !window.confirm('Supprimer cette playlist ?')) return;
            postForm('delete_playlist', { id: id })
                .then(function (j) {
                    if (j && j.success) {
                        toast(j.message || 'Supprimé');
                        loadChannelPlaylistsAdmin();
                        loadPlaylistsCache(function () {
                            renderVideoPlaylistCheckboxes([]);
                        });
                    } else {
                        toast((j && j.message) || 'Erreur', true);
                    }
                })
                .catch(function () {
                    toast('Erreur réseau', true);
                });
        });
        document.body.addEventListener('click', function (e) {
            var ed = e.target.closest && e.target.closest('.js-edit-channel-playlist');
            if (!ed) return;
            var id = ed.getAttribute('data-id');
            if (!id) return;
            postForm('get_playlists').then(function (j) {
                var rows = (j && j.success && j.data) ? j.data : [];
                var pl = null;
                rows.forEach(function (r) {
                    if (String(r.id) === String(id)) pl = r;
                });
                if (!pl) {
                    toast('Playlist introuvable.', true);
                    return;
                }
                var hid = document.getElementById('channel-playlist-edit-id');
                var t = document.getElementById('channel-playlist-title');
                var d = document.getElementById('channel-playlist-description');
                var vis = document.getElementById('channel-playlist-visibility');
                var form = document.getElementById('channel-playlist-form');
                if (hid) hid.value = String(pl.id);
                if (t) t.value = pl.title || '';
                if (d) d.value = pl.description || '';
                if (vis) vis.value = pl.visibility === 'private' ? 'private' : 'public';
                if (form) form.style.display = 'block';
                fetchVideosFromServer(function (err, data) {
                    var vids = !err && data ? data : [];
                    renderPlaylistVideoCheckboxesAdmin(vids, pl.video_ids || []);
                });
            });
        });
    }

    function initChannelPlaylistForm() {
        var addBtn = document.getElementById('channel-playlist-add-btn');
        var cancelBtn = document.getElementById('channel-playlist-cancel-btn');
        var form = document.getElementById('channel-playlist-form');
        if (!form) return;
        if (addBtn) {
            addBtn.addEventListener('click', function () {
                var hid = document.getElementById('channel-playlist-edit-id');
                if (hid) hid.value = '';
                var ti = document.getElementById('channel-playlist-title');
                if (ti) ti.value = '';
                var de = document.getElementById('channel-playlist-description');
                if (de) de.value = '';
                var vi = document.getElementById('channel-playlist-visibility');
                if (vi) vi.value = 'public';
                form.style.display = 'block';
                fetchVideosFromServer(function (err, data) {
                    var vids = !err && data ? data : [];
                    renderPlaylistVideoCheckboxesAdmin(vids, []);
                });
            });
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', resetChannelPlaylistFormUi);
        }
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var editId = document.getElementById('channel-playlist-edit-id');
            var title = document.getElementById('channel-playlist-title');
            var desc = document.getElementById('channel-playlist-description');
            var vis = document.getElementById('channel-playlist-visibility');
            var fd = new FormData();
            fd.append('action', 'save_playlist');
            if (editId && editId.value) fd.append('id', editId.value);
            fd.append('title', title ? title.value.trim() : '');
            fd.append('description', desc ? desc.value.trim() : '');
            fd.append('visibility', vis ? vis.value : 'public');
            fd.append('video_ids', JSON.stringify(getSelectedPlaylistVideoIdsAdmin()));
            fetch(ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) {
                    return r.json();
                })
                .then(function (j) {
                    if (j && j.success) {
                        toast(j.message || 'Enregistré');
                        resetChannelPlaylistFormUi();
                        loadChannelPlaylistsAdmin();
                        loadPlaylistsCache(function () {
                            renderVideoPlaylistCheckboxes([]);
                        });
                    } else {
                        toast((j && j.message) || 'Erreur', true);
                    }
                })
                .catch(function () {
                    toast('Erreur réseau', true);
                });
        });
    }

    function loadChannelPlaylistsAdmin() {
        var tbody = document.getElementById('channel-playlists-tbody');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="5" style="padding:12px;">Chargement…</td></tr>';
        postForm('get_playlists')
            .then(function (j) {
                var rows = (j && j.success && j.data) ? j.data : [];
                if (!rows.length) {
                    tbody.innerHTML = '<tr><td colspan="5" style="padding:12px;color:#64748b;">Aucune playlist.</td></tr>';
                    return;
                }
                tbody.innerHTML = rows
                    .map(function (p) {
                        return (
                            '<tr><td style="padding:8px;">' +
                            escHtml(p.title || '') +
                            '</td><td style="padding:8px;">' +
                            escHtml(p.visibility || '') +
                            '</td><td style="padding:8px;">' +
                            escHtml(String(p.video_count || 0)) +
                            '</td><td style="padding:8px;white-space:nowrap;">' +
                            escHtml(p.created_at || '') +
                            '</td>' +
                            saActionsRow(
                                '<button type="button" class="btn btn-outline btn-sm sa-btn-icon js-edit-channel-playlist" data-id="' +
                                    escAttr(String(p.id)) +
                                    '" aria-label="Modifier"><i class="bx bx-edit-alt" aria-hidden="true"></i></button><button type="button" class="btn btn-outline btn-sm sa-btn-icon btn-danger-outline js-del-channel-playlist" data-id="' +
                                    escAttr(String(p.id)) +
                                    '" aria-label="Supprimer"><i class="bx bx-trash" aria-hidden="true"></i></button>'
                            ) +
                            '</tr>'
                        );
                    })
                    .join('');
            })
            .catch(function () {
                tbody.innerHTML = '<tr><td colspan="5" style="padding:12px;color:#b91c1c;">Erreur.</td></tr>';
            });
    }

    // Channel posts
    var bootPosts = false;

    function channelPostTypeLabel(t) {
        if (t === 'image') return 'Image';
        if (t === 'poll') return 'Sondage';
        return 'Texte';
    }

    function fillChannelPostVideoSelect(videos) {
        var sel = document.getElementById('channel-post-video-id');
        if (!sel) return;
        var cur = sel.value;
        sel.innerHTML = '<option value="">— Aucune —</option>';
        (videos || []).forEach(function (v) {
            var o = document.createElement('option');
            o.value = String(v.id);
            o.textContent = v.title || 'Vidéo #' + v.id;
            sel.appendChild(o);
        });
        sel.value = cur;
    }

    function updateChannelPostTypeFields() {
        var t = document.getElementById('channel-post-type');
        var v = t ? t.value : 'text';
        var im = document.getElementById('channel-post-image-wrap');
        var pl = document.getElementById('channel-post-poll-wrap');
        if (im) im.style.display = v === 'image' ? 'block' : 'none';
        if (pl) pl.style.display = v === 'poll' ? 'block' : 'none';
    }

    function resetChannelPostFormUi() {
        var form = document.getElementById('channel-post-form');
        if (form) {
            form.style.display = 'none';
            form.reset();
        }
        var hid = document.getElementById('channel-post-edit-id');
        if (hid) hid.value = '';
        var prev = document.getElementById('channel-post-image-preview');
        if (prev) {
            prev.src = '';
            prev.style.display = 'none';
        }
        var rem = document.getElementById('channel-post-remove-image');
        if (rem) rem.checked = false;
        updateChannelPostTypeFields();
    }

    /** Nouvelle publication : réinitialise les champs et affiche le formulaire (sans le masquer avant). */
    function openNewChannelPostForm() {
        var form = document.getElementById('channel-post-form');
        if (!form) return;
        var hid = document.getElementById('channel-post-edit-id');
        if (hid) hid.value = '';
        form.reset();
        var prev = document.getElementById('channel-post-image-preview');
        if (prev) {
            prev.src = '';
            prev.style.display = 'none';
        }
        var rem = document.getElementById('channel-post-remove-image');
        if (rem) rem.checked = false;
        updateChannelPostTypeFields();
        form.style.display = 'block';
        try {
            form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } catch (e2) {}
        fetchVideosFromServer(function (err, data) {
            if (!err && data) fillChannelPostVideoSelect(data);
        });
    }

    var channelPostFormBound = false;
    function bindChannelPostFormOnce() {
        if (channelPostFormBound) return;
        channelPostFormBound = true;
        var typeSel = document.getElementById('channel-post-type');
        if (typeSel) typeSel.addEventListener('change', updateChannelPostTypeFields);
        document.body.addEventListener('click', function (e) {
            var addBtn = e.target.closest && e.target.closest('#channel-post-add-btn');
            if (addBtn) {
                e.preventDefault();
                openNewChannelPostForm();
            }
        });
        var cancel = document.getElementById('channel-post-cancel-btn');
        if (cancel) cancel.addEventListener('click', resetChannelPostFormUi);
        var form = document.getElementById('channel-post-form');
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var fd = new FormData();
                fd.append('action', 'save_channel_post');
                var id = document.getElementById('channel-post-edit-id');
                if (id && id.value) fd.append('id', id.value);
                var pt = document.getElementById('channel-post-type');
                fd.append('post_type', pt ? pt.value : 'text');
                var tit = document.getElementById('channel-post-title');
                fd.append('title', tit ? tit.value.trim() : '');
                var bod = document.getElementById('channel-post-body');
                fd.append('body', bod ? bod.value.trim() : '');
                var vis = document.getElementById('channel-post-visibility');
                fd.append('visibility', vis ? vis.value : 'public');
                var vidSel = document.getElementById('channel-post-video-id');
                fd.append('video_id', vidSel && vidSel.value ? vidSel.value : '');
                var pollTa = document.getElementById('channel-post-poll-options');
                fd.append('poll_options', pollTa ? pollTa.value : '');
                var rem = document.getElementById('channel-post-remove-image');
                if (rem && rem.checked) fd.append('remove_image', '1');
                var fileIn = document.getElementById('channel-post-image');
                if (fileIn && fileIn.files && fileIn.files[0]) {
                    fd.append('image', fileIn.files[0]);
                }
                fetch(ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) {
                        return r.json().catch(function () {
                            return { success: false, message: 'Réponse invalide du serveur.' };
                        });
                    })
                    .then(function (j) {
                        if (j && j.success) {
                            toast(j.message || 'Enregistré');
                            resetChannelPostFormUi();
                            loadChannelPostsAdmin();
                        } else {
                            toast((j && j.message) || 'Erreur', true);
                        }
                    })
                    .catch(function () {
                        toast('Erreur réseau ou serveur.', true);
                    });
            });
        }
    }

    function ensureChannelPostsBoot() {
        if (bootPosts) return;
        bootPosts = true;
        bindChannelPostFormOnce();
        document.body.addEventListener('click', function (e) {
            var del = e.target.closest && e.target.closest('.js-del-channel-post');
            if (!del) return;
            var id = del.getAttribute('data-id');
            if (!id || !window.confirm('Supprimer cette publication ?')) return;
            postForm('delete_channel_post', { id: id })
                .then(function (j) {
                    if (j && j.success) {
                        toast(j.message || 'Supprimé');
                        loadChannelPostsAdmin();
                    } else {
                        toast((j && j.message) || 'Erreur', true);
                    }
                })
                .catch(function () {
                    toast('Erreur réseau', true);
                });
        });
        document.body.addEventListener('click', function (e) {
            var ed = e.target.closest && e.target.closest('.js-edit-channel-post');
            if (!ed) return;
            var id = ed.getAttribute('data-id');
            if (!id) return;
            postForm('get_channel_posts')
                .then(function (j) {
                    var rows = (j && j.success && j.data) ? j.data : [];
                    var p = rows.filter(function (x) {
                        return String(x.id) === String(id);
                    })[0];
                    if (!p) return;
                    fetchVideosFromServer(function (err, data) {
                        if (!err && data) fillChannelPostVideoSelect(data);
                        var hid = document.getElementById('channel-post-edit-id');
                        var typ = document.getElementById('channel-post-type');
                        var ti = document.getElementById('channel-post-title');
                        var bo = document.getElementById('channel-post-body');
                        var vis = document.getElementById('channel-post-visibility');
                        var vidSel = document.getElementById('channel-post-video-id');
                        var pollTa = document.getElementById('channel-post-poll-options');
                        var prev = document.getElementById('channel-post-image-preview');
                        if (hid) hid.value = String(p.id || '');
                        if (typ) typ.value = p.post_type || 'text';
                        if (ti) ti.value = p.title || '';
                        if (bo) bo.value = p.body || '';
                        if (vis) vis.value = p.visibility || 'public';
                        if (vidSel) vidSel.value = p.video_id ? String(p.video_id) : '';
                        if (pollTa) {
                            pollTa.value = Array.isArray(p.poll_options) && p.poll_options.length
                                ? p.poll_options.join('\n')
                                : '';
                        }
                        if (prev) {
                            if (p.image_public_href) {
                                prev.src = p.image_public_href;
                                prev.style.display = 'block';
                            } else {
                                prev.src = '';
                                prev.style.display = 'none';
                            }
                        }
                        var rem = document.getElementById('channel-post-remove-image');
                        if (rem) rem.checked = false;
                        var form = document.getElementById('channel-post-form');
                        if (form) form.style.display = 'block';
                        updateChannelPostTypeFields();
                    });
                });
        });
    }

    function loadChannelPostsAdmin() {
        var tbody = document.getElementById('channel-posts-tbody');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="6" style="padding:12px;">Chargement…</td></tr>';
        postForm('get_channel_posts')
            .then(function (j) {
                var rows = (j && j.success && j.data) ? j.data : [];
                if (!rows.length) {
                    tbody.innerHTML = '<tr><td colspan="6" style="padding:12px;color:#64748b;">Aucune publication.</td></tr>';
                    return;
                }
                tbody.innerHTML = rows
                    .map(function (p) {
                        var body = (p.body || '').length > 80 ? (p.body || '').substring(0, 80) + '…' : (p.body || '');
                        var ptype = p.post_type || 'text';
                        var excerpt = escHtml(p.title || '') || escHtml(body) || '—';
                        if (ptype === 'image' && !body && !p.title) excerpt = '(image)';
                        if (ptype === 'poll') {
                            excerpt = escHtml(p.title || body || '(sondage)');
                            var opts = p.poll_options || [];
                            var pcts = p.poll_pcts || [];
                            if (opts.length && pcts.length) {
                                var bits = [];
                                for (var pi = 0; pi < Math.min(opts.length, pcts.length); pi++) {
                                    bits.push(escHtml(String(opts[pi])) + ' <strong>' + pcts[pi] + '%</strong>');
                                }
                                excerpt +=
                                    '<div style="font-size:0.78rem;color:#64748b;margin-top:6px;line-height:1.35;">' +
                                    bits.join(' · ') +
                                    '</div>';
                            }
                        }
                        return (
                            '<tr><td style="padding:8px;white-space:nowrap;">' +
                            escHtml(p.created_at || '') +
                            '</td><td style="padding:8px;">' +
                            escHtml(channelPostTypeLabel(ptype)) +
                            '</td><td style="padding:8px;">' +
                            excerpt +
                            '</td><td style="padding:8px;">' +
                            escHtml(p.visibility || '') +
                            '</td><td style="padding:8px;">' +
                            (p.video_id ? escHtml(String(p.video_id)) : '—') +
                            '</td>' +
                            saActionsRow(
                                '<button type="button" class="btn btn-outline btn-sm sa-btn-icon js-edit-channel-post" data-id="' +
                                    escAttr(String(p.id)) +
                                    '" aria-label="Modifier"><i class="bx bx-edit-alt" aria-hidden="true"></i></button><button type="button" class="btn btn-outline btn-sm sa-btn-icon btn-danger-outline js-del-channel-post" data-id="' +
                                    escAttr(String(p.id)) +
                                    '" aria-label="Supprimer"><i class="bx bx-trash" aria-hidden="true"></i></button>'
                            ) +
                            '</tr>'
                        );
                    })
                    .join('');
            })
            .catch(function () {
                tbody.innerHTML = '<tr><td colspan="6" style="padding:12px;color:#b91c1c;">Erreur.</td></tr>';
            });
    }

    // ---------------- Modals close ----------------
    function initModalsClose() {
        $all('.modal-close').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var m = btn.closest('.modal');
                if (m) m.classList.remove('is-open');
            });
        });
        $all('.modal').forEach(function (m) {
            m.addEventListener('click', function (e) {
                if (e.target === m) m.classList.remove('is-open');
            });
        });
    }

    function wireAdminNotificationBell() {
        var bell = document.getElementById('showNotifications');
        var page = document.getElementById('notificationPage');
        var ov = document.getElementById('notificationOverlay');
        if (!bell || !page || !ov) return;
        bell.addEventListener('click', function (e) {
            e.preventDefault();
            page.classList.add('active');
            ov.classList.add('active');
        });
    }

    // ---------------- Boot ----------------
    if (document.body && document.body.classList.contains('tcf-superadmin-app')) {
        initTheme();
    }
    document.addEventListener('DOMContentLoaded', function () {
        initTheme();
        wireAdminNotificationBell();
        initTopicsMenu();
        initVideosMenu();
        initSubscriptionMenu();
        initSiteManagementMenu();
        initTopicsForms();
        initUsersSection();
        initAdminsSection();
        initMessagesSection();
        initNotifications();
        initModalsClose();
        initTraceListeners();
        initActivityFeedControls();
        initAnalyticsPeriodListener();

        if (typeof Chart !== 'undefined') {
            Chart.defaults.color = chartTextColor();
            Chart.defaults.borderColor = 'rgba(148,163,184,0.2)';
        }
        window.TCF_ADMIN_SESSION_ID = typeof TCF_ADMIN_SESSION_ID_INLINE !== 'undefined' ? TCF_ADMIN_SESSION_ID_INLINE : null;

        initRouter();
        initSaQueryFocus();
    });
})();

