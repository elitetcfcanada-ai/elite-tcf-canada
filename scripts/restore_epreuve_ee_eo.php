<?php
/**
 * Restaure Expression Écrite + Expression Orale depuis database/epreuve.sql
 * (anciennes données propres : 1 carte = 1 mois, sans Part1/Data).
 *
 * CLI : php scripts/restore_epreuve_ee_eo.php
 * Web : scripts/restore_epreuve_ee_eo.php?key=REPAIR_TCF_2026
 */
declare(strict_types=1);

$isCli = PHP_SAPI === 'cli';
if (!$isCli) {
    if ((string) ($_GET['key'] ?? '') !== 'REPAIR_TCF_2026') {
        http_response_code(403);
        echo "Accès refusé.\n";
        exit;
    }
    header('Content-Type: text/html; charset=utf-8');
    echo '<h1>Restauration epreuve.sql (EE / EO)</h1><pre>';
}

require_once dirname(__DIR__) . '/includes/config.php';

$dumpEpreuve = dirname(__DIR__) . '/database/epreuve.sql';
$dumpSeeds = dirname(__DIR__) . '/database/seeds_ee_eo_data.sql';
$dump = is_file($dumpEpreuve) ? $dumpEpreuve : $dumpSeeds;
if (!is_file($dump)) {
    echo "Fichier introuvable: database/epreuve.sql ou database/seeds_ee_eo_data.sql\n";
    exit(1);
}
echo 'Source: ' . basename($dump) . "\n";

