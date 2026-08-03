/**
 * Compat quiz CE/CO — ne remplace PAS tcfQuizConfirm si déjà fourni
 * par tcf_confirm_dialog.js (chargé avant).
 */
(function (global) {
    'use strict';
    if (typeof global.tcfConfirm === 'function') {
        global.tcfQuizConfirm = global.tcfConfirm;
        return;
    }
    function tcfQuizConfirm(opts) {
        opts = opts || {};
        return Promise.resolve(
            !!window.confirm(opts.message || opts.title || 'Confirmer ?')
        );
    }
    global.tcfQuizConfirm = tcfQuizConfirm;
})(window);
