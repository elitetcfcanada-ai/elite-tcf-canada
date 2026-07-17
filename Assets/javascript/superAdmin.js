/**
 * Super admin — navigation sections + gestion vidéos (miniature / URL, durée serveur, lecture au clic).
 */
(function () {
    var adminEndpoint = 'superAdmin.php';
    var playlistsCache = [];

    function $(sel, root) {
        return (root || document).querySelector(sel);
    }

    function $all(sel, root) {
        return [].slice.call((root || document).querySelectorAll(sel));
    }

    function showToast(message, isError) {
        var toast = document.getElementById('notification-toast');
        var text = document.getElementById('notification-text');
        if (!toast || !text) {
            window.alert(message);
            return;
        }
        text.textContent = message;
        toast.style.background = isError ? '#7f1d1d' : '#14532d';
        toast.classList.add('show');
        window.clearTimeout(showToast._t);
        showToast._t = window.        setTimeout(function () {
            toast.classList.remove('show');
        }, 3200);
    }

    window.TCF_ADMIN_TOAST = showToast;

    function showSection(sectionId) {
        $all('.content-section').forEach(function (sec) {
            var on = sec.id === sectionId;
            sec.style.display = on ? 'block' : 'none';
            sec.classList.toggle('active', on);
        });
        $all('.menu-item[data-target]').forEach(function (item) {
            item.classList.toggle('active', item.getAttribute('data-target') === sectionId);
        });
        if (sectionId === 'testimonials') {
            loadTestimonialsAdmin();
        }
        if (sectionId === 'channel-playlists') {
            loadChannelPlaylistsAdmin();
        }
        if (sectionId === 'channel-posts') {
            loadChannelPostsAdmin();
        }
        if (window.TCFAdminApp && typeof window.TCFAdminApp.onSection === 'function') {
            window.TCFAdminApp.onSection(sectionId);
        }
    }

    function loadTestimonialsAdmin() {
        var tbody = document.getElementById('testimonials-admin-tbody');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="5" style="padding:12px;">Chargement…</td></tr>';
        var fd = new FormData();
        fd.append('action', 'get_testimonials');
        fetch(adminEndpoint, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) {
                return r.json();
            })
            .then(function (j) {
                if (!j || !j.success || !j.data || !j.data.length) {
                    tbody.innerHTML =
                        '<tr><td colspan="5" style="padding:12px;color:#64748b;">Aucun témoignage.</td></tr>';
                    return;
                }
                tbody.innerHTML = j.data
                    .map(function (t) {
                        var prev = (t.content || '').length > 140 ? (t.content || '').substring(0, 140) + '…' : t.content || '';
                        return (
                            '<tr><td style="padding:8px;white-space:nowrap;">' +
                            escapeHtml(t.created_at) +
                            '</td><td style="padding:8px;">' +
                            escapeHtml(t.author_name) +
                            '</td><td style="padding:8px;">' +
                            escapeHtml(prev) +
                            '</td><td style="padding:8px;">' +
                            (t.rating ? escapeHtml(String(t.rating)) + '/5' : '—') +
                            '</td><td style="padding:8px;"><button type="button" class="btn btn-outline btn-sm js-del-testimonial" data-id="' +
                            escapeAttr(String(t.id)) +
                            '">Supprimer</button></td></tr>'
                        );
                    })
                    .join('');
            })
            .catch(function () {
                tbody.innerHTML =
                    '<tr><td colspan="5" style="padding:12px;color:#b91c1c;">Erreur de chargement.</td></tr>';
            });
    }

    function deleteTestimonialById(id) {
        var fd = new FormData();
        fd.append('action', 'delete_testimonial');
        fd.append('id', id);
        fetch(adminEndpoint, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) {
                return r.json();
            })
            .then(function (j) {
                if (j && j.success) {
                    showToast(j.message || 'Supprimé');
                    loadTestimonialsAdmin();
                } else {
                    showToast((j && j.message) || 'Erreur', true);
                }
            })
            .catch(function () {
                showToast('Erreur réseau', true);
            });
    }

    function escapeHtml(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function escapeAttr(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;');
    }

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

    function loadPlaylistsCache(cb) {
        var fd = new FormData();
        fd.append('action', 'get_playlists');
        fetch(adminEndpoint, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) {
                return r.json();
            })
            .then(function (j) {
                if (j && j.success && j.data) {
                    playlistsCache = j.data;
                } else {
                    playlistsCache = [];
                }
                if (cb) cb();
            })
            .catch(function () {
                playlistsCache = [];
                if (cb) cb();
            });
    }

    function renderVideoPlaylistCheckboxes(selectedIds) {
        var el = document.getElementById('video-playlist-checkboxes');
        if (!el) return;
        var sel = selectedIds || [];
        sel = sel.map(function (x) {
            return parseInt(String(x), 10);
        });
        if (!playlistsCache || !playlistsCache.length) {
            el.innerHTML =
                '<p style="margin:0;font-size:13px;color:#64748b;">Aucune playlist. Créez-en dans « Playlists chaîne ».</p>';
            return;
        }
        el.innerHTML = playlistsCache
            .map(function (p) {
                var id = parseInt(String(p.id), 10);
                var checked = sel.indexOf(id) >= 0;
                return (
                    '<label style="display:flex;align-items:center;gap:8px;padding:4px 0;cursor:pointer;font-size:13px;">' +
                    '<input type="checkbox" class="js-video-pl" value="' +
                    escapeAttr(String(id)) +
                    '"' +
                    (checked ? ' checked' : '') +
                    '>' +
                    '<span>' +
                    escapeHtml(p.title || 'Sans titre') +
                    '</span></label>'
                );
            })
            .join('');
    }

    function getSelectedVideoPlaylistIds() {
        var el = document.getElementById('video-playlist-checkboxes');
        if (!el) return [];
        var ids = [];
        el.querySelectorAll('.js-video-pl:checked').forEach(function (cb) {
            ids.push(parseInt(cb.value, 10));
        });
        return ids;
    }

    function loadChannelPlaylistsAdmin() {
        var tbody = document.getElementById('channel-playlists-tbody');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="5" style="padding:12px;">Chargement…</td></tr>';
        var fd = new FormData();
        fd.append('action', 'get_playlists');
        fetch(adminEndpoint, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) {
                return r.json();
            })
            .then(function (j) {
                if (!j || !j.success || !j.data || !j.data.length) {
                    tbody.innerHTML =
                        '<tr><td colspan="5" style="padding:12px;color:#64748b;">Aucune playlist.</td></tr>';
                    return;
                }
                tbody.innerHTML = j.data
                    .map(function (p) {
                        return (
                            '<tr><td style="padding:8px;">' +
                            escapeHtml(p.title || '') +
                            '</td><td style="padding:8px;">' +
                            escapeHtml(p.visibility || '') +
                            '</td><td style="padding:8px;">' +
                            escapeHtml(String(p.video_count != null ? p.video_count : 0)) +
                            '</td><td style="padding:8px;white-space:nowrap;">' +
                            escapeHtml(p.created_at || '') +
                            '</td><td style="padding:8px;"><button type="button" class="btn btn-outline btn-sm js-edit-pl" data-id="' +
                            escapeAttr(String(p.id)) +
                            '">Modifier</button> <button type="button" class="btn btn-outline btn-sm js-del-pl" style="border-color:#b91c1c;color:#fecaca;" data-id="' +
                            escapeAttr(String(p.id)) +
                            '">Supprimer</button></td></tr>'
                        );
                    })
                    .join('');
            })
            .catch(function () {
                tbody.innerHTML =
                    '<tr><td colspan="5" style="padding:12px;color:#b91c1c;">Erreur de chargement.</td></tr>';
            });
    }

    function loadChannelPostsAdmin() {
        var tbody = document.getElementById('channel-posts-tbody');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="5" style="padding:12px;">Chargement…</td></tr>';
        var fd = new FormData();
        fd.append('action', 'get_channel_posts');
        fetch(adminEndpoint, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) {
                return r.json();
            })
            .then(function (j) {
                if (!j || !j.success || !j.data || !j.data.length) {
                    tbody.innerHTML =
                        '<tr><td colspan="5" style="padding:12px;color:#64748b;">Aucune publication.</td></tr>';
                    return;
                }
                tbody.innerHTML = j.data
                    .map(function (p) {
                        var excerpt = (p.body || '').length > 120 ? (p.body || '').substring(0, 120) + '…' : p.body || '';
                        return (
                            '<tr><td style="padding:8px;white-space:nowrap;">' +
                            escapeHtml(p.created_at || '') +
                            '</td><td style="padding:8px;"><strong>' +
                            escapeHtml(p.title || '') +
                            '</strong><br><span style="color:#64748b;font-size:12px;">' +
                            escapeHtml(excerpt) +
                            '</span></td><td style="padding:8px;">' +
                            escapeHtml(p.visibility || '') +
                            '</td><td style="padding:8px;">' +
                            escapeHtml(p.video_title || '—') +
                            '</td><td style="padding:8px;"><button type="button" class="btn btn-outline btn-sm js-edit-post" data-id="' +
                            escapeAttr(String(p.id)) +
                            '">Modifier</button> <button type="button" class="btn btn-outline btn-sm js-del-post" style="border-color:#b91c1c;color:#fecaca;" data-id="' +
                            escapeAttr(String(p.id)) +
                            '">Supprimer</button></td></tr>'
                        );
                    })
                    .join('');
            })
            .catch(function () {
                tbody.innerHTML =
                    '<tr><td colspan="5" style="padding:12px;color:#b91c1c;">Erreur de chargement.</td></tr>';
            });
    }

    function loadVideosForSelects(cb) {
        fetchVideosFromServer(function (err, data) {
            if (err || !data) {
                if (cb) cb([]);
                return;
            }
            if (cb) cb(data);
        });
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

    function renderPlaylistVideoCheckboxes(videos, selectedIds) {
        var el = document.getElementById('channel-playlist-video-checkboxes');
        if (!el) return;
        var sel = (selectedIds || []).map(function (x) {
            return parseInt(String(x), 10);
        });
        if (!videos || !videos.length) {
            el.innerHTML =
                '<p style="margin:0;font-size:13px;color:#64748b;">Aucune vidéo. Publiez d’abord des vidéos.</p>';
            return;
        }
        el.innerHTML = videos
            .map(function (v) {
                var id = parseInt(String(v.id), 10);
                var checked = sel.indexOf(id) >= 0;
                return (
                    '<label style="display:flex;align-items:center;gap:8px;padding:4px 0;cursor:pointer;font-size:13px;">' +
                    '<input type="checkbox" class="js-pl-vid" value="' +
                    escapeAttr(String(id)) +
                    '"' +
                    (checked ? ' checked' : '') +
                    '>' +
                    '<span>' +
                    escapeHtml(v.title || 'Vidéo #' + id) +
                    '</span></label>'
                );
            })
            .join('');
    }

    function getSelectedPlaylistVideoIds() {
        var el = document.getElementById('channel-playlist-video-checkboxes');
        if (!el) return [];
        var ids = [];
        el.querySelectorAll('.js-pl-vid:checked').forEach(function (cb) {
            ids.push(parseInt(cb.value, 10));
        });
        return ids;
    }

    function resetChannelPlaylistForm() {
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
    }

    function resetChannelPostForm() {
        var f = document.getElementById('channel-post-form');
        if (f) f.style.display = 'none';
        var hid = document.getElementById('channel-post-edit-id');
        if (hid) hid.value = '';
        var t = document.getElementById('channel-post-title');
        if (t) t.value = '';
        var b = document.getElementById('channel-post-body');
        if (b) b.value = '';
        var vis = document.getElementById('channel-post-visibility');
        if (vis) vis.value = 'public';
        var vid = document.getElementById('channel-post-video-id');
        if (vid) vid.value = '';
    }

    function initChannelPlaylistForm() {
        var addBtn = document.getElementById('channel-playlist-add-btn');
        var cancelBtn = document.getElementById('channel-playlist-cancel-btn');
        var form = document.getElementById('channel-playlist-form');
        if (addBtn) {
            addBtn.addEventListener('click', function () {
                resetChannelPlaylistForm();
                if (form) form.style.display = 'block';
                loadVideosForSelects(function (videos) {
                    renderPlaylistVideoCheckboxes(videos, []);
                });
            });
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', resetChannelPlaylistForm);
        }
        if (form) {
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
                fd.append('video_ids', JSON.stringify(getSelectedPlaylistVideoIds()));
                fetch(adminEndpoint, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) {
                        return r.json();
                    })
                    .then(function (j) {
                        if (j && j.success) {
                            showToast(j.message || 'Enregistré');
                            resetChannelPlaylistForm();
                            loadPlaylistsCache(function () {
                                renderVideoPlaylistCheckboxes(getSelectedVideoPlaylistIds());
                            });
                            loadChannelPlaylistsAdmin();
                        } else {
                            showToast((j && j.message) || 'Erreur', true);
                        }
                    })
                    .catch(function () {
                        showToast('Erreur réseau', true);
                    });
            });
        }
        document.body.addEventListener('click', function (e) {
            var btn = e.target.closest && e.target.closest('.js-del-pl');
            if (!btn) return;
            var id = btn.getAttribute('data-id');
            if (!id || !window.confirm('Supprimer cette playlist ?')) return;
            var fd = new FormData();
            fd.append('action', 'delete_playlist');
            fd.append('id', id);
            fetch(adminEndpoint, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) {
                    return r.json();
                })
                .then(function (j) {
                    if (j && j.success) {
                        showToast(j.message || 'Supprimé');
                        loadPlaylistsCache(function () {
                            renderVideoPlaylistCheckboxes(getSelectedVideoPlaylistIds());
                        });
                        loadChannelPlaylistsAdmin();
                    } else {
                        showToast((j && j.message) || 'Erreur', true);
                    }
                })
                .catch(function () {
                    showToast('Erreur réseau', true);
                });
        });
        document.body.addEventListener('click', function (e) {
            var btn = e.target.closest && e.target.closest('.js-edit-pl');
            if (!btn) return;
            var id = parseInt(btn.getAttribute('data-id'), 10);
            if (!id) return;
            var fd = new FormData();
            fd.append('action', 'get_playlists');
            fetch(adminEndpoint, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) {
                    return r.json();
                })
                .then(function (j) {
                    if (!j || !j.success || !j.data) return;
                    var p = j.data.filter(function (x) {
                        return parseInt(String(x.id), 10) === id;
                    })[0];
                    if (!p) return;
                    var hid = document.getElementById('channel-playlist-edit-id');
                    var t = document.getElementById('channel-playlist-title');
                    var d = document.getElementById('channel-playlist-description');
                    var vis = document.getElementById('channel-playlist-visibility');
                    if (hid) hid.value = String(p.id);
                    if (t) t.value = p.title || '';
                    if (d) d.value = p.description || '';
                    if (vis) vis.value = p.visibility || 'public';
                    if (form) form.style.display = 'block';
                    loadVideosForSelects(function (videos) {
                        renderPlaylistVideoCheckboxes(videos, p.video_ids || []);
                    });
                });
        });
    }

    function initChannelPostForm() {
        var addBtn = document.getElementById('channel-post-add-btn');
        var cancelBtn = document.getElementById('channel-post-cancel-btn');
        var form = document.getElementById('channel-post-form');
        if (addBtn) {
            addBtn.addEventListener('click', function () {
                resetChannelPostForm();
                if (form) form.style.display = 'block';
                loadVideosForSelects(function (videos) {
                    fillChannelPostVideoSelect(videos);
                });
            });
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', resetChannelPostForm);
        }
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var editId = document.getElementById('channel-post-edit-id');
                var title = document.getElementById('channel-post-title');
                var body = document.getElementById('channel-post-body');
                var vis = document.getElementById('channel-post-visibility');
                var vidSel = document.getElementById('channel-post-video-id');
                var fd = new FormData();
                fd.append('action', 'save_channel_post');
                if (editId && editId.value) fd.append('id', editId.value);
                fd.append('title', title ? title.value.trim() : '');
                fd.append('body', body ? body.value.trim() : '');
                fd.append('visibility', vis ? vis.value : 'public');
                fd.append('video_id', vidSel && vidSel.value ? vidSel.value : '');
                fetch(adminEndpoint, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) {
                        return r.json();
                    })
                    .then(function (j) {
                        if (j && j.success) {
                            showToast(j.message || 'Enregistré');
                            resetChannelPostForm();
                            loadChannelPostsAdmin();
                        } else {
                            showToast((j && j.message) || 'Erreur', true);
                        }
                    })
                    .catch(function () {
                        showToast('Erreur réseau', true);
                    });
            });
        }
        document.body.addEventListener('click', function (e) {
            var btn = e.target.closest && e.target.closest('.js-del-post');
            if (!btn) return;
            var id = btn.getAttribute('data-id');
            if (!id || !window.confirm('Supprimer cette publication ?')) return;
            var fd = new FormData();
            fd.append('action', 'delete_channel_post');
            fd.append('id', id);
            fetch(adminEndpoint, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) {
                    return r.json();
                })
                .then(function (j) {
                    if (j && j.success) {
                        showToast(j.message || 'Supprimé');
                        loadChannelPostsAdmin();
                    } else {
                        showToast((j && j.message) || 'Erreur', true);
                    }
                })
                .catch(function () {
                    showToast('Erreur réseau', true);
                });
        });
        document.body.addEventListener('click', function (e) {
            var btn = e.target.closest && e.target.closest('.js-edit-post');
            if (!btn) return;
            var id = parseInt(btn.getAttribute('data-id'), 10);
            if (!id) return;
            var fd = new FormData();
            fd.append('action', 'get_channel_posts');
            fetch(adminEndpoint, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) {
                    return r.json();
                })
                .then(function (j) {
                    if (!j || !j.success || !j.data) return;
                    var p = j.data.filter(function (x) {
                        return parseInt(String(x.id), 10) === id;
                    })[0];
                    if (!p) return;
                    loadVideosForSelects(function (videos) {
                        fillChannelPostVideoSelect(videos);
                        var hid = document.getElementById('channel-post-edit-id');
                        var t = document.getElementById('channel-post-title');
                        var b = document.getElementById('channel-post-body');
                        var vis = document.getElementById('channel-post-visibility');
                        var vidSel = document.getElementById('channel-post-video-id');
                        if (hid) hid.value = String(p.id);
                        if (t) t.value = p.title || '';
                        if (b) b.value = p.body || '';
                        if (vis) vis.value = p.visibility || 'public';
                        if (vidSel) {
                            vidSel.value = p.video_id ? String(p.video_id) : '';
                        }
                        if (form) form.style.display = 'block';
                    });
                });
        });
    }

    function thumbHref(v) {
        if (v.thumbnail_href) return v.thumbnail_href;
        return '';
    }

    function videoHref(v) {
        if (v.video_href) return v.video_href;
        return '';
    }

    function renderVideosGrid(list) {
        var grid = document.getElementById('videos-grid');
        if (!grid) return;

        if (!list || !list.length) {
            grid.innerHTML = '<p class="tcf-admin-video-empty">Aucune vidéo publiée.</p>';
            return;
        }

        grid.innerHTML = list
            .map(function (v) {
                var th = thumbHref(v);
                var vurl = videoHref(v);
                var durRaw = (v.duration || '').toString().trim();
                var dur = tcfAdminDurationMeaningful(durRaw) ? escapeHtml(durRaw) : '';
                var title = escapeHtml(v.title || '');
                var vis = escapeHtml(v.visibility || '');
                var desc = escapeHtml(v.description || '');
                var visSlug = (v.visibility || 'public').toLowerCase().replace(/\s+/g, '-');
                var img = th
                    ? '<img src="' + escapeHtml(th) + '" alt="" loading="lazy">'
                    : '<div class="tcf-admin-video-thumb-placeholder"></div>';
                return (
                    '<article class="tcf-admin-video-card" data-id="' +
                    escapeHtml(String(v.id)) +
                    '">' +
                    '<button type="button" class="tcf-admin-video-thumb js-admin-play-video" data-video-src="' +
                    escapeHtml(vurl) +
                    '" data-video-title="' +
                    title +
                    '" title="Lire la vidéo">' +
                    img +
                    (dur ? '<span class="tcf-duration">' + dur + '</span>' : '') +
                    '<span class="tcf-play-ic"><i class="bx bx-play-circle"></i></span>' +
                    '</button>' +
                    '<div class="tcf-admin-video-body">' +
                    '<h4>' +
                    title +
                    '</h4>' +
                    '<p class="tcf-admin-video-meta">' +
                    '<span class="sa-badge sa-badge--video-' +
                    visSlug +
                    '">' +
                    vis +
                    '</span> · ' +
                    (v.views != null ? escapeHtml(String(v.views)) : '0') +
                    ' vues</p>' +
                    (desc ? '<p class="tcf-admin-video-desc">' + desc + '</p>' : '') +
                    '<div class="tcf-admin-video-actions">' +
                    '<button type="button" class="btn btn-outline btn-sm js-edit-video">Modifier</button>' +
                    '<button type="button" class="btn btn-outline btn-sm btn-danger-outline js-delete-video">Supprimer</button>' +
                    '</div></div></article>'
                );
            })
            .join('');

        grid.querySelectorAll('.js-admin-play-video').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var url = btn.getAttribute('data-video-src');
                var t = btn.getAttribute('data-video-title') || '';
                openVideoModal(url, t);
            });
        });

        grid.querySelectorAll('.js-edit-video').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var card = btn.closest('.tcf-admin-video-card');
                var id = card && card.getAttribute('data-id');
                if (id) editVideoById(parseInt(id, 10), list);
            });
        });

        grid.querySelectorAll('.js-delete-video').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var card = btn.closest('.tcf-admin-video-card');
                var id = card && card.getAttribute('data-id');
                if (!id || !window.confirm('Supprimer cette vidéo ?')) return;
                deleteVideo(id);
            });
        });
    }

    function getModalEls() {
        return {
            wrap: document.getElementById('admin-video-play-modal'),
            player: document.getElementById('admin-video-play-player'),
            title: document.getElementById('admin-video-play-title')
        };
    }

    function closeVideoModal() {
        var m = getModalEls();
        if (!m.wrap || !m.player) return;
        m.player.pause();
        m.player.removeAttribute('src');
        m.player.load();
        m.wrap.style.display = 'none';
        m.wrap.setAttribute('aria-hidden', 'true');
    }

    function openVideoModal(url, title) {
        var m = getModalEls();
        if (!m.wrap || !m.player || !url) return;
        if (m.title) m.title.textContent = title || '';
        m.player.preload = 'auto';
        m.player.src = url;
        m.wrap.style.display = 'flex';
        m.wrap.setAttribute('aria-hidden', 'false');
        var p = m.player.play();
        if (p && typeof p.catch === 'function') {
            p.catch(function () {});
        }
    }

    function fetchVideosFromServer(cb) {
        var body = new URLSearchParams();
        body.set('action', 'get_videos');
        fetch(adminEndpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: body.toString(),
            credentials: 'same-origin'
        })
            .then(function (r) {
                return r.json();
            })
            .then(function (j) {
                if (j && j.success && j.data) {
                    cb(null, j.data);
                } else {
                    cb(j && j.message ? j.message : 'Erreur chargement vidéos', null);
                }
            })
            .catch(function () {
                cb('Réseau indisponible', null);
            });
    }

    function editVideoById(id, list) {
        var v = list.filter(function (x) {
            return String(x.id) === String(id);
        })[0];
        if (!v) return;

        var form = document.getElementById('video-form');
        var editId = document.getElementById('video-edit-id');
        var title = document.getElementById('video-title');
        var desc = document.getElementById('video-description');
        var vis = document.getElementById('video-visibility');
        if (!form || !editId || !title || !desc || !vis) return;

        editId.value = String(v.id);
        title.value = v.title || '';
        desc.value = v.description || '';
        vis.value = v.visibility || 'public';

        var thPrev = document.getElementById('thumbnail-preview');
        var thImg = document.getElementById('thumbnail-preview-img');
        var thLabel = document.getElementById('thumbnail-label');
        if (thPrev && thImg) {
            var thu = thumbHref(v);
            if (thu) {
                thImg.src = thu;
                thPrev.classList.add('is-visible');
            } else {
                thPrev.classList.remove('is-visible');
            }
        }
        if (thLabel) thLabel.textContent = 'Miniature actuelle (optionnel : remplacer)';

        loadPlaylistsCache(function () {
            renderVideoPlaylistCheckboxes(v.playlist_ids || []);
        });

        var vidPrev = document.getElementById('video-preview');
        var vidPlayer = document.getElementById('video-preview-player');
        var vidLabel = document.getElementById('video-file-label');
        if (vidPrev && vidPlayer) {
            vidPlayer.removeAttribute('src');
            vidPlayer.load();
            vidPrev.classList.remove('is-visible');
        }
        if (vidLabel) vidLabel.textContent = 'Vidéo actuelle (optionnel : remplacer)';

        var thInput = document.getElementById('thumbnail-file');
        var vidInput = document.getElementById('video-file');
        if (thInput) thInput.value = '';
        if (vidInput) vidInput.value = '';

        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function resetVideoForm() {
        var form = document.getElementById('video-form');
        if (form) {
            form.reset();
            form.style.display = 'none';
        }
        var editId = document.getElementById('video-edit-id');
        if (editId) editId.value = '';
        var thPrev = document.getElementById('thumbnail-preview');
        var vidPrev = document.getElementById('video-preview');
        var vidPlayer = document.getElementById('video-preview-player');
        if (thPrev) thPrev.classList.remove('is-visible');
        if (vidPrev) vidPrev.classList.remove('is-visible');
        if (vidPlayer) {
            vidPlayer.removeAttribute('src');
            vidPlayer.load();
        }
        var thLabel = document.getElementById('thumbnail-label');
        var vidLabel = document.getElementById('video-file-label');
        if (thLabel) thLabel.textContent = 'Sélectionner une miniature';
        if (vidLabel) vidLabel.textContent = 'Sélectionner une vidéo';
        loadPlaylistsCache(function () {
            renderVideoPlaylistCheckboxes([]);
        });
    }

    function deleteVideo(id) {
        var fd = new FormData();
        fd.append('action', 'delete_video');
        fd.append('id', id);
        fetch(adminEndpoint, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) {
                return r.json();
            })
            .then(function (j) {
                if (j && j.success) {
                    showToast(j.message || 'Vidéo supprimée');
                    fetchVideosFromServer(function (err, data) {
                        if (!err && data) renderVideosGrid(data);
                    });
                } else {
                    showToast((j && j.message) || 'Erreur', true);
                }
            })
            .catch(function () {
                showToast('Erreur réseau', true);
            });
    }

    function initVideoForm() {
        var addBtn = document.getElementById('add-video-btn');
        var cancelBtn = document.getElementById('cancel-video-btn');
        var form = document.getElementById('video-form');
        var thInput = document.getElementById('thumbnail-file');
        var vidInput = document.getElementById('video-file');
        var thPrev = document.getElementById('thumbnail-preview');
        var thImg = document.getElementById('thumbnail-preview-img');
        var vidPrev = document.getElementById('video-preview');
        var vidPlayer = document.getElementById('video-preview-player');

        if (addBtn) {
            addBtn.addEventListener('click', function () {
                resetVideoForm();
                loadPlaylistsCache(function () {
                    renderVideoPlaylistCheckboxes([]);
                });
                if (form) {
                    form.style.display = 'block';
                    form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', resetVideoForm);
        }

        if (thInput && thPrev && thImg) {
            thInput.addEventListener('change', function () {
                var f = thInput.files && thInput.files[0];
                if (!f) {
                    thPrev.classList.remove('is-visible');
                    return;
                }
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
                    return;
                }
                try {
                    if (vidPlayer.src && vidPlayer.src.indexOf('blob:') === 0) {
                        URL.revokeObjectURL(vidPlayer.src);
                    }
                } catch (e) {}
                vidPlayer.src = URL.createObjectURL(f);
                vidPrev.classList.add('is-visible');
            });
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var editIdEl = document.getElementById('video-edit-id');
                var isEdit = editIdEl && editIdEl.value;

                var fd = new FormData(form);
                fd.append('action', isEdit ? 'update_video' : 'add_video');
                if (isEdit) fd.set('id', editIdEl.value);
                fd.append('playlist_ids', JSON.stringify(getSelectedVideoPlaylistIds()));

                if (!isEdit) {
                    if (!fd.get('thumbnail') || !fd.get('thumbnail').size) {
                        showToast('Miniature requise', true);
                        return;
                    }
                    if (!fd.get('video') || !fd.get('video').size) {
                        showToast('Fichier vidéo requis', true);
                        return;
                    }
                }

                fetch(adminEndpoint, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function (r) {
                        return r.json();
                    })
                    .then(function (j) {
                        if (j && j.success) {
                            showToast(j.message || 'Enregistré');
                            resetVideoForm();
                            fetchVideosFromServer(function (err, data) {
                                if (!err && data) renderVideosGrid(data);
                            });
                        } else {
                            showToast((j && j.message) || 'Erreur', true);
                        }
                    })
                    .catch(function () {
                        showToast('Erreur réseau', true);
                    });
            });
        }

        var modal = document.getElementById('admin-video-play-modal');
        if (modal) {
            modal.querySelectorAll('[data-close-video-modal]').forEach(function (el) {
                el.addEventListener('click', closeVideoModal);
            });
        }
        document.addEventListener('keydown', function (ev) {
            if (ev.key === 'Escape') closeVideoModal();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        $all('.menu-item[data-target]').forEach(function (el) {
            el.addEventListener('click', function () {
                var t = el.getAttribute('data-target');
                if (t) showSection(t);
            });
        });
        $all('.sub-item[data-target]').forEach(function (el) {
            el.addEventListener('click', function () {
                var t = el.getAttribute('data-target');
                if (!t) return;
                if (t.indexOf('topics-') === 0) {
                    if (window.TCFAdminApp && typeof window.TCFAdminApp.onSection === 'function') {
                        window.TCFAdminApp.onSection(t);
                    }
                    showSection('topics-section');
                } else {
                    showSection(t);
                }
            });
        });

        var initial = typeof videosFromDB !== 'undefined' ? videosFromDB : [];
        renderVideosGrid(initial);
        initVideoForm();
        loadPlaylistsCache(function () {
            renderVideoPlaylistCheckboxes([]);
        });
        initChannelPlaylistForm();
        initChannelPostForm();

        document.body.addEventListener('click', function (e) {
            var btn = e.target.closest && e.target.closest('.js-del-testimonial');
            if (!btn) return;
            var id = btn.getAttribute('data-id');
            if (!id || !window.confirm('Supprimer ce témoignage ?')) return;
            deleteTestimonialById(id);
        });
    });
})();
