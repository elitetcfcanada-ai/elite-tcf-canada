<?php
/**
 * Importe Compréhension Écrite 3 depuis database/seeds/ce_exam_3.json
 * Usage : php scripts/seed_ce_exam_3.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';

$jsonPath = __DIR__ . '/../database/seeds/ce_exam_3.json';
if (!is_file($jsonPath)) {
    fwrite(STDERR, "Fichier manquant: $jsonPath\n");
    exit(1);
}

$raw = file_get_contents($jsonPath);
$payload = json_decode((string) $raw, true);
if (!is_array($payload)) {
    fwrite(STDERR, "JSON invalide.\n");
    exit(1);
}

$data = isset($payload['questions']) && is_array($payload['questions'])
    ? $payload['questions']
    : $payload;

if ($data === []) {
    fwrite(STDERR, "Aucune question.\n");
    exit(1);
}

$title = trim((string) ($payload['title'] ?? 'Compréhension Écrite 3'));
$subtitle = trim((string) ($payload['subtitle'] ?? 'Épreuve de compréhension écrite — 31 questions'));
$duration = (int) ($payload['duration_seconds'] ?? 3600);
$visibility = trim((string) ($payload['visibility'] ?? 'gratuit'));
if ($visibility === '') {
    $visibility = 'gratuit';
}

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

$existsId = (int) $pdo->query('SELECT id FROM tcf_ce_exams WHERE title=' . $pdo->quote($title) . ' LIMIT 1')->fetchColumn();
if ($existsId > 0) {
    echo "Déjà présent : $title (id=$existsId) — suppression puis réimport…\n";
    $pdo->prepare('DELETE FROM tcf_ce_exams WHERE id=?')->execute([$existsId]);
}

$slug = 'comprehension-ecrite-3-' . substr(uniqid('', true), -6);

$pdo->beginTransaction();
try {
    $pdo->prepare(
        'INSERT INTO tcf_ce_exams (slug,title,subtitle,intro_html,visibility,is_published,duration_seconds,published_at) VALUES (?,?,?,?,?,?,?,NOW())'
    )->execute([
        $slug,
        $title,
        $subtitle !== '' ? $subtitle : null,
        null,
        $visibility,
        1,
        $duration > 0 ? $duration : 3600,
    ]);
    $examId = (int) $pdo->lastInsertId();

    $insQ = $pdo->prepare(
        'INSERT INTO tcf_ce_questions (exam_id,sort_order,situation,question_text,points) VALUES (?,?,?,?,?)'
    );
    $insA = $pdo->prepare(
        'INSERT INTO tcf_ce_answers (question_id,answer_key,answer_text,is_correct,sort_order) VALUES (?,?,?,?,?)'
    );

    foreach (array_values($data) as $ord => $q) {
        if (!is_array($q)) {
            continue;
        }
        $sit = isset($q['situation']) ? trim((string) $q['situation']) : '';
        $txt = trim((string) ($q['text'] ?? $q['question_text'] ?? ''));
        $pts = (int) ($q['points'] ?? 3);
        if ($txt === '') {
            continue;
        }
        $insQ->execute([$examId, $ord + 1, $sit !== '' ? $sit : null, $txt, $pts]);
        $qid = (int) $pdo->lastInsertId();
        $answers = $q['answers'] ?? [];
        foreach (array_values($answers) as $i => $a) {
            if (!is_array($a)) {
                continue;
            }
            $key = strtolower(trim((string) ($a['id'] ?? '')));
            if (!preg_match('/^[a-z]$/', $key)) {
                $key = chr(ord('a') + ($i % 26));
            }
            $insA->execute([
                $qid,
                $key,
                trim((string) ($a['text'] ?? '')),
                !empty($a['correct']) ? 1 : 0,
                $i,
            ]);
        }
    }
    $pdo->commit();
    echo "OK — épreuve #$examId importée (" . count($data) . " questions) : $title\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
