<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';

/** Réattribue les lignes id=0 avant d'ajouter la clé primaire. */
function ee_reassign_zero_ids(PDO $pdo, string $table): int
{
    $zeros = $pdo->query("SELECT id FROM `$table` WHERE id = 0")->fetchAll(PDO::FETCH_COLUMN);
    if ($zeros === []) {
        return 0;
    }
    $max = (int) $pdo->query("SELECT COALESCE(MAX(id), 0) FROM `$table`")->fetchColumn();
    $n = 0;
    foreach ($zeros as $_) {
        $max++;
        $pdo->exec("UPDATE `$table` SET id = $max WHERE id = 0 LIMIT 1");
        $n++;
    }
    return $n;
}

$fixes = [
    'tcf_ee_exams' => 'INT UNSIGNED NOT NULL AUTO_INCREMENT',
    'tcf_ee_combinations' => 'INT UNSIGNED NOT NULL AUTO_INCREMENT',
    'tcf_ee_tasks' => 'INT UNSIGNED NOT NULL AUTO_INCREMENT',
    'tcf_ee_task_documents' => 'INT UNSIGNED NOT NULL AUTO_INCREMENT',
];

// Ordre : documents → tasks → combinations → exams (FK descendant d'abord si besoin)
foreach (['tcf_ee_task_documents', 'tcf_ee_tasks', 'tcf_ee_combinations', 'tcf_ee_exams'] as $t) {
    $n = ee_reassign_zero_ids($pdo, $t);
    if ($n > 0) {
        echo "Réattribué $n ligne(s) id=0 dans $t\n";
    }
}

foreach ($fixes as $table => $colDef) {
    $hasPk = (int) $pdo->query(
        "SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = " . $pdo->quote($table) . " AND CONSTRAINT_TYPE = 'PRIMARY KEY'"
    )->fetchColumn();
    if ($hasPk === 0) {
        $pdo->exec("ALTER TABLE `$table` ADD PRIMARY KEY (`id`)");
        echo "PK ajoutée sur $table\n";
    }
    $pdo->exec("ALTER TABLE `$table` MODIFY `id` $colDef");
    $max = (int) $pdo->query("SELECT COALESCE(MAX(id), 0) FROM `$table` WHERE id > 0")->fetchColumn();
    $next = max(1, $max + 1);
    $pdo->exec("ALTER TABLE `$table` AUTO_INCREMENT = $next");
    echo "OK $table → next id $next\n";
}

// Nettoyer l'insertion août 2025 avec id=0
$pdo->exec("DELETE FROM tcf_ee_exams WHERE slug = 'ee-expression-ecrite-aout-2025' AND id = 0");
$pdo->exec('DELETE FROM tcf_ee_combinations WHERE exam_id = 0');
echo "Nettoyage id=0 terminé.\n";
