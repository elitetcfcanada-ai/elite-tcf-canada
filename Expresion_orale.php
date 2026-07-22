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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $tcf_brand_title = 'Expression Orale — ELITE TCF CANADA';
    include __DIR__ . '/includes/tcf_brand_head.php';
    ?>
    <title>Expression Orale — ELITE TCF CANADA</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="Assets/css/theme-vars.css">
    <link rel="stylesheet" href="Assets/css/header_footer.css">
    <link rel="stylesheet" href="Assets/css/style_tcf.css">
    <link rel="stylesheet" href="Assets/css/style_sujets.css?v=consigne-header-2">
    <link rel="stylesheet" href="Assets/css/style_Expresion_Ecrite.css?v=consigne-header-2">
    <link rel="stylesheet" href="Assets/css/expression_orale.css?v=consigne-header-2">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<section class="hero-banner">
    <div class="hero-content">
        <p class="hero-kicker"><i class='bx bxs-school'></i> Épreuves</p>
        <h1 class="hero-skill-title" style="color:#d30d0d!important;-webkit-text-fill-color:#d30d0d!important;">Expression orale</h1>
        <p class="hero-motto">Bonne <span>préparation</span></p>
        <p class="hero-lead">Ouvrez une épreuve pour vous entraîner. Les corrections restent masquées jusqu’à votre clic.</p>
    </div>
</section>

<div class="consigne-container">
    <button class="consigne-btn is-active" id="epreuveBtn"><i class='bx bx-book-open'></i> Épreuve</button>
    <button class="consigne-btn" id="consigneBtn"><i class='bx bx-info-circle'></i> Consignes</button>
</div>

<section class="section_epreuve" id="eo-exams-section">
    <div class="row_arrangement" id="eo-exams-list"></div>
</section>

<section class="container" id="eo-consignes" style="display:none;">
    <header class="eo-exam-page-header">
        <div class="header-content">
            <h1>Consignes Expression Orale</h1>
            <p class="subtitle">Cliquez sur une tâche pour dérouler la consigne</p>
        </div>
    </header>
    <div class="container"><div id="eo-consignes-container"></div></div>
</section>


