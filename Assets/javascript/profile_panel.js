(function () {
    'use strict';

    function getProfileApiUrl() {
        var el = document.getElementById('tcf-profile-api-config');
        return el ? el.getAttribute('data-api-url') || '' : '';
    }

    function getUserEmail() {
        var el = document.getElementById('tcf-profile-api-config');
        return el ? el.getAttribute('data-user-email') || '' : '';
    }

    function maskEmail(email) {
        if (!email || email.indexOf('@') < 0) return email || '';
        var parts = email.split('@');
        var u = parts[0];
        var d = parts[1];
        var vis = u.length <= 2 ? u[0] + '*' : u[0] + '••••' + u.slice(-1);
        return vis + '@' + d;
    }

    function showLegacyModal(el) {
        if (!el) return;
        el.style.display = 'flex';
        el.classList.add('is-open');
    }

    function hideLegacyModal(el) {
        if (!el) return;
        el.style.display = 'none';
        el.classList.remove('is-open');
    }

    function openPremiumModal(el) {
        if (!el) return;
        el.classList.add('is-open');
        el.style.display = 'flex';
        el.setAttribute('aria-hidden', 'false');
    }

    function closePremiumModal(el) {
        if (!el) return;
        el.classList.remove('is-open');
        el.style.display = 'none';
        el.setAttribute('aria-hidden', 'true');
    }

    async function profileApi(action, data) {
        var url = getProfileApiUrl();
        if (!url) throw new Error('API non configurée.');
        var res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(Object.assign({ action: action }, data || {})),
        });
        var json = await res.json().catch(function () {
            return { ok: false, message: 'Réponse invalide du serveur.' };
        });
        return json;
    }

    function setButtonLoading(btn, loading) {
        if (!btn) return;
        var sp = btn.querySelector('.tcf-btn__spinner');
        var lab = btn.querySelector('.tcf-btn__label');
        btn.disabled = !!loading;
        if (sp) sp.hidden = !loading;
        if (lab) lab.style.opacity = loading ? '0.7' : '';
    }

    function showPwdAlert(el, type, message) {
        if (!el) return;
        el.hidden = !message;
        el.textContent = message || '';
        el.className = 'pwd-premium__alert';
        if (type === 'error') el.classList.add('pwd-premium__alert--error');
        else if (type === 'success') el.classList.add('pwd-premium__alert--success');
        else if (type === 'warn') el.classList.add('pwd-premium__alert--warn');
    }

    function pwdSetStep(modal, step) {
        var panels = [
            document.getElementById('pwdPremiumPanel1'),
            document.getElementById('pwdPremiumPanel2'),
            document.getElementById('pwdPremiumPanel3'),
        ];
        panels.forEach(function (p, i) {
            if (!p) return;
            p.classList.toggle('is-visible', i + 1 === step);
        });
        modal.querySelectorAll('[data-pwd-step-indicator]').forEach(function (dot) {
            var n = parseInt(dot.getAttribute('data-pwd-step-indicator'), 10);
            dot.classList.toggle('is-active', n === step);
            dot.classList.toggle('is-done', n < step);
        });
    }

    function otpGetCode() {
        var inputs = document.querySelectorAll('#pwdOtpRow .pwd-otp__box');
        var s = '';
        inputs.forEach(function (inp) {
            s += (inp.value || '').replace(/\D/g, '');
        });
        return s;
    }

    function otpClear() {
        document.querySelectorAll('#pwdOtpRow .pwd-otp__box').forEach(function (inp) {
            inp.value = '';
        });
        var first = document.querySelector('#pwdOtpRow .pwd-otp__box');
        if (first) first.focus();
    }

    function setupOtpInputs() {
        var row = document.getElementById('pwdOtpRow');
        if (!row) return;
        var inputs = row.querySelectorAll('.pwd-otp__box');

        row.addEventListener('paste', function (e) {
            e.preventDefault();
            var t = (e.clipboardData || window.clipboardData).getData('text') || '';
            var digits = t.replace(/\D/g, '').slice(0, 6);
            inputs.forEach(function (inp, i) {
                inp.value = digits[i] || '';
            });
            if (digits.length >= 6 && inputs[5]) inputs[5].focus();
            else if (digits.length > 0 && inputs[digits.length]) inputs[digits.length].focus();
        });

        inputs.forEach(function (inp, idx) {
            inp.addEventListener('input', function () {
                var v = inp.value.replace(/\D/g, '').slice(-1);
                inp.value = v;
                if (v && idx < inputs.length - 1) inputs[idx + 1].focus();
            });
            inp.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !inp.value && idx > 0) inputs[idx - 1].focus();
            });
        });
    }

    function setupPasswordWizard() {
        var modal = document.getElementById('passwordModal');
        if (!modal) return;

        setupOtpInputs();

        var alertEl = document.getElementById('pwdPremiumAlert');
        var emailMask = document.getElementById('pwdEmailMask');
        var btnSend = document.getElementById('pwdBtnSendCode');
        var btnResend = document.getElementById('pwdBtnResend');
        var btnVerify = document.getElementById('pwdBtnVerify');
        var btnFinal = document.getElementById('pwdBtnFinalSave');
        var btnBack = document.getElementById('pwdBackToRequest');
        var steps = modal.querySelector('.pwd-premium__steps');
        var panel1 = document.getElementById('pwdPremiumPanel1');
        var panel2 = document.getElementById('pwdPremiumPanel2');
        var panel3 = document.getElementById('pwdPremiumPanel3');

        function resetWizard() {
            showPwdAlert(alertEl, '', '');
            otpClear();
            var cp = document.getElementById('current_password');
            if (cp) cp.value = '';
            document.getElementById('new_password').value = '';
            document.getElementById('confirm_password').value = '';
            // OTP désactivé : ouverture directe sur le formulaire final
            pwdSetStep(modal, 3);
            if (btnResend) btnResend.hidden = true;
        }

        function forceDirectPasswordMode() {
            if (steps) steps.style.display = 'none';
            if (panel1) panel1.style.display = 'none';
            if (panel2) panel2.style.display = 'none';
            if (panel3) panel3.style.display = '';
        }

        document.getElementById('changePasswordBtn')?.addEventListener('click', function () {
            if (emailMask) emailMask.textContent = maskEmail(getUserEmail());
            resetWizard();
            forceDirectPasswordMode();
            openPremiumModal(modal);
        });

        document.getElementById('closePasswordModal')?.addEventListener('click', function () {
            closePremiumModal(modal);
        });
        document.getElementById('cancelPasswordEdit')?.addEventListener('click', function () {
            closePremiumModal(modal);
        });
        modal.querySelector('[data-close-password="1"]')?.addEventListener('click', function () {
            closePremiumModal(modal);
        });

        btnSend?.addEventListener('click', async function () {
            showPwdAlert(alertEl, '', '');
            setButtonLoading(btnSend, true);
            try {
                var data = await profileApi('password_send_code', {});
                if (data.ok) {
                    showPwdAlert(alertEl, data.mail_ok === false ? 'warn' : 'success', data.message);
                    pwdSetStep(modal, 2);
                    if (btnResend) btnResend.hidden = false;
                    otpClear();
                } else {
                    showPwdAlert(alertEl, 'error', data.message || 'Erreur.');
                }
            } catch (e) {
                showPwdAlert(alertEl, 'error', 'Connexion impossible. Réessayez.');
            }
            setButtonLoading(btnSend, false);
        });

        btnResend?.addEventListener('click', function () {
            btnSend.click();
        });

        btnVerify?.addEventListener('click', async function () {
            var code = otpGetCode();
            if (code.length !== 6) {
                showPwdAlert(alertEl, 'error', 'Entrez les 6 chiffres du code.');
                return;
            }
            showPwdAlert(alertEl, '', '');
            setButtonLoading(btnVerify, true);
            try {
                var data = await profileApi('password_verify_code', { code: code });
                if (data.ok) {
                    showPwdAlert(alertEl, 'success', data.message);
                    pwdSetStep(modal, 3);
                } else {
                    showPwdAlert(alertEl, 'error', data.message || 'Code invalide.');
                }
            } catch (e) {
                showPwdAlert(alertEl, 'error', 'Erreur réseau.');
            }
            setButtonLoading(btnVerify, false);
        });

        btnFinal?.addEventListener('click', async function () {
            var p0 = document.getElementById('current_password').value;
            var p1 = document.getElementById('new_password').value;
            var p2 = document.getElementById('confirm_password').value;
            showPwdAlert(alertEl, '', '');
            setButtonLoading(btnFinal, true);
            try {
                var data = await profileApi('password_update', { current_password: p0, new_password: p1, confirm_password: p2 });
                if (data.ok) {
                    showPwdAlert(alertEl, 'success', data.message);
                    setTimeout(function () {
                        closePremiumModal(modal);
                        resetWizard();
                    }, 1600);
                } else {
                    showPwdAlert(alertEl, 'error', data.message || 'Erreur.');
                    if (data.reset_wizard) {
                        pwdSetStep(modal, 1);
                    }
                }
            } catch (e) {
                showPwdAlert(alertEl, 'error', 'Erreur réseau.');
            }
            setButtonLoading(btnFinal, false);
        });

        btnBack?.addEventListener('click', function () {
            pwdSetStep(modal, 3);
            showPwdAlert(alertEl, '', '');
        });
    }

    function setupUsernameModal() {
        var modal = document.getElementById('usernameModal');
        if (!modal) return;

        var errEl = document.getElementById('usernameModalError');
        var inp = document.getElementById('usernameInput');
        var saveBtn = document.getElementById('saveUsernameBtn');

        function open() {
            errEl.hidden = true;
            errEl.textContent = '';
            openPremiumModal(modal);
        }

        function close() {
            closePremiumModal(modal);
        }

        document.getElementById('usernameValue')?.addEventListener('click', function () {
            var rowName = this.querySelector('.profile-v2-menu__mid strong');
            if (rowName && inp) inp.value = rowName.textContent.trim();
            open();
        });

        document.getElementById('closeUsernameModal')?.addEventListener('click', close);
        document.getElementById('cancelUsernameEdit')?.addEventListener('click', close);
        modal.querySelector('[data-close-username="1"]')?.addEventListener('click', close);

        saveBtn?.addEventListener('click', async function () {
            var name = (inp.value || '').trim();
            errEl.hidden = true;
            setButtonLoading(saveBtn, true);
            try {
                var data = await profileApi('update_display_name', { username: name });
                if (data.ok) {
                    var pn = document.getElementById('profileName');
                    if (pn) pn.textContent = data.name;
                    var row = document.querySelector('#usernameValue .profile-v2-menu__mid strong');
                    if (row) row.textContent = data.name;
                    close();
                } else {
                    errEl.textContent = data.message || 'Erreur.';
                    errEl.hidden = false;
                }
            } catch (e) {
                errEl.textContent = 'Connexion impossible.';
                errEl.hidden = false;
            }
            setButtonLoading(saveBtn, false);
        });
    }

    function setupPasswordToggles() {
        function bind(btnId, inputId) {
            var btn = document.getElementById(btnId);
            var input = document.getElementById(inputId);
            if (!btn || !input) return;
            btn.addEventListener('click', function () {
                var icon = btn.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    if (icon) {
                        icon.classList.remove('bx-hide');
                        icon.classList.add('bx-show');
                    }
                } else {
                    input.type = 'password';
                    if (icon) {
                        icon.classList.remove('bx-show');
                        icon.classList.add('bx-hide');
                    }
                }
            });
        }
        bind('toggleNewPassword', 'new_password');
        bind('toggleConfirmPassword', 'confirm_password');
        bind('toggleCurrentPassword', 'current_password');
    }

    var cropper = null;

    function destroyCropper() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    }

    function setupAvatarModal() {
        var modal = document.getElementById('avatarModal');
        var img = document.getElementById('avatarCropImage');
        var fileInput = document.getElementById('avatarUpload');
        var okBtn = document.getElementById('avatarCropOk');
        var saveBtn = document.getElementById('avatarSaveBtn');
        var hiddenData = document.getElementById('avatar_data_url');
        var form = document.getElementById('avatarForm');
        var previewBox = document.getElementById('avatarPreviewBox');
        var previewImg = document.getElementById('avatarPreviewImage');

        if (!modal || !img || !fileInput || !okBtn || !saveBtn || !hiddenData || !form) return;

        function resetAvatarForm() {
            destroyCropper();
            img.src = '';
            img.style.display = 'none';
            hiddenData.value = '';
            fileInput.value = '';
            okBtn.disabled = true;
            saveBtn.disabled = true;
            if (previewImg) previewImg.src = '';
            if (previewBox) previewBox.hidden = true;
        }

        document.getElementById('editAvatarBtn')?.addEventListener('click', function () {
            resetAvatarForm();
            showLegacyModal(modal);
        });

        document.getElementById('closeAvatarModal')?.addEventListener('click', function () {
            hideLegacyModal(modal);
            resetAvatarForm();
        });
        document.getElementById('cancelAvatarEdit')?.addEventListener('click', function () {
            hideLegacyModal(modal);
            resetAvatarForm();
        });

        document.getElementById('uploadAvatarBtn')?.addEventListener('click', function () {
            fileInput.click();
        });

        fileInput.addEventListener('change', function (e) {
            var f = e.target.files && e.target.files[0];
            if (!f) return;
            if (!/^image\/(jpeg|png|webp)$/i.test(f.type)) {
                alert('Formats acceptés : JPG, PNG, WebP.');
                return;
            }
            var reader = new FileReader();
            reader.onload = function (ev) {
                destroyCropper();
                img.src = ev.target.result;
                img.style.display = 'block';
                okBtn.disabled = false;
                saveBtn.disabled = true;
                hiddenData.value = '';
                if (previewImg) previewImg.src = '';
                if (previewBox) previewBox.hidden = true;
                if (typeof Cropper !== 'undefined') {
                    cropper = new Cropper(img, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 0.92,
                        background: false,
                    });
                }
            };
            reader.readAsDataURL(f);
        });

        okBtn.addEventListener('click', function () {
            if (!cropper) {
                alert('Choisissez une image puis validez le cadrage.');
                return;
            }
            var canvas = cropper.getCroppedCanvas({ width: 400, height: 400, imageSmoothingQuality: 'high' });
            if (!canvas) return;
            hiddenData.value = canvas.toDataURL('image/jpeg', 0.9);
            if (previewImg) previewImg.src = hiddenData.value;
            if (previewBox) previewBox.hidden = false;
            saveBtn.disabled = false;
            okBtn.disabled = true;
            destroyCropper();
            img.style.display = 'none';
        });

        form.addEventListener('submit', function (e) {
            if (!hiddenData.value.trim()) {
                e.preventDefault();
                alert('Validez le recadrage avec OK avant d’enregistrer.');
            }
        });
    }

    function buildActivityCalendarGrid(year, month, dateList, joinDate, todayStr) {
        var dateSet = {};
        (dateList || []).forEach(function (d) {
            dateSet[d] = true;
        });
        var first = new Date(year, month - 1, 1);
        var dim = new Date(year, month, 0).getDate();
        var dow = first.getDay();
        var pad = (dow + 6) % 7;
        var html = '';
        var i;
        var d;
        var ds;
        for (i = 0; i < pad; i++) {
            html += '<span class="profile-cal__cell profile-cal__cell--pad" aria-hidden="true"></span>';
        }
        for (d = 1; d <= dim; d++) {
            ds =
                year +
                '-' +
                String(month).padStart(2, '0') +
                '-' +
                String(d).padStart(2, '0');
            var cls = 'profile-cal__cell profile-cal__day';
            if (ds > todayStr) cls += ' profile-cal__day--future';
            else if (joinDate && ds < joinDate) cls += ' profile-cal__day--na';
            else if (dateSet[ds]) cls += ' profile-cal__day--present';
            else cls += ' profile-cal__day--absent';
            html += '<span class="' + cls + '" title="' + ds + '">' + d + '</span>';
        }
        return html;
    }

    function setupActivityCalendar() {
        var root = document.getElementById('profileActivityCalendar');
        var grid = document.getElementById('profileCalGrid');
        var titleEl = document.getElementById('profileCalTitle');
        var prev = document.getElementById('profileCalPrev');
        var next = document.getElementById('profileCalNext');
        if (!root || !grid || !titleEl) return;

        var joinDate = (root.getAttribute('data-join') || '').trim();
        var todayStr = (root.getAttribute('data-today') || '').trim() || new Date().toISOString().slice(0, 10);
        var y = parseInt(root.getAttribute('data-year'), 10);
        var m = parseInt(root.getAttribute('data-month'), 10);
        if (!y || !m) return;

        function applyMonth(ny, nm, dateList, tit) {
            y = ny;
            m = nm;
            root.setAttribute('data-year', String(y));
            root.setAttribute('data-month', String(m));
            titleEl.textContent = tit || '';
            grid.innerHTML = buildActivityCalendarGrid(y, m, dateList, joinDate, todayStr);
        }

        function step(delta) {
            var nd = new Date(y, m - 1 + delta, 1);
            var ny = nd.getFullYear();
            var nm = nd.getMonth() + 1;
            profileApi('activity_calendar_month', { year: ny, month: nm })
                .then(function (j) {
                    if (!j || !j.ok) return;
                    applyMonth(j.year, j.month, j.dates, j.title);
                })
                .catch(function () {});
        }

        if (prev) prev.addEventListener('click', function () { step(-1); });
        if (next) next.addEventListener('click', function () { step(1); });
    }

    /**
     * Déplace overlays / tiroir / modales directement sous <body> pour éviter tout contexte
     * d’empilement (transform, filter, overflow) qui piège les z-index et donne un rendu « mal superposé ».
     */
    function hoistProfileUiLayersToBody() {
        var ids = [
            'tcf-profile-api-config',
            'notificationOverlay',
            'notificationPage',
            'profileOverlay',
            'profilePage',
            'usernameModal',
            'avatarModal',
            'passwordModal',
        ];
        ids.forEach(function (id) {
            var el = document.getElementById(id);
            if (el && el.parentNode !== document.body) {
                document.body.appendChild(el);
            }
        });
    }

    function getNotifEndpoints() {
        var cfg = document.getElementById('tcf-profile-api-config');
        return {
            saEp: cfg && cfg.getAttribute('data-superadmin-endpoint'),
            notifEp: cfg && cfg.getAttribute('data-notifications-url')
        };
    }

    function updateNotifBadgeCount(count) {
        var n = Math.max(0, Number(count) || 0);
        var headBadge = document.getElementById('notifUnreadBadge');
        var markAll = document.getElementById('notifMarkAllReadBtn');
        var navBadge = document.querySelector('#showNotifications .notification-badge');

        if (headBadge) {
            if (n > 0) {
                headBadge.textContent = String(n);
                headBadge.hidden = false;
                headBadge.classList.remove('is-empty');
            } else {
                headBadge.textContent = '0';
                headBadge.hidden = true;
                headBadge.classList.add('is-empty');
            }
        }
        if (markAll) {
            markAll.hidden = n <= 0;
        }
        if (navBadge) {
            if (n > 0) {
                navBadge.textContent = String(n);
                navBadge.hidden = false;
                navBadge.style.display = '';
            } else {
                navBadge.hidden = true;
                navBadge.style.display = 'none';
            }
        } else if (n > 0) {
            var bell = document.getElementById('showNotifications');
            if (bell && !bell.querySelector('.notification-badge')) {
                var span = document.createElement('span');
                span.className = 'notification-badge';
                span.textContent = String(n);
                bell.appendChild(span);
            }
        }
    }

    function countUnreadNotifs() {
        return document.querySelectorAll('#notifList .notif-card.is-unread, #notifList .notification-item.is-unread').length;
    }

    function markNotifReadRequest(nid) {
        var eps = getNotifEndpoints();
        if (eps.saEp) {
            var fd = new FormData();
            fd.append('action', 'mark_notification_read');
            fd.append('id', String(nid));
            return fetch(eps.saEp, { method: 'POST', body: fd, credentials: 'same-origin' });
        }
        if (eps.notifEp) {
            return fetch(eps.notifEp, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ action: 'mark_read', id: Number(nid) })
            });
        }
        return Promise.resolve();
    }

    function markAllNotifsReadRequest() {
        var eps = getNotifEndpoints();
        if (eps.notifEp) {
            return fetch(eps.notifEp, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ action: 'mark_all_read' })
            });
        }
        // fallback POST form-like to current page
        var fd = new FormData();
        fd.append('mark_all_read', '1');
        return fetch(window.location.href, { method: 'POST', body: fd, credentials: 'same-origin' });
    }

    function applyNotifReadUi(item) {
        if (!item) return;
        item.classList.remove('is-unread');
        item.classList.add('is-read');
        var check = item.querySelector('.notif-read-check');
        if (check) {
            check.checked = true;
            check.disabled = true;
        }
        updateNotifBadgeCount(countUnreadNotifs());
    }

    function setupNotificationsAndProfile() {
        var showNotifications = document.getElementById('showNotifications');
        var notificationPage = document.getElementById('notificationPage');
        var notificationOverlay = document.getElementById('notificationOverlay');
        var closeNotifications = document.getElementById('closeNotifications');
        var notifDrawerCloseBtn = document.getElementById('notifDrawerCloseBtn');
        var markAllBtn = document.getElementById('notifMarkAllReadBtn');

        function closeNotifDrawer() {
            if (notificationPage) notificationPage.classList.remove('active');
            if (notificationOverlay) notificationOverlay.classList.remove('active');
        }
        function openNotifDrawer() {
            if (notificationPage) notificationPage.classList.add('active');
            if (notificationOverlay) notificationOverlay.classList.add('active');
        }

        if (showNotifications && notificationPage && notificationOverlay) {
            showNotifications.addEventListener('click', function (e) {
                e.preventDefault();
                openNotifDrawer();
            });
        }
        if (closeNotifications) closeNotifications.addEventListener('click', closeNotifDrawer);
        if (notifDrawerCloseBtn) notifDrawerCloseBtn.addEventListener('click', closeNotifDrawer);
        if (notificationOverlay && notificationPage) {
            notificationOverlay.addEventListener('click', closeNotifDrawer);
        }

        if (markAllBtn) {
            markAllBtn.addEventListener('click', function () {
                markAllBtn.disabled = true;
                markAllNotifsReadRequest()
                    .then(function () {
                        document.querySelectorAll('#notifList .notif-card.is-unread, #notifList .notification-item.is-unread').forEach(applyNotifReadUi);
                        updateNotifBadgeCount(0);
                    })
                    .finally(function () {
                        markAllBtn.disabled = false;
                    });
            });
        }

        document.addEventListener('change', function (e) {
            var input = e.target.closest && e.target.closest('.notif-read-check');
            if (!input || !input.checked) return;
            var nid = input.getAttribute('data-tcf-notif-id');
            var item = input.closest('.notif-card, .notification-item');
            if (!nid || !item || item.classList.contains('is-read')) return;
            input.disabled = true;
            markNotifReadRequest(nid)
                .then(function () {
                    applyNotifReadUi(item);
                })
                .catch(function () {
                    input.checked = false;
                    input.disabled = false;
                });
        });

        var showProfile = document.getElementById('showProfile');
        var profilePage = document.getElementById('profilePage');
        var profileOverlay = document.getElementById('profileOverlay');
        var closeProfile = document.getElementById('closeProfile');

        if (showProfile && profilePage && profileOverlay) {
            showProfile.addEventListener('click', function (e) {
                e.preventDefault();
                profilePage.classList.add('active');
                profileOverlay.classList.add('active');
            });
        }
        if (closeProfile && profilePage && profileOverlay) {
            closeProfile.addEventListener('click', function () {
                profilePage.classList.remove('active');
                profileOverlay.classList.remove('active');
            });
        }
        if (profileOverlay && profilePage) {
            profileOverlay.addEventListener('click', function () {
                profilePage.classList.remove('active');
                profileOverlay.classList.remove('active');
            });
        }

        document.getElementById('cancelBtn')?.addEventListener('click', function () {
            profilePage?.classList.remove('active');
            profileOverlay?.classList.remove('active');
        });

        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            var openPremium = document.querySelector('.tcf-modal-premium.is-open');
            if (openPremium) {
                openPremium.classList.remove('is-open');
                openPremium.style.display = 'none';
                openPremium.setAttribute('aria-hidden', 'true');
                return;
            }
            var avatarModal = document.getElementById('avatarModal');
            if (avatarModal && (avatarModal.style.display === 'flex' || avatarModal.classList.contains('is-open'))) {
                avatarModal.style.display = 'none';
                avatarModal.classList.remove('is-open');
                return;
            }
            if (profilePage && profilePage.classList.contains('active')) {
                profilePage.classList.remove('active');
                if (profileOverlay) profileOverlay.classList.remove('active');
                return;
            }
            if (notificationPage && notificationPage.classList.contains('active')) {
                notificationPage.classList.remove('active');
                if (notificationOverlay) notificationOverlay.classList.remove('active');
            }
        });

        window.addEventListener('click', function (event) {
            if (event.target.classList && event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
                event.target.classList.remove('is-open');
            }
        });
    }

    function setupNotificationExpand() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.tcf-notif-toggle');
            if (!btn) return;
            var body = btn.closest('.tcf-notif-body');
            if (!body) return;

            /* Messages communautaires : line-clamp (style WhatsApp), texte complet déjà dans le DOM */
            if (body.classList.contains('tcf-notif-body--wa')) {
                var expandedWa = body.getAttribute('data-tcf-notif-expanded') === '1';
                if (expandedWa) {
                    body.setAttribute('data-tcf-notif-expanded', '0');
                    btn.textContent = 'Voir plus';
                    btn.setAttribute('aria-expanded', 'false');
                } else {
                    body.setAttribute('data-tcf-notif-expanded', '1');
                    btn.textContent = 'Voir moins';
                    btn.setAttribute('aria-expanded', 'true');
                }
                return;
            }

            var ex = body.querySelector('.tcf-notif-excerpt');
            var full = body.querySelector('.tcf-notif-full');
            if (!full) return;
            var expanded = body.getAttribute('data-tcf-notif-expanded') === '1';
            if (expanded) {
                full.hidden = true;
                if (ex) ex.hidden = false;
                btn.textContent = 'Voir plus';
                btn.setAttribute('aria-expanded', 'false');
                body.setAttribute('data-tcf-notif-expanded', '0');
            } else {
                if (ex) ex.hidden = true;
                full.hidden = false;
                btn.textContent = 'Voir moins';
                btn.setAttribute('aria-expanded', 'true');
                body.setAttribute('data-tcf-notif-expanded', '1');
            }
        });
    }

    function setupNotificationDeepLinks() {
        document.addEventListener('click', function (e) {
            if (e.target.closest('.notif-card__check') || e.target.closest('.notif-read-check')) return;
            if (e.target.closest('.tcf-notif-toggle')) return;

            var openLink = e.target.closest && e.target.closest('a.notif-card__cta');
            var item = e.target.closest && e.target.closest('.notification-item[data-tcf-notif-deep]');
            if (!openLink && !item) return;

            var url = openLink
                ? openLink.getAttribute('href')
                : item.getAttribute('data-tcf-notif-deep');
            var nid = openLink
                ? openLink.getAttribute('data-tcf-notif-open')
                : item.getAttribute('data-tcf-notif-id');
            var hostItem = (openLink && openLink.closest('.notif-card, .notification-item')) || item;

            e.preventDefault();
            e.stopPropagation();

            function go() {
                if (url) window.location.href = url;
            }
            if (!nid) {
                go();
                return;
            }
            markNotifReadRequest(nid)
                .then(function () {
                    applyNotifReadUi(hostItem);
                    go();
                })
                .catch(function () {
                    go();
                });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        hoistProfileUiLayersToBody();
        setupNotificationsAndProfile();
        setupNotificationExpand();
        setupNotificationDeepLinks();
        setupActivityCalendar();
        setupPasswordWizard();
        setupUsernameModal();
        setupPasswordToggles();
        setupAvatarModal();

        var toast = document.querySelector('.tcf-toast');
        if (toast) {
            setTimeout(function () {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.4s ease';
                setTimeout(function () {
                    toast.remove();
                }, 450);
            }, 6500);
        }
    });
})();
