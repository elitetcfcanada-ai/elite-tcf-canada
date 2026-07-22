<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/subscription_access.php';
require_once __DIR__ . '/includes/rich_text.php';

$examId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($examId <= 0) {
    header('Location: ' . site_href('Expresion_orale.php'));
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
         FROM tcf_eo_exams WHERE id = ? AND is_published = 1 LIMIT 1"
    );
    $st->execute([$examId]);
    $exam = $st->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Throwable $e) {
    $exam = null;
}

if (!$exam) {
    header('Location: ' . site_href('Expresion_orale.php'));
    exit;
}

$isPremium = strtolower((string) ($exam['visibility'] ?? 'gratuit')) === 'premium';
$returnPath = 'epreuve_eo.php?id=' . $examId;

if ($isPremium && !$loggedIn) {
    header('Location: ' . site_href('login.php?next=' . rawurlencode($returnPath)));
    exit;
}
if ($isPremium && !$premiumOk) {
    header('Location: ' . site_href('abonnement.php'));
    exit;
}

$parts = [];
try {
    $stP = $pdo->prepare('SELECT * FROM tcf_eo_parts WHERE exam_id = ? ORDER BY sort_order ASC, part_number ASC, id ASC');
    $stP->execute([$examId]);
    $parts = $stP->fetchAll(PDO::FETCH_ASSOC);
    foreach ($parts as &$part) {
        $stS = $pdo->prepare('SELECT * FROM tcf_eo_subjects WHERE part_id = ? ORDER BY subject_number ASC, id ASC');
        $stS->execute([(int) $part['id']]);
        $part['subjects'] = $stS->fetchAll(PDO::FETCH_ASSOC);
        $tk = strtolower(trim((string) ($part['task_key'] ?? 'tache2')));
        if (!in_array($tk, ['tache1', 'tache2', 'tache3'], true)) {
            $tk = 'tache2';
        }
        $part['task_key'] = $tk;
    }
    unset($part);
} catch (Throwable $e) {
    $parts = [];
}

/** Regroupe Tâche 2 / 3 sous le même numéro de partie (UI mockup). */
$parties = [];
foreach ($parts as $part) {
    $num = max(1, (int) ($part['part_number'] ?? 1));
    if (!isset($parties[$num])) {
        $parties[$num] = [
            'part_number' => $num,
            'title' => 'Partie ' . $num,
            'tache2' => null,
            'tache3' => null,
        ];
    }
    $title = trim((string) ($part['part_title'] ?? ''));
    if ($title !== '') {
        $parties[$num]['title'] = $title;
    }
    $parties[$num][$part['task_key']] = $part;
}
ksort($parties, SORT_NUMERIC);

$taskMeta = [
    'tache1' => [
        'label' => 'Tâche 1',
        'sub' => 'Présentation',
        'info_title' => 'Tâche 1 — Présentation personnelle',
        'info_meta' => 'Durée 2 min · Sans préparation',
        'duration' => '2:00 minutes',
    ],
    'tache2' => [
        'label' => 'Tâche 2',
        'sub' => 'Interaction orale',
        'info_title' => 'Tâche 2 : Interaction',
        'info_meta' => 'Prép. 2 min | Durée 3 min 30 s',
    ],
    'tache3' => [
        'label' => 'Tâche 3',
        'sub' => 'Argumentation',
        'info_title' => 'Tâche 3 : Argumentation',
        'info_meta' => 'Prép. Aucune | Durée 4 min 30 s',
    ],
];

$t1Points = [
    ['n' => '1', 'title' => 'Identité', 'desc' => 'Nom, âge, ville'],
    ['n' => '2', 'title' => 'Formation', 'desc' => 'Études, travail'],
    ['n' => '3', 'title' => 'Loisirs', 'desc' => 'Passions, hobbies'],
    ['n' => '4', 'title' => 'Projets', 'desc' => 'Objectifs, TCF'],
];

$pageTitle = (string) ($exam['title'] ?? 'Expression Orale');

