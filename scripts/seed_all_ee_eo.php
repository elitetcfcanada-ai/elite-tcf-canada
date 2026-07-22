<?php
/**
 * Importe tous les sujets Expression Écrite + Expression Orale depuis database/seeds/.
 *
 * CLI :  php scripts/seed_all_ee_eo.php
 * Web :  scripts/seed_all_ee_eo.php?key=REPAIR_TCF_2026
 */
declare(strict_types=1);

$isCli = PHP_SAPI === 'cli';
if (!$isCli) {
    $key = (string) ($_GET['key'] ?? '');
    if ($key !== 'REPAIR_TCF_2026') {
        http_response_code(403);
        echo "Accès refusé.\n";
        exit;
    }
    header('Content-Type: text/html; charset=utf-8');
    echo '<h1>Import sujets EE / EO</h1><pre>';
}

require_once dirname(__DIR__) . '/includes/config.php';

function seed_run_script(string $path): void
{
    global $pdo;
    if (!is_file($path)) {
        echo "SKIP missing: {$path}\n";
        return;
    }
    echo "\n===== RUN " . basename($path) . " =====\n";
    require $path;
}

$root = dirname(__DIR__);

// Expression écrite
seed_run_script($root . '/scripts/import_exp_ecrite_to_db.php');

// Expression orale
seed_run_script($root . '/scripts/import_exp_orale_to_db.php');

// Compteurs
try {
    $ee = (int) $pdo->query('SELECT COUNT(*) FROM tcf_ee_exams')->fetchColumn();
    $eo = (int) $pdo->query('SELECT COUNT(*) FROM tcf_eo_exams')->fetchColumn();
    $eePub = (int) $pdo->query('SELECT COUNT(*) FROM tcf_ee_exams WHERE is_published = 1')->fetchColumn();
    $eoPub = (int) $pdo->query('SELECT COUNT(*) FROM tcf_eo_exams WHERE is_published = 1')->fetchColumn();
    echo "\n===== RESULT =====\n";
    echo "tcf_ee_exams={$ee} (published={$eePub})\n";
    echo "tcf_eo_exams={$eo} (published={$eoPub})\n";
} catch (Throwable $e) {
    echo 'COUNT ERR: ' . $e->getMessage() . "\n";
}

if (!$isCli) {
    echo "\nDONE — supprimez ce script après usage en production.\n</pre>";
}
