<?php

declare(strict_types=1);

/**
 * Coordonnées officielles du site (contact, support, footer, page d'accueil).
 */
function tcf_site_contact(): array
{
    return [
        'brand' => 'ELITE TCF CANADA',
        'email' => 'elitetcfcanada@gmail.com',
        'phone' => '+237 674 992 835',
        'phone_display' => '+237 674 992 835',
        'address' => 'Cameroun',
        'hours' => 'Lundi — Dimanche, 24h/24',
        'whatsapp' => '+237 674 992 835',
    ];
}

/** Chiffres uniquement pour wa.me / tel / Telegram. */
function tcf_site_phone_digits(?array $c = null): string
{
    $c = $c ?? tcf_site_contact();
    $raw = (string) ($c['whatsapp'] ?? $c['phone'] ?? '');
    $digits = preg_replace('/\D+/', '', $raw) ?: '237674992835';
    return $digits;
}

function tcf_site_mailto(?array $c = null): string
{
    $c = $c ?? tcf_site_contact();
    return 'mailto:' . (string) ($c['email'] ?? '');
}

function tcf_site_tel(?array $c = null): string
{
    return 'tel:+' . tcf_site_phone_digits($c);
}

function tcf_site_whatsapp_url(?array $c = null): string
{
    return 'https://wa.me/' . tcf_site_phone_digits($c);
}

/** Ouvre une discussion Telegram vers le numéro de la plateforme. */
function tcf_site_telegram_url(?array $c = null): string
{
    return 'https://t.me/+' . tcf_site_phone_digits($c);
}
