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
    $tcf_brand_title = 'Support TCF Canada — ' . $c['brand'];
    include __DIR__ . '/includes/tcf_brand_head.php';
    ?>
    <title>Support TCF Canada — <?php echo htmlspecialchars($c['brand']); ?></title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/theme-vars.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/header_footer.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/style_tcf.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/style_support.css')); ?>?v=alt-3">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
</head>
<body class="tcf-support-page">
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="support-main">
    <section class="support-hero-block support-block--dark">
        <div class="support-hero-kicker"><i class="bx bx-book-open"></i> Guide TCF Canada</div>
        <h1 class="support-hero-title">Comprendre le <span>TCF Canada</span></h1>
        <p>Le Test de Connaissance du Français pour le Canada est l’examen reconnu par IRCC pour évaluer votre niveau de français dans le cadre d’une demande d’immigration.</p>
        <div class="support-hero-stats" aria-label="Résumé du TCF Canada">
            <div>
                <strong>4</strong>
                <span>Compétences</span>
            </div>
            <div>
                <strong>0–699</strong>
                <span>Compréhensions</span>
            </div>
            <div>
                <strong>0–20</strong>
                <span>Expressions</span>
            </div>
            <div>
                <strong>NCLC 7</strong>
                <span>Seuil fréquent</span>
            </div>
        </div>
    </section>

    <section class="support-section support-block--light">
        <h2><i class="bx bx-info-circle"></i> Qu’est-ce que le TCF Canada&nbsp;?</h2>
        <p>Conçu par France Éducation international, le TCF Canada mesure votre niveau selon le CECRL. Chaque compétence reçoit une note indépendante. Un entraînement régulier reste indispensable pour atteindre le niveau visé.</p>
    </section>

    <section class="support-section support-block--dark">
        <h2><i class="bx bx-grid-alt"></i> Les 4 épreuves</h2>
        <div class="tcf-skills-grid">
            <article class="tcf-skill-card">
                <div class="tcf-skill-icon"><i class="bx bx-headphone"></i></div>
                <h3>Compréhension orale</h3>
                <p class="tcf-skill-meta">39 questions · 35 minutes</p>
                <p>QCM à partir d’enregistrements audio (conversations, annonces, messages).</p>
                <p class="tcf-skill-score">Score : <strong>0 à 699</strong></p>
            </article>
            <article class="tcf-skill-card">
                <div class="tcf-skill-icon"><i class="bx bx-book-open"></i></div>
                <h3>Compréhension écrite</h3>
                <p class="tcf-skill-meta">39 questions · 60 minutes</p>
                <p>QCM sur des textes courts puis plus longs (annonces, articles, argumentatifs).</p>
                <p class="tcf-skill-score">Score : <strong>0 à 699</strong></p>
            </article>
            <article class="tcf-skill-card">
                <div class="tcf-skill-icon"><i class="bx bx-edit"></i></div>
                <h3>Expression écrite</h3>
                <p class="tcf-skill-meta">3 tâches · 60 minutes</p>
                <p>Message simple, compte-rendu, puis texte argumentatif.</p>
                <p class="tcf-skill-score">Note : <strong>0 à 20</strong></p>
            </article>
            <article class="tcf-skill-card">
                <div class="tcf-skill-icon"><i class="bx bx-microphone"></i></div>
                <h3>Expression orale</h3>
                <p class="tcf-skill-meta">3 tâches · 12 minutes</p>
                <p>Entretien guidé : se présenter, obtenir des infos, défendre un point de vue.</p>
                <p class="tcf-skill-score">Note : <strong>0 à 20</strong></p>
            </article>
        </div>
    </section>

    <section class="support-section support-block--light">
        <h2><i class="bx bx-bar-chart-alt-2"></i> Niveaux CECRL</h2>
        <p class="support-section-intro">Chaque compétence est évaluée séparément. Voici les seuils de conversion en niveaux CECRL.</p>
        <div class="tcf-levels-grid">
            <article class="tcf-level-card">
                <div class="tcf-level-head"><span class="tcf-level-tag">A1</span><span class="tcf-level-label">Débutant</span></div>
                <ul>
                    <li>Compréhension : <strong>100–199</strong></li>
                    <li>Expression : <strong>4–5</strong> / 20</li>
                </ul>
                <p>Comprend des mots familiers et des expressions très simples.</p>
            </article>
            <article class="tcf-level-card">
                <div class="tcf-level-head"><span class="tcf-level-tag">A2</span><span class="tcf-level-label">Élémentaire</span></div>
                <ul>
                    <li>Compréhension : <strong>200–299</strong></li>
                    <li>Expression : <strong>6–7</strong> / 20</li>
                </ul>
                <p>Communique lors de tâches simples et habituelles.</p>
            </article>
            <article class="tcf-level-card">
                <div class="tcf-level-head"><span class="tcf-level-tag">B1</span><span class="tcf-level-label">Intermédiaire</span></div>
                <ul>
                    <li>Compréhension : <strong>300–399</strong></li>
                    <li>Expression : <strong>8–9</strong> / 20</li>
                </ul>
                <p>Se débrouille dans la plupart des situations du quotidien.</p>
            </article>
            <article class="tcf-level-card">
                <div class="tcf-level-head"><span class="tcf-level-tag">B2</span><span class="tcf-level-label">Intermédiaire avancé</span></div>
                <ul>
                    <li>Compréhension : <strong>400–499</strong></li>
                    <li>Expression : <strong>10–13</strong> / 20</li>
                </ul>
                <p>Niveau souvent visé pour les programmes d’immigration.</p>
            </article>
            <article class="tcf-level-card">
                <div class="tcf-level-head"><span class="tcf-level-tag">C1</span><span class="tcf-level-label">Avancé</span></div>
                <ul>
                    <li>Compréhension : <strong>500–599</strong></li>
                    <li>Expression : <strong>14–17</strong> / 20</li>
                </ul>
                <p>S’exprime de manière fluide et structurée sur des sujets complexes.</p>
            </article>
            <article class="tcf-level-card">
                <div class="tcf-level-head"><span class="tcf-level-tag">C2</span><span class="tcf-level-label">Maîtrise</span></div>
                <ul>
                    <li>Compréhension : <strong>600–699</strong></li>
                    <li>Expression : <strong>18–20</strong> / 20</li>
                </ul>
                <p>Comprend sans effort et s’exprime avec une grande précision.</p>
            </article>
        </div>
    </section>

    <section class="support-section support-block--dark">
        <h2><i class="bx bx-flag"></i> Équivalences NCLC</h2>
        <p class="support-section-intro">Pour Entrée express et la plupart des programmes IRCC, les scores sont convertis en NCLC. Le seuil le plus fréquent est <strong>NCLC 7</strong>.</p>
        <div class="tcf-nclc-table-wrap">
            <table class="tcf-nclc-table">
                <thead>
                    <tr>
                        <th>NCLC</th>
                        <th>C. orale</th>
                        <th>C. écrite</th>
                        <th>E. écrite</th>
                        <th>E. orale</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td><strong>10+</strong></td><td>549–699</td><td>549–699</td><td>16–20</td><td>16–20</td></tr>
                    <tr><td><strong>9</strong></td><td>523–548</td><td>524–548</td><td>14–15</td><td>14–15</td></tr>
                    <tr><td><strong>8</strong></td><td>503–522</td><td>499–523</td><td>12–13</td><td>12–13</td></tr>
                    <tr class="tcf-nclc-highlight"><td><strong>7</strong></td><td>458–502</td><td>453–498</td><td>10–11</td><td>10–11</td></tr>
                    <tr><td><strong>6</strong></td><td>398–457</td><td>406–452</td><td>7–9</td><td>7–9</td></tr>
                    <tr><td><strong>5</strong></td><td>369–397</td><td>375–405</td><td>6</td><td>6</td></tr>
                    <tr><td><strong>4</strong></td><td>331–368</td><td>342–374</td><td>4–5</td><td>4–5</td></tr>
                </tbody>
            </table>
        </div>
        <p class="tcf-nclc-note"><i class="bx bx-info-circle"></i> La ligne NCLC 7 est mise en avant car c’est le seuil le plus souvent demandé.</p>
    </section>

    <section class="support-section support-block--light">
        <h2><i class="bx bx-bulb"></i> Conseils pour réussir</h2>
        <ul class="support-tips">
            <li><strong>Travaillez les 4 compétences</strong> : chaque épreuve a son propre score.</li>
            <li><strong>Gérez le temps</strong> : entraînez-vous en conditions réelles.</li>
            <li><strong>Connaissez les consignes</strong> avant le jour J pour éviter de perdre du temps.</li>
            <li><strong>Visez B2 minimum</strong> si vous préparez Entrée express.</li>
            <li><strong>Pratiquez l’oral à voix haute</strong>, idéalement avec un partenaire.</li>
        </ul>
    </section>

    <section class="support-section support-contact-block support-block--dark">
        <h2><i class="bx bx-help-circle"></i> Besoin d’aide&nbsp;?</h2>
        <p>Une question sur votre compte, un abonnement ou une épreuve&nbsp;? Contactez <?php echo htmlspecialchars($c['brand']); ?> :</p>
        <div class="support-grid">
            <article class="support-card">
                <h2><i class="bx bxs-envelope"></i> E-mail</h2>
                <p><a href="<?php echo htmlspecialchars(tcf_site_mailto($c)); ?>"><?php echo htmlspecialchars($c['email']); ?></a></p>
            </article>
            <article class="support-card">
                <h2><i class="bx bxs-phone-call"></i> Téléphone</h2>
                <p><a href="<?php echo htmlspecialchars(tcf_site_tel($c)); ?>"><?php echo htmlspecialchars($c['phone_display']); ?></a></p>
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
