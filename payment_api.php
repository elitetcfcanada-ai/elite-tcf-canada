<?php

declare(strict_types=1);

/**
 * API paiement abonnement via Notch Pay (Mobile Money).
 * Actions JSON : init, process, status, sync
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/subscription_plans_data.php';
require_once __DIR__ . '/includes/platform_settings.php';
require_once __DIR__ . '/includes/payment_config.php';
require_once __DIR__ . '/includes/notchpay_client.php';
require_once __DIR__ . '/includes/subscription_activate.php';
require_once __DIR__ . '/includes/payment_reconcile.php';

header('Content-Type: application/json; charset=utf-8');

if (tcf_subscriptions_platform_disabled($pdo)) {
    echo json_encode(['success' => false, 'message' => 'Les abonnements sont temporairement désactivés.']);
    exit;
}

if (empty($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Connexion requise.',
        'login_url' => site_href('login.php'),
    ]);
    exit;
}

$uid = (int) $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT id, role, name, email FROM users WHERE id = ?');
$stmt->execute([$uid]);
$userRow = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$userRow || ($userRow['role'] ?? '') !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Seuls les comptes apprenants peuvent payer depuis cette page.']);
    exit;
}

$userName = (string) ($userRow['name'] ?? '');
$userEmail = (string) ($userRow['email'] ?? '');

$input = [];
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    if (is_string($raw) && $raw !== '') {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $input = $decoded;
        }
    }
}
if ($input === []) {
    $input = array_merge($_GET, $_POST);
}

$action = isset($input['action']) ? trim((string) $input['action']) : '';
if ($action === '') {
    echo json_encode(['success' => false, 'message' => 'Action manquante.']);
    exit;
}

tcf_subscription_payments_ensure_pending_table($pdo);

$userName = trim((string) ($userRow['name'] ?? 'Client TCF'));
$userEmail = trim((string) ($userRow['email'] ?? ''));
if ($userEmail === '') {
    $userEmail = 'client' . $uid . '@elite-tcf.local';
}

function tcf_payment_pending_by_ref(PDO $pdo, string $ref, int $uid): ?array
{
    return tcf_payment_pending_find_by_ref($pdo, $ref, $uid);
}

if ($action === 'sync') {
    // Force reconcile (ignore throttle session)
    unset($_SESSION['tcf_pay_reconcile_at']);
    $ref = isset($input['reference']) ? trim((string) $input['reference']) : '';
    if ($ref !== '') {
        $pending = tcf_payment_pending_by_ref($pdo, $ref, $uid);
        if ($pending) {
            $final = tcf_payment_try_finalize($pdo, $uid, $pending, (string) ($pending['channel'] ?? ''));
            if (!empty($final['success']) && tcf_payment_is_finalized_status($final['status'] ?? '')) {
                echo json_encode([
                    'success' => true,
                    'status' => 'complete',
                    'activated' => true,
                    'message' => $final['message'] ?? 'Abonnement activé.',
                    'subscription_type' => $final['subscription_type'] ?? null,
                    'subscription_label' => $final['subscription_label'] ?? null,
                    'premium_access' => true,
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
    }
    $sync = tcf_payment_reconcile_user($pdo, $uid);
    echo json_encode([
        'success' => !empty($sync['activated']),
        'status' => !empty($sync['activated']) ? 'complete' : 'pending',
        'activated' => !empty($sync['activated']),
        'checked' => (int) ($sync['checked'] ?? 0),
        'message' => $sync['message'] !== ''
            ? $sync['message']
            : 'Aucune transaction confirmée pour le moment.',
        'subscription_type' => $sync['subscription_type'] ?? null,
        'premium_access' => !empty($sync['activated']),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'init') {
    $planKey = isset($input['plan_key']) ? trim((string) $input['plan_key']) : '';
    $plan = tcf_subscription_plan_by_key($planKey);
    if ($plan === null) {
        echo json_encode(['success' => false, 'message' => 'Formule invalide.']);
        exit;
    }

    if (!tcf_notchpay_is_configured()) {
        echo json_encode(['success' => false, 'message' => 'Paiement non configuré (clés Notch Pay manquantes sur le serveur).']);
        exit;
    }

    // Avant un nouvel init : tenter de rattraper un paiement déjà validé
    $pre = tcf_payment_reconcile_user($pdo, $uid);
    if (!empty($pre['activated'])) {
        echo json_encode([
            'success' => true,
            'status' => 'complete',
            'already' => true,
            'message' => 'Un paiement Notch déjà confirmé a activé votre abonnement.',
            'premium_access' => true,
            'subscription_type' => $pre['subscription_type'] ?? null,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $phone = isset($input['phone']) ? trim((string) $input['phone']) : '';
    if ($phone === '') {
        echo json_encode(['success' => false, 'message' => 'Numéro de téléphone requis.']);
        exit;
    }

    $phone = tcf_notchpay_normalize_phone($phone);
    if ($phone === '' || !tcf_notchpay_is_valid_cm_phone($phone)) {
        echo json_encode([
            'success' => false,
            'message' => 'Numéro Cameroun invalide. Exemple MTN : +237 67X XXX XXX — Orange : +237 69X XXX XXX',
        ]);
        exit;
    }

    $provider = isset($input['provider']) ? trim((string) $input['provider']) : 'auto';
    $channel = tcf_notchpay_resolve_channel($phone, $provider);
    if (tcf_is_local_host()) {
        $amountXaf = tcf_subscription_payment_xaf_amount();
    } else {
        $amountXaf = isset($plan['payment_xaf']) ? (int) $plan['payment_xaf'] : tcf_subscription_payment_xaf_amount();
    }
    if ($amountXaf < 100) {
        $amountXaf = 100;
    }
    $reference = 'tcf_' . $uid . '_' . preg_replace('/[^a-z0-9_]/i', '', $planKey) . '_' . time() . '_' . bin2hex(random_bytes(4));
    $description = 'Abonnement ' . ($plan['tier'] ?? '') . ' — ' . ($plan['badge'] ?? $planKey);
    $callbackUrl = tcf_payment_callback_url();

    $init = tcf_notchpay_initialize_payment($amountXaf, $reference, $description, [
        'name' => $userName,
        'email' => $userEmail,
        'phone' => $phone,
    ], $callbackUrl, 'XAF');

    if (!$init['ok'] || !is_array($init['data'])) {
        echo json_encode(['success' => false, 'message' => $init['error'] ?? 'Initialisation du paiement impossible.']);
        exit;
    }

    $notchRef = tcf_notchpay_extract_reference($init['data']);
    if ($notchRef === '') {
        echo json_encode(['success' => false, 'message' => 'Référence Notch Pay manquante dans la réponse.']);
        exit;
    }

    $authorizationUrl = tcf_notchpay_extract_authorization_url($init['data']);
    if ($authorizationUrl === '') {
        echo json_encode(['success' => false, 'message' => 'Lien de paiement Notch Pay manquant. Réessayez dans un instant.']);
        exit;
    }

    try {
        tcf_payment_pending_insert($pdo, $uid, $planKey, $notchRef, $amountXaf, $channel);
    } catch (Throwable $e) {
        error_log('payment_api pending insert: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Impossible d\'enregistrer la transaction. Réessayez ou contactez le support.',
        ]);
        exit;
    }

    $mode = 'redirect';
    $message = 'Redirection vers la page de paiement sécurisée Notch Pay…';
    $chargeError = '';

    $charge = tcf_notchpay_charge_mobile($notchRef, $channel, $phone);
    if (!empty($charge['ok']) && is_array($charge['data'] ?? null)) {
        $chargeStatus = tcf_notchpay_payment_status_from_response($charge['data']);
        if (!tcf_notchpay_is_failure_status($chargeStatus)) {
            $mode = 'direct';
            $usedChannel = (string) ($charge['channel_used'] ?? $channel);
            if ($usedChannel !== '' && $usedChannel !== $channel) {
                $channel = $usedChannel;
                try {
                    tcf_payment_pending_update_channel_by_ref($pdo, $notchRef, $channel);
                } catch (Throwable $e) {
                }
            }
            $label = ($channel === 'cm.orange') ? 'Orange Money' : (($channel === 'cm.mtn') ? 'MTN Mobile Money' : 'Mobile Money');
            $message = 'Demande envoyée sur votre téléphone. Confirmez le paiement ' . $label . '.';
        } else {
            $chargeError = 'Le paiement a été refusé immédiatement. Vérifiez l\'opérateur (MTN / Orange) et le solde.';
        }
    } else {
        $chargeError = (string) ($charge['error'] ?? '');
    }

    if ($mode !== 'direct' && $chargeError !== '') {
        $message = 'Ouverture de la page Notch Pay pour finaliser (MTN / Orange)…';
    }

    $usdPrice = isset($plan['price']) ? (float) $plan['price'] : tcf_subscription_display_usd_amount();
    echo json_encode([
        'success' => true,
        'mode' => $mode,
        'reference' => $notchRef,
        'redirect_url' => $authorizationUrl,
        'channel' => $channel,
        'phone' => $phone,
        'amount_xaf' => $amountXaf,
        'amount_display' => '$' . number_format($usdPrice, 2, '.', ''),
        'message' => $message,
        'charge_hint' => $chargeError !== '' ? $chargeError : null,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'status') {
    $reference = isset($input['reference']) ? trim((string) $input['reference']) : '';
    if ($reference === '') {
        echo json_encode(['success' => false, 'message' => 'Référence manquante.']);
        exit;
    }

    $pending = tcf_payment_pending_by_ref($pdo, $reference, $uid);
    if ($pending === null) {
        // Référence inconnue localement : tenter reconcile globale (autre ref du même user)
        $sync = tcf_payment_reconcile_user($pdo, $uid);
        if (!empty($sync['activated'])) {
            echo json_encode([
                'success' => true,
                'status' => 'complete',
                'message' => $sync['message'] ?: 'Abonnement activé.',
                'premium_access' => true,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        echo json_encode(['success' => false, 'message' => 'Transaction introuvable.']);
        exit;
    }

    $channel = (string) ($pending['channel'] ?? '');
    $final = tcf_payment_try_finalize($pdo, $uid, $pending, $channel);

    $finalStatus = strtolower(trim((string) ($final['status'] ?? 'pending')));
    if (!empty($final['success']) && tcf_payment_is_finalized_status($finalStatus)) {
        echo json_encode([
            'success' => true,
            'status' => 'complete',
            'message' => $final['message'] ?? 'Abonnement activé.',
            'subscription_type' => $final['subscription_type'] ?? null,
            'subscription_label' => $final['subscription_label'] ?? null,
            'subscription_expires_at' => $final['subscription_expires_at'] ?? null,
            'premium_access' => $final['premium_access'] ?? true,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        'success' => (bool) ($final['success'] ?? false),
        'status' => $final['status'] ?? 'pending',
        'message' => $final['message'] ?? 'En attente de confirmation…',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
