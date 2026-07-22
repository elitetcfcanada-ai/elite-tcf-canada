<?php
/**
 * Supprime les forfaits en double (même plan_key) dans subscription_plan_catalog.
 *
 * CLI : php scripts/dedupe_subscription_plans.php
 * Web : scripts/dedupe_subscription_plans.php?key=REPAIR_TCF_2026
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
    echo '<h1>Déduplication forfaits</h1><pre>';
}

$root = dirname(__DIR__);
$host = 'localhost';
$dbname = 'TCF';
$username = 'root';
$password = '';
$port = '';
if (is_file($root . '/includes/config.local.php')) {
    require $root . '/includes/config.local.php';
} elseif (is_file($root . '/includes/config.hostinger.php')) {
    require $root . '/includes/config.hostinger.php';
}
$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
if ($port !== '') {
    $dsn .= ";port={$port}";
}
$pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

require_once $root . '/includes/subscription_plans_data.php';

$before = $pdo->query(
    'SELECT id, plan_key, tier, badge, price, is_active FROM subscription_plan_catalog ORDER BY plan_key, id'
)->fetchAll(PDO::FETCH_ASSOC);
echo "Avant (" . count($before) . "):\n";
foreach ($before as $r) {
    echo "  #{$r['id']} {$r['plan_key']} | {$r['tier']} {$r['badge']} \${$r['price']} active={$r['is_active']}\n";
}

$result = tcf_subscription_plans_dedupe_db($pdo);
echo "\nSupprimés={$result['removed']} restants={$result['remaining']}\n\n";

$after = $pdo->query(
    'SELECT id, plan_key, tier, badge, price, is_active FROM subscription_plan_catalog ORDER BY sort_order, id'
)->fetchAll(PDO::FETCH_ASSOC);
echo "Après (" . count($after) . "):\n";
foreach ($after as $r) {
    echo "  #{$r['id']} {$r['plan_key']} | {$r['tier']} {$r['badge']} \${$r['price']} active={$r['is_active']}\n";
}
echo "DONE\n";

if (!$isCli) {
    echo '</pre>';
}
