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
    <link rel="stylesheet" href="Assets/css/style_sujets.css">
    <link rel="stylesheet" href="Assets/css/style_Expresion_Ecrite.css">
    <link rel="stylesheet" href="Assets/css/expression_orale.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<section class="hero-banner">
    <div class="hero-content">
        <h4><i class='bx bxs-school'></i> Épreuves Expression Orale</h4>
        <h1>Bonne <span>Préparation</span> !</h1>
        <p>Maîtrisez l'expression orale du TCF Canada avec nos sujets d'entraînement.</p>
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
            <p class="subtitle">Cliquez sur une tâche pour afficher la consigne</p>
        </div>
    </header>
    <div class="container"><div id="eo-consignes-container"></div></div>
</section>

<section class="container" id="eo-detail-root" style="display:none;">
    <header class="eo-exam-page-header">
        <div class="header-content">
            <h1 id="eo-detail-title"><i class="bx bx-microphone"></i> Expression Orale</h1>
            <p class="subtitle" id="eo-detail-subtitle"></p>
        </div>
    </header>
    <div class="container" id="eo-detail-container"></div>
</section>

<div id="eo-lock-msg" style="display:none;max-width:860px;margin:0 auto 1rem;padding:1rem 1.2rem;border-radius:12px;background:#fff3f3;border:1px solid rgba(211,13,13,.35);">
    <strong>Accès premium requis.</strong>
    <p style="margin:.4rem 0 0;">
        <?php if (empty($_SESSION['user_id'])): ?>
            Connectez-vous puis activez un abonnement pour ouvrir cette épreuve.
            <a href="<?php echo htmlspecialchars(site_href('login.php')); ?>">Connexion</a> ·
            <a href="<?php echo htmlspecialchars(site_href('abonnement.php')); ?>">Abonnement</a>
        <?php else: ?>
            Votre abonnement n’est pas actif. Activez votre formule pour accéder à cette épreuve.
            <a href="<?php echo htmlspecialchars(site_href('abonnement.php')); ?>">Voir les formules</a>
        <?php endif; ?>
    </p>
