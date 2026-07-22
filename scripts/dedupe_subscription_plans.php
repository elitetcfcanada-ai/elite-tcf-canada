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

$pdo->exec('SET NAMES utf8mb4');

$total = (int) $pdo->query('SELECT COUNT(*) FROM subscription_plan_catalog')->fetchColumn();
$distinctKeys = (int) $pdo->query('SELECT COUNT(DISTINCT plan_key) FROM subscription_plan_catalog')->fetchColumn();
echo "COUNT(*)={$total} DISTINCT plan_key={$distinctKeys}\n\n";

$rows = $pdo->query(
    'SELECT id, plan_key, tier, badge, price, is_active, sort_order
     FROM subscription_plan_catalog
     ORDER BY plan_key ASC, id ASC'
)->fetchAll(PDO::FETCH_ASSOC);

echo "Lignes brutes:\n";
foreach ($rows as $r) {
    echo '  id=' . $r['id']
        . ' key=' . $r['plan_key']
        . ' | ' . $r['tier'] . ' / ' . $r['badge']
        . ' $' . $r['price']
        . ' active=' . $r['is_active']
        . ' sort=' . $r['sort_order']
        . "\n";
}

$removed = 0;
$keepByKey = [];
foreach ($rows as $r) {
    $key = (string) $r['plan_key'];
    $id = (int) $r['id'];
    if (!isset($keepByKey[$key])) {
        $keepByKey[$key] = $id;
        continue;
    }
    // Doublon → supprimer cette ligne
    $pdo->prepare('DELETE FROM subscription_plan_catalog WHERE id = ?')->execute([$id]);
    $removed++;
    echo "DELETE id={$id} key={$key}\n";
}

// Aussi supprimer les lignes actives en double même si clé différente mais même badge+tier+price
$again = $pdo->query(
    'SELECT id, plan_key, tier, badge, price, is_active
     FROM subscription_plan_catalog
     ORDER BY tier, badge, price, id'
)->fetchAll(PDO::FETCH_ASSOC);
$seenSig = [];
foreach ($again as $r) {
    $sig = strtolower(trim($r['tier'] . '|' . $r['badge'] . '|' . $r['price']));
    if (isset($seenSig[$sig])) {
        $pdo->prepare('DELETE FROM subscription_plan_catalog WHERE id = ?')->execute([(int) $r['id']]);
        $removed++;
        echo 'DELETE duplicate signature id=' . $r['id'] . ' sig=' . $sig . "\n";
        continue;
    }
    $seenSig[$sig] = (int) $r['id'];
}

try {
    $pdo->exec('ALTER TABLE subscription_plan_catalog ADD UNIQUE KEY uq_subscription_plan_key (plan_key)');
    echo "UNIQUE KEY plan_key ajouté\n";
} catch (Throwable $e) {
    echo "UNIQUE KEY: " . $e->getMessage() . "\n";
}

$final = $pdo->query(
    'SELECT id, plan_key, tier, badge, price, is_active
     FROM subscription_plan_catalog
     ORDER BY sort_order ASC, id ASC'
)->fetchAll(PDO::FETCH_ASSOC);

echo "\nSupprimés={$removed}\nAprès (" . count($final) . "):\n";
foreach ($final as $r) {
    echo '  id=' . $r['id'] . ' ' . $r['plan_key'] . ' | ' . $r['tier'] . ' ' . $r['badge'] . ' $' . $r['price'] . "\n";
}
echo "DONE\n";

if (!$isCli) {
    echo '</pre>';
}
