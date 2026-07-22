<?php
// Prérequis : includes/config.php déjà chargé.
?>
<div id="tcf-cookie-banner" class="tcf-cookie-banner" role="dialog" aria-modal="true" aria-labelledby="tcf-cookie-title" hidden>
    <div class="tcf-cookie-card">
        <div class="tcf-cookie-card__icon" aria-hidden="true">
            <i class="bx bxs-cookie"></i>
        </div>
        <div class="tcf-cookie-card__body">
            <h2 id="tcf-cookie-title" class="tcf-cookie-card__title">Cookies</h2>
            <p class="tcf-cookie-card__text">
                Nous utilisons des cookies pour améliorer votre expérience sur ELITE TCF CANADA.
            </p>
            <div class="tcf-cookie-card__actions">
                <button type="button" class="tcf-cookie-btn tcf-cookie-btn--refuse" id="tcf-cookie-refuse">Refuser</button>
                <button type="button" class="tcf-cookie-btn tcf-cookie-btn--accept" id="tcf-cookie-accept">Accepter</button>
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
    padding: 0.85rem 1rem calc(0.85rem + env(safe-area-inset-bottom, 0px));
    pointer-events: none;
    background: transparent;
}
.tcf-cookie-banner[hidden] { display: none !important; }
.tcf-cookie-card {
    pointer-events: auto;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    width: min(100%, 26.5rem);
    padding: 0.9rem 1rem;
    border-radius: 12px;
    background: #141622;
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.28);
}
.tcf-cookie-card__icon {
    flex: 0 0 auto;
    width: 2.1rem;
    height: 2.1rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(211, 13, 13, 0.16);
    color: #d30d0d;
    font-size: 1.25rem;
}
.tcf-cookie-card__body { min-width: 0; flex: 1 1 auto; }
.tcf-cookie-card__title {
    margin: 0 0 0.25rem;
    font-size: 0.92rem;
    font-weight: 700;
    line-height: 1.2;
    color: #fff;
}
.tcf-cookie-card__text {
    margin: 0 0 0.7rem;
    font-size: 0.78rem;
    line-height: 1.4;
    color: rgba(255, 255, 255, 0.78);
}
.tcf-cookie-card__actions {
    display: flex;
    gap: 0.45rem;
    flex-wrap: wrap;
}
.tcf-cookie-btn {
    border: none;
    border-radius: 7px;
    padding: 0.42rem 0.85rem;
    font-size: 0.78rem;
    font-weight: 650;
    cursor: pointer;
    line-height: 1.2;
    transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
}
.tcf-cookie-btn--refuse {
    background: transparent;
    color: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.28);
}
.tcf-cookie-btn--refuse:hover {
    border-color: rgba(255, 255, 255, 0.5);
    background: rgba(255, 255, 255, 0.06);
}
.tcf-cookie-btn--accept {
    background: #d30d0d;
    color: #fff;
    border: 1px solid #d30d0d;
}
.tcf-cookie-btn--accept:hover {
    background: #b80b0b;
    border-color: #b80b0b;
}
@media (max-width: 480px) {
    .tcf-cookie-card {
        width: 100%;
        gap: 0.65rem;
        padding: 0.8rem 0.85rem;
    }
    .tcf-cookie-card__actions { width: 100%; }
    .tcf-cookie-btn { flex: 1 1 auto; text-align: center; }
}
</style>
<script>
(function () {
    if (document.cookie.indexOf('tcf_consent=') !== -1) return;
    var b = document.getElementById('tcf-cookie-banner');
    if (!b) return;
    b.hidden = false;
    function setConsent(v) {
        var d = new Date();
        d.setTime(d.getTime() + 365 * 24 * 60 * 60 * 1000);
        document.cookie = 'tcf_consent=' + v + ';path=/;expires=' + d.toUTCString() + ';SameSite=Lax';
        b.hidden = true;
        if (v === 'all') location.reload();
    }
    var refuse = document.getElementById('tcf-cookie-refuse');
    var accept = document.getElementById('tcf-cookie-accept');
    if (refuse) refuse.addEventListener('click', function () { setConsent('essential'); });
    if (accept) accept.addEventListener('click', function () { setConsent('all'); });
})();
</script>
