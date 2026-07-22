(function () {
    'use strict';

    var POLL_INTERVAL_MS = 3500;
    var POLL_MAX_ATTEMPTS = 90;

    function initPaymentModal() {
        var paymentModal = document.getElementById('payment-modal');
        var paymentModalOverlay = document.getElementById('payment-modal-overlay');
        var paymentModalClose = document.getElementById('payment-modal-close');
        var paymentCancel = document.getElementById('payment-cancel');
        var paymentForm = document.getElementById('payment-form');
        var paymentPhone = document.getElementById('payment-phone');
        var paymentSubmit = document.getElementById('payment-submit');
        var paymentStatus = document.getElementById('payment-status');
        var paymentStatusMessage = document.getElementById('payment-status-message');
        var paymentPlanName = document.getElementById('payment-plan-name');
        var paymentPlanPrice = document.getElementById('payment-plan-price');

        var selectedPlan = null;
        var pollTimer = null;
        var pollAttempts = 0;
        var activeReference = null;

        if (!paymentModal) {
            return;
        }

        function paymentEndpoint() {
            return window.TCF_PAYMENT_ENDPOINT || 'payment_api.php';
        }

        function clearPoll() {
            if (pollTimer) {
                clearInterval(pollTimer);
                pollTimer = null;
            }
            pollAttempts = 0;
        }

        function openPaymentModal(plan) {
            selectedPlan = plan;

            if (paymentPlanName) {
                paymentPlanName.textContent = plan.label || '-';
            }
            if (paymentPlanPrice) {
                paymentPlanPrice.textContent = (plan.currency || '$') + plan.price;
            }

            if (paymentForm) {
                paymentForm.reset();
            }

            if (paymentStatus) {
                paymentStatus.hidden = true;
            }

            paymentModal.hidden = false;
            paymentModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            setTimeout(function () {
                if (paymentPhone) {
                    paymentPhone.focus();
                }
            }, 100);
        }

        window.openPaymentModal = openPaymentModal;

        function closePaymentModal() {
            clearPoll();
            activeReference = null;
            if (paymentModal) {
                paymentModal.hidden = true;
                paymentModal.style.display = 'none';
                document.body.style.overflow = '';
            }
            selectedPlan = null;
            setSubmitButtonLoading(false);
        }

        function showStatus(type, message) {
            if (!paymentStatus || !paymentStatusMessage) return;
            paymentStatus.className = 'payment-status payment-status--' + type;
            paymentStatusMessage.textContent = message;
            paymentStatus.hidden = false;
        }

        function hideStatus() {
            if (paymentStatus) {
                paymentStatus.hidden = true;
            }
        }

        function setSubmitButtonLoading(loading) {
            if (!paymentSubmit) return;

            var btnText = paymentSubmit.querySelector('.btn-text');
            var btnLoader = paymentSubmit.querySelector('.btn-loader');

            if (loading) {
                paymentSubmit.disabled = true;
                if (btnText) btnText.hidden = true;
                if (btnLoader) btnLoader.hidden = false;
            } else {
                paymentSubmit.disabled = false;
                if (btnText) btnText.hidden = false;
                if (btnLoader) btnLoader.hidden = true;
            }
        }

        function formatPhone(phone) {
            var digits = String(phone || '').replace(/\D+/g, '');
            if (!digits) {
                return '';
            }
            if (digits.indexOf('00') === 0) {
                digits = digits.slice(2);
            }
            while (digits.indexOf('237237') === 0) {
                digits = digits.slice(3);
            }
            if (/^2370\d{9}$/.test(digits)) {
                digits = '237' + digits.slice(4);
            }
            if (/^2376\d{8}$/.test(digits)) {
                return '+' + digits;
            }
            if (/^06\d{8}$/.test(digits)) {
                return '+237' + digits.slice(1);
            }
            if (/^6\d{8}$/.test(digits)) {
                return '+237' + digits;
            }
            if (digits.charAt(0) === '0' && digits.length === 10) {
                digits = digits.slice(1);
            }
            if (/^6\d{8}$/.test(digits)) {
                return '+237' + digits;
            }
            return digits.indexOf('237') === 0 ? '+' + digits : '+237' + digits.replace(/^0+/, '');
        }

        function validatePhone(phone) {
            return /^\+2376\d{8}$/.test(String(phone || ''));
        }

        function detectProviderFromPhone(formatted) {
            var local = String(formatted || '').replace(/^\+237/, '');
            if (/^(67|68|650|651|652|653|654)/.test(local)) {
                return 'mtn_momo';
            }
            if (/^(69|655|656|657|658|659)/.test(local)) {
                return 'orange_money';
            }
            return 'auto';
        }

        function providerLabel(provider) {
            if (provider === 'orange_money') return 'Orange Money';
            if (provider === 'mtn_momo') return 'MTN Mobile Money';
            return 'Mobile Money (MTN / Orange)';
        }

        function postJson(body) {
            return fetch(paymentEndpoint(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify(body),
            }).then(function (response) {
                return response.json();
            });
        }

        function handlePaymentComplete(data) {
            clearPoll();
            showStatus('success', data.message || 'Paiement réussi ! Votre abonnement est activé.');
            setTimeout(function () {
                closePaymentModal();
                if (window.location.search.indexOf('payment_ref=') !== -1) {
                    window.location.href = window.location.pathname;
                } else {
                    window.location.reload();
                }
            }, 2000);
        }

        function handlePaymentFailure(data) {
            clearPoll();
            showStatus('error', data.message || 'Le paiement a été refusé ou annulé.');
            setSubmitButtonLoading(false);
        }

        function pollStatus(reference) {
            pollAttempts += 1;

            if (pollAttempts > POLL_MAX_ATTEMPTS) {
                handlePaymentFailure({
                    message: 'Délai dépassé. Si vous avez confirmé sur votre téléphone, rechargez la page. Sinon, réessayez.',
                });
                return;
            }

            postJson({
                action: 'status',
                reference: reference,
            })
                .then(function (data) {
                    if (!data) {
                        return;
                    }

                    if (data.success && data.status === 'complete') {
                        handlePaymentComplete(data);
                        return;
                    }

                    if (data.status === 'failed' || data.status === 'cancelled' || data.status === 'canceled' ||
                        data.status === 'rejected' || data.status === 'declined' || data.status === 'expired') {
                        handlePaymentFailure(data);
                        return;
                    }

                    showStatus('loading', data.message || 'En attente de confirmation sur votre téléphone…');
                })
                .catch(function (error) {
                    console.error('Erreur lors du suivi du paiement:', error);
                    if (pollAttempts > 3) {
                        showStatus('loading', 'Vérification du paiement en cours…');
                    }
                });
        }

        function startPoll(reference) {
            clearPoll();
            activeReference = reference;
            pollStatus(reference);
            pollTimer = setInterval(function () {
                pollStatus(reference);
            }, POLL_INTERVAL_MS);
        }

        function beginStatusTracking(reference, message) {
            if (paymentForm) {
                paymentForm.style.display = 'none';
            }
            showStatus('loading', message || 'Suivi du paiement en cours…');
            startPoll(reference);
        }

        function handleInitResponse(data) {
            if (!data || !data.success || !data.reference) {
                setSubmitButtonLoading(false);
                showStatus('error', (data && data.message) || 'Erreur lors de l\'initialisation du paiement.');
                return;
            }

            if (data.mode === 'redirect' && data.redirect_url) {
                showStatus('loading', data.message || 'Ouverture de la page de paiement Notch Pay…');
                window.setTimeout(function () {
                    window.location.href = data.redirect_url;
                }, 600);
                return;
            }

            beginStatusTracking(
                data.reference,
                data.message || 'Demande envoyée. Confirmez le paiement sur votre téléphone.'
            );
        }

        if (paymentForm) {
            paymentForm.addEventListener('submit', function (e) {
                e.preventDefault();

                if (!selectedPlan) {
                    showStatus('error', 'Veuillez sélectionner une formule d\'abonnement.');
                    return;
                }

                var phone = paymentPhone ? paymentPhone.value.trim() : '';
                var provider = 'auto';

                if (!phone) {
                    showStatus('error', 'Veuillez entrer votre numéro de téléphone.');
                    if (paymentPhone) paymentPhone.focus();
                    return;
                }

                var formattedPhone = formatPhone(phone);
                if (!validatePhone(formattedPhone)) {
                    showStatus('error', 'Numéro Cameroun invalide. Exemple : +237 670 000 000');
                    if (paymentPhone) paymentPhone.focus();
                    return;
                }

                var detected = detectProviderFromPhone(formattedPhone);
                if (detected === 'mtn_momo' || detected === 'orange_money') {
                    provider = detected;
                }

                setSubmitButtonLoading(true);
                hideStatus();
                showStatus('loading', 'Initialisation du paiement avec ' + providerLabel(provider) + '…');

                postJson({
                    action: 'init',
                    plan_key: selectedPlan.key,
                    phone: formattedPhone,
                    provider: provider,
                })
                    .then(handleInitResponse)
                    .catch(function (error) {
                        setSubmitButtonLoading(false);
                        showStatus('error', 'Erreur de connexion. Veuillez réessayer.');
                        console.error('Payment error:', error);
                    });
            });
        }

        if (paymentModalClose) {
            paymentModalClose.addEventListener('click', closePaymentModal);
        }

        if (paymentCancel) {
            paymentCancel.addEventListener('click', closePaymentModal);
        }

        if (paymentModalOverlay) {
            paymentModalOverlay.addEventListener('click', closePaymentModal);
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && paymentModal && !paymentModal.hidden) {
                closePaymentModal();
            }
        });

        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.js-tcf-open-checkout');
            if (!btn) return;

            e.preventDefault();

            if (!window.TCF_SUBSCRIBE_LOGGED_IN) {
                var loginUrl = window.TCF_LOGIN_URL || 'login.php';
                var planKeyPending = btn.getAttribute('data-plan-key') || '';
                var returnPath = window.TCF_SUBSCRIBE_RETURN_PATH || 'abonnement.php';
                if (planKeyPending) {
                    returnPath += (returnPath.indexOf('?') >= 0 ? '&' : '?') + 'subscribe=' + encodeURIComponent(planKeyPending);
                }
                var next = encodeURIComponent(returnPath);
                window.location.href = loginUrl + (loginUrl.indexOf('?') >= 0 ? '&' : '?') + 'next=' + next;
                return;
            }

            var planKey = btn.getAttribute('data-plan-key');
            if (!planKey) return;

            openPaymentModal({
                key: planKey,
                label: btn.getAttribute('data-plan-label') || 'Abonnement',
                price: btn.getAttribute('data-plan-price') || '0',
                currency: btn.getAttribute('data-plan-currency') || '$',
            });
        });

        if (paymentPhone) {
            paymentPhone.addEventListener('input', function (e) {
                e.target.value = e.target.value.replace(/[^\d\+\s\-]/g, '');
            });
        }

        var params = new URLSearchParams(window.location.search);
        var returnRef = params.get('payment_ref');
        var paymentSuccess = params.get('payment_success');

        if (paymentSuccess === '1') {
            paymentModal.hidden = false;
            paymentModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            if (paymentForm) {
                paymentForm.style.display = 'none';
            }
            showStatus('success', 'Paiement confirmé ! Votre abonnement est activé.');
            setTimeout(function () {
                window.location.href = window.location.pathname;
            }, 2500);
            return;
        }

        if (returnRef) {
            paymentModal.hidden = false;
            paymentModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            if (paymentForm) {
                paymentForm.style.display = 'none';
            }
            if (paymentPlanName) {
                paymentPlanName.textContent = 'Finalisation';
            }
            if (paymentPlanPrice) {
                paymentPlanPrice.textContent = '';
            }
            setSubmitButtonLoading(true);
            beginStatusTracking(returnRef, 'Vérification du paiement après retour Notch Pay…');
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPaymentModal);
    } else {
        initPaymentModal();
    }
})();
