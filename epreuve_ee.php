<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/subscription_access.php';
require_once __DIR__ . '/includes/rich_text.php';

$examId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($examId <= 0) {
    header('Location: ' . site_href('Expresion_ecrite.php'));
    exit;
}

$viewer = null;
if (!empty($_SESSION['user_id'])) {
    $st = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $st->execute([(int) $_SESSION['user_id']]);
    $viewer = $st->fetch(PDO::FETCH_ASSOC) ?: null;
}
$premiumOk = tcf_user_has_premium_access($viewer);
$loggedIn = $viewer !== null;

$exam = null;
try {
    $st = $pdo->prepare(
        "SELECT id, slug, title, subtitle, visibility, is_published
         FROM tcf_ee_exams
         WHERE id = ? AND is_published = 1
         LIMIT 1"
    );
    $st->execute([$examId]);
    $exam = $st->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Throwable $e) {
    $exam = null;
}

if (!$exam) {
    header('Location: ' . site_href('Expresion_ecrite.php'));
    exit;
}

$isPremium = strtolower((string) ($exam['visibility'] ?? 'gratuit')) === 'premium';
$returnPath = 'epreuve_ee.php?id=' . $examId;

if ($isPremium && !$loggedIn) {
    header('Location: ' . site_href('login.php?next=' . rawurlencode($returnPath)));
    exit;
}
if ($isPremium && !$premiumOk) {
    header('Location: ' . site_href('abonnement.php'));
    exit;
}

$combinations = [];
try {
    $stC = $pdo->prepare('SELECT * FROM tcf_ee_combinations WHERE exam_id = ? ORDER BY sort_order ASC, combo_number ASC');
    $stC->execute([$examId]);
    $combinations = $stC->fetchAll(PDO::FETCH_ASSOC);
    foreach ($combinations as &$combo) {
        $stT = $pdo->prepare('SELECT * FROM tcf_ee_tasks WHERE combination_id = ? ORDER BY sort_order ASC, task_number ASC');
        $stT->execute([(int) $combo['id']]);
        $tasks = $stT->fetchAll(PDO::FETCH_ASSOC);
        foreach ($tasks as &$task) {
            $stD = $pdo->prepare('SELECT * FROM tcf_ee_task_documents WHERE task_id = ? ORDER BY sort_order ASC, doc_number ASC');
            $stD->execute([(int) $task['id']]);
            $task['documents'] = $stD->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($task);
        $combo['tasks'] = $tasks;
    }
    unset($combo);
} catch (Throwable $e) {
    $combinations = [];
}

$pageTitle = (string) ($exam['title'] ?? 'Expression Écrite');
$subtitle = (string) ($exam['subtitle'] ?? 'Sujets et corrections');
$backHref = site_href('Expresion_ecrite.php');
$apiHref = site_href('ee_api.php');
$loginHref = site_href('login.php?next=' . rawurlencode($returnPath));
$simLocked = !$loggedIn;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $tcf_brand_title = $pageTitle . ' — ELITE TCF CANADA';
    include __DIR__ . '/includes/tcf_brand_head.php';
    ?>
    <title><?php echo htmlspecialchars($pageTitle); ?> — ELITE TCF CANADA</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/theme-vars.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/header_footer.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/style_tcf.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/style_sujets.css')); ?>?v=no-glow-1">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/style_Expresion_Ecrite.css')); ?>?v=ee-type-sm">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/epreuve_reader.css')); ?>?v=sim-login-2">
</head>
<body class="tcf-page-epreuve-reader">
<?php include __DIR__ . '/includes/header.php'; ?>

<section class="hero-banner">
    <div class="hero-content">
        <p class="hero-kicker"><i class="bx bxs-school"></i> Épreuve</p>
        <h1 class="hero-skill-title" style="color:#d30d0d!important;-webkit-text-fill-color:#d30d0d!important;"><?php echo htmlspecialchars($pageTitle); ?></h1>
        <p class="hero-lead">Lisez les sujets, entraînez-vous avec le simulateur, puis consultez la correction.</p>
    </div>
</section>