function eo_render_subjects(array $subjects): void
{
    foreach ($subjects as $s) {
        $corr = trim((string) ($s['correction'] ?? ''));
        $hasCorr = $corr !== '';
        ?>
        <div class="task-card eo-subject-card">
            <div class="card-number"><?php echo htmlspecialchars((string) ($s['subject_number'] ?? '')); ?></div>
            <h3>
                <i class="<?php echo htmlspecialchars((string) ($s['icon_class'] ?? 'bx bx-message-detail')); ?>"></i>
                <?php echo htmlspecialchars((string) ($s['title'] ?? '')); ?>
            </h3>
            <?php if (!empty($s['role_label'])): ?>
                <p class="eo-role-label"><?php echo htmlspecialchars((string) $s['role_label']); ?></p>
            <?php endif; ?>
            <div class="eo-prompt">
                <h4 class="enonce-label">Énoncé</h4>
                <div class="ee-rich-text"><?php echo tcf_format_rich((string) ($s['prompt'] ?? '')); ?></div>
            </div>
            <?php if ($hasCorr): ?>
                <div class="tcf-task-actions">
                    <button type="button" class="tcf-corr-pill eo-corr-toggle" aria-expanded="false">
                        Voir la correction
                    </button>
                </div>
                <div class="correction tcf-correction-panel">
                    <h4>Correction</h4>
                    <div class="eo-correction-body ee-rich-text"><?php echo tcf_format_rich($corr); ?></div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
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
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/style_sujets.css')); ?>?v=frame-margins">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/style_Expresion_Ecrite.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/expression_orale.css')); ?>?v=frame-margins">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/epreuve_reader.css')); ?>?v=frame-margins">
</head>
<body class="tcf-page-epreuve-reader">
<?php include __DIR__ . '/includes/header.php'; ?>

<section class="hero-banner">
    <div class="hero-content">
        <p class="hero-kicker"><i class="bx bxs-school"></i> Épreuve</p>
        <h1 class="hero-skill-title" style="color:#d30d0d!important;-webkit-text-fill-color:#d30d0d!important;"><?php echo htmlspecialchars($pageTitle); ?></h1>
        <p class="hero-lead">Ouvrez une partie, choisissez une tâche, puis consultez la correction.</p>
    </div>
</section>

