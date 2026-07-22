<?php
/**
 * Restaure Compréhension Écrite + Orale depuis database/seeds_ce_co_data.sql
 *
 * CLI : php scripts/restore_ce_co.php
 * Web : scripts/restore_ce_co.php?key=REPAIR_TCF_2026
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
    echo '<h1>Restauration CE / CO</h1><pre>';
}

// Bootstrap DB (évite platform_settings si table locale cassée)
$root = dirname(__DIR__);
$host = 'localhost';
$dbname = 'TCF';
$username = 'root';
$password = '';
$port = '';
$localConfig = $root . '/includes/config.local.php';
$hostingerConfig = $root . '/includes/config.hostinger.php';
if (is_file($localConfig)) {
    require $localConfig;
} elseif (is_file($hostingerConfig)) {
    require $hostingerConfig;
}
$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
if ($port !== '') {
    $dsn .= ";port={$port}";
}
$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$dump = $root . '/database/seeds_ce_co_data.sql';
if (!is_file($dump)) {
    echo "Fichier introuvable: database/seeds_ce_co_data.sql\n";
    exit(1);
}

$sql = file_get_contents($dump);
if ($sql === false) {
    echo "Lecture impossible.\n";
    exit(1);
}

// Répare un éventuel mojibake CP850 dans le dump
if (str_contains($sql, '├') || str_contains($sql, 'ÔÇ')) {
    if (function_exists('iconv')) {
        $bytes = @iconv('UTF-8', 'CP850//IGNORE', $sql);
        if (is_string($bytes) && $bytes !== '' && mb_check_encoding($bytes, 'UTF-8')) {
            $sql = $bytes;
            echo "Dump: mojibake CP850 réparé à la volée.\n";
        }
    }
}

try {
    $pdo->exec('SET NAMES utf8mb4');
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    $parts = preg_split('/;\s*\n/', $sql) ?: [];
    $ok = 0;
    $err = 0;
    foreach ($parts as $stmt) {
        $stmt = trim($stmt);
        if ($stmt === '' || str_starts_with($stmt, '--') || str_starts_with($stmt, '/*')) {
            continue;
        }
        // Ignorer les méta SET/LOCK de mysqldump non indispensables
        if (preg_match('/^(LOCK TABLES|UNLOCK TABLES|\/\*!)/i', $stmt)) {
            continue;
        }
        try {
            $pdo->exec($stmt);
            $ok++;
        } catch (Throwable $e) {
            $err++;
            if ($err <= 8) {
                echo 'WARN: ' . $e->getMessage() . "\n";
            }
        }
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

    $ce = (int) $pdo->query('SELECT COUNT(*) FROM tcf_ce_exams WHERE is_published=1')->fetchColumn();
    $co = (int) $pdo->query('SELECT COUNT(*) FROM tcf_co_exams WHERE is_published=1')->fetchColumn();
    $ceq = (int) $pdo->query('SELECT COUNT(*) FROM tcf_ce_questions')->fetchColumn();
    $coq = (int) $pdo->query('SELECT COUNT(*) FROM tcf_co_questions')->fetchColumn();

    echo "Statements OK≈{$ok} err={$err}\n";
    echo "CE publiés={$ce} questions={$ceq}\n";
    echo "CO publiés={$co} questions={$coq}\n";
    $titles = $pdo->query('SELECT id, title FROM tcf_ce_exams ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($titles as $t) {
        echo '  CE #' . $t['id'] . ' ' . $t['title'] . "\n";
    }
    $titlesCo = $pdo->query('SELECT id, title FROM tcf_co_exams ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($titlesCo as $t) {
        echo '  CO #' . $t['id'] . ' ' . $t['title'] . "\n";
    }
    echo "DONE\n";
} catch (Throwable $e) {
    echo 'FATAL: ' . $e->getMessage() . "\n";
    exit(1);
}

if (!$isCli) {
    echo '</pre>';
}
