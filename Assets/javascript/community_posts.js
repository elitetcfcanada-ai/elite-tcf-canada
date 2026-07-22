(function () {
    'use strict';

    var root = document.getElementById('tcpFeed');
    if (!root) return;

    var api = root.getAttribute('data-api') || 'community_api.php';
    var logged = root.getAttribute('data-logged') === '1';
    var loginUrl = root.getAttribute('data-login') || 'login.php';

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function escAttr(s) {
        return esc(s).replace(/"/g, '&quot;');
    }

    function shortLinkLabel(url) {
        var raw = String(url || '').trim();
        if (!raw) return '';
        try {
            var u = new URL(raw, window.location.origin);
            var host = (u.hostname || '').replace(/^www\./i, '');
            var path = u.pathname && u.pathname !== '/' ? u.pathname : '';
            var label = host + path;
            if (u.search) label += '…';
            if (label.length > 40) label = label.slice(0, 37) + '…';
            return label || raw;
        } catch (e) {
            return raw.length > 40 ? raw.slice(0, 37) + '…' : raw;
        }
    }

    function renderPost(p) {
        var liked = !!p.liked_by_me;
        var imgSrc = p.image_href || p.image_url || '';
        var img = imgSrc
            ? '<div class="tcp-card__media-wrap"><img class="tcp-card__media" src="' + esc(imgSrc) + '" alt="" loading="lazy"></div>'
            : '';
        var body = p.body ? '<div class="tcp-card__body">' + esc(p.body) + '</div>' : '';
        var link = (p.link_url || '').trim();
        var linkHtml = link
            ? '<a class="tcp-card__link" href="' +
              escAttr(link) +
              '" target="_blank" rel="noopener noreferrer" title="' +
              escAttr(link) +
              '"><i class="bx bx-link" aria-hidden="true"></i><span class="tcp-card__link-text">' +
              esc(shortLinkLabel(link)) +
              '</span></a>'
            : '';
        return (
            '<article class="tcp-card" data-id="' +
            esc(p.id) +
            '">' +
            img +
            body +
            linkHtml +
            '<div class="tcp-card__actions">' +
            '<button type="button" class="tcp-like-btn' +
            (liked ? ' is-liked' : '') +
            '" data-like="' +
            esc(p.id) +
            '" aria-pressed="' +
            (liked ? 'true' : 'false') +
            '" aria-label="J’aime">' +
            '<i class="bx ' +
            (liked ? 'bxs-heart' : 'bx-heart') +
            '"></i>' +
            '<span class="tcp-like-count">' +
            esc(p.likes_count || 0) +
            '</span>' +
            '</button>' +
            '</div></article>'
        );
    }

    function setHtml(html) {
        root.innerHTML = html;
    }

    function load() {
        setHtml('<div class="tcp-feed__loading"><i class="bx bx-loader-alt bx-spin"></i> Chargement…</div>');
        fetch(api + '?action=list', { credentials: 'same-origin' })
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                if (!data || !data.success) {
                    setHtml('<div class="tcp-feed__error">Impossible de charger les annonces.</div>');
                    return;
                }
                var posts = data.data || data.posts || [];
                if (!posts.length) {
                    setHtml('<div class="tcp-feed__empty">Aucune annonce pour le moment.</div>');
                    return;
                }
                setHtml(posts.map(renderPost).join(''));
            })
            .catch(function () {
                setHtml('<div class="tcp-feed__error">Erreur réseau.</div>');
            });
    }

    root.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-like]');
        if (!btn) return;
        if (!logged) {
            window.location.href = loginUrl;
            return;
        }
        var id = btn.getAttribute('data-like');
        var fd = new FormData();
        fd.append('action', 'like_toggle');
        fd.append('post_id', id);
        btn.disabled = true;
        fetch(api, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                if (!data || !data.success) {
                    if (data && data.need_login) {
                        window.location.href = loginUrl;
                    }
                    return;
                }
                btn.classList.toggle('is-liked', !!data.liked);
                btn.setAttribute('aria-pressed', data.liked ? 'true' : 'false');
                var icon = btn.querySelector('i');
                if (icon) icon.className = 'bx ' + (data.liked ? 'bxs-heart' : 'bx-heart');
                var c = btn.querySelector('.tcp-like-count');
                if (c) c.textContent = String(data.likes_count || 0);
            })
            .finally(function () {
                btn.disabled = false;
            });
    });

    load();
})();
