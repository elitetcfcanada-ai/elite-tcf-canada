(function () {
    'use strict';

    function loginUrl() {
        return window.TCF_LOGIN_URL || 'login.php';
    }

    function onOpenClick(ev) {
        var btn = ev.target.closest('.js-tcf-open-checkout');
        if (!btn) return;
        ev.preventDefault();
        if (!window.TCF_SUBSCRIBE_LOGGED_IN) {
            var planKeyPending = btn.getAttribute('data-plan-key') || '';
            var returnPath = window.TCF_SUBSCRIBE_RETURN_PATH || 'abonnement.php';
            if (planKeyPending) {
                returnPath += (returnPath.indexOf('?') >= 0 ? '&' : '?') + 'subscribe=' + encodeURIComponent(planKeyPending);
            }
            var next = encodeURIComponent(returnPath);
            window.location.href = loginUrl() + (loginUrl().indexOf('?') >= 0 ? '&' : '?') + 'next=' + next;
            return;
        }

        var planKey = btn.getAttribute('data-plan-key') || '';
        var planLabel = btn.getAttribute('data-plan-label') || '';
        var planPrice = btn.getAttribute('data-plan-price') || '';
        var planCurrency = btn.getAttribute('data-plan-currency') || '$';

        if (window.openPaymentModal && typeof window.openPaymentModal === 'function') {
            window.openPaymentModal({
                key: planKey,
                label: planLabel,
                price: planPrice,
                currency: planCurrency
            });
            return;
        }

        // Ancienne modale (si présente)
        if (!overlay) return;
        state.planKey = planKey;
        state.planLabel = planLabel;
        state.price = planPrice;
        state.currency = planCurrency;
        state.paymentXaf = parseInt(btn.getAttribute('data-plan-xaf') || '100', 10) || 100;
        if (summaryEl) summaryEl.textContent = state.planLabel || 'Formule sélectionnée';
        if (amountEl) {
            var p = parseFloat(state.price);
            var disp = isNaN(p) ? state.price : (p % 1 === 0 ? String(p) : p.toFixed(2));
            amountEl.textContent = state.price ? state.currency + disp : '—';
        }
        if (xafNoteEl) {
            xafNoteEl.textContent = 'Prélèvement : ' + state.paymentXaf + ' FCFA via Mobile Money';
        }
        openModal();
    }

    // Toujours écouter les clics (même sans ancienne overlay) pour rediriger vers login
    document.addEventListener('click', onOpenClick);

    var overlay = document.getElementById('tcf-checkout-overlay');
    if (!overlay) return;

    var summaryEl = document.getElementById('tfc-checkout-plan-summary');
    var amountEl = document.getElementById('tfc-checkout-amount-value');
    var xafNoteEl = document.getElementById('tfc-checkout-xaf-note');
    var payBtn = document.getElementById('tfc-checkout-pay-btn');
    var successBox = document.getElementById('tfc-checkout-success');
    var successMsg = document.getElementById('tfc-checkout-success-msg');
    var statusMsg = document.getElementById('tfc-checkout-status-msg');
    var formBlock = document.getElementById('tfc-checkout-form');
    var actionsBlock = document.getElementById('tfc-checkout-actions');

    var state = {
        planKey: null,
        planLabel: '',
        price: '',
        currency: '$',
        paymentXaf: 100,
        reference: null,
        pollTimer: null
    };

    function paymentEndpoint() {
        return window.TCF_PAYMENT_ENDPOINT || '';
    }

    function clearPoll() {
        if (state.pollTimer) {
            window.clearInterval(state.pollTimer);
            state.pollTimer = null;
        }
    }

    function setStatus(text, isError) {
        if (!statusMsg) return;
        statusMsg.textContent = text || '';
        statusMsg.className = 'tfc-checkout-status-msg' + (isError ? ' is-error' : text ? ' is-info' : '');
    }

    function openModal() {
        overlay.hidden = false;
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('tcf-checkout-open');
        clearPoll();
        state.reference = null;
        setStatus('');
        if (successBox) {
            successBox.classList.add('is-hidden');
            successBox.hidden = true;
        }
        if (formBlock) formBlock.style.display = '';
        if (actionsBlock) actionsBlock.style.display = '';
        if (payBtn) {
            payBtn.style.display = '';
            payBtn.disabled = false;
            payBtn.textContent = 'Payer maintenant';
        }
    }

    function closeModal() {
        clearPoll();
        overlay.hidden = true;
        overlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('tcf-checkout-open');
    }

    function getPhoneNumber() {
        var el = document.getElementById('tfc-payment-phone');
        return el && el.value ? el.value.trim() : '';
    }

    function postJson(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify(body)
        }).then(function (r) {
            return r.json();
        });
    }

    function showSuccess(j) {
        if (formBlock) formBlock.style.display = 'none';
        if (actionsBlock) actionsBlock.style.display = 'none';
        if (payBtn) payBtn.style.display = 'none';
        setStatus('');
        if (successMsg) successMsg.textContent = (j && j.message) || 'Votre formule est active.';
        if (successBox) {
            successBox.classList.remove('is-hidden');
            successBox.hidden = false;
        }
        window.setTimeout(function () {
            window.location.reload();
        }, 1800);
    }

    function pollStatus() {
        if (!state.reference) return;
        var url = paymentEndpoint();
        postJson(url, { action: 'status', reference: state.reference })
            .then(function (j) {
                if (!j) return;
                if (j.status === 'complete' && j.success) {
                    clearPoll();
                    showSuccess(j);
                    return;
                }
                if (j.status === 'failed' || j.status === 'cancelled' || j.status === 'canceled') {
                    clearPoll();
                    setStatus(j.message || 'Paiement refusé ou annulé.', true);
                    if (payBtn) {
                        payBtn.disabled = false;
                        payBtn.textContent = 'Réessayer';
                    }
                    return;
                }
                setStatus(j.message || 'En attente de validation sur votre téléphone…');
            })
            .catch(function () {
                setStatus('Vérification du paiement…');
            });
    }

    function startPoll() {
        clearPoll();
        pollStatus();
        state.pollTimer = window.setInterval(pollStatus, 3500);
    }

    function runPayment() {
        var url = paymentEndpoint();
        if (!url || !state.planKey) {
            window.alert('Paiement non configuré.');
            return;
        }

        var phone = getPhoneNumber();
        if (!phone) {
            window.alert('Indiquez votre numéro de téléphone avec l\'indicatif international.');
            return;
        }

        // Valider que le numéro commence par +
        if (!phone.startsWith('+')) {
            window.alert('Le numéro doit commencer par l\'indicatif international (ex: +33, +1, +237).');
            return;
        }

        payBtn.disabled = true;
        payBtn.textContent = 'Initialisation…';
        setStatus('Connexion à Notch Pay…');

        postJson(url, {
            action: 'init',
            plan_key: state.planKey,
            phone: phone
        })
            .then(function (init) {
                if (!init || !init.success || !init.reference) {
                    throw new Error((init && init.message) || 'Initialisation impossible.');
                }
                state.reference = init.reference;
                setStatus('Paiement initialisé. Notch Pay détectera votre opérateur automatiquement.');
                payBtn.textContent = 'En attente de confirmation…';
                
                // Démarrer le polling pour vérifier le statut
                startPoll();
            })
            .catch(function (err) {
                payBtn.disabled = false;
                payBtn.textContent = 'Payer maintenant';
                setStatus(err && err.message ? err.message : 'Erreur de paiement.', true);
            });
    }

    overlay.querySelectorAll('.js-tcf-checkout-close').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });


    if (payBtn) {
        payBtn.addEventListener('click', runPayment);
    }

    var params = new URLSearchParams(window.location.search);
    var returnRef = params.get('payment_ref');
    if (returnRef && paymentEndpoint()) {
        state.reference = returnRef;
        openModal();
        if (summaryEl) summaryEl.textContent = 'Finalisation du paiement…';
        if (formBlock) formBlock.style.display = 'none';
        if (payBtn) payBtn.style.display = 'none';
        setStatus('Vérification du paiement en cours…');
        startPoll();
    }
})();
