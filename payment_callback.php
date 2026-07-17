<?php

declare(strict_types=1);

/**
 * Retour utilisateur après paiement Notch Pay (redirection navigateur).
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/payment_config.php';
require_once __DIR__ . '/includes/notchpay_client.php';
require_once __DIR__ . '/includes/subscription_activate.php';

$ref = isset($_GET['reference']) ? trim((string) $_GET['reference']) : '';
$target = site_href('abonnement.php');

if ($ref !== '' && !empty($_SESSION['user_id'])) {
    $uid = (int) $_SESSION['user_id'];
    tcf_subscription_payments_ensure_pending_table($pdo);

    $st = $pdo->prepare('SELECT * FROM subscription_payment_pending WHERE notch_reference = ? AND user_id = ? LIMIT 1');
    $st->execute([$ref, $uid]);
    $pending = $st->fetch(PDO::FETCH_ASSOC);

    if ($pending && ($pending['status'] ?? '') !== 'complete') {
        $check = tcf_notchpay_get_payment($ref);
        if ($check['ok'] && is_array($check['data'])) {
            $payStatus = tcf_notchpay_payment_status_from_response($check['data']);
            if (tcf_notchpay_is_success_status($payStatus)) {
                $planKey = (string) ($pending['plan_key'] ?? '');
                $channel = (string) ($pending['channel'] ?? 'notchpay');
                $plan = tcf_subscription_plan_by_key($planKey);
                $amountUsd = isset($plan['price']) ? (float) $plan['price'] : tcf_subscription_display_usd_amount();
                $result = tcf_subscription_activate_user($pdo, $uid, $planKey, $channel, $amountUsd, 'USD', $ref);
                if (!empty($result['success'])) {
                    try {
                        $pdo->prepare('UPDATE subscription_payment_pending SET status = ? WHERE id = ?')
                            ->execute(['complete', (int) $pending['id']]);
                    } catch (Throwable $e) {
                    }
                    $target .= (strpos($target, '?') !== false ? '&' : '?') . 'payment_success=1';
                    header('Location: ' . $target);
                    exit;
                }
            }
        }
    }

    $target .= (strpos($target, '?') !== false ? '&' : '?') . 'payment_ref=' . rawurlencode($ref);
}

header('Location: ' . $target);
exit;
