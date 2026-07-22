<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/subscription_access.php';
$viewer = null;
$premiumOk = false;
if (!empty($_SESSION['user_id'])) {
    $st = $pdo->prepare('SELECT * FROM users WHERE id=?');
    $st->execute([(int) $_SESSION['user_id']]);
    $viewer = $st->fetch(PDO::FETCH_ASSOC) ?: null;
    $premiumOk = $viewer ? tcf_user_has_premium_access($viewer) : false;
}
$ceApi = site_href('ce_api.php');
$quizBase = site_href('comprehesion_ecrite_quiz.php');
$loginUrl = site_href('login.php');
$aboUrl = site_href('abonnement.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $tcf_brand_title = 'Compréhension Écrite — ELITE TCF CANADA';
    include __DIR__ . '/includes/tcf_brand_head.php';
    ?>
    <title>Compréhension Écrite — ELITE TCF CANADA</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="Assets/css/theme-vars.css">
    <link rel="stylesheet" href="Assets/css/header_footer.css">
    <link rel="stylesheet" href="Assets/css/style_tcf.css">
    <link rel="stylesheet" href="Assets/css/style_sujets.css?v=no-glow-1">
    <link rel="stylesheet" href="Assets/css/style_Expresion_Ecrite.css?v=ce-consigne-combo">
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<section class="hero-banner">
    <div class="hero-content">
        <p class="hero-kicker"><i class='bx bxs-school'></i> Épreuves</p>
        <h1 class="hero-skill-title" style="color:#d30d0d!important;-webkit-text-fill-color:#d30d0d!important;">Compréhension écrite</h1>
        <p class="hero-motto">Bonne <span>préparation</span></p>
        <p class="hero-lead">Entraînez-vous à la lecture et aux QCM sur des textes variés.</p>
    </div>
</section>

<div class="consigne-container">
    <button type="button" class="consigne-btn is-active" id="ce-epreuve-btn"><i class='bx bx-book-open'></i> Épreuve</button>
    <button type="button" class="consigne-btn" id="ce-consigne-btn"><i class='bx bx-info-circle'></i> Consignes</button>
</div>

<div id="ce-lock-msg" style="display:none;max-width:860px;margin:0 auto 1rem;padding:1rem 1.2rem;border-radius:12px;background:#fff3f3;border:1px solid rgba(211,13,13,.35);">
    <strong>Accès premium requis.</strong>
    <p style="margin:.4rem 0 0;">
        <?php if (empty($_SESSION['user_id'])): ?>
            Connectez-vous puis activez un abonnement pour ouvrir cette épreuve.
            <a href="<?php echo htmlspecialchars($loginUrl); ?>">Connexion</a> ·
            <a href="<?php echo htmlspecialchars($aboUrl); ?>">Abonnement</a>
        <?php else: ?>
            Votre abonnement n’est pas actif. Activez votre formule pour accéder à cette épreuve.
            <a href="<?php echo htmlspecialchars($aboUrl); ?>">Voir les formules</a>
        <?php endif; ?>
    </p>
</div>

<section class="section_epreuve" id="ce-exams-section">
    <div class="row_arrangement" id="ce-exams-list">
        <div class="column_arrangement"><h5>Chargement des épreuves…</h5><i class='bx bx-loader-alt bx-spin'></i></div>
    </div>
</section>

<section class="container" id="ce-consignes-section" style="display:none;">
    <header>
        <div class="header-content">
            <h1>Consignes Compréhension Écrite</h1>
            <p class="subtitle">Astuces et techniques pour maximiser votre score</p>
        </div>
    </header>
    <div class="container"><div id="ce-consignes-container"></div></div>
</section>

<a href="#" class="scrollbtn"><i class="bx bxs-chevrons-up"></i></a>

<?php include __DIR__ . '/includes/footer.php'; ?>
<?php include __DIR__ . '/includes/cookie_banner.php'; ?>

<script src="Assets/javascript/sujet.js"></script>
<script src="Assets/javascript/script_tcf.js"></script>
<script>
(function () {
    var api = <?php echo json_encode($ceApi); ?>;
    var quizBase = <?php echo json_encode($quizBase); ?>;
    var loginUrl = <?php echo json_encode($loginUrl); ?>;
    var aboUrl = <?php echo json_encode($aboUrl); ?>;
    var viewer = <?php echo $viewer ? 'true' : 'false'; ?>;
    var premiumOk = <?php echo $premiumOk ? 'true' : 'false'; ?>;
    var listEl = document.getElementById('ce-exams-list');
    var lockMsg = document.getElementById('ce-lock-msg');
    var epreuveBtn = document.getElementById('ce-epreuve-btn');
    var consigneBtn = document.getElementById('ce-consigne-btn');
    var examsSection = document.getElementById('ce-exams-section');
    var consigneSection = document.getElementById('ce-consignes-section');
    var consignesRoot = document.getElementById('ce-consignes-container');

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (m) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
        });
    }
    function post(action, fields) {
        var fd = new FormData();
        fd.append('action', action);
        Object.keys(fields || {}).forEach(function (k) {
            fd.append(k, fields[k]);
        });
        return fetch(api, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) {
            return r.json();
        });
    }

    function setTopActionActive(target) {
        if (epreuveBtn) epreuveBtn.classList.toggle('is-active', target === 'epreuve');
        if (consigneBtn) consigneBtn.classList.toggle('is-active', target === 'consigne');
    }
    function showOnly(target) {
        if (examsSection) examsSection.style.display = target === 'epreuve' ? 'block' : 'none';
        if (consigneSection) consigneSection.style.display = target === 'consigne' ? 'block' : 'none';
        if (lockMsg && target !== 'epreuve') lockMsg.style.display = 'none';
        setTopActionActive(target);
    }

    function bindComboUi(root) {
        (root || document).querySelectorAll('.combinaison-header').forEach(function (h) {
            h.addEventListener('click', function () {
                var combo = this.parentElement;
                if (!combo) return;
                var willOpen = !combo.classList.contains('active');
                (root || document).querySelectorAll('.combinaison').forEach(function (c) {
                    c.classList.remove('active');
                    var content = c.querySelector('.combinaison-content');
                    if (content) content.style.display = 'none';
                });
                if (willOpen) {
                    combo.classList.add('active');
                    var openContent = combo.querySelector('.combinaison-content');
                    if (openContent) openContent.style.display = 'block';
                }
            });
        });
    }

    function renderConsignes(rows) {
        if (!consignesRoot) return;
        var sections = [
            { key: 'structure', title: 'Structure de l’épreuve et stratégie de scoring', meta: '39 questions • 60 min • 699 points' },
            { key: 'techniques', title: 'Les 5 techniques essentielles', meta: 'Lecture, temps, structure, inférence, élimination' },
            { key: 'erreurs', title: 'Erreurs courantes à éviter', meta: 'Pièges fréquents le jour de l’examen' }
        ];
        var byKey = {};
        (rows || []).forEach(function (r) {
            var k = (r && (r.section_key || r.task_key)) ? String(r.section_key || r.task_key) : '';
            if (k) byKey[k] = r;
        });
        consignesRoot.innerHTML = sections.map(function (t, idx) {
            var c = byKey[t.key];
            var title = (c && c.title) ? String(c.title) : t.title;
            var body = (c && c.body)
                ? String(c.body)
                : '<p class="tcf-consigne-empty">Aucune consigne publiée pour cette section.</p>';
            return '<div class="combinaison' + (idx === 0 ? ' active' : '') + '" id="consigne-' + t.key + '" data-task="' + t.key + '">' +
                '<div class="combinaison-header" role="button" tabindex="0">' +
                '<div><h2>' + esc(title) + '</h2></div>' +
                '<span class="icon">▼</span></div>' +
                '<div class="combinaison-content"' + (idx === 0 ? ' style="display:block;"' : '') + '>' +
                '<p class="ee-consigne-meta ee-consigne-meta--in-body">' + esc(t.meta) + '</p>' +
                body + '</div></div>';
        }).join('');
        bindComboUi(consignesRoot);
        consignesRoot.querySelectorAll('.combinaison-header').forEach(function (h) {
            h.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    h.click();
                }
            });
        });
    }

    function loadExams() {
        post('get_exams_public', {}).then(function (j) {
            var rows = (j && j.success && Array.isArray(j.data)) ? j.data : [];
            if (!rows.length) {
                listEl.innerHTML = "<div class='column_arrangement'><h5>Aucune épreuve publiée pour le moment.</h5><i class='bx bx-info-circle'></i></div>";
                return;
            }
            listEl.innerHTML = rows.map(function (r) {
                var isPremiumExam = String(r.visibility || 'gratuit') === 'premium';
                var alwaysFree = !!r.always_free;
                var locked = isPremiumExam && !alwaysFree && (!viewer || !premiumOk);
                var sep = quizBase.indexOf('?') >= 0 ? '&' : '?';
                var href = quizBase + sep + 'exam_id=' + encodeURIComponent(String(r.id));
                var nextPath = 'comprehesion_ecrite_quiz.php?exam_id=' + encodeURIComponent(String(r.id));
                var lockedHref = viewer
                    ? aboUrl
                    : (loginUrl + (loginUrl.indexOf('?') >= 0 ? '&' : '?') + 'next=' + encodeURIComponent(nextPath));
                return '<div class="column_arrangement' + (locked ? ' non_valide' : '') + '">' +
                    (locked
                        ? '<a href="' + esc(lockedHref) + '" class="ce-locked-link" data-locked="1">' +
                            '<h5>' + esc(r.title || 'Épreuve') + '</h5></a>'
                        : '<a href="' + esc(href) + '"><h5>' + esc(r.title || 'Épreuve') + '</h5></a>') +
                    '<i class="bx ' + (locked ? 'bx-lock' : 'bx-lock-open') + '"></i></div>';
            }).join('');
        }).catch(function () {
            if (listEl) {
                listEl.innerHTML = "<div class='column_arrangement'><h5>Impossible de charger les épreuves.</h5><i class='bx bx-error'></i></div>";
            }
        });
    }

    if (epreuveBtn) epreuveBtn.addEventListener('click', function () { showOnly('epreuve'); });
    if (consigneBtn) consigneBtn.addEventListener('click', function () { showOnly('consigne'); });

    loadExams();
    post('get_consignes', {}).then(function (j) {
        renderConsignes((j && j.success && j.data) ? j.data : []);
    });
    showOnly('epreuve');
})();
</script>
</body>
</html>
