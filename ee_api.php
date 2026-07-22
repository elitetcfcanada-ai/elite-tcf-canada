<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/subscription_access.php';
require_once __DIR__ . '/includes/tcf_notifications_helper.php';
require_once __DIR__ . '/includes/rich_text.php';
require_once __DIR__ . '/includes/admin_roles.php';

header('Content-Type: application/json; charset=utf-8');

function ee_json(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function ee_is_admin(): bool
{
    return tcf_is_staff_admin();
}

function ee_is_logged(): bool
{
    return !empty($_SESSION['user_id']);
}

function ee_slug(string $title): string
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
    return substr((string) $s, 0, 120) . '-' . substr(uniqid(), -6);
}

function ee_ensure_consignes_table(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_ee_consignes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            body TEXT NOT NULL,
            task_key VARCHAR(20) NOT NULL DEFAULT 'general',
            visibility VARCHAR(20) NOT NULL DEFAULT 'gratuit',
            is_published TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    try {
        $hasTaskKey = false;
        $cols = $pdo->query("SHOW COLUMNS FROM tcf_ee_consignes")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $col) {
            if (($col['Field'] ?? '') === 'task_key') {
                $hasTaskKey = true;
                break;
            }
        }
        if (!$hasTaskKey) {
            $pdo->exec("ALTER TABLE tcf_ee_consignes ADD COLUMN task_key VARCHAR(20) NOT NULL DEFAULT 'general' AFTER body");
        }
        $hasVisibility = false;
        $hasIsPublished = false;
        foreach ($cols as $col) {
            if (($col['Field'] ?? '') === 'visibility') {
                $hasVisibility = true;
            }
            if (($col['Field'] ?? '') === 'is_published') {
                $hasIsPublished = true;
            }
        }
        if (!$hasVisibility) {
            $pdo->exec("ALTER TABLE tcf_ee_consignes ADD COLUMN visibility VARCHAR(20) NOT NULL DEFAULT 'gratuit' AFTER task_key");
        }
        if (!$hasIsPublished) {
            $pdo->exec("ALTER TABLE tcf_ee_consignes ADD COLUMN is_published TINYINT(1) NOT NULL DEFAULT 1 AFTER visibility");
        }
    } catch (Throwable $e) {
    }
}

function ee_seed_default_consignes(PDO $pdo): void
{
    require_once __DIR__ . '/includes/tcf_consignes_defaults.php';
    $bodies = tcf_consigne_ee_bodies();
    $titles = [
        'tache1' => 'Tâche 1 : Message court',
        'tache2' => 'Tâche 2 : Article de blog / Narration',
        'tache3' => 'Tâche 3 : Texte argumentatif',
    ];

    foreach (['tache1', 'tache2', 'tache3'] as $i => $key) {
        $sort = $i + 1;
        $st = $pdo->prepare('SELECT id, body FROM tcf_ee_consignes WHERE task_key=? ORDER BY id ASC LIMIT 1');
        $st->execute([$key]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        $body = $bodies[$key];
        $title = $titles[$key];
        if (!$row) {
            $ins = $pdo->prepare(
                'INSERT INTO tcf_ee_consignes (title, body, task_key, visibility, is_published, sort_order, is_active) VALUES (?, ?, ?, ?, 1, ?, 1)'
            );
            $ins->execute([$title, $body, $key, 'gratuit', $sort]);
            continue;
        }
        if (tcf_consigne_body_needs_refresh((string) ($row['body'] ?? ''), 'ee')) {
            $upd = $pdo->prepare(
                'UPDATE tcf_ee_consignes SET title=?, body=?, visibility=?, is_published=1, sort_order=?, is_active=1 WHERE id=?'
            );
            $upd->execute([$title, $body, 'gratuit', $sort, (int) $row['id']]);
        }
    }
}

function ee_ensure_exams_visibility_column(PDO $pdo): void
{
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM tcf_ee_exams")->fetchAll(PDO::FETCH_ASSOC);
        $hasVisibility = false;
        foreach ($cols as $col) {
            if (($col['Field'] ?? '') === 'visibility') {
                $hasVisibility = true;
                break;
            }
        }
        if (!$hasVisibility) {
            $pdo->exec("ALTER TABLE tcf_ee_exams ADD COLUMN visibility VARCHAR(20) NOT NULL DEFAULT 'gratuit' AFTER subtitle");
        }
    } catch (Throwable $e) {
    }
}

function ee_ensure_exam_views_table(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_ee_exam_views (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            exam_id INT UNSIGNED NOT NULL,
            user_id INT NOT NULL DEFAULT 0,
            visitor_id VARCHAR(64) NOT NULL DEFAULT '',
            viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_ee_exam_viewer (exam_id, user_id, visitor_id),
            KEY idx_ee_exam (exam_id),
            KEY idx_ee_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    try {
        $pdo->exec("ALTER TABLE tcf_ee_exam_views ADD COLUMN IF NOT EXISTS visitor_id VARCHAR(64) NOT NULL DEFAULT '' AFTER user_id");
        $pdo->exec("ALTER TABLE tcf_ee_exam_views DROP INDEX IF EXISTS uq_ee_exam_user");
        $pdo->exec("ALTER TABLE tcf_ee_exam_views ADD UNIQUE KEY IF NOT EXISTS uq_ee_exam_viewer (exam_id, user_id, visitor_id)");
    } catch (Throwable $e) {
    }
}

function ee_track_exam_view(PDO $pdo, int $examId): void
{
    if ($examId <= 0) {
        return;
    }
    ee_ensure_exam_views_table($pdo);
    try {
        $uid = (int) ($_SESSION['user_id'] ?? 0);
        if ($uid > 0) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO tcf_ee_exam_views (exam_id, user_id, visitor_id) VALUES (?, ?, ?)");
            $stmt->execute([$examId, $uid, '']);
        } else {
            $vid = tcf_visitor_id();
            if ($vid !== '') {
                $stmt = $pdo->prepare("INSERT IGNORE INTO tcf_ee_exam_views (exam_id, user_id, visitor_id) VALUES (?, 0, ?)");
                $stmt->execute([$examId, $vid]);
            }
        }
    } catch (Throwable $e) {
    }
}

