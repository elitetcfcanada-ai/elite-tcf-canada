<?php
declare(strict_types=1);

/**
 * SEO ELITE TCF CANADA — titres, descriptions, mots-clés, URLs absolues.
 */

function tcf_seo_brand(): string
{
    return 'ELITE TCF CANADA';
}

/** Mots-clés de base (marque + intention de recherche). */
function tcf_seo_default_keywords(): string
{
    return implode(', ', [
        'ELITE TCF CANADA',
        'elite tcf canada',
        'Elite TCF Canada',
        'TCF Canada',
        'préparation TCF Canada',
        'examen TCF Canada',
        'TCF IRCC',
        'test de connaissance du français',
        'compréhension écrite TCF',
        'compréhension orale TCF',
        'expression écrite TCF',
        'expression orale TCF',
        'entraînement TCF Canada',
        'immigration Canada français',
        'préparer le TCF',
        'sujets TCF Canada',
        'quiz TCF Canada',
        'formation TCF Canada',
        'elitetcfcanada',
        'elitetcfcanada.online',
    ]);
}

function tcf_seo_default_description(): string
{
    return 'ELITE TCF CANADA — plateforme de préparation à l\'examen TCF Canada : '
        . 'compréhension écrite et orale, expression écrite et orale, vidéos et entraînements pour réussir.';
}

/**
 * URL canonique absolue de la page courante (ou chemin fourni).
 */
function tcf_seo_canonical(?string $path = null): string
{
    if ($path !== null && $path !== '') {
        return function_exists('site_url') ? site_url(ltrim($path, '/')) : $path;
    }

    if (function_exists('site_url')) {
        $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
        $qs = (string) ($_SERVER['QUERY_STRING'] ?? '');
        // Pages publiques indexables : pas de query string dans le canonique (sauf watch?v=)
        if ($script === 'watch.php' && $qs !== '' && preg_match('/(?:^|&)v=(\d+)/', $qs, $m)) {
            return site_url('watch.php?v=' . (int) $m[1]);
        }
        if (in_array($script, ['index.php', ''], true)) {
            return site_url('index.php');
        }
        return site_url($script);
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');

    return $scheme . '://' . $host . $uri;
}

/**
 * @return list<array{loc:string, priority:string, changefreq:string}>
 */
function tcf_seo_sitemap_entries(): array
{
    $pages = [
        ['index.php', '1.0', 'daily'],
        ['videos.php', '0.9', 'weekly'],
        ['comprehesion_ecrite.php', '0.9', 'weekly'],
        ['comprehension_orale.php', '0.9', 'weekly'],
        ['Expresion_ecrite.php', '0.9', 'weekly'],
        ['Expresion_orale.php', '0.9', 'weekly'],
        ['abonnement.php', '0.8', 'monthly'],
        ['posts.php', '0.7', 'weekly'],
        ['support.php', '0.6', 'monthly'],
    ];

    $out = [];
    foreach ($pages as [$file, $prio, $freq]) {
        $out[] = [
            'loc' => function_exists('site_url') ? site_url($file) : '/' . $file,
            'priority' => $prio,
            'changefreq' => $freq,
        ];
    }

    return $out;
}
