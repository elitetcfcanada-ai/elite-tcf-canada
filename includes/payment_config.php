<?php

declare(strict_types=1);

/**
 * Configuration paiement Notch Pay (Mobile Money).
 * Clé secrète : includes/payment_config.local.php (non versionné).
 */

$tcf_notchpay_public_key = '';
$tcf_notchpay_secret_key = '';
$tcf_notchpay_api_base = 'https://api.notchpay.co';
/** URL publique de base (ex. https://monsite.com ou tunnel ngrok) — optionnel, pour le callback Notch Pay. */
$tcf_payment_public_base_url = '';

$local = __DIR__ . '/payment_config.local.php';
if (is_file($local)) {
    require $local;
}

if ($tcf_notchpay_public_key === '' && getenv('NOTCHPAY_PUBLIC_KEY')) {
    $tcf_notchpay_public_key = (string) getenv('NOTCHPAY_PUBLIC_KEY');
}

if ($tcf_notchpay_secret_key === '' && getenv('NOTCHPAY_SECRET_KEY')) {
    $tcf_notchpay_secret_key = (string) getenv('NOTCHPAY_SECRET_KEY');
}

function tcf_notchpay_public_key(): string
{
    global $tcf_notchpay_public_key;
    return trim((string) $tcf_notchpay_public_key);
}

function tcf_notchpay_secret_key(): string
{
    global $tcf_notchpay_secret_key;
    return trim((string) $tcf_notchpay_secret_key);
}

function tcf_notchpay_api_base(): string
{
    global $tcf_notchpay_api_base;
    $b = trim((string) $tcf_notchpay_api_base);
    return $b !== '' ? rtrim($b, '/') : 'https://api.notchpay.co';
}

/** Montant Mobile Money en XAF pour les tests (100 FCFA). */
function tcf_subscription_payment_xaf_amount(): int
{
    return 100;
}

/** Affichage USD (~équivalent test). */
function tcf_subscription_display_usd_amount(): float
{
    return 0.16;
}

/** URL absolue du callback après paiement (Notch Pay exige https://…). */
function tcf_payment_callback_url(): string
{
    global $tcf_payment_public_base_url;
    $path = site_href('payment_callback.php');
    $base = rtrim(trim((string) $tcf_payment_public_base_url), '/');
    if ($base !== '') {
        return $base . $path;
    }
    $url = site_url('payment_callback.php');
    if (strpos($url, 'localhost') !== false || strpos($url, '127.0.0.1') !== false) {
        return 'https://elite-tcf.local/payment_callback.php';
    }
    return $url;
}
