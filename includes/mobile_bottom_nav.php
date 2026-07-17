<?php
/**
 * Barre de navigation mobile (type application) — affichée ≤991px via CSS.
 */
$tcf_mnav_script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
$tcf_mnav_logged = !empty($_SESSION['user_id']);
$tcf_mnav_url = function (string $file) use ($FOOTER_ASSET_PREFIX): string {
    if (function_exists('site_href')) {
        return site_href($file);
    }
    return $FOOTER_ASSET_PREFIX . $file;
};

$tcf_epreuves_scripts = [
    'comprehesion_ecrite.php',
    'Expresion_ecrite.php',
    'comprehension_orale.php',
    'Expresion_orale.php',
];

$tcf_mnav_active = static function (string $key) use ($tcf_mnav_script, $tcf_epreuves_scripts): string {
    if ($key === 'home' && $tcf_mnav_script === 'index.php') {
        return ' is-active';
    }
    if ($key === 'videos' && $tcf_mnav_script === 'videos.php') {
        return ' is-active';
    }
    if ($key === 'epreuves' && in_array($tcf_mnav_script, $tcf_epreuves_scripts, true)) {
        return ' is-active';
    }
    if ($key === 'profil' && in_array($tcf_mnav_script, ['login.php', 'abonnement.php', 'messages.php'], true)) {
        return ' is-active';
    }
    return '';
};
?>
<nav class="tcf-mobile-nav" id="tcfMobileNav" aria-label="Navigation mobile">
    <a href="<?php echo htmlspecialchars($tcf_mnav_url('index.php')); ?>"
       class="tcf-mobile-nav__item<?php echo $tcf_mnav_active('home'); ?>">
        <i class="bx bx-home-alt" aria-hidden="true"></i>
        <span>Accueil</span>
    </a>

    <button type="button"
            class="tcf-mobile-nav__item<?php echo $tcf_mnav_active('epreuves'); ?>"
            data-tcf-sheet-open="tcfSheetEpreuves"
            aria-controls="tcfSheetEpreuves"
            aria-expanded="false">
        <i class="bx bx-book-alt" aria-hidden="true"></i>
        <span>Épreuves</span>
    </button>

    <a href="<?php echo htmlspecialchars($tcf_mnav_url('videos.php')); ?>"
       class="tcf-mobile-nav__item<?php echo $tcf_mnav_active('videos'); ?>">
        <i class="bx bx-play-circle" aria-hidden="true"></i>
        <span>Vidéos</span>
    </a>

    <button type="button"
            class="tcf-mobile-nav__item"
            data-tcf-sheet-open="tcfSheetMenu"
            aria-controls="tcfSheetMenu"
            aria-expanded="false">
        <i class="bx bx-menu" aria-hidden="true"></i>
        <span>Menu</span>
    </button>
</nav>

<div class="tcf-mobile-sheet-overlay" id="tcfMobileSheetOverlay" hidden></div>

<section class="tcf-mobile-sheet" id="tcfSheetEpreuves" aria-label="Épreuves TCF Canada" hidden>
    <header class="tcf-mobile-sheet__head">
        <h2>Épreuves TCF Canada</h2>
        <button type="button" class="tcf-mobile-sheet__close" data-tcf-sheet-close aria-label="Fermer">
            <i class="bx bx-x"></i>
        </button>
    </header>
    <div class="tcf-mobile-sheet__body">
        <a href="<?php echo htmlspecialchars($tcf_mnav_url('Expresion_ecrite.php')); ?>" class="tcf-mobile-sheet__link">
            <i class="bx bx-edit-alt"></i> Expression Écrite
        </a>
        <a href="<?php echo htmlspecialchars($tcf_mnav_url('Expresion_orale.php')); ?>" class="tcf-mobile-sheet__link">
            <i class="bx bx-microphone"></i> Expression Orale
        </a>
        <a href="<?php echo htmlspecialchars($tcf_mnav_url('comprehesion_ecrite.php')); ?>" class="tcf-mobile-sheet__link">
            <i class="bx bx-book-alt"></i> Compréhension Écrite
        </a>
        <a href="<?php echo htmlspecialchars($tcf_mnav_url('comprehension_orale.php')); ?>" class="tcf-mobile-sheet__link">
            <i class="bx bx-headphone"></i> Compréhension Orale
        </a>
    </div>
</section>

<section class="tcf-mobile-sheet" id="tcfSheetMenu" aria-label="Menu principal" hidden>
    <header class="tcf-mobile-sheet__head">
        <h2>Menu</h2>
        <button type="button" class="tcf-mobile-sheet__close" data-tcf-sheet-close aria-label="Fermer">
            <i class="bx bx-x"></i>
        </button>
    </header>
    <div class="tcf-mobile-sheet__body">
        <a href="<?php echo htmlspecialchars($tcf_mnav_url('index.php')); ?>" class="tcf-mobile-sheet__link">
            <i class="bx bx-home-alt"></i> Accueil
        </a>
        <a href="<?php echo htmlspecialchars($tcf_mnav_url('videos.php')); ?>" class="tcf-mobile-sheet__link">
            <i class="bx bx-play-circle"></i> Vidéos
        </a>
        <a href="<?php echo htmlspecialchars($tcf_mnav_url('support.php')); ?>" class="tcf-mobile-sheet__link">
            <i class="bx bx-support"></i> Support
        </a>
        <a href="<?php echo htmlspecialchars($tcf_mnav_url('abonnement.php')); ?>" class="tcf-mobile-sheet__link">
            <i class="bx bx-credit-card"></i> Abonnement
        </a>
        <?php if ($tcf_mnav_logged): ?>
            <a href="<?php echo htmlspecialchars($tcf_mnav_url('messages.php')); ?>" class="tcf-mobile-sheet__link">
                <i class="bx bx-message-dots"></i> Messages
            </a>
            <button type="button" class="tcf-mobile-sheet__link tcf-mobile-sheet__link--btn" id="tcfMobileSheetProfile">
                <i class="bx bx-user"></i> Mon profil
            </button>
        <?php else: ?>
            <a href="<?php echo htmlspecialchars($tcf_mnav_url('login.php')); ?>" class="tcf-mobile-sheet__link">
                <i class="bx bx-log-in"></i> Connexion
            </a>
        <?php endif; ?>
    </div>
</section>
