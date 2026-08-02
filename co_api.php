<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/subscription_access.php';
require_once __DIR__ . '/includes/tcf_notifications_helper.php';
require_once __DIR__ . '/includes/rich_text.php';
require_once __DIR__ . '/includes/admin_roles.php';
require_once __DIR__ . '/includes/tcf_schema.php';
require_once __DIR__ . '/includes/tcf_exam_store.php';

header('Content-Type: application/json; charset=utf-8');

function co_json(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function co_is_admin(): bool
{
    return tcf_is_staff_admin();
}

function co_slug(string $title): string
{
    $s = mb_strtolower(trim($title));
    $s = preg_replace('/[àáâãäå]/u', 'a', $s);
    $s = preg_replace('/[èéêë]/u', 'e', $s);
    $s = preg_replace('/[ìíîï]/u', 'i', $s);
    $s = preg_replace('/[òóôõö]/u', 'o', $s);
    $s = preg_replace('/[ùúûü]/u', 'u', $s);
    $s = preg_replace('/[ç]/u', 'c', $s);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    $s = trim((string) $s, '-');

    return substr((string) $s, 0, 120) . '-' . substr(uniqid('', true), -6);
}


function co_ensure_tables(PDO $pdo): void
{
    if (tcf_schema_is_consolidated($pdo) || tcf_schema_has_table($pdo, 'comprehension_orale')) {
        return;
    }
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_co_exams (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(140) NOT NULL UNIQUE,
            title VARCHAR(255) NOT NULL,
            subtitle VARCHAR(255) DEFAULT NULL,
            intro_html TEXT DEFAULT NULL,
            visibility VARCHAR(20) NOT NULL DEFAULT 'gratuit',
            is_published TINYINT(1) NOT NULL DEFAULT 1,
            duration_seconds INT UNSIGNED NOT NULL DEFAULT 2100,
            published_at DATETIME DEFAULT NULL,
            created_by INT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_co_exam_pub (is_published, published_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_co_questions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            exam_id INT UNSIGNED NOT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            question_text TEXT NOT NULL,
            points INT NOT NULL DEFAULT 1,
            image_src TEXT DEFAULT NULL,
            audio_src TEXT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_co_q_exam (exam_id),
            CONSTRAINT fk_co_q_exam FOREIGN KEY (exam_id) REFERENCES tcf_co_exams(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_co_answers (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            question_id INT UNSIGNED NOT NULL,
            answer_key VARCHAR(8) NOT NULL,
            answer_text TEXT NOT NULL,
            is_correct TINYINT(1) NOT NULL DEFAULT 0,
            sort_order INT NOT NULL DEFAULT 0,
            KEY idx_co_a_q (question_id),
            CONSTRAINT fk_co_a_q FOREIGN KEY (question_id) REFERENCES tcf_co_questions(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_co_exam_views (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            exam_id INT UNSIGNED NOT NULL,
            user_id INT NOT NULL DEFAULT 0,
            visitor_id VARCHAR(64) NOT NULL DEFAULT '',
            viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_co_exam_viewer (exam_id, user_id, visitor_id),
            KEY idx_co_view_exam (exam_id),
            KEY idx_co_view_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    try {
        $pdo->exec("ALTER TABLE tcf_co_exam_views ADD COLUMN IF NOT EXISTS visitor_id VARCHAR(64) NOT NULL DEFAULT '' AFTER user_id");
        $pdo->exec("ALTER TABLE tcf_co_exam_views DROP INDEX IF EXISTS uq_co_exam_user");
        $pdo->exec("ALTER TABLE tcf_co_exam_views ADD UNIQUE KEY IF NOT EXISTS uq_co_exam_viewer (exam_id, user_id, visitor_id)");
    } catch (Throwable $e) {
    }
    try {
        $pdo->exec('ALTER TABLE tcf_co_questions ADD COLUMN audio_text TEXT DEFAULT NULL AFTER audio_src');
    } catch (Throwable $e) {
    }
    try {
        // Épreuve CO = 35 min (2100 s) — aligner anciennes valeurs par défaut 30 min
        $pdo->exec('UPDATE tcf_co_exams SET duration_seconds = 2100 WHERE duration_seconds IN (1800, 0) OR duration_seconds IS NULL');
    } catch (Throwable $e) {
    }
    co_ensure_co_consignes_table($pdo);
}

function co_is_logged(): bool
{
    return !empty($_SESSION['user_id']);
}

function co_can_view_premium_consigne(PDO $pdo): bool
{
    if (!co_is_logged()) {
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

/** Table consignes CO (3 sections : structure, techniques, erreurs). */
function co_ensure_co_consignes_table(PDO $pdo): void
{
    if (tcf_schema_is_consolidated($pdo) || tcf_schema_has_table($pdo, 'comprehension_orale')) {
        return;
    }
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_co_consignes (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL DEFAULT 'Consignes Compréhension Orale',
            body LONGTEXT NOT NULL,
            section_key VARCHAR(40) NOT NULL DEFAULT 'structure',
            visibility VARCHAR(20) NOT NULL DEFAULT 'gratuit',
            is_published TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 1,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    try {
        $cols = $pdo->query('SHOW COLUMNS FROM tcf_co_consignes')->fetchAll(PDO::FETCH_ASSOC);
        $names = array_map(static fn ($c) => (string) ($c['Field'] ?? ''), $cols);
        if (!in_array('section_key', $names, true)) {
            $pdo->exec("ALTER TABLE tcf_co_consignes ADD COLUMN section_key VARCHAR(40) NOT NULL DEFAULT 'structure' AFTER body");
        }
        if (!in_array('sort_order', $names, true)) {
            $pdo->exec('ALTER TABLE tcf_co_consignes ADD COLUMN sort_order INT NOT NULL DEFAULT 1 AFTER is_published');
        }
    } catch (Throwable $e) {
        // ignore migrate errors on restricted hosts
    }
}

function co_seed_default_consignes(PDO $pdo): void
{
    require_once __DIR__ . '/includes/tcf_consignes_defaults.php';
    $bodies = tcf_consigne_co_bodies();
    $titles = [
        'structure' => 'Structure de l’épreuve et stratégie de scoring',
        'techniques' => 'Les 5 techniques essentielles',
        'erreurs' => 'Erreurs courantes à éviter',
    ];
    if (tcf_schema_has_table($pdo, 'comprehension_orale')) {
        $sections = [];
        $sort = 0;
        foreach (['structure', 'techniques', 'erreurs'] as $key) {
            $sort++;
            $sections[] = ['key' => $key, 'title' => $titles[$key], 'body' => $bodies[$key], 'sort' => $sort];
        }
        tcf_exam_seed_consignes($pdo, 'co', $sections);
        return;
    }
    co_ensure_co_consignes_table($pdo);
    $sort = 0;
    foreach (['structure', 'techniques', 'erreurs'] as $key) {
        $sort++;
        $st = $pdo->prepare('SELECT id, body FROM tcf_co_consignes WHERE section_key=? ORDER BY id ASC LIMIT 1');
        $st->execute([$key]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $pdo->prepare('INSERT INTO tcf_co_consignes (title, body, section_key, visibility, is_published, sort_order) VALUES (?,?,?,?,1,?)')
                ->execute([$titles[$key], $bodies[$key], $key, 'gratuit', $sort]);
            continue;
        }
        if (tcf_consigne_body_needs_refresh((string) ($row['body'] ?? ''), 'co')) {
            $pdo->prepare('UPDATE tcf_co_consignes SET title=?, body=?, visibility=?, is_published=1, sort_order=? WHERE id=?')
                ->execute([$titles[$key], $bodies[$key], 'gratuit', $sort, (int) $row['id']]);
        }
    }
    try {
        $pdo->exec("DELETE FROM tcf_co_consignes WHERE section_key NOT IN ('structure','techniques','erreurs')");
    } catch (Throwable $e) {
        // ignore
    }
}

function co_track_view(PDO $pdo, int $examId): void
{
    if (tcf_schema_has_table($pdo, 'comprehension_orale')) {
        $uid = !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
        $vid = '';
        if (empty($uid)) {
            if (empty($_SESSION['tcf_visitor_id'])) {
                $_SESSION['tcf_visitor_id'] = bin2hex(random_bytes(16));
            }
            $vid = (string) $_SESSION['tcf_visitor_id'];
        }
        tcf_exam_track_view($pdo, 'co', $examId, $uid, $vid);
        return;
    }
    if ($examId <= 0) {
        return;
    }
    co_ensure_tables($pdo);
    try {
        $uid = (int) ($_SESSION['user_id'] ?? 0);
        if ($uid > 0) {
            $pdo->prepare('INSERT IGNORE INTO tcf_co_exam_views (exam_id, user_id, visitor_id) VALUES (?, ?, ?)')
                ->execute([$examId, $uid, '']);
        } else {
            $vid = tcf_visitor_id();
            if ($vid !== '') {
                $pdo->prepare('INSERT IGNORE INTO tcf_co_exam_views (exam_id, user_id, visitor_id) VALUES (?, 0, ?)')
                    ->execute([$examId, $vid]);
            }
        }
    } catch (Throwable $e) {
    }
}

/** URL absolue pour médias (chemin site ou URL externe) — BLOB durable si fichier absent. */
function co_resolve_media_url(string $ref): string
{
    global $pdo;
    $ref = trim($ref);
    if ($ref === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $ref) && !preg_match('#/(uploads/)#i', $ref)) {
        return $ref;
    }
    require_once __DIR__ . '/includes/persistent_media.php';
    $kind = preg_match('/\.(mp3|wav|ogg|m4a|aac)$/i', $ref) ? 'co_audio' : 'co_image';

    return tcf_persistent_media_public_href($pdo, $ref, $kind);
}

function co_fetch_exam_full(PDO $pdo, int $examId): ?array
{
    if (tcf_schema_has_table($pdo, 'comprehension_orale')) {
        return tcf_exam_fetch_by_id($pdo, 'co', $examId);
    }
    $st = $pdo->prepare('SELECT * FROM tcf_co_exams WHERE id=? LIMIT 1');
    $st->execute([$examId]);
    $exam = $st->fetch(PDO::FETCH_ASSOC);
    if (!$exam) {
        return null;
    }
    $qSt = $pdo->prepare('SELECT * FROM tcf_co_questions WHERE exam_id=? ORDER BY sort_order ASC, id ASC');
    $qSt->execute([$examId]);
    $questions = $qSt->fetchAll(PDO::FETCH_ASSOC);
    $aSt = $pdo->prepare('SELECT * FROM tcf_co_answers WHERE question_id=? ORDER BY sort_order ASC, answer_key ASC');
    foreach ($questions as &$q) {
        $aSt->execute([(int) $q['id']]);
        $q['answers'] = $aSt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($q);
    $exam['questions'] = $questions;

    return $exam;
}

/** Format attendu par le JS du quiz (comme l’ancien quiz statique). */
function co_strip_answer_letter_prefix(string $text): string
{
    $t = trim($text);
    // Uniquement A)/B./C-… — ne pas toucher « Aujourd'hui », « À Paris », etc.
    $t = preg_replace('/^\s*[A-Da-d]\s*[).:\-–—]\s*/u', '', $t) ?? $t;
    return trim($t);
}

function co_exam_to_quiz_payload(array $exam): array
{
    $out = [];
    $n = 1;
    foreach ($exam['questions'] ?? [] as $q) {
        $slots = [null, null, null, null];
        foreach (array_values($q['answers'] ?? []) as $i => $a) {
            $so = isset($a['sort_order']) ? (int) $a['sort_order'] : -1;
            $key = strtolower(trim((string) ($a['answer_key'] ?? '')));
            if ($so >= 0 && $so <= 3) {
                $slot = $so;
            } elseif (preg_match('/^[abcd]$/', $key)) {
                $slot = ord($key) - ord('a');
            } else {
                $slot = min(3, max(0, (int) $i));
            }
            if ($key === '' || !preg_match('/^[a-z0-9]+$/i', $key)) {
                $key = chr(97 + $slot);
            }
            $aid = isset($a['id']) && (string) $a['id'] !== ''
                ? (string) $a['id']
                : $key . ':' . $slot;
            $slots[$slot] = [
                'id' => $aid,
                'key' => $key,
                'text' => tcf_normalize_rich(co_strip_answer_letter_prefix((string) ($a['answer_text'] ?? ''))),
                'correct' => !empty($a['is_correct']),
            ];
        }
        $answers = [];
        for ($i = 0; $i < 4; $i++) {
            $key = chr(97 + $i);
            if (is_array($slots[$i])) {
                $answers[] = $slots[$i];
            } else {
                $answers[] = [
                    'id' => $key . ':' . $i,
                    'key' => $key,
                    'text' => '',
                    'correct' => false,
                ];
            }
        }
        $out[] = [
            'id' => $n++,
            'question' => tcf_normalize_rich((string) ($q['question_text'] ?? '')),
            'image' => co_resolve_media_url((string) ($q['image_src'] ?? '')),
            'audio' => co_resolve_media_url((string) ($q['audio_src'] ?? '')),
            'audio_text' => trim((string) ($q['audio_text'] ?? '')),
            'points' => (int) ($q['points'] ?? 1),
            'answers' => $answers,
        ];
    }

    return $out;
}

function co_exam_rank_from_title(string $title): int
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

function co_sort_exams_by_title(array &$rows): void
{
    usort($rows, static function (array $a, array $b): int {
        $ra = co_exam_rank_from_title((string) ($a['title'] ?? ''));
        $rb = co_exam_rank_from_title((string) ($b['title'] ?? ''));
        if ($ra !== $rb) {
            return $rb <=> $ra;
        }
        return (int) ($b['id'] ?? 0) <=> (int) ($a['id'] ?? 0);
    });
}

/** Liste publique utilisateur : plus anciennes en haut, nouvelles publiées en bas. */
function co_sort_exams_public_user_order(array &$rows): void
{
    usort($rows, static function (array $a, array $b): int {
        $ra = co_exam_rank_from_title((string) ($a['title'] ?? ''));
        $rb = co_exam_rank_from_title((string) ($b['title'] ?? ''));
        if ($ra !== $rb) {
            return $ra <=> $rb; // anciennes d'abord
        }
        return (int) ($a['id'] ?? 0) <=> (int) ($b['id'] ?? 0);
    });
}

/**
 * Auto-visibilité comme EE/EO :
 * les 3 épreuves publiées les plus récentes (rang titre) → gratuit
 * (sauf Premium forcé) ; les autres → premium.
 */
function co_sync_exam_visibility(PDO $pdo): void
{
    co_ensure_tables($pdo);
    try {
        if (tcf_schema_has_table($pdo, 'comprehension_orale')) {
            $rows = $pdo->query(
                "SELECT id, title, visibility FROM comprehension_orale WHERE kind='exam' AND is_published=1"
            )->fetchAll(PDO::FETCH_ASSOC);
            if (!$rows) {
                return;
            }
            co_sort_exams_by_title($rows);
            $top3Ids = [];
            foreach ($rows as $row) {
                $top3Ids[] = (int) ($row['id'] ?? 0);
                if (count($top3Ids) >= 3) {
                    break;
                }
            }
            if ($top3Ids) {
                $in = implode(',', array_map('intval', $top3Ids));
                $pdo->exec(
                    "UPDATE comprehension_orale SET visibility='premium'
                     WHERE kind='exam' AND is_published=1 AND id NOT IN ($in)"
                );
                $pdo->exec(
                    "UPDATE comprehension_orale SET visibility='gratuit'
                     WHERE kind='exam' AND is_published=1 AND id IN ($in) AND visibility<>'premium'"
                );
            } else {
                $pdo->exec(
                    "UPDATE comprehension_orale SET visibility='premium' WHERE kind='exam' AND is_published=1"
                );
            }
            return;
        }

        $rows = $pdo->query(
            'SELECT id, title, visibility FROM tcf_co_exams WHERE is_published=1'
        )->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) {
            return;
        }
        co_sort_exams_by_title($rows);
        $top3Ids = [];
        foreach ($rows as $row) {
            $top3Ids[] = (int) ($row['id'] ?? 0);
            if (count($top3Ids) >= 3) {
                break;
            }
        }
        if ($top3Ids) {
            $in = implode(',', array_map('intval', $top3Ids));
            $pdo->exec("UPDATE tcf_co_exams SET visibility='premium' WHERE is_published=1 AND id NOT IN ($in)");
            $pdo->exec(
                "UPDATE tcf_co_exams SET visibility='gratuit'
                 WHERE is_published=1 AND id IN ($in) AND visibility<>'premium'"
            );
        } else {
            $pdo->exec("UPDATE tcf_co_exams SET visibility='premium' WHERE is_published=1");
        }
    } catch (Throwable $e) {
        // ignore sync errors
    }
}

/**
 * Normalise les questions pour save_exam (formulaire ou import JSON).
 *
 * @param mixed $questions
 * @return list<array<string,mixed>>
 */
function co_normalize_questions_input($questions): array
{
    if (!is_array($questions)) {
        return [];
    }
    $list = $questions;
    if (isset($list['questions']) && is_array($list['questions'])) {
        $list = $list['questions'];
    }
    $out = [];
    foreach ($list as $q) {
        if (!is_array($q)) {
            continue;
        }
        $qtxt = trim((string) ($q['question_text'] ?? $q['question'] ?? $q['text'] ?? ''));
        $pts = (int) ($q['points'] ?? 1);
        $img = trim((string) ($q['image_src'] ?? $q['image'] ?? ''));
        $aud = trim((string) ($q['audio_src'] ?? $q['audio'] ?? ''));
        // Normaliser vers uploads/… (évite localhost / chemins absolus cassés en prod)
        if ($img !== '' && function_exists('tcf_uploads_relative_path')) {
            $normImg = tcf_uploads_relative_path($img);
            if ($normImg !== '' && !preg_match('#^https?://#i', $normImg)) {
                $img = $normImg;
            }
        }
        if ($aud !== '' && function_exists('tcf_uploads_relative_path')) {
            $normAud = tcf_uploads_relative_path($aud);
            if ($normAud !== '' && !preg_match('#^https?://#i', $normAud)) {
                $aud = $normAud;
            }
        }
        $audText = trim((string) ($q['audio_text'] ?? $q['tts'] ?? $q['script'] ?? ''));

        $answersIn = $q['answers'] ?? [];
        if (!is_array($answersIn)) {
            $answersIn = [];
        }
        
        // Vérifier si le format utilise 'correct' booléen dans chaque réponse
        $hasCorrectField = false;
        foreach ($answersIn as $a) {
            if (is_array($a) && isset($a['correct'])) {
                $hasCorrectField = true;
                break;
            }
        }
        
        $normAnswers = [];
        if ($hasCorrectField) {
            // Format avec 'correct' booléen dans chaque réponse
            $ci = 0;
            foreach ($answersIn as $idx => $a) {
                if (is_array($a)) {
                    $txt = co_strip_answer_letter_prefix((string) ($a['text'] ?? ''));
                    $isCorrect = !empty($a['correct']);
                    if ($isCorrect) {
                        $ci = $idx;
                    }
                    $normAnswers[] = ['text' => $txt];
                } else {
                    $normAnswers[] = ['text' => co_strip_answer_letter_prefix((string) $a)];
                }
            }
        } else {
            // Format avec correct_index ou correct (lettre)
            foreach ($answersIn as $a) {
                if (is_array($a)) {
                    $normAnswers[] = ['text' => co_strip_answer_letter_prefix((string) ($a['text'] ?? ''))];
                } else {
                    $normAnswers[] = ['text' => co_strip_answer_letter_prefix((string) $a)];
                }
            }
        }
        
        while (count($normAnswers) < 4) {
            $normAnswers[] = ['text' => ''];
        }
        $normAnswers = array_slice($normAnswers, 0, 4);

        if (!$hasCorrectField) {
            $ci = isset($q['correct_index']) ? (int) $q['correct_index'] : 0;
            if ($ci < 0 || $ci > 3) {
                $ci = 0;
            }

            $correctLetter = isset($q['correct']) ? strtolower(trim((string) $q['correct'])) : '';
            if ($correctLetter !== '' && preg_match('/^[abcd]$/', $correctLetter)) {
                $ci = ord($correctLetter) - ord('a');
            }
        }

        $out[] = [
            'question_text' => $qtxt,
            'points' => max(1, $pts),
            'image_src' => $img,
            'audio_src' => $aud,
            'audio_text' => $audText,
            'correct_index' => $ci,
            'answers' => $normAnswers,
        ];
    }

    return $out;
}

/**
 * Garantit une copie BLOB en base pour chaque média CO (image/audio) du payload.
 *
 * @param list<array<string,mixed>> $questions
 */
function co_persist_questions_media_blobs(PDO $pdo, array $questions): void
{
    require_once __DIR__ . '/includes/persistent_media.php';
    foreach ($questions as $q) {
        if (!is_array($q)) {
            continue;
        }
        $img = trim((string) ($q['image_src'] ?? ''));
        $aud = trim((string) ($q['audio_src'] ?? ''));
        if ($img !== '') {
            tcf_persistent_media_store_from_path($pdo, $img, 'co_image');
        }
        if ($aud !== '') {
            tcf_persistent_media_store_from_path($pdo, $aud, 'co_audio');
        }
    }
}

/**
 * Upload média compréhension orale (admin) → uploads/co_media/
 *
 * @return array{ok:bool, path?:string, url?:string, message?:string}
 */
function co_handle_media_upload(string $kind): array
{
    if (!co_is_admin()) {
        return ['ok' => false, 'message' => 'Accès refusé.'];
    }
    if ($kind !== 'image' && $kind !== 'audio') {
        return ['ok' => false, 'message' => 'Type de média invalide.'];
    }
    if (empty($_FILES['file']['tmp_name']) || !is_uploaded_file((string) $_FILES['file']['tmp_name'])) {
        return ['ok' => false, 'message' => 'Aucun fichier reçu.'];
    }

    $tmp = (string) $_FILES['file']['tmp_name'];
    $orig = (string) ($_FILES['file']['name'] ?? 'file');
    $size = (int) ($_FILES['file']['size'] ?? 0);

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp) ?: '';

    $imageMimes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
    $audioMimes = [
        'audio/mpeg' => 'mp3',
        'audio/mp3' => 'mp3',
        'audio/wav' => 'wav',
        'audio/x-wav' => 'wav',
        'audio/wave' => 'wav',
        'audio/ogg' => 'ogg',
        'audio/mp4' => 'm4a',
        'audio/x-m4a' => 'm4a',
        'audio/aac' => 'aac',
        'video/mp4' => 'm4a',
    ];

    $ext = null;
    if ($kind === 'image') {
        if ($size > 8 * 1024 * 1024) {
            return ['ok' => false, 'message' => 'Image trop volumineuse (max 8 Mo).'];
        }
        $ext = $imageMimes[$mime] ?? null;
    } else {
        if ($size > 25 * 1024 * 1024) {
            return ['ok' => false, 'message' => 'Audio trop volumineux (max 25 Mo).'];
        }
        $ext = $audioMimes[$mime] ?? null;
    }

    if ($ext === null) {
        return ['ok' => false, 'message' => 'Format non autorisé (' . $mime . ').'];
    }

    $root = realpath(__DIR__);
    if ($root === false) {
        return ['ok' => false, 'message' => 'Erreur serveur.'];
    }
    $dir = $root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'co_media';
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
        return ['ok' => false, 'message' => 'Impossible de créer le dossier d’upload.'];
    }

    $safeBase = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($orig, PATHINFO_FILENAME));
    if ($safeBase === '' || $safeBase === '_') {
        $safeBase = 'media';
    }
    $base = substr($safeBase, 0, 80) . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = $dir . DIRECTORY_SEPARATOR . $base;
    if (!@move_uploaded_file($tmp, $dest)) {
        return ['ok' => false, 'message' => 'Échec de l’enregistrement du fichier.'];
    }

    $rel = 'uploads/co_media/' . $base;

    try {
        require_once __DIR__ . '/includes/persistent_media.php';
        global $pdo;
        if (isset($pdo) && $pdo instanceof PDO) {
            tcf_persistent_media_store_from_path($pdo, $rel, $kind === 'audio' ? 'co_audio' : 'co_image');
        }
    } catch (Throwable $e) {
        error_log('co_handle_media_upload blob: ' . $e->getMessage());
    }

    return [
        'ok' => true,
        'path' => $rel,
        'url' => function_exists('tcf_persistent_media_public_href') && isset($pdo) && $pdo instanceof PDO
            ? tcf_persistent_media_public_href($pdo, $rel, $kind === 'audio' ? 'co_audio' : 'co_image')
            : (function_exists('site_href') ? site_href($rel) : '/' . $rel),
    ];
}

co_ensure_tables($pdo);

$action = trim((string) ($_POST['action'] ?? $_GET['action'] ?? ''));
if ($action === '') {
    co_json(['success' => false, 'message' => 'Action manquante.'], 400);
}

try {
    switch ($action) {
        case 'get_exams_public': {
            co_sync_exam_visibility($pdo);
            if (tcf_schema_has_table($pdo, 'comprehension_orale')) {
                $rows = tcf_exam_list($pdo, 'co', true);
                foreach ($rows as &$r) {
                    $full = tcf_exam_fetch_by_id($pdo, 'co', (int) $r['id']);
                    $r['question_count'] = count($full['questions'] ?? []);
                }
                unset($r);
            } else {
                $st = $pdo->query(
                    "SELECT e.id, e.slug, e.title, e.subtitle, e.visibility, e.duration_seconds, e.published_at,
                        (SELECT COUNT(*) FROM tcf_co_questions q WHERE q.exam_id = e.id) AS question_count
                     FROM tcf_co_exams e
                     WHERE e.is_published = 1"
                );
                $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            }
            co_sort_exams_public_user_order($rows);
            co_json(['success' => true, 'data' => $rows]);
        }

        case 'get_exam_quiz': {
            $examId = (int) ($_POST['exam_id'] ?? $_GET['exam_id'] ?? 0);
            if ($examId <= 0) {
                co_json(['success' => false, 'message' => 'Épreuve invalide.'], 422);
            }
            co_sync_exam_visibility($pdo);
            if (tcf_schema_has_table($pdo, 'comprehension_orale')) {
                $row = tcf_exam_fetch_by_id($pdo, 'co', $examId);
                if (!$row || empty($row['is_published'])) {
                    co_json(['success' => false, 'message' => 'Épreuve indisponible.'], 404);
                }
            } else {
                $st = $pdo->prepare('SELECT * FROM tcf_co_exams WHERE id=? AND is_published=1');
                $st->execute([$examId]);
                $row = $st->fetch(PDO::FETCH_ASSOC);
                if (!$row) {
                    co_json(['success' => false, 'message' => 'Épreuve indisponible.'], 404);
                }
            }
            if (((string) ($row['visibility'] ?? 'gratuit')) === 'premium') {
                if (empty($_SESSION['user_id'])) {
                    co_json(['success' => false, 'locked' => true, 'reason' => 'login', 'message' => 'Connectez-vous pour accéder à cette épreuve.'], 403);
                }
                $stmtU = $pdo->prepare('SELECT * FROM users WHERE id=?');
                $stmtU->execute([(int) $_SESSION['user_id']]);
                $viewer = $stmtU->fetch(PDO::FETCH_ASSOC) ?: null;
                if (!$viewer || !tcf_user_has_premium_access($viewer)) {
                    co_json(['success' => false, 'locked' => true, 'reason' => 'subscription', 'message' => 'Abonnement requis pour accéder à cette épreuve.'], 403);
                }
            }
            co_track_view($pdo, $examId);
            $full = co_fetch_exam_full($pdo, $examId);
            if (!$full || empty($full['questions'])) {
                co_json(['success' => false, 'message' => 'Contenu incomplet.'], 404);
            }
            $quiz = co_exam_to_quiz_payload($full);
            $totalPoints = 0;
            foreach ($quiz as $qq) {
                $totalPoints += (int) ($qq['points'] ?? 0);
            }
            co_json([
                'success' => true,
                'data' => [
                    'exam' => [
                        'id' => (int) $full['id'],
                        'title' => $full['title'],
                        'subtitle' => $full['subtitle'],
                        'intro_html' => $full['intro_html'],
                        'duration_seconds' => (int) ($full['duration_seconds'] ?? 2100),
                        'question_count' => count($quiz),
                        'total_points' => $totalPoints,
                    ],
                    'questions' => $quiz,
                ],
            ]);
        }

        case 'get_exams_admin': {
            if (!co_is_admin()) {
                co_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }
            co_sync_exam_visibility($pdo);
            if (tcf_schema_has_table($pdo, 'comprehension_orale')) {
                $rows = tcf_exam_list($pdo, 'co', false);
                foreach ($rows as &$r) {
                    $full = tcf_exam_fetch_by_id($pdo, 'co', (int) $r['id']);
                    $r['question_count'] = count($full['questions'] ?? []);
                    $r['view_count'] = (int) ($r['views_count'] ?? 0);
                }
                unset($r);
            } else {
                $st = $pdo->query(
                    "SELECT e.*, (SELECT COUNT(*) FROM tcf_co_questions q WHERE q.exam_id=e.id) AS question_count,
                        (SELECT COUNT(*) FROM tcf_co_exam_views v WHERE v.exam_id=e.id) AS view_count
                     FROM tcf_co_exams e"
                );
                $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            }
            co_sort_exams_by_title($rows);
            foreach ($rows as &$r) {
                $r['effective_visibility'] = (string) ($r['visibility'] ?? 'gratuit');
            }
            unset($r);
            co_json(['success' => true, 'data' => $rows]);
        }

        case 'get_exam_for_edit': {
            if (!co_is_admin()) {
                co_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }
            $examId = (int) ($_POST['exam_id'] ?? $_GET['exam_id'] ?? 0);
            if ($examId <= 0) {
                co_json(['success' => false, 'message' => 'ID invalide.'], 422);
            }
            $exam = co_fetch_exam_full($pdo, $examId);
            if (!$exam) {
                co_json(['success' => false, 'message' => 'Introuvable.'], 404);
            }
            $editQuestions = [];
            foreach ($exam['questions'] as $q) {
                $slots = [['text' => ''], ['text' => ''], ['text' => ''], ['text' => '']];
                $correctIndex = 0;
                foreach ($q['answers'] as $i => $a) {
                    $so = isset($a['sort_order']) ? (int) $a['sort_order'] : -1;
                    $key = strtolower(trim((string) ($a['answer_key'] ?? '')));
                    if ($so >= 0 && $so <= 3) {
                        $slot = $so;
                    } elseif (preg_match('/^[abcd]$/', $key)) {
                        $slot = ord($key) - ord('a');
                    } else {
                        $slot = min(3, max(0, (int) $i));
                    }
                    $slots[$slot] = ['text' => (string) ($a['answer_text'] ?? '')];
                    if (!empty($a['is_correct'])) {
                        $correctIndex = $slot;
                    }
                }
                $editQuestions[] = [
                    'id' => (int) $q['id'],
                    'question_text' => (string) ($q['question_text'] ?? ''),
                    'points' => (int) ($q['points'] ?? 1),
                    'image_src' => (string) ($q['image_src'] ?? ''),
                    'audio_src' => (string) ($q['audio_src'] ?? ''),
                    'audio_text' => (string) ($q['audio_text'] ?? ''),
                    'correct_index' => $correctIndex,
                    'answers' => $slots,
                ];
            }
            unset($exam['questions']);
            $exam['quiz_questions'] = $editQuestions;
            co_json(['success' => true, 'data' => $exam]);
        }

        case 'save_exam': {
            if (!co_is_admin()) {
                co_json(['success' => false, 'message' => 'Accès refusé.'], 403);
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
            $durationSeconds = max(60, min(86400, (int) ($_POST['duration_seconds'] ?? 2100)));

            if ($title === '') {
                co_json(['success' => false, 'message' => 'Titre obligatoire.'], 422);
            }

            $questions = $_POST['questions'] ?? null;
            if (!is_array($questions) || count($questions) === 0) {
                $raw = trim((string) ($_POST['questions_json'] ?? ''));
                if ($raw !== '') {
                    $decoded = json_decode($raw, true);
                    $questions = is_array($decoded) ? $decoded : [];
                } else {
                    $questions = [];
                }
            }

            $questions = co_normalize_questions_input($questions);

            if (count($questions) === 0) {
                co_json(['success' => false, 'message' => 'Ajoutez au moins une question (formulaire ou JSON).'], 422);
            }

            foreach ($questions as $qi => $q) {
                if (!is_array($q)) {
                    co_json(['success' => false, 'message' => 'Données question invalides.'], 422);
                }
                $answersIn = $q['answers'] ?? [];
                if (!is_array($answersIn) || count($answersIn) < 4) {
                    co_json(['success' => false, 'message' => 'Chaque question doit avoir 4 cases A–D (question ' . ($qi + 1) . ').'], 422);
                }
                $ci = isset($q['correct_index']) ? (int) $q['correct_index'] : 0;
                if ($ci < 0 || $ci > 3) {
                    co_json(['success' => false, 'message' => 'Indice de bonne réponse invalide (question ' . ($qi + 1) . ').'], 422);
                }
            }

            if (tcf_schema_has_table($pdo, 'comprehension_orale')) {
                $isNewExam = $examId <= 0;
                $wasPublished = 0;
                if ($examId > 0) {
                    $stWas = $pdo->prepare("SELECT is_published FROM comprehension_orale WHERE id=? AND kind='exam'");
                    $stWas->execute([$examId]);
                    $wasPublished = (int) $stWas->fetchColumn();
                }
                $qPayload = [];
                foreach ($questions as $i => $q) {
                    $answers = [];
                    $correctIdx = (int) ($q['correct_index'] ?? 0);
                    for ($ai = 0; $ai < 4; $ai++) {
                        $a = $q['answers'][$ai] ?? null;
                        $atxt = '';
                        if (is_array($a)) {
                            $atxt = co_strip_answer_letter_prefix((string) ($a['text'] ?? ''));
                        }
                        $answers[] = [
                            'id' => ($i + 1) * 10 + $ai,
                            'answer_key' => ['a', 'b', 'c', 'd'][$ai],
                            'answer_text' => $atxt,
                            'is_correct' => ($ai === $correctIdx) ? 1 : 0,
                            'sort_order' => $ai,
                        ];
                    }
                    $qPayload[] = [
                        'id' => $i + 1,
                        'sort_order' => $i + 1,
                        'question_text' => (string) ($q['question_text'] ?? ''),
                        'points' => (int) ($q['points'] ?? 1),
                        'image_src' => (string) ($q['image_src'] ?? ''),
                        'audio_src' => (string) ($q['audio_src'] ?? ''),
                        'audio_text' => (string) ($q['audio_text'] ?? ''),
                        'answers' => $answers,
                    ];
                }
                $examId = tcf_exam_save($pdo, 'co', [
                    'slug' => co_slug($title),
                    'title' => $title,
                    'subtitle' => $subtitle !== '' ? $subtitle : null,
                    'intro_html' => $introHtml !== '' ? $introHtml : null,
                    'visibility' => $visibility,
                    'is_published' => $isPublished,
                    'duration_seconds' => $durationSeconds,
                    'created_by' => (int) ($_SESSION['user_id'] ?? 0),
                ], ['questions' => $qPayload], $examId > 0 ? $examId : null);
                if ($isPublished && ($isNewExam || !$wasPublished)) {
                    tcf_notify_users_registered_before(
                        $pdo,
                        'exam',
                        'Nouvelle épreuve — Compréhension orale',
                        "L'épreuve « $title » est maintenant disponible.",
                        site_href('comprehension_orale_quiz.php?exam_id=' . $examId)
                    );
                }
                co_persist_questions_media_blobs($pdo, $questions);
                co_sync_exam_visibility($pdo);
                co_json(['success' => true, 'message' => 'Épreuve enregistrée.', 'exam_id' => $examId]);
            }

            $pdo->beginTransaction();
            try {
                $wasPublished = 0;
                $isNewExam = $examId <= 0;
                if ($examId > 0) {
                    $stWas = $pdo->prepare('SELECT is_published FROM tcf_co_exams WHERE id=?');
                    $stWas->execute([$examId]);
                    $wasPublished = (int) $stWas->fetchColumn();
                    $pdo->prepare(
                        'UPDATE tcf_co_exams SET title=?, subtitle=?, intro_html=?, visibility=?, is_published=?, duration_seconds=?, published_at=IF(?=1 AND is_published=0, NOW(), published_at) WHERE id=?'
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
                    $pdo->prepare('DELETE FROM tcf_co_questions WHERE exam_id=?')->execute([$examId]);
                } else {
                    $slug = co_slug($title);
                    $pdo->prepare(
                        'INSERT INTO tcf_co_exams (slug,title,subtitle,intro_html,visibility,is_published,duration_seconds,published_at,created_by) VALUES (?,?,?,?,?,?,?,IF(?=1,NOW(),NULL),?)'
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
                    'INSERT INTO tcf_co_questions (exam_id,sort_order,question_text,points,image_src,audio_src,audio_text) VALUES (?,?,?,?,?,?,?)'
                );
                $insA = $pdo->prepare(
                    'INSERT INTO tcf_co_answers (question_id,answer_key,answer_text,is_correct,sort_order) VALUES (?,?,?,?,?)'
                );

                foreach ($questions as $ord => $q) {
                    $qtxt = trim((string) ($q['question_text'] ?? ''));
                    $pts = (int) ($q['points'] ?? 1);
                    $img = trim((string) ($q['image_src'] ?? ''));
                    $aud = trim((string) ($q['audio_src'] ?? ''));
                    $audText = trim((string) ($q['audio_text'] ?? ''));
                    $correctIdx = (int) ($q['correct_index'] ?? 0);
                    if ($qtxt === '') {
                        throw new RuntimeException('Texte de question vide.');
                    }
                    if ($audText === '' && $aud === '') {
                        throw new RuntimeException(
                            'Question ' . ($ord + 1) . ' : indiquez le texte audio.'
                        );
                    }
                    $insQ->execute([
                        $examId,
                        $ord + 1,
                        $qtxt,
                        max(1, $pts),
                        $img !== '' ? $img : null,
                        $aud !== '' ? $aud : null,
                        $audText !== '' ? $audText : null,
                    ]);
                    $qid = (int) $pdo->lastInsertId();
                    $answersIn = $q['answers'] ?? [];
                    if (!is_array($answersIn)) {
                        $answersIn = [];
                    }
                    // 4 slots A–D toujours enregistrés (texte facultatif)
                    for ($ai = 0; $ai < 4; $ai++) {
                        $a = $answersIn[$ai] ?? null;
                        $atxt = '';
                        if (is_array($a)) {
                            $atxt = co_strip_answer_letter_prefix((string) ($a['text'] ?? ''));
                        } elseif (is_string($a)) {
                            $atxt = co_strip_answer_letter_prefix($a);
                        }
                        $key = ['a', 'b', 'c', 'd'][$ai];
                        $ok = ($ai === $correctIdx) ? 1 : 0;
                        $insA->execute([$qid, $key, $atxt, $ok, $ai]);
                    }
                    if ($correctIdx < 0 || $correctIdx > 3) {
                        throw new RuntimeException(
                            'Question ' . ($ord + 1) . ' : choisissez la bonne réponse (A–D).'
                        );
                    }
                }

                $pdo->commit();
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                co_json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            co_persist_questions_media_blobs($pdo, $questions);
            if ($isPublished && ($isNewExam || !$wasPublished)) {
                tcf_notify_users_registered_before(
                    $pdo,
                    'exam',
                    'Nouvelle épreuve — Compréhension orale',
                    "L'épreuve « $title » est maintenant disponible.",
                    site_href('comprehension_orale_quiz.php?exam_id=' . $examId)
                );
            }
            co_sync_exam_visibility($pdo);
            co_json(['success' => true, 'message' => 'Épreuve enregistrée.', 'exam_id' => $examId]);
        }

        case 'delete_exam': {
            if (!co_is_admin()) {
                co_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }
            if (!tcf_is_super_admin()) {
                co_json(['success' => false, 'message' => 'Seul le super administrateur peut supprimer une épreuve.'], 403);
            }
            $examId = (int) ($_POST['exam_id'] ?? 0);
            if ($examId <= 0) {
                co_json(['success' => false, 'message' => 'ID invalide.'], 422);
            }
            if (tcf_schema_has_table($pdo, 'comprehension_orale')) {
                tcf_exam_delete($pdo, 'co', $examId);
            } else {
                $pdo->prepare('DELETE FROM tcf_co_exams WHERE id=?')->execute([$examId]);
            }
            tcf_delete_notifications_matching($pdo, 'comprehension_orale_quiz.php?exam_id=' . $examId);
            co_sync_exam_visibility($pdo);
            co_json(['success' => true, 'message' => 'Supprimé.']);
        }

        case 'get_consignes': {
            if (tcf_schema_has_table($pdo, 'comprehension_orale')) {
                $canPremium = co_can_view_premium_consigne($pdo);
                $rows = tcf_exam_list_consignes($pdo, 'co');
                $keys = ['structure', 'techniques', 'erreurs'];
                $rows = array_values(array_filter($rows, static function (array $row) use ($keys, $canPremium): bool {
                    $key = (string) ($row['section_key'] ?? '');
                    if (!in_array($key, $keys, true) || empty($row['is_published'])) {
                        return false;
                    }
                    if (!$canPremium && (string) ($row['visibility'] ?? 'gratuit') !== 'gratuit') {
                        return false;
                    }
                    return true;
                }));
                usort($rows, static fn (array $a, array $b): int => ((int) ($a['sort_order'] ?? 0)) <=> ((int) ($b['sort_order'] ?? 0)));
            } else {
                co_seed_default_consignes($pdo);
                $canPremium = co_can_view_premium_consigne($pdo);
                $keys = "'structure','techniques','erreurs'";
                if ($canPremium) {
                    $st = $pdo->query(
                        "SELECT id, title, body, section_key, visibility, is_published, sort_order FROM tcf_co_consignes WHERE is_published=1 AND section_key IN ($keys) ORDER BY sort_order ASC, id ASC"
                    );
                } else {
                    $st = $pdo->query(
                        "SELECT id, title, body, section_key, visibility, is_published, sort_order FROM tcf_co_consignes WHERE is_published=1 AND visibility='gratuit' AND section_key IN ($keys) ORDER BY sort_order ASC, id ASC"
                    );
                }
                $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }
            foreach ($rows as &$row) {
                $row['body'] = tcf_normalize_rich((string) ($row['body'] ?? ''));
                $row['task_key'] = (string) ($row['section_key'] ?? '');
            }
            unset($row);
            co_json(['success' => true, 'data' => $rows, 'can_premium' => $canPremium]);
        }

        case 'get_consignes_bundle_admin': {
            if (!co_is_admin()) {
                co_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }
            $out = ['structure' => '', 'techniques' => '', 'erreurs' => '', 'is_published' => 1];
            if (tcf_schema_has_table($pdo, 'comprehension_orale')) {
                foreach (tcf_exam_list_consignes($pdo, 'co') as $row) {
                    $k = (string) ($row['section_key'] ?? '');
                    if (isset($out[$k])) {
                        $out[$k] = (string) ($row['body'] ?? '');
                        $out['is_published'] = (int) ($row['is_published'] ?? 1);
                    }
                }
            } else {
                co_seed_default_consignes($pdo);
                $st = $pdo->query("SELECT body, section_key, is_published FROM tcf_co_consignes WHERE section_key IN ('structure','techniques','erreurs') ORDER BY sort_order ASC, id ASC");
                foreach (($st->fetchAll(PDO::FETCH_ASSOC) ?: []) as $row) {
                    $k = (string) ($row['section_key'] ?? '');
                    if (isset($out[$k])) {
                        $out[$k] = (string) ($row['body'] ?? '');
                        $out['is_published'] = (int) ($row['is_published'] ?? 1);
                    }
                }
            }
            co_json(['success' => true, 'data' => $out]);
        }

        case 'save_consignes_bundle': {
            if (!co_is_admin()) {
                co_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }
            $structure = trim((string) ($_POST['structure'] ?? $_POST['body'] ?? ''));
            $techniques = trim((string) ($_POST['techniques'] ?? ''));
            $erreurs = trim((string) ($_POST['erreurs'] ?? ''));
            $isPublished = isset($_POST['is_published']) && $_POST['is_published'] === '0' ? 0 : 1;
            if ($structure === '' || $techniques === '' || $erreurs === '') {
                co_json(['success' => false, 'message' => 'Veuillez renseigner les 3 sections de consignes.'], 422);
            }
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
            if (tcf_schema_has_table($pdo, 'comprehension_orale')) {
                $pdo->exec("DELETE FROM comprehension_orale WHERE kind='consigne' AND section_key IN ('structure','techniques','erreurs')");
                $ins = $pdo->prepare(
                    "INSERT INTO comprehension_orale (kind, title, section_key, visibility, is_published, content_json, created_at, updated_at)
                     VALUES ('consigne',?,?,?,?,?,NOW(),NOW())"
                );
                $sort = 0;
                foreach (['structure', 'techniques', 'erreurs'] as $key) {
                    $sort++;
                    $ins->execute([
                        $titles[$key],
                        $key,
                        'gratuit',
                        $isPublished,
                        json_encode(['body' => $bodies[$key], 'sort_order' => $sort], JSON_UNESCAPED_UNICODE),
                    ]);
                }
            } else {
                co_seed_default_consignes($pdo);
                $pdo->exec("DELETE FROM tcf_co_consignes WHERE section_key IN ('structure','techniques','erreurs')");
                $ins = $pdo->prepare('INSERT INTO tcf_co_consignes (title, body, section_key, visibility, is_published, sort_order) VALUES (?,?,?,?,?,?)');
                $sort = 0;
                foreach (['structure', 'techniques', 'erreurs'] as $key) {
                    $sort++;
                    $ins->execute([$titles[$key], $bodies[$key], $key, 'gratuit', $isPublished, $sort]);
                }
            }
            co_json(['success' => true, 'message' => 'Consignes enregistrées.']);
        }

        case 'upload_co_media': {
            $kind = trim((string) ($_POST['kind'] ?? ''));
            $up = co_handle_media_upload($kind);
            if (!$up['ok']) {
                co_json(['success' => false, 'message' => $up['message'] ?? 'Erreur'], 422);
            }
            $path = (string) ($up['path'] ?? '');
            $url = (string) ($up['url'] ?? '');
            if ($url === '' && $path !== '') {
                $url = co_resolve_media_url($path);
            }
            co_json([
                'success' => true,
                'path' => $path,
                'url' => $url,
            ]);
        }

        default:
            co_json(['success' => false, 'message' => 'Action inconnue.'], 400);
    }
} catch (Throwable $e) {
    co_json(['success' => false, 'message' => $e->getMessage()], 500);
}
