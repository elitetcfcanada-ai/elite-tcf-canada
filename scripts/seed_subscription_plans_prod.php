<?php
/**
 * Réinsère les forfaits abonnement (sans doublons) — prix vitrine.
 *
 * CLI : php scripts/seed_subscription_plans_prod.php
 * Web : scripts/seed_subscription_plans_prod.php?key=REPAIR_TCF_2026
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
    echo '<h1>Seed forfaits abonnement</h1><pre>';
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

$features = json_encode([
    'Expression écrite',
    'Expression orale',
    'Compréhension écrite',
    'Compréhension orale',
], JSON_UNESCAPED_UNICODE);

$plans = [
    ['plan_2w', 'STANDARD', 'DEUX SEMAINES', 35.0, '$', 14, 1],
    ['plan_1m', 'STANDARD', 'UN MOIS', 50.0, '$', 30, 2],
    ['plan_2m', 'PREMIUM', 'DEUX MOIS', 75.0, '$', 60, 3],
];

// Recréer proprement
$pdo->exec('DELETE FROM subscription_plan_catalog');
try {
    $pdo->exec('ALTER TABLE subscription_plan_catalog ADD UNIQUE KEY uq_subscription_plan_key (plan_key)');
} catch (Throwable $e) {
    // ok
}

$st = $pdo->prepare(
    'INSERT INTO subscription_plan_catalog
        (plan_key, tier, badge, price, currency, duration_days, features_json, sort_order, is_active)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)'
);
foreach ($plans as $p) {
    $st->execute([$p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $features, $p[6]]);
    echo "OK {$p[0]} {$p[1]} {$p[2]} \${$p[3]}\n";
}

$rows = $pdo->query(
    'SELECT id, plan_key, tier, badge, price FROM subscription_plan_catalog ORDER BY sort_order, id'
)->fetchAll(PDO::FETCH_ASSOC);
echo "\nCatalogue (" . count($rows) . "):\n";
foreach ($rows as $r) {
    echo "  #{$r['id']} {$r['plan_key']} | {$r['tier']} {$r['badge']} \${$r['price']}\n";
}
echo "DONE\n";

if (!$isCli) {
    echo '</pre>';
}
