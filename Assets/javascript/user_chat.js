(function () {
    'use strict';

    var app = document.getElementById('tcf-user-chat-app');
    if (!app) return;
    var api = window.TCF_CHAT_API || 'chat_api.php';
    var threadsEl = document.getElementById('tcf-chat-threads');
    var layoutEl = document.querySelector('.tcf-chat-layout');
    var headEl = document.getElementById('tcf-chat-thread-head-title');
    var backBtn = document.getElementById('tcf-chat-back');
    var msgsEl = document.getElementById('tcf-chat-messages');
    var olderBar = document.getElementById('tcf-chat-older-bar');
    var input = document.getElementById('tcf-chat-input');
    var sendBtn = document.getElementById('tcf-chat-send');
    var refreshBtn = document.getElementById('tcf-chat-refresh');
    var onlineCountEl = document.getElementById('tcf-chat-online-count');
    var meStatusEl = document.getElementById('tcf-chat-me-status');
    var profileLink = document.getElementById('tcf-chat-profile-link');
    var emojiBtn = document.getElementById('tcf-chat-emoji-btn');
    var emojiPop = document.getElementById('tcf-chat-emoji-popover');
    var composerBlockedEl = document.getElementById('tcf-chat-composer-blocked');
    var composerWrap = document.getElementById('tcf-chat-composer-wrap');
    var themeLightBtn = document.getElementById('tcf-chat-theme-light');
    var themeDarkBtn = document.getElementById('tcf-chat-theme-dark');

    var PAGE_SIZE = 40;
    var THEME_STORAGE_KEY = 'tcf_messages_chat_theme';

    var state = {
        me: null,
        threads: [],
        threadId: null,
        mobileShowingChat: false,
        threadCanSend: true,
    };

    var msgState = {
        list: [],
        oldestId: null,
        newestId: null,
        hasMoreOlder: false,
        loadingOlder: false,
        loadingInitial: false,
    };

    var scrollOlderLock = false;

    function isNarrowChat() {
        return window.matchMedia('(max-width: 900px)').matches;
    }

    function syncChatMobileLayout() {
        if (!layoutEl) return;
        if (!isNarrowChat()) {
            layoutEl.classList.remove('tcf-chat-layout--conversation');
            return;
        }
        if (state.threadId && state.mobileShowingChat) {
            layoutEl.classList.add('tcf-chat-layout--conversation');
        } else {
            layoutEl.classList.remove('tcf-chat-layout--conversation');
        }
    }

    var assistanceUser = null;
    var ASSISTANCE_DISPLAY_NAME = 'Elite TCF Canada';

    var EMOJI_QUICK = (
        '😀 😃 😄 😁 😅 😂 🤣 😊 😍 🥰 😘 😜 🤔 😎 👍 👎 👏 🙏 💪 🔥 ✨ ❤️ ' +
        '🎉 ✅ ❌ ⭐ 📚 ✍️ 🇫🇷 🇨🇦 😢 😭 🤝 💬 📝 🎯 ⏰'
    )
        .split(/\s+/)
        .filter(Boolean);

    function esc(s) {
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

    function formatDateSeparator(d) {
        if (isNaN(d.getTime())) return '';
        var now = new Date();
        var today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        var msgDay = new Date(d.getFullYear(), d.getMonth(), d.getDate());
        var diffDays = Math.round((today - msgDay) / 86400000);
        if (diffDays === 0) return "Aujourd'hui";
        if (diffDays === 1) return 'Hier';
        var days = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
        if (diffDays > 1 && diffDays < 7) {
            var name = days[d.getDay()];
            return name.charAt(0).toUpperCase() + name.slice(1);
        }
        return pad2(d.getDate()) + '/' + pad2(d.getMonth() + 1) + '/' + d.getFullYear();
    }

    function formatTimeOnly(createdAt) {
        var d = parseSqlDate(createdAt);
        if (isNaN(d.getTime())) return '';
        return pad2(d.getHours()) + ':' + pad2(d.getMinutes());
    }

    function currentThreadIsGroup() {
        var id = state.threadId;
        if (!id || !state.threads || !state.threads.length) return false;
        for (var i = 0; i < state.threads.length; i++) {
            if (String(state.threads[i].id) === String(id)) {
                return state.threads[i].thread_type === 'group';
            }
        }
        return false;
    }

    function threadPeerIsAssistance(t) {
        if (!t || t.thread_type === 'group' || !assistanceUser || !assistanceUser.id) return false;
        var p = t.peer;
        return !!(p && String(p.id) === String(assistanceUser.id));
    }

    function messageFromAssistance(m) {
        if (!m || m.mine || !assistanceUser || !assistanceUser.id) return false;
        var sid = m.sender_id != null ? String(m.sender_id) : '';
        return sid !== '' && sid === String(assistanceUser.id);
    }

    function brandLogoImgHtml(size) {
        var src =
            typeof window.TCF_BRAND_LOGO === 'string' && window.TCF_BRAND_LOGO
                ? window.TCF_BRAND_LOGO
                : 'Assets/branding/favicon.svg';
        return (
            '<img src="' +
            escAttr(src) +
            '" alt="" class="tcf-brand-logo tcf-brand-logo--chat" width="' +
            size +
            '" height="' +
            size +
            '" decoding="async" loading="lazy">'
        );
    }

    function assistAvatarThreadHtml() {
        return (
            '<span class="tcf-chat-thread-avatar tcf-chat-assist-avatar" aria-hidden="true">' +
            brandLogoImgHtml(28) +
            '</span>'
        );
    }

    function assistAvatarMsgHtml() {
        return (
            '<span class="tcf-chat-msg-avatar tcf-chat-assist-avatar" aria-hidden="true">' +
            brandLogoImgHtml(28) +
            '</span>'
        );
    }

    function setThreadHead(t) {
        if (!headEl) return;
        if (!t) {
            headEl.textContent = 'Sélectionnez une conversation';
            return;
        }
        if (threadPeerIsAssistance(t)) {
            headEl.innerHTML =
                '<span class="tcf-chat-thread-head-inner">' +
                assistAvatarThreadHtml() +
                '<span>' +
                esc(ASSISTANCE_DISPLAY_NAME) +
                '</span></span>';
            return;
        }
        if (t.thread_type === 'group') {
            var gTitle = t.title || 'Groupe';
            if (t.group_avatar_url) {
                headEl.innerHTML =
                    '<span class="tcf-chat-thread-head-inner">' +
                    '<span class="tcf-chat-thread-avatar"><img src="' +
                    escAttr(t.group_avatar_url) +
                    '" alt=""></span><span>' +
                    esc(gTitle) +
                    '</span></span>';
            } else {
                headEl.innerHTML =
                    '<span class="tcf-chat-thread-head-inner">' +
                    '<span class="tcf-chat-thread-avatar tcf-chat-group-icon"><i class="bx bx-group" aria-hidden="true"></i></span><span>' +
                    esc(gTitle) +
                    '</span></span>';
            }
            return;
        }
        var title = t.title || (t.peer ? t.peer.name : 'Conversation');
        headEl.textContent = title || 'Conversation';
    }

    function syncThreadFromApiThread(th) {
        if (!th || th.thread_type !== 'group' || !state.threadId) return;
        var tid = state.threadId;
        var url = th.group_avatar_url || '';
        state.threads = state.threads.map(function (t) {
            if (String(t.id) !== String(tid)) return t;
            var copy = Object.assign({}, t);
            copy.group_avatar_url = url;
            if (th.title) copy.title = th.title;
            return copy;
        });
        var cur = state.threads.find(function (x) {
            return String(x.id) === String(tid);
        });
        setThreadHead(cur || null);
        renderThreads();
    }

    function updateComposerState() {
        var can = state.threadCanSend !== false;
        if (composerBlockedEl) {
            if (!state.threadId || can) {
                composerBlockedEl.hidden = true;
                composerBlockedEl.textContent = '';
            } else {
                composerBlockedEl.hidden = false;
                composerBlockedEl.textContent =
                    'Seuls les administrateurs peuvent envoyer des messages dans ce groupe. Vous pouvez lire la conversation.';
            }
        }
        if (composerWrap) {
            composerWrap.style.display = !state.threadId || can ? '' : 'none';
        }
        if (input) input.disabled = !can;
        if (sendBtn) sendBtn.disabled = !can;
        if (emojiBtn) emojiBtn.disabled = !can;
    }

    function initEmojiPicker() {
        if (!emojiBtn || !emojiPop || !input) return;
        EMOJI_QUICK.forEach(function (ch) {
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
            e.preventDefault();
            e.stopPropagation();
            emojiPop.hidden = !emojiPop.hidden;
        });
        document.addEventListener('click', function () {
            if (emojiPop) emojiPop.hidden = true;
        });
        emojiPop.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    function fetchJson(url, opts) {
        return fetch(url, Object.assign({ credentials: 'same-origin' }, opts || {})).then(function (r) {
            return r.json();
        });
    }

    function resetMessageState() {
        msgState.list = [];
        msgState.oldestId = null;
        msgState.newestId = null;
        msgState.hasMoreOlder = false;
        msgState.loadingOlder = false;
        msgState.loadingInitial = false;
        if (olderBar) olderBar.hidden = true;
        if (msgsEl) msgsEl.innerHTML = '';
    }

    function buildMessagesHtml(rows) {
        if (!rows || !rows.length) return '';
        var isGroup = currentThreadIsGroup();
        var parts = [];
        var lastKey = '';
        for (var i = 0; i < rows.length; i++) {
            var m = rows[i];
            var d = parseSqlDate(m.created_at);
            var key = isNaN(d.getTime()) ? 'unknown-' + i : dateKey(d);
            if (key !== lastKey) {
                lastKey = key;
                parts.push(
                    '<div class="tcf-chat-day-sep" role="presentation"><span>' +
                    esc(formatDateSeparator(d)) +
                    '</span></div>'
                );
            }
            var mine = !!m.mine;
            var avm;
            if (messageFromAssistance(m)) {
                avm = assistAvatarMsgHtml();
            } else if (m.sender_avatar_url) {
                avm =
                    '<span class="tcf-chat-msg-avatar"><img src="' +
                    escAttr(m.sender_avatar_url) +
                    '" alt=""></span>';
            } else {
                avm =
                    '<span class="tcf-chat-msg-avatar">' +
                    esc((m.sender_name || 'U').slice(0, 1).toUpperCase()) +
                    '</span>';
            }
            var myAvatar =
                state.me && state.me.avatar_url
                    ? '<span class="tcf-chat-msg-avatar tcf-chat-msg-avatar--me"><img src="' +
                      escAttr(state.me.avatar_url) +
                      '" alt=""></span>'
                    : '<span class="tcf-chat-msg-avatar tcf-chat-msg-avatar--me">' +
                      esc(((state.me && state.me.name) || 'Moi').slice(0, 1).toUpperCase()) +
                      '</span>';
            var showAuthor = isGroup && !mine;
            var bubbleClass = mine ? 'tcf-chat-bubble tcf-chat-bubble--me' : 'tcf-chat-bubble tcf-chat-bubble--them';
            var rowClass = 'tcf-chat-msg-row' + (mine ? ' tcf-chat-msg-row--me' : '');
            parts.push(
                '<div class="' +
                    rowClass +
                    '">' +
                    (mine ? '' : avm) +
                    '<div class="' +
                    bubbleClass +
                    '">' +
                    (showAuthor ? '<div class="tcf-chat-bubble-author">' + esc(m.sender_name || '') + '</div>' : '') +
                    '<div class="tcf-chat-bubble-text">' +
                    esc(m.message || '') +
                    '</div><div class="tcf-chat-bubble-foot">' +
                    esc(formatTimeOnly(m.created_at)) +
                    '</div></div>' +
                    (mine ? myAvatar : '') +
                    '</div>'
            );
        }
        return parts.join('');
    }

    function renderAllMessages(scrollToBottom) {
        if (!msgsEl) return;
        var nearBottom = msgsEl.scrollHeight - msgsEl.scrollTop - msgsEl.clientHeight < 120;
        if (!msgState.list.length) {
            msgsEl.innerHTML = '<p class="tcf-chat-empty">Aucun message.</p>';
        } else {
            msgsEl.innerHTML = buildMessagesHtml(msgState.list);
        }
        if (scrollToBottom || nearBottom) {
            msgsEl.scrollTop = msgsEl.scrollHeight;
        }
    }

    function applyThreadMeta(j) {
        if (j.thread && typeof j.thread.can_send !== 'undefined') {
            state.threadCanSend = !!j.thread.can_send;
        }
        updateComposerState();
        if (j.thread) {
            syncThreadFromApiThread(j.thread);
        }
    }

    function loadMessagesInitial() {
        if (!state.threadId || !msgsEl) return;
        msgState.loadingInitial = true;
        fetchJson(api, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'get_messages',
                thread_id: state.threadId,
                limit: PAGE_SIZE,
            }),
        })
            .then(function (j) {
                if (!j || !j.ok) return;
                applyThreadMeta(j);
                msgState.list = j.messages || [];
                msgState.hasMoreOlder = !!j.has_more_older;
                msgState.oldestId = j.oldest_id != null ? j.oldest_id : null;
                msgState.newestId = j.newest_id != null ? j.newest_id : null;
                renderAllMessages(true);
            })
            .finally(function () {
                msgState.loadingInitial = false;
            });
    }

    function loadOlderMessages() {
        if (
            !state.threadId ||
            !msgsEl ||
            !msgState.hasMoreOlder ||
            msgState.loadingOlder ||
            msgState.loadingInitial ||
            msgState.oldestId == null
        ) {
            return;
        }
        msgState.loadingOlder = true;
        if (olderBar) olderBar.hidden = false;
        var el = msgsEl;
        var oldSh = el.scrollHeight;
        var oldSt = el.scrollTop;
        fetchJson(api, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'get_messages',
                thread_id: state.threadId,
                limit: PAGE_SIZE,
                before_id: msgState.oldestId,
            }),
        })
            .then(function (j) {
                if (!j || !j.ok) return;
                var batch = j.messages || [];
                if (batch.length) {
                    msgState.list = batch.concat(msgState.list);
                    msgState.oldestId = j.oldest_id != null ? j.oldest_id : msgState.oldestId;
                    msgState.hasMoreOlder = !!j.has_more_older;
                    renderAllMessages(false);
                    el.scrollTop = el.scrollHeight - oldSh + oldSt;
                } else {
                    msgState.hasMoreOlder = false;
                }
            })
            .finally(function () {
                msgState.loadingOlder = false;
                if (olderBar) olderBar.hidden = true;
            });
    }

    function pollNewerMessages() {
        if (!state.threadId || msgState.newestId == null || !msgsEl) return;
        fetchJson(api, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'get_messages',
                thread_id: state.threadId,
                limit: 50,
                after_id: msgState.newestId,
            }),
        }).then(function (j) {
            if (!j || !j.ok || !j.messages || !j.messages.length) return;
            var stick = msgsEl.scrollHeight - msgsEl.scrollTop - msgsEl.clientHeight < 140;
            msgState.list = msgState.list.concat(j.messages);
            msgState.newestId = j.newest_id != null ? j.newest_id : msgState.newestId;
            renderAllMessages(false);
            if (stick) msgsEl.scrollTop = msgsEl.scrollHeight;
        });
    }

    function renderThreads() {
        if (!threadsEl) return;
        if (!state.threads.length) {
            threadsEl.innerHTML = '<div class="tcf-chat-empty-threads">Aucune conversation.</div>';
            return;
        }
        threadsEl.innerHTML = state.threads
            .map(function (t) {
                var active = state.threadId && String(state.threadId) === String(t.id);
                var isAssist = threadPeerIsAssistance(t);
                var name = isAssist ? ASSISTANCE_DISPLAY_NAME : t.title || (t.peer ? t.peer.name : 'Conversation');
                var sub =
                    t.thread_type === 'group'
                        ? String(t.member_count || 0) + ' membres'
                        : t.peer && t.peer.online
                          ? 'En ligne'
                          : 'Hors ligne';
                if (t.thread_type === 'group' && typeof t.online_count !== 'undefined') {
                    sub = String(t.online_count || 0) + ' en ligne · ' + String(t.member_count || 0) + ' membres';
                }
                var av;
                if (isAssist) {
                    av = assistAvatarThreadHtml();
                } else if (t.thread_type === 'group' && t.group_avatar_url) {
                    av =
                        '<span class="tcf-chat-thread-avatar"><img src="' +
                        escAttr(t.group_avatar_url) +
                        '" alt=""></span>';
                } else if (t.thread_type === 'group') {
                    av =
                        '<span class="tcf-chat-thread-avatar tcf-chat-group-icon"><i class="bx bx-group" aria-hidden="true"></i></span>';
                } else if (t.peer && t.peer.avatar_url) {
                    av =
                        '<span class="tcf-chat-thread-avatar"><img src="' +
                        escAttr(t.peer.avatar_url) +
                        '" alt=""></span>';
                } else {
                    av =
                        '<span class="tcf-chat-thread-avatar">' +
                        esc((name || 'U').slice(0, 1).toUpperCase()) +
                        '</span>';
                }
                return (
                    '<button type="button" class="tcf-chat-thread-btn' +
                    (active ? ' is-active' : '') +
                    '" data-thread-id="' +
                    escAttr(String(t.id)) +
                    '">' +
                    av +
                    '<span style="min-width:0;flex:1;"><strong>' +
                    esc(name) +
                    '</strong><small>' +
                    esc(sub) +
                    '</small></span></button>'
                );
            })
            .join('');
    }

    /**
     * @param {boolean} reloadMessages - Si false, met seulement à jour la liste (aperçus) sans recharger le fil ni perdre le scroll.
     */
    function loadThreads(reloadMessages) {
        if (reloadMessages === undefined) reloadMessages = true;
        fetchJson(api + '?action=list_threads').then(function (j) {
            state.threads = j && j.ok ? j.threads || [] : [];
            if (!state.threadId && state.threads.length && !isNarrowChat()) {
                state.threadId = state.threads[0].id;
            }
            renderThreads();
            var t = state.threads.find(function (x) {
                return String(x.id) === String(state.threadId);
            });
            setThreadHead(t || null);
            state.threadCanSend = true;
            updateComposerState();
            syncChatMobileLayout();
            if (reloadMessages && state.threadId) {
                loadMessagesInitial();
            } else if (!state.threadId) {
                resetMessageState();
            }
        });
    }

    function ensureSupportDirectThread() {
        if (!assistanceUser || !assistanceUser.id) {
            loadThreads();
            return;
        }
        fetchJson(api, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'ensure_direct', target_user_id: assistanceUser.id }),
        })
            .catch(function () {})
            .finally(loadThreads);
    }

    function bootstrap() {
        fetchJson(api + '?action=bootstrap')
            .then(function (j) {
                if (!j || !j.ok) return;
                state.me = j.me || null;
                assistanceUser = j.assistance_user || null;
                if (onlineCountEl) {
                    if (j.can_view_online_count) {
                        onlineCountEl.style.display = '';
                        onlineCountEl.textContent = String(j.online_count || 0) + ' en ligne';
                    } else {
                        onlineCountEl.style.display = 'none';
                    }
                }
                if (meStatusEl) meStatusEl.textContent = 'Vous êtes en ligne';
                if (profileLink) {
                    profileLink.textContent = 'Mon profil (en ligne)';
                    profileLink.href = j.profile_href || '#';
                }
            })
            .finally(ensureSupportDirectThread);
    }

    function applyChatTheme(mode) {
        var m = mode === 'dark' ? 'dark' : 'light';
        app.classList.remove('tcf-chat-theme-light', 'tcf-chat-theme-dark');
        app.classList.add(m === 'dark' ? 'tcf-chat-theme-dark' : 'tcf-chat-theme-light');
        try {
            localStorage.setItem(THEME_STORAGE_KEY, m);
        } catch (e) {}
        if (themeLightBtn) themeLightBtn.setAttribute('aria-pressed', m === 'light' ? 'true' : 'false');
        if (themeDarkBtn) themeDarkBtn.setAttribute('aria-pressed', m === 'dark' ? 'true' : 'false');
    }

    function initChatTheme() {
        var saved = 'light';
        try {
            saved = localStorage.getItem(THEME_STORAGE_KEY) || 'light';
        } catch (e) {}
        applyChatTheme(saved === 'dark' ? 'dark' : 'light');
    }

    if (themeLightBtn) {
        themeLightBtn.addEventListener('click', function () {
            applyChatTheme('light');
        });
    }
    if (themeDarkBtn) {
        themeDarkBtn.addEventListener('click', function () {
            applyChatTheme('dark');
        });
    }
    initChatTheme();

    if (threadsEl) {
        threadsEl.addEventListener('click', function (e) {
            var bt = e.target.closest && e.target.closest('[data-thread-id]');
            if (!bt) return;
            state.threadId = bt.getAttribute('data-thread-id');
            state.mobileShowingChat = true;
            resetMessageState();
            renderThreads();
            var t = state.threads.find(function (x) {
                return String(x.id) === String(state.threadId);
            });
            setThreadHead(t || null);
            state.threadCanSend = true;
            updateComposerState();
            syncChatMobileLayout();
            loadMessagesInitial();
        });
    }

    if (backBtn && layoutEl) {
        backBtn.addEventListener('click', function () {
            state.mobileShowingChat = false;
            syncChatMobileLayout();
        });
    }

    var _resizeT;
    window.addEventListener('resize', function () {
        window.clearTimeout(_resizeT);
        _resizeT = window.setTimeout(syncChatMobileLayout, 120);
    });

    if (msgsEl) {
        msgsEl.addEventListener('scroll', function () {
            if (msgsEl.scrollTop > 100) return;
            if (scrollOlderLock || msgState.loadingOlder || msgState.loadingInitial) return;
            scrollOlderLock = true;
            window.requestAnimationFrame(function () {
                scrollOlderLock = false;
                if (msgsEl.scrollTop <= 100) loadOlderMessages();
            });
        });
    }

    function send() {
        var txt = (input && input.value ? input.value : '').trim();
        if (!txt || !state.threadId) return;
        if (state.threadCanSend === false) return;
        if (sendBtn) sendBtn.disabled = true;
        fetchJson(api, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'send_message', thread_id: state.threadId, message: txt }),
        })
            .then(function (j) {
                if (j && j.ok && input) input.value = '';
                loadThreads(false);
                loadMessagesInitial();
            })
            .finally(function () {
                if (sendBtn) sendBtn.disabled = false;
            });
    }

    if (sendBtn) sendBtn.addEventListener('click', send);
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function () {
            loadThreads();
            if (state.threadId) loadMessagesInitial();
        });
    }
    if (input) {
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                send();
            }
        });
    }

    initEmojiPicker();
    updateComposerState();
    syncChatMobileLayout();
    bootstrap();
    window.setInterval(function () {
        loadThreads(false);
        pollNewerMessages();
    }, 8000);
})();
