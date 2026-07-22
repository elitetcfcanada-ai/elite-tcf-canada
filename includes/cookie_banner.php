<?php
// Prérequis : includes/config.php déjà chargé.
$tcfCookieSecureJs = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'true' : 'false';
?>
<div id="tcf-cookie-banner" class="tcf-cookie-banner" role="dialog" aria-modal="true" aria-labelledby="tcf-cookie-title" hidden>
    <div class="tcf-cookie-panel">
        <div class="tcf-cookie-panel__accent" aria-hidden="true"></div>
        <div class="tcf-cookie-panel__inner">
            <div class="tcf-cookie-panel__top">
                <div class="tcf-cookie-panel__icon" aria-hidden="true">
                    <i class="bx bxs-cookie"></i>
                </div>
                <div class="tcf-cookie-panel__copy">
                    <h2 id="tcf-cookie-title" class="tcf-cookie-panel__title">Votre confidentialité</h2>
                    <p class="tcf-cookie-panel__text">
                        Nous utilisons des cookies <strong>essentiels</strong> pour sécuriser votre connexion
                        (session, « rester connecté ») et, avec votre accord, des cookies de
                        <strong>mesure d’audience</strong> pour améliorer ELITE TCF CANADA.
                    </p>
                </div>
            </div>
            <div class="tcf-cookie-panel__actions">
                <button type="button" class="tcf-cookie-btn tcf-cookie-btn--ghost" id="tcf-cookie-refuse">Essentiels uniquement</button>
                <button type="button" class="tcf-cookie-btn tcf-cookie-btn--primary" id="tcf-cookie-accept">Tout accepter</button>
            </div>
        </div>
    </div>
</div>
<style>
.tcf-cookie-banner {
    position: fixed;
    inset: auto 0 0 0;
    z-index: 100000;
    display: flex;
    justify-content: center;
    align-items: flex-end;
    padding: 1rem 1rem calc(1rem + env(safe-area-inset-bottom, 0px));
    pointer-events: none;
    background: linear-gradient(180deg, transparent 0%, rgba(10, 12, 18, 0.35) 100%);
}
.tcf-cookie-banner[hidden] { display: none !important; }
.tcf-cookie-panel {
    pointer-events: auto;
    width: min(100%, 34rem);
    border-radius: 16px;
    overflow: hidden;
    background: #0f1219;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow:
        0 4px 6px rgba(0, 0, 0, 0.12),
        0 22px 48px rgba(0, 0, 0, 0.38);
    animation: tcfCookieIn 0.35s ease both;
}
@keyframes tcfCookieIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.tcf-cookie-panel__accent {
    height: 3px;
    background: linear-gradient(90deg, #d30d0d 0%, #ff5a5a 55%, #d30d0d 100%);
}
.tcf-cookie-panel__inner { padding: 1.05rem 1.15rem 1.1rem; }
.tcf-cookie-panel__top {
    display: flex;
    gap: 0.85rem;
    align-items: flex-start;
}
.tcf-cookie-panel__icon {
    flex: 0 0 auto;
    width: 2.55rem;
    height: 2.55rem;
    border-radius: 12px;
    display: grid;
    place-items: center;
    background: rgba(211, 13, 13, 0.14);
    color: #ff6b6b;
    font-size: 1.35rem;
    border: 1px solid rgba(211, 13, 13, 0.28);
}
.tcf-cookie-panel__copy { min-width: 0; flex: 1 1 auto; }
.tcf-cookie-panel__title {
    margin: 0 0 0.35rem;
    font-size: 1.02rem;
    font-weight: 750;
    letter-spacing: -0.01em;
    color: #fff;
    line-height: 1.25;
}
.tcf-cookie-panel__text {
    margin: 0;
    font-size: 0.82rem;
    line-height: 1.5;
    color: rgba(255, 255, 255, 0.76);
}
.tcf-cookie-panel__text strong { color: #fff; font-weight: 650; }
.tcf-cookie-panel__actions {
    display: flex;
    gap: 0.55rem;
    flex-wrap: wrap;
    margin-top: 0.95rem;
    justify-content: flex-end;
}
.tcf-cookie-btn {
    border: none;
    border-radius: 999px;
    padding: 0.55rem 1.05rem;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
    line-height: 1.2;
    transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease, transform 0.12s ease;
}
.tcf-cookie-btn:active { transform: translateY(1px); }
.tcf-cookie-btn--ghost {
    background: transparent;
    color: rgba(255, 255, 255, 0.92);
    border: 1px solid rgba(255, 255, 255, 0.28);
}
.tcf-cookie-btn--ghost:hover {
    border-color: rgba(255, 255, 255, 0.5);
    background: rgba(255, 255, 255, 0.06);
}
.tcf-cookie-btn--primary {
    background: #d30d0d;
    color: #fff;
    border: 1px solid #d30d0d;
    box-shadow: 0 6px 16px rgba(211, 13, 13, 0.28);
}
.tcf-cookie-btn--primary:hover {
    background: #b80b0b;
    border-color: #b80b0b;
}
@media (max-width: 520px) {
    .tcf-cookie-panel { width: 100%; border-radius: 14px; }
    .tcf-cookie-panel__actions { width: 100%; }
    .tcf-cookie-btn { flex: 1 1 auto; text-align: center; }
}
</style>
<script>
(function () {
    if (document.cookie.indexOf('tcf_consent=') !== -1) return;
    var b = document.getElementById('tcf-cookie-banner');
    if (!b) return;
    b.hidden = false;
    var secure = <?php echo $tcfCookieSecureJs; ?>;
    function setConsent(v) {
        var d = new Date();
        d.setTime(d.getTime() + 365 * 24 * 60 * 60 * 1000);
        var c = 'tcf_consent=' + encodeURIComponent(v)
            + ';path=/;expires=' + d.toUTCString()
            + ';SameSite=Lax';
        if (secure) c += ';Secure';
        document.cookie = c;
        b.hidden = true;
        if (v === 'all') location.reload();
    }
    var refuse = document.getElementById('tcf-cookie-refuse');
    var accept = document.getElementById('tcf-cookie-accept');
    if (refuse) refuse.addEventListener('click', function () { setConsent('essential'); });
    if (accept) accept.addEventListener('click', function () { setConsent('all'); });
})();
</script>