function ee_exam_rank_from_title(string $title): int
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
    if ($year <= 0) return 0;
    return ($year * 100) + $month;
}

function ee_sort_exams_by_title_date(array &$rows): void
{
    usort($rows, static function (array $a, array $b): int {
        $ra = ee_exam_rank_from_title((string) ($a['title'] ?? ''));
        $rb = ee_exam_rank_from_title((string) ($b['title'] ?? ''));
        if ($ra !== $rb) return $rb <=> $ra;
        return (int) ($b['id'] ?? 0) <=> (int) ($a['id'] ?? 0);
    });
}

function ee_recent_free_exam_ids_from_rows(array $rows, int $limit = 3): array
{
    ee_sort_exams_by_title_date($rows);
    $ids = [];
    foreach ($rows as $row) {
        if ((string) ($row['visibility'] ?? 'gratuit') === 'premium') {
            continue; // premium force remains premium, even in top 3
        }
        $ids[] = (int) ($row['id'] ?? 0);
        if (count($ids) >= $limit) {
            break;
        }
    }
    return $ids;
}

function ee_sync_exam_visibility(PDO $pdo): void
{
    $rows = $pdo->query("SELECT id, title, visibility FROM tcf_ee_exams WHERE is_published=1")->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) {
        return;
    }
    ee_sort_exams_by_title_date($rows);
    $top3Ids = [];
    foreach ($rows as $row) {
        $top3Ids[] = (int) ($row['id'] ?? 0);
        if (count($top3Ids) >= 3) break;
    }
    if ($top3Ids) {
        $in = implode(',', array_map('intval', $top3Ids));
        $pdo->exec("UPDATE tcf_ee_exams SET visibility='premium' WHERE is_published=1 AND id NOT IN ($in)");
        $pdo->exec("UPDATE tcf_ee_exams SET visibility='gratuit' WHERE is_published=1 AND id IN ($in) AND visibility<>'premium'");
    } else {
        $pdo->exec("UPDATE tcf_ee_exams SET visibility='premium' WHERE is_published=1");
    }
}

function ee_can_view_premium_consigne(PDO $pdo): bool
{
    if (!ee_is_logged()) {
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

function ee_consigne_task_title(string $taskKey): string
{
    if ($taskKey === 'tache2') {
        return 'Tâche 2 : Article de blog / Narration';
    }
    if ($taskKey === 'tache3') {
        return 'Tâche 3 : Texte argumentatif';
    }
    return 'Tâche 1 : Message court';
}

function ee_simulator_task_defaults(string $taskKey): array
{
    if ($taskKey === 'tache2') {
        return ['label' => 'Tâche 2', 'task_number' => 2, 'duration_seconds' => 1200, 'word_min' => 120, 'word_max' => 150];
    }
    if ($taskKey === 'tache3') {
        return ['label' => 'Tâche 3', 'task_number' => 3, 'duration_seconds' => 1200, 'word_min' => 120, 'word_max' => 180];
    }
    return ['label' => 'Tâche 1', 'task_number' => 1, 'duration_seconds' => 1200, 'word_min' => 60, 'word_max' => 120];
}

function ee_get_api_key(): string
{
    $apiKey = '';
    $keyFile = __DIR__ . '/includes/gemini_key.php';
    if (is_file($keyFile)) {
        $fromFile = include $keyFile;
        if (is_string($fromFile) && trim($fromFile) !== '') {
            $apiKey = trim($fromFile);
        }
    }
    if ($apiKey === '') {
        $apiKey = trim((string) getenv('GEMINI_API_KEY'));
    }
    return $apiKey;
}

function ee_call_gemini_text(string $prompt, string $apiKey): ?string
{
    if ($apiKey === '') {
        return null;
    }
    $body = [
        'contents' => [
            ['role' => 'user', 'parts' => [['text' => $prompt]]],
        ],
        'generationConfig' => ['temperature' => 0.7, 'topP' => 0.95, 'maxOutputTokens' => 512],
    ];
    $models = ['gemini-2.5-flash', 'gemini-2.0-flash-001', 'gemini-2.0-flash', 'gemini-flash-latest'];
    foreach ($models as $model) {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent?key=' . rawurlencode($apiKey);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 30,
        ]);
        $resp = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($resp === false || $httpCode >= 400) {
            continue;
        }
        $decoded = json_decode($resp, true);
        if (!is_array($decoded)) {
            continue;
        }
        $raw = '';
        foreach (($decoded['candidates'][0]['content']['parts'] ?? []) as $p) {
            $raw .= trim((string) ($p['text'] ?? ''));
        }
        $raw = trim((string) preg_replace('/^```[a-z]*\s*/i', '', $raw));
        $raw = trim((string) preg_replace('/```$/', '', $raw));
        if ($raw !== '') {
            return $raw;
        }
    }
    return null;
}

function ee_try_decode_feedback(string $rawText): ?array
{
    $raw = trim($rawText);
    if ($raw === '') return null;
    $raw = (string) preg_replace('/^```(?:json)?\s*/i', '', $raw);
    $raw = (string) preg_replace('/```\s*$/', '', $raw);
    $raw = trim($raw);

    $decoded = json_decode($raw, true);
    if (is_array($decoded)) return $decoded;

    // extraire un objet JSON noyé dans du texte
    $start = strpos($raw, '{');
    $end = strrpos($raw, '}');
    if ($start !== false && $end !== false && $end > $start) {
        $slice = substr($raw, $start, $end - $start + 1);
        $decoded = json_decode($slice, true);
        if (is_array($decoded)) return $decoded;
    }

    // réparation simple des guillemets typographiques
    $normalized = str_replace(["\xE2\x80\x9C", "\xE2\x80\x9D", "\xE2\x80\x99"], ['"', '"', "'"], $raw);
    $decoded = json_decode($normalized, true);
    if (is_array($decoded)) return $decoded;

    return null;
}

