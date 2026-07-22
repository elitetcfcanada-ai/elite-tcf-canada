/**
 * Dialogue de confirmation stylé pour les quiz CE / CO.
 * window.tcfQuizConfirm({ title, message, confirmLabel, cancelLabel }).then(bool)
 */
(function (global) {
    'use strict';

    function ensureRoot() {
        var root = document.getElementById('tcf-quiz-dialog-root');
        if (root) return root;
        root = document.createElement('div');
        root.id = 'tcf-quiz-dialog-root';
        root.innerHTML =
            '<div class="tcf-qdlg" id="tcf-qdlg" hidden aria-hidden="true">' +
            '<div class="tcf-qdlg__backdrop" data-qdlg-cancel></div>' +
            '<div class="tcf-qdlg__panel" role="dialog" aria-modal="true" aria-labelledby="tcf-qdlg-title">' +
            '<div class="tcf-qdlg__icon" aria-hidden="true"><i class="bx bx-flag"></i></div>' +
            '<h3 class="tcf-qdlg__title" id="tcf-qdlg-title"></h3>' +
            '<p class="tcf-qdlg__msg" id="tcf-qdlg-msg"></p>' +
            '<div class="tcf-qdlg__actions">' +
            '<button type="button" class="tcf-qdlg__btn tcf-qdlg__btn--ghost" id="tcf-qdlg-cancel"></button>' +
            '<button type="button" class="tcf-qdlg__btn tcf-qdlg__btn--primary" id="tcf-qdlg-ok"></button>' +
            '</div></div></div>';
        document.body.appendChild(root);
        return root;
    }

    function tcfQuizConfirm(opts) {
        opts = opts || {};
        return new Promise(function (resolve) {
            ensureRoot();
            var dlg = document.getElementById('tcf-qdlg');
            var titleEl = document.getElementById('tcf-qdlg-title');
            var msgEl = document.getElementById('tcf-qdlg-msg');
            var okBtn = document.getElementById('tcf-qdlg-ok');
            var cancelBtn = document.getElementById('tcf-qdlg-cancel');
            if (!dlg || !okBtn || !cancelBtn) {
                resolve(window.confirm(opts.message || 'Confirmer ?'));
                return;
            }
            titleEl.textContent = opts.title || 'Confirmer';
            msgEl.textContent = opts.message || '';
            okBtn.textContent = opts.confirmLabel || 'Valider';
            cancelBtn.textContent = opts.cancelLabel || 'Annuler';

            function close(val) {
                dlg.hidden = true;
                dlg.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('tcf-qdlg-open');
                okBtn.removeEventListener('click', onOk);
                cancelBtn.removeEventListener('click', onCancel);
                dlg.querySelector('[data-qdlg-cancel]').removeEventListener('click', onCancel);
                document.removeEventListener('keydown', onKey);
                resolve(!!val);
            }
            function onOk() { close(true); }
            function onCancel() { close(false); }
            function onKey(e) {
                if (e.key === 'Escape') onCancel();
                if (e.key === 'Enter') onOk();
            }

            okBtn.addEventListener('click', onOk);
            cancelBtn.addEventListener('click', onCancel);
            dlg.querySelector('[data-qdlg-cancel]').addEventListener('click', onCancel);
            document.addEventListener('keydown', onKey);

            dlg.hidden = false;
            dlg.setAttribute('aria-hidden', 'false');
            document.body.classList.add('tcf-qdlg-open');
            setTimeout(function () { okBtn.focus(); }, 30);
        });
    }

    global.tcfQuizConfirm = tcfQuizConfirm;
})(window);
