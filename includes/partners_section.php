<?php

declare(strict_types=1);

/**
 * Section partenaires (logos) — page d'accueil.
 * Attendu : $pdo disponible. Charge les partenaires publiés.
 */
if (!isset($pdo) || !($pdo instanceof PDO)) {
    return;
}

if (!function_exists('tcf_partners_list_published')) {
    require_once __DIR__ . '/partners_helper.php';
}

try {
    $tcf_partners_home = tcf_partners_list_published($pdo, 48);
} catch (Throwable $e) {
    $tcf_partners_home = [];
}

if (!$tcf_partners_home) {
    return;
}
?>
<section class="tcf-partners tcf-partners--dark" id="partenaires" aria-labelledby="tcf-partners-title">
    <div class="tcf-partners__inner">
        <header class="tcf-partners__header subscription-header">
            <h4 class="section-subtitle tcf-sub-kicker" id="tcf-partners-kicker">
                <i class="bx bxs-handshake" aria-hidden="true"></i> PARTENAIRES
            </h4>
            <h2 class="section-title tfc-sub-main-title" id="tcf-partners-title">
                Ils nous accompagnent — <span class="tcf-sub-accent">nos partenaires</span>
            </h2>
            <div class="tcf-sub-title-bar" aria-hidden="true"></div>
            <p class="tcf-partners__lead">Des entreprises et organisations qui soutiennent ELITE TCF CANADA.</p>
        </header>

        <ul class="tcf-partners__grid" role="list">
            <?php foreach ($tcf_partners_home as $partner): ?>
                <?php
                $pName = trim((string) ($partner['name'] ?? 'Partenaire'));
                $pLogo = trim((string) ($partner['logo_href'] ?? ''));
                $pWeb = trim((string) ($partner['website_url'] ?? ''));
                if ($pLogo === '') {
                    continue;
                }
                $tag = $pWeb !== '' ? 'a' : 'div';
                $hrefAttr = $pWeb !== ''
                    ? ' href="' . htmlspecialchars($pWeb, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer"'
                    : '';
                ?>
                <li class="tcf-partners__item">
                    <<?php echo $tag; ?> class="tcf-partners__card"<?php echo $hrefAttr; ?> title="<?php echo htmlspecialchars($pName, ENT_QUOTES, 'UTF-8'); ?>">
                        <span class="tcf-partners__logo-wrap">
                            <img
                                class="tcf-partners__logo"
                                src="<?php echo htmlspecialchars($pLogo, ENT_QUOTES, 'UTF-8'); ?>"
                                alt="<?php echo htmlspecialchars($pName, ENT_QUOTES, 'UTF-8'); ?>"
                                loading="lazy"
                                decoding="async"
                                onerror="this.onerror=null;this.classList.add('is-broken');this.alt='Logo indisponible';"
                            >
                        </span>
                        <span class="tcf-partners__name"><?php echo htmlspecialchars($pName, ENT_QUOTES, 'UTF-8'); ?></span>
                    </<?php echo $tag; ?>>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
