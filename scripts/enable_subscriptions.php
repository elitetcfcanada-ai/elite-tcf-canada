<?php
declare(strict_types=1);

$key = (string) ($_GET['key'] ?? '');
if ($key !== 'REPAIR_TCF_2026') {
    http_response_code(403);
    echo "Accès refusé.\n";
    exit;
}

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/payment_config.php';

header('Content-Type: text/html; charset=utf-8');
echo '<h1>Activation abonnements + paiement</h1><pre>';

tcf_platform_setting_set($pdo, 'subscriptions_disabled', '0');
$disabled = tcf_subscriptions_platform_disabled($pdo);
echo 'subscriptions_disabled=' . ($disabled ? '1 (encore désactivé)' : '0 (activé)') . "\n";
echo 'payment_keys=' . (tcf_notchpay_is_configured() ? 'OK' : 'MISSING') . "\n";
echo 'callback=' . tcf_payment_callback_url() . "\n";
echo 'hostinger_payment_file=' . (is_file(dirname(__DIR__) . '/includes/payment_config.hostinger.php') ? 'yes' : 'no') . "\n";
echo "\nDONE — testez /abonnement.php puis un paiement.\n";
echo "Supprimez ce script après usage.\n</pre>";