function ee_feedback_fallback_from_text(string $rawText): array
{
    $short = trim(strip_tags($rawText));
    if ($short === '') $short = "Réponse partiellement exploitable. Merci de réessayer.";
    return [
        'cefr_level' => 'B1',
        'score_global' => 10,
        'score_details' => ['coherence' => 2, 'vocabulaire' => 2, 'grammaire' => 2, 'consignes' => 2],
        'remarks' => $short,
        'corrections' => "Impossible d'extraire un JSON strict. Relancez la correction.",
        'improved_text' => '',
        'tips' => ["Relire la structure", "Vérifier grammaire et ponctuation", "Renforcer les transitions"],
    ];
}

function ee_rule_based_feedback(string $userText, string $prompt, int $wordMin, int $wordMax, int $wordCount): array
{
    $text = trim($userText);
    $lower = mb_strtolower($text);
    $scoreCoherence = 2;
    $scoreVocab = 2;
    $scoreGrammar = 2;
    $scoreConsignes = 2;
    $tips = [];
    $remarks = [];

    // Cohérence: connecteurs + paragraphes
    $connectors = ['d\'abord', 'ensuite', 'de plus', 'en revanche', 'cependant', 'en conclusion', 'à mon avis', 'selon moi'];
    $foundConn = 0;
    foreach ($connectors as $c) {
        if (mb_stripos($lower, $c) !== false) $foundConn++;
    }
    if ($foundConn >= 2) $scoreCoherence++;
    if (preg_match('/\n\s*\n/u', $text)) $scoreCoherence++;
    if ($scoreCoherence < 3) $tips[] = "Ajoutez des connecteurs logiques pour mieux structurer vos idées.";

    // Vocabulaire: diversité simple
    $tokens = preg_split('/\s+/u', preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $lower) ?? '') ?: [];
    $tokens = array_values(array_filter($tokens, static fn($t) => mb_strlen($t) > 2));
    $unique = count(array_unique($tokens));
    if ($unique >= 35) $scoreVocab++;
    if ($unique >= 55) $scoreVocab++;
    if ($scoreVocab < 3) $tips[] = "Variez davantage le vocabulaire (synonymes, expressions).";

    // Grammaire / ponctuation (heuristiques)
    if (preg_match('/[.!?]/u', $text)) $scoreGrammar++;
    if (substr_count($text, ',') >= 2) $scoreGrammar++;
    if ($scoreGrammar < 3) $tips[] = "Soignez la ponctuation et la longueur des phrases.";

    // Respect des consignes
    if ($wordMin > 0 && $wordCount >= $wordMin) $scoreConsignes++;
    if ($wordMax > 0 && $wordCount <= $wordMax) $scoreConsignes++;
    if ($wordMin > 0 && $wordMax > 0 && ($wordCount < $wordMin || $wordCount > $wordMax)) {
        $remarks[] = "Le nombre de mots ($wordCount) est hors plage attendue ($wordMin-$wordMax).";
        $tips[] = "Respectez strictement la plage de mots demandée.";
    }
    $promptKeywords = array_values(array_unique(array_filter(preg_split('/\s+/u', mb_strtolower(strip_tags($prompt))) ?: [], static fn($w) => mb_strlen($w) >= 5)));
    $hits = 0;
    foreach (array_slice($promptKeywords, 0, 20) as $kw) {
        if (mb_stripos($lower, $kw) !== false) $hits++;
    }
    if ($hits >= 2) $scoreConsignes++;
    if ($scoreConsignes < 3) $tips[] = "Reprenez mieux les éléments-clés de la consigne.";

    $global = $scoreCoherence + $scoreVocab + $scoreGrammar + $scoreConsignes;
    $level = 'B1';
    if ($global >= 12) $level = 'B2';
    if ($global >= 15) $level = 'C1';
    if ($global >= 18) $level = 'C2';

    if (!$remarks) {
        $remarks[] = "Production globalement cohérente. Continuez à renforcer précision et argumentation.";
    }
    if (!$tips) {
        $tips[] = "Très bon travail : maintenez cette structure et enrichissez encore les exemples.";
    }

    return [
        'cefr_level' => $level,
        'score_global' => $global,
        'score_details' => [
            'coherence' => $scoreCoherence,
            'vocabulaire' => $scoreVocab,
            'grammaire' => $scoreGrammar,
            'consignes' => $scoreConsignes,
        ],
        'remarks' => implode(' ', $remarks),
        'corrections' => "Correction automatique robuste appliquée. Relisez les phrases longues, les accords et les transitions.",
        'improved_text' => '',
        'tips' => array_values(array_unique($tips)),
    ];
}

function ee_normalize_feedback(array $fb): array
{
    $out = [];
    $out['cefr_level'] = (string) ($fb['cefr_level'] ?? 'B1');
    $out['score_global'] = is_numeric($fb['score_global'] ?? null) ? (int) $fb['score_global'] : 10;
    $out['score_details'] = is_array($fb['score_details'] ?? null) ? $fb['score_details'] : ['coherence' => 2, 'vocabulaire' => 2, 'grammaire' => 2, 'consignes' => 2];
    $out['remarks'] = (string) ($fb['remarks'] ?? '');
    $out['corrections'] = (string) ($fb['corrections'] ?? '');
    $out['improved_text'] = (string) ($fb['improved_text'] ?? '');
    $tips = $fb['tips'] ?? [];
    $out['tips'] = is_array($tips) ? array_values(array_map('strval', $tips)) : [];
    return $out;
}

