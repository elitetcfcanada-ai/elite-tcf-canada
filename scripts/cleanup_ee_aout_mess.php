<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Combinaisons août 2025 importées par erreur sur l'examen id=23 (403–418)
$comboIds = $pdo->query('SELECT id FROM tcf_ee_combinations WHERE id BETWEEN 403 AND 418')->fetchAll(PDO::FETCH_COLUMN);
if ($comboIds) {
    $in = implode(',', array_map('intval', $comboIds));
    $pdo->exec("DELETE d FROM tcf_ee_task_documents d INNER JOIN tcf_ee_tasks t ON t.id = d.task_id WHERE t.combination_id IN ($in)");
    $pdo->exec("DELETE FROM tcf_ee_tasks WHERE combination_id IN ($in)");
    $pdo->exec("DELETE FROM tcf_ee_combinations WHERE id IN ($in)");
    echo 'Supprimé ' . count($comboIds) . " combinaison(s) orphelines (403-418).\n";
}

// Épreuve août 2025 fantôme (id=1)
$pdo->exec("DELETE FROM tcf_ee_exams WHERE slug = 'ee-expression-ecrite-aout-2025'");
echo "Épreuve août 2025 (slug) supprimée pour réimport.\n";
