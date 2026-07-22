<?php

declare(strict_types=1);

/**
 * Configuration paiement Notch Pay (Mobile Money).
 *
 * Priorité :
 * 1. En local (localhost) : includes/payment_config.local.php si présent
 * 2. En production : includes/payment_config.hostinger.php
 * 3. Variables d'environnement NOTCHPAY_PUBLIC_KEY / NOTCHPAY_SECRET_KEY
 *
 * Important : le fichier .local.php n'est JAMAIS chargé hors localhost,
 * pour éviter qu'une conf de dev casse les paiements sur Hostinger.
 */

$tcf_notchpay_public_key = '';
$tcf_notchpay_secret_key = '';
$tcf_notchpay_api_base = 'https://api.notchpay.co';
$tcf_notchpay_webhook_hash = '';
/** URL publique de base (ex. https://elitetcfcanada.online) — pour le callback Notch Pay. */
$tcf_payment_public_base_url = '';

$local = __DIR__ . '/payment_config.local.php';
$hostinger = __DIR__ . '/payment_config.hostinger.php';

$reqHost = strtolower(trim((string) ($_SERVER['HTTP_HOST'] ?? '')));
$isLocalReq = ($reqHost === '' || $reqHost === 'localhost' || str_starts_with($reqHost, 'localhost:')
    || $reqHost === '127.0.0.1' || str_starts_with($reqHost, '127.0.0.1:'));

if ($isLocalReq && is_file($local)) {
    require $local;
} elseif (is_file($hostinger)) {
    require $hostinger;
} elseif (is_file($local)) {
    // Dernier recours (CLI / cron sans HTTP_HOST)
    require $local;
}

if ($tcf_notchpay_public_key === '' && getenv('NOTCHPAY_PUBLIC_KEY')) {
    $tcf_notchpay_public_key = (string) getenv('NOTCHPAY_PUBLIC_KEY');
}

if ($tcf_notchpay_secret_key === '' && getenv('NOTCHPAY_SECRET_KEY')) {
    $tcf_notchpay_secret_key = (string) getenv('NOTCHPAY_SECRET_KEY');
}

if ($tcf_notchpay_webhook_hash === '' && getenv('NOTCHPAY_WEBHOOK_HASH')) {
    $tcf_notchpay_webhook_hash = (string) getenv('NOTCHPAY_WEBHOOK_HASH');
}

// Callback HTTPS forcé sur le domaine de production si non défini
if ($tcf_payment_public_base_url === '') {
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

function tcf_notchpay_webhook_hash(): string
{
    global $tcf_notchpay_webhook_hash;
    return trim((string) $tcf_notchpay_webhook_hash);
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

/**
 * URL absolue du callback navigateur après paiement.
 * Production : toujours HTTPS (base configurée ou host courant).
 * Local : HTTP via site_url (Notch ne pourra pas joindre localhost — normal).
 */
function tcf_payment_callback_url(): string
{
    global $tcf_payment_public_base_url;
    $path = site_href('payment_callback.php');
    $base = rtrim(trim((string) $tcf_payment_public_base_url), '/');
    if ($base !== '') {
        return $base . $path;
    }

    if (function_exists('site_url')) {
        return site_url('payment_callback.php');
    }

    if (!tcf_is_local_host()) {
        $host = trim((string) ($_SERVER['HTTP_HOST'] ?? 'elitetcfcanada.online'));
        return 'https://' . $host . $path;
    }

    return 'http://localhost' . $path;
}

/** URL absolue webhook Notch Pay (à enregistrer dans le dashboard Notch). */
function tcf_payment_webhook_url(): string
{
    global $tcf_payment_public_base_url;
    $path = site_href('payment_webhook.php');
    $base = rtrim(trim((string) $tcf_payment_public_base_url), '/');
    if ($base !== '') {
        return $base . $path;
    }
    if (function_exists('site_url')) {
        return site_url('payment_webhook.php');
    }
    return tcf_payment_callback_url();
}

function tcf_notchpay_is_configured(): bool
{
    return tcf_notchpay_public_key() !== '';
}
