/**
 * Compat quiz CE/CO — délègue au dialogue interne partagé.
 * Préférer Assets/javascript/tcf_confirm_dialog.js
 */
(function (global) {
    'use strict';
    function tcfQuizConfirm(opts) {
        opts = opts || {};
        if (typeof global.tcfConfirm === 'function') {
            return global.tcfConfirm({
                title: opts.title || 'Confirmer',
                message: opts.message || '',
                confirmLabel: opts.confirmLabel || 'Valider',
                cancelLabel: opts.cancelLabel || 'Annuler',
                variant: opts.variant || 'info',
                icon: opts.icon || 'bx bx-flag'
            });
        }
        return Promise.resolve(false);
    }
    global.tcfQuizConfirm = tcfQuizConfirm;
})(window);
