<?php

declare(strict_types=1);

/**
 * Webhook Notch Pay — activation abonnement côté serveur.
 * Re-vérifie toujours via l’API Notch avant d’activer.
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/payment_config.php';
require_once __DIR__ . '/includes/notchpay_client.php';
require_once __DIR__ . '/includes/subscription_activate.php';
require_once __DIR__ . '/includes/subscription_plans_data.php';
require_once __DIR__ . '/includes/payment_reconcile.php';

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

$pending = tcf_payment_pending_find_by_ref($pdo, $ref);
if (!$pending) {
    http_response_code(200);
    echo json_encode(['ok' => true, 'ignored' => true, 'reason' => 'unknown_ref']);
    exit;
}

$uid = (int) ($pending['user_id'] ?? 0);
$channel = (string) ($pending['channel'] ?? 'notchpay');

// Même si déjà « complete » en base : forcer finalize si l’utilisateur n’a pas le premium
$result = tcf_payment_try_finalize($pdo, $uid, $pending, $channel);

// Event complete même si API un peu en retard : 2e tentative via reconcile
$isCompleteEvent = str_contains($eventType, 'complete') || str_contains($eventType, 'success');
if (empty($result['premium_access']) && $isCompleteEvent && $uid > 0) {
    $sync = tcf_payment_reconcile_user($pdo, $uid);
    if (!empty($sync['activated'])) {
        $result = [
            'success' => true,
            'status' => 'complete',
            'message' => $sync['message'] ?? 'Abonnement activé.',
            'premium_access' => true,
        ];
    }
}

http_response_code(200);
echo json_encode([
    'ok' => !empty($result['success']) && tcf_payment_is_finalized_status($result['status'] ?? ''),
    'status' => $result['status'] ?? 'pending',
    'message' => $result['message'] ?? null,
]);
