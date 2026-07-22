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

    function renderPost(p) {
        var liked = !!p.liked_by_me;
        var imgSrc = p.image_href || p.image_url || '';
        var img = imgSrc
            ? '<div class="tcp-card__media-wrap"><img class="tcp-card__media" src="' + esc(imgSrc) + '" alt="" loading="lazy"></div>'
            : '';
        var body = p.body ? '<div class="tcp-card__body">' + esc(p.body) + '</div>' : '';
        return (
            '<article class="tcp-card" data-id="' +
            esc(p.id) +
            '">' +
            img +
            body +
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
