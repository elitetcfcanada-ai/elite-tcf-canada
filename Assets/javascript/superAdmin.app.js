/**
 * Super Admin — tableau de bord, stats, traçabilité (Chart.js + Leaflet), utilisateurs,
 * messages communautaires, chat, sujets (topics), administrateurs, notifications.
 * S’appuie sur les actions POST de superAdmin.php et les jeux de données initiaux (usersFromDB, …).
 */
(function () {
    'use strict';

    var ENDPOINT = 'superAdmin.php';
    var THEME_KEY = 'tcf_superadmin_theme_v2';

    var TOPIC_SECTIONS = {
        'topics-written': { type: 'Compréhension Écrite', title: 'Gestion des sujets — Compréhension écrite' },
        'topics-oral': { type: 'Compréhension Orale', title: 'Gestion des sujets — Compréhension orale' },
        'topics-expression': { type: 'Expression Écrite', title: 'Gestion des sujets — Expression écrite' },
        'topics-speaking': { type: 'Expression Orale', title: 'Gestion des sujets — Expression orale' }
    };

    var traceState = { map: null, charts: {} };
    var chatState = { userId: null };
    var topicsState = { targetId: 'topics-written' };

    function $(sel, root) {
        return (root || document).querySelector(sel);
    }

    function $all(sel, root) {
        return [].slice.call((root || document).querySelectorAll(sel));
    }

    function toast(msg, isErr) {
        if (window.TCF_ADMIN_TOAST) {
            window.TCF_ADMIN_TOAST(msg, isErr);
            return;
        }
        window.alert(msg);
    }

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

    function initTheme() {
        /* Délégué à superAdmin.ui.js (html[data-sa-theme]) — no-op ici pour éviter les conflits. */
    }

    function initTopicsMenu() {
        var menu = $('#topics-menu');
        var sub = $('#topics-submenu');
        if (menu && sub) {
            menu.addEventListener('click', function (e) {
                e.stopPropagation();
                menu.classList.toggle('is-expanded');
                sub.classList.toggle('is-open');
            });
        }
    }

    function setTopicContext(targetId) {
        var cfg = TOPIC_SECTIONS[targetId];
        if (!cfg) return;
        topicsState.targetId = targetId;
        var titleEl = $('#topics-section-title');
        var typeInput = $('#topic-type');
        if (titleEl) titleEl.textContent = cfg.title;
        if (typeInput) typeInput.value = cfg.type;
        loadTopicsTable(cfg.type);
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
        var tb = $('#topics-table tbody');
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
                    '</td><td><button type="button" class="btn btn-outline btn-sm sa-topic-edit">Modifier</button> <button type="button" class="btn btn-outline btn-sm btn-danger-outline sa-topic-del">Supprimer</button></td></tr>'
                );
            })
            .join('');
    }

    function initTopicsForms() {
        var addBtn = $('#add-topic-btn');
        var cancelBtn = $('#cancel-topic-btn');
        var form = $('#topic-form');
        if (addBtn && form) {
            addBtn.addEventListener('click', function () {
                $('#topic-edit-id').value = '';
                $('#topic-title').value = '';
                $('#json-file-label').textContent = 'Choisir un fichier JSON';
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
                var editId = $('#topic-edit-id').value;
                fd.append('action', editId ? 'update_topic' : 'add_topic');
                if (editId) fd.append('id', editId);
                fd.append('type', $('#topic-type').value);
                fd.append('title', $('#topic-title').value);
                fd.append('visibility', $('#topic-visibility').value);
                fetch(ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) {
                        return r.json();
                    })
                    .then(function (j) {
                        if (j && j.success) {
                            toast(j.message || 'OK');
                            form.style.display = 'none';
                            setTopicContext(topicsState.targetId);
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
            var ed = e.target.closest && e.target.closest('.sa-topic-edit');
            if (ed) {
                var tr = ed.closest('tr');
                var id = tr && tr.getAttribute('data-id');
                if (!id) return;
                postForm('get_topics', { type: $('#topic-type').value })
                    .then(function (j) {
                        if (!j || !j.success || !j.data) return;
                        var t = j.data.filter(function (x) {
                            return String(x.id) === String(id);
                        })[0];
                        if (!t) return;
                        $('#topic-edit-id').value = String(t.id);
                        $('#topic-title').value = t.title || '';
                        $('#topic-visibility').value = t.visibility || 'gratuit';
                        $('#topic-form').style.display = 'block';
                    });
                return;
            }
            var del = e.target.closest && e.target.closest('.sa-topic-del');
            if (del) {
                var tr2 = del.closest('tr');
                var id2 = tr2 && tr2.getAttribute('data-id');
                if (!id2 || !window.confirm('Supprimer ce sujet ?')) return;
                postForm('delete_topic', { id: id2 }).then(function (j) {
                    if (j && j.success) {
                        toast(j.message || 'Supprimé');
                        setTopicContext(topicsState.targetId);
                    } else {
                        toast((j && j.message) || 'Erreur', true);
                    }
                });
            }
        });
    }

    function chartTextColor() {
        return '#141622';
    }

    function destroyChart(key) {
        if (traceState.charts[key]) {
            traceState.charts[key].destroy();
            delete traceState.charts[key];
        }
    }

    function loadTraceability() {
        var rangeEl = $('#trace-range');
        var range = rangeEl ? rangeEl.value : '30d';
        postForm('get_traceability', { range: range })
            .then(function (j) {
                if (!j || !j.success || !j.data) return;
                var d = j.data;
                applyTraceMetric(d);
                buildTrafficChart('traceTrafficVisitsChart', d.traffic_visits || [], 'Sources visites');
                buildTrafficChart('traceTrafficSignupsChart', d.traffic_signups || [], 'Sources inscriptions');
                updateGeoLayer(d);
                var mode = $('#trace-geo-mode') ? $('#trace-geo-mode').value : 'visits';
                buildCountriesChart(d, mode);
            })
            .catch(function () {});
    }

    function applyTraceMetric(d) {
        var metricEl = $('#trace-metric');
        var m = metricEl ? metricEl.value : 'visits';
        var labels, values;
        if (m === 'users') {
            labels = d.users_labels;
            values = d.users_values;
        } else if (m === 'subs') {
            labels = d.payments_count_labels;
            values = d.payments_count_values;
        } else if (m === 'revenue') {
            labels = d.revenue_labels;
            values = d.revenue_values;
        } else {
            labels = d.visits_labels;
            values = d.visits_values;
        }
        destroyChart('time');
        var canvas = $('#traceTimeChart');
        if (!canvas || typeof Chart === 'undefined') return;
        var tc = chartTextColor();
        traceState.charts.time = new Chart(canvas, {
            type: 'line',
            data: {
                labels: labels || [],
                datasets: [
                    {
                        label: m,
                        data: values || [],
                        borderColor: '#d30d0d',
                        backgroundColor: 'rgba(211,13,13,0.12)',
                        fill: true,
                        tension: 0.25
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: tc } } },
                scales: {
                    x: { ticks: { color: tc }, grid: { color: 'rgba(20,22,34,0.08)' } },
                    y: { ticks: { color: tc }, grid: { color: 'rgba(20,22,34,0.08)' }, beginAtZero: true }
                }
            }
        });
    }

    function buildTrafficChart(canvasId, rows, label) {
        destroyChart(canvasId);
        var canvas = $('#' + canvasId);
        if (!canvas || typeof Chart === 'undefined') return;
        var labels = (rows || []).map(function (r) {
            return r.src || '—';
        });
        var data = (rows || []).map(function (r) {
            return parseInt(r.c, 10) || 0;
        });
        var tc = chartTextColor();
        traceState.charts[canvasId] = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [
                    {
                        data: data,
                        backgroundColor: [
                            '#d30d0d',
                            '#b00b0b',
                            'rgba(20,22,34,0.45)',
                            'rgba(20,22,34,0.3)',
                            'rgba(20,22,34,0.2)',
                            'rgba(20,22,34,0.12)'
                        ]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { color: tc } } }
            }
        });
    }

    function buildCountriesChart(d, mode) {
        destroyChart('countries');
        var list = mode === 'signups' ? d.signup_countries || [] : d.visit_countries || [];
        var title = $('#trace-countries-title');
        if (title) {
            title.textContent =
                mode === 'signups' ? 'Répartition par pays — inscriptions' : 'Répartition par pays — visites';
        }
        var canvas = $('#traceCountriesChart');
        if (!canvas || typeof Chart === 'undefined') return;
        var labels = list.slice(0, 12).map(function (r) {
            return r.name || r.code || '—';
        });
        var values = list.slice(0, 12).map(function (r) {
            return parseInt(r.c, 10) || 0;
        });
        var tc = chartTextColor();
        traceState.charts.countries = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{ label: 'Nombre', data: values, backgroundColor: 'rgba(211,13,13,0.55)' }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: tc }, grid: { color: 'rgba(20,22,34,0.08)' }, beginAtZero: true },
                    y: { ticks: { color: tc }, grid: { display: false } }
                }
            }
        });
    }

    function updateGeoLayer(d) {
        var el = $('#traceMapGeo');
        if (!el || typeof L === 'undefined') return;
        var mode = $('#trace-geo-mode') ? $('#trace-geo-mode').value : 'visits';
        var list = mode === 'signups' ? d.signup_countries || [] : d.visit_countries || [];
        if (traceState.map) {
            traceState.map.remove();
            traceState.map = null;
        }
        traceState.map = L.map(el, { worldCopyJump: true }).setView([20, 0], 2);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(traceState.map);
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
                .addTo(traceState.map)
                .bindPopup(escHtml(r.name || r.code || '') + ': <strong>' + c + '</strong>');
        });
        setTimeout(function () {
            if (traceState.map) traceState.map.invalidateSize();
        }, 200);
    }

    function refreshStats() {
        postForm('get_stats', {})
            .then(function (j) {
                if (!j || !j.success || !j.data) return;
                var x = j.data;
                var uc = $('#users-count');
                var vc = $('#visitors-count');
                var sc = $('#subs-count');
                var rc = $('#revenue-count');
                if (uc) uc.textContent = String(x.users != null ? x.users : 0);
                if (vc) vc.textContent = String(x.visitors != null ? x.visitors : 0);
                if (sc) sc.textContent = String(x.subs != null ? x.subs : 0);
                if (rc) rc.textContent = '$' + (parseFloat(x.revenue) || 0).toFixed(2);
            })
            .catch(function () {});
    }

    function renderActivities(list) {
        var el = $('#activity-list');
        if (!el) return;
        if (!list || !list.length) {
            el.innerHTML = '<p style="color:var(--sa-muted);font-size:0.875rem;">Aucune activité récente.</p>';
            return;
        }
        el.innerHTML = list
            .map(function (a) {
                return (
                    '<div class="activity-item"><div class="activity-item-icon"><i class="bx ' +
                    escAttr(a.icon || 'bx-bell') +
                    '"></i></div><div class="activity-item-body"><strong>' +
                    escHtml(a.title) +
                    '</strong><div>' +
                    escHtml(a.description || '') +
                    '</div><div class="activity-item-meta">' +
                    escHtml(a.user_name || '') +
                    ' · ' +
                    escHtml(a.created_at || '') +
                    '</div></div></div>'
                );
            })
            .join('');
    }

    function loadActivities() {
        postForm('get_activities', {})
            .then(function (j) {
                if (j && j.success && j.data) renderActivities(j.data);
            })
            .catch(function () {});
    }

    function subscriptionBadgeClass(sub) {
        var s = (sub || 'free').toLowerCase();
        if (s === 'monthly') return 'sa-badge--monthly';
        if (s === 'annual') return 'sa-badge--annual';
        return 'sa-badge--free';
    }

    function renderUsersTable(users) {
        var tb = $('#users-table tbody');
        if (!tb) return;
        if (!users || !users.length) {
            tb.innerHTML = '<tr><td colspan="8" style="padding:12px;">Aucun utilisateur.</td></tr>';
            return;
        }
        tb.innerHTML = users
            .map(function (u) {
                var av = u.avatar_url
                    ? '<img class="sa-avatar-sm" src="' + escAttr(u.avatar_url) + '" alt="">'
                    : '<span class="sa-avatar-fallback">' + escHtml((u.name || '?').substring(0, 2).toUpperCase()) + '</span>';
                var dot = u.is_online ? 'presence-dot--on' : 'presence-dot--off';
                return (
                    '<tr data-user=\'' +
                    escAttr(JSON.stringify(u)) +
                    '\'>' +
                    '<td><div class="sa-user-cell">' +
                    av +
                    '<span>' +
                    escHtml(u.name) +
                    '<span class="presence-dot ' +
                    dot +
                    '" title="' +
                    (u.is_online ? 'En ligne' : 'Hors ligne') +
                    '"></span></span></div></td>' +
                    '<td>' +
                    escHtml(u.email) +
                    '</td>' +
                    '<td><span class="sa-badge ' +
                    subscriptionBadgeClass(u.subscription_type) +
                    '">' +
                    escHtml(u.subscription_type || 'free') +
                    '</span></td>' +
                    '<td><span class="sa-badge sa-badge--' +
                    (u.status === 'inactive' ? 'inactive' : 'active') +
                    '">' +
                    escHtml(u.status || '') +
                    '</span></td>' +
                    '<td>' +
                    escHtml(u.created_at || '') +
                    '</td>' +
                    '<td>' +
                    escHtml(String(u.activity_days_count != null ? u.activity_days_count : 0)) +
                    '</td>' +
                    '<td>' +
                    escHtml(u.activity_last_date || '—') +
                    '</td>' +
                    '<td><button type="button" class="btn btn-outline btn-sm sa-user-view">Profil</button> <button type="button" class="btn btn-outline btn-sm sa-user-edit">Modifier</button> <button type="button" class="btn btn-outline btn-sm btn-danger-outline sa-user-del">Supprimer</button></td></tr>'
                );
            })
            .join('');
    }

    function openUserModal(edit, u) {
        var modal = $('#user-modal');
        var pwdFields = $all('.user-password-fields');
        $('#user-edit-id').value = edit && u ? String(u.id) : '';
        $('#user-name').value = u ? u.name || '' : '';
        $('#user-email').value = u ? u.email || '' : '';
        $('#user-subscription').value = u ? u.subscription_type || 'free' : 'free';
        $('#user-status').value = u ? u.status || 'active' : 'active';
        $('#user-password').value = '';
        $('#user-password-confirm').value = '';
        pwdFields.forEach(function (el) {
            el.style.display = edit ? 'none' : 'block';
        });
        $('.modal-title', modal).textContent = edit ? 'Modifier utilisateur' : 'Ajouter un utilisateur';
        modal.classList.add('is-open');
    }

    function initUsersSection() {
        var addBtn = $('#add-user-btn');
        if (addBtn) {
            addBtn.addEventListener('click', function () {
                openUserModal(false, null);
            });
        }
        var form = $('#user-form-modal');
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var editId = $('#user-edit-id').value;
                var fd = new FormData();
                fd.append('action', editId ? 'update_user' : 'add_user');
                if (editId) fd.append('id', editId);
                fd.append('name', $('#user-name').value.trim());
                fd.append('email', $('#user-email').value.trim());
                fd.append('subscription', $('#user-subscription').value);
                fd.append('status', $('#user-status').value);
                if (!editId) {
                    fd.append('password', $('#user-password').value);
                    fd.append('confirmPassword', $('#user-password-confirm').value);
                }
                fetch(ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) {
                        return r.json();
                    })
                    .then(function (j) {
                        if (j && j.success) {
                            toast(j.message || 'OK');
                            $('#user-modal').classList.remove('is-open');
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
        document.body.addEventListener('click', function (e) {
            var v = e.target.closest && e.target.closest('.sa-user-view');
            if (v) {
                var tr = v.closest('tr');
                var raw = tr && tr.getAttribute('data-user');
                if (!raw) return;
                try {
                    var u = JSON.parse(raw);
                    $('#user-profile-view-name').textContent = u.name || '';
                    $('#user-profile-view-email').textContent = u.email || '';
                    $('#user-profile-view-sub').textContent = u.subscription_type || '';
                    $('#user-profile-view-status').textContent = u.status || '';
                    $('#user-profile-view-created').textContent = u.created_at || '';
                    $('#user-profile-view-activity-days').textContent = String(u.activity_days_count != null ? u.activity_days_count : 0);
                    $('#user-profile-view-activity-last').textContent = u.activity_last_date || '—';
                    var img = $('#user-profile-view-img');
                    var ini = $('#user-profile-view-initials');
                    if (u.avatar_url && img) {
                        img.src = u.avatar_url;
                        img.style.display = 'block';
                        if (ini) ini.style.display = 'none';
                    } else {
                        if (img) img.style.display = 'none';
                        if (ini) {
                            ini.style.display = 'flex';
                            ini.textContent = (u.name || '?')
                                .split(' ')
                                .map(function (w) {
                                    return w.charAt(0);
                                })
                                .join('')
                                .substring(0, 2)
                                .toUpperCase();
                        }
                    }
                    var pr = $('#user-profile-view-presence');
                    if (pr) {
                        pr.className = 'presence-dot ' + (u.is_online ? 'presence-dot--on' : 'presence-dot--off');
                    }
                    $('#user-profile-view-modal').classList.add('is-open');
                } catch (err) {}
                return;
            }
            var ed = e.target.closest && e.target.closest('.sa-user-edit');
            if (ed) {
                var tr2 = ed.closest('tr');
                var raw2 = tr2 && tr2.getAttribute('data-user');
                if (raw2) {
                    try {
                        openUserModal(true, JSON.parse(raw2));
                    } catch (e2) {}
                }
                return;
            }
            var del = e.target.closest && e.target.closest('.sa-user-del');
            if (del) {
                var tr3 = del.closest('tr');
                var raw3 = tr3 && tr3.getAttribute('data-user');
                if (!raw3 || !window.confirm('Supprimer cet utilisateur ?')) return;
                try {
                    var u3 = JSON.parse(raw3);
                    postForm('delete_user', { id: u3.id }).then(function (j) {
                        if (j && j.success) {
                            toast(j.message || 'Supprimé');
                            reloadUsers();
                        } else {
                            toast((j && j.message) || 'Erreur', true);
                        }
                    });
                } catch (e3) {}
            }
        });
    }

    function reloadUsers() {
        postForm('get_users', {})
            .then(function (j) {
                if (j && j.success && j.data) renderUsersTable(j.data);
            })
            .catch(function () {});
    }

    function renderAdminsTable(admins) {
        var tb = $('#admins-table tbody');
        if (!tb) return;
        if (!admins || !admins.length) {
            tb.innerHTML = '<tr><td colspan="7" style="padding:12px;">Aucun administrateur.</td></tr>';
            return;
        }
        var sid = window.TCF_ADMIN_SESSION_ID ? String(window.TCF_ADMIN_SESSION_ID) : '';
        tb.innerHTML = admins
            .map(function (a) {
                var roleBadge = a.role === 'super_admin' ? 'sa-badge--super' : 'sa-badge--admin';
                var isSelf = sid && String(a.id) === sid;
                var actions = isSelf
                    ? '<span style="color:var(--sa-muted);font-size:0.75rem;">Compte actuel</span>'
                    : '<button type="button" class="btn btn-outline btn-sm sa-admin-edit">Modifier</button> <button type="button" class="btn btn-outline btn-sm btn-danger-outline sa-admin-del">Supprimer</button> <button type="button" class="btn btn-outline btn-sm sa-admin-demote">Rétrograder</button>';
                return (
                    '<tr data-admin=\'' +
                    escAttr(JSON.stringify(a)) +
                    '\'>' +
                    '<td>' +
                    escHtml(a.name) +
                    '</td>' +
                    '<td>' +
                    escHtml(a.email) +
                    '</td>' +
                    '<td><span class="sa-badge ' +
                    roleBadge +
                    '">' +
                    escHtml(a.role) +
                    '</span></td>' +
                    '<td><span class="sa-badge sa-badge--' +
                    (a.status === 'inactive' ? 'inactive' : 'active') +
                    '">' +
                    escHtml(a.status || '') +
                    '</span></td>' +
                    '<td>' +
                    escHtml(a.created_at || '') +
                    '</td>' +
                    '<td>' +
                    escHtml(a.last_login || '—') +
                    '</td>' +
                    '<td>' +
                    actions +
                    '</td></tr>'
                );
            })
            .join('');
    }

    function openAdminModal(edit, a) {
        var modal = $('#admin-modal');
        var pwd = $all('.admin-password-fields');
        $('#admin-edit-id').value = edit && a ? String(a.id) : '';
        $('#admin-name').value = a ? a.name || '' : '';
        $('#admin-email').value = a ? a.email || '' : '';
        $('#admin-role').value = a ? a.role || 'admin' : 'admin';
        $('#admin-status').value = a ? a.status || 'active' : 'active';
        $('#admin-password').value = '';
        $('#admin-password-confirm').value = '';
        pwd.forEach(function (el) {
            el.style.display = edit ? 'none' : 'block';
        });
        $('.modal-title', modal).textContent = edit ? 'Modifier administrateur' : 'Ajouter un administrateur';
        modal.classList.add('is-open');
    }

    function initAdminsSection() {
        var addBtn = $('#add-admin-btn');
        if (addBtn) {
            addBtn.addEventListener('click', function () {
                openAdminModal(false, null);
            });
        }
        var form = $('#admin-form-modal');
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var editId = $('#admin-edit-id').value;
                var fd = new FormData();
                fd.append('action', editId ? 'update_admin' : 'add_admin');
                if (editId) fd.append('id', editId);
                fd.append('name', $('#admin-name').value.trim());
                fd.append('email', $('#admin-email').value.trim());
                fd.append('role', $('#admin-role').value);
                fd.append('status', $('#admin-status').value);
                if (!editId) {
                    fd.append('password', $('#admin-password').value);
                    fd.append('confirmPassword', $('#admin-password-confirm').value);
                }
                fetch(ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) {
                        return r.json();
                    })
                    .then(function (j) {
                        if (j && j.success) {
                            toast(j.message || 'OK');
                            $('#admin-modal').classList.remove('is-open');
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
        document.body.addEventListener('click', function (e) {
            var ed = e.target.closest && e.target.closest('.sa-admin-edit');
            if (ed) {
                var tr = ed.closest('tr');
                var raw = tr && tr.getAttribute('data-admin');
                if (raw) {
                    try {
                        openAdminModal(true, JSON.parse(raw));
                    } catch (err) {}
                }
                return;
            }
            var del = e.target.closest && e.target.closest('.sa-admin-del');
            if (del) {
                var tr2 = del.closest('tr');
                var raw2 = tr2 && tr2.getAttribute('data-admin');
                if (!raw2 || !window.confirm('Supprimer cet administrateur ?')) return;
                try {
                    var a = JSON.parse(raw2);
                    postForm('delete_admin', { id: a.id }).then(function (j) {
                        if (j && j.success) {
                            toast(j.message || 'OK');
                            reloadAdmins();
                        } else {
                            toast((j && j.message) || 'Erreur', true);
                        }
                    });
                } catch (e2) {}
                return;
            }
            var dem = e.target.closest && e.target.closest('.sa-admin-demote');
            if (dem) {
                var tr3 = dem.closest('tr');
                var raw3 = tr3 && tr3.getAttribute('data-admin');
                if (!raw3 || !window.confirm('Rétrograder cet administrateur en utilisateur ?')) return;
                try {
                    var a2 = JSON.parse(raw3);
                    postForm('demote_to_user', { id: a2.id }).then(function (j) {
                        if (j && j.success) {
                            toast(j.message || 'OK');
                            reloadAdmins();
                        } else {
                            toast((j && j.message) || 'Erreur', true);
                        }
                    });
                } catch (e3) {}
            }
        });
    }

    function reloadAdmins() {
        postForm('get_admins', {})
            .then(function (j) {
                if (j && j.success && j.data) renderAdminsTable(j.data);
            })
            .catch(function () {});
    }

    function renderMessages(list) {
        var box = $('#messages-container');
        if (!box) return;
        if (!list || !list.length) {
            box.innerHTML = '<p style="color:var(--sa-muted);">Aucun message.</p>';
            return;
        }
        box.innerHTML = list
            .map(function (m) {
                return (
                    '<div class="sa-msg-card" data-msg=\'' +
                    escAttr(JSON.stringify(m)) +
                    '\'>' +
                    '<h4 class="sa-msg-subject">' +
                    escHtml(m.subject || '') +
                    '</h4>' +
                    '<p class="sa-msg-body">' +
                    escHtml((m.content || '').substring(0, 200)) +
                    (m.content && m.content.length > 200 ? '…' : '') +
                    '</p>' +
                    '<div class="sa-msg-meta">' +
                    escHtml(m.recipients || '') +
                    ' · ' +
                    escHtml(m.created_at || '') +
                    '</div>' +
                    '<div class="sa-msg-actions"><button type="button" class="btn btn-outline btn-sm sa-msg-edit">Modifier</button> <button type="button" class="btn btn-outline btn-sm btn-danger-outline sa-msg-del">Supprimer</button></div></div>'
                );
            })
            .join('');
    }

    function initMessagesSection() {
        var form = $('#message-form');
        var addBtn = $('#add-message-btn');
        var cancelBtn = $('#cancel-message-btn');
        if (addBtn && form) {
            addBtn.addEventListener('click', function () {
                $('#message-edit-id').value = '';
                $('#message-subject').value = '';
                $('#message-content').value = '';
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
                var editId = $('#message-edit-id').value;
                var fd = new FormData();
                fd.append('action', editId ? 'update_message' : 'add_message');
                if (editId) fd.append('id', editId);
                fd.append('subject', $('#message-subject').value);
                fd.append('content', $('#message-content').value);
                fd.append('recipients', $('#message-recipients').value);
                fetch(ENDPOINT, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) {
                        return r.json();
                    })
                    .then(function (j) {
                        if (j && j.success) {
                            toast(j.message || 'OK');
                            form.style.display = 'none';
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
                    $('#message-edit-id').value = String(m.id);
                    $('#message-subject').value = m.subject || '';
                    $('#message-content').value = m.content || '';
                    $('#message-recipients').value = m.recipients || 'all';
                    $('#message-form').style.display = 'block';
                } catch (err) {}
                return;
            }
            var del = e.target.closest && e.target.closest('.sa-msg-del');
            if (del) {
                var card2 = del.closest('.sa-msg-card');
                var raw2 = card2 && card2.getAttribute('data-msg');
                if (!raw2 || !window.confirm('Supprimer ce message ?')) return;
                try {
                    var m2 = JSON.parse(raw2);
                    postForm('delete_message', { id: m2.id }).then(function (j) {
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

    function reloadMessages() {
        postForm('get_messages', {})
            .then(function (j) {
                if (j && j.success && j.data) renderMessages(j.data);
            })
            .catch(function () {});
    }

    function renderChatUsers(users) {
        var box = $('#chat-users');
        if (!box) return;
        if (!users || !users.length) {
            box.innerHTML = '<p style="padding:12px;color:var(--sa-muted);font-size:0.85rem;">Aucun utilisateur.</p>';
            return;
        }
        box.innerHTML = users
            .map(function (u) {
                var active = chatState.userId && String(chatState.userId) === String(u.id) ? ' is-active' : '';
                return (
                    '<div class="chat-user-item' +
                    active +
                    '" data-uid="' +
                    escAttr(String(u.id)) +
                    '" data-name="' +
                    escAttr(u.name || '') +
                    '">' +
                    escHtml(u.name) +
                    '</div>'
                );
            })
            .join('');
    }

    function loadChatThread(userId) {
        postForm('get_chat_messages', { user_id: userId })
            .then(function (j) {
                if (!j || !j.success || !j.data) return;
                var body = $('#message-body');
                if (!body) return;
                body.innerHTML = '';
                j.data.forEach(function (m) {
                    var div = document.createElement('div');
                    var isAd = parseInt(m.is_admin, 10) === 1;
                    div.className = 'chat-bubble ' + (isAd ? 'chat-bubble--admin' : 'chat-bubble--user');
                    div.innerHTML =
                        '<div>' +
                        escHtml(m.message || '') +
                        '</div><div style="font-size:0.7rem;opacity:0.75;margin-top:4px;">' +
                        escHtml(m.created_at || '') +
                        '</div>';
                    body.appendChild(div);
                });
                body.scrollTop = body.scrollHeight;
            })
            .catch(function () {});
    }

    function initChatSection() {
        var users = typeof usersFromDB !== 'undefined' ? usersFromDB : [];
        renderChatUsers(users);
        var usersBox = $('#chat-users');
        if (usersBox) {
            usersBox.addEventListener('click', function (e) {
                var item = e.target.closest('.chat-user-item');
                if (!item) return;
                var uid = item.getAttribute('data-uid');
                var name = item.getAttribute('data-name') || '';
                chatState.userId = uid;
                $all('.chat-user-item').forEach(function (el) {
                    el.classList.toggle('is-active', el === item);
                });
                $('#current-user-name').textContent = name;
                $('#current-user-avatar').textContent = (name || 'U').substring(0, 2).toUpperCase();
                $('.message-footer').style.display = 'flex';
                loadChatThread(uid);
            });
        }
        var sendBtn = $('#send-message-btn');
        var input = $('#chat-input');
        function sendChat() {
            if (!chatState.userId || !input) return;
            var text = input.value.trim();
            if (!text) return;
            postForm('send_chat_message', { user_id: chatState.userId, message: text }).then(function (j) {
                if (j && j.success) {
                    input.value = '';
                    loadChatThread(chatState.userId);
                } else {
                    toast((j && j.message) || 'Erreur envoi', true);
                }
            });
        }
        if (sendBtn) sendBtn.addEventListener('click', sendChat);
        if (input) {
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    sendChat();
                }
            });
        }
    }

    function initAnalytics() {
        var vids = typeof videosFromDB !== 'undefined' ? videosFromDB : [];
        var sorted = []
            .concat(vids)
            .sort(function (a, b) {
                return (parseInt(b.views, 10) || 0) - (parseInt(a.views, 10) || 0);
            })
            .slice(0, 10);
        var tb = $('#popular-videos-table tbody');
        if (tb) {
            tb.innerHTML = sorted.length
                ? sorted
                      .map(function (v) {
                          return (
                              '<tr><td>' +
                              escHtml(v.title) +
                              '</td><td>' +
                              escHtml(String(v.views != null ? v.views : 0)) +
                              '</td><td>—</td><td>—</td><td>—</td></tr>'
                          );
                      })
                      .join('')
                : '<tr><td colspan="5" style="padding:12px;">Aucune donnée.</td></tr>';
        }
        destroyChart('videoPerf');
        destroyChart('audience');
        var c1 = $('#video-performance-chart');
        if (c1 && typeof Chart !== 'undefined' && sorted.length) {
            var tc = chartTextColor();
            traceState.charts.videoPerf = new Chart(c1, {
                type: 'bar',
                data: {
                    labels: sorted.map(function (v) {
                        return (v.title || '').substring(0, 24);
                    }),
                    datasets: [{ label: 'Vues', data: sorted.map(function (v) { return parseInt(v.views, 10) || 0; }), backgroundColor: 'rgba(211,13,13,0.65)' }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: tc } } },
                    scales: {
                        x: { ticks: { color: tc, maxRotation: 45 }, grid: { display: false } },
                        y: { ticks: { color: tc }, beginAtZero: true }
                    }
                }
            });
        }
        var c2 = $('#audience-chart');
        if (c2 && typeof Chart !== 'undefined' && sorted.length) {
            var tc2 = chartTextColor();
            var pub = vids.filter(function (x) { return x.visibility === 'public'; }).length;
            var prv = vids.filter(function (x) { return x.visibility === 'private'; }).length;
            var prem = vids.filter(function (x) { return x.visibility === 'premium'; }).length;
            traceState.charts.audience = new Chart(c2, {
                type: 'pie',
                data: {
                    labels: ['Public', 'Privé', 'Premium'],
                    datasets: [{ data: [pub, prv, prem], backgroundColor: ['#d30d0d', '#b00b0b', 'rgba(20,22,34,0.35)'] }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: tc2 } } } }
            });
        }
    }

    function renderNotifications(list) {
        var box = $('#notifications-list');
        var badge = $('#notification-count');
        if (!box) return;
        var unread = 0;
        box.innerHTML = (list || [])
            .map(function (n) {
                if (!parseInt(n.is_read, 10)) unread++;
                var cls = parseInt(n.is_read, 10) ? '' : ' sa-notif-item--unread';
                return (
                    '<div class="sa-notif-item' +
                    cls +
                    '" data-nid="' +
                    escAttr(String(n.id)) +
                    '"><strong>' +
                    escHtml(n.title || '') +
                    '</strong><div style="font-size:0.75rem;margin-top:4px;">' +
                    escHtml(n.content || '') +
                    '</div><div style="font-size:0.7rem;color:var(--sa-muted);margin-top:4px;">' +
                    escHtml(n.created_at || '') +
                    '</div></div>'
                );
            })
            .join('');
        if (badge) {
            badge.textContent = unread > 99 ? '99+' : String(unread);
            badge.style.display = unread ? 'flex' : 'none';
        }
    }

    function initNotifications() {
        var panel = $('#notifications-panel');
        var btn = $('#notifications-btn');
        if (btn && panel) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                panel.classList.toggle('is-open');
            });
        }
        document.addEventListener('click', function () {
            if (panel) panel.classList.remove('is-open');
        });
        if (panel) {
            panel.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }
        var list = typeof notificationsFromDB !== 'undefined' ? notificationsFromDB : [];
        renderNotifications(list);
        var mar = $('#mark-all-read');
        if (mar) {
            mar.addEventListener('click', function () {
                (list || []).forEach(function (n) {
                    if (!parseInt(n.is_read, 10)) {
                        postForm('mark_notification_read', { id: n.id });
                    }
                });
                list.forEach(function (n) {
                    n.is_read = 1;
                });
                renderNotifications(list);
            });
        }
        document.body.addEventListener('click', function (e) {
            var it = e.target.closest && e.target.closest('.sa-notif-item');
            if (!it) return;
            var id = it.getAttribute('data-nid');
            if (!id) return;
            postForm('mark_notification_read', { id: id }).then(function () {
                (list || []).forEach(function (n) {
                    if (String(n.id) === String(id)) n.is_read = 1;
                });
                renderNotifications(list);
            });
        });
    }

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

    function initTraceListeners() {
        var range = $('#trace-range');
        if (range) range.addEventListener('change', loadTraceability);
        var metric = $('#trace-metric');
        if (metric) metric.addEventListener('change', loadTraceability);
        var geo = $('#trace-geo-mode');
        if (geo) geo.addEventListener('change', loadTraceability);
    }

    function onSection(sectionId) {
        if (sectionId === 'dashboard') {
            refreshStats();
            loadActivities();
            loadTraceability();
        }
        if (sectionId === 'users') {
            reloadUsers();
        }
        if (sectionId === 'messages') {
            reloadMessages();
        }
        if (sectionId === 'admins') {
            reloadAdmins();
        }
        if (sectionId === 'analytics') {
            initAnalytics();
        }
        if (TOPIC_SECTIONS[sectionId]) {
            setTopicContext(sectionId);
        }
        if (sectionId === 'topics-section' && topicsState.targetId) {
            setTopicContext(topicsState.targetId);
        }
    }

    window.TCFAdminApp = { onSection: onSection };

    document.addEventListener('DOMContentLoaded', function () {
        initTheme();
        initTopicsMenu();
        initTopicsForms();
        initUsersSection();
        initAdminsSection();
        initMessagesSection();
        initChatSection();
        initNotifications();
        initModalsClose();
        initTraceListeners();

        if (typeof usersFromDB !== 'undefined') {
            renderUsersTable(usersFromDB);
        }
        if (typeof adminsFromDB !== 'undefined') {
            renderAdminsTable(adminsFromDB);
        }
        if (typeof messagesFromDB !== 'undefined') {
            renderMessages(messagesFromDB);
        }
        if (typeof activitiesFromDB !== 'undefined') {
            renderActivities(activitiesFromDB);
        }

        if (typeof Chart !== 'undefined') {
            Chart.defaults.color = chartTextColor();
            Chart.defaults.borderColor = 'rgba(148,163,184,0.2)';
        }

        refreshStats();
        loadTraceability();

        window.TCF_ADMIN_SESSION_ID = typeof TCF_ADMIN_SESSION_ID_INLINE !== 'undefined' ? TCF_ADMIN_SESSION_ID_INLINE : null;
    });
})();
