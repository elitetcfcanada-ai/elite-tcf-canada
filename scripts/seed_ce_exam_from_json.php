<?php
/**
 * Import initial : crée une épreuve Compréhension écrite depuis le JSON extrait du quiz statique.
 * Usage : php scripts/seed_ce_exam_from_json.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';

$jsonPath = __DIR__ . '/../database/seeds/ce_questions_extracted.json';
if (!is_file($jsonPath)) {
    fwrite(STDERR, "Fichier manquant: $jsonPath\n");
    exit(1);
}

$raw = file_get_contents($jsonPath);
$data = json_decode($raw, true);
if (!is_array($data) || $data === []) {
    fwrite(STDERR, "JSON invalide.\n");
    exit(1);
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

$title = 'Compréhension Écrite 1';
$slug = 'comprehension-ecrite-1-' . substr(uniqid('', true), -6);
$exists = (int) $pdo->query("SELECT COUNT(*) FROM tcf_ce_exams WHERE title=" . $pdo->quote($title))->fetchColumn();
if ($exists > 0) {
    echo "Déjà présent : $title — rien à faire.\n";
    exit(0);
}

$pdo->beginTransaction();
try {
    $pdo->prepare(
        'INSERT INTO tcf_ce_exams (slug,title,subtitle,intro_html,visibility,is_published,duration_seconds,published_at) VALUES (?,?,?,?,?,?,?,NOW())'
    )->execute([
        $slug,
        $title,
        'Testez vos compétences en compréhension écrite française (quiz)',
        null,
        'gratuit',
        1,
        3600,
    ]);
    $examId = (int) $pdo->lastInsertId();

    $insQ = $pdo->prepare(
        'INSERT INTO tcf_ce_questions (exam_id,sort_order,situation,question_text,points) VALUES (?,?,?,?,?)'
    );
    $insA = $pdo->prepare(
        'INSERT INTO tcf_ce_answers (question_id,answer_key,answer_text,is_correct,sort_order) VALUES (?,?,?,?,?)'
    );

    foreach ($data as $ord => $q) {
        $sit = isset($q['situation']) ? trim((string) $q['situation']) : '';
        $txt = trim((string) ($q['text'] ?? ''));
        $pts = (int) ($q['points'] ?? 3);
        $insQ->execute([$examId, $ord + 1, $sit !== '' ? $sit : null, $txt, $pts]);
        $qid = (int) $pdo->lastInsertId();
        $answers = $q['answers'] ?? [];
        foreach ($answers as $i => $a) {
            $key = strtolower(trim((string) ($a['id'] ?? 'a')));
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
    echo "OK — épreuve #$examId importée (" . count($data) . " questions).\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
