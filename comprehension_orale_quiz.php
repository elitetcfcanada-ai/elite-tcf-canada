<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/subscription_access.php';

$examId = isset($_GET['exam_id']) ? (int) $_GET['exam_id'] : 0;
$returnPath = 'comprehension_orale_quiz.php' . ($examId > 0 ? ('?exam_id=' . $examId) : '');
if ($examId > 0) {
    try {
        $st = $pdo->prepare('SELECT id, visibility FROM tcf_co_exams WHERE id = ? AND is_published = 1 LIMIT 1');
        $st->execute([$examId]);
        $examRow = $st->fetch(PDO::FETCH_ASSOC) ?: null;
        if ($examRow && strtolower((string) ($examRow['visibility'] ?? 'gratuit')) === 'premium') {
            if (empty($_SESSION['user_id'])) {
                header('Location: ' . site_href('login.php?next=' . rawurlencode($returnPath)));
                exit;
            }
            $stU = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
            $stU->execute([(int) $_SESSION['user_id']]);
            $viewer = $stU->fetch(PDO::FETCH_ASSOC) ?: null;
            if (!$viewer || !tcf_user_has_premium_access($viewer)) {
                header('Location: ' . site_href('abonnement.php'));
                exit;
            }
        }
    } catch (Throwable $e) {
        // laisser le JS / API gérer
    }
}

