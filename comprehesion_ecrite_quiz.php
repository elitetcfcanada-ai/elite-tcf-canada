<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/includes/config.php';
$ceApi = site_href('ce_api.php');
$backList = site_href('comprehesion_ecrite.php');
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $tcf_brand_title = 'ELITE TCF CANADA — Compréhension Écrite (quiz)';
    include __DIR__ . '/includes/tcf_brand_head.php';
    ?>
    <title>ELITE TCF CANADA — Compréhension Écrite (quiz)</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Source+Sans+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Assets/css/theme-vars.css">
    <link rel="stylesheet" href="Assets/css/comprehesion_Ecrite.css">
    <link rel="stylesheet" href="Assets/css/tcf-responsive-pills.css">
</head>

<body>
<div class="tcf-quiz-standalone-wrap">
    <div class="bg-pattern"></div>

    <div class="app-container" id="start-screen">
        <header class="app-header">
            <div class="logo-container">
                <div class="logo-icon">
                    <i class='bx bx-book-reader'></i>
                </div>
                <h1>ELITE TCF CANADA <span>Premium</span></h1>
                <p class="subtitle">Test de Connaissance du Français — Compréhension écrite</p>
            </div>
        </header>

        <main class="app-main">
            <p style="text-align:center;margin-bottom:12px;">
                <a href="<?php echo htmlspecialchars($backList); ?>" style="color:var(--primary,#d30d0d);font-weight:600;">
                    <i class='bx bx-arrow-back'></i> Retour aux épreuves
                </a>
            </p>
            <div class="quiz-card">
                <div class="card-icon">
                    <i class='bx bx-edit-alt'></i>
                </div>
                <div class="card-content">
                    <h2 id="quiz-title">Compréhension Écrite</h2>
                    <p id="quiz-description">Chargement de l’épreuve…</p>
                    <div class="card-badge">
                        <i class='bx bx-star'></i>
                        <span>ELITE TCF CANADA</span>
                    </div>
                </div>
                <div class="card-decoration"></div>
            </div>

            <div class="action-buttons">
                <button type="button" id="start-btn" class="btn btn-primary" disabled>
                    <i class='bx bx-play-circle'></i> Commencer le test
                </button>
            </div>
        </main>

        <footer class="app-footer">
            <p style="text-align: center; color: var(--gray); font-size: 0.85rem;">© ELITE TCF CANADA</p>
        </footer>
    </div>

    <div class="app-container hidden" id="quiz-screen">
        <div class="quiz-header">
            <div class="header-content">
                <div class="timer-container">
                    <div class="progress-container">
                        <div class="progress-bar">
                            <div class="progress" id="time-progress"></div>
                            <div class="progress-wave"></div>
                        </div>
                    </div>
                    <span class="timer" id="timer-display">60:00</span>
                </div>
                <button type="button" id="quit-btn" class="btn btn-danger">
                    <i class='bx bx-power-off'></i> Quitter
                </button>
            </div>
        </div>

        <main class="quiz-main">
            <div class="question-card">
                <div class="question-number" id="question-number">
                    <span>Question 1/1</span>
                    <span class="question-points" id="question-points">0 points</span>
                </div>
                <div id="situation-container" class="situation-container hidden">
                    <div class="situation-title">
                        <i class='bx bx-message-rounded'></i>
                        <span>Situation</span>
                    </div>
                    <div class="situation-text" id="situation-text"></div>
                </div>
                <div class="question-text" id="question-text"></div>

                <div class="answers-grid" id="answers-container"></div>
            </div>
        </main>

        <footer class="quiz-footer">
            <div class="navigation-buttons">
                <button type="button" id="prev-btn" class="btn btn-nav">
                    <i class='bx bx-chevron-left'></i> Précédent
                </button>
                <button type="button" id="next-btn" class="btn btn-nav">
                    Suivant <i class='bx bx-chevron-right'></i>
                </button>
                <button type="button" id="finish-btn" class="btn btn-finish hidden">
                    <i class='bx bx-flag'></i> Terminer
                </button>
            </div>

            <div class="indicators-container">
                <div class="indicators" id="question-indicators"></div>
            </div>
        </footer>
    </div>

    <div class="app-container hidden" id="results-screen">
        <div class="results-header">
            <h2><i class='bx bx-bar-chart-alt-2'></i> Vos résultats</h2>
        </div>

        <main class="results-main">
            <div class="score-display">
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
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class='bx bx-check-circle'></i></div>
                    <span class="stat-value" id="correct-answers">0</span>
                    <span class="stat-label">Bonnes réponses</span>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class='bx bx-x-circle'></i></div>
                    <span class="stat-value" id="incorrect-answers">0</span>
                    <span class="stat-label">Mauvaises réponses</span>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class='bx bx-time-five'></i></div>
                    <span class="stat-value" id="time-taken">0:00</span>
                    <span class="stat-label">Temps écoulé</span>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class='bx bx-trophy'></i></div>
                    <span class="stat-value" id="total-points">0</span>
                    <span class="stat-label">Points obtenus</span>
                </div>
            </div>

            <div class="results-indicators-container">
                <div class="results-indicators-title">
                    <i class='bx bx-list-check'></i>
                    <span>Récapitulatif des questions</span>
                </div>
                <div class="results-indicators" id="results-indicators"></div>
            </div>

            <div class="answers-review" id="answers-review"></div>
        </main>

        <footer class="results-footer">
            <button type="button" id="restart-btn" class="btn btn-primary">
                <i class='bx bx-refresh'></i> Recommencer
            </button>
            <button type="button" id="back-to-start-btn" class="btn btn-secondary">
                <i class='bx bx-home'></i> Retour à l’accueil
            </button>
        </footer>
    </div>

    <script>
        window.TCF_CE_API = <?php echo json_encode($ceApi, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    </script>
    <script src="Assets/javascript/comprehesion_quiz_dynamic.js"></script>
</div>
</body>

</html>