function ee_save_combinations(PDO $pdo, int $examId, array $combinationsData): void
{
    foreach ($combinationsData as $ci => $combo) {
        $comboTitle = trim((string) ($combo['title'] ?? 'Combinaison ' . ($ci + 1)));
        $comboNumber = (int) ($combo['combo_number'] ?? ($ci + 1));
        $sortOrder = (int) ($combo['sort_order'] ?? $ci);

        $pdo->prepare('INSERT INTO tcf_ee_combinations (exam_id, combo_number, title, sort_order) VALUES (?, ?, ?, ?)')
            ->execute([$examId, $comboNumber ?: ($ci + 1), $comboTitle, $sortOrder]);
        $comboId = (int) $pdo->lastInsertId();

        $tasks = $combo['tasks'] ?? [];
        foreach ($tasks as $ti => $task) {
            $taskNum = (int) ($task['task_number'] ?? ($ti + 1));
            $promptTxt = trim((string) ($task['prompt'] ?? ''));
            $correctionTxt = trim((string) ($task['correction'] ?? ''));
            $wordMin = isset($task['word_min']) && $task['word_min'] !== '' && $task['word_min'] !== null ? (int) $task['word_min'] : null;
            $wordMax = isset($task['word_max']) && $task['word_max'] !== '' && $task['word_max'] !== null ? (int) $task['word_max'] : null;
            $sortT = (int) ($task['sort_order'] ?? $ti);

            $pdo->prepare('INSERT INTO tcf_ee_tasks (combination_id, task_number, prompt, correction, word_min, word_max, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)')
                ->execute([$comboId, $taskNum ?: ($ti + 1), $promptTxt, $correctionTxt !== '' ? $correctionTxt : null, $wordMin, $wordMax, $sortT]);
            $taskId = (int) $pdo->lastInsertId();

            $docs = $task['documents'] ?? [];
            foreach ($docs as $di => $doc) {
                $docNum = (int) ($doc['doc_number'] ?? ($di + 1));
                $docTitle = trim((string) ($doc['title'] ?? ''));
                $docContent = trim((string) ($doc['content'] ?? ''));
                if ($docContent === '') {
                    continue;
                }
                $pdo->prepare('INSERT INTO tcf_ee_task_documents (task_id, doc_number, title, content, sort_order) VALUES (?, ?, ?, ?, ?)')
                    ->execute([$taskId, $docNum ?: ($di + 1), $docTitle !== '' ? $docTitle : null, $docContent, $di]);
            }
        }
    }
}

$action = trim((string) ($_POST['action'] ?? $_GET['action'] ?? ''));

