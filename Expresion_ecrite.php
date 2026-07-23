<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/subscription_access.php';

$viewer = null;
if (!empty($_SESSION['user_id'])) {
    $st = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $st->execute([(int) $_SESSION['user_id']]);
    $viewer = $st->fetch(PDO::FETCH_ASSOC) ?: null;
}
$premiumOk = tcf_user_has_premium_access($viewer);

function ee_title_rank_local(string $title): int
{
    $t = mb_strtolower($title);
    $months = [
        'janvier' => 1, 'janv' => 1,
        'fevrier' => 2, 'février' => 2, 'fevr' => 2, 'févr' => 2,
        'mars' => 3,
        'avril' => 4, 'avr' => 4,
        'mai' => 5,
        'juin' => 6,
        'juillet' => 7, 'juil' => 7,
        'aout' => 8, 'août' => 8,
        'septembre' => 9, 'sept' => 9,
        'octobre' => 10, 'oct' => 10,
        'novembre' => 11, 'nov' => 11,
        'decembre' => 12, 'décembre' => 12, 'dec' => 12, 'déc' => 12,
    ];
    $year = 0;
    if (preg_match('/(20\d{2})/u', $t, $m)) {
        $year = (int) $m[1];
    }
    $month = 0;
    foreach ($months as $label => $num) {
        if (mb_stripos($t, $label) !== false) {
            $month = $num;
            break;
        }
    }
    if ($year <= 0) {
        return 0;
    }
    return ($year * 100) + $month;
}