<main class="tcf-epreuve-reader tcf-epreuve-reader--wide">
    <div id="eo-detail-container">
        <?php if (empty($parties)): ?>
            <p>Aucun sujet disponible pour cette épreuve.</p>
        <?php else: ?>
            <?php $pIdx = 0; foreach ($parties as $party): ?>
                <?php
                $subs2 = $party['tache2']['subjects'] ?? [];
                $subs3 = $party['tache3']['subjects'] ?? [];
                $count = count($subs2) + count($subs3);
                $defaultTask = !empty($subs2) ? 'tache2' : (!empty($subs3) ? 'tache3' : 'tache1');
                $openFirst = $pIdx === 0;
                $pIdx++;
                ?>
                <div class="combinaison eo-partie<?php echo $openFirst ? ' active' : ''; ?>">
                    <div class="combinaison-header tcf-combo-bar eo-part-bar" role="button" tabindex="0" aria-expanded="<?php echo $openFirst ? 'true' : 'false'; ?>">
                        <div class="tcf-combo-bar__left">
                            <h2><?php echo htmlspecialchars((string) $party['title']); ?></h2>
                        </div>
                        <div class="tcf-combo-bar__right">
                            <span class="eo-part-meta"><?php echo (int) $count; ?> sujet<?php echo $count > 1 ? 's' : ''; ?></span>
                            <span class="icon">▼</span>
                        </div>
                    </div>
                    <div class="combinaison-content eo-partie-body"<?php echo $openFirst ? ' style="display:block;"' : ''; ?>>
                        <nav class="eo-task-switcher" aria-label="Tâches de la partie">
                            <?php foreach ($taskMeta as $key => $meta): ?>
                                <button
                                    type="button"
                                    class="eo-task-switcher__btn<?php echo $key === $defaultTask ? ' is-active' : ''; ?>"
                                    data-eo-task="<?php echo htmlspecialchars($key); ?>"
                                    aria-pressed="<?php echo $key === $defaultTask ? 'true' : 'false'; ?>">
                                    <i class="bx bx-microphone" aria-hidden="true"></i>
                                    <span class="eo-task-switcher__texts">
                                        <strong><?php echo htmlspecialchars($meta['label']); ?></strong>
                                        <small><?php echo htmlspecialchars($meta['sub']); ?></small>
                                    </span>
                                </button>
                            <?php endforeach; ?>
                        </nav>

                        <!-- Tâche 1 : carte présentation (fixe, partout) -->
                        <div class="eo-task-panel" data-eo-task-panel="tache1"<?php echo $defaultTask === 'tache1' ? '' : ' hidden'; ?>>
                            <article class="eo-t1-card">
                                <header class="eo-t1-card__head">
                                    <div class="eo-t1-card__title-wrap">
                                        <span class="eo-t1-card__icon" aria-hidden="true"><i class="bx bx-microphone"></i></span>
                                        <div>
                                            <h3>Tâche 1 — Présentation personnelle</h3>
                                            <p>Expression orale TCF Canada</p>
                                        </div>
                                    </div>
                                    <span class="eo-t1-card__time"><i class="bx bx-time-five"></i> 2:00 minutes</span>
                                </header>
                                <div class="eo-t1-card__body">
                                    <p class="eo-t1-card__lead">Présentez-vous de manière structurée. L’examinateur vous invite à parler de vous pendant 2 minutes sans temps de préparation.</p>
                                    <h4 class="eo-t1-card__points-title">Points à aborder</h4>
                                    <div class="eo-t1-points">
                                        <?php foreach ($t1Points as $pt): ?>
                                            <div class="eo-t1-point">
                                                <span class="eo-t1-point__n"><?php echo htmlspecialchars($pt['n']); ?></span>
                                                <strong><?php echo htmlspecialchars($pt['title']); ?></strong>
                                                <small><?php echo htmlspecialchars($pt['desc']); ?></small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button type="button" class="eo-t1-soon" disabled>
                                        <i class="bx bx-play"></i> Bientôt disponible
                                    </button>
                                </div>
                            </article>
                        </div>

                        <!-- Tâche 2 -->
                        <div class="eo-task-panel" data-eo-task-panel="tache2"<?php echo $defaultTask === 'tache2' ? '' : ' hidden'; ?>>
                            <div class="eo-task-info">
                                <span class="eo-task-info__icon" aria-hidden="true"><i class="bx bx-time-five"></i></span>
                                <div>
                                    <strong><?php echo htmlspecialchars($taskMeta['tache2']['info_title']); ?></strong>
                                    <span><?php echo htmlspecialchars($taskMeta['tache2']['info_meta']); ?></span>
                                </div>
                            </div>
                            <div class="task-container">
                                <?php if (empty($subs2)): ?>
                                    <p class="eo-task-empty">Aucun sujet disponible pour cette tâche.</p>
                                <?php else: ?>
                                    <?php eo_render_subjects($subs2); ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Tâche 3 -->
                        <div class="eo-task-panel" data-eo-task-panel="tache3"<?php echo $defaultTask === 'tache3' ? '' : ' hidden'; ?>>
                            <div class="eo-task-info">
                                <span class="eo-task-info__icon" aria-hidden="true"><i class="bx bx-time-five"></i></span>
                                <div>
                                    <strong><?php echo htmlspecialchars($taskMeta['tache3']['info_title']); ?></strong>
                                    <span><?php echo htmlspecialchars($taskMeta['tache3']['info_meta']); ?></span>
                                </div>
                            </div>
                            <div class="task-container">
                                <?php if (empty($subs3)): ?>
                                    <p class="eo-task-empty">Aucun sujet disponible pour cette tâche.</p>
                                <?php else: ?>
                                    <?php eo_render_subjects($subs3); ?>
                                <?php endif; ?>
                            </div>
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
    function toggleCombo(combo) {
        if (!combo) return;
        var open = !combo.classList.contains('active');
        combo.classList.toggle('active', open);
        var content = combo.querySelector('.combinaison-content');
        var header = combo.querySelector('.combinaison-header');
        if (content) content.style.display = open ? 'block' : 'none';
        if (header) header.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    document.querySelectorAll('#eo-detail-container .combinaison-header').forEach(function (h) {
        h.addEventListener('click', function () { toggleCombo(h.parentElement); });
        h.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleCombo(h.parentElement);
            }
        });
    });

    document.querySelectorAll('#eo-detail-container .eo-partie-body').forEach(function (body) {
        var switcher = body.querySelector('.eo-task-switcher');
        if (!switcher) return;
        switcher.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-eo-task]');
            if (!btn || !switcher.contains(btn)) return;
            var key = btn.getAttribute('data-eo-task');
            switcher.querySelectorAll('[data-eo-task]').forEach(function (b) {
                var on = b === btn;
                b.classList.toggle('is-active', on);
                b.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
            body.querySelectorAll('[data-eo-task-panel]').forEach(function (panel) {
                panel.hidden = panel.getAttribute('data-eo-task-panel') !== key;
            });
        });
    });

    document.querySelectorAll('.eo-corr-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var card = btn.closest('.eo-subject-card');
            var panel = card ? card.querySelector('.tcf-correction-panel') : null;
            if (!panel) return;
            var open = btn.getAttribute('aria-expanded') === 'true';
            open = !open;
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            btn.textContent = open ? 'Masquer la correction' : 'Voir la correction';
            panel.classList.toggle('is-open', open);
        });
    });
})();
</script>
</body>
</html>
