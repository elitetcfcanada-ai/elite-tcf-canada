(function () {
    'use strict';

    var root = document.getElementById('tcf-reset-root');
    var apiUrl = root ? root.getAttribute('data-api-url') || '' : '';
    var loginUrl = root ? root.getAttribute('data-login-url') || 'login.php' : 'login.php';

    var emailForm = document.getElementById('email-form');
    var codeForm = document.getElementById('code-form');
    var passwordForm = document.getElementById('password-form');
    var container = document.querySelector('.reset-container');
    var instructionSide = document.getElementById('instruction-side');
    var codeInstruction = document.getElementById('code-instruction');
    var emailInput = document.getElementById('reset-email');
    var emailHidden = document.getElementById('reset-email-hidden');
    var toast = document.getElementById('reset-toast');

    function showToast(msg, isErr) {
        if (!toast) {
            alert(msg);
            return;
        }
        toast.textContent = msg;
        toast.hidden = false;
        toast.className = 'reset-toast' + (isErr ? ' reset-toast--err' : '');
        setTimeout(function () {
            toast.hidden = true;
        }, 5000);
    }

    function api(action, data) {
        return fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(Object.assign({ action: action }, data || {})),
        }).then(function (r) {
            return r.json();
        });
    }

    function goStep(which) {
        emailForm.classList.remove('active-step');
        emailForm.classList.add('hidden-step');
        codeForm.classList.remove('active-step');
        codeForm.classList.add('hidden-step');
        passwordForm.classList.remove('active-step');
        passwordForm.classList.add('hidden-step');
        if (which === 'email') {
            emailForm.classList.add('active-step');
            emailForm.classList.remove('hidden-step');
        } else if (which === 'code') {
            codeForm.classList.add('active-step');
            codeForm.classList.remove('hidden-step');
        } else if (which === 'pass') {
            passwordForm.classList.add('active-step');
            passwordForm.classList.remove('hidden-step');
        }
    }

    setTimeout(function () {
        if (container) container.classList.add('active');
    }, 200);

    emailForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var em = (emailInput.value || '').trim();
        if (!em) return;
        var btn = document.getElementById('btn-reset-send');
        if (btn) btn.disabled = true;
        api('reset_send', { email: em })
            .then(function (j) {
                if (j && j.ok) {
                    emailHidden.value = em;
                    if (codeInstruction) codeInstruction.textContent = 'Code envoyé à ' + em;
                    if (instructionSide) instructionSide.textContent = 'Saisissez le code reçu (1 min). Vous pouvez le renvoyer.';
                    goStep('code');
                    document.getElementById('reset-otp').focus();
                    showToast(j.message || 'Si l’adresse est enregistrée, un code a été envoyé.', false);
                } else {
                    showToast((j && j.message) || 'Erreur.', true);
                }
            })
            .catch(function () {
                showToast('Erreur réseau.', true);
            })
            .then(function () {
                if (btn) btn.disabled = false;
            });
    });

    document.getElementById('resend-code').addEventListener('click', function () {
        var em = emailHidden.value.trim();
        if (!em) return;
        api('reset_send', { email: em }).then(function (j) {
            showToast(j && j.ok ? 'Nouveau code envoyé.' : (j.message || 'Erreur'), !j || !j.ok);
        });
    });

    codeForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var em = emailHidden.value.trim();
        var code = (document.getElementById('reset-otp').value || '').replace(/\D/g, '');
        if (code.length !== 6) {
            showToast('Entrez les 6 chiffres.', true);
            return;
        }
        var btn = document.getElementById('btn-reset-verify');
        if (btn) btn.disabled = true;
        api('reset_verify', { email: em, code: code })
            .then(function (j) {
                if (j && j.ok) {
                    goStep('pass');
                    if (instructionSide) instructionSide.textContent = 'Choisissez un mot de passe fort.';
                    showToast(j.message || 'Code OK', false);
                } else {
                    showToast((j && j.message) || 'Code invalide.', true);
                }
            })
            .catch(function () {
                showToast('Erreur réseau.', true);
            })
            .then(function () {
                if (btn) btn.disabled = false;
            });
    });

    passwordForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var p1 = document.getElementById('reset-new1').value;
        var p2 = document.getElementById('reset-new2').value;
        var btn = document.getElementById('btn-reset-save');
        if (btn) btn.disabled = true;
        api('reset_finish', { new_password: p1, confirm_password: p2 })
            .then(function (j) {
                if (j && j.ok) {
                    showToast(j.message || 'Mot de passe mis à jour.', false);
                    setTimeout(function () {
                        window.location.href = loginUrl;
                    }, 1500);
                } else {
                    showToast((j && j.message) || 'Erreur.', true);
                }
            })
            .catch(function () {
                showToast('Erreur réseau.', true);
            })
            .then(function () {
                if (btn) btn.disabled = false;
            });
    });
})();
