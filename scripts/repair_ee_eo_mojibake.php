<?php
/**
 * Répare le mojibake CP850 (├®, ÔÇÖ, etc.) dans les textes EE/EO en base.
 * Alternative rapide si le dump n'est pas réimporté.
 *
 * CLI : php scripts/repair_ee_eo_mojibake.php
 * Web : scripts/repair_ee_eo_mojibake.php?key=REPAIR_TCF_2026
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
    echo '<h1>Réparation encodage EE / EO</h1><pre>';
}

// Bootstrap DB sans platform_settings (évite les tables locales cassées)
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

/**
 * Inverse le mojibake CP850→UTF-8 : "├®" → "é".
 */
function tcf_repair_cp850_mojibake(string $text): string
{
    if ($text === '') {
        return $text;
    }
    // Marqueurs typiques du dump corrompu (box-drawing / ÔÇ…) — jamais du français valide
    if (strpos($text, '├') === false && strpos($text, 'ÔÇ') === false && strpos($text, '╗') === false) {
        return $text;
    }

    if (!function_exists('iconv')) {
        return $text;
    }

    $bytes = @iconv('UTF-8', 'CP850//IGNORE', $text);
    if ($bytes === false || $bytes === '') {
        return $text;
    }
    if (!mb_check_encoding($bytes, 'UTF-8')) {
        return $text;
    }
    return $bytes;
}

$tables = [
    'tcf_ee_exams' => ['title', 'subtitle'],
    'tcf_ee_combinations' => ['title', 'subtitle'],
    'tcf_ee_tasks' => ['prompt', 'correction'],
    'tcf_ee_task_documents' => ['title', 'content'],
    'tcf_ee_consignes' => ['title', 'body'],
    'tcf_eo_exams' => ['title', 'subtitle'],
    'tcf_eo_parts' => ['title', 'subtitle'],
    'tcf_eo_subjects' => ['title', 'prompt', 'correction', 'role_label'],
    'tcf_eo_consignes' => ['title', 'body'],
];

$totalRows = 0;
$totalFields = 0;

try {
    $pdo->exec("SET NAMES utf8mb4");
    foreach ($tables as $table => $cols) {
        $exists = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($table))->fetchColumn();
        if (!$exists) {
            echo "SKIP missing table {$table}\n";
            continue;
        }
        $colList = array_merge(['id'], $cols);
        $sql = 'SELECT `' . implode('`, `', $colList) . '` FROM `' . $table . '`';
        $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $updated = 0;
        foreach ($rows as $row) {
            $sets = [];
            $params = [];
            foreach ($cols as $col) {
                $raw = $row[$col];
                if ($raw === null || !is_string($raw)) {
                    continue;
                }
                $fixed = tcf_repair_cp850_mojibake($raw);
                if ($fixed !== $raw) {
                    $sets[] = "`{$col}` = ?";
                    $params[] = $fixed;
                    $totalFields++;
                }
            }
            if ($sets === []) {
                continue;
            }
            $params[] = $row['id'];
            $pdo->prepare('UPDATE `' . $table . '` SET ' . implode(', ', $sets) . ' WHERE id = ?')
                ->execute($params);
            $updated++;
            $totalRows++;
        }
        echo "{$table}: {$updated} row(s) updated\n";
    }

    $sample = $pdo->query("SELECT id, title FROM tcf_ee_exams ORDER BY id LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nSample titles:\n";
    foreach ($sample as $s) {
        echo '  #' . $s['id'] . ' ' . $s['title'] . "\n";
    }
    $bad = (int) $pdo->query("SELECT COUNT(*) FROM tcf_ee_exams WHERE title LIKE '%├%' OR title LIKE '%ÔÇ%'")->fetchColumn();
    echo "\nRemaining mojibake titles in tcf_ee_exams: {$bad}\n";
    echo "DONE rows={$totalRows} fields={$totalFields}\n";
} catch (Throwable $e) {
    echo 'FATAL: ' . $e->getMessage() . "\n";
    exit(1);
}

if (!$isCli) {
    echo '</pre>';
}
