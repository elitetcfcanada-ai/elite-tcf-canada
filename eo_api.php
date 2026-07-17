<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/subscription_access.php';

header('Content-Type: application/json; charset=utf-8');

function eo_json(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function eo_is_admin(): bool
{
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'super_admin'], true);
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
    $count = (int) $pdo->query("SELECT COUNT(*) FROM tcf_eo_consignes WHERE task_key IN ('tache2','tache3')")->fetchColumn();
    if ($count > 0) return;
    $rows = [
        ['Tâche 2', "Interaction : obtenir des informations dans une situation de la vie courante.", 'tache2', 2],
        ['Tâche 3', "Point de vue : exprimer et défendre une opinion de façon structurée.", 'tache3', 3],
    ];
    $ins = $pdo->prepare("INSERT INTO tcf_eo_consignes (title, body, task_key, visibility, is_published, sort_order, is_active) VALUES (?, ?, ?, 'gratuit', 1, ?, 1)");
    foreach ($rows as $r) {
        $ins->execute([$r[0], $r[1], $r[2], $r[3]]);
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
    $k = '';
    $f = __DIR__ . '/includes/gemini_key.php';
    if (is_file($f)) {
        $v = include $f;
        if (is_string($v) && trim($v) !== '') $k = trim($v);
    }
    if ($k === '') $k = trim((string) getenv('GEMINI_API_KEY'));
    return $k;
}

function eo_gemini_text(string $prompt, string $apiKey): ?string
{
    if ($apiKey === '') return null;
    $body = [
        'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
        'generationConfig' => ['temperature' => 0.5, 'topP' => 0.9, 'maxOutputTokens' => 600],
    ];
    $models = ['gemini-2.5-flash', 'gemini-2.0-flash-001', 'gemini-2.0-flash', 'gemini-flash-latest'];
    foreach ($models as $m) {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($m) . ':generateContent?key=' . rawurlencode($apiKey);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 20,
        ]);
        $resp = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($resp === false || $code >= 400) continue;
        $j = json_decode($resp, true);
        if (!is_array($j)) continue;
        $txt = '';
        foreach (($j['candidates'][0]['content']['parts'] ?? []) as $p) $txt .= trim((string) ($p['text'] ?? ''));
        $txt = trim((string) preg_replace('/^```[a-z]*\s*/i', '', $txt));
        $txt = trim((string) preg_replace('/```$/', '', $txt));
        if ($txt !== '') return $txt;
    }
    return null;
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

eo_ensure_tables($pdo);
eo_seed_default_consignes($pdo);

$action = (string) ($_POST['action'] ?? '');
if ($action === '') eo_json(['success' => false, 'message' => 'Action manquante.'], 400);

