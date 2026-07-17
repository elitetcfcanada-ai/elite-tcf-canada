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
    $tcf_brand_title = 'Politique de cookies — ELITE TCF CANADA';
    include __DIR__ . '/includes/tcf_brand_head.php';
    ?>
    <title>Politique de cookies — ELITE TCF CANADA</title>
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
    <h1>Politique de cookies</h1>
    <p>Ce site de préparation au TCF Canada utilise des cookies et technologies similaires pour faire fonctionner le site et, avec votre accord, mesurer l’audience.</p>

    <h2>Cookies strictement nécessaires</h2>
    <p>Ils permettent le fonctionnement du site (par exemple session de connexion, sécurité). Ils ne peuvent pas être désactivés via la bannière sans empêcher certaines fonctionnalités.</p>

    <h2>Cookies de mesure d’audience (optionnels)</h2>
    <p>Si vous choisissez « Essentiels uniquement », aucune mesure d’audience détaillée n’est enregistrée (les statistiques du tableau de bord resteront vides ou partielles). Avant tout choix de bannière, ou si vous choisissez « Tout accepter », le site peut enregistrer des statistiques de fréquentation en base de données : pages vues, source de trafic estimée à partir du référent, et localisation approximative dérivée de l’adresse IP (pays / région). Ces données servent à des rapports agrégés dans l’administration du site.</p>

    <h2>Durée</h2>
    <p>Le cookie de consentement <code>tcf_consent</code> est conservé environ 12 mois. Vous pouvez effacer les cookies depuis les paramètres de votre navigateur à tout moment.</p>

    <h2>Vos choix</h2>
    <p>La bannière vous permet d’accepter uniquement l’essentiel ou d’accepter également la mesure d’audience. Vous pouvez modifier votre choix en supprimant le cookie puis en rechargeant la page.</p>

    <p><a href="politique-confidentialite.php">Politique de confidentialité</a> · <a href="index.php">Accueil</a></p>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
<?php include __DIR__ . '/includes/cookie_banner.php'; ?>
</body>
</html>
