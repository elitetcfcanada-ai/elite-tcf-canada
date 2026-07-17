<?php
session_start();
require_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $tcf_brand_title = 'Politique de confidentialité — ELITE TCF CANADA';
    include __DIR__ . '/includes/tcf_brand_head.php';
    ?>
    <title>Politique de confidentialité — ELITE TCF CANADA</title>
    <link rel="stylesheet" href="Assets/css/theme-vars.css">
    <link rel="stylesheet" href="Assets/css/header_footer.css">
    <link rel="stylesheet" href="Assets/css/style_tcf.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <style>
        .legal-page { max-width: 800px; margin: 8rem auto 4rem; padding: 0 2rem; font-size: 1.5rem; line-height: 1.7; }
        .legal-page h1 { color: var(--tcf-primary); margin-bottom: 1rem; }
        .legal-page h2 { margin-top: 2rem; font-size: 1.8rem; }
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<main class="legal-page">
    <h1>Politique de confidentialité</h1>
    <p>Ce site propose une préparation au TCF Canada. La présente politique décrit comment nous traitons les données personnelles et les mesures d’audience.</p>

    <h2>Responsable du traitement</h2>
    <p>Les données collectées via ce site sont traitées dans le cadre de la gestion des comptes utilisateurs, des abonnements et de l’amélioration du service éducatif proposé.</p>

    <h2>Données collectées</h2>
    <ul>
        <li><strong>Compte :</strong> nom, adresse e-mail, mot de passe (haché), informations d’abonnement.</li>
        <li><strong>Mesure d’audience (si vous acceptez les cookies analytiques) :</strong> pages consultées, identifiant de session technique, adresse IP (pour estimer le pays / la région via un service tiers), référent HTTP et classification de la source de trafic (direct, recherche, réseaux sociaux, etc.). Vous ne saisissez pas votre position : elle est déduite automatiquement à partir de l’IP.</li>
        <li><strong>À l’inscription :</strong> nous enregistrons également une estimation du pays et la source de trafic, pour des statistiques agrégées dans l’espace d’administration.</li>
    </ul>

    <h2>Finalités</h2>
    <ul>
        <li>Fournir l’accès aux contenus et aux fonctionnalités du site.</li>
        <li>Gérer les paiements et abonnements lorsque applicable.</li>
        <li>Produire des statistiques anonymisées ou agrégées sur l’utilisation du site (tableaux de bord administrateur).</li>
    </ul>

    <h2>Base légale</h2>
    <p>Exécution du service pour les données de compte ; consentement pour les cookies non essentiels et la mesure d’audience détaillée ; intérêt légitime pour la sécurité et la lutte contre la fraude, dans la mesure du nécessaire.</p>

    <h2>Durée de conservation</h2>
    <p>Les données de compte sont conservées pendant la durée d’utilisation du service. Les journaux d’audience peuvent être conservés pour une durée limitée permettant des analyses historiques (par exemple quelques années), puis supprimés ou anonymisés.</p>

    <h2>Vos droits</h2>
    <p>Selon le droit applicable, vous pouvez demander l’accès, la rectification, l’effacement, la limitation ou vous opposer à certains traitements, ainsi que la portabilité lorsque applicable. Contact : <a href="mailto:elitetcfcanada@gmail.com">elitetcfcanada@gmail.com</a>.</p>

    <h2>Sous-traitants et transferts</h2>
    <p>La géolocalisation approximative par IP peut s’appuyer sur un service externe (par exemple ip-api.com) lorsque vous avez accepté les cookies analytiques. Les échanges ont lieu sur les protocoles prévus par ce prestataire.</p>

    <p><a href="politique-cookies.php">Politique de cookies</a> · <a href="index.php">Accueil</a></p>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
<?php include __DIR__ . '/includes/cookie_banner.php'; ?>
</body>
</html>
