<?php

declare(strict_types=1);

/**
 * Configuration paiement Notch Pay (Mobile Money).
 *
 * Priorité :
 * 1. includes/payment_config.local.php (dev local, non versionné)
 * 2. includes/payment_config.hostinger.php (production)
 * 3. Variables d'environnement NOTCHPAY_PUBLIC_KEY / NOTCHPAY_SECRET_KEY
 */

$tcf_notchpay_public_key = '';
$tcf_notchpay_secret_key = '';
$tcf_notchpay_api_base = 'https://api.notchpay.co';
/** URL publique de base (ex. https://elitetcfcanada.online) — pour le callback Notch Pay. */
$tcf_payment_public_base_url = '';

$local = __DIR__ . '/payment_config.local.php';
$hostinger = __DIR__ . '/payment_config.hostinger.php';

if (is_file($local)) {
    require $local;
} elseif (is_file($hostinger)) {
    require $hostinger;
}

if ($tcf_notchpay_public_key === '' && getenv('NOTCHPAY_PUBLIC_KEY')) {
    $tcf_notchpay_public_key = (string) getenv('NOTCHPAY_PUBLIC_KEY');
}

if ($tcf_notchpay_secret_key === '' && getenv('NOTCHPAY_SECRET_KEY')) {
    $tcf_notchpay_secret_key = (string) getenv('NOTCHPAY_SECRET_KEY');
}

// Callback HTTPS forcé sur le domaine de production si non défini
if ($tcf_payment_public_base_url === '') {
    $reqHost = strtolower(trim((string) ($_SERVER['HTTP_HOST'] ?? '')));
    if ($reqHost !== '' && strpos($reqHost, 'elitetcfcanada.online') !== false) {
        $tcf_payment_public_base_url = 'https://elitetcfcanada.online';
    }
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

/** True si on est sur un hôte local (XAMPP). */
function tcf_is_local_host(): bool
{
    $host = strtolower(trim((string) ($_SERVER['HTTP_HOST'] ?? '')));
    if ($host === '' || $host === 'localhost' || str_starts_with($host, 'localhost:')
        || $host === '127.0.0.1' || str_starts_with($host, '127.0.0.1:')) {
        return true;
    }
    return false;
}

/** URL absolue du callback après paiement (Notch Pay exige https:// en production). */
function tcf_payment_callback_url(): string
{
    global $tcf_payment_public_base_url;
    $path = site_href('payment_callback.php');
    $base = rtrim(trim((string) $tcf_payment_public_base_url), '/');
    if ($base !== '') {
        return $base . $path;
    }

    // Production : toujours HTTPS sur le host courant
    if (!tcf_is_local_host()) {
        $host = trim((string) ($_SERVER['HTTP_HOST'] ?? 'elitetcfcanada.online'));
        return 'https://' . $host . $path;
    }

    // Local : HTTP (évite certificat invalide)
    return 'http://localhost' . $path;
}

function tcf_notchpay_is_configured(): bool
{
    return tcf_notchpay_public_key() !== '';
}