<main class="tcf-epreuve-reader">
    <div class="search-container tcf-epreuve-search">
        <input type="text" class="search-box" placeholder="Entrer numéro combinaison..." id="searchInput">
    </div>

    <div id="ee-combos-container">
        <?php if (empty($combinations)): ?>
            <p>Aucune combinaison disponible pour cette épreuve.</p>
        <?php else: ?>
            <?php foreach ($combinations as $cIdx => $combo): ?>
                <?php
                $comboId = (int) ($combo['id'] ?? 0);
                $comboTitle = (string) ($combo['title'] ?? ('Combinaison ' . ($combo['combo_number'] ?? '')));
                $comboTasks = $combo['tasks'] ?? [];
                ?>
                <div class="combinaison<?php echo $cIdx === 0 ? ' active' : ''; ?>" data-id="<?php echo htmlspecialchars((string) ($combo['combo_number'] ?? '')); ?>">
                    <div class="combinaison-header tcf-combo-bar" role="button" tabindex="0" aria-expanded="<?php echo $cIdx === 0 ? 'true' : 'false'; ?>">
                        <div class="tcf-combo-bar__left">
                            <h2><?php echo htmlspecialchars($comboTitle); ?></h2>
                        </div>
                        <div class="tcf-combo-bar__right">
                            <button type="button"
                                class="tcf-combo-sim-btn ee-combo-sim-toggle<?php echo $simLocked ? ' is-disabled' : ''; ?>"
                                aria-expanded="false"
                                aria-label="<?php echo $simLocked ? 'Connectez-vous pour utiliser le simulateur' : 'Ouvrir le simulateur'; ?>"
                                title="<?php echo $simLocked ? 'Connectez-vous pour utiliser le simulateur' : 'Simulateur IA'; ?>"
                                <?php echo $simLocked ? 'aria-disabled="true"' : ''; ?>>
                                <i class="bx bx-play"></i>
                                <span>Simulateur</span>
                            </button>
                            <span class="icon tcf-combo-bar__chevron">▼</span>
                        </div>
                    </div>
                    <div class="combinaison-content"<?php echo $cIdx === 0 ? ' style="display:block;"' : ''; ?>>
                        <div class="ee-task-grid">
                            <?php
                            $eeTaskMeta = [
                                '1' => ['type' => 'Message court', 'words' => '60-120 mots', 'time' => '10 min'],
                                '2' => ['type' => 'Narration', 'words' => '120-150 mots', 'time' => '20 min'],
                                '3' => ['type' => 'Argumentation', 'words' => '120-180 mots', 'time' => '30 min'],
                            ];
                            ?>
                            <?php foreach ($comboTasks as $task): ?>
                                <?php
                                $taskNum = (string) (int) ($task['task_number'] ?? 0);
                                if ($taskNum === '0') {
                                    $taskNum = '1';
                                }
                                $meta = $eeTaskMeta[$taskNum] ?? ['type' => 'Tâche', 'words' => '', 'time' => ''];
                                $correction = (string) ($task['correction'] ?? '');
                                if ($taskNum === '3') {
                                    $correction = preg_replace('/<h4[^>]*>\s*Points?\s*clés?.*?<\/h4>/iu', '', $correction) ?? $correction;
                                }
                                $hasCorr = trim(strip_tags($correction)) !== '';
                                $prompt = (string) ($task['prompt'] ?? '');
                                ?>
                                <div class="tache ee-tache ee-tache--<?php echo htmlspecialchars($taskNum); ?>">
                                    <div class="ee-tache__head">
                                        <div class="ee-tache__title">
                                            <span class="ee-tache__num" aria-hidden="true"><?php echo htmlspecialchars($taskNum); ?></span>
                                            <h3>Tâche <?php echo htmlspecialchars($taskNum); ?></h3>
                                        </div>
                                        <p class="ee-tache__meta">
                                            <span><?php echo htmlspecialchars($meta['type']); ?></span>
                                            <span class="ee-tache__dot" aria-hidden="true">•</span>
                                            <span><?php echo htmlspecialchars($meta['words']); ?></span>
                                            <span class="ee-tache__dot" aria-hidden="true">•</span>
                                            <span class="ee-tache__time"><i class="bx bx-time-five" aria-hidden="true"></i> <?php echo htmlspecialchars($meta['time']); ?></span>
                                        </p>
                                    </div>
                                    <h4 class="enonce-label">Énoncé</h4>
                                    <div class="ee-rich-text"><?php echo tcf_format_rich($prompt); ?></div>
                                    <?php foreach (($task['documents'] ?? []) as $idx => $doc): ?>
                                        <?php
                                        $docTitle = (string) ($doc['title'] ?? ('Document ' . ($idx + 1)));
                                        $docContent = (string) ($doc['content'] ?? '');
                                        ?>
                                        <div class="document">
                                            <?php if (trim($docTitle) !== ''): ?>
                                                <h4><?php echo htmlspecialchars($docTitle); ?></h4>
                                            <?php endif; ?>
                                            <div class="ee-rich-text"><?php echo tcf_format_rich($docContent); ?></div>
                                        </div>
                                    <?php endforeach; ?>

                                    <?php
                                    $taskId = (int) ($task['id'] ?? 0);
                                    $wordMin = (string) ($task['word_min'] ?? '');
                                    $wordMax = (string) ($task['word_max'] ?? '');
                                    $promptAttr = htmlspecialchars($prompt, ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <div class="tcf-task-actions">
                                        <button
                                            type="button"
                                            class="tcf-sim-pill ee-task-sim-toggle<?php echo $simLocked ? ' is-disabled' : ''; ?>"
                                            aria-expanded="false"
                                            title="<?php echo $simLocked ? 'Connectez-vous pour utiliser le simulateur' : 'Simulateur IA'; ?>"
                                            <?php echo $simLocked ? 'aria-disabled="true"' : ''; ?>
                                            data-task-id="<?php echo $taskId; ?>"
                                            data-task-number="<?php echo htmlspecialchars($taskNum); ?>"
                                            data-exam-id="<?php echo (int) $examId; ?>"
                                            data-combo-id="<?php echo $comboId; ?>"
                                            data-word-min="<?php echo htmlspecialchars($wordMin); ?>"
                                            data-word-max="<?php echo htmlspecialchars($wordMax); ?>"
                                            data-prompt="<?php echo $promptAttr; ?>">
                                            <i class="bx bx-play" aria-hidden="true"></i>
                                            Simulateur
                                        </button>
                                        <?php if ($hasCorr): ?>
                                            <button type="button" class="tcf-corr-pill ee-corr-toggle" aria-expanded="false">
                                                Voir la correction
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <div class="tcf-task-sim-panel" hidden>
                                        <textarea class="form-control ee-user-text" rows="6" placeholder="Rédigez votre réponse…"></textarea>
                                        <button type="button" class="simulate-btn ee-ai-correct-btn">Envoyer</button>
                                        <div class="ee-ai-result"></div>
                                    </div>

                                    <?php if ($hasCorr): ?>
                                        <div class="correction tcf-correction-panel">
                                            <h4>Correction Tâche <?php echo htmlspecialchars($taskNum); ?></h4>
                                            <div class="ee-rich-text"><?php echo tcf_format_rich($correction); ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="tcf-combo-sim-panel" hidden>
                            <div class="tcf-sim-task-picker">
                                <?php foreach ($comboTasks as $tIdx => $task): ?>
                                    <?php
                                    $taskId = (int) ($task['id'] ?? 0);
                                    $taskNum = (string) (int) ($task['task_number'] ?? ($tIdx + 1));
                                    if ($taskNum === '0') {
                                        $taskNum = (string) ($tIdx + 1);
                                    }
                                    $prompt = (string) ($task['prompt'] ?? '');
                                    $wordMin = (string) ($task['word_min'] ?? '');
                                    $wordMax = (string) ($task['word_max'] ?? '');
                                    ?>
                                    <button
                                        type="button"
                                        class="tcf-sim-task-btn<?php echo $tIdx === 0 ? ' is-active' : ''; ?>"
                                        data-task-id="<?php echo $taskId; ?>"
                                        data-task-number="<?php echo htmlspecialchars($taskNum); ?>"
                                        data-exam-id="<?php echo (int) $examId; ?>"
                                        data-combo-id="<?php echo $comboId; ?>"
                                        data-word-min="<?php echo htmlspecialchars($wordMin); ?>"
                                        data-word-max="<?php echo htmlspecialchars($wordMax); ?>"
                                        data-prompt="<?php echo htmlspecialchars($prompt, ENT_QUOTES, 'UTF-8'); ?>">
                                        Tâche <?php echo htmlspecialchars($taskNum); ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <div class="tcf-sim-enonce-preview" aria-live="polite"></div>
                            <textarea class="form-control ee-user-text" rows="6" placeholder="Rédigez votre réponse..."></textarea>
                            <button type="button" class="simulate-btn ee-ai-correct-btn">Envoyer</button>
                            <div class="ee-ai-result"></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<?php include __DIR__ . '/includes/cookie_banner.php'; ?>
<script src="<?php echo htmlspecialchars(site_href('Assets/javascript/script_tcf.js')); ?>?v=nav-active-epreuve"></script>
<script>
(function () {
    var api = <?php echo json_encode($apiHref); ?>;
    var loggedIn = <?php echo $loggedIn ? 'true' : 'false'; ?>;
    var loginHref = <?php echo json_encode($loginHref); ?>;

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (m) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
        });
    }
    function requireSimLogin() {
        if (loggedIn) return true;
        window.location.href = loginHref;
        return false;
    }
    function post(action, fields) {
        var fd = new FormData();
        fd.append('action', action);
        Object.keys(fields || {}).forEach(function (k) { fd.append(k, fields[k]); });
        return fetch(api, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); });
    }

    function setComboOpen(combo, open) {
        if (!combo) return;
        combo.classList.toggle('active', open);
        var content = combo.querySelector('.combinaison-content');
        var header = combo.querySelector('.combinaison-header');
        if (content) content.style.display = open ? 'block' : 'none';
        if (header) header.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function toggleCombo(combo) {
        if (!combo) return;
        setComboOpen(combo, !combo.classList.contains('active'));
    }

    function closeCorrection(task) {
        if (!task) return;
        var btn = task.querySelector('.ee-corr-toggle');
        var panel = task.querySelector('.tcf-correction-panel');
        if (btn) {
            btn.setAttribute('aria-expanded', 'false');
            btn.textContent = 'Voir la correction';
        }
        if (panel) panel.classList.remove('is-open');
    }

    function setTaskCorrectionLocked(task, locked) {
        if (!task) return;
        var corr = task.querySelector('.ee-corr-toggle');
        if (!corr) return;
        corr.disabled = !!locked;
        corr.classList.toggle('is-disabled', !!locked);
        corr.setAttribute('aria-disabled', locked ? 'true' : 'false');
        if (locked) closeCorrection(task);
    }

    function setComboCorrectionsLocked(combo, locked) {
        if (!combo) return;
        combo.querySelectorAll('.tache').forEach(function (task) {
            // Keep locked if that task's own simulator is open
            var taskSimOpen = task.querySelector('.ee-task-sim-toggle[aria-expanded="true"]');
            setTaskCorrectionLocked(task, locked || !!taskSimOpen);
        });
    }

    function closeTaskSimulator(task) {
        if (!task) return;
        var btn = task.querySelector('.ee-task-sim-toggle');
        var panel = task.querySelector('.tcf-task-sim-panel');
        if (btn) {
            btn.setAttribute('aria-expanded', 'false');
            btn.classList.remove('is-active');
        }
        if (panel) panel.hidden = true;
        var combo = task.closest('.combinaison');
        var comboSimOpen = combo && combo.querySelector('.ee-combo-sim-toggle[aria-expanded="true"]');
        setTaskCorrectionLocked(task, !!comboSimOpen);
    }

    function openTaskSimulator(task) {
        if (!task) return;
        var combo = task.closest('.combinaison');
        if (combo) {
            setComboOpen(combo, true);
            // Close combo-level simulator if open
            var comboBtn = combo.querySelector('.ee-combo-sim-toggle');
            var comboPanel = combo.querySelector('.tcf-combo-sim-panel');
            if (comboBtn) {
                comboBtn.setAttribute('aria-expanded', 'false');
                comboBtn.classList.remove('is-active');
            }
            if (comboPanel) comboPanel.hidden = true;
            // Close other task sims in same combo
            combo.querySelectorAll('.tache').forEach(function (other) {
                if (other !== task) closeTaskSimulator(other);
            });
        }
        var btn = task.querySelector('.ee-task-sim-toggle');
        var panel = task.querySelector('.tcf-task-sim-panel');
        if (btn) {
            btn.setAttribute('aria-expanded', 'true');
            btn.classList.add('is-active');
        }
        if (panel) {
            panel.hidden = false;
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        setTaskCorrectionLocked(task, true);
    }

    function bindAiSend(panel, getActiveMeta) {
        var ta = panel.querySelector('.ee-user-text');
        var out = panel.querySelector('.ee-ai-result');
        var sendBtn = panel.querySelector('.ee-ai-correct-btn');
        if (!sendBtn || !ta || !out) return;
        sendBtn.addEventListener('click', function () {
            if (!requireSimLogin()) return;
            var meta = getActiveMeta();
            if (!meta) return;
            var text = (ta.value || '').trim();
            if (text.length < 10) {
                out.innerHTML = '<p style="color:#b91c1c;">Veuillez rédiger une réponse plus complète.</p>';
                return;
            }
            sendBtn.disabled = true;
            sendBtn.textContent = 'Envoi…';
            post('ai_correct', {
                task_id: meta.taskId || '',
                exam_id: meta.examId || '',
                combo_id: meta.comboId || '',
                user_text: text,
                word_min: meta.wordMin || '',
                word_max: meta.wordMax || '',
                task_prompt: meta.prompt || ''
            }).then(function (j) {
                if (j && j.reason === 'login') {
                    window.location.href = loginHref;
                    return;
                }
                if (!j || !j.success || !j.feedback) {
                    out.innerHTML = '<p style="color:#b91c1c;">' + esc((j && j.message) || 'Erreur de correction simulation.') + '</p>';
                    return;
                }
                var f = j.feedback;
                var tips = Array.isArray(f.tips) ? f.tips.map(function (t) { return '<li>' + esc(t) + '</li>'; }).join('') : '';
                out.innerHTML =
                    '<p><strong>Niveau:</strong> ' + esc(f.cefr_level || 'N/A') +
                    ' · <strong>Score:</strong> ' + esc(f.score_global || 'N/A') + '/20 · <strong>Mots:</strong> ' + esc(j.word_count || 0) + '</p>' +
                    '<p><strong>Remarques:</strong><br>' + esc(f.remarks || '') + '</p>' +
                    '<p><strong>Corrections:</strong><br>' + esc(f.corrections || '') + '</p>' +
                    '<p><strong>Version améliorée:</strong><br>' + esc(f.improved_text || '') + '</p>' +
                    (tips ? '<p><strong>Conseils:</strong></p><ul>' + tips + '</ul>' : '');
            }).catch(function () {
                out.innerHTML = '<p style="color:#b91c1c;">Erreur réseau pendant la correction.</p>';
            }).finally(function () {
                sendBtn.disabled = false;
                sendBtn.textContent = 'Envoyer';
            });
        });
    }

    document.querySelectorAll('.combinaison-header').forEach(function (h) {
        h.addEventListener('click', function (e) {
            if (e.target.closest('.ee-combo-sim-toggle')) return;
            toggleCombo(h.parentElement);
        });
        h.addEventListener('keydown', function (e) {
            if (e.target.closest('.ee-combo-sim-toggle')) return;
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleCombo(h.parentElement);
            }
        });
    });

    document.querySelectorAll('.ee-corr-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (btn.disabled || btn.classList.contains('is-disabled')) return;
            var task = btn.closest('.tache');
            var panel = task ? task.querySelector('.tcf-correction-panel') : null;
            if (!panel) return;
            var open = btn.getAttribute('aria-expanded') === 'true';
            open = !open;
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            btn.textContent = open ? 'Masquer la correction' : 'Voir la correction';
            panel.classList.toggle('is-open', open);
        });
    });

    document.querySelectorAll('.ee-task-sim-toggle').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (btn.disabled || btn.classList.contains('is-disabled') || !requireSimLogin()) return;
            var task = btn.closest('.tache');
            if (!task) return;
            var open = btn.getAttribute('aria-expanded') === 'true';
            if (open) closeTaskSimulator(task);
            else openTaskSimulator(task);
        });
    });

    document.querySelectorAll('.tcf-task-sim-panel').forEach(function (panel) {
        var task = panel.closest('.tache');
        var toggle = task ? task.querySelector('.ee-task-sim-toggle') : null;
        bindAiSend(panel, function () {
            if (!toggle) return null;
            return {
                taskId: toggle.getAttribute('data-task-id') || '',
                examId: toggle.getAttribute('data-exam-id') || '',
                comboId: toggle.getAttribute('data-combo-id') || '',
                wordMin: toggle.getAttribute('data-word-min') || '',
                wordMax: toggle.getAttribute('data-word-max') || '',
                prompt: toggle.getAttribute('data-prompt') || ''
            };
        });
    });

    function activeSimTask(panel) {
        return panel ? panel.querySelector('.tcf-sim-task-btn.is-active') : null;
    }

    function updateComboEnoncePreview(panel) {
        var preview = panel.querySelector('.tcf-sim-enonce-preview');
        var active = activeSimTask(panel);
        if (!preview || !active) return;
        var num = active.getAttribute('data-task-number') || '';
        var prompt = active.getAttribute('data-prompt') || '';
        preview.innerHTML =
            '<div class="tcf-sim-enonce-card">' +
            '<strong>Énoncé — Tâche ' + esc(num) + '</strong>' +
            '<p>' + esc(prompt) + '</p>' +
            '</div>';
    }

    document.querySelectorAll('.ee-combo-sim-toggle').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (btn.disabled || btn.classList.contains('is-disabled') || !requireSimLogin()) return;
            var combo = btn.closest('.combinaison');
            if (!combo) return;
            var panel = combo.querySelector('.tcf-combo-sim-panel');
            if (!panel) return;
            var open = btn.getAttribute('aria-expanded') === 'true';
            open = !open;
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            btn.classList.toggle('is-active', open);
            if (open) {
                setComboOpen(combo, true);
                // Close per-task sims
                combo.querySelectorAll('.tache').forEach(closeTaskSimulator);
                panel.hidden = false;
                updateComboEnoncePreview(panel);
                setComboCorrectionsLocked(combo, true);
                panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                panel.hidden = true;
                setComboCorrectionsLocked(combo, false);
            }
        });
    });

    document.querySelectorAll('.tcf-combo-sim-panel').forEach(function (panel) {
        var drafts = {};
        var ta = panel.querySelector('.ee-user-text');
        var out = panel.querySelector('.ee-ai-result');
        var taskBtns = panel.querySelectorAll('.tcf-sim-task-btn');

        function currentKey() {
            var active = activeSimTask(panel);
            return active ? (active.getAttribute('data-task-id') || '') : '';
        }

        updateComboEnoncePreview(panel);

        taskBtns.forEach(function (tb) {
            tb.addEventListener('click', function () {
                var prev = currentKey();
                if (ta && prev) drafts[prev] = ta.value || '';
                taskBtns.forEach(function (b) { b.classList.toggle('is-active', b === tb); });
                var key = tb.getAttribute('data-task-id') || '';
                if (ta) ta.value = drafts[key] || '';
                if (out) out.innerHTML = '';
                updateComboEnoncePreview(panel);
            });
        });

        bindAiSend(panel, function () {
            var active = activeSimTask(panel);
            if (!active) return null;
            return {
                taskId: active.getAttribute('data-task-id') || '',
                examId: active.getAttribute('data-exam-id') || '',
                comboId: active.getAttribute('data-combo-id') || '',
                wordMin: active.getAttribute('data-word-min') || '',
                wordMax: active.getAttribute('data-word-max') || '',
                prompt: active.getAttribute('data-prompt') || ''
            };
        });
    });

    var searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var q = (searchInput.value || '').trim().toLowerCase();
            document.querySelectorAll('#ee-combos-container .combinaison').forEach(function (c) {
                if (!q) { c.style.display = 'block'; return; }
                var id = String(c.getAttribute('data-id') || '').toLowerCase();
                var titleEl = c.querySelector('.combinaison-header h2');
                var title = titleEl ? titleEl.textContent.toLowerCase() : '';
                c.style.display = (id.indexOf(q) >= 0 || title.indexOf('combinaison ' + q) >= 0) ? 'block' : 'none';
            });
        });
    }
})();
</script>
</body>
</html>
