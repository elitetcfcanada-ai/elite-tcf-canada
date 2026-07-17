<?php
/**
 * API souscription — activation uniquement après paiement Notch Pay confirmé.
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/subscription_plans_data.php';
require_once __DIR__ . '/includes/platform_settings.php';
require_once __DIR__ . '/includes/payment_config.php';
require_once __DIR__ . '/includes/notchpay_client.php';
require_once __DIR__ . '/includes/subscription_activate.php';

header('Content-Type: application/json; charset=utf-8');

if (tcf_subscriptions_platform_disabled($pdo)) {
    echo json_encode([
        'success' => false,
        'message' => 'Les abonnements sont temporairement désactivés. Tout le contenu est accessible gratuitement.',
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

if (empty($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Connexion requise pour souscrire.',
        'login_url' => site_href('login.php'),
    ]);
    exit;
}

$paymentReference = isset($_POST['payment_reference']) ? trim((string) $_POST['payment_reference']) : '';
if ($paymentReference === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Paiement requis. Utilisez Mobile Money (Orange / MTN) via la fenêtre de paiement.',
        'payment_api' => site_href('payment_api.php'),
    ]);
    exit;
}

$uid = (int) $_SESSION['user_id'];
tcf_subscription_payments_ensure_pending_table($pdo);

$st = $pdo->prepare('SELECT * FROM subscription_payment_pending WHERE notch_reference = ? AND user_id = ? LIMIT 1');
$st->execute([$paymentReference, $uid]);
$pending = $st->fetch(PDO::FETCH_ASSOC);
if (!$pending) {
    echo json_encode(['success' => false, 'message' => 'Référence de paiement introuvable.']);
    exit;
}

if (($pending['status'] ?? '') === 'complete') {
    $planKey = (string) ($pending['plan_key'] ?? '');
    echo json_encode([
        'success' => true,
        'message' => 'Votre abonnement est déjà actif.',
        'subscription_type' => $planKey,
        'subscription_label' => tcf_subscription_label($planKey),
        'premium_access' => true,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$check = tcf_notchpay_get_payment($paymentReference);
if (!$check['ok'] || !is_array($check['data'])) {
    echo json_encode(['success' => false, 'message' => $check['error'] ?? 'Impossible de vérifier le paiement.']);
    exit;
}

$payStatus = tcf_notchpay_payment_status_from_response($check['data']);
if (!tcf_notchpay_is_success_status($payStatus)) {
    echo json_encode([
        'success' => false,
        'message' => 'Paiement non confirmé. Statut : ' . ($payStatus !== '' ? $payStatus : 'en attente'),
        'status' => $payStatus !== '' ? $payStatus : 'pending',
    ]);
    exit;
}

$planKey = (string) ($pending['plan_key'] ?? '');
$channel = (string) ($pending['channel'] ?? 'notchpay');
$plan = tcf_subscription_plan_by_key($planKey);
$amountUsd = isset($plan['price']) ? (float) $plan['price'] : tcf_subscription_display_usd_amount();

$result = tcf_subscription_activate_user(
    $pdo,
    $uid,
    $planKey,
    $channel,
    $amountUsd,
    'USD',
    $paymentReference
);

if (!$result['success']) {
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo->prepare('UPDATE subscription_payment_pending SET status = ? WHERE id = ?')
        ->execute(['complete', (int) $pending['id']]);
} catch (Throwable $e) {
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
