<?php
/**
 * Barre de navigation mobile (type application) — affichée ≤991px via CSS.
 */
$tcf_mnav_script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
if ($tcf_mnav_script === '' || strpos($tcf_mnav_script, '.') === false) {
    $uriPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
    $tcf_mnav_script = basename((string) $uriPath) ?: 'index.php';
}
$tcf_mnav_logged = !empty($_SESSION['user_id']);

/** URL absolue app (toujours fiable, y compris depuis sous-dossiers). */
$tcf_mnav_url = static function (string $file, string $hash = '') : string {
    $file = ltrim($file, '/');
    if (function_exists('site_href')) {
        $href = site_href($file);
    } else {
        $href = '/' . $file;
    }
    $hash = ltrim($hash, '#');
    if ($hash !== '') {
        $href .= '#' . $hash;
    }
    return $href;
};

$tcf_epreuves_scripts = [
    'comprehesion_ecrite.php',
    'Expresion_ecrite.php',
    'comprehension_orale.php',
    'Expresion_orale.php',
    'epreuve_ee.php',
    'epreuve_eo.php',
    'comprehesion_ecrite_quiz.php',
    'comprehension_orale_quiz.php',
];

$tcf_mnav_skill = null;
if (in_array($tcf_mnav_script, ['Expresion_ecrite.php', 'epreuve_ee.php'], true)) {
    $tcf_mnav_skill = ['key' => 'ee', 'short' => 'Écrite', 'label' => 'Expression Écrite'];
} elseif (in_array($tcf_mnav_script, ['Expresion_orale.php', 'epreuve_eo.php'], true)) {
    $tcf_mnav_skill = ['key' => 'eo', 'short' => 'Orale', 'label' => 'Expression Orale'];
} elseif (in_array($tcf_mnav_script, ['comprehesion_ecrite.php', 'comprehesion_ecrite_quiz.php'], true)) {
    $tcf_mnav_skill = ['key' => 'ce', 'short' => 'C. Écrite', 'label' => 'Compréhension Écrite'];
} elseif (in_array($tcf_mnav_script, ['comprehension_orale.php', 'comprehension_orale_quiz.php'], true)) {
    $tcf_mnav_skill = ['key' => 'co', 'short' => 'C. Orale', 'label' => 'Compréhension Orale'];
}

$tcf_mnav_active = static function (string $key) use ($tcf_mnav_script, $tcf_epreuves_scripts): string {
    if ($key === 'home' && $tcf_mnav_script === 'index.php') {
        return ' is-active';
    }
    if ($key === 'videos' && in_array($tcf_mnav_script, ['videos.php', 'watch.php'], true)) {
        return ' is-active';
    }
    if ($key === 'epreuves' && in_array($tcf_mnav_script, $tcf_epreuves_scripts, true)) {
        return ' is-active';
    }
    if ($key === 'profil' && in_array($tcf_mnav_script, ['login.php', 'abonnement.php', 'posts.php', 'support.php'], true)) {
        return ' is-active';
    }
    return '';
};

