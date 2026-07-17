/**
 * Messagerie équipe — Super Admin (admin / super_admin uniquement)
 */
(function () {
    'use strict';

    var api = '';
    var myId = '';
    var threads = [];
    var selectedThreadId = null;
    var messagesCache = [];
    var pollTimer = null;
    var editingMessageId = null;
    var mobileMode = 'list';
    var staffCache = [];
    var groupSelected = {};
    var groupMembersPendingRemove = {};
    var groupMembersPendingAdd = {};
    var groupAvatarRemoved = false;
    var initialized = false;

    var EMOJI = (
        '😀 😃 😄 😁 😅 😂 🤣 😊 😍 🥰 😘 😜 🤔 😎 👍 👎 👏 🙏 💪 🔥 ✨ ❤️ ' +
        '🎉 ✅ ❌ ⭐ 📚 ✍️ 🇫🇷 🇨🇦 😢 😭 🤝 💬 📝 🎯 ⏰'
    )
        .split(/\s+/)
        .filter(Boolean);

    function $(id) {
        return document.getElementById(id);
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

    function toast(msg, isErr) {
        var el = $('notification-toast');
        var text = $('notification-text');
        if (!el || !text) {
            window.alert(msg);
            return;
        }
        text.textContent = msg;
        el.style.background = isErr ? '#7f1d1d' : '#14532d';
        el.classList.add('show');
        window.clearTimeout(toast._t);
        toast._t = window.setTimeout(function () {
            el.classList.remove('show');
        }, 3200);
    }

    function post(action, payload) {
        return fetch(api, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify(Object.assign({ action: action }, payload || {}))
        }).then(function (r) {
            return r.json();
        });
    }

    function get(action) {
        return fetch(api + '?action=' + encodeURIComponent(action), { credentials: 'same-origin' }).then(function (r) {
            return r.json();
        });
    }

    function initials(name) {
        var s = (name || '').trim();
        if (!s) return '?';
        var p = s.split(/\s+/).filter(Boolean);
        return ((p[0] || '').charAt(0) + (p[1] || '').charAt(0)).toUpperCase() || s.charAt(0).toUpperCase();
    }

    function avatarHtml(url, name) {
        if (url) {
            return '<img src="' + escAttr(url) + '" alt="">';
        }
        return escHtml(initials(name));
    }

    function parseSqlDate(s) {
        if (!s) return new Date(NaN);
        var str = String(s).trim();
        var m = str.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?/);
        if (m) {
            return new Date(
                parseInt(m[1], 10),
                parseInt(m[2], 10) - 1,
                parseInt(m[3], 10),
                parseInt(m[4], 10),
                parseInt(m[5], 10),
                m[6] ? parseInt(m[6], 10) : 0
            );
        }
        var t = Date.parse(str.replace(' ', 'T'));
        return isNaN(t) ? new Date(NaN) : new Date(t);
    }

    function pad2(n) {
        return (n < 10 ? '0' : '') + n;
    }

    function dateKey(d) {
        return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
    }

    function formatDateSep(d) {
        if (isNaN(d.getTime())) return '';
        var now = new Date();
        var today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        var msgDay = new Date(d.getFullYear(), d.getMonth(), d.getDate());
        var diff = Math.round((today - msgDay) / 86400000);
        if (diff === 0) return "Aujourd'hui";
        if (diff === 1) return 'Hier';
        return pad2(d.getDate()) + '/' + pad2(d.getMonth() + 1) + '/' + d.getFullYear();
    }

    function formatTime(s) {
        var d = parseSqlDate(s);
        if (isNaN(d.getTime())) return '';
        return pad2(d.getHours()) + ':' + pad2(d.getMinutes());
    }

    function currentThread() {
        for (var i = 0; i < threads.length; i++) {
            if (String(threads[i].id) === String(selectedThreadId)) return threads[i];
        }
        return null;
    }

    function syncMobileLayout() {
        var app = $('ssc-app');
        if (!app) return;
        var mq = window.matchMedia('(max-width: 900px)');
        app.classList.remove('ssc-mobile-list', 'ssc-mobile-chat');
        if (!mq.matches) return;
        if (selectedThreadId && mobileMode === 'chat') {
            app.classList.add('ssc-mobile-chat');
        } else {
            app.classList.add('ssc-mobile-list');
        }
    }

    function renderThreadList() {
        var box = $('ssc-thread-list');
        if (!box) return;
        var q = (($('ssc-search') || {}).value || '').trim().toLowerCase();
        var list = threads.slice().sort(function (a, b) {
            var ka = a.last_message_at || a.updated_at || '';
            var kb = b.last_message_at || b.updated_at || '';
            return String(kb).localeCompare(String(ka));
        });
        if (q) {
            list = list.filter(function (t) {
                var title = t.thread_type === 'group' ? t.title : t.peer ? t.peer.name : '';
                var hay = (title + ' ' + (t.last_message || '')).toLowerCase();
                return hay.indexOf(q) !== -1;
            });
        }
        if (!list.length) {
            box.innerHTML =
                '<div class="ssc-list-empty">' +
                (q
                    ? 'Aucune conversation trouvée.'
                    : 'Aucune conversation. Démarrez un échange avec un membre du staff ou créez un groupe.') +
                '</div>';
            return;
        }
        box.innerHTML = list
            .map(function (t) {
                var active = selectedThreadId && String(selectedThreadId) === String(t.id);
                var isGroup = t.thread_type === 'group';
                var name = isGroup ? t.title || 'Groupe' : t.peer ? t.peer.name : 'Conversation';
                var meta = isGroup
                    ? String(t.member_count || 0) + ' membres'
                    : t.peer && t.peer.online
                      ? 'En ligne'
                      : 'Hors ligne';
                var av;
                if (isGroup) {
                    av = t.group_avatar_url
                        ? '<img src="' + escAttr(t.group_avatar_url) + '" alt="">'
                        : '<i class="bx bx-group"></i>';
                } else {
                    av = avatarHtml((t.peer && t.peer.avatar_url) || '', name);
                }
                return (
                    '<button type="button" class="ssc-thread-item' +
                    (active ? ' is-active' : '') +
                    '" data-thread-id="' +
                    escAttr(String(t.id)) +
                    '">' +
                    '<div class="ssc-thread-avatar">' +
                    av +
                    '</div><div class="ssc-thread-body">' +
                    '<div class="ssc-thread-name">' +
                    escHtml(name) +
                    '</div><div class="ssc-thread-meta">' +
                    escHtml(meta) +
                    '</div>' +
                    (t.last_message
                        ? '<div class="ssc-thread-preview">' + escHtml(String(t.last_message).slice(0, 60)) + '</div>'
                        : '') +
                    '</div></button>'
                );
            })
            .join('');
    }

    function renderMessages(list) {
        var box = $('ssc-messages');
        var composer = $('ssc-composer');
        if (!box) return;
        messagesCache = list || [];
        if (!selectedThreadId) {
            box.innerHTML =
                '<div class="ssc-empty-state"><i class="bx bxs-chat"></i><p>Sélectionnez une conversation pour échanger avec l’équipe.</p></div>';
            if (composer) composer.classList.remove('is-visible');
            return;
        }
        if (!list || !list.length) {
            box.innerHTML = '<div class="ssc-empty-state"><i class="bx bx-message-rounded-dots"></i><p>Aucun message. Écrivez le premier.</p></div>';
        } else {
            var t = currentThread();
            var isGroup = t && t.thread_type === 'group';
            var parts = [];
            var lastKey = '';
            for (var i = 0; i < list.length; i++) {
                var m = list[i];
                var d = parseSqlDate(m.created_at);
                var key = isNaN(d.getTime()) ? 'x' + i : dateKey(d);
                if (key !== lastKey) {
                    lastKey = key;
                    parts.push(
                        '<div class="ssc-day-sep"><span>' + escHtml(formatDateSep(d)) + '</span></div>'
                    );
                }
                var mine = !!m.mine;
                var deleted = !!m.is_deleted;
                var who = m.sender_name || 'Membre';
                var bubbleClass = 'ssc-msg-bubble' + (deleted ? ' ssc-msg-bubble--deleted' : '');
                var text = deleted ? 'Message supprimé' : escHtml(m.message || '');
                var foot =
                    escHtml(formatTime(m.created_at)) +
                    (m.is_edited ? ' <span class="ssc-msg-edited">· modifié</span>' : '');
                var actions = '';
                if (mine && !deleted) {
                    actions =
                        '<div class="ssc-msg-actions">' +
                        '<button type="button" class="ssc-msg-act" data-edit-id="' +
                        escAttr(String(m.id)) +
                        '" title="Modifier"><i class="bx bx-edit-alt"></i></button>' +
                        '<button type="button" class="ssc-msg-act" data-del-id="' +
                        escAttr(String(m.id)) +
                        '" title="Supprimer"><i class="bx bx-trash"></i></button></div>';
                } else if (mine && deleted) {
                    actions = '';
                } else if (!mine && !deleted) {
                    actions =
                        '<div class="ssc-msg-actions">' +
                        '<button type="button" class="ssc-msg-act" data-del-id="' +
                        escAttr(String(m.id)) +
                        '" title="Supprimer"><i class="bx bx-trash"></i></button></div>';
                }
                parts.push(
                    '<div class="ssc-msg-row ' +
                    (mine ? 'ssc-msg-row--me' : 'ssc-msg-row--other') +
                    '" data-msg-id="' +
                    escAttr(String(m.id)) +
                    '">' +
                    (!mine
                        ? '<div class="ssc-msg-avatar">' + avatarHtml(m.sender_avatar_url, who) + '</div>'
                        : '') +
                    '<div class="ssc-msg-bubble-wrap">' +
                    actions +
                    '<div class="' +
                    bubbleClass +
                    '">' +
                    (isGroup && !mine && !deleted
                        ? '<div class="ssc-msg-author">' + escHtml(who) + '</div>'
                        : '') +
                    text +
                    '<div class="ssc-msg-foot">' +
                    foot +
                    '</div></div></div>' +
                    (mine ? '<div class="ssc-msg-avatar">' + avatarHtml(m.sender_avatar_url, who) + '</div>' : '') +
                    '</div>'
                );
            }
            box.innerHTML = parts.join('');
            box.scrollTop = box.scrollHeight;
        }
        if (composer) {
            var th = currentThread();
            var canSend = !th || th.can_send !== false;
            composer.classList.toggle('is-visible', !!selectedThreadId && canSend !== false);
        }
    }

    function updateHeader() {
        var t = currentThread();
        var nameEl = $('ssc-head-name');
        var statusEl = $('ssc-head-status');
        var avEl = $('ssc-head-avatar');
        var settingsBtn = $('ssc-group-settings-btn');
        if (!t) {
            if (nameEl) nameEl.textContent = 'Messagerie équipe';
            if (statusEl) statusEl.textContent = 'Administrateurs et super-administrateurs';
            if (avEl) avEl.innerHTML = '<i class="bx bx-message-dots"></i>';
            if (settingsBtn) settingsBtn.hidden = true;
            return;
        }
        if (t.thread_type === 'group') {
            if (nameEl) nameEl.textContent = t.title || 'Groupe';
            if (statusEl) {
                statusEl.textContent =
                    String(t.member_count || 0) + ' membres' + (t.online_count ? ' · ' + t.online_count + ' en ligne' : '');
            }
            if (avEl) {
                avEl.innerHTML = t.group_avatar_url
                    ? '<img src="' + escAttr(t.group_avatar_url) + '" alt="">'
                    : '<i class="bx bx-group"></i>';
            }
            if (settingsBtn) settingsBtn.hidden = false;
        } else if (t.peer) {
            if (nameEl) nameEl.textContent = t.peer.name || 'Conversation';
            if (statusEl) statusEl.textContent = t.peer.online ? 'En ligne' : 'Hors ligne';
            if (avEl) avEl.innerHTML = avatarHtml(t.peer.avatar_url, t.peer.name);
            if (settingsBtn) settingsBtn.hidden = true;
        }
    }

    function loadMessages(silent) {
        if (!selectedThreadId) return;
        post('get_messages', { thread_id: selectedThreadId })
            .then(function (j) {
                if (!j || !j.ok) return;
                if (j.thread && j.thread.can_send === false) {
                    var c = currentThread();
                    if (c) c.can_send = false;
                }
                renderMessages(j.messages || []);
                if (!silent) updateHeader();
            })
            .catch(function () {
                if (!silent) toast('Erreur chargement messages', true);
            });
    }

    function loadThreads() {
        return get('list_threads')
            .then(function (j) {
                threads = j && j.ok ? j.threads || [] : [];
                if (selectedThreadId) {
                    var ok = threads.some(function (x) {
                        return String(x.id) === String(selectedThreadId);
                    });
                    if (!ok) {
                        selectedThreadId = null;
                        mobileMode = 'list';
                        cancelEdit();
                        renderMessages([]);
                        updateHeader();
                    }
                }
                renderThreadList();
                updateHeader();
                syncMobileLayout();
            })
            .catch(function () {
                threads = [];
                renderThreadList();
            });
    }

    function selectThread(threadId) {
        selectedThreadId = threadId;
        mobileMode = 'chat';
        cancelEdit();
        renderThreadList();
        updateHeader();
        syncMobileLayout();
        loadMessages();
        var composer = $('ssc-composer');
        if (composer) composer.classList.add('is-visible');
    }

    function sendMessage() {
        var input = $('ssc-composer-input');
        if (!input || !selectedThreadId) return;
        var text = (input.value || '').trim();
        if (!text) return;
        var btn = $('ssc-send-btn');
        if (btn) btn.disabled = true;

        if (editingMessageId) {
            post('edit_message', { message_id: editingMessageId, message: text })
                .then(function (j) {
                    if (j && j.ok) {
                        cancelEdit();
                        loadMessages(true);
                        loadThreads();
                    } else {
                        toast((j && j.message) || 'Modification impossible', true);
                    }
                })
                .catch(function () {
                    toast('Erreur réseau', true);
                })
                .finally(function () {
                    if (btn) btn.disabled = false;
                });
            return;
        }

        post('send_message', { thread_id: selectedThreadId, message: text })
            .then(function (j) {
                if (j && j.ok) {
                    input.value = '';
                    input.style.height = 'auto';
                    loadMessages(true);
                    loadThreads();
                } else {
                    toast((j && j.message) || 'Envoi impossible', true);
                }
            })
            .catch(function () {
                toast('Erreur réseau', true);
            })
            .finally(function () {
                if (btn) btn.disabled = false;
            });
    }

    function cancelEdit() {
        editingMessageId = null;
        var banner = $('ssc-edit-banner');
        var input = $('ssc-composer-input');
        if (banner) banner.classList.remove('is-visible');
        if (input) {
            input.value = '';
            input.placeholder = 'Écrivez votre message…';
        }
    }

    function startEdit(messageId, currentText) {
        editingMessageId = messageId;
        var banner = $('ssc-edit-banner');
        var input = $('ssc-composer-input');
        if (banner) banner.classList.add('is-visible');
        if (input) {
            input.value = currentText || '';
            input.placeholder = 'Modifier le message…';
            input.focus();
        }
    }

    function deleteMessage(messageId) {
        if (!window.confirm('Supprimer ce message ?')) return;
        post('delete_message', { message_id: messageId })
            .then(function (j) {
                if (j && j.ok) {
                    loadMessages(true);
                    loadThreads();
                } else {
                    toast((j && j.message) || 'Suppression impossible', true);
                }
            })
            .catch(function () {
                toast('Erreur réseau', true);
            });
    }

    function bootstrap() {
        get('bootstrap')
            .then(function (j) {
                if (!j || !j.ok) return;
                var pill = $('ssc-online-count');
                if (pill) pill.textContent = String(j.online_count || 0) + ' en ligne';
                var btn = $('ssc-toggle-presence');
                if (btn && j.me) {
                    var vis = String(j.me.is_visible) === '1' || j.me.is_visible === 1;
                    btn.setAttribute('data-visible', vis ? '1' : '0');
                    btn.innerHTML =
                        '<i class="bx ' +
                        (vis ? 'bx-wifi' : 'bx-wifi-off') +
                        '"></i> ' +
                        (vis ? 'Masquer ma présence' : 'Afficher ma présence');
                }
            })
            .catch(function () {});
    }

    function openNewDmModal() {
        var modal = $('ssc-new-dm-modal');
        if (!modal) return;
        var list = $('ssc-dm-staff-list');
        if (list) list.innerHTML = '<p class="ssc-list-empty">Chargement…</p>';
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        get('list_users')
            .then(function (j) {
                staffCache = j && j.ok ? j.users || [] : [];
                renderDmStaffList('');
            })
            .catch(function () {
                if (list) list.innerHTML = '<p class="ssc-list-empty">Erreur de chargement.</p>';
            });
    }

    function renderDmStaffList(q) {
        var box = $('ssc-dm-staff-list');
        if (!box) return;
        q = (q || '').trim().toLowerCase();
        var rows = staffCache.filter(function (u) {
            if (String(u.id) === myId) return false;
            if (!q) return true;
            return ((u.name || '') + ' ' + (u.email || '')).toLowerCase().indexOf(q) !== -1;
        });
        if (!rows.length) {
            box.innerHTML = '<p class="ssc-list-empty">Aucun membre du staff trouvé.</p>';
            return;
        }
        box.innerHTML = rows
            .map(function (u) {
                return (
                    '<button type="button" class="ssc-staff-pick-item" data-staff-id="' +
                    escAttr(String(u.id)) +
                    '"><span class="ssc-thread-avatar" style="width:34px;height:34px;font-size:0.7rem">' +
                    avatarHtml(u.avatar_url || '', u.name) +
                    '</span><span><strong>' +
                    escHtml(u.name || 'Sans nom') +
                    '</strong><small>' +
                    escHtml(u.email || '') +
                    '</small></span><span class="ssc-role-badge">' +
                    escHtml(u.role_label || 'Staff') +
                    '</span></button>'
                );
            })
            .join('');
    }

    function startDirect(staffId) {
        post('ensure_direct', { target_user_id: staffId })
            .then(function (j) {
                if (j && j.ok && j.thread_id) {
                    closeModal('ssc-new-dm-modal');
                    loadThreads().then(function () {
                        selectThread(j.thread_id);
                    });
                } else {
                    toast((j && j.message) || 'Impossible d’ouvrir la conversation', true);
                }
            })
            .catch(function () {
                toast('Erreur réseau', true);
            });
    }

    function closeModal(id) {
        var m = $(id);
        if (m) {
            m.classList.remove('is-open');
            m.setAttribute('aria-hidden', 'true');
        }
    }

    function openNewGroupModal() {
        groupSelected = {};
        var modal = $('sa-new-group-modal');
        if (!modal) return;
        var nm = $('sa-new-group-name');
        var ph = $('sa-new-group-photo');
        var sr = $('sa-new-group-user-search');
        if (nm) nm.value = '';
        if (ph) ph.value = '';
        if (sr) sr.value = '';
        saNewGroupRenderChips();
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        get('list_users')
            .then(function (j) {
                window._sscStaffCache = j && j.ok ? j.users || [] : [];
                saNewGroupRenderUserCards();
            })
            .catch(function () {});
    }

    function saNewGroupRenderChips() {
        var box = $('sa-new-group-chips');
        if (!box) return;
        var ids = Object.keys(groupSelected);
        if (!ids.length) {
            box.innerHTML = '<span class="sa-new-group-chips-empty">Ajoutez des administrateurs au groupe.</span>';
            return;
        }
        box.innerHTML = ids
            .map(function (id) {
                var u = groupSelected[id];
                return (
                    '<span class="sa-new-group-chip" data-chip-id="' +
                    escAttr(id) +
                    '"><span class="sa-new-group-chip-text">' +
                    escHtml((u.name || '') + ' · ' + (u.email || '')) +
                    '</span><button type="button" class="sa-new-group-chip-x">&times;</button></span>'
                );
            })
            .join('');
    }

    function saNewGroupRenderUserCards() {
        var wrap = $('sa-new-group-user-cards');
        if (!wrap) return;
        var cache = window._sscStaffCache || [];
        var q = (($('sa-new-group-user-search') || {}).value || '').trim().toLowerCase();
        var rows = cache.filter(function (u) {
            if (String(u.id) === myId) return false;
            if (!q) return true;
            return ((u.name || '') + ' ' + (u.email || '')).toLowerCase().indexOf(q) !== -1;
        });
        if (!rows.length) {
            wrap.innerHTML = '<p class="sa-new-group-cards-empty">Aucun compte staff correspondant.</p>';
            return;
        }
        wrap.innerHTML = rows
            .map(function (u) {
                var id = String(u.id);
                var on = !!groupSelected[id];
                return (
                    '<button type="button" class="sa-new-group-user-card' +
                    (on ? ' is-on' : '') +
                    '" data-pick-id="' +
                    escAttr(id) +
                    '"><span class="sa-new-group-user-card-av">' +
                    escHtml((u.name || '?').charAt(0).toUpperCase()) +
                    '</span><span class="sa-new-group-user-card-body"><strong>' +
                    escHtml(u.name || 'Sans nom') +
                    '</strong><small>' +
                    escHtml(u.email || '') +
                    '</small></span><span class="ssc-role-badge">' +
                    escHtml(u.role_label || '') +
                    '</span></button>'
                );
            })
            .join('');
    }

    function submitNewGroup() {
        var title = (($('sa-new-group-name') || {}).value || '').trim();
        if (!title) {
            toast('Nom du groupe requis.', true);
            return;
        }
        var ids = Object.keys(groupSelected).map(function (k) {
            return parseInt(k, 10);
        });
        if (!ids.length) {
            toast('Ajoutez au moins un membre.', true);
            return;
        }
        var adminsOnly =
            $('sa-new-group-admins-only') && $('sa-new-group-admins-only').checked ? 1 : 0;
        var fileEl = $('sa-new-group-photo');
        var file = fileEl && fileEl.files && fileEl.files[0];

        function postCreate(extra) {
            post(
                'create_group',
                Object.assign(
                    { title: title, member_ids: ids, admins_only_post: adminsOnly },
                    extra || {}
                )
            )
                .then(function (j) {
                    if (j && j.ok) {
                        toast(j.message || 'Groupe créé');
                        closeModal('sa-new-group-modal');
                        loadThreads();
                    } else {
                        toast((j && j.message) || 'Création impossible', true);
                    }
                })
                .catch(function () {
                    toast('Erreur réseau', true);
                });
        }

        if (file) {
            var fr = new FileReader();
            fr.onload = function () {
                postCreate(typeof fr.result === 'string' && fr.result.indexOf('data:') === 0 ? { avatar_data_url: fr.result } : {});
            };
            fr.readAsDataURL(file);
        } else {
            postCreate({});
        }
    }

    function openGroupSettings() {
        var t = currentThread();
        if (!t || t.thread_type !== 'group') return;
        var modal = $('sa-group-settings-modal');
        if (!modal) return;
        $('sa-group-settings-thread-id').value = String(t.id);
        $('sa-group-settings-title').value = t.title || '';
        $('sa-group-settings-admins-only').checked = !!parseInt(String(t.admins_only_post || 0), 10);
        groupMembersPendingRemove = {};
        groupMembersPendingAdd = {};
        groupAvatarRemoved = false;
        var fav = $('sa-group-settings-avatar');
        if (fav) fav.value = '';
        loadGroupMembersList(t.id);
        renderGroupAddStaff('');
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function renderGroupAddStaff(q) {
        var box = $('ssc-group-add-list');
        if (!box) return;
        q = (q || '').trim().toLowerCase();
        if (!window._sscGroupStaffCache) {
            box.innerHTML = '<p class="ssc-list-empty">Chargement…</p>';
            get('list_users')
                .then(function (j) {
                    window._sscGroupStaffCache = j && j.ok ? j.users || [] : [];
                    renderGroupAddStaff(q);
                })
                .catch(function () {
                    box.innerHTML = '<p class="ssc-list-empty">Erreur.</p>';
                });
            return;
        }
        var rows = window._sscGroupStaffCache.filter(function (u) {
            if (String(u.id) === myId) return false;
            if (groupMembersPendingRemove[String(u.id)]) return false;
            if (!q) return true;
            return ((u.name || '') + ' ' + (u.email || '')).toLowerCase().indexOf(q) !== -1;
        });
        if (!rows.length) {
            box.innerHTML = '<p class="ssc-list-empty">Aucun membre à ajouter.</p>';
            return;
        }
        box.innerHTML = rows
            .map(function (u) {
                return (
                    '<button type="button" class="ssc-staff-pick-item" data-add-staff-id="' +
                    escAttr(String(u.id)) +
                    '"><span><strong>' +
                    escHtml(u.name || '') +
                    '</strong><small>' +
                    escHtml(u.email || '') +
                    '</small></span><span class="ssc-role-badge">' +
                    escHtml(u.role_label || '') +
                    '</span></button>'
                );
            })
            .join('');
    }

    function loadGroupMembersList(threadId) {
        var box = $('ssc-group-members-list');
        if (!box) return;
        box.innerHTML = '<p class="ssc-list-empty">Chargement…</p>';
        post('list_group_members', { thread_id: threadId })
            .then(function (j) {
                if (!j || !j.ok) {
                    box.innerHTML = '<p class="ssc-list-empty">Erreur.</p>';
                    return;
                }
                var members = j.members || [];
                if (!members.length) {
                    box.innerHTML = '<p class="ssc-list-empty">Aucun membre.</p>';
                    return;
                }
                box.innerHTML = members
                    .map(function (m) {
                        var isMe = String(m.id) === myId;
                        return (
                            '<div class="ssc-group-member-row" data-member-id="' +
                            escAttr(String(m.id)) +
                            '"><span>' +
                            escHtml(m.name || '') +
                            ' <small>(' +
                            escHtml(m.role_label || '') +
                            ')</small></span>' +
                            (isMe
                                ? '<span class="ssc-role-badge">Vous</span>'
                                : '<button type="button" class="btn btn-outline btn-sm ssc-rm-member">Retirer</button>') +
                            '</div>'
                        );
                    })
                    .join('');
            })
            .catch(function () {
                box.innerHTML = '<p class="ssc-list-empty">Erreur réseau.</p>';
            });
    }

    function saveGroupSettings() {
        var tid = parseInt(($('sa-group-settings-thread-id') || {}).value || '0', 10);
        var title = (($('sa-group-settings-title') || {}).value || '').trim();
        if (!tid || !title) {
            toast('Nom requis.', true);
            return;
        }
        var adminsOnly = $('sa-group-settings-admins-only').checked ? 1 : 0;
        var addIds = Object.keys(groupMembersPendingAdd).map(function (k) {
            return parseInt(k, 10);
        });
        var rmIds = Object.keys(groupMembersPendingRemove).map(function (k) {
            return parseInt(k, 10);
        });
        var fileEl = $('sa-group-settings-avatar');
        var file = fileEl && fileEl.files && fileEl.files[0];

        function finishGroupSave(extra) {
            post('update_group', Object.assign({
                thread_id: tid,
                title: title,
                admins_only_post: adminsOnly
            }, extra || {}))
                .then(function (j) {
                    if (!j || !j.ok) {
                        toast((j && j.message) || 'Erreur', true);
                        return null;
                    }
                    if (addIds.length || rmIds.length) {
                        return post('sync_group_members', {
                            thread_id: tid,
                            add_member_ids: addIds,
                            remove_member_ids: rmIds
                        });
                    }
                    return j;
                })
                .then(function (j) {
                    if (j && j.ok) {
                        toast(j.message || 'Enregistré');
                        closeModal('sa-group-settings-modal');
                        groupMembersPendingAdd = {};
                        groupMembersPendingRemove = {};
                        groupAvatarRemoved = false;
                        loadThreads();
                        loadMessages(true);
                    } else if (j) {
                        toast((j && j.message) || 'Erreur', true);
                    }
                })
                .catch(function () {
                    toast('Erreur réseau', true);
                });
        }

        if (file) {
            var fr = new FileReader();
            fr.onload = function () {
                finishGroupSave({ avatar_data_url: fr.result });
            };
            fr.readAsDataURL(file);
            return;
        }
        if (groupAvatarRemoved) {
            finishGroupSave({ remove_avatar: true });
        } else {
            finishGroupSave({});
        }
    }

    function wireEvents() {
        var threadList = $('ssc-thread-list');
        if (threadList && threadList.getAttribute('data-wired') !== '1') {
            threadList.setAttribute('data-wired', '1');
            threadList.addEventListener('click', function (e) {
                var btn = e.target.closest('.ssc-thread-item');
                if (!btn) return;
                selectThread(parseInt(btn.getAttribute('data-thread-id') || '0', 10));
            });
        }

        var search = $('ssc-search');
        if (search) search.addEventListener('input', renderThreadList);

        var sendBtn = $('ssc-send-btn');
        if (sendBtn) sendBtn.addEventListener('click', sendMessage);

        var input = $('ssc-composer-input');
        if (input) {
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
            input.addEventListener('input', function () {
                input.style.height = 'auto';
                input.style.height = Math.min(input.scrollHeight, 120) + 'px';
            });
        }

        var cancelEditBtn = $('ssc-edit-cancel');
        if (cancelEditBtn) cancelEditBtn.addEventListener('click', cancelEdit);

        var msgBox = $('ssc-messages');
        if (msgBox && msgBox.getAttribute('data-wired') !== '1') {
            msgBox.setAttribute('data-wired', '1');
            msgBox.addEventListener('click', function (e) {
                var editBtn = e.target.closest('[data-edit-id]');
                var delBtn = e.target.closest('[data-del-id]');
                if (editBtn) {
                    var mid = parseInt(editBtn.getAttribute('data-edit-id') || '0', 10);
                    var found = null;
                    for (var ei = 0; ei < messagesCache.length; ei++) {
                        if (parseInt(messagesCache[ei].id, 10) === mid) {
                            found = messagesCache[ei];
                            break;
                        }
                    }
                    startEdit(mid, found ? found.message : '');
                } else if (delBtn) {
                    deleteMessage(parseInt(delBtn.getAttribute('data-del-id') || '0', 10));
                }
            });
        }

        var refreshBtn = $('ssc-refresh');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function () {
                bootstrap();
                loadThreads();
                if (selectedThreadId) loadMessages(true);
            });
        }

        var newDmBtn = $('ssc-new-dm');
        if (newDmBtn) newDmBtn.addEventListener('click', openNewDmModal);

        var newGroupBtn = $('ssc-new-group');
        if (newGroupBtn) newGroupBtn.addEventListener('click', openNewGroupModal);

        var presenceBtn = $('ssc-toggle-presence');
        if (presenceBtn) {
            presenceBtn.addEventListener('click', function () {
                var vis = presenceBtn.getAttribute('data-visible') === '1';
                post('set_presence_visibility', { is_visible: vis ? 0 : 1 })
                    .then(function (j) {
                        if (j && j.ok) {
                            bootstrap();
                            loadThreads();
                            toast('Présence mise à jour');
                        }
                    })
                    .catch(function () {});
            });
        }

        var backBtn = $('ssc-back');
        if (backBtn) {
            backBtn.addEventListener('click', function () {
                mobileMode = 'list';
                syncMobileLayout();
            });
        }

        var groupBtn = $('ssc-group-settings-btn');
        if (groupBtn) groupBtn.addEventListener('click', openGroupSettings);

        var emojiBtn = $('ssc-emoji-btn');
        var emojiPop = $('ssc-emoji-pop');
        if (emojiBtn && emojiPop && input) {
            EMOJI.forEach(function (ch) {
                var b = document.createElement('button');
                b.type = 'button';
                b.textContent = ch;
                b.addEventListener('click', function () {
                    input.value += ch;
                    input.focus();
                    emojiPop.hidden = true;
                });
                emojiPop.appendChild(b);
            });
            emojiBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                emojiPop.hidden = !emojiPop.hidden;
            });
            document.addEventListener('click', function () {
                emojiPop.hidden = true;
            });
            emojiPop.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }

        var dmList = $('ssc-dm-staff-list');
        if (dmList) {
            dmList.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-staff-id]');
                if (!btn) return;
                startDirect(parseInt(btn.getAttribute('data-staff-id') || '0', 10));
            });
        }
        var dmSearch = $('ssc-dm-search');
        if (dmSearch) dmSearch.addEventListener('input', function () {
            renderDmStaffList(dmSearch.value);
        });

        var ngSubmit = $('sa-new-group-submit');
        if (ngSubmit) ngSubmit.addEventListener('click', submitNewGroup);

        var ngEmailAdd = $('sa-new-group-email-add');
        if (ngEmailAdd) {
            ngEmailAdd.addEventListener('click', function () {
                var raw = (($('sa-new-group-email-input') || {}).value || '').trim().toLowerCase();
                if (!raw) {
                    toast('Saisissez une adresse e-mail.', true);
                    return;
                }
                var cache = window._sscStaffCache || [];
                var found = null;
                for (var ei = 0; ei < cache.length; ei++) {
                    if ((cache[ei].email || '').trim().toLowerCase() === raw) {
                        found = cache[ei];
                        break;
                    }
                }
                if (!found) {
                    toast('Aucun compte staff actif avec cet e-mail.', true);
                    return;
                }
                groupSelected[String(found.id)] = found;
                saNewGroupRenderChips();
                saNewGroupRenderUserCards();
                var emInp = $('sa-new-group-email-input');
                if (emInp) emInp.value = '';
            });
        }

        var ngCards = $('sa-new-group-user-cards');
        if (ngCards) {
            ngCards.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-pick-id]');
                if (!btn) return;
                var id = btn.getAttribute('data-pick-id');
                var cache = window._sscStaffCache || [];
                if (groupSelected[id]) {
                    delete groupSelected[id];
                } else {
                    for (var i = 0; i < cache.length; i++) {
                        if (String(cache[i].id) === id) {
                            groupSelected[id] = cache[i];
                            break;
                        }
                    }
                }
                saNewGroupRenderChips();
                saNewGroupRenderUserCards();
            });
        }
        var ngSearch = $('sa-new-group-user-search');
        if (ngSearch) ngSearch.addEventListener('input', saNewGroupRenderUserCards);

        var ngChips = $('sa-new-group-chips');
        if (ngChips) {
            ngChips.addEventListener('click', function (e) {
                if (!e.target.classList.contains('sa-new-group-chip-x')) return;
                var chip = e.target.closest('[data-chip-id]');
                if (chip) {
                    delete groupSelected[chip.getAttribute('data-chip-id')];
                    saNewGroupRenderChips();
                    saNewGroupRenderUserCards();
                }
            });
        }

        var gsSave = $('sa-group-settings-save');
        if (gsSave) gsSave.addEventListener('click', saveGroupSettings);

        var rmGroupAv = $('sa-group-settings-remove-avatar');
        if (rmGroupAv) {
            rmGroupAv.addEventListener('click', function () {
                groupAvatarRemoved = true;
                var fi = $('sa-group-settings-avatar');
                if (fi) fi.value = '';
                toast('La photo sera retirée à l’enregistrement.');
            });
        }

        var gmList = $('ssc-group-members-list');
        if (gmList) {
            gmList.addEventListener('click', function (e) {
                var rm = e.target.closest('.ssc-rm-member');
                if (!rm) return;
                var row = rm.closest('[data-member-id]');
                var mid = row ? row.getAttribute('data-member-id') : '';
                if (mid) {
                    groupMembersPendingRemove[mid] = true;
                    row.remove();
                    renderGroupAddStaff(($('ssc-group-add-search') || {}).value || '');
                }
            });
        }

        var groupAddList = $('ssc-group-add-list');
        if (groupAddList) {
            groupAddList.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-add-staff-id]');
                if (!btn) return;
                var sid = btn.getAttribute('data-add-staff-id');
                if (sid) {
                    groupMembersPendingAdd[sid] = true;
                    delete groupMembersPendingRemove[sid];
                    toast('Membre ajouté — enregistrez pour confirmer.');
                    renderGroupAddStaff(($('ssc-group-add-search') || {}).value || '');
                }
            });
        }
        var groupAddSearch = $('ssc-group-add-search');
        if (groupAddSearch) {
            groupAddSearch.addEventListener('input', function () {
                renderGroupAddStaff(groupAddSearch.value);
            });
        }

        document.querySelectorAll('[data-ssc-modal-close]').forEach(function (el) {
            el.addEventListener('click', function () {
                var modal = el.closest('.modal');
                if (modal) closeModal(modal.id);
            });
        });

        window.addEventListener('resize', syncMobileLayout);
    }

    function startPolling() {
        window.clearInterval(pollTimer);
        pollTimer = window.setInterval(function () {
            if (!$('ssc-thread-list')) return;
            loadThreads();
            if (selectedThreadId) loadMessages(true);
        }, 8000);
    }

    function init() {
        var root = $('chat');
        if (!root) return;
        if (initialized && root.getAttribute('data-ssc-ready') === '1') {
            bootstrap();
            loadThreads();
            return;
        }
        initialized = true;
        root.setAttribute('data-ssc-ready', '1');
        api = window.TCF_CHAT_API || 'chat_api.php';
        myId = root.getAttribute('data-chat-me-id') || '';
        wireEvents();
        bootstrap();
        loadThreads();
        syncMobileLayout();
        startPolling();
    }

    window.TcfStaffChat = { init: init };
})();
