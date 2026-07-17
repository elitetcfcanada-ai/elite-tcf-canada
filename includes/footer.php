<?php
require_once __DIR__ . '/site_contact.php';
if (!function_exists('tcf_brand_logo_href')) {
    require_once __DIR__ . '/tcf_brand_logo.php';
}
$tcf_foot_contact = tcf_site_contact();
$tcf_footer_hours = trim((string) ($tcf_foot_contact['hours'] ?? ''));

$__tcf_root = str_replace('\\', '/', realpath(__DIR__ . '/..'));
$__tcf_here = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'] ?? ''));
$__tcf_rel = trim(preg_replace('#^' . preg_quote($__tcf_root, '#') . '#', '', $__tcf_here), '/');
$__tcf_depth = ($__tcf_rel === '') ? 0 : (substr_count($__tcf_rel, '/') + 1);
$FOOTER_ASSET_PREFIX = $__tcf_depth ? str_repeat('../', $__tcf_depth) : '';

$tcf_foot_url = function (string $file) use ($FOOTER_ASSET_PREFIX): string {
    if (function_exists('site_href')) {
        return site_href($file);
    }
    return $FOOTER_ASSET_PREFIX . $file;
};

$tcf_foot_role = (string) ($_SESSION['role'] ?? '');
$tcf_is_admin_area = strpos((string) ($_SERVER['SCRIPT_NAME'] ?? ''), '/admin/') !== false;
?>
<link rel="stylesheet" href="<?php echo htmlspecialchars($FOOTER_ASSET_PREFIX); ?>Assets/css/theme-vars.css">
<link rel="stylesheet" href="<?php echo htmlspecialchars($FOOTER_ASSET_PREFIX); ?>Assets/css/tcf-responsive-pills.css">
<link rel="stylesheet" href="<?php echo htmlspecialchars($FOOTER_ASSET_PREFIX); ?>Assets/css/tcf-typography.css">
<link rel="stylesheet" href="<?php echo htmlspecialchars($FOOTER_ASSET_PREFIX); ?>Assets/css/tcf-brand-logo.css">
<link rel="stylesheet" href="<?php echo htmlspecialchars($FOOTER_ASSET_PREFIX); ?>Assets/css/mobile-bottom-nav.css">
<link rel="stylesheet" href="<?php echo htmlspecialchars($FOOTER_ASSET_PREFIX); ?>Assets/css/site-footer.css">
<link rel="stylesheet" href="<?php echo htmlspecialchars($FOOTER_ASSET_PREFIX); ?>Assets/css/tcf-ui-layers.css">
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<footer class="site-footer footer">
    <div class="footer-container">
        <div class="footer-row">
            <div class="footer-col footer-about">
                <div class="footer-logo">
                    <?php echo tcf_brand_logo_img(['class' => 'tcf-brand-logo tcf-brand-logo--footer', 'size' => 30, 'prefix' => $FOOTER_ASSET_PREFIX]); ?>
                    <span class="footer-logo-text">ELITE&nbsp;TCF&nbsp;CANADA</span>
                </div>
                <p class="footer-description">Préparation à l’examen TCF Canada : compréhension et expression, écrites et orales. Votre réussite linguistique pour l’immigration canadienne avec ELITE TCF CANADA.</p>
                <div class="footer-contact">
                    <div class="contact-item">
                        <i class='bx bx-phone'></i>
                        <span><?php echo htmlspecialchars($tcf_foot_contact['phone_display']); ?></span>
                    </div>
                    <div class="contact-item">
                        <i class='bx bx-time-five'></i>
                        <span><?php echo htmlspecialchars($tcf_footer_hours !== '' ? $tcf_footer_hours : (string) ($tcf_foot_contact['hours'] ?? '')); ?></span>
                    </div>
                    <div class="contact-item">
                        <i class='bx bx-location-plus'></i>
                        <span><?php echo htmlspecialchars($tcf_foot_contact['address']); ?></span>
                    </div>
                    <div class="contact-item">
                        <i class='bx bxs-envelope'></i>
                        <span><?php echo htmlspecialchars($tcf_foot_contact['email']); ?></span>
                    </div>
                </div>
            </div>

            <div class="footer-col">
                <h5>Navigation</h5>
                <ul class="footer-links">
                    <li><a href="<?php echo htmlspecialchars($tcf_foot_url('index.php')); ?>"><i class='bx bx-chevron-right'></i> Accueil</a></li>
                    <li><a href="<?php echo htmlspecialchars($tcf_foot_url('support.php')); ?>"><i class='bx bx-chevron-right'></i> Support</a></li>
                    <li><a href="<?php echo htmlspecialchars($tcf_foot_url('login.php')); ?>"><i class='bx bx-chevron-right'></i> Connexion</a></li>
                    <li><a href="<?php echo htmlspecialchars($tcf_foot_url('politique-confidentialite.php')); ?>"><i class='bx bx-chevron-right'></i> Confidentialité</a></li>
                    <li><a href="<?php echo htmlspecialchars($tcf_foot_url('politique-cookies.php')); ?>"><i class='bx bx-chevron-right'></i> Cookies</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h5>Épreuves</h5>
                <ul class="footer-links">
                    <li><a href="<?php echo htmlspecialchars($tcf_foot_url('Expresion_ecrite.php')); ?>"><i class='bx bx-chevron-right'></i> Expression écrite</a></li>
                    <li><a href="<?php echo htmlspecialchars($tcf_foot_url('Expresion_orale.php')); ?>"><i class='bx bx-chevron-right'></i> Expression orale</a></li>
                    <li><a href="<?php echo htmlspecialchars($tcf_foot_url('comprehesion_ecrite.php')); ?>"><i class='bx bx-chevron-right'></i> Compréhension écrite</a></li>
                    <li><a href="<?php echo htmlspecialchars($tcf_foot_url('comprehension_orale.php')); ?>"><i class='bx bx-chevron-right'></i> Compréhension orale</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h5>Restez informé</h5>
                <p class="newsletter-desc">Conseils et actualités pour réussir le TCF Canada.</p>
                <form class="newsletter-form" action="#" method="get" onsubmit="return false;">
                    <input type="email" name="email" placeholder="Votre adresse email" autocomplete="email">
                    <button type="submit">S'abonner <i class='bx bxl-telegram'></i></button>
                </form>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <div class="copyright">
                    <p>&copy; <?php echo date('Y'); ?> <span>ELITE TCF</span> CANADA. Tous droits réservés.</p>
                </div>
                <div class="social-links">
                    <a href="#" aria-label="WhatsApp"><i class='bx bxl-whatsapp'></i></a>
                    <a href="#" aria-label="Facebook"><i class='bx bxl-facebook'></i></a>
                    <a href="#" aria-label="Instagram"><i class='bx bxl-instagram'></i></a>
                    <a href="#" aria-label="YouTube"><i class='bx bxl-youtube'></i></a>
                    <a href="#" aria-label="Telegram"><i class='bx bxl-telegram'></i></a>
                </div>
            </div>
        </div>
    </div>

    <a href="#" class="scroll-top-footer scroll-top" id="tcf-scroll-top" aria-label="Haut de page">
        <i class="bx bxs-chevrons-up"></i>
    </a>
</footer>

<?php if (!$tcf_is_admin_area): ?>
    <?php include __DIR__ . '/mobile_bottom_nav.php'; ?>
    <script src="<?php echo htmlspecialchars($FOOTER_ASSET_PREFIX); ?>Assets/javascript/mobile-bottom-nav.js"></script>
<?php endif; ?>

<script>
window.TCF_BRAND_LOGO = <?php echo json_encode(tcf_brand_logo_href($FOOTER_ASSET_PREFIX !== '' ? $FOOTER_ASSET_PREFIX : null), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
(function () {
    var btn = document.getElementById('tcf-scroll-top');
    if (!btn) return;
    window.addEventListener('scroll', function () {
        btn.classList.toggle('show', window.pageYOffset > 300);
    });
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
})();
</script>
