<?php
declare(strict_types=1);

/**
 * Supprime les anciennes tables d’épreuves tcf_* après bascule complète CE/CO/EE/EO.
 * Usage : /scripts/drop_legacy_exam_tables.php?key=REPAIR_TCF_2026
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/tcf_schema.php';

if (PHP_SAPI !== 'cli' && (string) ($_GET['key'] ?? '') !== 'REPAIR_TCF_2026') {
    http_response_code(403);
    exit('Forbidden');
}
header('Content-Type: text/plain; charset=utf-8');

foreach (['comprehension_ecrite', 'comprehension_orale', 'expression_ecrite', 'expression_orale'] as $need) {
    if (!tcf_schema_has_table($pdo, $need)) {
        echo "Abort: $need manquante. Lancez d’abord migrate_consolidate_db.php\n";
        exit(1);
    }
}

$drop = [
    'tcf_ce_answers', 'tcf_ce_questions', 'tcf_ce_exam_views', 'tcf_ce_consignes', 'tcf_ce_exams',
    'tcf_co_answers', 'tcf_co_questions', 'tcf_co_exam_views', 'tcf_co_consignes', 'tcf_co_exams',
    'tcf_ee_task_documents', 'tcf_ee_tasks', 'tcf_ee_combinations', 'tcf_ee_exam_views', 'tcf_ee_consignes', 'tcf_ee_exams',
    'tcf_eo_subjects', 'tcf_eo_parts', 'tcf_eo_exam_views', 'tcf_eo_consignes', 'tcf_eo_exams',
];
$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
foreach ($drop as $t) {
    $pdo->exec('DROP TABLE IF EXISTS `' . str_replace('`', '``', $t) . '`');
    echo "DROP $t\n";
}
$pdo->exec('SET FOREIGN_KEY_CHECKS=1');
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo 'Tables restantes: ' . count($tables) . "\n";
foreach ($tables as $t) {
    echo " - $t\n";
}
echo "OK\n";
