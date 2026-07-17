<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/site_contact.php';
$c = tcf_site_contact();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $tcf_brand_title = 'Comprendre le TCF Canada — ' . $c['brand'];
    include __DIR__ . '/includes/tcf_brand_head.php';
    ?>
    <title>Comprendre le TCF Canada — <?php echo htmlspecialchars($c['brand']); ?></title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/theme-vars.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/header_footer.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/style_tcf.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/style_support.css')); ?>">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="support-main">
    <section class="support-hero-block">
        <div class="support-hero-kicker"><i class="bx bx-award"></i> Guide officiel de préparation</div>
        <h1>Comprendre le <span>TCF Canada</span></h1>
        <p>Le Test de Connaissance du Français pour le Canada (TCF Canada) est l'examen officiel reconnu par Immigration, Réfugiés et Citoyenneté Canada (IRCC) pour évaluer vos compétences en français dans le cadre d'une demande de résidence permanente ou de citoyenneté canadienne.</p>
        <div class="support-hero-stats" aria-label="Résumé du TCF Canada">
            <div>
                <strong>4</strong>
                <span>Compétences</span>
            </div>
            <div>
                <strong>0-699</strong>
                <span>Compréhensions</span>
            </div>
            <div>
                <strong>0-20</strong>
                <span>Expressions</span>
            </div>
            <div>
                <strong>NCLC 7</strong>
                <span>Seuil fréquent</span>
            </div>
        </div>
    </section>

    <section class="support-section">
        <h2><i class="bx bx-book-reader"></i> Qu'est-ce que le TCF Canada ?</h2>
        <p>Conçu par France Éducation international, le TCF Canada mesure votre niveau de français selon le <strong>Cadre Européen Commun de Référence pour les Langues (CECRL)</strong>. Il s'adresse à toute personne souhaitant prouver ses compétences linguistiques dans une démarche d'immigration vers le Canada.</p>
        <p>Le test évalue quatre compétences distinctes et chaque compétence reçoit une note indépendante. Aucune préparation académique n'est requise, mais un entraînement régulier reste indispensable pour atteindre le niveau visé.</p>
    </section>

    <section class="support-section">
        <h2><i class="bx bx-grid-alt"></i> Les 4 épreuves du TCF Canada</h2>
        <div class="tcf-skills-grid">
            <article class="tcf-skill-card">
                <div class="tcf-skill-icon"><i class="bx bx-headphone"></i></div>
                <h3>Compréhension orale</h3>
                <p class="tcf-skill-meta">39 questions · 35 minutes</p>
                <p>QCM à partir d'enregistrements audio variés (conversations, annonces, messages, extraits radio).</p>
                <p class="tcf-skill-score">Score : <strong>0 à 699 points</strong></p>
            </article>
            <article class="tcf-skill-card">
                <div class="tcf-skill-icon"><i class="bx bx-book-open"></i></div>
                <h3>Compréhension écrite</h3>
                <p class="tcf-skill-meta">39 questions · 60 minutes</p>
                <p>QCM portant sur des textes courts puis de plus en plus longs (annonces, articles, textes argumentatifs).</p>
                <p class="tcf-skill-score">Score : <strong>0 à 699 points</strong></p>
            </article>
            <article class="tcf-skill-card">
                <div class="tcf-skill-icon"><i class="bx bx-edit"></i></div>
                <h3>Expression écrite</h3>
                <p class="tcf-skill-meta">3 tâches · 60 minutes</p>
                <p>Rédaction d'un message simple, d'un article ou compte-rendu, puis d'un texte argumentatif.</p>
                <p class="tcf-skill-score">Note : <strong>0 à 20 points</strong></p>
            </article>
            <article class="tcf-skill-card">
                <div class="tcf-skill-icon"><i class="bx bx-microphone"></i></div>
                <h3>Expression orale</h3>
                <p class="tcf-skill-meta">3 tâches · 12 minutes</p>
                <p>Entretien guidé : se présenter, obtenir des informations, défendre un point de vue face à l'examinateur.</p>
                <p class="tcf-skill-score">Note : <strong>0 à 20 points</strong></p>
            </article>
        </div>
    </section>

    <section class="support-section">
        <h2><i class="bx bx-trophy"></i> Les niveaux du CECRL et les points requis</h2>
        <p class="support-section-intro">Chaque compétence est évaluée séparément. Les seuils ci-dessous correspondent à la conversion officielle des points en niveaux CECRL.</p>
        <div class="tcf-levels-grid">
            <article class="tcf-level-card lvl-a1">
                <div class="tcf-level-head"><span class="tcf-level-tag">A1</span><span class="tcf-level-label">Débutant</span></div>
                <ul>
                    <li>Compréhension orale et écrite : <strong>100 à 199</strong> points</li>
                    <li>Expression orale et écrite : <strong>4 à 5</strong> points / 20</li>
                </ul>
                <p>Comprend des mots familiers et des expressions très simples sur soi et son environnement immédiat.</p>
            </article>
            <article class="tcf-level-card lvl-a2">
                <div class="tcf-level-head"><span class="tcf-level-tag">A2</span><span class="tcf-level-label">Élémentaire</span></div>
                <ul>
                    <li>Compréhension orale et écrite : <strong>200 à 299</strong> points</li>
                    <li>Expression orale et écrite : <strong>6 à 7</strong> points / 20</li>
                </ul>
                <p>Communique lors de tâches simples et habituelles ne demandant qu'un échange direct d'informations.</p>
            </article>
            <article class="tcf-level-card lvl-b1">
                <div class="tcf-level-head"><span class="tcf-level-tag">B1</span><span class="tcf-level-label">Intermédiaire</span></div>
                <ul>
                    <li>Compréhension orale et écrite : <strong>300 à 399</strong> points</li>
                    <li>Expression orale et écrite : <strong>8 à 9</strong> points / 20</li>
                </ul>
                <p>Se débrouille dans la plupart des situations de la vie quotidienne et exprime des opinions simples.</p>
            </article>
            <article class="tcf-level-card lvl-b2">
                <div class="tcf-level-head"><span class="tcf-level-tag">B2</span><span class="tcf-level-label">Intermédiaire avancé</span></div>
                <ul>
                    <li>Compréhension orale et écrite : <strong>400 à 499</strong> points</li>
                    <li>Expression orale et écrite : <strong>10 à 13</strong> points / 20</li>
                </ul>
                <p>Communique avec aisance et spontanéité. Niveau visé pour la plupart des programmes d'immigration canadiens.</p>
            </article>
            <article class="tcf-level-card lvl-c1">
                <div class="tcf-level-head"><span class="tcf-level-tag">C1</span><span class="tcf-level-label">Avancé</span></div>
                <ul>
                    <li>Compréhension orale et écrite : <strong>500 à 599</strong> points</li>
                    <li>Expression orale et écrite : <strong>14 à 17</strong> points / 20</li>
                </ul>
                <p>Comprend des textes longs et exigeants ; s'exprime de manière fluide et structurée sur des sujets complexes.</p>
            </article>
            <article class="tcf-level-card lvl-c2">
                <div class="tcf-level-head"><span class="tcf-level-tag">C2</span><span class="tcf-level-label">Maîtrise</span></div>
                <ul>
                    <li>Compréhension orale et écrite : <strong>600 à 699</strong> points</li>
                    <li>Expression orale et écrite : <strong>18 à 20</strong> points / 20</li>
                </ul>
                <p>Comprend sans effort tout ce qu'il lit ou entend ; s'exprime spontanément avec une grande précision.</p>
            </article>
        </div>
    </section>

    <section class="support-section">
        <h2><i class="bx bx-flag"></i> Équivalences NCLC pour l'immigration</h2>
        <p class="support-section-intro">Pour Entrée express et la plupart des programmes IRCC, les scores TCF Canada sont convertis en <strong>NCLC (Niveau de Compétence Linguistique Canadien)</strong>. La majorité des programmes exigent au minimum <strong>NCLC 7</strong>.</p>
        <div class="tcf-nclc-table-wrap">
            <table class="tcf-nclc-table">
                <thead>
                    <tr>
                        <th>NCLC</th>
                        <th>Compréhension orale</th>
                        <th>Compréhension écrite</th>
                        <th>Expression écrite</th>
                        <th>Expression orale</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td><strong>10 +</strong></td><td>549 – 699</td><td>549 – 699</td><td>16 – 20</td><td>16 – 20</td></tr>
                    <tr><td><strong>9</strong></td><td>523 – 548</td><td>524 – 548</td><td>14 – 15</td><td>14 – 15</td></tr>
                    <tr><td><strong>8</strong></td><td>503 – 522</td><td>499 – 523</td><td>12 – 13</td><td>12 – 13</td></tr>
                    <tr class="tcf-nclc-highlight"><td><strong>7</strong></td><td>458 – 502</td><td>453 – 498</td><td>10 – 11</td><td>10 – 11</td></tr>
                    <tr><td><strong>6</strong></td><td>398 – 457</td><td>406 – 452</td><td>7 – 9</td><td>7 – 9</td></tr>
                    <tr><td><strong>5</strong></td><td>369 – 397</td><td>375 – 405</td><td>6</td><td>6</td></tr>
                    <tr><td><strong>4</strong></td><td>331 – 368</td><td>342 – 374</td><td>4 – 5</td><td>4 – 5</td></tr>
                </tbody>
            </table>
        </div>
        <p class="tcf-nclc-note"><i class="bx bx-info-circle"></i> La ligne NCLC 7 est surlignée car elle correspond au seuil le plus fréquemment demandé pour l'immigration économique.</p>
    </section>

    <section class="support-section">
        <h2><i class="bx bx-bulb"></i> Conseils pour réussir</h2>
        <ul class="support-tips">
            <li><strong>Travaillez les 4 compétences en parallèle</strong> : chaque épreuve a son propre score, négliger une compétence peut bloquer votre objectif global.</li>
            <li><strong>Gérez le temps</strong> : entraînez-vous en conditions réelles, surtout pour la compréhension écrite (60 minutes pour 39 questions).</li>
            <li><strong>Familiarisez-vous avec les consignes</strong> : la perte de temps à les déchiffrer le jour J est l'une des erreurs les plus fréquentes.</li>
            <li><strong>Visez B2 minimum</strong> si vous préparez Entrée express : c'est le niveau qui rapporte le plus de points dans le calcul CRS pour la langue secondaire.</li>
            <li><strong>Travaillez l'expression orale</strong> à voix haute, idéalement avec un partenaire ou un formateur, pour gagner en fluidité.</li>
        </ul>
    </section>

    <section class="support-section support-contact-block">
        <h2><i class="bx bx-help-circle"></i> Besoin d'aide ?</h2>
        <p>Une question sur votre compte, un abonnement ou une épreuve ? Contactez l'équipe <?php echo htmlspecialchars($c['brand']); ?> :</p>
        <div class="support-grid">
            <article class="support-card">
                <h2><i class="bx bxs-envelope"></i> E-mail</h2>
                <p><a href="mailto:<?php echo htmlspecialchars($c['email']); ?>"><?php echo htmlspecialchars($c['email']); ?></a></p>
            </article>
            <article class="support-card">
                <h2><i class="bx bxs-phone-call"></i> Téléphone</h2>
                <p><?php echo htmlspecialchars($c['phone_display']); ?></p>
            </article>
            <article class="support-card">
                <h2><i class="bx bxs-time"></i> Horaires</h2>
                <p><?php echo htmlspecialchars($c['hours']); ?></p>
            </article>
            <article class="support-card">
                <h2><i class="bx bxs-map"></i> Localisation</h2>
                <p><?php echo htmlspecialchars($c['address']); ?></p>
            </article>
        </div>
    </section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<?php include __DIR__ . '/includes/cookie_banner.php'; ?>
<script src="<?php echo htmlspecialchars(site_href('Assets/javascript/script_tcf.js')); ?>"></script>
</body>
</html>
