<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';
$slug = $argv[1] ?? '';
if ($slug === '') {
    fwrite(STDERR, "Usage: php verify_ee_exam.php <slug>\n");
    exit(1);
}
$st = $pdo->prepare('SELECT id FROM tcf_ee_exams WHERE slug = ?');
$st->execute([$slug]);
$examId = (int) $st->fetchColumn();
$combos = (int) $pdo->query("SELECT COUNT(*) FROM tcf_ee_combinations WHERE exam_id = $examId")->fetchColumn();
$tasks = (int) $pdo->query("SELECT COUNT(*) FROM tcf_ee_tasks t JOIN tcf_ee_combinations c ON c.id = t.combination_id WHERE c.exam_id = $examId")->fetchColumn();
$docs = (int) $pdo->query("SELECT COUNT(*) FROM tcf_ee_task_documents d JOIN tcf_ee_tasks t ON t.id = d.task_id JOIN tcf_ee_combinations c ON c.id = t.combination_id WHERE c.exam_id = $examId")->fetchColumn();
$corr = (int) $pdo->query("SELECT COUNT(*) FROM tcf_ee_tasks t JOIN tcf_ee_combinations c ON c.id = t.combination_id WHERE c.exam_id = $examId AND t.correction IS NOT NULL AND TRIM(t.correction) != ''")->fetchColumn();
echo "exam_id=$examId combos=$combos tasks=$tasks docs=$docs with_correction=$corr\n";