</div>

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
    var detailRoot = document.getElementById('eo-detail-root');
    var detailContainer = document.getElementById('eo-detail-container');
    var lockMsg = document.getElementById('eo-lock-msg');
    var examsList = document.getElementById('eo-exams-list');
    var consignesRoot = document.getElementById('eo-consignes-container');

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
        if (detailRoot) detailRoot.style.display = target === 'epreuve' && detailRoot.dataset.loaded === '1' ? 'block' : 'none';
        if (lockMsg && target !== 'epreuve') lockMsg.style.display = 'none';
        setTopActionActive(target);
    }
    function bindComboUi(root) {
        (root || document).querySelectorAll('.combinaison-header').forEach(function (h) {
            h.addEventListener('click', function () {
                var combo = this.parentElement;
                if (!combo) return;
                combo.classList.toggle('active');
                var content = combo.querySelector('.combinaison-content');
                if (content) content.style.display = combo.classList.contains('active') ? 'block' : 'none';
            });
        });
    }
    function bindCorrectionToggles(root) {
        (root || document).querySelectorAll('.eo-correction-toggle-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var panel = btn.parentElement && btn.parentElement.querySelector('.eo-correction-panel');
                if (!panel) return;
                var open = btn.getAttribute('aria-expanded') === 'true';
                open = !open;
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                btn.textContent = open ? 'Masquer la correction' : 'Voir la correction';
                panel.hidden = !open;
                panel.style.display = open ? 'block' : 'none';
            });
        });
    }
    function formatRichText(text) {
        var t = String(text || '').trim();
        if (!t) return '';
        if (/<[a-z][\s\S]*>/i.test(t)) return t;
        return esc(t).replace(/\n/g, '<br>');
    }
    function subjectCardHtml(s) {
        var corr = String(s.correction || '').trim();
        var hasCorr = corr.length > 0;
        var html = '<div class="task-card eo-subject-card">' +
            '<div class="card-number">' + esc(s.subject_number || '') + '</div>' +
            '<h3><i class="' + esc(s.icon_class || 'bx bx-message-detail') + '"></i> ' + esc(s.title || '') + '</h3>';
        if (s.role_label) {
            html += '<p class="eo-role-label">' + esc(s.role_label) + '</p>';
        }
        html += '<div class="eo-prompt"><h4 class="enonce-label">Énoncé</h4><p>' + esc(s.prompt || '') + '</p></div>';
        if (hasCorr) {
            html += '<button type="button" class="eo-correction-toggle-btn" aria-expanded="false">Voir la correction</button>' +
                '<div class="eo-correction-panel correction" hidden style="display:none">' +
                '<h4>Correction</h4><div class="eo-correction-body">' + formatRichText(corr) + '</div></div>';
        }
        html += '</div>';
        return html;
    }

    function renderConsignes(rows) {
        var tasks = ['tache2', 'tache3'];
        var labels = { tache2: 'Tâche 2', tache3: 'Tâche 3' };
        var nav = '<div class="task-navigation" style="margin:10px 0 16px;">' +
            '<button class="task-btn active" data-consigne-target="tache2"><i class="bx bx-task"></i> Tâche 2</button>' +
            '<button class="task-btn" data-consigne-target="tache3"><i class="bx bx-task"></i> Tâche 3</button>' +
            '</div>';
        var panels = '';
        tasks.forEach(function (taskKey, idx) {
            var list = (rows || []).filter(function (r) { return r.task_key === taskKey; });
            var body = list.length
                ? list.map(function (c) {
                    return '<div class="task-card" style="border-top:5px solid var(--main-color);margin-bottom:12px;">' +
                        '<h3><i class="bx bx-info-circle"></i> ' + labels[taskKey] + '</h3>' +
                        '<div class="ee-consigne-body">' + String(c.body || '') + '</div>' +
                        '</div>';
                }).join('')
                : '<p>Aucune consigne publiée.</p>';
            panels += '<div class="combinaison' + (idx === 0 ? ' active' : '') + '" id="consigne-' + taskKey + '">' +
                '<div class="combinaison-header"><h2>' + labels[taskKey] + '</h2><span class="icon">▼</span></div>' +
                '<div class="combinaison-content"' + (idx === 0 ? ' style="display:block;"' : '') + '>' + body + '</div></div>';
        });
        consignesRoot.innerHTML = nav + panels;
        bindComboUi(consignesRoot);
        consignesRoot.querySelectorAll('[data-consigne-target]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var target = btn.getAttribute('data-consigne-target');
                consignesRoot.querySelectorAll('[data-consigne-target]').forEach(function (b) { b.classList.toggle('active', b === btn); });
                consignesRoot.querySelectorAll('.task-section').forEach(function (sec) { sec.classList.toggle('active', sec.id === 'consigne-' + target); });
            });
        });
    }

    function renderExamDetail(exam) {
        if (!detailContainer) return;
        var byTask = { tache2: [], tache3: [] };
        (exam.parts || []).forEach(function (p) {
            var k = (p.task_key || '').toLowerCase();
            if (!byTask[k]) byTask[k] = [];
            byTask[k].push(p);
        });
        Object.keys(byTask).forEach(function (k) {
            byTask[k].sort(function (a, b) { return Number(a.part_number || 0) - Number(b.part_number || 0); });
        });
        function section(taskKey) {
            var parts = byTask[taskKey] || [];
            if (!parts.length) return '';
            var sectionHtml = '<div class="task-section' + (taskKey === 'tache2' ? ' active' : '') + '" id="section-' + taskKey + '">';
            parts.forEach(function (p, idx) {
                var partTitle = esc(p.part_title || ('Partie ' + (p.part_number || '')));
                sectionHtml += '<div class="combinaison eo-partie' + (idx === 0 ? ' active' : '') + '" data-part="' + esc(p.part_number || '') + '">' +
                    '<div class="combinaison-header"><h2>' + partTitle + '</h2>' +
                    '<span class="icon">▼</span></div>' +
                    '<div class="combinaison-content"' + (idx === 0 ? ' style="display:block;"' : '') + '>' +
                    '<div class="task-container">';
                (p.subjects || []).forEach(function (s) {
                    sectionHtml += subjectCardHtml(s);
                });
                sectionHtml += '</div></div></div>';
            });
            sectionHtml += '</div>';
            return sectionHtml;
        }
        var titleEl = document.getElementById('eo-detail-title');
        var subtitleEl = document.getElementById('eo-detail-subtitle');
        if (titleEl) {
            titleEl.innerHTML = '<i class="bx bx-microphone"></i> ' + esc(exam.title || 'Expression Orale');
        }
        if (subtitleEl) {
            subtitleEl.textContent = exam.subtitle || "Découvrez les nouveaux sujets de l'expression orale qui se répètent. Pratiquez sur ces thèmes afin d'obtenir de bonnes notes.";
        }
        detailContainer.innerHTML =
            '<div class="task-navigation">' +
            '<button class="task-btn active" data-target="tache2"><i class="bx bx-task"></i> Tâche 2</button>' +
            '<button class="task-btn" data-target="tache3"><i class="bx bx-task"></i> Tâche 3</button>' +
            '</div>' +
            section('tache2') +
            section('tache3');
        detailRoot.dataset.loaded = '1';
        detailRoot.style.display = 'block';
        lockMsg.style.display = 'none';
        bindComboUi(detailContainer);
        bindCorrectionToggles(detailContainer);
        detailContainer.querySelectorAll('.task-btn').forEach(function (b) {
            b.addEventListener('click', function () {
                var target = b.getAttribute('data-target');
                detailContainer.querySelectorAll('.task-btn').forEach(function (x) { x.classList.toggle('active', x === b); });
                detailContainer.querySelectorAll('.task-section').forEach(function (sec) { sec.classList.toggle('active', sec.id === 'section-' + target); });
            });
        });
    }

    function loadExam(examId) {
        post('get_exam_public', { exam_id: String(examId) }).then(function (j) {
            if (!j || !j.success || !j.data) return;
            renderExamDetail(j.data);
            window.scrollTo({ top: detailRoot.offsetTop - 90, behavior: 'smooth' });
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
                return '<button type="button" class="column_arrangement ee-exam-item' + (locked ? ' non_valide' : '') + '" data-id="' + esc(r.id) + '" data-locked="' + (locked ? '1' : '0') + '">' +
                    '<h5 class="ee-exam-title">' + esc(r.title || 'Épreuve') + '</h5>' +
                    '<i class="bx ' + (locked ? 'bx-lock' : 'bx-lock-open') + '"></i></button>';
            }).join('');
            examsList.querySelectorAll('.ee-exam-item').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if ((btn.getAttribute('data-locked') || '') === '1') {
                        lockMsg.style.display = 'block';
                        detailRoot.style.display = 'none';
                        return;
                    }
                    loadExam(Number(btn.getAttribute('data-id') || 0));
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