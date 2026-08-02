<?php

declare(strict_types=1);

/**
 * Réconciliation paiements Notch Pay → activation abonnement.
 * Couvre les cas : USSD validé hors navigateur, webhook manqué, session perdue, erreur UI.
 */

require_once __DIR__ . '/notchpay_client.php';
require_once __DIR__ . '/subscription_activate.php';
require_once __DIR__ . '/subscription_plans_data.php';
require_once __DIR__ . '/tcf_legacy_tables.php';
require_once __DIR__ . '/subscription_access.php';

/**
 * Liste les transactions d’un utilisateur encore à synchroniser (pending ou complete sans premium).
 *
 * @return list<array<string,mixed>>
 */
function tcf_payment_pending_list_for_user(PDO $pdo, int $uid, int $limit = 15): array
{
    if ($uid <= 0) {
        return [];
    }
    $limit = max(1, min(40, $limit));
    $rows = [];
    try {
        if (tcf_historique_abonnements_available($pdo)) {
            $st = $pdo->prepare(
                "SELECT * FROM historique_abonnements
                 WHERE user_id = ?
                   AND reference IS NOT NULL AND reference <> ''
                   AND LOWER(COALESCE(status,'')) NOT IN ('failed','failure','cancelled','canceled','rejected','declined','expired','timeout','abandoned')
                 ORDER BY id DESC
                 LIMIT {$limit}"
            );
            $st->execute([$uid]);
            $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } else {
            $st = $pdo->prepare(
                "SELECT * FROM subscription_payment_pending
                 WHERE user_id = ?
                   AND notch_reference IS NOT NULL AND notch_reference <> ''
                   AND LOWER(COALESCE(status,'')) NOT IN ('failed','failure','cancelled','canceled','rejected','declined','expired','timeout','abandoned')
                 ORDER BY id DESC
                 LIMIT {$limit}"
            );
            $st->execute([$uid]);
            $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
    } catch (Throwable $e) {
        error_log('tcf_payment_pending_list_for_user: ' . $e->getMessage());
        return [];
    }

    $out = [];
    foreach ($rows as $row) {
        $n = tcf_payment_pending_normalize($row);
        if ($n) {
            $out[] = $n;
        }
    }

    return $out;
}

/**
 * Finalise une ligne pending si Notch confirme le paiement.
 *
 * @param array<string,mixed> $pending
 * @return array{success:bool,status:string,message:string,already?:bool,subscription_type?:string,subscription_label?:string,subscription_expires_at?:?string,premium_access?:bool}
 */
function tcf_payment_try_finalize(PDO $pdo, int $uid, array $pending, string $channel = ''): array
{
    $ref = trim((string) ($pending['notch_reference'] ?? $pending['reference'] ?? ''));
    $planKey = trim((string) ($pending['plan_key'] ?? ''));
    $statusRow = (string) ($pending['status'] ?? 'pending');
    $pendingId = (int) ($pending['id'] ?? 0);

    if ($ref === '' || $uid <= 0 || $planKey === '') {
        return ['success' => false, 'status' => 'pending', 'message' => 'Transaction incomplete.'];
    }

    // Déjà premium actif → marquer complete et sortir
    try {
        $stU = $pdo->prepare('SELECT subscription_type, subscription_expires_at, role, created_at FROM users WHERE id = ? LIMIT 1');
        $stU->execute([$uid]);
        $uRow = $stU->fetch(PDO::FETCH_ASSOC) ?: null;
        if ($uRow && tcf_user_has_premium_access($uRow)) {
            if (!tcf_payment_is_finalized_status($statusRow) && $pendingId > 0) {
                try {
                    tcf_payment_pending_update_status($pdo, $pendingId, 'complete', $channel !== '' ? $channel : null);
                } catch (Throwable $e) {
                }
            }
            $subType = (string) ($uRow['subscription_type'] ?? $planKey);

            return [
                'success' => true,
                'status' => 'complete',
                'already' => true,
                'message' => 'Votre abonnement est activé.',
                'subscription_type' => $subType,
                'subscription_label' => function_exists('tcf_subscription_label') ? tcf_subscription_label($subType) : $subType,
                'subscription_expires_at' => $uRow['subscription_expires_at'] ?? null,
                'premium_access' => true,
            ];
        }
    } catch (Throwable $e) {
    }

    $check = tcf_notchpay_get_payment($ref);
    if (!$check['ok'] || !is_array($check['data'])) {
        return [
            'success' => false,
            'status' => 'pending',
            'message' => $check['error'] ?? 'Impossible de vérifier le paiement auprès de Notch Pay.',
        ];
    }

    $payStatus = tcf_notchpay_payment_status_from_response($check['data']);
    if ($payStatus === '') {
        // Certains payloads mettent le statut ailleurs
        $payStatus = strtolower(trim((string) (
            $check['data']['code']
            ?? $check['data']['data']['status']
            ?? $check['data']['data']['transaction']['status']
            ?? ''
        )));
    }

    if (tcf_notchpay_is_failure_status($payStatus)) {
        if ($pendingId > 0) {
            try {
                tcf_payment_pending_update_status($pdo, $pendingId, $payStatus !== '' ? $payStatus : 'failed');
            } catch (Throwable $e) {
            }
        }

        return [
            'success' => false,
            'status' => $payStatus !== '' ? $payStatus : 'failed',
            'message' => 'Le paiement a été refusé, annulé ou a expiré.',
        ];
    }

    if (!tcf_notchpay_is_success_status($payStatus)) {
        return [
            'success' => true,
            'status' => $payStatus !== '' ? $payStatus : 'pending',
            'message' => 'Paiement en cours de confirmation auprès de Notch Pay…',
        ];
    }

    $plan = tcf_subscription_plan_by_key($planKey, false);
    $amountUsd = isset($plan['price']) ? (float) $plan['price'] : tcf_subscription_display_usd_amount();
    if ($plan && strtoupper((string) ($plan['currency'] ?? '')) === 'XAF' && $amountUsd >= 100) {
        $amountUsd = round($amountUsd / 600, 2);
    }
    $method = $channel !== '' ? $channel : (string) ($pending['channel'] ?? 'notchpay');
    $result = tcf_subscription_activate_user($pdo, $uid, $planKey, $method, $amountUsd, 'USD', $ref);

    if (empty($result['success'])) {
        return [
            'success' => false,
            'status' => 'pending',
            'message' => (string) ($result['message'] ?? 'Paiement reçu mais activation impossible. Réessayez ou contactez le support.'),
        ];
    }

    if ($pendingId > 0) {
        try {
            tcf_payment_pending_update_status($pdo, $pendingId, 'complete', $method);
        } catch (Throwable $e) {
        }
    }

    return array_merge($result, ['success' => true, 'status' => 'complete']);
}

/**
 * Vérifie toutes les transactions ouvertes d’un utilisateur auprès de Notch et active si payé.
 *
 * @return array{checked:int,activated:bool,message:string,subscription_type?:string,premium_access?:bool}
 */
function tcf_payment_reconcile_user(PDO $pdo, int $uid): array
{
    if ($uid <= 0) {
        return ['checked' => 0, 'activated' => false, 'message' => ''];
    }

    try {
        $stU = $pdo->prepare('SELECT subscription_type, subscription_expires_at, role, created_at FROM users WHERE id = ? AND role = \'user\' LIMIT 1');
        $stU->execute([$uid]);
        $uRow = $stU->fetch(PDO::FETCH_ASSOC) ?: null;
        if (!$uRow) {
            return ['checked' => 0, 'activated' => false, 'message' => ''];
        }
        if (tcf_user_has_premium_access($uRow)) {
            return [
                'checked' => 0,
                'activated' => true,
                'message' => 'Abonnement déjà actif.',
                'subscription_type' => (string) ($uRow['subscription_type'] ?? ''),
                'premium_access' => true,
            ];
        }
    } catch (Throwable $e) {
        return ['checked' => 0, 'activated' => false, 'message' => ''];
    }

    tcf_subscription_payments_ensure_pending_table($pdo);
    $list = tcf_payment_pending_list_for_user($pdo, $uid, 12);
    $checked = 0;
    foreach ($list as $pending) {
        $checked++;
        $channel = (string) ($pending['channel'] ?? '');
        $final = tcf_payment_try_finalize($pdo, $uid, $pending, $channel);
        if (!empty($final['success']) && tcf_payment_is_finalized_status($final['status'] ?? '') && !empty($final['premium_access'])) {
            return [
                'checked' => $checked,
                'activated' => true,
                'message' => (string) ($final['message'] ?? 'Abonnement activé après vérification Notch Pay.'),
                'subscription_type' => (string) ($final['subscription_type'] ?? ''),
                'subscription_label' => (string) ($final['subscription_label'] ?? ''),
                'subscription_expires_at' => $final['subscription_expires_at'] ?? null,
                'premium_access' => true,
            ];
        }
        // Si success sans premium_access mais status complete — recharger user
        if (!empty($final['success']) && tcf_payment_is_finalized_status($final['status'] ?? '')) {
            try {
                $stU2 = $pdo->prepare('SELECT subscription_type, subscription_expires_at, role, created_at FROM users WHERE id = ? LIMIT 1');
                $stU2->execute([$uid]);
                $u2 = $stU2->fetch(PDO::FETCH_ASSOC) ?: null;
                if ($u2 && tcf_user_has_premium_access($u2)) {
                    return [
                        'checked' => $checked,
                        'activated' => true,
                        'message' => 'Abonnement activé.',
                        'subscription_type' => (string) ($u2['subscription_type'] ?? ''),
                        'premium_access' => true,
                    ];
                }
            } catch (Throwable $e) {
            }
        }
    }

    return [
        'checked' => $checked,
        'activated' => false,
        'message' => $checked > 0
            ? 'Aucune confirmation de paiement trouvée pour le moment. Si vous avez validé sur votre téléphone, patientez quelques secondes puis rechargez.'
            : '',
    ];
}

/**
 * Appel léger (throttlé) depuis les pages du site pour rattraper un paiement validé hors session.
 */
function tcf_payment_reconcile_session_user(PDO $pdo): void
{
    if (PHP_SAPI === 'cli') {
        return;
    }
    if (empty($_SESSION['user_id'])) {
        return;
    }
    if (!empty($_SESSION['is_admin']) || (function_exists('tcf_session_is_staff') && tcf_session_is_staff())) {
        return;
    }
    $role = (string) ($_SESSION['role'] ?? 'user');
    if ($role !== '' && $role !== 'user') {
        return;
    }
    $uid = (int) $_SESSION['user_id'];
    if ($uid <= 0) {
        return;
    }
    $now = time();
    $last = (int) ($_SESSION['tcf_pay_reconcile_at'] ?? 0);
    // Au plus une fois / 45 s (évite spam API Notch)
    if ($last > 0 && ($now - $last) < 45) {
        return;
    }
    $_SESSION['tcf_pay_reconcile_at'] = $now;
    try {
        $res = tcf_payment_reconcile_user($pdo, $uid);
        if (!empty($res['activated'])) {
            $_SESSION['tcf_pay_just_activated'] = 1;
        }
    } catch (Throwable $e) {
        error_log('tcf_payment_reconcile_session_user: ' . $e->getMessage());
    }
}