// Si on a le dump seeds déjà nettoyé (DELETE + INSERT), l’exécuter directement
if (basename($dump) === 'seeds_ee_eo_data.sql') {
    $sql = file_get_contents($dump);
    if ($sql === false) {
        echo "Lecture impossible.\n";
        exit(1);
    }
    try {
        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        // Exécuter par statements
        $parts = preg_split('/;\s*\n/', $sql) ?: [];
        $ok = 0;
        $err = 0;
        foreach ($parts as $stmt) {
            $stmt = trim($stmt);
            if ($stmt === '' || str_starts_with($stmt, '--') || str_starts_with($stmt, '/*')) {
                continue;
            }
            try {
                $pdo->exec($stmt);
                $ok++;
            } catch (Throwable $e) {
                $err++;
                if ($err <= 5) {
                    echo 'WARN: ' . $e->getMessage() . "\n";
                }
            }
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        $ee = (int) $pdo->query('SELECT COUNT(*) FROM tcf_ee_exams WHERE is_published=1')->fetchColumn();
        $eo = (int) $pdo->query('SELECT COUNT(*) FROM tcf_eo_exams WHERE is_published=1')->fetchColumn();
        echo "Statements OK≈{$ok} err={$err}\nEE publiés={$ee} EO publiés={$eo}\nDONE\n";
    } catch (Throwable $e) {
        echo 'FATAL: ' . $e->getMessage() . "\n";
        exit(1);
    }
    if (!$isCli) {
        echo '</pre>';
    }
    exit(0);
}

$tables = [
    // enfants d'abord pour truncate
    'tcf_ee_task_documents',
    'tcf_ee_tasks',
    'tcf_ee_combinations',
    'tcf_ee_exam_views',
    'tcf_ee_consignes',
    'tcf_ee_exams',
    'tcf_eo_subjects',
    'tcf_eo_parts',
    'tcf_eo_exam_views',
    'tcf_eo_consignes',
    'tcf_eo_exams',
];

$wanted = array_fill_keys($tables, true);

echo "Lecture de epreuve.sql…\n";
$fh = fopen($dump, 'rb');
if ($fh === false) {
    echo "Impossible d'ouvrir le dump.\n";
    exit(1);
}

$inserts = []; // table => list of full INSERT SQL
$buffer = '';
$currentTable = null;
$inInsert = false;

while (($line = fgets($fh)) !== false) {
    if (!$inInsert) {
        if (preg_match('/^INSERT INTO `(tcf_e[eo]_[a-z0-9_]+)`/i', $line, $m)) {
            $t = $m[1];
            if (!isset($wanted[$t])) {
                continue;
            }
            $inInsert = true;
            $currentTable = $t;
            $buffer = $line;
            if (str_ends_with(rtrim($line), ';')) {
                $inserts[$t][] = $buffer;
                $inInsert = false;
                $currentTable = null;
                $buffer = '';
            }
        }
        continue;
    }

    $buffer .= $line;
    if (str_ends_with(rtrim($line), ';')) {
        $inserts[$currentTable][] = $buffer;
        $inInsert = false;
        $currentTable = null;
        $buffer = '';
    }
}
fclose($fh);

foreach ($tables as $t) {
    $n = isset($inserts[$t]) ? count($inserts[$t]) : 0;
    echo "Trouvé INSERT {$t}: {$n}\n";
}

try {
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    $pdo->exec("SET SESSION sql_mode = REPLACE(@@sql_mode, 'NO_AUTO_VALUE_ON_ZERO', '')");

    // Vider dans l'ordre enfants → parents
    foreach ($tables as $t) {
        try {
            $pdo->exec("DELETE FROM `{$t}`");
            echo "VIDÉ {$t}\n";
        } catch (Throwable $e) {
            echo "SKIP truncate {$t}: " . $e->getMessage() . "\n";
        }
    }

    // Insérer parents puis enfants (ordre inverse pour les parents d'abord)
    $insertOrder = [
        'tcf_ee_exams',
        'tcf_ee_combinations',
        'tcf_ee_tasks',
        'tcf_ee_task_documents',
        'tcf_ee_consignes',
        'tcf_eo_exams',
        'tcf_eo_parts',
        'tcf_eo_subjects',
        'tcf_eo_consignes',
        // views optionnelles (peuvent contenir id=0)
        // 'tcf_ee_exam_views',
        // 'tcf_eo_exam_views',
    ];

    foreach ($insertOrder as $t) {
        if (empty($inserts[$t])) {
            echo "AUCUNE donnée pour {$t}\n";
            continue;
        }
        foreach ($inserts[$t] as $sql) {
            // INSERT IGNORE : le dump epreuve.sql contient parfois des id en double
            $sqlIgnore = preg_replace('/^INSERT INTO/i', 'INSERT IGNORE INTO', ltrim($sql), 1);
            try {
                $pdo->exec($sqlIgnore);
            } catch (Throwable $e) {
                echo "ERR {$t}: " . $e->getMessage() . "\n";
            }
        }
        $cnt = (int) $pdo->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
        echo "OK {$t} => {$cnt} lignes\n";
    }

    // AUTO_INCREMENT
    foreach (['tcf_ee_exams', 'tcf_eo_exams', 'tcf_ee_combinations', 'tcf_ee_tasks', 'tcf_ee_task_documents', 'tcf_eo_parts', 'tcf_eo_subjects'] as $t) {
        try {
            $max = (int) $pdo->query("SELECT COALESCE(MAX(id),0) FROM `{$t}`")->fetchColumn();
            $pdo->exec("ALTER TABLE `{$t}` AUTO_INCREMENT = " . max(1, $max + 1));
        } catch (Throwable $e) {
        }
    }

    // Masquer d'éventuels restes Part/Data (sécurité)
    $hidden = $pdo->exec(
        "UPDATE tcf_ee_exams SET is_published = 0
         WHERE title REGEXP 'Part[0-9]+| Data$'
            OR slug REGEXP 'part[0-9]+|_data'"
    );
    echo "Masqués Part/Data: {$hidden}\n";

    $ee = (int) $pdo->query('SELECT COUNT(*) FROM tcf_ee_exams WHERE is_published=1')->fetchColumn();
    $eo = (int) $pdo->query('SELECT COUNT(*) FROM tcf_eo_exams WHERE is_published=1')->fetchColumn();
    echo "\n===== RESULT =====\n";
    echo "EE publiés: {$ee}\n";
    echo "EO publiés: {$eo}\n";

    $titles = $pdo->query('SELECT title FROM tcf_ee_exams WHERE is_published=1 ORDER BY title')->fetchAll(PDO::FETCH_COLUMN);
    echo "Titres EE:\n - " . implode("\n - ", $titles) . "\n";

    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
} catch (Throwable $e) {
    echo 'FATAL: ' . $e->getMessage() . "\n";
    try {
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    } catch (Throwable $e2) {
    }
    exit(1);
}

echo "\nDONE — affichage = design cartes mois (sans Part1/Data).\n";
if (!$isCli) {
    echo '</pre>';
}
