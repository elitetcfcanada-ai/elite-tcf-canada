<?php
require_once __DIR__ . '/../includes/config.php';
try {
    $tables = $pdo->query("SHOW TABLES LIKE '%subscription%'")->fetchAll(PDO::FETCH_COLUMN);
    echo "TABLES:\n" . implode("\n", $tables) . "\n\n";
    foreach ($tables as $t) {
        $n = (int) $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "$t => $n rows\n";
    }
    echo "\nCATALOG:\n";
    $rows = $pdo->query('SELECT id, plan_key, tier, badge, price, is_active FROM subscription_plan_catalog')->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
} catch (Throwable $e) {
    echo 'ERR: ' . $e->getMessage() . "\n";
}
