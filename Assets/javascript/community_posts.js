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

    function clipText(text, maxChars) {
        var raw = String(text || '');
        var limit = maxChars || 160;
        if (raw.length <= limit) {
            return { short: raw, full: raw, clipped: false };
        }
        // Couper sur un espace proche pour un rendu type Facebook
        var cut = raw.slice(0, limit);
        var sp = cut.lastIndexOf(' ');
        if (sp > Math.floor(limit * 0.55)) {
            cut = cut.slice(0, sp);
        }
        return { short: cut.replace(/\s+$/, ''), full: raw, clipped: true };
    }

    function renderPost(p) {
        var liked = !!p.liked_by_me;
        var imgSrc = p.image_href || p.image_url || '';
        var img = imgSrc
            ? '<div class="tcp-card__media-wrap"><img class="tcp-card__media" src="' + esc(imgSrc) + '" alt="" loading="lazy"></div>'
            : '';
        var bodyHtml = '';
        if (p.body) {
            var clipped = clipText(p.body, 160);
            var premiumPill = String(p.visibility || '') === 'premium'
                ? '<span class="tcp-card__premium-pill">Premium</span>'
                : '';
            if (clipped.clipped) {
                bodyHtml =
                    '<div class="tcp-card__body" data-expanded="0">' +
                    premiumPill +
                    '<span class="tcp-card__body-short">' +
                    esc(clipped.short) +
                    '…</span>' +
                    '<span class="tcp-card__body-full" hidden>' +
                    esc(clipped.full) +
                    '</span> ' +
                    '<button type="button" class="tcp-card__see-more" aria-expanded="false">Voir plus</button>' +
                    '</div>';
            } else {
                bodyHtml =
                    '<div class="tcp-card__body">' +
                    premiumPill +
                    '<span class="tcp-card__body-full">' +
                    esc(clipped.full) +
                    '</span></div>';
            }
        } else if (String(p.visibility || '') === 'premium') {
            bodyHtml = '<div class="tcp-card__body"><span class="tcp-card__premium-pill">Premium</span></div>';
        }
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
            bodyHtml +
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
        var seeMore = e.target.closest('.tcp-card__see-more');
        if (seeMore) {
            e.preventDefault();
            var bodyEl = seeMore.closest('.tcp-card__body');
            if (!bodyEl) return;
            var shortEl = bodyEl.querySelector('.tcp-card__body-short');
            var fullEl = bodyEl.querySelector('.tcp-card__body-full');
            if (!shortEl || !fullEl) return;
            var expanded = bodyEl.getAttribute('data-expanded') === '1';
            if (expanded) {
                shortEl.hidden = false;
                fullEl.hidden = true;
                seeMore.textContent = 'Voir plus';
                seeMore.setAttribute('aria-expanded', 'false');
                bodyEl.setAttribute('data-expanded', '0');
            } else {
                shortEl.hidden = true;
                fullEl.hidden = false;
                seeMore.textContent = 'Voir moins';
                seeMore.setAttribute('aria-expanded', 'true');
                bodyEl.setAttribute('data-expanded', '1');
            }
            return;
        }

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
