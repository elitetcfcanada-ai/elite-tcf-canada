<?php
// Prérequis : includes/config.php déjà chargé (fonction site_href).
$cookiePolicy = site_href('politique-cookies.php');
$privacyPolicy = site_href('politique-confidentialite.php');
?>
<div id="tcf-cookie-banner" class="tcf-cookie-banner" role="dialog" aria-label="Cookies" hidden>
    <div class="tcf-cookie-inner">
        <p>
            Nous utilisons des cookies pour mesurer l’audience et améliorer votre expérience sur ce site de préparation au TCF Canada.
            Consultez notre <a href="<?php echo htmlspecialchars($cookiePolicy); ?>">politique de cookies</a> et notre
            <a href="<?php echo htmlspecialchars($privacyPolicy); ?>">politique de confidentialité</a>.
        </p>
        <div class="tcf-cookie-actions">
            <button type="button" class="tcf-cookie-btn tcf-cookie-essential" id="tcf-cookie-essential">Essentiels uniquement</button>
            <button type="button" class="tcf-cookie-btn tcf-cookie-all" id="tcf-cookie-all">Tout accepter</button>
        </div>
    </div>
</div>
<style>
.tcf-cookie-banner {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 100000;
    padding: 1rem 1.25rem;
    background: var(--tcf-footer-bg, #141622);
    color: var(--tcf-text-on-dark, #fff);
    box-shadow: 0 -4px 24px rgba(0,0,0,.2);
    font-size: 1.4rem;
}
.tcf-cookie-inner {
    max-width: 1100px;
    margin: 0 auto;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 1rem;
    justify-content: space-between;
}
.tcf-cookie-banner a { color: var(--tcf-accent-light, #ff8a8a); }
.tcf-cookie-actions { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.tcf-cookie-btn {
    border: none;
    padding: 0.65rem 1.2rem;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 1.3rem;
}
.tcf-cookie-essential {
    background: transparent;
    color: var(--tcf-text-on-dark, #fff);
    border: 2px solid rgba(255,255,255,.35);
}
.tcf-cookie-all {
    background: var(--tcf-primary, #d30d0d);
    color: #fff;
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
    document.getElementById('tcf-cookie-essential').addEventListener('click', function () { setConsent('essential'); });
    document.getElementById('tcf-cookie-all').addEventListener('click', function () { setConsent('all'); });
})();
</script>
