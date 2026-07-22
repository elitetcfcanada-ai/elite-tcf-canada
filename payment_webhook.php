<?php

declare(strict_types=1);

/**
 * Webhook Notch Pay — activation abonnement côté serveur (prod + local si tunnel).
 * À enregistrer dans le dashboard Notch Pay : https://votredomaine/payment_webhook.php
 *
 * Sécurité :
 * - Si NOTCHPAY_WEBHOOK_HASH / $tcf_notchpay_webhook_hash est défini : vérifie X-Notch-Signature
 * - Dans tous les cas : re-vérifie le statut via l'API Notch avant d'activer
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/payment_config.php';
require_once __DIR__ . '/includes/notchpay_client.php';
require_once __DIR__ . '/includes/subscription_activate.php';
require_once __DIR__ . '/includes/subscription_plans_data.php';

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'POST only']);
    exit;
}

$raw = (string) file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Invalid JSON']);
    exit;
}

$hash = tcf_notchpay_webhook_hash();
if ($hash !== '') {
    $sig = (string) ($_SERVER['HTTP_X_NOTCH_SIGNATURE'] ?? $_SERVER['HTTP_X_NOTCHPAY_SIGNATURE'] ?? '');
    $expected = hash_hmac('sha256', $raw, $hash);
    if ($sig === '' || !hash_equals($expected, $sig)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'message' => 'Invalid signature']);
        exit;
    }
}

$eventType = strtolower((string) ($payload['type'] ?? $payload['event'] ?? ''));
$data = $payload['data'] ?? $payload['payment'] ?? $payload['transaction'] ?? $payload;
if (!is_array($data)) {
    $data = [];
}

$ref = tcf_notchpay_extract_reference(array_merge($payload, ['payment' => $data, 'transaction' => $data]));
if ($ref === '' && isset($data['reference'])) {
    $ref = trim((string) $data['reference']);
}

if ($ref === '') {
    http_response_code(200);
    echo json_encode(['ok' => true, 'ignored' => true, 'reason' => 'no_reference']);
    exit;
}

tcf_subscription_payments_ensure_pending_table($pdo);

$st = $pdo->prepare('SELECT * FROM subscription_payment_pending WHERE notch_reference = ? LIMIT 1');
$st->execute([$ref]);
$pending = $st->fetch(PDO::FETCH_ASSOC);
if (!$pending) {
    http_response_code(200);
    echo json_encode(['ok' => true, 'ignored' => true, 'reason' => 'unknown_ref']);
    exit;
}

if (($pending['status'] ?? '') === 'complete') {
    http_response_code(200);
    echo json_encode(['ok' => true, 'already' => true]);
    exit;
}

// Re-vérification API (ne jamais faire confiance uniquement au webhook)
$check = tcf_notchpay_get_payment($ref);
if (!$check['ok'] || !is_array($check['data'])) {
    http_response_code(200);
    echo json_encode(['ok' => false, 'message' => $check['error'] ?? 'verify_failed']);
    exit;
}

$payStatus = tcf_notchpay_payment_status_from_response($check['data']);
$isCompleteEvent = str_contains($eventType, 'complete') || str_contains($eventType, 'success');

if (tcf_notchpay_is_failure_status($payStatus)) {
    try {
        $pdo->prepare('UPDATE subscription_payment_pending SET status = ? WHERE id = ?')
            ->execute([$payStatus !== '' ? $payStatus : 'failed', (int) $pending['id']]);
    } catch (Throwable $e) {
    }
    http_response_code(200);
    echo json_encode(['ok' => true, 'status' => $payStatus]);
    exit;
}

if (!tcf_notchpay_is_success_status($payStatus) && !$isCompleteEvent) {
    http_response_code(200);
    echo json_encode(['ok' => true, 'status' => $payStatus !== '' ? $payStatus : 'pending']);
    exit;
}

if (!tcf_notchpay_is_success_status($payStatus)) {
    // Event dit complete mais API pas encore à jour : on laisse le polling/callback finir
    http_response_code(200);
    echo json_encode(['ok' => true, 'status' => 'pending_sync']);
    exit;
}

$uid = (int) ($pending['user_id'] ?? 0);
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
}

http_response_code(200);
echo json_encode([
    'ok' => !empty($result['success']),
    'status' => 'complete',
    'message' => $result['message'] ?? null,
]);
