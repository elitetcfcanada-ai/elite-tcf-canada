<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/subscription_access.php';
require_once __DIR__ . '/includes/tcf_notifications_helper.php';
require_once __DIR__ . '/includes/rich_text.php';
require_once __DIR__ . '/includes/admin_roles.php';
require_once __DIR__ . '/includes/gemini_client.php';
require_once __DIR__ . '/includes/tcf_schema.php';
require_once __DIR__ . '/includes/tcf_exam_store.php';
require_once __DIR__ . '/includes/tcf_exam_json_io.php';

function eo_json(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function eo_is_admin(): bool
{
    return tcf_is_staff_admin();
}

function eo_slug(string $title): string
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

function eo_ensure_tables(PDO $pdo): void
{
    if (tcf_schema_is_consolidated($pdo) || tcf_schema_has_table($pdo, 'expression_orale')) {
        return;
    }
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_eo_exams (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(140) NOT NULL UNIQUE,
            title VARCHAR(255) NOT NULL,
            subtitle VARCHAR(255) DEFAULT NULL,
            visibility VARCHAR(20) NOT NULL DEFAULT 'gratuit',
            is_published TINYINT(1) NOT NULL DEFAULT 1,
            published_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            created_by INT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_eo_exam_published (is_published, published_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_eo_parts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            exam_id INT UNSIGNED NOT NULL,
            task_key VARCHAR(20) NOT NULL DEFAULT 'tache2',
            part_number INT NOT NULL DEFAULT 1,
            part_title VARCHAR(255) DEFAULT NULL,
            sort_order INT NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_eo_part_exam (exam_id),
            CONSTRAINT fk_eo_part_exam FOREIGN KEY (exam_id) REFERENCES tcf_eo_exams(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_eo_subjects (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            part_id INT UNSIGNED NOT NULL,
            subject_number INT NOT NULL DEFAULT 1,
            title VARCHAR(255) NOT NULL,
            prompt TEXT NOT NULL,
            role_label VARCHAR(255) DEFAULT NULL,
            icon_class VARCHAR(80) DEFAULT 'bx bx-message-detail',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_eo_subject_part (part_id),
            CONSTRAINT fk_eo_subject_part FOREIGN KEY (part_id) REFERENCES tcf_eo_parts(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_eo_consignes (
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
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_eo_exam_views (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            exam_id INT UNSIGNED NOT NULL,
            user_id INT NOT NULL DEFAULT 0,
            visitor_id VARCHAR(64) NOT NULL DEFAULT '',
            viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_eo_exam_viewer (exam_id, user_id, visitor_id),
            KEY idx_eo_exam (exam_id),
            KEY idx_eo_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    try {
        $pdo->exec("ALTER TABLE tcf_eo_exam_views ADD COLUMN IF NOT EXISTS visitor_id VARCHAR(64) NOT NULL DEFAULT '' AFTER user_id");
        $pdo->exec("ALTER TABLE tcf_eo_exam_views DROP INDEX IF EXISTS uq_eo_exam_user");
        $pdo->exec("ALTER TABLE tcf_eo_exam_views ADD UNIQUE KEY IF NOT EXISTS uq_eo_exam_viewer (exam_id, user_id, visitor_id)");
    } catch (Throwable $e) {
    }
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM tcf_eo_exams")->fetchAll(PDO::FETCH_ASSOC);
        $hasVisibility = false;
        foreach ($cols as $col) {
            if (($col['Field'] ?? '') === 'visibility') {
                $hasVisibility = true;
                break;
            }
        }
        if (!$hasVisibility) {
            $pdo->exec("ALTER TABLE tcf_eo_exams ADD COLUMN visibility VARCHAR(20) NOT NULL DEFAULT 'gratuit' AFTER subtitle");
        }
    } catch (Throwable $e) {
    }
    try {
        $subCols = $pdo->query("SHOW COLUMNS FROM tcf_eo_subjects")->fetchAll(PDO::FETCH_ASSOC);
        $hasCorrection = false;
        foreach ($subCols as $col) {
            if (($col['Field'] ?? '') === 'correction') {
                $hasCorrection = true;
                break;
            }
        }
        if (!$hasCorrection) {
            $pdo->exec("ALTER TABLE tcf_eo_subjects ADD COLUMN correction MEDIUMTEXT NULL AFTER prompt");
        }
    } catch (Throwable $e) {
    }
}

function eo_seed_default_consignes(PDO $pdo): void
{
    require_once __DIR__ . '/includes/tcf_consignes_defaults.php';
    $bodies = tcf_consigne_eo_bodies();
    $titles = [
        'tache1' => 'Tâche 1 : Présentation (entretien dirigé)',
        'tache2' => 'Tâche 2 : Exercice en interaction',
        'tache3' => 'Tâche 3 : Expression d’un point de vue',
    ];
    if (tcf_schema_has_table($pdo, 'expression_orale')) {
        $sections = [];
        foreach (['tache1', 'tache2', 'tache3'] as $i => $key) {
            $sections[] = ['key' => $key, 'title' => $titles[$key], 'body' => $bodies[$key], 'sort' => $i + 1];
        }
        tcf_exam_seed_consignes($pdo, 'eo', $sections);
        return;
    }
    foreach (['tache1', 'tache2', 'tache3'] as $i => $key) {
        $sort = $i + 1;
        $st = $pdo->prepare('SELECT id, body FROM tcf_eo_consignes WHERE task_key=? ORDER BY id ASC LIMIT 1');
        $st->execute([$key]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        $body = $bodies[$key];
        $title = $titles[$key];
        if (!$row) {
            $ins = $pdo->prepare(
                "INSERT INTO tcf_eo_consignes (title, body, task_key, visibility, is_published, sort_order, is_active) VALUES (?, ?, ?, 'gratuit', 1, ?, 1)"
            );
            $ins->execute([$title, $body, $key, $sort]);
            continue;
        }
        if (tcf_consigne_body_needs_refresh((string) ($row['body'] ?? ''), 'eo')) {
            $upd = $pdo->prepare(
                'UPDATE tcf_eo_consignes SET title=?, body=?, visibility=?, is_published=1, sort_order=?, is_active=1 WHERE id=?'
            );
            $upd->execute([$title, $body, 'gratuit', $sort, (int) $row['id']]);
        }
    }
}

function eo_can_view_premium_consigne(PDO $pdo): bool
{
    if (empty($_SESSION['user_id'])) return false;
    try {
        $st = $pdo->prepare('SELECT * FROM users WHERE id=?');
        $st->execute([(int) $_SESSION['user_id']]);
        $u = $st->fetch(PDO::FETCH_ASSOC);
        return (bool) ($u && tcf_user_has_premium_access($u));
    } catch (Throwable $e) {
        return false;
    }
}

function eo_fetch_exam(PDO $pdo, int $examId): ?array
{
    if (tcf_schema_has_table($pdo, 'expression_orale')) {
        return tcf_exam_fetch_by_id($pdo, 'eo', $examId);
    }
    $st = $pdo->prepare("SELECT * FROM tcf_eo_exams WHERE id=? LIMIT 1");
    $st->execute([$examId]);
    $exam = $st->fetch(PDO::FETCH_ASSOC);
    if (!$exam) return null;

    $partsSt = $pdo->prepare("SELECT * FROM tcf_eo_parts WHERE exam_id=? ORDER BY sort_order ASC, id ASC");
    $partsSt->execute([$examId]);
    $parts = $partsSt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($parts as &$p) {
        $sSt = $pdo->prepare("SELECT * FROM tcf_eo_subjects WHERE part_id=? ORDER BY subject_number ASC, id ASC");
        $sSt->execute([(int) $p['id']]);
        $p['subjects'] = $sSt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($p);
    $exam['parts'] = $parts;
    return $exam;
}

function eo_api_key(): string
{
    return tcf_gemini_api_key();
}

function eo_gemini_text(string $prompt, string $apiKey): ?string
{
    if ($apiKey === '') return null;
    $body = [
        'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
        'generationConfig' => ['temperature' => 0.5, 'topP' => 0.9, 'maxOutputTokens' => 600],
    ];
    $err = '';
    $j = tcf_gemini_generate($body, $apiKey, $err, 20);
    if (!is_array($j)) return null;
    $txt = tcf_gemini_extract_text($j);
    $txt = trim((string) preg_replace('/^```[a-z]*\s*/i', '', $txt));
    $txt = trim((string) preg_replace('/```$/', '', $txt));
    return $txt !== '' ? $txt : null;
}

function eo_try_decode_feedback(string $raw): ?array
{
    $raw = trim($raw);
    if ($raw === '') {
        return null;
    }
    $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw) ?? $raw;
    $raw = preg_replace('/\s*```$/', '', $raw) ?? $raw;
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        return $decoded;
    }
    if (preg_match('/\{[\s\S]*\}/', $raw, $m)) {
        $decoded = json_decode($m[0], true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }
    return null;
}

function eo_sanitize_highlight_html(string $html): string
{
    $html = strip_tags($html, '<mark><span><strong><em><br><b><i>');
    $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
    $html = preg_replace('/\s(href|src|style|javascript)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
    return trim($html);
}

/**
 * @param array<string,mixed> $fb
 * @return array<string,mixed>
 */
function eo_normalize_oral_feedback(array $fb): array
{
    $level = strtoupper(trim((string) ($fb['cefr_level'] ?? 'B1')));
    if (!preg_match('/^(A1|A2|B1|B2|C1|C2)$/', $level)) {
        $level = 'B1';
    }
    $details = $fb['score_details'] ?? [];
    if (!is_array($details)) {
        $details = [];
    }
    $normDetails = [
        'linguistique' => (int) ($details['linguistique'] ?? $details['grammaire'] ?? 0),
        'pragmatique' => (int) ($details['pragmatique'] ?? $details['coherence'] ?? 0),
        'sociolinguistique' => (int) ($details['sociolinguistique'] ?? $details['registre'] ?? 0),
        'phonetique' => (int) ($details['phonetique'] ?? $details['prononciation'] ?? 0),
    ];
    foreach ($normDetails as $k => $v) {
        $normDetails[$k] = max(0, min(5, $v));
    }
    $errors = [];
    foreach ((array) ($fb['errors'] ?? []) as $err) {
        if (!is_array($err)) {
            continue;
        }
        $errors[] = [
            'type' => trim((string) ($err['type'] ?? 'langue')),
            'excerpt' => trim((string) ($err['excerpt'] ?? '')),
            'correction' => trim((string) ($err['correction'] ?? '')),
            'explanation' => trim((string) ($err['explanation'] ?? '')),
            'better' => trim((string) ($err['better'] ?? $err['should_say'] ?? '')),
        ];
    }
    $advice = [];
    foreach ((array) ($fb['advice'] ?? $fb['tips'] ?? []) as $tip) {
        $t = trim((string) $tip);
        if ($t !== '') {
            $advice[] = $t;
        }
    }
    $strengths = [];
    foreach ((array) ($fb['strengths'] ?? []) as $s) {
        $t = trim((string) $s);
        if ($t !== '') {
            $strengths[] = $t;
        }
    }
    $missing = [];
    foreach ((array) ($fb['missing_points'] ?? $fb['missing'] ?? []) as $m) {
        if (is_array($m)) {
            $title = trim((string) ($m['title'] ?? $m['point'] ?? ''));
            $detail = trim((string) ($m['detail'] ?? $m['example'] ?? $m['explanation'] ?? ''));
            if ($title !== '' || $detail !== '') {
                $missing[] = [
                    'title' => $title !== '' ? $title : 'Point manquant',
                    'detail' => $detail,
                ];
            }
            continue;
        }
        $t = trim((string) $m);
        if ($t !== '') {
            $missing[] = ['title' => $t, 'detail' => ''];
        }
    }
    $reformulations = [];
    foreach ((array) ($fb['reformulations'] ?? []) as $r) {
        if (!is_array($r)) {
            continue;
        }
        $youSaid = trim((string) ($r['you_said'] ?? $r['said'] ?? ''));
        $sayInstead = trim((string) ($r['say_instead'] ?? $r['better'] ?? $r['correction'] ?? ''));
        if ($youSaid === '' && $sayInstead === '') {
            continue;
        }
        $reformulations[] = [
            'you_said' => $youSaid,
            'say_instead' => $sayInstead,
            'why' => trim((string) ($r['why'] ?? $r['explanation'] ?? '')),
        ];
    }
    $highlight = eo_sanitize_highlight_html((string) ($fb['highlighted_html'] ?? $fb['highlighted_response'] ?? ''));
    $transcript = trim((string) ($fb['transcript'] ?? ''));
    $betterAnswer = trim((string) ($fb['better_answer'] ?? $fb['model_answer'] ?? $fb['should_have_said'] ?? ''));
    $adviceSpoken = trim((string) ($fb['advice_spoken'] ?? ''));
    if ($adviceSpoken === '') {
        $parts = ['Merci pour votre oral.'];
        if ($transcript !== '') {
            $short = mb_strlen($transcript) > 180 ? mb_substr($transcript, 0, 180) . '…' : $transcript;
            $parts[] = 'Vous avez dit notamment : ' . $short;
        }
        if ($reformulations !== []) {
            $r0 = $reformulations[0];
            if ($r0['you_said'] !== '' && $r0['say_instead'] !== '') {
                $parts[] = 'Quand vous avez dit « ' . $r0['you_said'] . ' », il valait mieux dire « ' . $r0['say_instead'] . ' ».';
            }
        }
        if ($missing !== []) {
            $parts[] = 'Il manquait surtout : ' . $missing[0]['title'] . '.';
        }
        if ($betterAnswer !== '') {
            $ba = mb_strlen($betterAnswer) > 320 ? mb_substr($betterAnswer, 0, 320) . '…' : $betterAnswer;
            $parts[] = 'Voici une version plus solide : ' . $ba;
        } elseif ($advice !== []) {
            $parts[] = implode('. ', array_slice($advice, 0, 3));
        }
        $parts[] = 'Vous n’êtes pas obligé d’utiliser tout le temps imparti : l’essentiel est la clarté, les arguments et les exemples.';
        $adviceSpoken = implode(' ', $parts);
    }
    $scoreGlobal = (int) ($fb['score_global'] ?? array_sum($normDetails));
    return [
        'cefr_level' => $level,
        'score_global' => max(0, min(20, $scoreGlobal)),
        'score_details' => $normDetails,
        'transcript' => $transcript,
        'highlighted_html' => $highlight,
        'errors' => $errors,
        'strengths' => $strengths,
        'missing_points' => $missing,
        'reformulations' => $reformulations,
        'better_answer' => $betterAnswer,
        'advice' => $advice,
        'advice_spoken' => $adviceSpoken,
        'remarks' => trim((string) ($fb['remarks'] ?? '')),
    ];
}

/**
 * @return array{ok:bool,mime?:string,bytes?:string,error?:string}
 */
function eo_read_uploaded_audio(): array
{
    if (empty($_FILES['audio']) || !is_array($_FILES['audio'])) {
        return ['ok' => false, 'error' => 'missing'];
    }
    $f = $_FILES['audio'];
    if ((int) ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'upload_error'];
    }
    $tmp = (string) ($f['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return ['ok' => false, 'error' => 'invalid'];
    }
    $size = (int) ($f['size'] ?? 0);
    if ($size <= 0 || $size > 12 * 1024 * 1024) {
        return ['ok' => false, 'error' => 'size'];
    }
    $mime = strtolower(trim((string) ($f['type'] ?? '')));
    $name = strtolower((string) ($f['name'] ?? ''));
    $allowed = [
        'audio/webm', 'audio/ogg', 'audio/wav', 'audio/x-wav', 'audio/mpeg',
        'audio/mp3', 'audio/mp4', 'audio/m4a', 'audio/x-m4a', 'audio/aac',
        'video/webm',
    ];
    if ($mime === '' || $mime === 'application/octet-stream') {
        if (str_ends_with($name, '.webm')) {
            $mime = 'audio/webm';
        } elseif (str_ends_with($name, '.ogg')) {
            $mime = 'audio/ogg';
        } elseif (str_ends_with($name, '.wav')) {
            $mime = 'audio/wav';
        } elseif (str_ends_with($name, '.mp3')) {
            $mime = 'audio/mpeg';
        } elseif (str_ends_with($name, '.m4a')) {
            $mime = 'audio/mp4';
        } else {
            $mime = 'audio/webm';
        }
    }
    if (!in_array($mime, $allowed, true)) {
        return ['ok' => false, 'error' => 'mime'];
    }
    $bytes = file_get_contents($tmp);
    if ($bytes === false || $bytes === '') {
        return ['ok' => false, 'error' => 'read'];
    }
    if ($mime === 'video/webm') {
        $mime = 'audio/webm';
    }
    return ['ok' => true, 'mime' => $mime, 'bytes' => $bytes];
}

function eo_exam_rank_from_title(string $title): int
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

function eo_sort_exams_by_title_date(array &$rows): void
{
    usort($rows, static function (array $a, array $b): int {
        $ra = eo_exam_rank_from_title((string) ($a['title'] ?? ''));
        $rb = eo_exam_rank_from_title((string) ($b['title'] ?? ''));
        if ($ra !== $rb) return $rb <=> $ra;
        return (int) ($b['id'] ?? 0) <=> (int) ($a['id'] ?? 0);
    });
}

function eo_sync_exam_visibility(PDO $pdo): void
{
    if (tcf_schema_has_table($pdo, 'expression_orale')) {
        $rows = $pdo->query("SELECT id, title, visibility FROM expression_orale WHERE kind='exam' AND is_published=1")->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) {
            return;
        }
        eo_sort_exams_by_title_date($rows);
        $top3Ids = [];
        foreach ($rows as $row) {
            $top3Ids[] = (int) ($row['id'] ?? 0);
            if (count($top3Ids) >= 3) {
                break;
            }
        }
        if ($top3Ids) {
            $in = implode(',', array_map('intval', $top3Ids));
            $pdo->exec("UPDATE expression_orale SET visibility='premium' WHERE kind='exam' AND is_published=1 AND id NOT IN ($in)");
            $pdo->exec("UPDATE expression_orale SET visibility='gratuit' WHERE kind='exam' AND is_published=1 AND id IN ($in) AND visibility<>'premium'");
        } else {
            $pdo->exec("UPDATE expression_orale SET visibility='premium' WHERE kind='exam' AND is_published=1");
        }
        return;
    }
    $rows = $pdo->query("SELECT id, title, visibility FROM tcf_eo_exams WHERE is_published=1")->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) {
        return;
    }
    eo_sort_exams_by_title_date($rows);
    $top3Ids = [];
    foreach ($rows as $row) {
        $top3Ids[] = (int) ($row['id'] ?? 0);
        if (count($top3Ids) >= 3) break;
    }
    if ($top3Ids) {
        $in = implode(',', array_map('intval', $top3Ids));
        $pdo->exec("UPDATE tcf_eo_exams SET visibility='premium' WHERE is_published=1 AND id NOT IN ($in)");
        $pdo->exec("UPDATE tcf_eo_exams SET visibility='gratuit' WHERE is_published=1 AND id IN ($in) AND visibility<>'premium'");
    } else {
        $pdo->exec("UPDATE tcf_eo_exams SET visibility='premium' WHERE is_published=1");
    }
}

function eo_track_exam_view(PDO $pdo, int $examId): void
{
    if (tcf_schema_has_table($pdo, 'expression_orale')) {
        $uid = !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
        $vid = '';
        if (empty($uid)) {
            if (empty($_SESSION['tcf_visitor_id'])) {
                $_SESSION['tcf_visitor_id'] = bin2hex(random_bytes(16));
            }
            $vid = (string) $_SESSION['tcf_visitor_id'];
        }
        tcf_exam_track_view($pdo, 'eo', $examId, $uid, $vid);
        return;
    }
    if ($examId <= 0) {
        return;
    }
    try {
        $uid = (int) ($_SESSION['user_id'] ?? 0);
        if ($uid > 0) {
            $st = $pdo->prepare("INSERT IGNORE INTO tcf_eo_exam_views (exam_id, user_id, visitor_id) VALUES (?, ?, ?)");
            $st->execute([$examId, $uid, '']);
        } else {
            $vid = tcf_visitor_id();
            if ($vid !== '') {
                $st = $pdo->prepare("INSERT IGNORE INTO tcf_eo_exam_views (exam_id, user_id, visitor_id) VALUES (?, 0, ?)");
                $st->execute([$examId, $vid]);
            }
        }
    } catch (Throwable $e) {
    }
}

/** @return list<array<string,mixed>> */
function eo_build_parts_content(array $parts): array
{
    $out = [];
    $sortOrder = 0;
    foreach ($parts as $i => $p) {
        $subjects = is_array($p['subjects'] ?? null) ? $p['subjects'] : [];
        if (!$subjects) {
            continue;
        }
        $taskKey = (string) ($p['task_key'] ?? 'tache2');
        if (!in_array($taskKey, ['tache1', 'tache2', 'tache3'], true)) {
            $taskKey = 'tache2';
        }
        $sortOrder++;
        $subOut = [];
        foreach ($subjects as $j => $s) {
            $subOut[] = [
                'id' => (int) ($s['id'] ?? ($i + 1) * 10 + $j + 1),
                'subject_number' => $j + 1,
                'title' => trim((string) ($s['title'] ?? '')),
                'prompt' => trim((string) ($s['prompt'] ?? '')),
                'correction' => trim((string) ($s['correction'] ?? '')) ?: null,
                'role_label' => trim((string) ($s['role_label'] ?? '')) ?: null,
                'icon_class' => trim((string) ($s['icon_class'] ?? '')) ?: 'bx bx-message-detail',
            ];
        }
        $out[] = [
            'id' => (int) ($p['id'] ?? $i + 1),
            'task_key' => $taskKey,
            'part_number' => max(1, (int) ($p['part_number'] ?? ($i + 1))),
            'part_title' => trim((string) ($p['part_title'] ?? '')) ?: null,
            'sort_order' => $sortOrder,
            'subjects' => $subOut,
        ];
    }
    return $out;
}

eo_ensure_tables($pdo);
eo_seed_default_consignes($pdo);

$action = (string) ($_POST['action'] ?? '');
if ($action === '') eo_json(['success' => false, 'message' => 'Action manquante.'], 400);

try {
    switch ($action) {
        case 'get_exams_public': {
            eo_sync_exam_visibility($pdo);
            if (tcf_schema_has_table($pdo, 'expression_orale')) {
                $rows = tcf_exam_list($pdo, 'eo', true);
                foreach ($rows as &$r) {
                    $full = tcf_exam_fetch_by_id($pdo, 'eo', (int) ($r['id'] ?? 0));
                    $r['part_count'] = count($full['parts'] ?? []);
                }
                unset($r);
            } else {
                $st = $pdo->query(
                    "SELECT e.id, e.title, e.subtitle, e.visibility, e.published_at, COUNT(DISTINCT p.id) AS part_count
                     FROM tcf_eo_exams e
                     LEFT JOIN tcf_eo_parts p ON p.exam_id = e.id
                     WHERE e.is_published=1
                     GROUP BY e.id
                     ORDER BY e.published_at DESC, e.id DESC"
                );
                $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            }
            eo_sort_exams_by_title_date($rows);
            eo_json(['success' => true, 'data' => $rows]);
        }
        case 'get_exam_public': {
            $examId = (int) ($_POST['exam_id'] ?? 0);
            if ($examId <= 0) eo_json(['success' => false, 'message' => 'Épreuve invalide.'], 422);
            eo_sync_exam_visibility($pdo);
            if (tcf_schema_has_table($pdo, 'expression_orale')) {
                $row = tcf_exam_fetch_by_id($pdo, 'eo', $examId);
                if (!$row || empty($row['is_published'])) {
                    eo_json(['success' => false, 'message' => 'Épreuve indisponible.'], 404);
                }
            } else {
                $st = $pdo->prepare("SELECT id, visibility FROM tcf_eo_exams WHERE id=? AND is_published=1");
                $st->execute([$examId]);
                $row = $st->fetch(PDO::FETCH_ASSOC);
                if (!$row) eo_json(['success' => false, 'message' => 'Épreuve indisponible.'], 404);
            }
            if ((string) ($row['visibility'] ?? 'gratuit') === 'premium') {
                if (!isset($_SESSION['user_id']) || (int) $_SESSION['user_id'] <= 0) {
                    eo_json(['success' => false, 'locked' => true, 'reason' => 'login', 'message' => 'Connectez-vous pour accéder à cette épreuve.'], 403);
                }
                $stmtU = $pdo->prepare("SELECT * FROM users WHERE id=?");
                $stmtU->execute([(int) $_SESSION['user_id']]);
                $viewer = $stmtU->fetch(PDO::FETCH_ASSOC) ?: null;
                if (!$viewer || !tcf_user_has_premium_access($viewer)) {
                    eo_json(['success' => false, 'locked' => true, 'reason' => 'subscription', 'message' => 'Abonnement requis pour accéder à cette épreuve.'], 403);
                }
            }
            eo_track_exam_view($pdo, $examId);
            $exam = eo_fetch_exam($pdo, $examId);
            eo_json(['success' => true, 'data' => $exam]);
        }
        case 'get_exams_admin': {
            if (!eo_is_admin()) eo_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            eo_sync_exam_visibility($pdo);
            if (tcf_schema_has_table($pdo, 'expression_orale')) {
                $rows = tcf_exam_list($pdo, 'eo', false);
                foreach ($rows as &$r) {
                    $full = tcf_exam_fetch_by_id($pdo, 'eo', (int) ($r['id'] ?? 0));
                    $parts = $full['parts'] ?? [];
                    $r['part_count'] = count($parts);
                    $subjectCount = 0;
                    foreach ($parts as $p) {
                        $subjectCount += count($p['subjects'] ?? []);
                    }
                    $r['subject_count'] = $subjectCount;
                    $r['view_count'] = (int) ($r['views_count'] ?? 0);
                    $r['effective_visibility'] = (string) ($r['visibility'] ?? 'gratuit');
                }
                unset($r);
            } else {
                $st = $pdo->query(
                    "SELECT e.*, COUNT(DISTINCT p.id) AS part_count, COUNT(s.id) AS subject_count
                     FROM tcf_eo_exams e
                     LEFT JOIN tcf_eo_parts p ON p.exam_id=e.id
                     LEFT JOIN tcf_eo_subjects s ON s.part_id=p.id
                     GROUP BY e.id
                     ORDER BY e.created_at DESC, e.id DESC"
                );
                $rows = $st->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as &$r) {
                    $r['effective_visibility'] = (string) ($r['visibility'] ?? 'gratuit');
                    $sv = $pdo->prepare("SELECT COUNT(*) FROM tcf_eo_exam_views WHERE exam_id=?");
                    $sv->execute([(int) ($r['id'] ?? 0)]);
                    $r['view_count'] = (int) $sv->fetchColumn();
                }
                unset($r);
            }
            eo_sort_exams_by_title_date($rows);
            eo_json(['success' => true, 'data' => $rows]);
        }
        case 'get_exam_for_edit': {
            if (!eo_is_admin()) eo_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            $examId = (int) ($_POST['exam_id'] ?? 0);
            if ($examId <= 0) eo_json(['success' => false, 'message' => 'Épreuve invalide.'], 422);
            $exam = eo_fetch_exam($pdo, $examId);
            if (!$exam) eo_json(['success' => false, 'message' => 'Épreuve introuvable.'], 404);
            eo_json(['success' => true, 'data' => $exam]);
        }
        case 'export_exam_json': {
            if (!eo_is_admin()) eo_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            $examId = (int) ($_POST['exam_id'] ?? $_GET['exam_id'] ?? 0);
            if ($examId <= 0) eo_json(['success' => false, 'message' => 'Épreuve invalide.'], 422);
            $exam = eo_fetch_exam($pdo, $examId);
            if (!$exam) eo_json(['success' => false, 'message' => 'Épreuve introuvable.'], 404);
            $payload = tcf_exam_export_eo_payload($exam);
            $slug = eo_slug((string) ($exam['title'] ?? 'eo')) ?: 'eo';
            tcf_exam_send_json_download($payload, 'eo_' . $slug . '_' . $examId);
        }
        case 'save_exam': {
            if (!eo_is_admin()) eo_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            $examId = (int) ($_POST['exam_id'] ?? 0);
            $title = trim((string) ($_POST['title'] ?? ''));
            $subtitle = trim((string) ($_POST['subtitle'] ?? ''));
            $visibility = (string) ($_POST['visibility'] ?? 'gratuit');
            if (!in_array($visibility, ['gratuit', 'premium'], true)) $visibility = 'gratuit';
            $isPublished = ((string) ($_POST['is_published'] ?? '1')) === '1' ? 1 : 0;
            $partsRaw = (string) ($_POST['parts_json'] ?? '[]');
            $parts = json_decode($partsRaw, true);
            if ($title === '') eo_json(['success' => false, 'message' => "Titre obligatoire."], 422);
            if (!is_array($parts)) eo_json(['success' => false, 'message' => "Structure des parties invalide."], 422);

            $savedPartCount = 0;
            foreach ($parts as $idx => $p) {
                $subjects = is_array($p['subjects'] ?? null) ? $p['subjects'] : [];
                if (!$subjects) {
                    continue;
                }
                if (count($subjects) !== 5) {
                    eo_json(['success' => false, 'message' => "Chaque tâche publiée doit contenir exactement 5 sujets (ligne " . ($idx + 1) . ")."], 422);
                }
                foreach ($subjects as $s) {
                    if (trim((string) ($s['title'] ?? '')) === '' || trim((string) ($s['prompt'] ?? '')) === '') {
                        eo_json(['success' => false, 'message' => "Chaque sujet doit avoir un titre et un contenu."], 422);
                    }
                }
                $savedPartCount++;
            }
            if ($savedPartCount < 1) {
                eo_json(['success' => false, 'message' => "Ajoutez au moins une partie avec des sujets (tâche 1, 2 ou 3)."], 422);
            }

            $partsContent = eo_build_parts_content($parts);
            if (tcf_schema_has_table($pdo, 'expression_orale')) {
                $wasPublished = 0;
                $isNewExam = $examId <= 0;
                if ($examId > 0) {
                    $stWas = $pdo->prepare("SELECT is_published FROM expression_orale WHERE id=? AND kind='exam'");
                    $stWas->execute([$examId]);
                    $wasPublished = (int) $stWas->fetchColumn();
                }
                $examId = tcf_exam_save($pdo, 'eo', [
                    'slug' => eo_slug($title),
                    'title' => $title,
                    'subtitle' => $subtitle !== '' ? $subtitle : null,
                    'visibility' => $visibility,
                    'is_published' => $isPublished,
                    'duration_seconds' => 3600,
                    'created_by' => (int) ($_SESSION['user_id'] ?? 0),
                ], ['parts' => $partsContent], $examId > 0 ? $examId : null);
                eo_sync_exam_visibility($pdo);
                if ($isPublished && ($isNewExam || !$wasPublished)) {
                    tcf_notify_users_registered_before(
                        $pdo,
                        'exam',
                        'Nouvelle épreuve — Expression orale',
                        "L'épreuve « $title » est maintenant disponible.",
                        site_href('epreuve_eo.php?id=' . $examId)
                    );
                }
                eo_json(['success' => true, 'message' => 'Épreuve enregistrée.', 'exam_id' => $examId]);
            }

            $pdo->beginTransaction();
            $wasPublished = 0;
            $isNewExam = $examId <= 0;
            if ($examId > 0) {
                $stWas = $pdo->prepare('SELECT is_published FROM tcf_eo_exams WHERE id=?');
                $stWas->execute([$examId]);
                $wasPublished = (int) $stWas->fetchColumn();
                $st = $pdo->prepare("UPDATE tcf_eo_exams SET title=?, subtitle=?, visibility=?, is_published=?, published_at=IF(?=1,NOW(),published_at) WHERE id=?");
                $st->execute([$title, $subtitle !== '' ? $subtitle : null, $visibility, $isPublished, $isPublished, $examId]);
                $pdo->prepare("DELETE FROM tcf_eo_parts WHERE exam_id=?")->execute([$examId]);
            } else {
                $slug = eo_slug($title);
                $st = $pdo->prepare("INSERT INTO tcf_eo_exams (slug,title,subtitle,visibility,is_published,published_at,created_by) VALUES (?,?,?,?,?,NOW(),?)");
                $st->execute([$slug, $title, $subtitle !== '' ? $subtitle : null, $visibility, $isPublished, (int) ($_SESSION['user_id'] ?? 0)]);
                $examId = (int) $pdo->lastInsertId();
            }
            $insPart = $pdo->prepare("INSERT INTO tcf_eo_parts (exam_id,task_key,part_number,part_title,sort_order) VALUES (?,?,?,?,?)");
            $insSub = $pdo->prepare("INSERT INTO tcf_eo_subjects (part_id,subject_number,title,prompt,correction,role_label,icon_class) VALUES (?,?,?,?,?,?,?)");
            $sortOrder = 0;
            foreach ($parts as $i => $p) {
                $subjects = is_array($p['subjects'] ?? null) ? $p['subjects'] : [];
                if (!$subjects) {
                    continue;
                }
                $taskKey = (string) ($p['task_key'] ?? 'tache2');
                if (!in_array($taskKey, ['tache1', 'tache2', 'tache3'], true)) {
                    $taskKey = 'tache2';
                }
                $partNumber = max(1, (int) ($p['part_number'] ?? ($i + 1)));
                $partTitle = trim((string) ($p['part_title'] ?? ''));
                $sortOrder++;
                $insPart->execute([$examId, $taskKey, $partNumber, $partTitle !== '' ? $partTitle : null, $sortOrder]);
                $partId = (int) $pdo->lastInsertId();
                foreach ($subjects as $j => $s) {
                    $correction = trim((string) ($s['correction'] ?? ''));
                    $insSub->execute([
                        $partId,
                        $j + 1,
                        trim((string) ($s['title'] ?? '')),
                        trim((string) ($s['prompt'] ?? '')),
                        $correction !== '' ? $correction : null,
                        trim((string) ($s['role_label'] ?? '')) ?: null,
                        trim((string) ($s['icon_class'] ?? '')) ?: 'bx bx-message-detail',
                    ]);
                }
            }
            eo_sync_exam_visibility($pdo);
            $pdo->commit();
            if ($isPublished && ($isNewExam || !$wasPublished)) {
                tcf_notify_users_registered_before(
                    $pdo,
                    'exam',
                    'Nouvelle épreuve — Expression orale',
                    "L'épreuve « $title » est maintenant disponible.",
                    site_href('epreuve_eo.php?id=' . $examId)
                );
            }
            eo_json(['success' => true, 'message' => 'Épreuve enregistrée.', 'exam_id' => $examId]);
        }
        case 'delete_exam': {
            if (!eo_is_admin()) eo_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            if (!tcf_is_super_admin()) {
                eo_json(['success' => false, 'message' => 'Seul le super administrateur peut supprimer une épreuve.'], 403);
            }
            $examId = (int) ($_POST['exam_id'] ?? 0);
            if ($examId <= 0) eo_json(['success' => false, 'message' => 'Épreuve invalide.'], 422);
            if (tcf_schema_has_table($pdo, 'expression_orale')) {
                tcf_exam_delete($pdo, 'eo', $examId);
            } else {
                $pdo->prepare("DELETE FROM tcf_eo_exams WHERE id=?")->execute([$examId]);
            }
            tcf_delete_notifications_matching($pdo, 'epreuve_eo.php?id=' . $examId);
            eo_json(['success' => true, 'message' => 'Épreuve supprimée.']);
        }
        case 'get_consignes': {
            if (tcf_schema_has_table($pdo, 'expression_orale')) {
                $canPremium = eo_can_view_premium_consigne($pdo);
                $taskKeys = ['tache1', 'tache2', 'tache3'];
                $rows = array_values(array_filter(
                    tcf_exam_list_consignes($pdo, 'eo'),
                    static function (array $row) use ($taskKeys, $canPremium): bool {
                        $key = (string) ($row['section_key'] ?? '');
                        if (!in_array($key, $taskKeys, true) || empty($row['is_published'])) {
                            return false;
                        }
                        return $canPremium || (string) ($row['visibility'] ?? 'gratuit') === 'gratuit';
                    }
                ));
                foreach ($rows as &$row) {
                    $row['task_key'] = (string) ($row['section_key'] ?? '');
                    $row['body'] = tcf_normalize_rich((string) ($row['body'] ?? ''));
                }
                unset($row);
            } else {
                eo_seed_default_consignes($pdo);
                $canPremium = eo_can_view_premium_consigne($pdo);
                if ($canPremium) {
                    $st = $pdo->query("SELECT id,title,body,task_key,visibility,is_published,sort_order FROM tcf_eo_consignes WHERE is_published=1 AND task_key IN ('tache1','tache2','tache3') ORDER BY task_key ASC, sort_order ASC, id ASC");
                } else {
                    $st = $pdo->query("SELECT id,title,body,task_key,visibility,is_published,sort_order FROM tcf_eo_consignes WHERE is_published=1 AND visibility='gratuit' AND task_key IN ('tache1','tache2','tache3') ORDER BY task_key ASC, sort_order ASC, id ASC");
                }
                $rows = $st->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as &$row) {
                    $row['body'] = tcf_normalize_rich((string) ($row['body'] ?? ''));
                }
                unset($row);
            }
            eo_json(['success' => true, 'data' => $rows]);
        }
        case 'get_consignes_bundle_admin': {
            if (!eo_is_admin()) eo_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            $out = ['tache1' => '', 'tache2' => '', 'tache3' => '', 'is_published' => 1];
            if (tcf_schema_has_table($pdo, 'expression_orale')) {
                foreach (tcf_exam_list_consignes($pdo, 'eo') as $row) {
                    $k = (string) ($row['section_key'] ?? '');
                    if (isset($out[$k])) {
                        $out[$k] = (string) ($row['body'] ?? '');
                        $out['is_published'] = (int) ($row['is_published'] ?? 1);
                    }
                }
            } else {
                eo_seed_default_consignes($pdo);
                $st = $pdo->prepare("SELECT body,is_published FROM tcf_eo_consignes WHERE task_key=? ORDER BY sort_order ASC,id ASC LIMIT 1");
                foreach (['tache1', 'tache2', 'tache3'] as $k) {
                    $st->execute([$k]);
                    $r = $st->fetch(PDO::FETCH_ASSOC);
                    if ($r) {
                        $out[$k] = (string) ($r['body'] ?? '');
                        $out['is_published'] = (int) ($r['is_published'] ?? 1);
                    }
                }
            }
            eo_json(['success' => true, 'data' => $out]);
        }
        case 'save_consignes_bundle': {
            if (!eo_is_admin()) eo_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            $t1 = trim((string) ($_POST['tache1'] ?? ''));
            $t2 = trim((string) ($_POST['tache2'] ?? ''));
            $t3 = trim((string) ($_POST['tache3'] ?? ''));
            $isPublished = ((string) ($_POST['is_published'] ?? '1')) === '1' ? 1 : 0;
            if ($t1 === '' || $t2 === '' || $t3 === '') {
                eo_json(['success' => false, 'message' => 'Veuillez renseigner les consignes des 3 tâches.'], 422);
            }
            if (tcf_schema_has_table($pdo, 'expression_orale')) {
                $pdo->exec("DELETE FROM expression_orale WHERE kind='consigne' AND section_key IN ('tache1','tache2','tache3')");
                $ins = $pdo->prepare(
                    "INSERT INTO expression_orale (kind, title, section_key, visibility, is_published, content_json, created_at, updated_at)
                     VALUES ('consigne',?,?,?,?,?,NOW(),NOW())"
                );
                $ins->execute(['Tâche 1 : Présentation (entretien dirigé)', 'tache1', 'gratuit', $isPublished, json_encode(['body' => $t1, 'sort_order' => 1], JSON_UNESCAPED_UNICODE)]);
                $ins->execute(['Tâche 2 : Exercice en interaction', 'tache2', 'gratuit', $isPublished, json_encode(['body' => $t2, 'sort_order' => 2], JSON_UNESCAPED_UNICODE)]);
                $ins->execute(['Tâche 3 : Expression d’un point de vue', 'tache3', 'gratuit', $isPublished, json_encode(['body' => $t3, 'sort_order' => 3], JSON_UNESCAPED_UNICODE)]);
            } else {
                $pdo->beginTransaction();
                $pdo->exec("DELETE FROM tcf_eo_consignes WHERE task_key IN ('tache1','tache2','tache3')");
                $ins = $pdo->prepare("INSERT INTO tcf_eo_consignes (title,body,task_key,visibility,is_published,sort_order,is_active) VALUES (?,?,?,'gratuit',?,?,1)");
                $ins->execute(['Tâche 1 : Présentation (entretien dirigé)', $t1, 'tache1', $isPublished, 1]);
                $ins->execute(['Tâche 2 : Exercice en interaction', $t2, 'tache2', $isPublished, 2]);
                $ins->execute(['Tâche 3 : Expression d’un point de vue', $t3, 'tache3', $isPublished, 3]);
                $pdo->commit();
            }
            eo_json(['success' => true, 'message' => $isPublished ? 'Consignes publiées.' : 'Consignes enregistrées en brouillon.']);
        }
        case 'get_simulator_subject': {
            if (empty($_SESSION['user_id'])) {
                eo_json(['success' => false, 'reason' => 'login', 'message' => 'Connectez-vous pour utiliser le simulateur.'], 401);
            }
            $taskKey = (string) ($_POST['task_key'] ?? 'tache2');
            if (!in_array($taskKey, ['tache1', 'tache2', 'tache3'], true)) $taskKey = 'tache2';
            $consigne = '';
            if (tcf_schema_has_table($pdo, 'expression_orale')) {
                foreach (tcf_exam_list_consignes($pdo, 'eo') as $row) {
                    if ((string) ($row['section_key'] ?? '') === $taskKey && !empty($row['is_published'])) {
                        $consigne = (string) ($row['body'] ?? '');
                        break;
                    }
                }
                $candidates = [];
                foreach (tcf_exam_list($pdo, 'eo', true) as $examRow) {
                    $full = tcf_exam_fetch_by_id($pdo, 'eo', (int) ($examRow['id'] ?? 0));
                    foreach ($full['parts'] ?? [] as $part) {
                        if ((string) ($part['task_key'] ?? '') !== $taskKey) {
                            continue;
                        }
                        foreach ($part['subjects'] ?? [] as $subject) {
                            $subject['part_number'] = (int) ($part['part_number'] ?? 0);
                            $candidates[] = $subject;
                        }
                    }
                }
                $row = $candidates !== [] ? $candidates[array_rand($candidates)] : [];
            } else {
                $consigneSt = $pdo->prepare("SELECT body FROM tcf_eo_consignes WHERE is_published=1 AND task_key=? ORDER BY sort_order ASC,id ASC LIMIT 1");
                $consigneSt->execute([$taskKey]);
                $consigne = (string) ($consigneSt->fetchColumn() ?: '');
                $subjectSt = $pdo->prepare(
                    "SELECT s.title, s.prompt, s.role_label, p.part_number
                     FROM tcf_eo_subjects s
                     INNER JOIN tcf_eo_parts p ON p.id=s.part_id
                     INNER JOIN tcf_eo_exams e ON e.id=p.exam_id
                     WHERE e.is_published=1 AND p.task_key=?
                     ORDER BY RAND() LIMIT 1"
                );
                $subjectSt->execute([$taskKey]);
                $row = $subjectSt->fetch(PDO::FETCH_ASSOC) ?: [];
            }
            eo_json(['success' => true, 'data' => [
                'task_key' => $taskKey,
                'task_label' => strtoupper(str_replace('tache', 'Tâche ', $taskKey)),
                'consigne' => $consigne,
                'subject_title' => (string) ($row['title'] ?? ''),
                'subject' => (string) ($row['prompt'] ?? ''),
                'role_label' => (string) ($row['role_label'] ?? ''),
                'part_number' => (int) ($row['part_number'] ?? 0),
            ]]);
        }
        case 'simulator_reply': {
            if (empty($_SESSION['user_id'])) {
                eo_json(['success' => false, 'reason' => 'login', 'message' => 'Connectez-vous pour utiliser le simulateur.'], 401);
            }
            $taskKey = (string) ($_POST['task_key'] ?? 'tache2');
            if (!in_array($taskKey, ['tache1', 'tache2', 'tache3'], true)) $taskKey = 'tache2';
            $msg = trim((string) ($_POST['message'] ?? ''));
            if ($msg === '') eo_json(['success' => false, 'message' => 'Message vide.'], 422);
            $subject = trim((string) ($_POST['subject'] ?? ''));
            $consigne = trim((string) ($_POST['consigne'] ?? ''));
            $historyRaw = (string) ($_POST['history_json'] ?? '[]');
            $history = json_decode($historyRaw, true);
            if (!is_array($history)) $history = [];
            $history = array_slice($history, -8);

            $context = "Tu es examinateur TCF Canada pour l'expression orale.\n"
                . "Réponds en français avec des phrases courtes et naturelles pour une conversation orale.\n"
                . "Donne: 1) réponse directe, 2) correction rapide de la langue si utile, 3) relance question.\n"
                . "Tâche: " . $taskKey . "\n"
                . "Sujet: " . $subject . "\n"
                . "Consigne: " . $consigne . "\n";
            foreach ($history as $h) {
                if (!is_array($h)) continue;
                $r = (string) ($h['role'] ?? 'user');
                $t = trim((string) ($h['text'] ?? ''));
                if ($t === '') continue;
                $context .= ($r === 'assistant' ? "Assistant: " : "Candidat: ") . $t . "\n";
            }
            $context .= "Candidat: " . $msg . "\nAssistant:";
            $reply = eo_gemini_text($context, eo_api_key());
            if (!$reply) {
                $reply = "Merci. Votre réponse est compréhensible. Essayez d'enrichir le vocabulaire et de mieux connecter vos idées. Pouvez-vous développer un exemple précis ?";
            }
            eo_json(['success' => true, 'reply' => trim($reply)]);
        }
        case 'ai_correct_oral': {
            if (empty($_SESSION['user_id'])) {
                eo_json([
                    'success' => false,
                    'reason' => 'login',
                    'message' => 'Connectez-vous pour utiliser le simulateur.',
                ], 401);
            }

            $taskKey = (string) ($_POST['task_key'] ?? 'tache2');
            if (!in_array($taskKey, ['tache1', 'tache2', 'tache3'], true)) {
                $taskKey = 'tache2';
            }
            $subjectTitle = trim((string) ($_POST['subject_title'] ?? ''));
            $subjectPrompt = trim((string) ($_POST['subject_prompt'] ?? ''));
            $roleLabel = trim((string) ($_POST['role_label'] ?? ''));
            $clientTranscript = trim((string) ($_POST['transcript'] ?? ''));
            $durationSec = max(0, (int) ($_POST['duration_sec'] ?? 0));

            $audio = eo_read_uploaded_audio();
            $hasAudio = !empty($audio['ok']);
            if (!$hasAudio && mb_strlen($clientTranscript) < 12) {
                eo_json([
                    'success' => false,
                    'message' => 'Enregistrez votre oral avant de terminer.',
                ], 422);
            }

            $apiKey = eo_api_key();
            if ($apiKey === '') {
                eo_json(['success' => false, 'message' => 'Service de correction indisponible.'], 500);
            }

            $taskLabels = [
                'tache1' => 'Tâche 1 — Présentation personnelle (entretien dirigé, ~2 min)',
                'tache2' => 'Tâche 2 — Interaction orale (prép. 2 min, ~3 min 30)',
                'tache3' => 'Tâche 3 — Expression d’un point de vue (~4 min 30)',
            ];
            $taskLabel = $taskLabels[$taskKey];

            $systemPrompt = "Tu es un examinateur-coach professionnel du TCF Canada (expression orale), style simulateur d’examen. "
                . "Tu corriges VOCALement : advice_spoken est le cœur du produit (monologue de coach fluide, 18–30 phrases). "
                . "L’écrit sert seulement d’appui minimal. "
                . "Dans advice_spoken : cite parfois un COURT fragment du candidat (« vous avez dit : … ») puis enchaîne immédiatement « il fallait plutôt… ». "
                . "Ne relis JAMAIS toute la production du candidat. Citation = 3 à 8 mots max. "
                . "Couvre comme un vrai pro : hésitations (euh), silences trop longs, rythme, débit trop rapide/lent, "
                . "répétitions, manque de connecteurs, arguments faibles, exemples absents ou trop vagues, "
                . "registre, prononciation/liaisons si audible, structure (intro / développement / conclusion), "
                . "et ce qu’un examinateur attend vraiment sur cette tâche. "
                . "Propose des arguments et exemples concrets à dire. "
                . "Durée max indicative : ne sanctionne PAS un oral plus court s’il est clair et riche. "
                . "better_answer = version orale modèle courte (à faire lire à voix haute aussi). "
                . "highlighted_html peut rester court. Pas de mention d’IA. "
                . "Réponds UNIQUEMENT en JSON strict : "
                . '{"cefr_level":"B1","score_global":12,"score_details":{"linguistique":3,"pragmatique":3,"sociolinguistique":3,"phonetique":3},'
                . '"transcript":"...","highlighted_html":"...",'
                . '"errors":[{"type":"hésitation","excerpt":"euh…","correction":"...","better":"...","explanation":"..."}],'
                . '"reformulations":[{"you_said":"court extrait","say_instead":"...","why":"..."}],'
                . '"missing_points":[{"title":"Argument / exemple","detail":"..."}],'
                . '"better_answer":"...","strengths":["..."],"advice":["..."],'
                . '"advice_spoken":"...","remarks":"..."}';

            $userText = "ÉPREUVE : Expression orale TCF Canada\n"
                . "TÂCHE : {$taskLabel}\n"
                . "DURÉE ENREGISTRÉE (secondes) : {$durationSec} (indicatif seulement — ne pas sanctionner si plus court)\n"
                . "TITRE DU SUJET : " . ($subjectTitle !== '' ? $subjectTitle : '(présentation)') . "\n"
                . "RÔLE : " . ($roleLabel !== '' ? $roleLabel : 'Candidat') . "\n"
                . "ÉNONCÉ / CONSIGNE :\n" . ($subjectPrompt !== '' ? $subjectPrompt : 'Présentez-vous.') . "\n\n";
            if ($clientTranscript !== '') {
                $userText .= "TRANSCRIPTION PARTIELLE DU CANDIDAT :\n" . $clientTranscript . "\n\n";
            }
            if ($hasAudio) {
                $userText .= "Audio joint : transcris fidèlement, puis corrige en citant ses phrases exactes et en proposant la version attendue + arguments/exemples.\n";
            } else {
                $userText .= "Pas d'audio : base-toi sur la transcription. Phonétique = 0 si non évaluable.\n";
            }

            $parts = [['text' => $systemPrompt . "\n\n" . $userText]];
            if ($hasAudio) {
                $parts[] = [
                    'inline_data' => [
                        'mime_type' => (string) $audio['mime'],
                        'data' => base64_encode((string) $audio['bytes']),
                    ],
                ];
            }

            $body = [
                'contents' => [
                    ['role' => 'user', 'parts' => $parts],
                ],
                'generationConfig' => [
                    'temperature' => 0.3,
                    'topP' => 0.9,
                    'maxOutputTokens' => 6144,
                ],
            ];

            $geminiErr = '';
            $decoded = tcf_gemini_generate($body, $apiKey, $geminiErr, 100);
            if (!$decoded && $hasAudio) {
                // Certains modèles refusent l'audio : retenter en texte seul.
                $textOnlyParts = [[
                    'text' => $systemPrompt . "\n\n" . $userText
                        . "\n(Note: l'audio n'a pas pu être analysé ; base-toi sur la transcription.)\n"
                        . ($clientTranscript !== '' ? '' : "Transcription indisponible — fournis une évaluation prudente.\n"),
                ]];
                $bodyText = [
                    'contents' => [['role' => 'user', 'parts' => $textOnlyParts]],
                    'generationConfig' => [
                        'temperature' => 0.3,
                        'topP' => 0.9,
                        'maxOutputTokens' => 6144,
                    ],
                ];
                $geminiErr2 = '';
                $decoded = tcf_gemini_generate($bodyText, $apiKey, $geminiErr2, 70);
                if ($decoded) {
                    $geminiErr = '';
                } elseif ($geminiErr2 !== '') {
                    $geminiErr = $geminiErr2;
                }
            }
            if (!$decoded) {
                eo_json([
                    'success' => false,
                    'message' => $geminiErr !== '' ? $geminiErr : 'Correction temporairement indisponible.',
                ], 502);
            }

            $rawText = tcf_gemini_extract_text($decoded);
            $feedback = eo_try_decode_feedback($rawText);
            if (!is_array($feedback)) {
                $repair = eo_gemini_text(
                    "Convertis le texte suivant en JSON strict avec les clés cefr_level, score_global, score_details, "
                    . "transcript, highlighted_html, errors, reformulations, missing_points, better_answer, "
                    . "strengths, advice, advice_spoken, remarks.\nTexte:\n" . $rawText,
                    $apiKey
                );
                $feedback = is_string($repair) ? eo_try_decode_feedback($repair) : null;
            }
            if (!is_array($feedback)) {
                $fallbackTranscript = $clientTranscript !== '' ? $clientTranscript : 'Production orale reçue.';
                $feedback = [
                    'cefr_level' => 'B1',
                    'score_global' => 10,
                    'score_details' => [
                        'linguistique' => 2,
                        'pragmatique' => 3,
                        'sociolinguistique' => 2,
                        'phonetique' => $hasAudio ? 2 : 0,
                    ],
                    'transcript' => $fallbackTranscript,
                    'highlighted_html' => htmlspecialchars($fallbackTranscript, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                    'errors' => [[
                        'type' => 'général',
                        'excerpt' => '',
                        'correction' => '',
                        'better' => '',
                        'explanation' => 'La correction est temporairement limitée. Réessayez.',
                    ]],
                    'reformulations' => [],
                    'missing_points' => [[
                        'title' => 'Développer un exemple concret',
                        'detail' => 'Ajoutez un exemple précis lié au sujet pour renforcer votre point de vue.',
                    ]],
                    'better_answer' => '',
                    'strengths' => ['Vous avez produit une réponse orale liée au sujet.'],
                    'advice' => [
                        'Citez un argument clair puis un exemple.',
                        'Variez les connecteurs (d’abord, ensuite, en revanche, par conséquent).',
                    ],
                    'advice_spoken' => 'Merci pour votre oral. Reprenez vos idées une par une, ajoutez un exemple concret, et reformulez les phrases hésitantes plus clairement. Le temps total n’est pas une obligation : visez la qualité.',
                    'remarks' => 'Correction de secours : réessayez dans un instant.',
                ];
            }

            $feedback = eo_normalize_oral_feedback($feedback);
            if ($feedback['transcript'] === '' && $clientTranscript !== '') {
                $feedback['transcript'] = $clientTranscript;
            }
            if ($feedback['highlighted_html'] === '' && $feedback['transcript'] !== '') {
                $feedback['highlighted_html'] = htmlspecialchars($feedback['transcript'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }

            eo_json([
                'success' => true,
                'feedback' => $feedback,
                'task_key' => $taskKey,
                'duration_sec' => $durationSec,
                'had_audio' => $hasAudio,
            ]);
        }
        default:
            eo_json(['success' => false, 'message' => 'Action non reconnue.'], 400);
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    eo_json(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()], 500);
}

