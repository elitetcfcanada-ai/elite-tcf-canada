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
    if ($year <= 0) return 0;
    return ($year * 100) + $month;
}

$exams = [];
try {
    // Exclure les fragments d'import (Part1/Data) — une carte = un mois
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
        if ($ra !== $rb) return $rb <=> $ra;
        return (int) ($b['id'] ?? 0) <=> (int) ($a['id'] ?? 0);
    });
} catch (Throwable $e) {
    $exams = [];
}
$freeExamIds = [];
foreach ($exams as $examRow) {
    if ((string) ($examRow['visibility'] ?? 'gratuit') === 'premium') {
        continue;
    }
    $freeExamIds[] = (int) ($examRow['id'] ?? 0);
    if (count($freeExamIds) >= 3) break;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $tcf_brand_title = 'Expression Écrite — ELITE TCF CANADA';
    include __DIR__ . '/includes/tcf_brand_head.php';
    ?>
    <title>Expression Écrite — ELITE TCF CANADA</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="Assets/css/theme-vars.css">
    <link rel="stylesheet" href="Assets/css/header_footer.css">
    <link rel="stylesheet" href="Assets/css/style_tcf.css">
    <link rel="stylesheet" href="Assets/css/style_sujets.css">
    <link rel="stylesheet" href="Assets/css/style_Expresion_Ecrite.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<section class="hero-banner">
    <div class="hero-content">
        <h4><i class='bx bxs-school'></i> Épreuves Expression Écrite</h4>
        <h1>Bonne <span>Préparation</span> !</h1>
        <p>Les sujets ci-dessous proviennent uniquement de la base de données. Cliquez sur une épreuve pour afficher son contenu ici, sur la même page.</p>
    </div>
</section>

<div class="consigne-container">
    <button class="consigne-btn" id="epreuveBtn" type="button"><i class='bx bx-book-open'></i> Épreuve</button>
    <button class="consigne-btn" id="consigneBtn" type="button"><i class='bx bx-info-circle'></i> Consignes</button>
    <button class="consigne-btn" id="simulatorBtn" type="button"><i class='bx bx-edit'></i> Simulateur</button>
</div>

<section class="container" id="ee-consignes" style="display:none;">
    <header>
        <div class="header-content">
            <h1>Consignes expression écrite</h1>
            <p class="subtitle">Cliquez sur une tâche pour afficher la consigne</p>
        </div>
    </header>
    <div class="container">
        <div id="ee-consignes-container"></div>
    </div>
</section>

<section class="container" id="ee-simulator" style="display:none;">
    <header>
        <div class="header-content">
            <h1>Simulateur Expression Écrite</h1>
            <p class="subtitle">Simulation réaliste : choix de tâche, temps imparti, rédaction et correction</p>
        </div>
    </header>
    <div class="container">
        <div class="simulator-card sim-pro-shell-v2">
            <div class="sim-v2-head">
                <div>
                    <h3 class="sim-v2-title">Simulation professionnelle</h3>
                    <p class="sim-v2-sub">Sélectionnez une tâche, respectez le temps imparti et obtenez une correction détaillée.</p>
                </div>
                <div class="sim-v2-badges">
                    <span><i class='bx bx-time-five'></i> Timer réel</span>
                    <span><i class='bx bx-target-lock'></i> Objectif mots</span>
                    <span><i class='bx bx-check-circle'></i> Feedback complet</span>
                </div>
            </div>

            <section class="sim-v2-panel" id="sim-pro-config">
                <h4 class="sim-v2-step">Etape 1 : Configuration</h4>
                <div class="sim-v2-grid">
                    <div class="sim-v2-field">
                        <label for="sim-mode">Mode</label>
                        <select id="sim-mode" class="form-control">
                            <option value="strict">Examen (strict)</option>
                            <option value="training">Entraînement</option>
                        </select>
                    </div>
                </div>
                <div class="sim-task-picker" id="sim-task-picker">
                    <button type="button" class="sim-v2-task sim-task-btn" data-task-key="tache1">Tâche 1</button>
                    <button type="button" class="sim-v2-task sim-task-btn" data-task-key="tache2">Tâche 2</button>
                    <button type="button" class="sim-v2-task sim-task-btn" data-task-key="tache3">Tâche 3</button>
                </div>
            </section>

            <section class="sim-v2-panel sim-v2-brief" id="sim-brief" style="display:none;"></section>

            <section id="sim-active-area" style="display:none;" class="sim-v2-panel sim-v2-writing">
                <h4 class="sim-v2-step">Etape 2 : Rédaction</h4>
                <div class="sim-v2-meta">
                    <span id="sim-task-label" class="word-counter"></span>
                    <span id="sim-timer" class="word-counter"></span>
                </div>
                <div class="sim-progress" aria-hidden="true">
                    <span id="sim-progress-bar" class="sim-progress__bar"></span>
                </div>
                <textarea id="sim-user-text" rows="9" class="form-control sim-v2-textarea" placeholder="Rédigez votre production ici..."></textarea>
                <div class="word-counter" id="sim-word-counter" style="margin-top:8px;">Nombre de mots: 0</div>
                <div class="sim-v2-actions">
                    <button type="button" class="simulate-btn" id="sim-send-btn">Envoyer</button>
                    <button type="button" class="simulator-close-btn" id="sim-cancel-btn">Annuler</button>
                </div>
            </section>

            <section class="simulator-result sim-v2-panel" id="sim-result"></section>
        </div>
    </div>
</section>

<section class="section_epreuve" id="ee-exams-section" style="display:none;">
    <div class="row_arrangement" id="ee-exams-list">
        <?php if (empty($exams)): ?>
            <div class="column_arrangement"><h5>Aucune épreuve publiée pour le moment.</h5><i class='bx bx-info-circle'></i></div>
        <?php else: ?>
            <?php foreach ($exams as $exam): ?>
                <?php
                $examId = (int) ($exam['id'] ?? 0);
                $isFree = in_array($examId, $freeExamIds, true);
                $locked = !$isFree && (!$viewer || !$premiumOk);
                ?>
                <button type="button" class="column_arrangement<?php echo $locked ? ' non_valide' : ''; ?> ee-exam-item"
                        data-exam-id="<?php echo $examId; ?>"
                        data-locked="<?php echo $locked ? '1' : '0'; ?>">
                    <h5 class="ee-exam-title" style="color:#fff;-webkit-text-fill-color:#fff;"><?php echo htmlspecialchars((string) ($exam['title'] ?? 'Épreuve')); ?></h5>
                    <i class='bx <?php echo $locked ? 'bx-lock' : 'bx-lock-open'; ?>'></i>
                </button>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<section class="container" id="ee-detail-root" style="display:none;">
    <header>
        <div class="header-content">
            <h1 id="ee-detail-title">Expression Écrite TCF Canada</h1>
            <p class="subtitle" id="ee-detail-subtitle"></p>
        </div>
    </header>
    <div class="container">
        <div class="search-container">
            <input type="text" class="search-box" placeholder="Entrer numéro combinaison..." id="searchInput">
        </div>
        <div id="ee-combos-container"></div>
    </div>
</section>

<div id="ee-lock-msg" style="display:none;max-width:860px;margin:0 auto 1rem;padding:1rem 1.2rem;border-radius:12px;background:#fff3f3;border:1px solid rgba(211,13,13,.35);">
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
    var api = <?php echo json_encode(site_href('ee_api.php')); ?>;
    var detailRoot = document.getElementById('ee-detail-root');
    var combosRoot = document.getElementById('ee-combos-container');
    var consignesRoot = document.getElementById('ee-consignes-container');
    var consigneSection = document.getElementById('ee-consignes');
    var consigneBtn = document.getElementById('consigneBtn');
    var simulatorBtn = document.getElementById('simulatorBtn');
    var epreuveBtn = document.getElementById('epreuveBtn');
    var examsSection = document.getElementById('ee-exams-section');
    var simulatorSection = document.getElementById('ee-simulator');
    var lockMsg = document.getElementById('ee-lock-msg');
    var searchInput = document.getElementById('searchInput');
    var consignesCache = [];
    var simConfig = document.getElementById('sim-pro-config');
    var simBrief = document.getElementById('sim-brief');
    var simTaskPicker = document.getElementById('sim-task-picker');
    var simActiveArea = document.getElementById('sim-active-area');
    var simTaskLabel = document.getElementById('sim-task-label');
    var simTimer = document.getElementById('sim-timer');
    var simProgressBar = document.getElementById('sim-progress-bar');
    var simUserText = document.getElementById('sim-user-text');
    var simMode = document.getElementById('sim-mode');
    var simSendBtn = document.getElementById('sim-send-btn');
    var simCancelBtn = document.getElementById('sim-cancel-btn');
    var simResult = document.getElementById('sim-result');
    var simWordCounter = document.getElementById('sim-word-counter');
    var simState = { taskKey: null, taskLabel: '', prompt: '', consigne: '', secondsLeft: 0, timerId: null, wordMin: 0, wordMax: 0, running: false };

    function esc(s) { return String(s == null ? '' : s); }
    function cleanTask3Correction(taskNumber, correctionHtml) {
        if (String(taskNumber) !== '3') return correctionHtml || '';
        return String(correctionHtml || '')
            .replace(/\b[Ll]e\s+Document\s+1\b/g, 'Le premier document')
            .replace(/\b[Ll]e\s+Document\s+2\b/g, 'Le second document')
            .replace(/\b[Dd]ocument\s+1\b/g, 'premier document')
            .replace(/\b[Dd]ocument\s+2\b/g, 'second document');
    }
    function post(action, fields) {
        var fd = new FormData();
        fd.append('action', action);
        Object.keys(fields || {}).forEach(function (k) { fd.append(k, fields[k]); });
        return fetch(api, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); });
    }
    function setTopActionActive(target) {
        if (epreuveBtn) epreuveBtn.classList.toggle('is-active', target === 'epreuve');
        if (consigneBtn) consigneBtn.classList.toggle('is-active', target === 'consigne');
        if (simulatorBtn) simulatorBtn.classList.toggle('is-active', target === 'simulateur');
    }
    function showOnly(target) {
        if (examsSection) examsSection.style.display = target === 'epreuve' ? 'block' : 'none';
        if (detailRoot) detailRoot.style.display = target === 'epreuve' && detailRoot.dataset.loaded === '1' ? 'block' : 'none';
        if (lockMsg && target !== 'epreuve') lockMsg.style.display = 'none';
        if (consigneSection) consigneSection.style.display = target === 'consigne' ? 'block' : 'none';
        if (simulatorSection) simulatorSection.style.display = target === 'simulateur' ? 'block' : 'none';
        setTopActionActive(target);
    }
    function formatSeconds(total) {
        var s = Math.max(0, Number(total || 0));
        var m = Math.floor(s / 60);
        var ss = s % 60;
        return String(m).padStart(2, '0') + ':' + String(ss).padStart(2, '0');
    }
    function stopSimTimer() {
        if (simState.timerId) {
            clearInterval(simState.timerId);
            simState.timerId = null;
        }
    }
    function resetSimState() {
        stopSimTimer();
        simState.taskKey = null;
        simState.taskLabel = '';
        simState.prompt = '';
        simState.consigne = '';
        simState.secondsLeft = 0;
        simState.wordMin = 0;
        simState.wordMax = 0;
        simState.running = false;
        if (simActiveArea) simActiveArea.style.display = 'none';
        if (simTaskPicker) simTaskPicker.style.display = 'flex';
        if (simConfig) simConfig.style.display = 'block';
        if (simBrief) simBrief.style.display = 'none';
        if (simUserText) simUserText.value = '';
        if (simUserText) simUserText.disabled = false;
        if (simTimer) simTimer.textContent = '';
        if (simProgressBar) simProgressBar.style.width = '0%';
        if (simTaskLabel) simTaskLabel.textContent = '';
        if (simWordCounter) simWordCounter.textContent = 'Nombre de mots: 0';
        if (simResult) {
            simResult.classList.remove('visible');
            simResult.innerHTML = '';
        }
    }
    function startSimTimer() {
        stopSimTimer();
        if (!simTimer) return;
        var initial = Math.max(1, Number(simState.secondsLeft || 1));
        simTimer.textContent = 'Temps restant: ' + formatSeconds(simState.secondsLeft);
        if (simProgressBar) simProgressBar.style.width = '100%';
        simState.timerId = setInterval(function () {
            simState.secondsLeft -= 1;
            simTimer.textContent = 'Temps restant: ' + formatSeconds(simState.secondsLeft);
            if (simProgressBar) {
                var pct = Math.max(0, Math.min(100, (simState.secondsLeft / initial) * 100));
                simProgressBar.style.width = pct + '%';
            }
            if (simState.secondsLeft <= 0) {
                stopSimTimer();
                simState.running = false;
                if (simUserText) simUserText.disabled = true;
                submitSimResponse(true);
            }
        }, 1000);
    }
    function loadSimSubject(taskKey) {
        if (simBrief) {
            simBrief.style.display = 'block';
            simBrief.innerHTML = '<p>Chargement du sujet...</p>';
        }
        post('get_simulator_subject', { task_key: taskKey })
            .then(function (j) {
                if (!j || !j.success || !j.data) {
                    if (simBrief) simBrief.innerHTML = '<p style="color:#b91c1c;">Impossible de charger le sujet.</p>';
                    return;
                }
                var d = j.data;
                simState.taskKey = d.task_key || taskKey;
                simState.taskLabel = d.task_label || 'Tâche';
                simState.prompt = d.subject || '';
                simState.consigne = d.consigne || '';
                simState.secondsLeft = Number(d.duration_seconds || 0);
                simState.wordMin = Number(d.word_min || 0);
                simState.wordMax = Number(d.word_max || 0);
                if (simBrief) {
                    simBrief.innerHTML =
                        '<h4>' + esc(simState.taskLabel) + '</h4>' +
                        '<p><strong>Temps:</strong> ' + formatSeconds(simState.secondsLeft) + '</p>' +
                        '<p><strong>Objectif:</strong> ' + simState.wordMin + ' à ' + simState.wordMax + ' mots</p>' +
                        '<p><strong>Sujet:</strong> ' + esc(simState.prompt) + '</p>' +
                        '<p><strong>Consigne:</strong></p><div class="ee-consigne-body">' + String(simState.consigne) + '</div>' +
                        '<button type="button" class="simulate-btn" id="sim-start-btn" style="margin-top:10px;">Démarrer la simulation</button>';
                }
                var startBtn = document.getElementById('sim-start-btn');
                if (startBtn) {
                    startBtn.addEventListener('click', function () {
                        simState.running = true;
                        if (simTaskLabel) simTaskLabel.textContent = simState.taskLabel + ' · Objectif ' + simState.wordMin + '-' + simState.wordMax + ' mots';
                        if (simTaskPicker) simTaskPicker.style.display = 'none';
                        if (simConfig) simConfig.style.display = 'none';
                        if (simActiveArea) simActiveArea.style.display = 'block';
                        if (simUserText) {
                            simUserText.disabled = false;
                            simUserText.focus();
                        }
                        startSimTimer();
                    });
                }
            })
            .catch(function () {
                if (simBrief) simBrief.innerHTML = '<p style="color:#b91c1c;">Erreur réseau pendant le chargement du sujet.</p>';
            });
    }
    function submitSimResponse(fromTimeout) {
        if (!simState.taskKey || !simUserText || !simState.running && !fromTimeout) return;
        var txt = (simUserText.value || '').trim();
        if (!txt) {
            if (!fromTimeout && simResult) {
                simResult.classList.add('visible');
                simResult.innerHTML = '<p style="color:#b91c1c;">Veuillez écrire votre réponse avant envoi.</p>';
            }
            return;
        }
        var words = txt.split(/\s+/).filter(Boolean).length;
        var outOfRange = !!(simMode && simMode.value === 'strict' && (words < simState.wordMin || words > simState.wordMax));
        simState.running = false;
        stopSimTimer();
        if (simSendBtn) {
            simSendBtn.disabled = true;
            simSendBtn.textContent = 'Correction...';
        }
        if (simCancelBtn) simCancelBtn.disabled = true;
        post('ai_correct', {
            task_id: 0,
            exam_id: 0,
            combo_id: 0,
            user_text: txt,
            word_min: simState.wordMin,
            word_max: simState.wordMax,
            task_prompt: simState.prompt
        }).then(function (j) {
            if (!j || !j.success || !j.feedback) {
                if (simResult) {
                    simResult.classList.add('visible');
                    simResult.innerHTML = '<p style="color:#b91c1c;">' + esc((j && j.message) || 'Erreur de correction.') + '</p>';
                }
                return;
            }
            var f = j.feedback;
            var tips = Array.isArray(f.tips) ? f.tips.map(function (t) { return '<li>' + esc(t) + '</li>'; }).join('') : '';
            if (simResult) {
                simResult.classList.add('visible');
                simResult.innerHTML =
                    '<p><strong>' + (fromTimeout ? 'Temps imparti terminé.' : 'Soumission terminée.') + '</strong></p>' +
                    (outOfRange ? '<p style="color:#b45309;"><strong>Attention:</strong> hors consigne de longueur (' + simState.wordMin + '-' + simState.wordMax + ' mots). Une pénalité peut s\'appliquer.</p>' : '') +
                    '<p><strong>Niveau:</strong> ' + esc(f.cefr_level || 'N/A') + ' · <strong>Score:</strong> ' + esc(f.score_global || 'N/A') + '/20 · <strong>Mots:</strong> ' + esc(j.word_count || 0) + '</p>' +
                    '<p><strong>Remarques:</strong><br>' + esc(f.remarks || '') + '</p>' +
                    '<p><strong>Corrections:</strong><br>' + esc(f.corrections || '') + '</p>' +
                    '<p><strong>Version améliorée:</strong><br>' + esc(f.improved_text || '') + '</p>' +
                    (tips ? '<p><strong>Conseils:</strong></p><ul>' + tips + '</ul>' : '');
            }
        }).catch(function () {
            if (simResult) {
                simResult.classList.add('visible');
                simResult.innerHTML = '<p style="color:#b91c1c;">Erreur réseau pendant la correction.</p>';
            }
        }).finally(function () {
            if (simSendBtn) {
                simSendBtn.disabled = false;
                simSendBtn.textContent = 'Envoyer';
            }
            if (simCancelBtn) simCancelBtn.disabled = false;
            if (simUserText) simUserText.disabled = (simMode && simMode.value === 'strict');
        });
    }
    function renderConsignes() {
        if (!consignesRoot) return;
        var all = consignesCache || [];
        var tasks = ['tache1', 'tache2', 'tache3'];
        var labels = { tache1: 'Tâche 1', tache2: 'Tâche 2', tache3: 'Tâche 3' };
        if (!all.length) {
            consignesRoot.innerHTML = '<div class="combinaison active"><div class="combinaison-content" style="display:block;"><p>Aucune consigne disponible.</p></div></div>';
            return;
        }
        var html = '';
        tasks.forEach(function (taskKey, idx) {
            var rows = all.filter(function (c) { return c.task_key === taskKey; });
            var bodyHtml = '';
            if (!rows.length) {
                bodyHtml = '<p>Aucune consigne publiée pour cette tâche.</p>';
            } else {
                rows.forEach(function (c) {
                    // Afficher exactement le texte publié (balises HTML incluses).
                    bodyHtml += '<div class="ee-consigne-body">' + String(c.body || '') + '</div>';
                });
            }
            html += '<div class="combinaison' + (idx === 0 ? ' active' : '') + '" data-consigne-task="' + taskKey + '">' +
                '<div class="combinaison-header"><h2>' + labels[taskKey] + '</h2><span class="icon">▼</span></div>' +
                '<div class="combinaison-content"' + (idx === 0 ? ' style="display:block;"' : '') + '>' + bodyHtml + '</div></div>';
        });
        consignesRoot.innerHTML = html;
        bindComboUi();
    }
    function loadConsignes() {
        post('get_consignes', {})
            .then(function (j) {
                if (!j || !j.success || !Array.isArray(j.data)) {
                    consignesRoot.innerHTML = '<div class="combinaison active"><div class="combinaison-content" style="display:block;"><p>Aucune consigne disponible.</p></div></div>';
                    return;
                }
                consignesCache = j.data;
                renderConsignes();
            })
            .catch(function () {
                consignesRoot.innerHTML = '<div class="combinaison active"><div class="combinaison-content" style="display:block;"><p>Impossible de charger les consignes.</p></div></div>';
            });
    }
    function renderExam(data) {
        document.getElementById('ee-detail-title').textContent = data.title || 'Expression Écrite TCF Canada';
        document.getElementById('ee-detail-subtitle').textContent = data.subtitle || 'Corrections et exemples pour les différentes combinaisons';
        var html = '';
        (data.combinations || []).forEach(function (combo) {
            html += '<div class="combinaison" data-id="' + esc(combo.combo_number || '') + '">' +
                '<div class="combinaison-header"><h2>' + esc(combo.title || ('Combinaison ' + (combo.combo_number || ''))) + '</h2><span class="icon">▼</span></div>' +
                '<div class="combinaison-content"><div class="ee-task-grid">';
            (combo.tasks || []).forEach(function (task) {
                var correctionCleaned = cleanTask3Correction(task.task_number, task.correction || '');
                html += '<div class="tache"><h3>Tâche ' + esc(task.task_number || '') + '</h3><h4 class="enonce-label">Enonce</h4><p>' + esc(task.prompt || '') + '</p>';
                (task.documents || []).forEach(function (doc, idx) {
                    var docTitle = esc(doc.title || ('Document ' + (idx + 1)));
                    var docContent = String(doc.content || '')
                        .replace(/^\s*Document\s*\d+\s*:?\s*/i, '')
                        .trim();
                    html += '<div class="document"><h4>' + docTitle + ' :</h4><p>' + esc(docContent) + '</p></div>';
                });
                html += '<div class="correction"><h4>Correction Tâche ' + esc(task.task_number || '') + '</h4><div>' + correctionCleaned + '</div></div>' +
                    '<div class="correction" style="margin-top:10px;">' +
                    '<h4>Votre réponse (Simulation)</h4>' +
                    '<textarea class="form-control ee-user-text" rows="5" data-task-id="' + esc(task.id || '') + '" data-exam-id="' + esc(data.id || '') + '" data-combo-id="' + esc(combo.id || '') + '" data-word-min="' + esc(task.word_min || '') + '" data-word-max="' + esc(task.word_max || '') + '" data-prompt="' + esc(task.prompt || '') + '" placeholder="Rédigez votre réponse..."></textarea>' +
                    '<button type="button" class="btn btn-primary ee-ai-correct-btn" style="margin-top:8px;">Corriger avec simulation</button>' +
                    '<div class="ee-ai-result" style="margin-top:10px;"></div>' +
                    '</div></div>';
            });
            html += '</div></div></div>';
        });
        combosRoot.innerHTML = html;
        detailRoot.dataset.loaded = '1';
        detailRoot.style.display = 'block';
        lockMsg.style.display = 'none';
        bindComboUi();
        applyCombinationSearch();
        window.scrollTo({ top: detailRoot.offsetTop - 90, behavior: 'smooth' });
    }

    function applyCombinationSearch() {
        var q = ((searchInput && searchInput.value) || '').trim().toLowerCase();
        document.querySelectorAll('#ee-combos-container .combinaison').forEach(function (c) {
            if (!q) {
                c.style.display = 'block';
                return;
            }
            var comboId = String(c.getAttribute('data-id') || '').toLowerCase();
            var comboTitleEl = c.querySelector('.combinaison-header h2');
            var comboTitle = comboTitleEl ? comboTitleEl.textContent.toLowerCase() : '';
            var match = comboId.indexOf(q) >= 0 || comboTitle.indexOf('combinaison ' + q) >= 0;
            c.style.display = match ? 'block' : 'none';
        });
    }

    function bindComboUi() {
        document.querySelectorAll('.combinaison-header').forEach(function (h) {
            h.addEventListener('click', function () {
                var combo = this.parentElement;
                combo.classList.toggle('active');
            });
        });
        document.querySelectorAll('.ee-ai-correct-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var wrap = btn.closest('.correction');
                var ta = wrap.querySelector('.ee-user-text');
                var out = wrap.querySelector('.ee-ai-result');
                var text = (ta.value || '').trim();
                if (text.length < 10) {
                    out.innerHTML = '<p style="color:#b91c1c;">Veuillez rédiger une réponse plus complète.</p>';
                    return;
                }
                btn.disabled = true;
                btn.textContent = 'Correction...';
                post('ai_correct', {
                    task_id: ta.getAttribute('data-task-id') || '',
                    exam_id: ta.getAttribute('data-exam-id') || '',
                    combo_id: ta.getAttribute('data-combo-id') || '',
                    user_text: text,
                    word_min: ta.getAttribute('data-word-min') || '',
                    word_max: ta.getAttribute('data-word-max') || '',
                    task_prompt: ta.getAttribute('data-prompt') || ''
                }).then(function (j) {
                    if (!j || !j.success || !j.feedback) {
                        out.innerHTML = '<p style="color:#b91c1c;">' + esc((j && j.message) || 'Erreur de correction simulation.') + '</p>';
                        return;
                    }
                    var f = j.feedback;
                    var tips = Array.isArray(f.tips) ? f.tips.map(function (t) { return '<li>' + esc(t) + '</li>'; }).join('') : '';
                    out.innerHTML = '<p><strong>Niveau:</strong> ' + esc(f.cefr_level || 'N/A') + ' · <strong>Score:</strong> ' + esc(f.score_global || 'N/A') + '/20 · <strong>Mots:</strong> ' + esc(j.word_count || 0) + '</p>' +
                        '<p><strong>Remarques:</strong><br>' + esc(f.remarks || '') + '</p>' +
                        '<p><strong>Corrections:</strong><br>' + esc(f.corrections || '') + '</p>' +
                        '<p><strong>Version améliorée:</strong><br>' + esc(f.improved_text || '') + '</p>' +
                        (tips ? '<p><strong>Conseils:</strong></p><ul>' + tips + '</ul>' : '');
                }).catch(function () {
                    out.innerHTML = '<p style="color:#b91c1c;">Erreur réseau.</p>';
                }).finally(function () {
                    btn.disabled = false;
                    btn.textContent = 'Corriger avec simulation';
                });
            });
        });
    }

    document.querySelectorAll('.ee-exam-item').forEach(function (btn) {
        var titleEl = btn.querySelector('.ee-exam-title');
        if (titleEl) {
            titleEl.style.color = '#fff';
            titleEl.style.webkitTextFillColor = '#fff';
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
            if (btn.getAttribute('data-locked') === '1') {
                detailRoot.style.display = 'none';
                lockMsg.style.display = 'block';
                window.scrollTo({ top: lockMsg.offsetTop - 90, behavior: 'smooth' });
                return;
            }
            post('get_exam_detail', { exam_id: btn.getAttribute('data-exam-id') })
                .then(function (j) {
                    if (!j || !j.success || !j.data) {
                        lockMsg.style.display = 'block';
                        lockMsg.innerHTML = '<strong>Erreur</strong><p style="margin:.4rem 0 0;">' + esc((j && j.message) || 'Impossible de charger le contenu.') + '</p>';
                        detailRoot.style.display = 'none';
                        return;
                    }
                    renderExam(j.data);
                })
                .catch(function () {
                    lockMsg.style.display = 'block';
                    lockMsg.innerHTML = '<strong>Erreur réseau</strong>';
                    detailRoot.style.display = 'none';
                });
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            applyCombinationSearch();
        });
    }
    if (consigneBtn && consigneSection) {
        consigneBtn.addEventListener('click', function () {
            showOnly('consigne');
            consigneSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }
    if (simulatorBtn) {
        simulatorBtn.addEventListener('click', function () {
            showOnly('simulateur');
            if (simulatorSection) {
                simulatorSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            resetSimState();
        });
    }
    if (epreuveBtn) {
        epreuveBtn.addEventListener('click', function () {
            showOnly('epreuve');
            var target = detailRoot && detailRoot.style.display !== 'none'
                ? detailRoot
                : document.getElementById('ee-exams-list');
            if (target && target.scrollIntoView) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }
    showOnly('epreuve');
    if (simTaskPicker) {
        simTaskPicker.querySelectorAll('.sim-task-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                loadSimSubject(btn.getAttribute('data-task-key') || 'tache1');
            });
        });
    }
    if (simSendBtn) {
        simSendBtn.addEventListener('click', function () {
            submitSimResponse(false);
        });
    }
    if (simUserText && simWordCounter) {
        simUserText.addEventListener('input', function () {
            var wc = (this.value || '').trim().split(/\s+/).filter(Boolean).length;
            simWordCounter.textContent = 'Nombre de mots: ' + wc;
        });
    }
    if (simCancelBtn) {
        simCancelBtn.addEventListener('click', function () {
            resetSimState();
        });
    }
    loadConsignes();
})();
</script>
</body>
</html>