$coApi = site_href('co_api.php');
$backList = site_href('comprehension_orale.php');
$loginUrl = site_href('login.php');
$aboUrl = site_href('abonnement.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $tcf_brand_title = 'ELITE TCF CANADA — Compréhension orale (quiz)';
    include __DIR__ . '/includes/tcf_brand_head.php';
    ?>
    <title>ELITE TCF CANADA — Compréhension orale (quiz)</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Source+Sans+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Assets/css/theme-vars.css">
    <link rel="stylesheet" href="Assets/css/comprehesion_Orale.css?v=20">
    <link rel="stylesheet" href="Assets/css/header_footer.css">
    <link rel="stylesheet" href="Assets/css/tcf-responsive-pills.css">
    <link rel="stylesheet" href="Assets/css/quiz-site-chrome.css?v=17">
    <link rel="stylesheet" href="Assets/css/tcf-quiz-pro.css?v=results-taller-10">
</head>
<body class="tcf-quiz-with-site-nav">
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="tcf-quiz-standalone-wrap">
    <div class="bg-pattern"></div>

    <div class="app-container tcf-qpro-start" id="start-screen">
        <a class="tcf-qpro-back" href="<?php echo htmlspecialchars($backList); ?>">
            <i class='bx bx-arrow-back'></i> Retour aux épreuves
        </a>
        <div class="tcf-qpro-start-card">
            <h2 id="quiz-title">Épreuve</h2>
            <p id="quiz-description" class="tcf-qpro-desc">Chargement de l’épreuve…</p>
            <ul class="tcf-qpro-meta" aria-label="Informations de l’épreuve">
                <li>
                    <strong id="quiz-meta-questions">—</strong>
                    <span>Questions</span>
                </li>
                <li>
                    <strong id="quiz-meta-duration">—</strong>
                    <span>Durée</span>
                </li>
                <li>
                    <strong>Audio</strong>
                    <span>Format</span>
                </li>
            </ul>
            <div class="tcf-qpro-start-actions">
                <button type="button" id="start-btn" class="btn btn-primary" disabled>
                    <i class='bx bx-play-circle'></i> Commencer le test
                </button>
            </div>
        </div>
    </div>

    <div class="app-container hidden" id="quiz-screen">
        <div class="co-exam-layout">
            <aside class="co-exam-sidebar" aria-label="Navigation de l’examen">
                <div class="co-exam-timer" id="co-exam-timer-block">
                    <div class="co-exam-timer__top">
                        <span class="timer" id="timer-display">35:00</span>
                        <span class="co-exam-timer__label">Temps restant</span>
                    </div>
                    <div class="progress-container">
                        <div class="progress-bar">
                            <div class="progress" id="time-progress"></div>
                        </div>
                    </div>
                </div>

                <div class="co-exam-nav-panel">
                    <h3 class="co-exam-nav-panel__title">Navigation des questions</h3>
                    <div class="indicators-container">
                        <div class="indicators" id="question-indicators"></div>
                    </div>
                    <ul class="co-exam-legend" aria-label="Légende">
                        <li><span class="co-exam-legend__swatch is-current"></span> Actuelle</li>
                        <li><span class="co-exam-legend__swatch is-answered"></span> Répondue</li>
                        <li><span class="co-exam-legend__swatch is-todo"></span> Non rép.</li>
                    </ul>
                </div>

                <button type="button" id="quit-btn" class="btn btn-danger co-exam-quit">
                    <i class='bx bx-log-out'></i> Quitter l’examen
                </button>
            </aside>

            <div class="co-exam-main">
                <main class="quiz-main">
                    <div class="question-card">
                        <div class="question-number" id="question-number">
                            <span>Question : 1</span>
                        </div>

                        <div class="situation-container">
                            <div class="situation-title">
                                <i class='bx bx-image'></i>
                                <span>Situation visuelle</span>
                            </div>
                            <img id="situation-image" class="situation-image" src="" alt="">
                        </div>

                        <p class="question-text" id="co-question-text"></p>

                        <div class="audio-container" id="co-audio-box">
                            <div class="situation-title">
                                <i class='bx bx-headphone'></i>
                                <span>Extrait audio</span>
                            </div>
                            <p class="co-tts-status" id="co-tts-status" aria-live="polite">Prêt à écouter</p>

                            <div class="co-audio-player" id="co-audio-player" role="group" aria-label="Lecteur audio">
                                <button type="button" id="play-btn" class="co-audio-player__btn play-btn" aria-pressed="false" aria-label="Écouter">
                                    <i class="bx bx-play"></i>
                                </button>
                                <div class="co-audio-player__track">
                                    <div class="co-audio-player__bar" id="co-audio-progress-bar" role="slider" tabindex="0" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" aria-label="Progression — cliquer pour revenir en arrière">
                                        <div class="co-audio-player__fill" id="co-audio-progress-fill"></div>
                                    </div>
                                    <div class="co-audio-player__times">
                                        <span id="co-audio-time-current">0:00</span>
                                        <span id="co-audio-time-total">0:00</span>
                                    </div>
                                </div>
                                <button type="button" id="co-audio-replay-btn" class="co-audio-player__replay" title="Réécouter" aria-label="Réécouter depuis le début">
                                    <i class="bx bx-revision"></i>
                                </button>
                            </div>

                            <audio id="question-audio" class="audio-player hidden" controls></audio>
                        </div>

                        <div class="answers-grid" id="answers-container"></div>
                    </div>
                </main>

                <footer class="quiz-footer">
                    <div class="navigation-buttons">
                        <button type="button" id="prev-btn" class="btn btn-nav">
                            <i class='bx bx-chevron-left'></i> Précédent
                        </button>
                        <div class="co-exam-meta-badge" id="co-exam-meta-badge" aria-live="polite">
                            <span class="co-exam-meta-badge__level" id="question-level-badge">—</span>
                            <span class="co-exam-meta-badge__pts" id="question-points">0 pts</span>
                        </div>
                        <button type="button" id="next-btn" class="btn btn-nav btn-nav--next">
                            Suivant <i class='bx bx-chevron-right'></i>
                        </button>
                        <button type="button" id="finish-btn" class="btn btn-finish hidden">
                            <i class='bx bx-flag'></i> Terminer
                        </button>
                    </div>
                </footer>
            </div>
        </div>
    </div>

    <div class="app-container tcf-qpro-results hidden" id="results-screen">
        <div class="results-header">
            <h2><i class='bx bx-bar-chart-alt-2'></i> Vos résultats</h2>
        </div>
        <main class="results-main">
            <div class="score-display tcf-qpro-score-wrap">
                <div class="score-circle">
                    <svg viewBox="0 0 36 36">
                        <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="circle-fill" id="score-circle" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="score-text">
                        <span id="percentage-text">0%</span>
                        <span id="level-text">Niveau A1</span>
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class='bx bx-check-circle'></i></div>
                        <span class="stat-value" id="correct-answers">0</span>
                        <span class="stat-label">Bonnes</span>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class='bx bx-x-circle'></i></div>
                        <span class="stat-value" id="incorrect-answers">0</span>
                        <span class="stat-label">Mauvaises</span>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class='bx bx-time-five'></i></div>
                        <span class="stat-value" id="time-taken">0:00</span>
                        <span class="stat-label">Temps</span>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class='bx bx-trophy'></i></div>
                        <span class="stat-value" id="total-points">0</span>
                        <span class="stat-label">Points</span>
                    </div>
                </div>
            </div>
            <footer class="results-footer">
                <button type="button" id="show-correction-btn" class="btn btn-primary">
                    <i class='bx bx-check-shield'></i>
                    <span class="btn-txt btn-txt--full">Voir la correction</span>
                    <span class="btn-txt btn-txt--short">Correction</span>
                </button>
                <button type="button" id="restart-btn" class="btn btn-secondary">
                    <i class='bx bx-refresh'></i>
                    <span class="btn-txt btn-txt--full">Recommencer</span>
                    <span class="btn-txt btn-txt--short">Recommencer</span>
                </button>
                <a href="<?php echo htmlspecialchars($backList); ?>" id="back-to-start-btn" class="btn btn-outline">
                    <i class='bx bx-home'></i>
                    <span class="btn-txt btn-txt--full">Accueil</span>
                    <span class="btn-txt btn-txt--short">Accueil</span>
                </a>
            </footer>

            <div class="tcf-correction-panel hidden" id="correction-panel" hidden aria-hidden="true">
                <div class="results-indicators-container">
                    <div class="results-indicators-title">
                        <i class='bx bx-list-check'></i>
                        <span>Récapitulatif des questions</span>
                    </div>
                    <div class="results-indicators" id="results-indicators"></div>
                </div>
                <div class="answers-review" id="answers-review"></div>
                <nav class="tcf-qpro-review-nav" id="correction-nav" aria-label="Navigation de la correction" hidden>
                    <button type="button" class="btn tcf-qpro-review-nav__btn" id="correction-prev-btn">
                        <i class='bx bx-chevron-left'></i>
                        <span>Précédent</span>
                    </button>
                    <span class="tcf-qpro-review-nav__pos" id="correction-pos">1 / 1</span>
                    <button type="button" class="btn tcf-qpro-review-nav__btn tcf-qpro-review-nav__btn--next" id="correction-next-btn">
                        <span>Suivant</span>
                        <i class='bx bx-chevron-right'></i>
                    </button>
                    <button type="button" class="btn tcf-qpro-review-nav__btn tcf-qpro-review-nav__btn--restart" id="correction-restart-btn" hidden>
                        <i class='bx bx-refresh'></i>
                        <span>Recommencer</span>
                    </button>
                </nav>
            </div>
        </main>
    </div>

    <script>
        window.TCF_CO_API = <?php echo json_encode($coApi, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        window.TCF_LOGIN_URL = <?php echo json_encode($loginUrl); ?>;
        window.TCF_ABO_URL = <?php echo json_encode($aboUrl); ?>;
        window.TCF_BACK_URL = <?php echo json_encode($backList); ?>;
    </script>
    <script src="Assets/javascript/tcf-tts.js?v=8"></script>
    <script src="Assets/javascript/tcf_quiz_dialog.js?v=1"></script>
    <script src="Assets/javascript/comprehension_quiz_dynamic.js?v=19"></script>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
<?php include __DIR__ . '/includes/cookie_banner.php'; ?>
</body>
</html>
