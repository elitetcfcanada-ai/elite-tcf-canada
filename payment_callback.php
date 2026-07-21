<?php

declare(strict_types=1);

/**
 * Retour utilisateur après paiement Notch Pay (redirection navigateur).
 * Fonctionne même si la session a été perdue pendant la redirection Mobile Money.
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/payment_config.php';
require_once __DIR__ . '/includes/notchpay_client.php';
require_once __DIR__ . '/includes/subscription_activate.php';

$ref = isset($_GET['reference']) ? trim((string) $_GET['reference']) : '';
if ($ref === '' && isset($_GET['trxref'])) {
    $ref = trim((string) $_GET['trxref']);
}
$target = site_href('abonnement.php');

if ($ref !== '') {
    tcf_subscription_payments_ensure_pending_table($pdo);

    $st = $pdo->prepare('SELECT * FROM subscription_payment_pending WHERE notch_reference = ? LIMIT 1');
    $st->execute([$ref]);
    $pending = $st->fetch(PDO::FETCH_ASSOC);

    if ($pending) {
        $uid = (int) ($pending['user_id'] ?? 0);

        // Restaurer la session si perdue après redirection Notch Pay
        if ($uid > 0 && (empty($_SESSION['user_id']) || (int) $_SESSION['user_id'] !== $uid)) {
            try {
                $uSt = $pdo->prepare('SELECT id, name, email, role FROM users WHERE id = ? LIMIT 1');
                $uSt->execute([$uid]);
                $user = $uSt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $_SESSION['user_id'] = (int) $user['id'];
                    $_SESSION['username'] = (string) ($user['name'] ?? '');
                    $_SESSION['email'] = (string) ($user['email'] ?? '');
                    $_SESSION['role'] = (string) ($user['role'] ?? 'user');
                    $_SESSION['is_admin'] = in_array($user['role'] ?? '', ['admin', 'super_admin'], true);
                }
            } catch (Throwable $e) {
            }
        }

        if (($pending['status'] ?? '') !== 'complete') {
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
        } elseif (($pending['status'] ?? '') === 'complete') {
            $target .= (strpos($target, '?') !== false ? '&' : '?') . 'payment_success=1';
            header('Location: ' . $target);
            exit;
        }
    }

    $target .= (strpos($target, '?') !== false ? '&' : '?') . 'payment_ref=' . rawurlencode($ref);
}

header('Location: ' . $target);
exit;
