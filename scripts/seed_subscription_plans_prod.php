<?php
/**
 * Répare subscription_plan_catalog puis réinsère 3 forfaits uniques.
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

echo "Réparation table…\n";
$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
$pdo->exec('DROP TABLE IF EXISTS subscription_plan_catalog');
$pdo->exec(
    "CREATE TABLE subscription_plan_catalog (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        plan_key VARCHAR(64) NOT NULL,
        tier VARCHAR(80) NOT NULL DEFAULT '',
        badge VARCHAR(120) NOT NULL DEFAULT '',
        price DECIMAL(10,2) NOT NULL DEFAULT 0,
        currency VARCHAR(8) NOT NULL DEFAULT '\$',
        duration_days INT UNSIGNED NOT NULL DEFAULT 7,
        features_json TEXT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_subscription_plan_key (plan_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);
$pdo->exec('SET FOREIGN_KEY_CHECKS=1');
echo "Table recréée OK\n";

$st = $pdo->prepare(
    'INSERT INTO subscription_plan_catalog
        (plan_key, tier, badge, price, currency, duration_days, features_json, sort_order, is_active)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)'
);
foreach ($plans as $p) {
    $st->execute([$p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $features, $p[6]]);
    $id = (int) $pdo->lastInsertId();
    echo "OK id={$id} {$p[0]} {$p[1]} {$p[2]} \${$p[3]}\n";
}

$rows = $pdo->query(
    'SELECT id, plan_key, tier, badge, price FROM subscription_plan_catalog ORDER BY sort_order, id'
)->fetchAll(PDO::FETCH_ASSOC);
$count = (int) $pdo->query('SELECT COUNT(*) FROM subscription_plan_catalog')->fetchColumn();
$distinct = (int) $pdo->query('SELECT COUNT(DISTINCT plan_key) FROM subscription_plan_catalog')->fetchColumn();
echo "\nCOUNT={$count} DISTINCT_KEYS={$distinct}\n";
foreach ($rows as $r) {
    echo "  #{$r['id']} {$r['plan_key']} | {$r['tier']} {$r['badge']} \${$r['price']}\n";
}
echo "DONE\n";

if (!$isCli) {
    echo '</pre>';
}
