<?php
declare(strict_types=1);

/**
 * Réactive les abonnements payés (Notch complete) encore bloqués en free/pending.
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

echo "=== Repair stuck subscriptions ===\n";

if (!tcf_historique_abonnements_available($pdo)) {
    echo "No historique_abonnements table.\n";
    exit(0);
}

$st = $pdo->query(
    "SELECT h.id, h.user_id, h.plan_key, h.reference, h.status, h.provider, h.amount, h.currency,
            u.subscription_type, u.subscription_expires_at
     FROM historique_abonnements h
     LEFT JOIN users u ON u.id = h.user_id
     WHERE h.reference IS NOT NULL AND h.reference <> ''
       AND (
         LOWER(COALESCE(h.status,'')) IN ('pending','','processing')
         OR u.subscription_type IS NULL
         OR u.subscription_type = 'free'
         OR u.subscription_expires_at IS NULL
         OR u.subscription_expires_at < NOW()
       )
     ORDER BY h.id DESC
     LIMIT 80"
);
$rows = $st ? ($st->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
echo 'candidates=' . count($rows) . "\n";

$fixed = 0;
foreach ($rows as $row) {
    $ref = trim((string) ($row['reference'] ?? ''));
    $uid = (int) ($row['user_id'] ?? 0);
    $planKey = trim((string) ($row['plan_key'] ?? ''));
    if ($ref === '' || $uid <= 0 || $planKey === '') {
        continue;
    }
    $check = tcf_notchpay_get_payment($ref);
    if (!$check['ok'] || !is_array($check['data'])) {
        echo "skip #{$row['id']} verify_fail\n";
        continue;
    }
    $payStatus = tcf_notchpay_payment_status_from_response($check['data']);
    if (!tcf_notchpay_is_success_status($payStatus)) {
        echo "skip #{$row['id']} status={$payStatus}\n";
        continue;
    }
    $plan = tcf_subscription_plan_by_key($planKey, false);
    $amountUsd = isset($plan['price']) ? (float) $plan['price'] : tcf_subscription_display_usd_amount();
    $channel = (string) ($row['provider'] ?? 'notchpay');
    $result = tcf_subscription_activate_user($pdo, $uid, $planKey, $channel, $amountUsd, 'USD', $ref);
    if (!empty($result['success'])) {
        try {
            $pdo->prepare(
                "UPDATE historique_abonnements SET status='complete', updated_at=NOW() WHERE id=?"
            )->execute([(int) $row['id']]);
        } catch (Throwable $e) {
        }
        $fixed++;
        echo "fixed user={$uid} plan={$planKey} premium=" . (!empty($result['premium_access']) ? 'yes' : 'no') . "\n";
    } else {
        echo "fail user={$uid} msg=" . ($result['message'] ?? '') . "\n";
    }
}

echo "OK fixed={$fixed}\n";
