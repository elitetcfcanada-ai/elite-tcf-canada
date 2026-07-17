(function () {
    if (!window.TCF_ASSISTANT_ENABLED || !window.TCF_ASSISTANT_API) {
        return;
    }
    if (window.__tcfAssistantWidgetInit) {
        return;
    }
    window.__tcfAssistantWidgetInit = true;

    var root = document.getElementById('tcf-ai-assistant');
    if (!root) {
        return;
    }

    if (root.parentNode !== document.body) {
        document.body.appendChild(root);
    }

    var fab = document.getElementById('tcf-ai-assistant-fab');
    var panel = document.getElementById('tcf-ai-assistant-panel');
    var closeBtn = document.getElementById('tcf-ai-assistant-close');
    var log = document.getElementById('tcf-ai-assistant-log');
    var input = document.getElementById('tcf-ai-assistant-input');
    var send = document.getElementById('tcf-ai-assistant-send');
    var LS_KEY = window.TCF_ASSISTANT_LS_KEY || 'tcf_ai_assistant_history_v1';
    var GREETING = (root.getAttribute('data-greeting') || '').trim()
        || 'Bonjour, je suis votre assistant. Comment puis-je vous aider ?';
    var MAX_TURNS = 6;
    var MOBILE_MAX = 991;

    function isMobile() {
        return window.innerWidth <= MOBILE_MAX;
    }

    function addMessage(text, role) {
        var item = document.createElement('div');
        item.className = 'tcf-ai-assistant__msg tcf-ai-assistant__msg--' + role;
        var safe = String(text || '');
        var urlRegex = /https?:\/\/[^\s]+/g;
        var parts = safe.split(urlRegex);
        var matches = safe.match(urlRegex) || [];
        item.textContent = '';
        for (var i = 0; i < parts.length; i++) {
            if (parts[i]) item.appendChild(document.createTextNode(parts[i]));
            if (matches[i]) {
                var a = document.createElement('a');
                a.href = matches[i];
                a.textContent = matches[i];
                a.target = '_blank';
                a.rel = 'noopener noreferrer';
                a.style.color = 'inherit';
                a.style.textDecoration = 'underline';
                item.appendChild(a);
            }
        }
        log.appendChild(item);
        log.scrollTop = log.scrollHeight;
    }

    function loadHistory() {
        try {
            var raw = localStorage.getItem(LS_KEY);
            var parsed = raw ? JSON.parse(raw) : [];
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function saveHistory(hist) {
        try {
            localStorage.setItem(LS_KEY, JSON.stringify(hist));
        } catch (e) {}
    }

    function pushHistory(role, text) {
        var hist = loadHistory();
        hist.push({ role: role, text: String(text || '').slice(0, 1500) });
        var maxMsgs = MAX_TURNS * 2;
        if (hist.length > maxMsgs) {
            hist = hist.slice(hist.length - maxMsgs);
        }
        saveHistory(hist);
        return hist;
    }

    function renderHistory() {
        var hist = loadHistory();
        log.innerHTML = '';
        if (!hist.length) {
            addMessage(GREETING, 'bot');
            return;
        }
        for (var i = 0; i < hist.length; i++) {
            var h = hist[i];
            if (!h || !h.role || !h.text) continue;
            addMessage(h.text, h.role === 'bot' ? 'bot' : 'user');
        }
    }

    function setLoading(loading) {
        send.disabled = loading;
        send.textContent = loading ? '...' : 'Envoyer';
    }

    function setOpen(open) {
        if (!panel) {
            return;
        }
        panel.classList.toggle('is-open', open);
        root.classList.toggle('tcf-ai-assistant--open', open);
        panel.hidden = !open;
        panel.setAttribute('aria-hidden', open ? 'false' : 'true');
        if (fab) {
            fab.setAttribute('aria-expanded', open ? 'true' : 'false');
            fab.setAttribute('aria-label', open ? 'Fermer l\'assistant' : 'Ouvrir l\'assistant');
        }
        var headerAssistant = document.getElementById('tcfHeaderNavAssistant');
        if (headerAssistant) {
            headerAssistant.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
        document.body.classList.toggle('tcf-assistant-open', open && isMobile());
        if (open) {
            renderHistory();
            window.requestAnimationFrame(function () {
                if (input) {
                    input.focus();
                }
            });
        }
    }

    function closePanel() {
        setOpen(false);
    }

    function openPanel() {
        setOpen(true);
    }

    function togglePanel() {
        setOpen(!panel.classList.contains('is-open'));
    }

    async function askAssistant() {
        var question = (input.value || '').trim();
        if (question.length < 2) {
            return;
        }
        addMessage(question, 'user');
        var history = pushHistory('user', question);
        input.value = '';
        setLoading(true);

        try {
            var response = await fetch(window.TCF_ASSISTANT_API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: question, history: history })
            });
            var data = await response.json();
            if (!response.ok || !data.ok) {
                throw new Error((data && data.message) ? data.message : 'Assistant indisponible.');
            }
            var reply = data.reply || 'Je n\u2019ai pas de réponse pour le moment.';
            addMessage(reply, 'bot');
            pushHistory('bot', reply);
        } catch (err) {
            addMessage('Erreur: ' + (err.message || 'Assistant indisponible.'), 'bot');
        } finally {
            setLoading(false);
            input.focus();
        }
    }

    var headerBtn = document.getElementById('tcfHeaderNavAssistant');
    if (headerBtn) {
        root.classList.add('tcf-ai-assistant--header-trigger');
        if (headerBtn.getAttribute('data-tcf-assistant-bound') !== '1') {
            headerBtn.setAttribute('data-tcf-assistant-bound', '1');
            headerBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                togglePanel();
            });
        }
    }

    if (fab && !headerBtn) {
        fab.addEventListener('click', togglePanel);
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closePanel);
    }

    send.addEventListener('click', askAssistant);
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            askAssistant();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && panel.classList.contains('is-open')) {
            closePanel();
        }
    });

    document.addEventListener('click', function (e) {
        if (!panel.classList.contains('is-open') || !isMobile()) {
            return;
        }
        if (panel.contains(e.target)) {
            return;
        }
        if (headerBtn && (headerBtn === e.target || headerBtn.contains(e.target))) {
            return;
        }
        if (fab && (fab === e.target || fab.contains(e.target))) {
            return;
        }
        closePanel();
    });

    window.addEventListener('resize', function () {
        if (!panel.classList.contains('is-open')) {
            document.body.classList.remove('tcf-assistant-open');
        } else {
            document.body.classList.toggle('tcf-assistant-open', isMobile());
        }
    });

    panel.hidden = true;
    panel.setAttribute('aria-hidden', 'true');

    renderHistory();

    window.tcfAssistantOpen = openPanel;
    window.tcfAssistantClose = closePanel;
    window.tcfAssistantToggle = togglePanel;

    if (window.__tcfAssistantPendingOpen) {
        window.__tcfAssistantPendingOpen = false;
        openPanel();
    }
})();
