<?php

declare(strict_types=1);

/**
 * API paiement abonnement via Notch Pay (Mobile Money).
 * Actions JSON : init, process, status
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/subscription_plans_data.php';
require_once __DIR__ . '/includes/platform_settings.php';
require_once __DIR__ . '/includes/payment_config.php';
require_once __DIR__ . '/includes/notchpay_client.php';
require_once __DIR__ . '/includes/subscription_activate.php';

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
    $st = $pdo->prepare('SELECT * FROM subscription_payment_pending WHERE notch_reference = ? AND user_id = ? LIMIT 1');
    $st->execute([$ref, $uid]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function tcf_payment_try_finalize(PDO $pdo, int $uid, array $pending, string $channel): array
{
    $ref = (string) ($pending['notch_reference'] ?? '');
    $planKey = (string) ($pending['plan_key'] ?? '');
    $statusRow = (string) ($pending['status'] ?? 'pending');

    if ($statusRow === 'complete') {
        return ['success' => true, 'status' => 'complete', 'already' => true];
    }

    $check = tcf_notchpay_get_payment($ref);
    if (!$check['ok'] || !is_array($check['data'])) {
        return ['success' => false, 'status' => 'pending', 'message' => $check['error'] ?? 'Impossible de vérifier le paiement.'];
    }

    $payStatus = tcf_notchpay_payment_status_from_response($check['data']);
    if (tcf_notchpay_is_failure_status($payStatus)) {
        try {
            $pdo->prepare('UPDATE subscription_payment_pending SET status = ? WHERE id = ?')
                ->execute([$payStatus !== '' ? $payStatus : 'failed', (int) ($pending['id'] ?? 0)]);
        } catch (Throwable $e) {
        }

        return [
            'success' => false,
            'status' => $payStatus !== '' ? $payStatus : 'failed',
            'message' => 'Le paiement a été refusé, annulé ou a expiré.',
        ];
    }

    if (!tcf_notchpay_is_success_status($payStatus)) {
        return ['success' => true, 'status' => $payStatus !== '' ? $payStatus : 'pending', 'message' => 'Paiement en cours…'];
    }

    $plan = tcf_subscription_plan_by_key($planKey);
    $amountUsd = isset($plan['price']) ? (float) $plan['price'] : tcf_subscription_display_usd_amount();
    $method = $channel !== '' ? $channel : (string) ($pending['channel'] ?? 'notchpay');
    $result = tcf_subscription_activate_user($pdo, $uid, $planKey, $method, $amountUsd, 'USD', $ref);

    if (!$result['success']) {
        return ['success' => false, 'status' => 'complete', 'message' => $result['message']];
    }

    try {
        $pdo->prepare('UPDATE subscription_payment_pending SET status = ?, channel = COALESCE(NULLIF(?, \'\'), channel) WHERE id = ?')
            ->execute(['complete', $channel, (int) ($pending['id'] ?? 0)]);
    } catch (Throwable $e) {
    }

    return array_merge($result, ['success' => true, 'status' => 'complete']);
}

if ($action === 'init') {
    $planKey = isset($input['plan_key']) ? trim((string) $input['plan_key']) : '';
    $plan = tcf_subscription_plan_by_key($planKey);
    if ($plan === null) {
        echo json_encode(['success' => false, 'message' => 'Formule invalide.']);
        exit;
    }

    if (tcf_notchpay_secret_key() === '' && tcf_notchpay_public_key() === '') {
        echo json_encode(['success' => false, 'message' => 'Paiement non configuré (clés Notch Pay manquantes).']);
        exit;
    }

    $phone = isset($input['phone']) ? trim((string) $input['phone']) : '';
    if ($phone === '') {
        echo json_encode(['success' => false, 'message' => 'Numéro de téléphone requis.']);
        exit;
    }

    $channel = tcf_notchpay_detect_channel($phone);
    $amountXaf = isset($plan['payment_xaf']) ? (int) $plan['payment_xaf'] : tcf_subscription_payment_xaf_amount();
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
        $ins = $pdo->prepare(
            'INSERT INTO subscription_payment_pending (user_id, plan_key, notch_reference, amount_xaf, channel, status) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $ins->execute([$uid, $planKey, $notchRef, $amountXaf, $channel, 'pending']);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => 'Impossible d\'enregistrer la transaction.']);
        exit;
    }

    $mode = 'redirect';
    $message = 'Redirection vers la page de paiement sécurisée Notch Pay…';

    $charge = tcf_notchpay_process_mobile($notchRef, $channel, $phone);
    if ($charge['ok'] && is_array($charge['data'])) {
        $chargeStatus = tcf_notchpay_payment_status_from_response($charge['data']);
        if (!tcf_notchpay_is_failure_status($chargeStatus)) {
            $mode = 'direct';
            $message = 'Demande envoyée sur votre téléphone. Confirmez le paiement Mobile Money (MTN / Orange).';
        }
    }

    $usdPrice = isset($plan['price']) ? (float) $plan['price'] : tcf_subscription_display_usd_amount();
    echo json_encode([
        'success' => true,
        'mode' => $mode,
        'reference' => $notchRef,
        'redirect_url' => $authorizationUrl,
        'channel' => $channel,
        'amount_xaf' => $amountXaf,
        'amount_display' => '$' . number_format($usdPrice, 2, '.', ''),
        'message' => $message,
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
        echo json_encode(['success' => false, 'message' => 'Transaction introuvable.']);
        exit;
    }

    $channel = (string) ($pending['channel'] ?? '');
    $final = tcf_payment_try_finalize($pdo, $uid, $pending, $channel);

    if (($final['status'] ?? '') === 'complete' && !empty($final['success'])) {
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
