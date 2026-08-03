<?php
require_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    $tcf_brand_title = 'Programmes de formation | ELITE TCF CANADA';
    $tcf_brand_desc = 'Découvrez le programme de formation ELITE TCF CANADA : préparation aux 4 épreuves du TCF Canada (CE, CO, EE, EO), vidéos et entraînements.';
    $tcf_brand_keywords = 'programme formation TCF Canada, préparation TCF Canada, ELITE TCF CANADA, parcours TCF, cours TCF Canada';
    include __DIR__ . '/includes/tcf_brand_head.php';
    ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/theme-vars.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/header_footer.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/style_tcf.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/style_support.css')); ?>?v=alt-3">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/programmes.css')); ?>?v=1">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
</head>
<body class="tcf-support-page tcf-programmes-page">
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="support-main">
    <section class="support-hero-block support-block--dark">
        <div class="support-hero-kicker"><i class="bx bxs-school"></i> Nos programmes</div>
        <h1 class="support-hero-title">Programme de <span>formation</span></h1>
        <p>Un parcours clair pour préparer le TCF Canada : les quatre compétences, des entraînements ciblés et des ressources mises à jour sur la plateforme.</p>
        <div class="support-hero-stats" aria-label="Résumé du programme">
            <div>
                <strong>4</strong>
                <span>Épreuves</span>
            </div>
            <div>
                <strong>CE · CO</strong>
                <span>Compréhensions</span>
            </div>
            <div>
                <strong>EE · EO</strong>
                <span>Expressions</span>
            </div>
            <div>
                <strong>En ligne</strong>
                <span>À votre rythme</span>
            </div>
        </div>
    </section>

    <section class="support-section support-block--light">
        <h2><i class="bx bx-target-lock"></i> Objectif du programme</h2>
        <p>Vous entraîner dans les conditions proches de l’examen officiel : comprendre les consignes, gérer le temps, progresser sur chaque compétence et viser le niveau NCLC adapté à votre projet d’immigration.</p>
        <p>Le programme s’appuie sur la plateforme ELITE TCF CANADA : sujets d’entraînement, vidéos pédagogiques et accès selon votre formule (gratuit ou premium).</p>
    </section>

    <section class="support-section support-block--dark">
        <h2><i class="bx bx-grid-alt"></i> Les 4 piliers du parcours</h2>
        <p class="support-section-intro">Chaque pilier renvoie à l’espace d’entraînement correspondant sur le site.</p>
        <div class="prog-pillars">
            <a class="prog-pillar" href="<?php echo htmlspecialchars(site_href('comprehesion_ecrite.php')); ?>">
                <i class="bx bx-book-alt" aria-hidden="true"></i>
                <strong>Compréhension écrite</strong>
                <span>Textes, QCM, stratégie de lecture</span>
            </a>
            <a class="prog-pillar" href="<?php echo htmlspecialchars(site_href('comprehension_orale.php')); ?>">
                <i class="bx bx-headphone" aria-hidden="true"></i>
                <strong>Compréhension orale</strong>
                <span>Audios, repérage d’infos, rythme d’écoute</span>
            </a>
            <a class="prog-pillar" href="<?php echo htmlspecialchars(site_href('Expresion_ecrite.php')); ?>">
                <i class="bx bx-edit-alt" aria-hidden="true"></i>
                <strong>Expression écrite</strong>
                <span>3 tâches, structure, argumentation</span>
            </a>
            <a class="prog-pillar" href="<?php echo htmlspecialchars(site_href('Expresion_orale.php')); ?>">
                <i class="bx bx-message-dots" aria-hidden="true"></i>
                <strong>Expression orale</strong>
                <span>Dialogue, fluidité, situations réelles</span>
            </a>
        </div>
    </section>

    <section class="support-section support-block--light">
        <h2><i class="bx bx-map-alt"></i> Comment avancer sur la plateforme</h2>
        <ol class="prog-steps">
            <li>
                <strong>Découvrir</strong>
                <span>Regardez les vidéos et lisez les consignes pour comprendre le format de chaque épreuve.</span>
            </li>
            <li>
                <strong>S’entraîner</strong>
                <span>Passez les sujets disponibles en compréhension et en expression, à votre rythme.</span>
            </li>
            <li>
                <strong>Corriger</strong>
                <span>Analysez vos résultats, relisez les corrections et notez vos points à retravailler.</span>
            </li>
            <li>
                <strong>Intensifier</strong>
                <span>Avec un accès premium, élargissez le catalogue et préparez-vous plus régulièrement.</span>
            </li>
        </ol>
    </section>

    <section class="support-section support-block--dark prog-cta-block">
        <h2><i class="bx bx-rocket"></i> Prêt à commencer&nbsp;?</h2>
        <p>Revenez à l’accueil, explorez les vidéos ou choisissez une formule d’accès pour débloquer davantage de contenus.</p>
        <div class="prog-cta-row">
            <a class="prog-cta prog-cta--primary" href="<?php echo htmlspecialchars(site_href('videos.php')); ?>">
                <i class="bx bx-play-circle"></i> Voir les vidéos
            </a>
            <a class="prog-cta" href="<?php echo htmlspecialchars(site_href('abonnement.php')); ?>">
                <i class="bx bx-crown"></i> Abonnements
            </a>
            <a class="prog-cta" href="<?php echo htmlspecialchars(site_href('index.php')); ?>#formations">
                <i class="bx bx-home-alt"></i> Retour formations
            </a>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<?php include __DIR__ . '/includes/cookie_banner.php'; ?>
<script src="<?php echo htmlspecialchars(site_href('Assets/javascript/script_tcf.js')); ?>"></script>
</body>
</html>
