/**
 * Dialogue de confirmation interne (carte) — site + admin.
 * window.tcfConfirm({ title, message, confirmLabel, cancelLabel, variant, icon }).then(bool)
 * variant: 'danger' | 'info' (même rouge plateforme)
 * Alias: window.tcfQuizConfirm (quiz CE/CO)
 */
(function (global) {
    'use strict';

    var BRAND = '#d30d0d';
    var BRAND_SOFT = 'rgba(211, 13, 13, 0.1)';
    var BRAND_HOVER = '#b40b0b';

    function ensureRoot() {
        var root = document.getElementById('tcf-confirm-dialog-root');
        if (root) return root;
        root = document.createElement('div');
        root.id = 'tcf-confirm-dialog-root';
        root.innerHTML =
            '<div class="tcf-qdlg" id="tcf-qdlg" hidden aria-hidden="true">' +
            '<div class="tcf-qdlg__backdrop" data-qdlg-cancel></div>' +
            '<div class="tcf-qdlg__panel" role="dialog" aria-modal="true" aria-labelledby="tcf-qdlg-title">' +
            '<div class="tcf-qdlg__icon" id="tcf-qdlg-icon" aria-hidden="true"><i class="bx bx-trash"></i></div>' +
            '<h3 class="tcf-qdlg__title" id="tcf-qdlg-title"></h3>' +
            '<p class="tcf-qdlg__msg" id="tcf-qdlg-msg"></p>' +
            '<div class="tcf-qdlg__actions">' +
            '<button type="button" class="tcf-qdlg__btn tcf-qdlg__btn--ghost" id="tcf-qdlg-cancel"></button>' +
            '<button type="button" class="tcf-qdlg__btn tcf-qdlg__btn--primary" id="tcf-qdlg-ok"></button>' +
            '</div></div></div>';
        document.body.appendChild(root);
        return root;
    }

    function applyBrandColors(iconWrap, okBtn) {
        if (iconWrap) {
            iconWrap.style.background = BRAND_SOFT;
            iconWrap.style.color = BRAND;
        }
        if (okBtn) {
            okBtn.style.background = BRAND;
            okBtn.style.color = '#fff';
            okBtn.onmouseenter = function () {
                okBtn.style.background = BRAND_HOVER;
            };
            okBtn.onmouseleave = function () {
                okBtn.style.background = BRAND;
            };
        }
    }

    function tcfConfirm(opts) {
        opts = opts || {};
        return new Promise(function (resolve) {
            ensureRoot();
            var dlg = document.getElementById('tcf-qdlg');
            var titleEl = document.getElementById('tcf-qdlg-title');
            var msgEl = document.getElementById('tcf-qdlg-msg');
            var okBtn = document.getElementById('tcf-qdlg-ok');
            var cancelBtn = document.getElementById('tcf-qdlg-cancel');
            var iconWrap = document.getElementById('tcf-qdlg-icon');
            if (!dlg || !okBtn || !cancelBtn) {
                resolve(!!window.confirm((opts && opts.message) || 'Confirmer ?'));
                return;
            }

            var variant = opts.variant === 'info' ? 'info' : 'danger';
            dlg.classList.remove('tcf-qdlg--danger', 'tcf-qdlg--info');
            dlg.classList.add(variant === 'info' ? 'tcf-qdlg--info' : 'tcf-qdlg--danger');

            var iconClass = opts.icon || (variant === 'info' ? 'bx bx-flag' : 'bx bx-trash');
            if (iconWrap) {
                iconWrap.innerHTML = '<i class="' + iconClass + '"></i>';
            }
            applyBrandColors(iconWrap, okBtn);

            titleEl.textContent = opts.title || 'Confirmer';
            msgEl.textContent = opts.message || '';
            okBtn.textContent = opts.confirmLabel || (variant === 'danger' ? 'Supprimer' : 'Confirmer');
            cancelBtn.textContent = opts.cancelLabel || 'Annuler';

            var settled = false;
            function close(val) {
                if (settled) return;
                settled = true;
                dlg.hidden = true;
                dlg.classList.remove('is-open');
                dlg.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('tcf-qdlg-open');
                okBtn.removeEventListener('click', onOk);
                cancelBtn.removeEventListener('click', onCancel);
                var bd = dlg.querySelector('[data-qdlg-cancel]');
                if (bd) bd.removeEventListener('click', onCancel);
                document.removeEventListener('keydown', onKey);
                resolve(!!val);
            }
            function onOk(e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                close(true);
            }
            function onCancel(e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                close(false);
            }
            function onKey(e) {
                if (e.key === 'Escape') onCancel(e);
            }

            okBtn.addEventListener('click', onOk);
            cancelBtn.addEventListener('click', onCancel);
            var backdrop = dlg.querySelector('[data-qdlg-cancel]');
            if (backdrop) backdrop.addEventListener('click', onCancel);
            document.addEventListener('keydown', onKey);

            dlg.hidden = false;
            dlg.classList.add('is-open');
            dlg.setAttribute('aria-hidden', 'false');
            document.body.classList.add('tcf-qdlg-open');
            try {
                document.body.appendChild(dlg.parentNode || dlg);
            } catch (e) {}
            setTimeout(function () {
                try {
                    okBtn.focus();
                } catch (e2) {}
            }, 30);
        });
    }

    global.tcfConfirm = tcfConfirm;
    global.tcfQuizConfirm = tcfConfirm;
})(window);
