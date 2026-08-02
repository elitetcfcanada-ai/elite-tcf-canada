<?php
declare(strict_types=1);

/**
 * Réactive les abonnements payés (Notch) encore bloqués.
 * Web : /scripts/repair_stuck_subscriptions.php?key=REPAIR_TCF_2026
 */

$cli = PHP_SAPI === 'cli';
if (!$cli && (string) ($_GET['key'] ?? '') !== 'REPAIR_TCF_2026') {
    http_response_code(403);
    echo "Forbidden\n";
    exit;
}

header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/payment_config.php';
require_once __DIR__ . '/../includes/notchpay_client.php';
require_once __DIR__ . '/../includes/subscription_activate.php';
require_once __DIR__ . '/../includes/tcf_legacy_tables.php';
require_once __DIR__ . '/../includes/subscription_access.php';
require_once __DIR__ . '/../includes/payment_reconcile.php';

echo "=== Repair stuck subscriptions ===\n";

tcf_users_ensure_subscription_type_varchar($pdo);
$stCol = $pdo->query("SHOW COLUMNS FROM users LIKE 'subscription_type'");
$col = $stCol ? $stCol->fetch(PDO::FETCH_ASSOC) : [];
echo 'subscription_type=' . ($col['Type'] ?? '?') . "\n";

$repairedTypes = tcf_repair_users_with_active_expiry($pdo);
echo "repaired_empty_type_with_future_expiry={$repairedTypes}\n";

if (!tcf_historique_abonnements_available($pdo) && !tcf_schema_has_table($pdo, 'subscription_payment_pending')) {
    echo "No payment pending table.\nOK\n";
    exit(0);
}

// Utilisateurs avec au moins une transaction non-échouée
$userIds = [];
try {
    if (tcf_historique_abonnements_available($pdo)) {
        $st = $pdo->query(
            "SELECT DISTINCT user_id FROM historique_abonnements
             WHERE reference IS NOT NULL AND reference <> ''
               AND LOWER(COALESCE(status,'')) NOT IN ('failed','failure','cancelled','canceled','rejected','declined','expired')
             ORDER BY user_id DESC LIMIT 100"
        );
        $userIds = array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN) ?: []);
    }
} catch (Throwable $e) {
}

echo 'users_to_check=' . count($userIds) . "\n";
$fixed = 0;
foreach ($userIds as $uid) {
    if ($uid <= 0) {
        continue;
    }
    $res = tcf_payment_reconcile_user($pdo, $uid);
    if (!empty($res['activated'])) {
        $fixed++;
        echo "fixed user={$uid} checked={$res['checked']} msg=" . ($res['message'] ?? '') . "\n";
    } else {
        echo "noop user={$uid} checked={$res['checked']}\n";
    }
}

echo "OK fixed={$fixed}\n";
