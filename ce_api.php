<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/subscription_access.php';
require_once __DIR__ . '/includes/tcf_notifications_helper.php';
require_once __DIR__ . '/includes/rich_text.php';
require_once __DIR__ . '/includes/admin_roles.php';

header('Content-Type: application/json; charset=utf-8');

function ce_json(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function ce_is_admin(): bool
{
    return tcf_is_staff_admin();
}

function ce_slug(string $title): string
{
    $s = mb_strtolower(trim($title));
    $s = preg_replace('/[àáâãäå]/u', 'a', $s);
    $s = preg_replace('/[èéêë]/u', 'e', $s);
    $s = preg_replace('/[ìíîï]/u', 'i', $s);
    $s = preg_replace('/[òóôõö]/u', 'o', $s);
    $s = preg_replace('/[ùúûü]/u', 'u', $s);
    $s = preg_replace('/[ç]/u', 'c', $s);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-');
}


function ce_is_logged(): bool
{
    return !empty($_SESSION['user_id']);
}

function ce_can_view_premium_consigne(PDO $pdo): bool
{
    if (!ce_is_logged()) {
        return false;
    }
    try {
        $stmtU = $pdo->prepare('SELECT * FROM users WHERE id=?');
        $stmtU->execute([(int) $_SESSION['user_id']]);
        $userRow = $stmtU->fetch(PDO::FETCH_ASSOC);

        return (bool) ($userRow && tcf_user_has_premium_access($userRow));
    } catch (Throwable $e) {
        return false;
    }
}

/** Consignes CE (3 sections : structure, techniques, erreurs). */
function ce_ensure_ce_consignes_table(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_ce_consignes (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL DEFAULT 'Consignes Compréhension Écrite',
            body LONGTEXT NOT NULL,
            section_key VARCHAR(40) NOT NULL DEFAULT 'structure',
            visibility VARCHAR(20) NOT NULL DEFAULT 'gratuit',
            is_published TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 1,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    try {
        $cols = $pdo->query('SHOW COLUMNS FROM tcf_ce_consignes')->fetchAll(PDO::FETCH_ASSOC);
        $names = array_map(static fn ($c) => (string) ($c['Field'] ?? ''), $cols);
        if (!in_array('section_key', $names, true)) {
            $pdo->exec("ALTER TABLE tcf_ce_consignes ADD COLUMN section_key VARCHAR(40) NOT NULL DEFAULT 'structure' AFTER body");
        }
        if (!in_array('sort_order', $names, true)) {
            $pdo->exec('ALTER TABLE tcf_ce_consignes ADD COLUMN sort_order INT NOT NULL DEFAULT 1 AFTER is_published');
        }
    } catch (Throwable $e) {
        // ignore migrate errors on restricted hosts
    }
}

function ce_seed_ce_consignes(PDO $pdo): void
{
    require_once __DIR__ . '/includes/tcf_consignes_defaults.php';
    ce_ensure_ce_consignes_table($pdo);
    $bodies = tcf_consigne_ce_bodies();
    $titles = [
        'structure' => 'Structure de l’épreuve et stratégie de scoring',
        'techniques' => 'Les 5 techniques essentielles',
        'erreurs' => 'Erreurs courantes à éviter',
    ];
    $sort = 0;
    foreach (['structure', 'techniques', 'erreurs'] as $key) {
        $sort++;
        $st = $pdo->prepare('SELECT id, body FROM tcf_ce_consignes WHERE section_key=? ORDER BY id ASC LIMIT 1');
        $st->execute([$key]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $pdo->prepare('INSERT INTO tcf_ce_consignes (title, body, section_key, visibility, is_published, sort_order) VALUES (?,?,?,?,1,?)')
                ->execute([$titles[$key], $bodies[$key], $key, 'gratuit', $sort]);
            continue;
        }
        if (tcf_consigne_body_needs_refresh((string) ($row['body'] ?? ''), 'ce')) {
            $pdo->prepare('UPDATE tcf_ce_consignes SET title=?, body=?, visibility=?, is_published=1, sort_order=? WHERE id=?')
                ->execute([$titles[$key], $bodies[$key], 'gratuit', $sort, (int) $row['id']]);
        }
    }
    try {
        $pdo->exec("DELETE FROM tcf_ce_consignes WHERE section_key NOT IN ('structure','techniques','erreurs')");
    } catch (Throwable $e) {
        // ignore
    }
}

function ce_ensure_tables(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_ce_exams (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(140) NOT NULL UNIQUE,
            title VARCHAR(255) NOT NULL,
            subtitle VARCHAR(255) DEFAULT NULL,
            intro_html TEXT DEFAULT NULL,
            visibility VARCHAR(20) NOT NULL DEFAULT 'gratuit',
            is_published TINYINT(1) NOT NULL DEFAULT 1,
            duration_seconds INT UNSIGNED NOT NULL DEFAULT 3600,
            published_at DATETIME DEFAULT NULL,
            created_by INT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_ce_exam_pub (is_published, published_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_ce_questions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            exam_id INT UNSIGNED NOT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            situation TEXT DEFAULT NULL,
            question_text TEXT NOT NULL,
            points INT NOT NULL DEFAULT 3,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_ce_q_exam (exam_id),
            CONSTRAINT fk_ce_q_exam FOREIGN KEY (exam_id) REFERENCES tcf_ce_exams(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_ce_answers (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            question_id INT UNSIGNED NOT NULL,
            answer_key VARCHAR(8) NOT NULL,
            answer_text TEXT NOT NULL,
            is_correct TINYINT(1) NOT NULL DEFAULT 0,
            sort_order INT NOT NULL DEFAULT 0,
            KEY idx_ce_a_q (question_id),
            CONSTRAINT fk_ce_a_q FOREIGN KEY (question_id) REFERENCES tcf_ce_questions(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_ce_exam_views (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            exam_id INT UNSIGNED NOT NULL,
            user_id INT NOT NULL DEFAULT 0,
            visitor_id VARCHAR(64) NOT NULL DEFAULT '',
            viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_ce_exam_viewer (exam_id, user_id, visitor_id),
            KEY idx_ce_view_exam (exam_id),
            KEY idx_ce_view_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    try {
        $pdo->exec("ALTER TABLE tcf_ce_exam_views ADD COLUMN IF NOT EXISTS visitor_id VARCHAR(64) NOT NULL DEFAULT '' AFTER user_id");
        $pdo->exec("ALTER TABLE tcf_ce_exam_views DROP INDEX IF EXISTS uq_ce_exam_user");
        $pdo->exec("ALTER TABLE tcf_ce_exam_views ADD UNIQUE KEY IF NOT EXISTS uq_ce_exam_viewer (exam_id, user_id, visitor_id)");
    } catch (Throwable $e) {
    }
    ce_ensure_ce_consignes_table($pdo);
}

function ce_track_view(PDO $pdo, int $examId): void
{
    if ($examId <= 0) {
        return;
    }
    ce_ensure_tables($pdo);
    try {
        $uid = (int) ($_SESSION['user_id'] ?? 0);
        if ($uid > 0) {
            $pdo->prepare('INSERT IGNORE INTO tcf_ce_exam_views (exam_id, user_id, visitor_id) VALUES (?, ?, ?)')
                ->execute([$examId, $uid, '']);
        } else {
            $vid = tcf_visitor_id();
            if ($vid !== '') {
                $pdo->prepare('INSERT IGNORE INTO tcf_ce_exam_views (exam_id, user_id, visitor_id) VALUES (?, 0, ?)')
                    ->execute([$examId, $vid]);
            }
        }
    } catch (Throwable $e) {
    }
}

function ce_fetch_exam_full(PDO $pdo, int $examId): ?array
{
    $st = $pdo->prepare('SELECT * FROM tcf_ce_exams WHERE id=? LIMIT 1');
    $st->execute([$examId]);
    $exam = $st->fetch(PDO::FETCH_ASSOC);
    if (!$exam) {
        return null;
    }
    $qSt = $pdo->prepare('SELECT * FROM tcf_ce_questions WHERE exam_id=? ORDER BY sort_order ASC, id ASC');
    $qSt->execute([$examId]);
    $questions = $qSt->fetchAll(PDO::FETCH_ASSOC);
    $aSt = $pdo->prepare('SELECT * FROM tcf_ce_answers WHERE question_id=? ORDER BY sort_order ASC, answer_key ASC');
    foreach ($questions as &$q) {
        $aSt->execute([(int) $q['id']]);
        $q['answers'] = $aSt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($q);
    $exam['questions'] = $questions;
    return $exam;
}

/** Format quiz JS: id, situation, text, points, answers[{id,text,correct}] */
function ce_exam_to_quiz_payload(array $exam): array
{
    $out = [];
    $n = 1;
    foreach ($exam['questions'] ?? [] as $q) {
        $answers = [];
        foreach (array_values($q['answers'] ?? []) as $i => $a) {
            $key = strtolower(trim((string) ($a['answer_key'] ?? '')));
            if ($key === '' || !preg_match('/^[a-z0-9]+$/i', $key)) {
                $key = chr(97 + (int) $i); /* a, b, c… */
            }
            /* id unique : préfère la PK SQL, sinon clé+index (évite 4× "a") */
            $aid = isset($a['id']) && (string) $a['id'] !== ''
                ? (string) $a['id']
                : $key . ':' . $i;
            $answers[] = [
                'id' => $aid,
                'key' => $key,
                'text' => tcf_normalize_rich((string) ($a['answer_text'] ?? '')),
                'correct' => !empty($a['is_correct']),
            ];
        }
        $out[] = [
            'id' => $n++,
            'situation' => isset($q['situation']) && (string) $q['situation'] !== ''
                ? tcf_normalize_rich((string) $q['situation'])
                : '',
            'text' => tcf_normalize_rich((string) ($q['question_text'] ?? '')),
            'points' => (int) ($q['points'] ?? 3),
            'answers' => $answers,
        ];
    }
    return $out;
}

function ce_exam_rank_from_title(string $title): int
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
    if (preg_match('/(20\d{2})/u', $t, $ym)) {
        $year = (int) $ym[1];
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

function ce_sort_exams_by_title(array &$rows): void
{
    usort($rows, static function (array $a, array $b): int {
        $ra = ce_exam_rank_from_title((string) ($a['title'] ?? ''));
        $rb = ce_exam_rank_from_title((string) ($b['title'] ?? ''));
        if ($ra !== $rb) {
            return $rb <=> $ra;
        }
        return (int) ($b['id'] ?? 0) <=> (int) ($a['id'] ?? 0);
    });
}

/** Liste publique : anciennes épreuves en haut, les nouvelles publiées en bas (id croissant). */
function ce_sort_exams_public_user_order(array &$rows): void
{
    usort($rows, static function (array $a, array $b): int {
        return (int) ($a['id'] ?? 0) <=> (int) ($b['id'] ?? 0);
    });
}

/** Les 3 épreuves publiées les plus récentes sont toujours accessibles sans abonnement. */
function ce_last_three_published_exam_ids(PDO $pdo): array
{
    ce_ensure_tables($pdo);
    try {
        $st = $pdo->query(
            "SELECT id FROM tcf_ce_exams WHERE is_published = 1
             ORDER BY COALESCE(published_at, updated_at, created_at) DESC, id DESC
             LIMIT 3"
        );
        $ids = $st ? $st->fetchAll(PDO::FETCH_COLUMN) : [];
    } catch (Throwable $e) {
        return [];
    }

    return array_values(array_map('intval', is_array($ids) ? $ids : []));
}

ce_ensure_tables($pdo);

$action = trim((string) ($_POST['action'] ?? $_GET['action'] ?? ''));
if ($action === '') {
    ce_json(['success' => false, 'message' => 'Action manquante.'], 400);
}

try {
    switch ($action) {
        case 'get_exams_public': {
            $st = $pdo->query(
                "SELECT e.id, e.slug, e.title, e.subtitle, e.visibility, e.duration_seconds, e.published_at,
                    (SELECT COUNT(*) FROM tcf_ce_questions q WHERE q.exam_id = e.id) AS question_count
                 FROM tcf_ce_exams e
                 WHERE e.is_published = 1"
            );
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            ce_sort_exams_public_user_order($rows);
            $alwaysFree = array_fill_keys(ce_last_three_published_exam_ids($pdo), true);
            foreach ($rows as &$r) {
                $r['always_free'] = isset($alwaysFree[(int) ($r['id'] ?? 0)]);
            }
            unset($r);
            ce_json(['success' => true, 'data' => $rows]);
        }

        case 'get_exam_quiz': {
            $examId = (int) ($_POST['exam_id'] ?? $_GET['exam_id'] ?? 0);
            if ($examId <= 0) {
                ce_json(['success' => false, 'message' => 'Épreuve invalide.'], 422);
            }
            $st = $pdo->prepare('SELECT * FROM tcf_ce_exams WHERE id=? AND is_published=1');
            $st->execute([$examId]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                ce_json(['success' => false, 'message' => 'Épreuve indisponible.'], 404);
            }
            $alwaysFreeIds = ce_last_three_published_exam_ids($pdo);
            $isAlwaysFree = in_array($examId, $alwaysFreeIds, true);
            if (((string) ($row['visibility'] ?? 'gratuit')) === 'premium' && !$isAlwaysFree) {
                if (empty($_SESSION['user_id'])) {
                    ce_json(['success' => false, 'locked' => true, 'reason' => 'login', 'message' => 'Connectez-vous pour accéder à cette épreuve.'], 403);
                }
                $stmtU = $pdo->prepare('SELECT * FROM users WHERE id=?');
                $stmtU->execute([(int) $_SESSION['user_id']]);
                $viewer = $stmtU->fetch(PDO::FETCH_ASSOC) ?: null;
                if (!$viewer || !tcf_user_has_premium_access($viewer)) {
                    ce_json(['success' => false, 'locked' => true, 'reason' => 'subscription', 'message' => 'Abonnement requis pour accéder à cette épreuve.'], 403);
                }
            }
            ce_track_view($pdo, $examId);
            $full = ce_fetch_exam_full($pdo, $examId);
            if (!$full || empty($full['questions'])) {
                ce_json(['success' => false, 'message' => 'Contenu incomplet.'], 404);
            }
            $quiz = ce_exam_to_quiz_payload($full);
            $totalPoints = 0;
            foreach ($quiz as $qq) {
                $totalPoints += (int) ($qq['points'] ?? 0);
            }
            ce_json([
                'success' => true,
                'data' => [
                    'exam' => [
                        'id' => (int) $full['id'],
                        'title' => $full['title'],
                        'subtitle' => $full['subtitle'],
                        'intro_html' => $full['intro_html'],
                        'duration_seconds' => (int) ($full['duration_seconds'] ?? 3600),
                        'question_count' => count($quiz),
                        'total_points' => $totalPoints,
                    ],
                    'questions' => $quiz,
                ],
            ]);
        }

        case 'get_exams_admin': {
            if (!ce_is_admin()) {
                ce_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }
            $st = $pdo->query(
                "SELECT e.*, (SELECT COUNT(*) FROM tcf_ce_questions q WHERE q.exam_id=e.id) AS question_count,
                    (SELECT COUNT(*) FROM tcf_ce_exam_views v WHERE v.exam_id=e.id) AS view_count
                 FROM tcf_ce_exams e"
            );
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            ce_sort_exams_by_title($rows);
            foreach ($rows as &$r) {
                $r['effective_visibility'] = (string) ($r['visibility'] ?? 'gratuit');
            }
            unset($r);
            ce_json(['success' => true, 'data' => $rows]);
        }

        case 'get_exam_for_edit': {
            if (!ce_is_admin()) {
                ce_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }
            $examId = (int) ($_POST['exam_id'] ?? $_GET['exam_id'] ?? 0);
            if ($examId <= 0) {
                ce_json(['success' => false, 'message' => 'ID invalide.'], 422);
            }
            $exam = ce_fetch_exam_full($pdo, $examId);
            if (!$exam) {
                ce_json(['success' => false, 'message' => 'Introuvable.'], 404);
            }
            $quiz = [];
            foreach ($exam['questions'] as $q) {
                $correctIndex = 0;
                $ans = [];
                $idx = 0;
                foreach ($q['answers'] as $a) {
                    if (!empty($a['is_correct'])) {
                        $correctIndex = $idx;
                    }
                    $ans[] = [
                        'key' => $a['answer_key'],
                        'text' => $a['answer_text'],
                        'correct' => (int) $a['is_correct'] === 1,
                    ];
                    $idx++;
                }
                $quiz[] = [
                    'id' => (int) $q['id'],
                    'situation' => $q['situation'],
                    'question_text' => $q['question_text'],
                    'points' => (int) $q['points'],
                    'correct_index' => $correctIndex,
                    'answers' => $ans,
                ];
            }
            unset($exam['questions']);
            $exam['quiz_questions'] = $quiz;
            ce_json(['success' => true, 'data' => $exam]);
        }

        case 'save_exam': {
            if (!ce_is_admin()) {
                ce_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }
            $examId = (int) ($_POST['exam_id'] ?? 0);
            $title = trim((string) ($_POST['title'] ?? ''));
            $subtitle = trim((string) ($_POST['subtitle'] ?? ''));
            $introHtml = trim((string) ($_POST['intro_html'] ?? ''));
            $visibility = (string) ($_POST['visibility'] ?? 'gratuit');
            if (!in_array($visibility, ['gratuit', 'premium'], true)) {
                $visibility = 'gratuit';
            }
            $isPublished = ((string) ($_POST['is_published'] ?? '1')) === '1' ? 1 : 0;
            $durationSeconds = max(60, min(86400, (int) ($_POST['duration_seconds'] ?? 3600)));
            $questions = $_POST['questions'] ?? null;
            if (!is_array($questions) || count($questions) === 0) {
                $questionsRaw = (string) ($_POST['questions_json'] ?? '[]');
                $questions = json_decode($questionsRaw, true);
            }
            if (!is_array($questions)) {
                $questions = [];
            }

            $normalized = [];
            foreach ($questions as $qi => $q) {
                if (!is_array($q)) {
                    continue;
                }
                
                // Support format avec 'correct' dans chaque réponse (format database/seeds)
                if (isset($q['answers']) && is_array($q['answers'])) {
                    $answersIn = $q['answers'];
                    $hasCorrectField = false;
                    foreach ($answersIn as $a) {
                        if (isset($a['correct'])) {
                            $hasCorrectField = true;
                            break;
                        }
                    }
                    
                    if ($hasCorrectField) {
                        // Format avec 'correct' booléen dans chaque réponse
                        $answersNorm = [];
                        $ci = 0;
                        foreach ($answersIn as $idx => $a) {
                            $txt = trim((string) ($a['text'] ?? ''));
                            $isCorrect = !empty($a['correct']);
                            if ($isCorrect) {
                                $ci = $idx;
                            }
                            $answersNorm[] = ['text' => $txt, 'correct' => $isCorrect];
                        }
                        $normalized[] = [
                            'situation' => trim((string) ($q['situation'] ?? '')),
                            'question_text' => trim((string) ($q['question_text'] ?? $q['text'] ?? '')),
                            'points' => (int) ($q['points'] ?? 3),
                            'answers' => $answersNorm,
                        ];
                    } elseif (isset($q['correct_index'])) {
                        // Format avec correct_index
                        $ci = (int) ($q['correct_index'] ?? 0);
                        $ci = max(0, min(3, $ci));
                        $answersNorm = [];
                        for ($j = 0; $j < 4; $j++) {
                            $a = $q['answers'][$j] ?? null;
                            $txt = '';
                            if (is_array($a)) {
                                $txt = trim((string) ($a['text'] ?? ''));
                            }
                            $answersNorm[] = ['text' => $txt, 'correct' => ($j === $ci)];
                        }
                        $normalized[] = [
                            'situation' => trim((string) ($q['situation'] ?? '')),
                            'question_text' => trim((string) ($q['question_text'] ?? '')),
                            'points' => (int) ($q['points'] ?? 3),
                            'answers' => $answersNorm,
                        ];
                    } else {
                        $normalized[] = $q;
                    }
                } else {
                    $normalized[] = $q;
                }
            }
            $questions = $normalized;

            if ($title === '') {
                ce_json(['success' => false, 'message' => 'Titre obligatoire.'], 422);
            }
            if (!is_array($questions) || count($questions) === 0) {
                ce_json(['success' => false, 'message' => 'Ajoutez au moins une question.'], 422);
            }

            foreach ($questions as $qi => $q) {
                $answers = $q['answers'] ?? null;
                if (!is_array($answers) || count($answers) < 2) {
                    ce_json(['success' => false, 'message' => 'Chaque question doit avoir au moins 2 réponses (question ' . ($qi + 1) . ').'], 422);
                }
                $correct = 0;
                foreach ($answers as $a) {
                    if (!empty($a['correct'])) {
                        $correct++;
                    }
                }
                if ($correct !== 1) {
                    ce_json(['success' => false, 'message' => 'Une seule bonne réponse par question (question ' . ($qi + 1) . ').'], 422);
                }
            }

            $pdo->beginTransaction();
            try {
                $wasPublished = 0;
                $isNewExam = $examId <= 0;
                if ($examId > 0) {
                    $stWas = $pdo->prepare('SELECT is_published FROM tcf_ce_exams WHERE id=?');
                    $stWas->execute([$examId]);
                    $wasPublished = (int) $stWas->fetchColumn();
                    $pdo->prepare(
                        'UPDATE tcf_ce_exams SET title=?, subtitle=?, intro_html=?, visibility=?, is_published=?, duration_seconds=?, published_at=IF(?=1 AND is_published=0, NOW(), published_at) WHERE id=?'
                    )->execute([
                        $title,
                        $subtitle !== '' ? $subtitle : null,
                        $introHtml !== '' ? $introHtml : null,
                        $visibility,
                        $isPublished,
                        $durationSeconds,
                        $isPublished,
                        $examId,
                    ]);
                    $pdo->prepare('DELETE FROM tcf_ce_questions WHERE exam_id=?')->execute([$examId]);
                } else {
                    $slug = ce_slug($title);
                    $pdo->prepare(
                        'INSERT INTO tcf_ce_exams (slug,title,subtitle,intro_html,visibility,is_published,duration_seconds,published_at,created_by) VALUES (?,?,?,?,?,?,?,IF(?=1,NOW(),NULL),?)'
                    )->execute([
                        $slug,
                        $title,
                        $subtitle !== '' ? $subtitle : null,
                        $introHtml !== '' ? $introHtml : null,
                        $visibility,
                        $isPublished,
                        $durationSeconds,
                        $isPublished,
                        (int) ($_SESSION['user_id'] ?? 0),
                    ]);
                    $examId = (int) $pdo->lastInsertId();
                }

                $insQ = $pdo->prepare(
                    'INSERT INTO tcf_ce_questions (exam_id,sort_order,situation,question_text,points) VALUES (?,?,?,?,?)'
                );
                $insA = $pdo->prepare(
                    'INSERT INTO tcf_ce_answers (question_id,answer_key,answer_text,is_correct,sort_order) VALUES (?,?,?,?,?)'
                );

                foreach ($questions as $ord => $q) {
                    $sit = isset($q['situation']) ? trim((string) $q['situation']) : '';
                    $txt = trim((string) ($q['question_text'] ?? $q['text'] ?? ''));
                    $pts = (int) ($q['points'] ?? 3);
                    if ($txt === '') {
                        throw new RuntimeException('Texte de question vide.');
                    }
                    $insQ->execute([$examId, $ord + 1, $sit !== '' ? $sit : null, $txt, $pts]);
                    $qid = (int) $pdo->lastInsertId();
                    $answers = $q['answers'] ?? [];
                    $sk = 0;
                    foreach ($answers as $a) {
                        $key = strtolower(trim((string) ($a['key'] ?? $a['id'] ?? 'a')));
                        if (!preg_match('/^[a-z]$/', $key)) {
                            $key = ['a', 'b', 'c', 'd'][$sk % 4];
                        }
                        $atxt = trim((string) ($a['text'] ?? ''));
                        $ok = !empty($a['correct']) ? 1 : 0;
                        if ($atxt === '') {
                            continue;
                        }
                        $insA->execute([$qid, $key, $atxt, $ok, $sk]);
                        $sk++;
                    }
                }

                $pdo->commit();
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                ce_json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            if ($isPublished && ($isNewExam || !$wasPublished)) {
                tcf_notify_users_registered_before(
                    $pdo,
                    'exam',
                    'Nouvelle épreuve — Compréhension écrite',
                    "L'épreuve « $title » est maintenant disponible.",
                    site_href('comprehesion_ecrite_quiz.php?exam_id=' . $examId)
                );
            }
            ce_json(['success' => true, 'message' => 'Épreuve enregistrée.', 'exam_id' => $examId]);
        }

        case 'get_consignes': {
            ce_seed_ce_consignes($pdo);
            $canPremium = ce_can_view_premium_consigne($pdo);
            $keys = "'structure','techniques','erreurs'";
            if ($canPremium) {
                $st = $pdo->query(
                    "SELECT id, title, body, section_key, visibility, is_published, sort_order FROM tcf_ce_consignes WHERE is_published=1 AND section_key IN ($keys) ORDER BY sort_order ASC, id ASC"
                );
            } else {
                $st = $pdo->query(
                    "SELECT id, title, body, section_key, visibility, is_published, sort_order FROM tcf_ce_consignes WHERE is_published=1 AND visibility='gratuit' AND section_key IN ($keys) ORDER BY sort_order ASC, id ASC"
                );
            }
            $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as &$row) {
                $row['body'] = tcf_normalize_rich((string) ($row['body'] ?? ''));
                $row['task_key'] = (string) ($row['section_key'] ?? '');
            }
            unset($row);
            ce_json(['success' => true, 'data' => $rows, 'can_premium' => $canPremium]);
        }

        case 'get_consignes_bundle_admin': {
            if (!ce_is_admin()) {
                ce_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }
            ce_seed_ce_consignes($pdo);
            $out = ['structure' => '', 'techniques' => '', 'erreurs' => '', 'is_published' => 1];
            $st = $pdo->query("SELECT body, section_key, is_published FROM tcf_ce_consignes WHERE section_key IN ('structure','techniques','erreurs') ORDER BY sort_order ASC, id ASC");
            foreach (($st->fetchAll(PDO::FETCH_ASSOC) ?: []) as $row) {
                $k = (string) ($row['section_key'] ?? '');
                if (isset($out[$k])) {
                    $out[$k] = (string) ($row['body'] ?? '');
                }
                $out['is_published'] = (int) ($row['is_published'] ?? 1);
            }
            ce_json(['success' => true, 'data' => $out]);
        }

        case 'save_consignes_bundle': {
            if (!ce_is_admin()) {
                ce_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }
            $structure = trim((string) ($_POST['structure'] ?? $_POST['body'] ?? ''));
            $techniques = trim((string) ($_POST['techniques'] ?? ''));
            $erreurs = trim((string) ($_POST['erreurs'] ?? ''));
            $isPublished = isset($_POST['is_published']) && $_POST['is_published'] === '0' ? 0 : 1;
            if ($structure === '' || $techniques === '' || $erreurs === '') {
                ce_json(['success' => false, 'message' => 'Veuillez renseigner les 3 sections de consignes.'], 422);
            }
            ce_seed_ce_consignes($pdo);
            $titles = [
                'structure' => 'Structure de l’épreuve et stratégie de scoring',
                'techniques' => 'Les 5 techniques essentielles',
                'erreurs' => 'Erreurs courantes à éviter',
            ];
            $bodies = [
                'structure' => $structure,
                'techniques' => $techniques,
                'erreurs' => $erreurs,
            ];
            $pdo->exec("DELETE FROM tcf_ce_consignes WHERE section_key IN ('structure','techniques','erreurs')");
            $ins = $pdo->prepare('INSERT INTO tcf_ce_consignes (title, body, section_key, visibility, is_published, sort_order) VALUES (?,?,?,?,?,?)');
            $sort = 0;
            foreach (['structure', 'techniques', 'erreurs'] as $key) {
                $sort++;
                $ins->execute([$titles[$key], $bodies[$key], $key, 'gratuit', $isPublished, $sort]);
            }
            ce_json(['success' => true, 'message' => 'Consignes enregistrées.']);
        }

        case 'delete_exam': {
            if (!ce_is_admin()) {
                ce_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }
            if (!tcf_is_super_admin()) {
                ce_json(['success' => false, 'message' => 'Seul le super administrateur peut supprimer une épreuve.'], 403);
            }
            $examId = (int) ($_POST['exam_id'] ?? 0);
            if ($examId <= 0) {
                ce_json(['success' => false, 'message' => 'ID invalide.'], 422);
            }
            $pdo->prepare('DELETE FROM tcf_ce_exams WHERE id=?')->execute([$examId]);
            tcf_delete_notifications_matching($pdo, 'comprehesion_ecrite_quiz.php?exam_id=' . $examId);
            ce_json(['success' => true, 'message' => 'Supprimé.']);
        }

        default:
            ce_json(['success' => false, 'message' => 'Action inconnue.'], 400);
    }
} catch (Throwable $e) {
    ce_json(['success' => false, 'message' => $e->getMessage()], 500);
}
