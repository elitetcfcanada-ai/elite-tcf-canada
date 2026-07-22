<?php
/**
 * Diagnostic paiement (sans exposer les clés).
 * ?key=REPAIR_TCF_2026
 */
declare(strict_types=1);

$key = (string) ($_GET['key'] ?? '');
if ($key !== 'REPAIR_TCF_2026') {
    http_response_code(403);
    echo "Accès refusé.\n";
    exit;
}

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/payment_config.php';
require_once dirname(__DIR__) . '/includes/notchpay_client.php';

header('Content-Type: text/html; charset=utf-8');
echo '<h1>Diagnostic paiement</h1><pre>';

$pub = tcf_notchpay_public_key();
$sec = tcf_notchpay_secret_key();
echo 'host=' . ($_SERVER['HTTP_HOST'] ?? '') . "\n";
echo 'https=' . ($_SERVER['HTTPS'] ?? 'off') . "\n";
echo 'pub_configured=' . ($pub !== '' ? 'yes' : 'no') . ' prefix=' . substr($pub, 0, 8) . "\n";
echo 'sec_configured=' . ($sec !== '' ? 'yes' : 'no') . ' prefix=' . substr($sec, 0, 8) . "\n";
echo 'callback=' . tcf_payment_callback_url() . "\n";
echo 'webhook=' . tcf_payment_webhook_url() . "\n";
echo 'local_host=' . (tcf_is_local_host() ? 'yes' : 'no') . "\n";
echo 'local_file=' . (is_file(__DIR__ . '/../includes/payment_config.local.php') ? 'yes' : 'no') . "\n";
echo 'hostinger_file=' . (is_file(__DIR__ . '/../includes/payment_config.hostinger.php') ? 'yes' : 'no') . "\n";

$samples = [
    '670000000',
    '0670000000',
    '+237670000000',
    '237670000000',
    '690000000',
    '655000000',
    '650000000',
];
echo "\n--- Phone normalize / channel ---\n";
foreach ($samples as $s) {
    $n = tcf_notchpay_normalize_phone($s);
    $ch = tcf_notchpay_detect_channel($s);
    $ok = tcf_notchpay_is_valid_cm_phone($n) ? 'OK' : 'BAD';
    echo $s . ' => ' . $n . ' [' . $ch . '] ' . $ok . "\n";
}

if ($pub !== '') {
    // Ping léger : init impossible sans montant réel ; on teste juste une requête GET si dispo
    $ping = tcf_notchpay_request('GET', '/payments?page=1&perpage=1');
    echo 'api_ok=' . ($ping['ok'] ? 'yes' : 'no') . ' http=' . ($ping['http'] ?? 0) . "\n";
    if (!$ping['ok']) {
        echo 'api_error=' . ($ping['error'] ?? '') . "\n";
    }
} else {
    echo "KEYS_MISSING — paiement impossible sur cet environnement.\n";
}

echo "</pre>";
