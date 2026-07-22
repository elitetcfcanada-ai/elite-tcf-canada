<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/avatar_helper.php';
require_once __DIR__ . '/includes/site_contact.php';
$tcf_index_contact = tcf_site_contact();
$testimonialsHome = [];
try {
    $testimonialsHome = $pdo->query(
        'SELECT t.id, t.author_name, t.content, t.rating, t.created_at, t.user_id, u.avatar AS user_avatar '
        . 'FROM testimonials t '
        . 'LEFT JOIN users u ON u.id = t.user_id '
        . 'ORDER BY t.created_at DESC LIMIT 24'
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $testimonialsHome = [];
}
$indexUserSubscription = null;
if (!empty($_SESSION['user_id'])) {
    $st = $pdo->prepare('SELECT subscription_type, name, role FROM users WHERE id = ?');
    $st->execute([(int) $_SESSION['user_id']]);
    $indexUserSubscription = $st->fetch(PDO::FETCH_ASSOC) ?: null;
}
$contactFlash = $_SESSION['contact_flash'] ?? null;
unset($_SESSION['contact_flash']);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $FOOTER_ASSET_PREFIX = '';
    $tcf_brand_title = 'ELITE TCF CANADA — Préparation à l\'examen TCF Canada';
    include __DIR__ . '/includes/tcf_brand_head.php';
    ?>
    <link rel="stylesheet" href="<?php echo site_href('Assets/css/theme-vars.css'); ?>">
    <link rel="stylesheet" href="<?php echo site_href('Assets/css/header_footer.css'); ?>">
    <link rel="stylesheet" href="<?php echo site_href('Assets/css/tcf-brand-logo.css'); ?>">
    <link rel="stylesheet" href="<?php echo site_href('Assets/css/style_tcf.css'); ?>?v=tc-card-2">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/subscription_section.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/services-section.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/partners-section.css')); ?>?v=partners-3">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <style>
        @media (max-width: 1100px) {
            .pricing-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }
        @media (max-width: 350px) {
            .pricing-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Montserrat:ital,wght@0,400;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo site_href('Assets/css/index-home-mobile.css'); ?>">
    <title>ELITE TCF CANADA — Préparation à l'examen TCF Canada</title>
</head>

<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <!-----------------------------------debut configuration de la page d'acceuil------------------------------->
<section class="main-section">
    <div class="main-content">
        <div class="hero-copy">
        <h3>BIENVENUE DANS NOTRE SITE! <br>De Préparation au <br><span> TCF CANADA </span></h3>
        <p>Nous sommes heureux de vous accueillir et de vous accompagner dans votre préparation à 
            votre examen. N'hésitez pas à explorer nos ressources, poser 
            des questions et interagir avec notre communauté. Nous sommes là
            pour vous aider à réussir votre examen. Bonne visite et bonne 
            préparation !</p>
        <a href="support.php" class="info-button"><span></span>Plus d'Info</a>
        </div>
        <div class="image-gallery">
            <img src="<?php echo site_href('Assets/IMAGE/home/canada1.jpg'); ?>" alt="TCF Canada" class="canada-image" width="280" height="280" fetchpriority="high" decoding="async">
        </div>
    </div>
</section>

<?php /* Bloc "Votre accès aux ressources / Gérer mon abonnement" supprimé à la demande */ ?>

<!-- Début configuration de la section à propos -->
<section class="tcf-container" id="formations">
    <div class="tcf-wrapper">
                            
        <h4 class="tcf-main-title"><i class='bx bxs-school'></i> NOS FORMATIONS</h4>
        
        <div class="tcf-card">
            <!-- Image en arrière-plan -->
            <div class="tcf-bg-image">
                <img src="<?php echo site_href('Assets/IMAGE/home/canada.jpg'); ?>" alt="TCF Canada Formation" loading="lazy">
                <div class="tcf-image-overlay"></div>
            </div>
            
            <div class="tcf-content">
                <h2 class="tcf-title">PRÉPARATION AU <span>TCF CANADA</span></h2>
                <p class="tcf-subtitle">Cette formation est conçue pour couvrir les quatre épreuves</p>
                
                <div class="tcf-skills-grid">
                    <div class="tcf-skills-row">
                        <a href="Expresion_ecrite.php"><button class="tcf-skill-btn" style="animation-delay: 0.1s">
                            <i class='bx bx-edit-alt'></i>
                            Expression Écrite
                        </button></a>
                        <a href="Expresion_orale.php"><button class="tcf-skill-btn" style="animation-delay: 0.2s">
                            <i class='bx bx-message-dots'></i>
                            Expression Orale
                        </button></a>
                    </div>

                    <div class="tcf-skills-row">
                        <a href="comprehesion_ecrite.php"><button class="tcf-skill-btn" style="animation-delay: 0.3s">
                            <i class='bx bx-book-alt'></i>
                            Compréhension Écrite
                        </button></a>
                        <a href="comprehension_orale.php"><button class="tcf-skill-btn" style="animation-delay: 0.4s">
                            <i class='bx bx-headphone'></i>
                            Compréhension Orale
                        </button></a>
                    </div>
                </div>

                <div class="tcf-cta">
                    <a href="#" class="tcf-cta-btn">
                        Programme de la Formation
                        <i class='bx bx-chevron-right'></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Services -->
<section id="services" class="tcf-services" aria-labelledby="tcf-services-title">
    <div class="tcf-wrapper">
        <h4 class="tcf-main-title" id="tcf-services-title"><i class='bx bxs-school'></i> NOS SERVICES</h4>

        <div class="tcf-services__layout">
            <figure class="tcf-services__visual">
                <img src="<?php echo site_href('Assets/IMAGE/services/5 (2).jpg'); ?>" alt="Accompagnement et préparation TCF Canada" loading="lazy">
                <figcaption class="tcf-services__badge">
                    <i class='bx bx-check-shield' aria-hidden="true"></i>
                    4 piliers pour progresser efficacement
                </figcaption>
            </figure>

            <div class="tcf-services__grid">
                <article class="tcf-service-card">
                    <div class="tcf-service-card__icon" aria-hidden="true">
                        <i class='bx bx-group'></i>
                    </div>
                    <div class="tcf-service-card__body">
                        <h3 class="tcf-service-card__title">Cours en ligne</h3>
                        <p class="tcf-service-card__text">Parcours structurés couvrant l’ensemble des compétences évaluées aux quatre épreuves du TCF Canada.</p>
                    </div>
                </article>

                <article class="tcf-service-card">
                    <div class="tcf-service-card__icon" aria-hidden="true">
                        <i class='bx bx-play-circle'></i>
                    </div>
                    <div class="tcf-service-card__body">
                        <h3 class="tcf-service-card__title">Tutorat en ligne</h3>
                        <p class="tcf-service-card__text">Séances individuelles avec des tuteurs expérimentés pour cibler vos points faibles et gagner en confiance.</p>
                    </div>
                </article>

                <article class="tcf-service-card">
                    <div class="tcf-service-card__icon" aria-hidden="true">
                        <i class='bx bx-medal'></i>
                    </div>
                    <div class="tcf-service-card__body">
                        <h3 class="tcf-service-card__title">Évaluations</h3>
                        <p class="tcf-service-card__text">Tests et simulations pour vous familiariser avec le format officiel et mesurer vos progrès en conditions réelles.</p>
                    </div>
                </article>

                <article class="tcf-service-card">
                    <div class="tcf-service-card__icon" aria-hidden="true">
                        <i class='bx bx-refresh'></i>
                    </div>
                    <div class="tcf-service-card__body">
                        <h3 class="tcf-service-card__title">Mises à jour</h3>
                        <p class="tcf-service-card__text">Contenus actualisés en permanence pour rester alignés avec les exigences et évolutions du TCF Canada.</p>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>

    <?php include __DIR__ . '/includes/subscription_plans_section.php'; ?>

    <?php include __DIR__ . '/includes/partners_section.php'; ?>

  <!-- DEBUT configuration play button -->
  <section class="play">
    <a href="#">
        <div class="play-btn">
            <i class="bx bx-play"></i>
        </div>
    </a>
  </section>

<!-- Témoignages (bandeau clair, même logique que formations / alternance) -->
<section class="tcf-section-block tcf-section-testimonials" id="temoignages" aria-labelledby="tcf-testimonials-title">
    <div class="tcf-section-inner">
        <div class="subscription-header">
            <h4 class="section-subtitle tcf-sub-kicker" id="tcf-testimonials-kicker"><i class='bx bxs-school'></i> TÉMOIGNAGES</h4>
            <h2 class="section-title tfc-sub-main-title" id="tcf-testimonials-title">Ils nous <span class="tcf-sub-accent">font confiance</span> — laissez le <span class="tcf-sub-accent">vôtre</span> après votre passage sur le site</h2>
            <div class="tcf-sub-title-bar" aria-hidden="true"></div>
        </div>
        <div class="tcf-tc-carousel-wrap">
            <div class="tcf-tc-viewport">
                <div class="tcf-tc-track" id="tcf-tc-track" role="region" aria-roledescription="carrousel" aria-label="Témoignages">
                    <?php if (count($testimonialsHome) === 0): ?>
                    <div class="tcf-tc-page tcf-tc-page--empty">
                        <div class="tcf-tc-slide tcf-tc-empty" id="tcf-tc-slide-empty">Aucun témoignage pour le moment. Soyez le premier à en laisser un !</div>
                    </div>
                    <?php else: ?>
                    <?php
                    $tcfTcPages = array_chunk($testimonialsHome, 2);
                    foreach ($tcfTcPages as $pageItems):
                        $tcfTcPageSingle = count($pageItems) === 1;
                    ?>
                    <div class="tcf-tc-page<?php echo $tcfTcPageSingle ? ' tcf-tc-page--single' : ''; ?>">
                        <?php foreach ($pageItems as $tm): ?>
                        <?php
                        $tcfAuthorName = trim((string) ($tm['author_name'] ?? 'Visiteur'));
                        $tcfDateLabel = '';
                        if (!empty($tm['created_at'])) {
                            $tcfTs = strtotime((string) $tm['created_at']);
                            if ($tcfTs !== false) {
                                $tcfDateLabel = date('d/m/Y', $tcfTs);
                            }
                        }
                        $tcfAvatarUrl = null;
                        if (!empty($tm['user_id'])) {
                            $syncedAvatar = tcf_sync_user_avatar_from_disk($pdo, (int) $tm['user_id'], $tm['user_avatar'] ?? null);
                            $tcfAvatarUrl = tcf_avatar_public_url($syncedAvatar);
                        }
                        $tcfRating = isset($tm['rating']) ? (int) $tm['rating'] : 0;
                        if ($tcfRating < 0 || $tcfRating > 5) {
                            $tcfRating = 0;
                        }
                        ?>
                        <article class="tcf-tc-slide">
                            <div class="tcf-tc-wave-card">
                                <div class="tcf-tc-wave-card__white">
                                    <div class="tcf-tc-wave-card__grid">
                                        <div class="tcf-tc-wave-card__profile">
                                            <div class="tcf-tc-wave-card__photo-ring">
                                                <?php if ($tcfAvatarUrl): ?>
                                                <img class="tcf-tc-wave-card__photo" src="<?php echo htmlspecialchars($tcfAvatarUrl); ?>" alt="" width="120" height="120" loading="lazy" decoding="async">
                                                <?php else: ?>
                                                <div class="tcf-tc-wave-card__photo tcf-tc-wave-card__photo--fallback" aria-hidden="true">
                                                    <i class="bx bx-user"></i>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="tcf-tc-wave-card__stars-bar" aria-label="<?php echo $tcfRating > 0 ? $tcfRating . ' sur 5' : 'Pas de note'; ?>">
                                                <?php for ($tcfSi = 1; $tcfSi <= 5; $tcfSi++): ?>
                                                <i class="bx <?php echo ($tcfRating > 0 && $tcfSi <= $tcfRating) ? 'bxs-star' : 'bx-star'; ?>" aria-hidden="true"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <p class="tcf-tc-wave-card__name"><?php echo htmlspecialchars($tcfAuthorName); ?></p>
                                            <p class="tcf-tc-wave-card__job">Candidat TCF Canada</p>
                                        </div>
                                        <div class="tcf-tc-wave-card__copy">
                                            <p class="tcf-tc-wave-card__script">Clients</p>
                                            <p class="tcf-tc-wave-card__headline">Témoignage</p>
                                            <div class="tcf-tc-wave-card__quote-inner">
                                                <span class="tcf-tc-wave-card__qm tcf-tc-wave-card__qm--open" aria-hidden="true">“</span>
                                                <div class="tcf-tc-wave-card__quote-body">
                                                    <p class="tcf-tc-wave-card__quote"><?php echo nl2br(htmlspecialchars((string) $tm['content'])); ?></p>
                                                </div>
                                                <span class="tcf-tc-wave-card__qm tcf-tc-wave-card__qm--close" aria-hidden="true">”</span>
                                            </div>
                                            <div class="tcf-tc-wave-card__attrib">
                                                <span class="tcf-tc-wave-card__attrib-lines" aria-hidden="true"><i></i><i></i></span>
                                                <span class="tcf-tc-wave-card__attrib-text"><?php echo $tcfDateLabel !== '' ? htmlspecialchars($tcfDateLabel) : 'Candidat TCF'; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <svg class="tcf-tc-wave-card__wave" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 56" preserveAspectRatio="none" aria-hidden="true">
                                    <path fill="currentColor" d="M0,28 C200,6 400,46 600,26 C800,4 1000,44 1200,22 L1200,56 L0,56 Z"/>
                                </svg>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (count($testimonialsHome) > 1): ?>
            <div class="tcf-tc-nav" id="tcf-tc-nav">
                <button type="button" id="tcf-tc-prev" aria-label="Témoignage précédent"><i class="bx bx-chevron-left"></i></button>
                <button type="button" id="tcf-tc-next" aria-label="Témoignage suivant"><i class="bx bx-chevron-right"></i></button>
            </div>
            <div class="tcf-tc-dots" id="tcf-tc-dots" aria-hidden="true"></div>
            <?php endif; ?>
        </div>
        <?php if (!empty($_SESSION['user_id'])): ?>
        <form class="tcf-tc-form" id="tcf-testimonial-form" novalidate>
            <h3>Laisser un témoignage</h3>
            <div class="tcf-tc-field">
                <label for="tcf-tc-name">Votre nom</label>
                <input type="text" id="tcf-tc-name" name="author_name" maxlength="120" required placeholder="Prénom ou pseudo" autocomplete="name" value="<?php echo !empty($_SESSION['username']) ? htmlspecialchars((string) $_SESSION['username']) : ''; ?>">
            </div>
            <div class="tcf-tc-field">
                <label for="tcf-tc-rating">Note (optionnel)</label>
                <select id="tcf-tc-rating" name="rating">
                    <option value="0">—</option>
                    <option value="5">5 — Excellent</option>
                    <option value="4">4 — Très bien</option>
                    <option value="3">3 — Bien</option>
                    <option value="2">2 — Moyen</option>
                    <option value="1">1 — Insuffisant</option>
                </select>
            </div>
            <div class="tcf-tc-field">
                <label for="tcf-tc-content">Votre message</label>
                <textarea id="tcf-tc-content" name="content" maxlength="350" required placeholder="Votre message (10 à 350 caractères)" rows="5"></textarea>
            </div>
            <button type="submit" class="tcf-tc-submit">Publier mon témoignage</button>
            <p class="tcf-tc-form-msg" id="tcf-tc-form-msg" role="status"></p>
        </form>
        <?php endif; ?>
    </div>
</section>

<!-- Contact : coordonnées + formulaire côte à côte -->
<section class="tcf-section-block tcf-section-contact" id="contact" aria-label="Contact TCF Canada">
    <div class="tcf-section-inner tcf-contact-split">
        <div class="tcf-contact-col tcf-contact-col--info">
            <h4 class="tcf-main-title tcf-section-contact__title"><i class='bx bxs-school'></i> NOUS CONTACTER</h4>
            <p class="tcf-section-lead tcf-section-lead--on-dark">Une question ou un partenariat ? Écrivez-nous ou utilisez les coordonnées ci-contre.</p>
            <?php if (!empty($contactFlash)): ?>
            <div class="tcf-contact-flash tcf-contact-flash--<?php echo $contactFlash['type'] === 'ok' ? 'ok' : 'err'; ?>" role="status"><?php echo htmlspecialchars((string) ($contactFlash['text'] ?? '')); ?></div>
            <?php endif; ?>
            <div class="tcf-contact-cards">
                <div class="tcf-contact-info-card">
                    <h5><i class="bx bxs-envelope"></i> E-mail</h5>
                    <p><a href="<?php echo htmlspecialchars(tcf_site_mailto($tcf_index_contact)); ?>"><?php echo htmlspecialchars($tcf_index_contact['email']); ?></a></p>
                </div>
                <div class="tcf-contact-info-card">
                    <h5><i class="bx bxs-phone-call"></i> Téléphone</h5>
                    <p><a href="<?php echo htmlspecialchars(tcf_site_tel($tcf_index_contact)); ?>"><?php echo htmlspecialchars($tcf_index_contact['phone_display']); ?></a></p>
                </div>
                <div class="tcf-contact-info-card">
                    <h5><i class="bx bxs-time"></i> Horaires</h5>
                    <p><?php echo htmlspecialchars($tcf_index_contact['hours']); ?></p>
                </div>
                <div class="tcf-contact-info-card">
                    <h5><i class="bx bxs-map"></i> Adresse</h5>
                    <p><?php echo htmlspecialchars($tcf_index_contact['address']); ?></p>
                </div>
            </div>
            <p class="tcf-contact-support-note">Besoin d’aide sur le site ? Consultez aussi la page <a href="<?php echo htmlspecialchars(site_href('support.php')); ?>">Support</a>.</p>
        </div>
        <div class="tcf-contact-col tcf-contact-col--form">
            <form class="tcf-contact-form" method="post" action="<?php echo htmlspecialchars(site_href('contact_submit.php')); ?>" novalidate>
                <h3 class="tcf-contact-form__title">Envoyer un message</h3>
                <div class="tcf-contact-field">
                    <label for="tcf-contact-name">Nom <span aria-hidden="true">*</span></label>
                    <input type="text" id="tcf-contact-name" name="name" maxlength="200" required autocomplete="name" >
                </div>
                <div class="tcf-contact-field">
                    <label for="tcf-contact-email">E-mail <span aria-hidden="true">*</span></label>
                    <input type="email" id="tcf-contact-email" name="email" required autocomplete="email">
                </div>
                <div class="tcf-contact-field">
                    <label for="tcf-contact-subject">Sujet</label>
                    <input type="text" id="tcf-contact-subject" name="subject" maxlength="200">
                </div>
                <div class="tcf-contact-field">
                    <label for="tcf-contact-message">Message <span aria-hidden="true">*</span></label>
                    <textarea id="tcf-contact-message" name="message" required maxlength="8000" rows="6" ></textarea>
                </div>
                <div class="tcf-contact-hp" aria-hidden="true">
                    <label for="tcf-contact-hp">Ne pas remplir ce champ</label>
                    <input type="text" id="tcf-contact-hp" name="website" tabindex="-1" autocomplete="off">
                </div>
                <button type="submit" class="tcf-contact-submit">Envoyer</button>
            </form>
        </div>
    </div>
</section>

<script>
(function () {
    var track = document.getElementById('tcf-tc-track');
    if (!track) return;

    var BACKUP = track.innerHTML;
    /* Sous ~900px : une carte/page (format carte de visite). Au-delà : 2 cartes côte à côte. */
    var MOBILE_MQ = window.matchMedia('(max-width: 899px)');
    var state = { i: 0, timer: null, go: null, n: 0 };

    function debounce(fn, ms) {
        var t;
        return function () {
            clearTimeout(t);
            t = setTimeout(fn, ms);
        };
    }

    function flattenForMobile() {
        var pages = Array.prototype.slice.call(track.querySelectorAll('.tcf-tc-page'));
        var frag = document.createDocumentFragment();
        var emptyPage = null;
        pages.forEach(function (page) {
            if (page.classList.contains('tcf-tc-page--empty')) {
                emptyPage = page;
                return;
            }
            Array.prototype.forEach.call(page.querySelectorAll('.tcf-tc-slide'), function (slide) {
                var wrap = document.createElement('div');
                wrap.className = 'tcf-tc-page tcf-tc-page--one';
                wrap.appendChild(slide);
                frag.appendChild(wrap);
            });
        });
        track.innerHTML = '';
        if (emptyPage) {
            track.appendChild(emptyPage);
        }
        while (frag.firstChild) {
            track.appendChild(frag.firstChild);
        }
    }

    function restoreDesktop() {
        track.innerHTML = BACKUP;
    }

    function bindCarousel() {
        if (state.timer) {
            clearInterval(state.timer);
            state.timer = null;
        }
        var pages = track.querySelectorAll('.tcf-tc-page');
        var n = pages.length;
        var nav = document.getElementById('tcf-tc-nav');
        var dotsEl = document.getElementById('tcf-tc-dots');
        track.style.transform = '';

        if (nav) {
            nav.style.display = n <= 1 ? 'none' : '';
        }
        if (dotsEl) {
            dotsEl.innerHTML = '';
            dotsEl.style.display = n <= 1 ? 'none' : '';
        }

        if (n <= 1) {
            state.i = 0;
            state.n = n;
            track.style.transform = '';
            return;
        }

        state.i = 0;
        state.n = n;

        function applySlide() {
            var vp = track.parentElement;
            if (!vp) return;
            /* Une page = largeur du viewport (pas translateX(-i*100%) qui est % de la piste entière) */
            var w = vp.clientWidth || vp.offsetWidth;
            track.style.transform = 'translateX(' + (-state.i * w) + 'px)';
            if (dotsEl) {
                Array.prototype.forEach.call(dotsEl.querySelectorAll('button'), function (d, k) {
                    d.classList.toggle('is-active', k === state.i);
                });
            }
        }

        function go(idx) {
            state.i = ((idx % n) + n) % n;
            applySlide();
        }
        state.go = go;

        if (dotsEl) {
            for (var d = 0; d < n; d++) {
                var b = document.createElement('button');
                b.type = 'button';
                b.setAttribute('aria-label', 'Témoignage ' + (d + 1));
                if (d === 0) b.classList.add('is-active');
                (function (idx) { b.addEventListener('click', function () { go(idx); }); })(d);
                dotsEl.appendChild(b);
            }
        }

        var prev = document.getElementById('tcf-tc-prev');
        var next = document.getElementById('tcf-tc-next');
        if (prev) {
            prev.onclick = function () { go(state.i - 1); };
        }
        if (next) {
            next.onclick = function () { go(state.i + 1); };
        }

        requestAnimationFrame(function () {
            requestAnimationFrame(applySlide);
        });

        state.timer = setInterval(function () {
            go(state.i + 1);
        }, 8000);
    }

    window.addEventListener('resize', debounce(function () {
        if (typeof state.go === 'function' && state.n > 1) {
            state.go(state.i);
        }
    }, 120));

    function applyLayout() {
        var mobile = MOBILE_MQ.matches;
        if (mobile && track.dataset.tcfLayout !== 'mobile') {
            restoreDesktop();
            flattenForMobile();
            track.dataset.tcfLayout = 'mobile';
            bindCarousel();
        } else if (!mobile && track.dataset.tcfLayout === 'mobile') {
            restoreDesktop();
            delete track.dataset.tcfLayout;
            bindCarousel();
        }
    }

    if (MOBILE_MQ.matches) {
        flattenForMobile();
        track.dataset.tcfLayout = 'mobile';
    }
    bindCarousel();
    window.addEventListener('resize', debounce(applyLayout, 180));
})();

(function () {
    var form = document.getElementById('tcf-testimonial-form');
    var msg = document.getElementById('tcf-tc-form-msg');
    if (!form) return;
    var api = <?php echo json_encode(site_href('testimonials_api.php')); ?>;
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (msg) { msg.textContent = ''; msg.className = 'tcf-tc-form-msg'; }
        var fd = new FormData(form);
        var body = {
            action: 'add',
            author_name: (fd.get('author_name') || '').trim(),
            content: (fd.get('content') || '').trim(),
            rating: parseInt(fd.get('rating') || '0', 10) || 0
        };
        if (body.content.length < 10) {
            if (msg) { msg.textContent = 'Le message doit contenir au moins 10 caractères.'; msg.className = 'tcf-tc-form-msg err'; }
            return;
        }
        if (body.content.length > 350) {
            if (msg) { msg.textContent = 'Le message ne peut pas dépasser 350 caractères.'; msg.className = 'tcf-tc-form-msg err'; }
            return;
        }
        fetch(api, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d && d.ok) {
                    if (msg) { msg.textContent = d.message || 'Merci !'; msg.className = 'tcf-tc-form-msg ok'; }
                    form.reset();
                    window.location.reload();
                } else {
                    if (msg) { msg.textContent = (d && d.message) ? d.message : 'Erreur.'; msg.className = 'tcf-tc-form-msg err'; }
                }
            })
            .catch(function () {
                if (msg) { msg.textContent = 'Erreur réseau.'; msg.className = 'tcf-tc-form-msg err'; }
            });
    });
})();
</script>

 <!--debut configuration du FOOTER -----------------------------------------------------------------------> 

 <?php include __DIR__ . '/includes/footer.php'; ?>
 <?php include __DIR__ . '/includes/cookie_banner.php'; ?>

 <!--SWIPER JS LINK-->
 <script src="<?php echo site_href('Assets/javascript/script_tcf.js'); ?>"></script>
    <script>
        window.TCF_SUBSCRIBE_ENDPOINT = <?php echo json_encode(site_href('subscribe_api.php')); ?>;
        window.TCF_PAYMENT_ENDPOINT = <?php echo json_encode(site_href('payment_api.php')); ?>;
        window.TCF_LOGIN_URL = <?php echo json_encode(site_href('login.php')); ?>;
        window.TCF_SUBSCRIBE_LOGGED_IN = <?php echo !empty($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        window.TCF_SUBSCRIBE_RETURN_PATH = <?php echo json_encode('abonnement.php'); ?>;
    </script>
    <script src="<?php echo htmlspecialchars(site_href('Assets/javascript/payment_modal.js')); ?>?v=<?php echo filemtime(__DIR__ . '/Assets/javascript/payment_modal.js'); ?>"></script>
    <script>
        (function() {
            var grid = document.querySelector('.pricing-grid');
            var cards = document.querySelectorAll('[data-responsive-card="true"]');
            
            function updateCardsLayout() {
                if (window.innerWidth >= 1100) {
                    // Desktop : 4 colonnes
                    cards.forEach(function(card) {
                        card.style.flex = '0 0 calc(25% - 0.75rem)';
                        card.style.maxWidth = '280px';
                    });
                } else {
                    // Mobile/tablette : 2 colonnes
                    cards.forEach(function(card) {
                        card.style.flex = '0 0 calc(50% - 0.5rem)';
                        card.style.maxWidth = '400px';
                    });
                }
            }
            
            if (grid && cards.length > 0) {
                updateCardsLayout();
                window.addEventListener('resize', updateCardsLayout);
            }
        })();
    </script>
</body>

</html>
