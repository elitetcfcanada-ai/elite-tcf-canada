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

    if (player && videoId > 0 && player.tagName === 'VIDEO') {
        var watchErrShown = false;
        function showWatchPlayerError() {
            if (watchErrShown) return;
            var mediaErr = player.error;
            // 1 = MEDIA_ERR_ABORTED (ignorer)
            if (mediaErr && mediaErr.code === 1) return;
            watchErrShown = true;
            player.style.display = 'none';
            var err = document.getElementById('tcf-watch-player-error')
                || document.getElementById(player.id + '-error');
            if (err) err.hidden = false;
        }
        player.addEventListener('error', showWatchPlayerError);
        // Si <source> échoue, l’événement peut être sur le source ; remonter aussi
        var srcEl = player.querySelector('source');
        if (srcEl) {
            srcEl.addEventListener('error', showWatchPlayerError);
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
