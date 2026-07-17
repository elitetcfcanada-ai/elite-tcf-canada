<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';

$slug = $argv[1] ?? 'eo-expression-orale-aout-2025';

$st = $pdo->prepare('SELECT id, title, subtitle FROM tcf_eo_exams WHERE slug = ?');
$st->execute([$slug]);
$exam = $st->fetch(PDO::FETCH_ASSOC);
if (!$exam) {
    echo "Épreuve introuvable: $slug\n";
    exit(1);
}

$examId = (int) $exam['id'];
$parts = $pdo->prepare(
    'SELECT p.id AS part_id, p.task_key, p.part_number, COUNT(s.id) AS n
     FROM tcf_eo_parts p
     LEFT JOIN tcf_eo_subjects s ON s.part_id = p.id
     WHERE p.exam_id = ?
     GROUP BY p.id
     ORDER BY p.sort_order'
);
$parts->execute([$examId]);

$corr = $pdo->prepare(
    'SELECT COUNT(*) FROM tcf_eo_subjects s JOIN tcf_eo_parts p ON p.id=s.part_id WHERE p.exam_id=? AND s.correction IS NOT NULL AND TRIM(s.correction)<>""'
);
$corr->execute([$examId]);

echo "Exam #{$examId}: {$exam['title']}\n";
echo "Subtitle: {$exam['subtitle']}\n";
echo "Corrections: " . (int) $corr->fetchColumn() . "\n";
foreach ($parts->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "  {$row['task_key']} partie {$row['part_number']}: {$row['n']} sujets\n";
}
