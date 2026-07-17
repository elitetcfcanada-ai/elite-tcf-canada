<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/site_contact.php';

/**
 * Texte par défaut (si champs BDD vides).
 */
function tcf_channel_branding_default_tagline(): string
{
    return 'Bonne préparation au TCF — abonne-toi à la chaîne pour les vidéos, annonces et contenus publiés par l’équipe.';
}

/**
 * @return array{title: string, tagline: string, logo_href: string, banner_href: string}
 */
function tcf_channel_branding_front(PDO $pdo): array
{
    $sc = tcf_site_contact();
    $defTitle = (string) ($sc['brand'] ?? 'ELITE TCF CANADA');
    $defTag = tcf_channel_branding_default_tagline();

    $title = $defTitle;
    $tagline = $defTag;
    $logoUrl = null;
    $bannerUrl = null;

    try {
        $st = $pdo->query('SELECT title, tagline, logo_url, banner_url FROM channel_branding WHERE id = 1 LIMIT 1');
        $r = $st->fetch(PDO::FETCH_ASSOC);
        if ($r) {
            if (trim((string) ($r['title'] ?? '')) !== '') {
                $title = trim((string) $r['title']);
            }
            if (trim((string) ($r['tagline'] ?? '')) !== '') {
                $tagline = trim((string) $r['tagline']);
            }
            $logoUrl = $r['logo_url'] !== null && (string) $r['logo_url'] !== '' ? (string) $r['logo_url'] : null;
            $bannerUrl = $r['banner_url'] !== null && (string) $r['banner_url'] !== '' ? (string) $r['banner_url'] : null;
        }
    } catch (Throwable $e) {
    }

    $logoHref = '';
    if ($logoUrl !== null && $logoUrl !== '') {
        $logoHref = tcf_uploads_public_href($logoUrl);
    }
    if ($logoHref === '') {
        $logoHref = site_href('Assets/IMAGE/home/canada.jpg');
    }

    $bannerHref = '';
    if ($bannerUrl !== null && $bannerUrl !== '') {
        $bannerHref = tcf_uploads_public_href($bannerUrl);
    }
    if ($bannerHref === '') {
        $fs = dirname(__DIR__) . '/Assets/IMAGE/home/channel-banner.jpg';
        if (is_file($fs)) {
            $bannerHref = site_href('Assets/IMAGE/home/channel-banner.jpg');
        }
    }

    return [
        'title' => $title,
        'tagline' => $tagline,
        'logo_href' => $logoHref,
        'banner_href' => $bannerHref,
    ];
}

/**
 * Données brutes pour le formulaire admin + URLs pour prévisualisation.
 *
 * @return array{title: string, tagline: string, logo_href: string, banner_href: string}
 */
function tcf_channel_branding_for_admin(PDO $pdo): array
{
    try {
        $st = $pdo->query('SELECT title, tagline, logo_url, banner_url FROM channel_branding WHERE id = 1 LIMIT 1');
        $r = $st->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return ['title' => '', 'tagline' => '', 'logo_href' => '', 'banner_href' => ''];
    }
    if (!$r) {
        return ['title' => '', 'tagline' => '', 'logo_href' => '', 'banner_href' => ''];
    }

    $logo = (string) ($r['logo_url'] ?? '');
    $ban = (string) ($r['banner_url'] ?? '');

    return [
        'title' => (string) ($r['title'] ?? ''),
        'tagline' => (string) ($r['tagline'] ?? ''),
        'logo_href' => $logo !== '' ? tcf_uploads_public_href($logo) : '',
        'banner_href' => $ban !== '' ? tcf_uploads_public_href($ban) : '',
    ];
}