$tcf_mnav_epreuves_label = $tcf_mnav_skill ? $tcf_mnav_skill['short'] : 'Épreuves';
?>
<nav class="tcf-mobile-nav" id="tcfMobileNav" aria-label="Navigation mobile"
     data-tcf-home="<?php echo htmlspecialchars($tcf_mnav_url('index.php')); ?>"
     data-tcf-login="<?php echo htmlspecialchars($tcf_mnav_url('login.php')); ?>">
    <a href="<?php echo htmlspecialchars($tcf_mnav_url('index.php')); ?>"
       class="tcf-mobile-nav__item<?php echo $tcf_mnav_active('home'); ?>"
       data-tcf-nav-go>
        <i class="bx bx-home-alt" aria-hidden="true"></i>
        <span>Accueil</span>
    </a>

    <button type="button"
            class="tcf-mobile-nav__item<?php echo $tcf_mnav_active('epreuves'); ?>"
            data-tcf-sheet-open="tcfSheetEpreuves"
            aria-controls="tcfSheetEpreuves"
            aria-expanded="false"
            aria-label="<?php echo htmlspecialchars($tcf_mnav_skill ? ('Épreuve : ' . $tcf_mnav_skill['label']) : 'Épreuves'); ?>">
        <i class="bx bx-book-alt" aria-hidden="true"></i>
        <span><?php echo htmlspecialchars($tcf_mnav_epreuves_label); ?></span>
    </button>

    <a href="<?php echo htmlspecialchars($tcf_mnav_url('videos.php')); ?>"
       class="tcf-mobile-nav__item<?php echo $tcf_mnav_active('videos'); ?>"
       data-tcf-nav-go>
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
        <a href="<?php echo htmlspecialchars($tcf_mnav_url('Expresion_ecrite.php')); ?>" data-tcf-nav-go class="tcf-mobile-sheet__link<?php echo in_array($tcf_mnav_script, ['Expresion_ecrite.php', 'epreuve_ee.php'], true) ? ' is-active' : ''; ?>">
            <i class="bx bx-edit-alt"></i> Expression Écrite
        </a>
        <a href="<?php echo htmlspecialchars($tcf_mnav_url('Expresion_orale.php')); ?>" data-tcf-nav-go class="tcf-mobile-sheet__link<?php echo in_array($tcf_mnav_script, ['Expresion_orale.php', 'epreuve_eo.php'], true) ? ' is-active' : ''; ?>">
            <i class="bx bx-microphone"></i> Expression Orale
        </a>
        <a href="<?php echo htmlspecialchars($tcf_mnav_url('comprehesion_ecrite.php')); ?>" data-tcf-nav-go class="tcf-mobile-sheet__link<?php echo in_array($tcf_mnav_script, ['comprehesion_ecrite.php', 'comprehesion_ecrite_quiz.php'], true) ? ' is-active' : ''; ?>">
            <i class="bx bx-book-alt"></i> Compréhension Écrite
        </a>
        <a href="<?php echo htmlspecialchars($tcf_mnav_url('comprehension_orale.php')); ?>" data-tcf-nav-go class="tcf-mobile-sheet__link<?php echo in_array($tcf_mnav_script, ['comprehension_orale.php', 'comprehension_orale_quiz.php'], true) ? ' is-active' : ''; ?>">
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
        <a href="<?php echo htmlspecialchars($tcf_mnav_url('videos.php')); ?>" data-tcf-nav-go class="tcf-mobile-sheet__link<?php echo in_array($tcf_mnav_script, ['videos.php', 'watch.php'], true) ? ' is-active' : ''; ?>">
            <i class="bx bx-play-circle"></i> Vidéos
        </a>
        <a href="<?php echo htmlspecialchars($tcf_mnav_url('index.php', 'services')); ?>" data-tcf-nav-go class="tcf-mobile-sheet__link">
            <i class="bx bx-briefcase-alt-2"></i> Services
        </a>
        <a href="<?php echo htmlspecialchars($tcf_mnav_url('abonnement.php')); ?>" data-tcf-nav-go class="tcf-mobile-sheet__link<?php echo $tcf_mnav_script === 'abonnement.php' ? ' is-active' : ''; ?>">
            <i class="bx bx-credit-card"></i> Abonnement
        </a>
        <a href="<?php echo htmlspecialchars($tcf_mnav_url('index.php', 'temoignages')); ?>" data-tcf-nav-go class="tcf-mobile-sheet__link">
            <i class="bx bx-message-rounded-dots"></i> Témoignages
        </a>
        <a href="<?php echo htmlspecialchars($tcf_mnav_url('index.php', 'contact')); ?>" data-tcf-nav-go class="tcf-mobile-sheet__link">
            <i class="bx bx-envelope"></i> Contact
        </a>
        <a href="<?php echo htmlspecialchars($tcf_mnav_url('posts.php')); ?>" data-tcf-nav-go class="tcf-mobile-sheet__link<?php echo $tcf_mnav_script === 'posts.php' ? ' is-active' : ''; ?>">
            <i class="bx bx-news"></i> Annonces
        </a>
        <a href="<?php echo htmlspecialchars($tcf_mnav_url('support.php')); ?>" data-tcf-nav-go class="tcf-mobile-sheet__link<?php echo $tcf_mnav_script === 'support.php' ? ' is-active' : ''; ?>">
            <i class="bx bx-support"></i> Support
        </a>
        <?php if ($tcf_mnav_logged): ?>
            <button type="button" class="tcf-mobile-sheet__link tcf-mobile-sheet__link--btn" id="tcfMobileSheetProfile">
                <i class="bx bx-user"></i> Mon profil
            </button>
        <?php else: ?>
            <a href="<?php echo htmlspecialchars($tcf_mnav_url('login.php')); ?>" data-tcf-nav-go class="tcf-mobile-sheet__link">
                <i class="bx bx-log-in"></i> Connexion
            </a>
        <?php endif; ?>
    </div>
</section>
