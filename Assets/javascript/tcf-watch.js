(function () {
    'use strict';

    var api = window.TCF_VIDEOS_API || 'videos_api.php';
    var loginHref = window.TCF_LOGIN_HREF || 'login.php';
    var user = window.TCF_VIDEO_USER;
    var videoId = parseInt(window.TCF_WATCH_VIDEO_ID, 10) || 0;
    var player = document.getElementById('tcf-watch-player');
    var likeBtn = document.getElementById('tcf-watch-like-btn');
    var likeCountEl = document.getElementById('tcf-watch-like-count');
    var loginHint = document.getElementById('tcf-watch-login-hint');
    var commentForm = document.getElementById('tcf-watch-comment-form');
    var commentBody = document.getElementById('tcf-watch-comment-body');
    var commentCancel = document.getElementById('tcf-watch-comment-cancel');
    var commentMsg = document.getElementById('tcf-watch-comment-msg');
    var commentsList = document.getElementById('tcf-watch-comments-list');
    var commentCountLabel = document.getElementById('tcf-watch-comment-count-label');

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function setLikeUi(likes, liked, canClick) {
        if (!likeBtn || !likeCountEl) return;
        likeCountEl.textContent = String(likes);
        likeBtn.classList.toggle('is-on', !!liked);
        likeBtn.setAttribute('aria-pressed', liked ? 'true' : 'false');
        var ic = likeBtn.querySelector('i');
        if (ic) ic.className = liked ? 'bx bxs-like' : 'bx bx-like';
        likeBtn.disabled = !canClick;
    }

    function countComments(comments) {
        var n = 0;
        (comments || []).forEach(function (c) {
            n += 1;
            n += (c.replies || []).length;
        });
        return n;
    }

    function renderComments(comments) {
        if (!commentsList) return;
        var total = countComments(comments);
        if (commentCountLabel) {
            commentCountLabel.textContent = total > 0 ? total + ' commentaire' + (total > 1 ? 's' : '') : 'Commentaires';
        }
        if (!comments.length) {
            commentsList.innerHTML = '<p style="color:#606060;font-size:0.875rem;margin:0;">Soyez le premier à commenter.</p>';
            return;
        }
        var html = '';
        comments.forEach(function (c) {
            html += commentHtml(c);
            (c.replies || []).forEach(function (r) {
                html += '<div class="tcf-watch-c-replies">' + commentHtml(r) + '</div>';
            });
        });
        commentsList.innerHTML = html;
    }

    function commentHtml(c) {
        var av = c.avatar_url && String(c.avatar_url).trim();
        var avatar = av
            ? '<img class="tcf-watch-c-avatar" src="' + esc(av) + '" alt="" width="40" height="40" loading="lazy">'
            : '<div class="tcf-watch-c-avatar tcf-watch-c-avatar--ph"><i class="bx bx-user"></i></div>';
        return (
            '<article class="tcf-watch-c-item">' +
            avatar +
            '<div class="tcf-watch-c-body-wrap">' +
            '<div class="tcf-watch-c-meta"><strong>' + esc(c.user_name) + '</strong><span>' + esc(c.created_at) + '</span></div>' +
            '<div class="tcf-watch-c-text">' + esc(c.body) + '</div>' +
            '</div></article>'
        );
    }

    function loadSocial() {
        if (videoId <= 0) return;
        var canLike = !!(user && user.id);
        setLikeUi(0, false, canLike);

        if (loginHint) {
            if (!user || !user.id) {
                loginHint.style.display = 'none';
            } else {
                loginHint.style.display = 'none';
            }
        }
        if (commentForm) {
            commentForm.style.display = user && user.id ? 'flex' : 'none';
        }

        fetch(api + '?action=state&video_id=' + encodeURIComponent(String(videoId)), { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d && d.ok) setLikeUi(d.likes || 0, !!d.user_liked, canLike);
            })
            .catch(function () {});

        fetch(api + '?action=comments&video_id=' + encodeURIComponent(String(videoId)), { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d && d.ok) renderComments(d.comments || []);
            })
            .catch(function () {
                if (commentsList) commentsList.innerHTML = '<p class="tcf-watch-msg err">Impossible de charger les commentaires.</p>';
            });
    }

    function applyWatchFormat(format) {
        var wrap = document.querySelector('.tcf-watch-player-wrap');
        var main = document.querySelector('.tcf-watch-minimal');
        if (wrap) {
            wrap.classList.remove('is-landscape', 'is-portrait', 'is-square');
            wrap.classList.add('is-' + format);
            wrap.setAttribute('data-format', format);
        }
        if (main) {
            main.classList.toggle('is-watch-portrait', format === 'portrait');
            main.classList.toggle('is-watch-landscape', format === 'landscape');
            main.classList.toggle('is-watch-square', format === 'square');
        }
        if (document.body) {
            document.body.classList.remove('tcf-watch-format-landscape', 'tcf-watch-format-portrait', 'tcf-watch-format-square');
            document.body.classList.add('tcf-watch-format-' + format);
        }
    }

    function detectVideoFormat(videoEl) {
        var w = videoEl.videoWidth || 0;
        var h = videoEl.videoHeight || 0;
        if (w < 2 || h < 2) return null;
        var ratio = w / h;
        // Vertical type TikTok / Shorts
        if (ratio < 0.85) return 'portrait';
        // Carré (Instagram-like)
        if (ratio >= 0.85 && ratio <= 1.15) return 'square';
        // Paysage type YouTube
        return 'landscape';
    }

    function sizeWatchWrap(format, videoW, videoH) {
        var wrap = document.querySelector('.tcf-watch-player-wrap');
        if (!wrap || !videoW || !videoH) return;
        var ratio = videoW / videoH;
        var parentW = (wrap.parentElement && wrap.parentElement.clientWidth) || window.innerWidth || 360;
        var maxH;
        var maxW;

        if (format === 'portrait') {
            // Mobile : grand (OK). Desktop / tablette : plus compact pour voir toute la vidéo.
            var vw = window.innerWidth || 360;
            var vh = window.innerHeight || 800;
            if (vw <= 600) {
                maxH = Math.min(vh * 0.62, 560);
                maxW = Math.min(parentW - 16, 320);
            } else if (vw <= 1024) {
                maxH = Math.min(vh * 0.58, 520);
                maxW = Math.min(parentW - 24, 320);
            } else {
                maxH = Math.min(vh * 0.52, 480);
                maxW = Math.min(parentW - 24, 300);
            }
            var h = maxH;
            var w = h * ratio;
            if (w > maxW) {
                w = maxW;
                h = w / ratio;
            }
            wrap.style.width = Math.round(w) + 'px';
            wrap.style.height = Math.round(h) + 'px';
            wrap.style.aspectRatio = 'auto';
            wrap.style.maxHeight = 'none';
            return;
        }

        if (format === 'square') {
            maxW = Math.min(parentW, Math.min(window.innerHeight * 0.8, 640));
            wrap.style.width = Math.round(maxW) + 'px';
            wrap.style.height = Math.round(maxW) + 'px';
            wrap.style.aspectRatio = '1 / 1';
            wrap.style.maxHeight = '';
            return;
        }

        // Paysage YouTube : pleine largeur, hauteur bornée
        maxH = Math.min(window.innerHeight * 0.8, 810);
        maxW = parentW;
        var lw = maxW;
        var lh = lw / ratio;
        if (lh > maxH) {
            lh = maxH;
            lw = lh * ratio;
        }
        wrap.style.width = '100%';
        wrap.style.height = Math.round(lh) + 'px';
        wrap.style.aspectRatio = videoW + ' / ' + videoH;
        wrap.style.maxHeight = maxH + 'px';
    }

    function syncPlayerAspect() {
        if (!player || player.tagName !== 'VIDEO') return;
        var format = detectVideoFormat(player);
        if (!format) return;
        applyWatchFormat(format);
        sizeWatchWrap(format, player.videoWidth, player.videoHeight);
    }

    var resizeTimer = null;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(syncPlayerAspect, 120);
    });

    // Embed YouTube Shorts déjà marqué portrait côté PHP
    var embedPortrait = document.querySelector('.tcf-video-embed-wrap--portrait');
    if (embedPortrait) {
        applyWatchFormat('portrait');
        sizeWatchWrap('portrait', 9, 16);
        embedPortrait.style.width = '100%';
        embedPortrait.style.height = '100%';
        embedPortrait.style.maxWidth = 'none';
        embedPortrait.style.aspectRatio = 'auto';
    } else if (document.querySelector('.tcf-video-embed-wrap--landscape')) {
        applyWatchFormat('landscape');
    }

    if (player && videoId > 0 && player.tagName === 'VIDEO') {
        // Ne plus afficher de message d'erreur technique à l'utilisateur.
        // On journalise seulement pour le debug navigateur.
        player.addEventListener('error', function () {
            try {
                var code = player.error && player.error.code ? player.error.code : '?';
                console.warn('[TCF watch] lecture vidéo impossible (code ' + code + ')');
            } catch (e) {}
        });
        player.addEventListener('loadedmetadata', syncPlayerAspect);
        player.addEventListener('loadeddata', syncPlayerAspect);
        if (player.readyState >= 1) {
            syncPlayerAspect();
        }
        fetch(api, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ action: 'view', video_id: videoId })
        }).catch(function () {});
    }

    if (likeBtn) {
        likeBtn.addEventListener('click', function () {
            if (!user || !user.id || videoId <= 0) {
                window.location.href = loginHref;
                return;
            }
            likeBtn.disabled = true;
            fetch(api, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ action: 'like', video_id: videoId })
            })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (d && d.ok) setLikeUi(d.likes, !!d.user_liked, true);
                })
                .finally(function () {
                    if (user && user.id) likeBtn.disabled = false;
                });
        });
    }

    function submitComment() {
        if (!user || !user.id || videoId <= 0 || !commentForm) return;
        var body = commentBody && commentBody.value ? commentBody.value.trim() : '';
        if (!body) return;
        var sub = commentForm.querySelector('.tcf-watch-composer__submit');
        if (sub) sub.disabled = true;
        if (commentMsg) commentMsg.textContent = '';
        fetch(api, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ action: 'comment', video_id: videoId, body: body })
        })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (commentMsg) {
                    commentMsg.className = 'tcf-watch-msg ' + (d && d.ok ? 'ok' : 'err');
                    commentMsg.textContent = (d && d.message) ? d.message : '';
                }
                if (d && d.ok && commentBody) {
                    commentBody.value = '';
                    loadSocial();
                }
            })
            .catch(function () {
                if (commentMsg) {
                    commentMsg.className = 'tcf-watch-msg err';
                    commentMsg.textContent = 'Erreur réseau.';
                }
            })
            .finally(function () {
                if (sub) sub.disabled = false;
            });
    }

    if (commentForm) {
        commentForm.addEventListener('submit', function (e) {
            e.preventDefault();
            submitComment();
        });
    }

    if (commentCancel && commentBody) {
        commentCancel.addEventListener('click', function () {
            commentBody.value = '';
            if (commentMsg) commentMsg.textContent = '';
        });
    }

    if (commentBody) {
        commentBody.addEventListener('focus', function () {
            commentForm.classList.add('is-focused');
        });
    }

    loadSocial();
})();