try {
    switch ($action) {
        case 'get_exams_public': {
            eo_sync_exam_visibility($pdo);
            $st = $pdo->query(
                "SELECT e.id, e.title, e.subtitle, e.visibility, e.published_at, COUNT(DISTINCT p.id) AS part_count
                 FROM tcf_eo_exams e
                 LEFT JOIN tcf_eo_parts p ON p.exam_id = e.id
                 WHERE e.is_published=1
                 GROUP BY e.id
                 ORDER BY e.published_at DESC, e.id DESC"
            );
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            eo_sort_exams_by_title_date($rows);
            eo_json(['success' => true, 'data' => $rows]);
        }
        case 'get_exam_public': {
            $examId = (int) ($_POST['exam_id'] ?? 0);
            if ($examId <= 0) eo_json(['success' => false, 'message' => 'Épreuve invalide.'], 422);
            eo_sync_exam_visibility($pdo);
            $st = $pdo->prepare("SELECT id, visibility FROM tcf_eo_exams WHERE id=? AND is_published=1");
            $st->execute([$examId]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row) eo_json(['success' => false, 'message' => 'Épreuve indisponible.'], 404);
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
            $st = $pdo->query(
                "SELECT e.*, COUNT(DISTINCT p.id) AS part_count, COUNT(s.id) AS subject_count
                 FROM tcf_eo_exams e
                 LEFT JOIN tcf_eo_parts p ON p.exam_id=e.id
                 LEFT JOIN tcf_eo_subjects s ON s.part_id=p.id
                 GROUP BY e.id
                 ORDER BY e.created_at DESC, e.id DESC"
            );
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            eo_sort_exams_by_title_date($rows);
            foreach ($rows as &$r) {
                $r['effective_visibility'] = (string) ($r['visibility'] ?? 'gratuit');
                $sv = $pdo->prepare("SELECT COUNT(*) FROM tcf_eo_exam_views WHERE exam_id=?");
                $sv->execute([(int) ($r['id'] ?? 0)]);
                $r['view_count'] = (int) $sv->fetchColumn();
            }
            unset($r);
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

            $pdo->beginTransaction();
            if ($examId > 0) {
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
            eo_json(['success' => true, 'message' => 'Épreuve enregistrée.', 'exam_id' => $examId]);
        }
        case 'delete_exam': {
            if (!eo_is_admin()) eo_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            $examId = (int) ($_POST['exam_id'] ?? 0);
            if ($examId <= 0) eo_json(['success' => false, 'message' => 'Épreuve invalide.'], 422);
            $pdo->prepare("DELETE FROM tcf_eo_exams WHERE id=?")->execute([$examId]);
            eo_json(['success' => true, 'message' => 'Épreuve supprimée.']);
        }
        case 'get_consignes': {
            $canPremium = eo_can_view_premium_consigne($pdo);
            if ($canPremium) {
                $st = $pdo->query("SELECT id,title,body,task_key,visibility,is_published,sort_order FROM tcf_eo_consignes WHERE is_published=1 AND task_key IN ('tache2','tache3') ORDER BY task_key ASC, sort_order ASC, id ASC");
            } else {
                $st = $pdo->query("SELECT id,title,body,task_key,visibility,is_published,sort_order FROM tcf_eo_consignes WHERE is_published=1 AND visibility='gratuit' AND task_key IN ('tache2','tache3') ORDER BY task_key ASC, sort_order ASC, id ASC");
            }
            eo_json(['success' => true, 'data' => $st->fetchAll(PDO::FETCH_ASSOC)]);
        }
        case 'get_consignes_bundle_admin': {
            if (!eo_is_admin()) eo_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            $out = ['tache2' => '', 'tache3' => '', 'is_published' => 1];
            $st = $pdo->prepare("SELECT body,is_published FROM tcf_eo_consignes WHERE task_key=? ORDER BY sort_order ASC,id ASC LIMIT 1");
            foreach (['tache2', 'tache3'] as $k) {
                $st->execute([$k]);
                $r = $st->fetch(PDO::FETCH_ASSOC);
                if ($r) {
                    $out[$k] = (string) ($r['body'] ?? '');
                    $out['is_published'] = (int) ($r['is_published'] ?? 1);
                }
            }
            eo_json(['success' => true, 'data' => $out]);
        }
        case 'save_consignes_bundle': {
            if (!eo_is_admin()) eo_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            $t2 = trim((string) ($_POST['tache2'] ?? ''));
            $t3 = trim((string) ($_POST['tache3'] ?? ''));
            $isPublished = ((string) ($_POST['is_published'] ?? '1')) === '1' ? 1 : 0;
            if ($t2 === '' || $t3 === '') eo_json(['success' => false, 'message' => 'Veuillez renseigner les consignes des tâches 2 et 3.'], 422);
            $pdo->beginTransaction();
            $pdo->exec("DELETE FROM tcf_eo_consignes WHERE task_key IN ('tache1','tache2','tache3')");
            $ins = $pdo->prepare("INSERT INTO tcf_eo_consignes (title,body,task_key,visibility,is_published,sort_order,is_active) VALUES (?,?,?,'gratuit',?,?,1)");
            $ins->execute(['Tâche 2', $t2, 'tache2', $isPublished, 2]);
            $ins->execute(['Tâche 3', $t3, 'tache3', $isPublished, 3]);
            $pdo->commit();
            eo_json(['success' => true, 'message' => $isPublished ? 'Consignes publiées.' : 'Consignes enregistrées en brouillon.']);
        }
        case 'get_simulator_subject': {
            $taskKey = (string) ($_POST['task_key'] ?? 'tache2');
            if (!in_array($taskKey, ['tache1', 'tache2', 'tache3'], true)) $taskKey = 'tache2';
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
        default:
            eo_json(['success' => false, 'message' => 'Action non reconnue.'], 400);
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    eo_json(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()], 500);
}

