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
require_once __DIR__ . '/includes/payment_reconcile.php';

$ref = isset($_GET['reference']) ? trim((string) $_GET['reference']) : '';
if ($ref === '' && isset($_GET['trxref'])) {
    $ref = trim((string) $_GET['trxref']);
}
$target = site_href('abonnement.php');

if ($ref !== '') {
    tcf_subscription_payments_ensure_pending_table($pdo);

    $pending = tcf_payment_pending_find_by_ref($pdo, $ref);

    if ($pending) {
        $uid = (int) ($pending['user_id'] ?? 0);

        if ($uid > 0 && (empty($_SESSION['user_id']) || (int) $_SESSION['user_id'] !== $uid)) {
            try {
                $uSt = $pdo->prepare('SELECT id, name, email, role FROM users WHERE id = ? LIMIT 1');
                $uSt->execute([$uid]);
                $user = $uSt->fetch(PDO::FETCH_ASSOC);
                if ($user && ($user['role'] ?? '') === 'user') {
                    $_SESSION['user_id'] = (int) $user['id'];
                    $_SESSION['username'] = (string) ($user['name'] ?? '');
                    $_SESSION['email'] = (string) ($user['email'] ?? '');
                    $_SESSION['role'] = 'user';
                    $_SESSION['is_admin'] = false;
                }
            } catch (Throwable $e) {
            }
        }

        $channel = (string) ($pending['channel'] ?? 'notchpay');
        $result = tcf_payment_try_finalize($pdo, $uid, $pending, $channel);
        if (!empty($result['success']) && tcf_payment_is_finalized_status($result['status'] ?? '')) {
            $target .= (strpos($target, '?') !== false ? '&' : '?') . 'payment_success=1';
            header('Location: ' . $target);
            exit;
        }

        // Dernière chance : toutes les transactions du user
        if ($uid > 0) {
            $sync = tcf_payment_reconcile_user($pdo, $uid);
            if (!empty($sync['activated'])) {
                $target .= (strpos($target, '?') !== false ? '&' : '?') . 'payment_success=1';
                header('Location: ' . $target);
                exit;
            }
        }
    }

    $target .= (strpos($target, '?') !== false ? '&' : '?') . 'payment_ref=' . rawurlencode($ref);
}

header('Location: ' . $target);
exit;
