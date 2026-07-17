<?php

declare(strict_types=1);

/**
 * Formateurs (page d’accueil). Table `trainers` — voir database/tcf.sql
 * Réseaux : Facebook, YouTube, Telegram uniquement (JSON objet {facebook,youtube,telegram}).
 */

function tcf_trainer_photo_href(?string $stored): string
{
    $fallback = site_href('Assets/IMAGE/employers/1.jpg');
    if ($stored === null || trim($stored) === '') {
        return $fallback;
    }
    $s = trim($stored);
    if (preg_match('#^https?://#i', $s)) {
        return $s;
    }
    if (str_starts_with($s, 'Assets/')) {
        return site_href($s);
    }
    $up = tcf_uploads_public_href($s);

    return $up !== '' ? $up : $fallback;
}

/** @param 'facebook'|'youtube'|'telegram' $network */
function tcf_trainer_social_icon_class_by_network(string $network): string
{
    return match ($network) {
        'facebook' => 'bx bxl-facebook',
        'youtube' => 'bx bxl-youtube',
        'telegram' => 'bx bxl-telegram',
        default => 'bx bx-link-external',
    };
}

function tcf_trainers_one_url(string $raw): string
{
    $u = trim($raw);
    if ($u === '') {
        return '';
    }
    if (preg_match('#^t\.me/#i', $u)) {
        $u = 'https://' . $u;
    }
    if (!preg_match('#^https?://#i', $u)) {
        return '';
    }
    if (function_exists('mb_strlen') && mb_strlen($u) > 500) {
        return mb_substr($u, 0, 500);
    }

    return strlen($u) > 500 ? substr($u, 0, 500) : $u;
}

/**
 * @return array{facebook: string, youtube: string, telegram: string}
 */
function tcf_trainers_social_decode(?string $json): array
{
    $out = ['facebook' => '', 'youtube' => '', 'telegram' => ''];
    if ($json === null || trim($json) === '') {
        return $out;
    }
    $d = json_decode($json, true);
    if (!is_array($d)) {
        return $out;
    }
    if (array_key_exists('facebook', $d) || array_key_exists('youtube', $d) || array_key_exists('telegram', $d)) {
        $out['facebook'] = tcf_trainers_one_url((string) ($d['facebook'] ?? ''));
        $out['youtube'] = tcf_trainers_one_url((string) ($d['youtube'] ?? ''));
        $out['telegram'] = tcf_trainers_one_url((string) ($d['telegram'] ?? ''));

        return $out;
    }
    foreach ($d as $row) {
        if (!is_array($row)) {
            continue;
        }
        $url = tcf_trainers_one_url((string) ($row['url'] ?? ''));
        if ($url === '') {
            continue;
        }
        $icon = strtolower((string) ($row['icon'] ?? ''));
        $label = strtolower((string) ($row['label'] ?? ''));
        $key = '';
        if (str_contains($icon, 'facebook') || str_contains($label, 'facebook')) {
            $key = 'facebook';
        } elseif (str_contains($icon, 'youtube') || str_contains($label, 'youtube')) {
            $key = 'youtube';
        } elseif (str_contains($icon, 'telegram') || str_contains($label, 'telegram')) {
            $key = 'telegram';
        }
        if ($key !== '' && $out[$key] === '') {
            $out[$key] = $url;
        }
    }

    return $out;
}

/**
 * Valide et renvoie le JSON à stocker (toujours les 3 clés).
 */
function tcf_trainers_social_normalize_json_string(string $raw): string
{
    $d = json_decode($raw, true);
    if (!is_array($d)) {
        return json_encode(['facebook' => '', 'youtube' => '', 'telegram' => ''], JSON_UNESCAPED_UNICODE);
    }
    $fb = tcf_trainers_one_url((string) ($d['facebook'] ?? ''));
    $yt = tcf_trainers_one_url((string) ($d['youtube'] ?? ''));
    $tg = tcf_trainers_one_url((string) ($d['telegram'] ?? ''));

    return json_encode(
        ['facebook' => $fb, 'youtube' => $yt, 'telegram' => $tg],
        JSON_UNESCAPED_UNICODE
    );
}

/**
 * Données publiques pour la section d’accueil (ordre : Facebook, YouTube, Telegram).
 *
 * @return list<array{name: string, role_title: string, photo_href: string, social_links: list<array{network: string, url: string, label: string}>}>
 */
function tcf_trainers_list_public(PDO $pdo): array
{
    try {
        $st = $pdo->query(
            'SELECT name, role_title, photo_url, social_links_json FROM trainers WHERE is_active = 1 ORDER BY sort_order ASC, id ASC'
        );
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [];
    }
    $labels = [
        'facebook' => 'Facebook',
        'youtube' => 'YouTube',
        'telegram' => 'Telegram',
    ];
    $order = ['facebook', 'youtube', 'telegram'];
    $out = [];
    foreach ($rows as $r) {
        $soc = tcf_trainers_social_decode((string) ($r['social_links_json'] ?? ''));
        $clean = [];
        foreach ($order as $k) {
            if (($soc[$k] ?? '') !== '') {
                $clean[] = [
                    'network' => $k,
                    'url' => $soc[$k],
                    'label' => $labels[$k],
                ];
            }
        }
        $out[] = [
            'name' => (string) ($r['name'] ?? ''),
            'role_title' => (string) ($r['role_title'] ?? ''),
            'photo_href' => tcf_trainer_photo_href($r['photo_url'] ?? null),
            'social_links' => $clean,
        ];
    }

    return $out;
}