<?php include __DIR__ . '/includes/footer.php'; ?>
<?php include __DIR__ . '/includes/cookie_banner.php'; ?>
<script src="Assets/javascript/script_tcf.js"></script>
<script>
(function () {
    var api = <?php echo json_encode(site_href('eo_api.php')); ?>;
    var viewer = <?php echo $viewer ? 'true' : 'false'; ?>;
    var premiumOk = <?php echo $premiumOk ? 'true' : 'false'; ?>;
    var epreuveBtn = document.getElementById('epreuveBtn');
    var consigneBtn = document.getElementById('consigneBtn');
    var examsSection = document.getElementById('eo-exams-section');
    var consigneSection = document.getElementById('eo-consignes');
    var examsList = document.getElementById('eo-exams-list');
    var consignesRoot = document.getElementById('eo-consignes-container');
    var loginBase = <?php echo json_encode(site_href('login.php')); ?>;
    var abonnementUrl = <?php echo json_encode(site_href('abonnement.php')); ?>;
    var readBase = <?php echo json_encode(site_href('epreuve_eo.php')); ?>;

    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (m) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]; }); }
    function post(action, fields) {
        var fd = new FormData();
        fd.append('action', action);
        Object.keys(fields || {}).forEach(function (k) { fd.append(k, fields[k]); });
        return fetch(api, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); });
    }
    function setTopActionActive(target) {
        if (epreuveBtn) epreuveBtn.classList.toggle('is-active', target === 'epreuve');
        if (consigneBtn) consigneBtn.classList.toggle('is-active', target === 'consigne');
    }
    function showOnly(target) {
        if (examsSection) examsSection.style.display = target === 'epreuve' ? 'block' : 'none';
        if (consigneSection) consigneSection.style.display = target === 'consigne' ? 'block' : 'none';
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
        var tasks = [
            { key: 'tache1', title: 'Tâche 1 : Présentation (entretien dirigé)', meta: '2 minutes • 3/20 points' },
            { key: 'tache2', title: 'Tâche 2 : Exercice en interaction', meta: '2 min préparation + 3 min 30 dialogue • 7/20 points' },
            { key: 'tache3', title: 'Tâche 3 : Expression d’un point de vue', meta: '4 min 30 • 10/20 points' }
        ];
        var byKey = {};
        (rows || []).forEach(function (r) {
            if (r && r.task_key) byKey[r.task_key] = r;
        });
        var html = tasks.map(function (t, idx) {
            var c = byKey[t.key];
            var title = (c && c.title) ? String(c.title) : t.title;
            var body = (c && c.body)
                ? String(c.body)
                : '<p class="tcf-consigne-empty">Aucune consigne publiée pour cette tâche.</p>';
            return '<div class="combinaison' + (idx === 0 ? ' active' : '') + '" id="consigne-' + t.key + '" data-task="' + t.key + '">' +
                '<div class="combinaison-header" role="button" tabindex="0">' +
                '<div><h2>' + esc(title) + '</h2></div>' +
                '<span class="icon">▼</span></div>' +
                '<div class="combinaison-content"' + (idx === 0 ? ' style="display:block;"' : '') + '>' +
                '<p class="ee-consigne-meta ee-consigne-meta--in-body">' + esc(t.meta) + '</p>' +
                body + '</div></div>';
        }).join('');

        html += '<div class="combinaison" id="consigne-criteres">' +
            '<div class="combinaison-header" role="button" tabindex="0">' +
            '<div><h2>Critères d’évaluation & conseils</h2></div>' +
            '<span class="icon">▼</span></div>' +
            '<div class="combinaison-content">' +
            <?php
            require_once __DIR__ . '/includes/tcf_consignes_defaults.php';
            echo json_encode(tcf_consigne_eo_criteria_html(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
            ?> +
            '</div></div>';

        consignesRoot.innerHTML = html;
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
                examsList.innerHTML = "<div class='column_arrangement'><h5>Aucune épreuve publiée.</h5><i class='bx bx-info-circle'></i></div>";
                return;
            }
            examsList.innerHTML = rows.map(function (r) {
                var isPremiumExam = String(r.visibility || 'gratuit') === 'premium';
                var locked = isPremiumExam && (!viewer || !premiumOk);
                var id = Number(r.id || 0);
                return '<button type="button" class="column_arrangement ee-exam-item' + (locked ? ' non_valide' : '') + '" data-id="' + esc(id) + '" data-locked="' + (locked ? '1' : '0') + '">' +
                    '<h5 class="ee-exam-title">' + esc(r.title || 'Épreuve') + '</h5>' +
                    '<i class="bx ' + (locked ? 'bx-lock' : 'bx-lock-open') + '"></i></button>';
            }).join('');
            examsList.querySelectorAll('.ee-exam-item').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var id = Number(btn.getAttribute('data-id') || 0);
                    var locked = (btn.getAttribute('data-locked') || '') === '1';
                    if (locked) {
                        window.location.href = viewer
                            ? abonnementUrl
                            : (loginBase + (loginBase.indexOf('?') >= 0 ? '&' : '?') + 'next=' + encodeURIComponent('epreuve_eo.php?id=' + id));
                        return;
                    }
                    window.location.href = readBase + (readBase.indexOf('?') >= 0 ? '&' : '?') + 'id=' + id;
                });
            });
        });
    }

    if (epreuveBtn) epreuveBtn.addEventListener('click', function () { showOnly('epreuve'); });
    if (consigneBtn) consigneBtn.addEventListener('click', function () { showOnly('consigne'); });

    loadExams();
    post('get_consignes', {}).then(function (j) { renderConsignes((j && j.success && j.data) ? j.data : []); });
    showOnly('epreuve');
})();
</script>
</body>
</html>