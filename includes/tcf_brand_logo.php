<?php

declare(strict_types=1);

/** Chemin relatif du logo officiel du site. */
function tcf_brand_logo_path(): string
{
    return 'Assets/branding/favicon.svg';
}

/** URL publique du logo (site_href ou préfixe relatif). */
function tcf_brand_logo_href(?string $assetPrefix = null): string
{
    $path = tcf_brand_logo_path();
    if ($assetPrefix !== null && $assetPrefix !== '') {
        return $assetPrefix . $path;
    }
    if (function_exists('site_href')) {
        return site_href($path);
    }
    return $path;
}

/**
 * Balise <img> du logo ELITE TCF CANADA.
 *
 * @param array{class?: string, size?: int, alt?: string, prefix?: string|null} $options
 */
function tcf_brand_logo_img(array $options = []): string
{
    $class = (string) ($options['class'] ?? 'tcf-brand-logo');
    $size = max(16, (int) ($options['size'] ?? 36));
    $alt = (string) ($options['alt'] ?? 'ELITE TCF CANADA');
    $prefix = array_key_exists('prefix', $options) ? ($options['prefix'] !== null ? (string) $options['prefix'] : null) : null;
    $href = tcf_brand_logo_href($prefix);

    return sprintf(
        '<img src="%s" alt="%s" class="%s" width="%d" height="%d" decoding="async" loading="lazy">',
        htmlspecialchars($href, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($alt, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($class, ENT_QUOTES, 'UTF-8'),
        $size,
        $size
    );
}