switch ($action) {

    case 'get_consignes':
        try {
            ee_ensure_consignes_table($pdo);
            ee_seed_default_consignes($pdo);
            $canPremium = ee_can_view_premium_consigne($pdo);
            if ($canPremium) {
                $stmt = $pdo->query("SELECT id, title, body, task_key, visibility, is_published, sort_order FROM tcf_ee_consignes WHERE is_published=1 AND task_key IN ('tache1','tache2','tache3') ORDER BY task_key ASC, sort_order ASC, id ASC");
            } else {
                $stmt = $pdo->query("SELECT id, title, body, task_key, visibility, is_published, sort_order FROM tcf_ee_consignes WHERE is_published=1 AND visibility='gratuit' AND task_key IN ('tache1','tache2','tache3') ORDER BY task_key ASC, sort_order ASC, id ASC");
            }
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$row) {
                $row['body'] = tcf_normalize_rich((string) ($row['body'] ?? ''));
            }
            unset($row);
            ee_json(['success' => true, 'data' => $rows, 'can_premium' => $canPremium]);
        } catch (Throwable $e) {
            ee_json(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'get_consignes_admin':
        if (!ee_is_admin()) {
            ee_json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }
        try {
            ee_ensure_consignes_table($pdo);
            ee_seed_default_consignes($pdo);
            $stmt = $pdo->query("SELECT id, title, body, task_key, visibility, is_published, sort_order FROM tcf_ee_consignes WHERE task_key IN ('tache1','tache2','tache3') ORDER BY task_key ASC, sort_order ASC, id ASC");
            ee_json(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (Throwable $e) {
            ee_json(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'get_consignes_bundle_admin':
        if (!ee_is_admin()) {
            ee_json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }
        try {
            ee_ensure_consignes_table($pdo);
            ee_seed_default_consignes($pdo);
            $tasks = ['tache1', 'tache2', 'tache3'];
            $bundle = [
                'tache1' => '',
                'tache2' => '',
                'tache3' => '',
                'is_published' => 1,
            ];
            $stmt = $pdo->prepare("SELECT body, is_published FROM tcf_ee_consignes WHERE task_key=? ORDER BY sort_order ASC, id ASC LIMIT 1");
            foreach ($tasks as $t) {
                $stmt->execute([$t]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $bundle[$t] = (string) ($row['body'] ?? '');
                    $bundle['is_published'] = (int) ($row['is_published'] ?? 1);
                }
            }
            ee_json(['success' => true, 'data' => $bundle]);
        } catch (Throwable $e) {
            ee_json(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'save_consignes_bundle':
        if (!ee_is_admin()) {
            ee_json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }
        $tache1 = trim((string) ($_POST['tache1'] ?? ''));
        $tache2 = trim((string) ($_POST['tache2'] ?? ''));
        $tache3 = trim((string) ($_POST['tache3'] ?? ''));
        $isPublished = isset($_POST['is_published']) && $_POST['is_published'] === '0' ? 0 : 1;
        if ($tache1 === '' || $tache2 === '' || $tache3 === '') {
            ee_json(['success' => false, 'message' => 'Veuillez renseigner les consignes des 3 tâches.']);
        }
        try {
            ee_ensure_consignes_table($pdo);
            $pdo->beginTransaction();
            $pdo->exec("DELETE FROM tcf_ee_consignes WHERE task_key IN ('tache1','tache2','tache3')");
            $ins = $pdo->prepare("INSERT INTO tcf_ee_consignes (title, body, task_key, visibility, is_published, sort_order, is_active) VALUES (?, ?, ?, 'gratuit', ?, ?, 1)");
            $rows = [
                ['tache1', $tache1, 1],
                ['tache2', $tache2, 2],
                ['tache3', $tache3, 3],
            ];
            foreach ($rows as $r) {
                $ins->execute([ee_consigne_task_title($r[0]), $r[1], $r[0], $isPublished, $r[2]]);
            }
            $pdo->commit();
            ee_json(['success' => true, 'message' => 'Consignes enregistrées et publiées.']);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            ee_json(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'get_simulator_subject':
        $taskKey = trim((string) ($_POST['task_key'] ?? 'tache1'));
        if (!in_array($taskKey, ['tache1', 'tache2', 'tache3'], true)) {
            $taskKey = 'tache1';
        }
        $defaults = ee_simulator_task_defaults($taskKey);
        try {
            ee_ensure_consignes_table($pdo);
            ee_seed_default_consignes($pdo);
            $consigneStmt = $pdo->prepare("SELECT body FROM tcf_ee_consignes WHERE is_published=1 AND task_key=? ORDER BY sort_order ASC, id ASC LIMIT 1");
            $consigneStmt->execute([$taskKey]);
            $consigne = (string) ($consigneStmt->fetchColumn() ?: '');

            $taskNum = (int) $defaults['task_number'];
            $promptStmt = $pdo->prepare("SELECT t.prompt FROM tcf_ee_tasks t INNER JOIN tcf_ee_combinations c ON c.id=t.combination_id INNER JOIN tcf_ee_exams e ON e.id=c.exam_id WHERE e.is_published=1 AND t.task_number=? AND t.prompt IS NOT NULL AND t.prompt<>'' ORDER BY RAND() LIMIT 1");
            $promptStmt->execute([$taskNum]);
            $baseSubject = trim((string) ($promptStmt->fetchColumn() ?: ''));
            if ($baseSubject === '') {
                $fallback = [
                    'tache1' => "Vous écrivez un courriel à un ami pour lui proposer une activité ce week-end.",
                    'tache2' => "Vous racontez une expérience marquante sur votre blog et donnez votre opinion.",
                    'tache3' => "Faut-il limiter l'utilisation du téléphone portable chez les jeunes ? Donnez votre avis.",
                ];
                $baseSubject = $fallback[$taskKey];
            }

            $subject = $baseSubject;
            $apiKey = ee_get_api_key();
            if ($apiKey !== '') {
                $consigneTxt = trim(strip_tags($consigne));
                $aiPrompt = "Tu es concepteur de sujets TCF Canada expression écrite. "
                    . "Crée UN nouveau sujet pour {$defaults['label']} en français. "
                    . "Inspire-toi de cet exemple sans le copier : \"$baseSubject\". "
                    . "Respecte ce cadre de consigne : \"$consigneTxt\". "
                    . "Retourne uniquement le sujet final, court et clair, sans puce ni commentaire.";
                $generated = ee_call_gemini_text($aiPrompt, $apiKey);
                if (is_string($generated) && trim($generated) !== '') {
                    $subject = trim($generated);
                }
            }

            ee_json([
                'success' => true,
                'data' => [
                    'task_key' => $taskKey,
                    'task_label' => $defaults['label'],
                    'duration_seconds' => (int) $defaults['duration_seconds'],
                    'word_min' => (int) $defaults['word_min'],
                    'word_max' => (int) $defaults['word_max'],
                    'subject' => $subject,
                    'consigne' => $consigne,
                    'inspiration' => $baseSubject,
                ],
            ]);
        } catch (Throwable $e) {
            ee_json(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'save_consigne':
        if (!ee_is_admin()) {
            ee_json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }
        $id = (int) ($_POST['id'] ?? 0);
        $title = trim((string) ($_POST['title'] ?? ''));
        $body = trim((string) ($_POST['body'] ?? ''));
        $taskKey = trim((string) ($_POST['task_key'] ?? 'general'));
        $visibility = trim((string) ($_POST['visibility'] ?? 'gratuit'));
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isPublished = isset($_POST['is_published']) && $_POST['is_published'] === '0' ? 0 : 1;
        if ($title === '' || $body === '') {
            ee_json(['success' => false, 'message' => 'Titre et contenu sont requis.']);
        }
        if (!in_array($taskKey, ['general', 'tache1', 'tache2', 'tache3'], true)) {
            $taskKey = 'general';
        }
        if (!in_array($visibility, ['gratuit', 'premium'], true)) {
            $visibility = 'gratuit';
        }
        try {
            ee_ensure_consignes_table($pdo);
            if ($id > 0) {
                $pdo->prepare('UPDATE tcf_ee_consignes SET title=?, body=?, task_key=?, visibility=?, is_published=?, sort_order=? WHERE id=?')
                    ->execute([$title, $body, $taskKey, $visibility, $isPublished, $sortOrder, $id]);
                ee_json(['success' => true, 'message' => 'Consigne mise à jour.']);
            }
            $pdo->prepare('INSERT INTO tcf_ee_consignes (title, body, task_key, visibility, is_published, sort_order) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([$title, $body, $taskKey, $visibility, $isPublished, $sortOrder]);
            ee_json(['success' => true, 'message' => 'Consigne créée.']);
        } catch (Throwable $e) {
            ee_json(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'delete_consigne':
        if (!ee_is_admin()) {
            ee_json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            ee_json(['success' => false, 'message' => 'ID invalide.']);
        }
        try {
            ee_ensure_consignes_table($pdo);
            $pdo->prepare('DELETE FROM tcf_ee_consignes WHERE id=?')->execute([$id]);
            ee_json(['success' => true, 'message' => 'Consigne supprimée.']);
        } catch (Throwable $e) {
            ee_json(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'get_exams':
        try {
            ee_ensure_exams_visibility_column($pdo);
            ee_sync_exam_visibility($pdo);
            $stmt = $pdo->query('SELECT id, slug, title, subtitle, visibility, is_published, published_at, created_at FROM tcf_ee_exams WHERE is_published=1');
            $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ee_sort_exams_by_title_date($exams);
            ee_json(['success' => true, 'data' => $exams]);
        } catch (Throwable $e) {
            ee_json(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'get_exams_admin':
        if (!ee_is_admin()) {
            ee_json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }
        try {
            ee_ensure_exams_visibility_column($pdo);
            ee_ensure_exam_views_table($pdo);
            ee_sync_exam_visibility($pdo);
            $stmt = $pdo->query('SELECT id, slug, title, subtitle, visibility, is_published, published_at, created_at FROM tcf_ee_exams');
            $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ee_sort_exams_by_title_date($exams);
            foreach ($exams as &$e) {
                $s = $pdo->prepare('SELECT COUNT(*) FROM tcf_ee_combinations WHERE exam_id=?');
                $s->execute([$e['id']]);
                $e['combo_count'] = (int) $s->fetchColumn();
                $sv = $pdo->prepare('SELECT COUNT(*) FROM tcf_ee_exam_views WHERE exam_id=?');
                $sv->execute([$e['id']]);
                $e['view_count'] = (int) $sv->fetchColumn();
                $e['effective_visibility'] = (string) ($e['visibility'] ?? 'gratuit');
            }
            unset($e);
            ee_json(['success' => true, 'data' => $exams]);
        } catch (Throwable $e) {
            ee_json(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'get_exam_detail':
        $examId = (int) ($_POST['exam_id'] ?? $_GET['exam_id'] ?? 0);
        if ($examId <= 0) {
            ee_json(['success' => false, 'message' => 'ID invalide.']);
        }

        ee_ensure_exams_visibility_column($pdo);
        ee_sync_exam_visibility($pdo);
        $stmtEx = $pdo->prepare('SELECT * FROM tcf_ee_exams WHERE id=?');
        $stmtEx->execute([$examId]);
        $exam = $stmtEx->fetch(PDO::FETCH_ASSOC);
        if (!$exam) {
            ee_json(['success' => false, 'message' => 'Épreuve introuvable.']);
        }

        $isFree = ((string) ($exam['visibility'] ?? 'gratuit')) !== 'premium';

        if (!$isFree) {
            if (!ee_is_logged()) {
                ee_json(['success' => false, 'locked' => true, 'reason' => 'login', 'message' => 'Connectez-vous pour accéder à cette épreuve.']);
            }
            $stmtU = $pdo->prepare('SELECT * FROM users WHERE id=?');
            $stmtU->execute([(int) $_SESSION['user_id']]);
            $userRow = $stmtU->fetch(PDO::FETCH_ASSOC);
            if (!$userRow || !tcf_user_has_premium_access($userRow)) {
                ee_json(['success' => false, 'locked' => true, 'reason' => 'subscription', 'message' => 'Abonnement requis pour accéder à cette épreuve.']);
            }
        }
        ee_track_exam_view($pdo, $examId);

        $stmtC = $pdo->prepare('SELECT * FROM tcf_ee_combinations WHERE exam_id=? ORDER BY sort_order ASC, combo_number ASC');
        $stmtC->execute([$examId]);
        $combos = $stmtC->fetchAll(PDO::FETCH_ASSOC);

        foreach ($combos as &$combo) {
            $stmtT = $pdo->prepare('SELECT * FROM tcf_ee_tasks WHERE combination_id=? ORDER BY sort_order ASC, task_number ASC');
            $stmtT->execute([$combo['id']]);
            $tasks = $stmtT->fetchAll(PDO::FETCH_ASSOC);
            foreach ($tasks as &$task) {
                $stmtD = $pdo->prepare('SELECT * FROM tcf_ee_task_documents WHERE task_id=? ORDER BY sort_order ASC, doc_number ASC');
                $stmtD->execute([$task['id']]);
                $task['documents'] = $stmtD->fetchAll(PDO::FETCH_ASSOC);
            }
            unset($task);
            $combo['tasks'] = $tasks;
        }
        unset($combo);

        $exam['combinations'] = $combos;
        $exam['is_free'] = $isFree;
        ee_json(['success' => true, 'data' => $exam]);
        break;

    case 'create_exam':
        if (!ee_is_admin()) {
            ee_json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }
        $title = trim((string) ($_POST['title'] ?? ''));
        $subtitle = trim((string) ($_POST['subtitle'] ?? ''));
        $visibility = trim((string) ($_POST['visibility'] ?? 'gratuit'));
        if (!in_array($visibility, ['gratuit', 'premium'], true)) $visibility = 'gratuit';
        $is_published = isset($_POST['is_published']) && $_POST['is_published'] === '1' ? 1 : 0;
        $combosJson = (string) ($_POST['combinations'] ?? '');
        if ($title === '') {
            ee_json(['success' => false, 'message' => 'Le titre est requis.']);
        }

        $slug = ee_slug($title);
        $combinationsData = json_decode($combosJson, true);
        if (!is_array($combinationsData)) {
            $combinationsData = [];
        }

        try {
            ee_ensure_exams_visibility_column($pdo);
            $pdo->beginTransaction();
            $pdo->prepare('INSERT INTO tcf_ee_exams (slug,title,subtitle,visibility,is_published,published_at,created_by) VALUES (?,?,?,?,?,?,?)')
                ->execute([$slug, $title, $subtitle ?: null, $visibility, $is_published, $is_published ? date('Y-m-d H:i:s') : null, (int) $_SESSION['user_id']]);
            $examId = (int) $pdo->lastInsertId();

            ee_save_combinations($pdo, $examId, $combinationsData);
            ee_sync_exam_visibility($pdo);

            $pdo->commit();
            try {
                $pdo->prepare('INSERT INTO activities (user_id,type,title,description,icon) VALUES (?,?,?,?,?)')
                    ->execute([(int) $_SESSION['user_id'], 'topic', 'Épreuve EE publiée', "L'épreuve '$title' a été créée", 'bx bxs-book']);
            } catch (Throwable $e) {
            }
            if ($is_published) {
                tcf_notify_users_registered_before(
                    $pdo,
                    'exam',
                    'Nouvelle épreuve — Expression écrite',
                    "L'épreuve « $title » est maintenant disponible.",
                    site_href('epreuve_ee.php?id=' . $examId)
                );
            }

            ee_json(['success' => true, 'message' => 'Épreuve créée avec succès.', 'exam_id' => $examId]);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            ee_json(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'update_exam':
        if (!ee_is_admin()) {
            ee_json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }
        $examId = (int) ($_POST['exam_id'] ?? 0);
        $title = trim((string) ($_POST['title'] ?? ''));
        $subtitle = trim((string) ($_POST['subtitle'] ?? ''));
        $visibility = trim((string) ($_POST['visibility'] ?? 'gratuit'));
        if (!in_array($visibility, ['gratuit', 'premium'], true)) $visibility = 'gratuit';
        $is_published = isset($_POST['is_published']) && $_POST['is_published'] === '1' ? 1 : 0;
        $combosJson = (string) ($_POST['combinations'] ?? '');
        if ($examId <= 0 || $title === '') {
            ee_json(['success' => false, 'message' => 'Données invalides.']);
        }

        $combinationsData = json_decode($combosJson, true);
        if (!is_array($combinationsData)) {
            $combinationsData = [];
        }

        try {
            ee_ensure_exams_visibility_column($pdo);
            $wasPublished = 0;
            $stWas = $pdo->prepare('SELECT is_published FROM tcf_ee_exams WHERE id=?');
            $stWas->execute([$examId]);
            $wasPublished = (int) $stWas->fetchColumn();

            $pdo->beginTransaction();
            $pdo->prepare('UPDATE tcf_ee_exams SET title=?,subtitle=?,visibility=?,is_published=?,published_at=CASE WHEN ?=1 AND is_published=0 THEN NOW() ELSE published_at END WHERE id=?')
                ->execute([$title, $subtitle ?: null, $visibility, $is_published, $is_published, $examId]);

            $pdo->prepare('DELETE FROM tcf_ee_combinations WHERE exam_id=?')->execute([$examId]);
            ee_save_combinations($pdo, $examId, $combinationsData);
            ee_sync_exam_visibility($pdo);

            $pdo->commit();
            try {
                $pdo->prepare('INSERT INTO activities (user_id,type,title,description,icon) VALUES (?,?,?,?,?)')
                    ->execute([(int) $_SESSION['user_id'], 'topic', 'Épreuve EE modifiée', "L'épreuve '$title' a été mise à jour", 'bx bxs-book']);
            } catch (Throwable $e) {
            }
            if ($is_published && !$wasPublished) {
                tcf_notify_users_registered_before(
                    $pdo,
                    'exam',
                    'Nouvelle épreuve — Expression écrite',
                    "L'épreuve « $title » est maintenant disponible.",
                    site_href('epreuve_ee.php?id=' . $examId)
                );
            }

            ee_json(['success' => true, 'message' => 'Épreuve mise à jour.']);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            ee_json(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'delete_exam':
        if (!ee_is_admin()) {
            ee_json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }
        if (!tcf_is_super_admin()) {
            ee_json(['success' => false, 'message' => 'Seul le super administrateur peut supprimer une épreuve.'], 403);
        }
        $examId = (int) ($_POST['exam_id'] ?? 0);
        if ($examId <= 0) {
            ee_json(['success' => false, 'message' => 'ID invalide.']);
        }
        try {
            $s = $pdo->prepare('SELECT title FROM tcf_ee_exams WHERE id=?');
            $s->execute([$examId]);
            $row = $s->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                ee_json(['success' => false, 'message' => 'Épreuve introuvable.']);
            }
            $pdo->prepare('DELETE FROM tcf_ee_exams WHERE id=?')->execute([$examId]);
            tcf_delete_notifications_matching($pdo, 'epreuve_ee.php?id=' . $examId);
            try {
                $pdo->prepare('INSERT INTO activities (user_id,type,title,description,icon) VALUES (?,?,?,?,?)')
                    ->execute([(int) $_SESSION['user_id'], 'topic', 'Épreuve EE supprimée', "L'épreuve '{$row['title']}' supprimée", 'bx bxs-book']);
            } catch (Throwable $e) {
            }
            ee_json(['success' => true, 'message' => 'Épreuve supprimée.']);
        } catch (Throwable $e) {
            ee_json(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'get_exam_for_edit':
        if (!ee_is_admin()) {
            ee_json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }
        $examId = (int) ($_POST['exam_id'] ?? $_GET['exam_id'] ?? 0);
        if ($examId <= 0) {
            ee_json(['success' => false, 'message' => 'ID invalide.']);
        }
        try {
            $stmtEx = $pdo->prepare('SELECT * FROM tcf_ee_exams WHERE id=?');
            $stmtEx->execute([$examId]);
            $exam = $stmtEx->fetch(PDO::FETCH_ASSOC);
            if (!$exam) {
                ee_json(['success' => false, 'message' => 'Épreuve introuvable.']);
            }

            $stmtC = $pdo->prepare('SELECT * FROM tcf_ee_combinations WHERE exam_id=? ORDER BY sort_order ASC, combo_number ASC');
            $stmtC->execute([$examId]);
            $combos = $stmtC->fetchAll(PDO::FETCH_ASSOC);
            foreach ($combos as &$combo) {
                $stmtT = $pdo->prepare('SELECT * FROM tcf_ee_tasks WHERE combination_id=? ORDER BY sort_order ASC, task_number ASC');
                $stmtT->execute([$combo['id']]);
                $tasks = $stmtT->fetchAll(PDO::FETCH_ASSOC);
                foreach ($tasks as &$task) {
                    $stmtD = $pdo->prepare('SELECT * FROM tcf_ee_task_documents WHERE task_id=? ORDER BY sort_order ASC, doc_number ASC');
                    $stmtD->execute([$task['id']]);
                    $task['documents'] = $stmtD->fetchAll(PDO::FETCH_ASSOC);
                }
                unset($task);
                $combo['tasks'] = $tasks;
            }
            unset($combo);

            $exam['combinations'] = $combos;
            ee_json(['success' => true, 'data' => $exam]);
        } catch (Throwable $e) {
            ee_json(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'ai_correct':
        $taskId = (int) ($_POST['task_id'] ?? 0);
        $examId = (int) ($_POST['exam_id'] ?? 0);
        $comboId = (int) ($_POST['combo_id'] ?? 0);
        $userText = trim((string) ($_POST['user_text'] ?? ''));
        $wordMin = (int) ($_POST['word_min'] ?? 0);
        $wordMax = (int) ($_POST['word_max'] ?? 0);
        $prompt = trim((string) ($_POST['task_prompt'] ?? ''));

        if ($userText === '' || mb_strlen($userText) < 10) {
            ee_json(['success' => false, 'message' => 'Veuillez écrire votre réponse avant de demander la correction.']);
        }

        $wordCount = str_word_count(strip_tags($userText), 0, "àáâãäåæçèéêëìíîïðñòóôõöùúûüýÿÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖÙÚÛÜÝ");

        $submissionId = 0;
        $currentUserId = ee_is_logged() ? (int) ($_SESSION['user_id'] ?? 0) : 0;
        if ($currentUserId > 0) {
            try {
                $pdo->prepare('INSERT INTO tcf_ee_submissions (user_id, exam_id, combination_id, task_id, user_text, word_count) VALUES (?, ?, ?, ?, ?, ?)')
                    ->execute([$currentUserId, $examId, $comboId, $taskId, $userText, $wordCount]);
                $submissionId = (int) $pdo->lastInsertId();
            } catch (Throwable $e) {
            }
        }

        $apiKey = '';
        $keyFile = __DIR__ . '/includes/gemini_key.php';
        if (is_file($keyFile)) {
            $fromFile = include $keyFile;
            if (is_string($fromFile) && trim($fromFile) !== '') {
                $apiKey = trim($fromFile);
            }
        }
        if ($apiKey === '') {
            $apiKey = trim((string) getenv('GEMINI_API_KEY'));
        }
        if ($apiKey === '') {
            ee_json(['success' => false, 'message' => 'Clé Gemini non configurée.'], 500);
        }

        $wordConstraint = '';
        if ($wordMin > 0 && $wordMax > 0) {
            $wordConstraint = "Cette tâche exige entre $wordMin et $wordMax mots. Le texte soumis contient $wordCount mots. ";
        } elseif ($wordMin > 0) {
            $wordConstraint = "Cette tâche exige au minimum $wordMin mots. Le texte soumis contient $wordCount mots. ";
        }

        $systemPrompt = "Tu es un correcteur expert du TCF Canada (production écrite). "
            . "Critères officiels : cohérence/cohésion, vocabulaire, grammaire, respect des consignes, registre, longueur. "
            . "Objectif : aider l'apprenant à viser le niveau C2. "
            . "Réponds UNIQUEMENT en JSON strict (pas de markdown, pas de texte avant/après) avec cette structure : "
            . '{"cefr_level":"B2","score_global":14,"score_details":{"coherence":4,"vocabulaire":3,"grammaire":3,"consignes":4},"remarks":"...","corrections":"...","improved_text":"...","tips":["..."]}';

        $userMessage = "CONSIGNE : $prompt\n\n" . $wordConstraint . "TEXTE :\n" . $userText;

        $body = [
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $systemPrompt]]],
                ['role' => 'model', 'parts' => [['text' => 'OK, je retournerai uniquement le JSON demandé.']]],
                ['role' => 'user', 'parts' => [['text' => $userMessage]]],
            ],
            'generationConfig' => ['temperature' => 0.2, 'topP' => 0.9, 'maxOutputTokens' => 2048],
        ];

        $models = ['gemini-2.5-flash', 'gemini-2.0-flash-001', 'gemini-2.0-flash', 'gemini-flash-latest'];
        $decoded = null;
        foreach ($models as $model) {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent?key=' . rawurlencode($apiKey);
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
                CURLOPT_TIMEOUT => 40,
            ]);
            $resp = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($resp !== false) {
                $dec = json_decode($resp, true);
                if (is_array($dec) && $httpCode < 400) {
                    $decoded = $dec;
                    break;
                }
            }
        }

        if (!$decoded) {
            ee_json(['success' => false, 'message' => "Erreur de connexion à l'IA."], 502);
        }

        $rawText = '';
        foreach (($decoded['candidates'][0]['content']['parts'] ?? []) as $part) {
            $rawText .= trim((string) ($part['text'] ?? ''));
        }
        $feedback = ee_try_decode_feedback($rawText);
        if (!is_array($feedback)) {
            // tentative de réparation via second prompt court
            $repairPrompt = "Convertis le texte suivant en JSON strict avec les clés "
                . "cefr_level, score_global, score_details, remarks, corrections, improved_text, tips. "
                . "Texte:\n" . $rawText;
            $repaired = ee_call_gemini_text($repairPrompt, $apiKey);
            $feedback = is_string($repaired) ? ee_try_decode_feedback($repaired) : null;
        }
        if (!is_array($feedback)) {
            $feedback = ee_rule_based_feedback($userText, $prompt, $wordMin, $wordMax, $wordCount);
        }
        $feedback = ee_normalize_feedback($feedback);

        if ($submissionId > 0) {
            try {
                $pdo->prepare('INSERT INTO tcf_ee_ai_feedback (submission_id, cefr_level, score_json, remarks, corrected_text) VALUES (?, ?, ?, ?, ?)')
                    ->execute([
                        $submissionId,
                        $feedback['cefr_level'] ?? null,
                        json_encode($feedback['score_details'] ?? [], JSON_UNESCAPED_UNICODE),
                        $feedback['remarks'] ?? null,
                        $feedback['improved_text'] ?? null,
                    ]);
            } catch (Throwable $e) {
            }
        }

        ee_json(['success' => true, 'feedback' => $feedback, 'word_count' => $wordCount, 'submission_id' => $submissionId]);
        break;

    default:
        ee_json(['success' => false, 'message' => 'Action non reconnue.'], 400);
}