$exams = [];
try {
    $exams = $pdo->query(
        "SELECT id, slug, title, subtitle, visibility, published_at
         FROM tcf_ee_exams
         WHERE is_published = 1
           AND title NOT REGEXP 'Part[0-9]+|[[:space:]]Data$'
           AND slug NOT REGEXP 'part[0-9]+|_data'
         ORDER BY id DESC"
    )->fetchAll(PDO::FETCH_ASSOC);
    usort($exams, static function (array $a, array $b): int {
        $ra = ee_title_rank_local((string) ($a['title'] ?? ''));
        $rb = ee_title_rank_local((string) ($b['title'] ?? ''));
        if ($ra !== $rb) {
            return $rb <=> $ra;
        }
        return (int) ($b['id'] ?? 0) <=> (int) ($a['id'] ?? 0);
    });
} catch (Throwable $e) {
    $exams = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    $tcf_brand_title = 'Expression Écrite TCF Canada | ELITE TCF CANADA';
    $tcf_brand_desc = 'Préparez l\'expression écrite du TCF Canada avec ELITE TCF CANADA : sujets d\'entraînement, consignes et pratique pour réussir.';
    $tcf_brand_keywords = 'expression écrite TCF, TCF Canada EE, rédaction TCF Canada, ELITE TCF CANADA, sujets expression écrite, examen TCF IRCC';
    include __DIR__ . '/includes/tcf_brand_head.php';
    ?>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="Assets/css/theme-vars.css">
    <link rel="stylesheet" href="Assets/css/header_footer.css">
    <link rel="stylesheet" href="Assets/css/style_tcf.css">
    <link rel="stylesheet" href="Assets/css/style_sujets.css?v=consigne-header-2">
    <link rel="stylesheet" href="Assets/css/style_Expresion_Ecrite.css?v=consigne-header-2">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<section class="hero-banner">
    <div class="hero-content">
        <p class="hero-kicker"><i class='bx bxs-school'></i> Épreuves</p>
        <h1 class="hero-skill-title" style="color:#d30d0d!important;-webkit-text-fill-color:#d30d0d!important;">Expression écrite</h1>
        <p class="hero-motto">Bonne <span>préparation</span></p>
        <p class="hero-lead">Ouvrez une épreuve pour vous entraîner. Simulateur et corrections sont sur la page dédiée.</p>
    </div>
</section>

<div class="consigne-container">
    <button class="consigne-btn is-active" id="epreuveBtn" type="button"><i class='bx bx-book-open'></i> Épreuve</button>
    <button class="consigne-btn" id="consigneBtn" type="button"><i class='bx bx-info-circle'></i> Consignes</button>
</div>

<section class="container" id="ee-consignes" style="display:none;">
    <header>
        <div class="header-content">
            <h1>Consignes expression écrite</h1>
            <p class="subtitle">Cliquez sur une tâche pour dérouler la consigne</p>
        </div>
    </header>
    <div class="container">
        <div id="ee-consignes-container"></div>
    </div>
</section>

<section class="section_epreuve" id="ee-exams-section">
    <div class="row_arrangement" id="ee-exams-list">
        <?php if (empty($exams)): ?>
            <div class="column_arrangement"><h5>Aucune épreuve publiée pour le moment.</h5><i class='bx bx-info-circle'></i></div>
        <?php else: ?>
            <?php foreach ($exams as $exam): ?>
                <?php
                $examId = (int) ($exam['id'] ?? 0);
                $isPremiumExam = strtolower((string) ($exam['visibility'] ?? 'gratuit')) === 'premium';
                $locked = $isPremiumExam && (!$viewer || !$premiumOk);
                $readHref = site_href('epreuve_ee.php?id=' . $examId);
                $loginHref = site_href('login.php?next=' . rawurlencode('epreuve_ee.php?id=' . $examId));
                $subHref = site_href('abonnement.php');
                ?>
                <button type="button" class="column_arrangement<?php echo $locked ? ' non_valide' : ''; ?> ee-exam-item"
                        data-locked="<?php echo $locked ? '1' : '0'; ?>"
                        data-logged="<?php echo $viewer ? '1' : '0'; ?>"
                        data-href="<?php echo htmlspecialchars($readHref); ?>"
                        data-login="<?php echo htmlspecialchars($loginHref); ?>"
                        data-abonnement="<?php echo htmlspecialchars($subHref); ?>">
                    <h5 class="ee-exam-title" style="color:#fff;-webkit-text-fill-color:#fff;"><?php echo htmlspecialchars((string) ($exam['title'] ?? 'Épreuve')); ?></h5>
                    <i class='bx <?php echo $locked ? 'bx-lock' : 'bx-lock-open'; ?>'></i>
                </button>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
<?php include __DIR__ . '/includes/cookie_banner.php'; ?>
<script src="Assets/javascript/script_tcf.js"></script>
<script>
(function () {
    var api = <?php echo json_encode(site_href('ee_api.php')); ?>;
    var consignesRoot = document.getElementById('ee-consignes-container');
    var consigneSection = document.getElementById('ee-consignes');
    var consigneBtn = document.getElementById('consigneBtn');
    var epreuveBtn = document.getElementById('epreuveBtn');
    var examsSection = document.getElementById('ee-exams-section');

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (m) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
        });
    }
    function post(action, fields) {
        var fd = new FormData();
        fd.append('action', action);
        Object.keys(fields || {}).forEach(function (k) { fd.append(k, fields[k]); });
        return fetch(api, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); });
    }
    function showOnly(target) {
        if (examsSection) examsSection.style.display = target === 'epreuve' ? 'block' : 'none';
        if (consigneSection) consigneSection.style.display = target === 'consigne' ? 'block' : 'none';
        if (epreuveBtn) epreuveBtn.classList.toggle('is-active', target === 'epreuve');
        if (consigneBtn) consigneBtn.classList.toggle('is-active', target === 'consigne');
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
    function renderTaskConsignes(rows) {
        if (!consignesRoot) return;
        var tasks = [
            { key: 'tache1', title: 'Tâche 1 : Message court', meta: '60-120 mots • 10 minutes • Niveau A2-B1' },
            { key: 'tache2', title: 'Tâche 2 : Article de blog / Narration', meta: '120-150 mots • 20 minutes • Niveau B1 avancé – B2' },
            { key: 'tache3', title: 'Tâche 3 : Texte argumentatif', meta: '120-180 mots (2 parties) • 30 minutes • Niveau C1-C2' }
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
            '<div><h2>Critères d’évaluation</h2></div>' +
            '<span class="icon">▼</span></div>' +
            '<div class="combinaison-content">' +
            <?php
            require_once __DIR__ . '/includes/tcf_consignes_defaults.php';
            echo json_encode(tcf_consigne_ee_criteria_html(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
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
    function loadConsignes() {
        if (!consignesRoot) return;
        post('get_consignes', {}).then(function (j) {
            renderTaskConsignes((j && j.success && Array.isArray(j.data)) ? j.data : []);
        }).catch(function () {
            consignesRoot.innerHTML = '<p>Impossible de charger les consignes.</p>';
        });
    }

    document.querySelectorAll('.ee-exam-item').forEach(function (btn) {
        var titleEl = btn.querySelector('.ee-exam-title');
        if (titleEl) {
            btn.addEventListener('mouseenter', function () {
                titleEl.style.color = '#d30d0d';
                titleEl.style.webkitTextFillColor = '#d30d0d';
            });
            btn.addEventListener('mouseleave', function () {
                titleEl.style.color = '#fff';
                titleEl.style.webkitTextFillColor = '#fff';
            });
        }
        btn.addEventListener('click', function () {
            var locked = btn.getAttribute('data-locked') === '1';
            var logged = btn.getAttribute('data-logged') === '1';
            if (locked) {
                window.location.href = logged
                    ? (btn.getAttribute('data-abonnement') || 'abonnement.php')
                    : (btn.getAttribute('data-login') || 'login.php');
                return;
            }
            window.location.href = btn.getAttribute('data-href') || 'Expresion_ecrite.php';
        });
    });

    if (consigneBtn) consigneBtn.addEventListener('click', function () { showOnly('consigne'); });
    if (epreuveBtn) epreuveBtn.addEventListener('click', function () { showOnly('epreuve'); });
    showOnly('epreuve');
    loadConsignes();
})();
</script>
</body>
</html>
