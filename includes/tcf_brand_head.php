<?php
/**
 * Identité navigateur + SEO — favicon, meta, Open Graph, Twitter, JSON-LD.
 * À inclure dans le <head> de chaque page publique ou admin.
 *
 * Optionnel avant include :
 *   $tcf_brand_title, $tcf_brand_desc, $tcf_brand_keywords
 *   $tcf_seo_canonical, $tcf_seo_robots, $tcf_seo_og_type, $tcf_seo_og_image
 *   $tcf_seo_skip_title (true pour ne pas émettre <title>)
 *   $FOOTER_ASSET_PREFIX
 */
require_once __DIR__ . '/tcf_seo.php';

$tcf_brand_asset_base = isset($FOOTER_ASSET_PREFIX) ? $FOOTER_ASSET_PREFIX : '';
if (function_exists('site_href')) {
    $tcf_favicon_href = site_href('Assets/branding/favicon.svg');
    $tcf_favicon_jpg_href = site_href('Assets/branding/favicon.jpg');
    $tcf_manifest_href = site_href('Assets/branding/site.webmanifest');
} else {
    $tcf_favicon_href = $tcf_brand_asset_base . 'Assets/branding/favicon.svg';
    $tcf_favicon_jpg_href = $tcf_brand_asset_base . 'Assets/branding/favicon.jpg';
    $tcf_manifest_href = $tcf_brand_asset_base . 'Assets/branding/site.webmanifest';
}

$tcf_brand_title = $tcf_brand_title ?? (tcf_seo_brand() . ' | Préparation à l\'examen TCF Canada');
$tcf_brand_desc = $tcf_brand_desc ?? tcf_seo_default_description();
$tcf_brand_keywords = $tcf_brand_keywords ?? tcf_seo_default_keywords();
$tcf_seo_robots = $tcf_seo_robots ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
$tcf_seo_og_type = $tcf_seo_og_type ?? 'website';
$tcf_seo_canonical_url = $tcf_seo_canonical ?? tcf_seo_canonical();
$tcf_seo_og_image_url = $tcf_seo_og_image ?? (
    function_exists('site_url')
        ? site_url('Assets/branding/favicon.jpg')
        : $tcf_favicon_jpg_href
);
$tcf_seo_site_url = function_exists('site_url') ? site_url('index.php') : $tcf_seo_canonical_url;
$tcf_seo_skip_title = !empty($tcf_seo_skip_title);

$tcf_seo_jsonld = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Organization',
            '@id' => rtrim($tcf_seo_site_url, '/') . '/#organization',
            'name' => tcf_seo_brand(),
            'alternateName' => ['Elite TCF Canada', 'elitetcfcanada', 'ELITE TCF'],
            'url' => $tcf_seo_site_url,
            'logo' => [
                '@type' => 'ImageObject',
                'url' => $tcf_seo_og_image_url,
            ],
            'description' => tcf_seo_default_description(),
            'email' => 'elitetcfcanada@gmail.com',
            'sameAs' => [],
        ],
        [
            '@type' => 'WebSite',
            '@id' => rtrim($tcf_seo_site_url, '/') . '/#website',
            'name' => tcf_seo_brand(),
            'alternateName' => 'Elite TCF Canada — Préparation TCF Canada',
            'url' => $tcf_seo_site_url,
            'description' => tcf_seo_default_description(),
            'inLanguage' => 'fr-FR',
            'publisher' => [
                '@id' => rtrim($tcf_seo_site_url, '/') . '/#organization',
            ],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => rtrim(function_exists('site_url') ? site_url('videos.php') : $tcf_seo_site_url, '/') . '?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ],
        [
            '@type' => 'WebPage',
            '@id' => $tcf_seo_canonical_url . '#webpage',
            'url' => $tcf_seo_canonical_url,
            'name' => $tcf_brand_title,
            'description' => $tcf_brand_desc,
            'isPartOf' => [
                '@id' => rtrim($tcf_seo_site_url, '/') . '/#website',
            ],
            'inLanguage' => 'fr-FR',
        ],
    ],
];
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<?php if (!$tcf_seo_skip_title): ?>
<title><?php echo htmlspecialchars($tcf_brand_title, ENT_QUOTES, 'UTF-8'); ?></title>
<?php endif; ?>
<meta name="description" content="<?php echo htmlspecialchars($tcf_brand_desc, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="keywords" content="<?php echo htmlspecialchars($tcf_brand_keywords, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="author" content="<?php echo htmlspecialchars(tcf_seo_brand(), ENT_QUOTES, 'UTF-8'); ?>">
<meta name="robots" content="<?php echo htmlspecialchars($tcf_seo_robots, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="googlebot" content="<?php echo htmlspecialchars($tcf_seo_robots, ENT_QUOTES, 'UTF-8'); ?>">
<link rel="canonical" href="<?php echo htmlspecialchars($tcf_seo_canonical_url, ENT_QUOTES, 'UTF-8'); ?>">
<link rel="icon" href="<?php echo htmlspecialchars($tcf_favicon_href, ENT_QUOTES, 'UTF-8'); ?>" type="image/svg+xml" sizes="any">
<link rel="alternate icon" href="<?php echo htmlspecialchars($tcf_favicon_jpg_href, ENT_QUOTES, 'UTF-8'); ?>" type="image/jpeg" sizes="512x512">
<link rel="apple-touch-icon" href="<?php echo htmlspecialchars($tcf_favicon_jpg_href, ENT_QUOTES, 'UTF-8'); ?>" sizes="512x512">
<link rel="manifest" href="<?php echo htmlspecialchars($tcf_manifest_href, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="application-name" content="<?php echo htmlspecialchars(tcf_seo_brand(), ENT_QUOTES, 'UTF-8'); ?>">
<meta name="apple-mobile-web-app-title" content="ELITE TCF">
<meta name="theme-color" content="#c8102e" id="tcf-theme-color-meta">
<meta name="format-detection" content="telephone=no">
<meta name="language" content="French">
<meta name="geo.region" content="CA">
<meta name="geo.placename" content="Canada">
<meta property="og:locale" content="fr_FR">
<meta property="og:site_name" content="<?php echo htmlspecialchars(tcf_seo_brand(), ENT_QUOTES, 'UTF-8'); ?>">
<meta property="og:title" content="<?php echo htmlspecialchars($tcf_brand_title, ENT_QUOTES, 'UTF-8'); ?>">
<meta property="og:description" content="<?php echo htmlspecialchars($tcf_brand_desc, ENT_QUOTES, 'UTF-8'); ?>">
<meta property="og:type" content="<?php echo htmlspecialchars($tcf_seo_og_type, ENT_QUOTES, 'UTF-8'); ?>">
<meta property="og:url" content="<?php echo htmlspecialchars($tcf_seo_canonical_url, ENT_QUOTES, 'UTF-8'); ?>">
<meta property="og:image" content="<?php echo htmlspecialchars($tcf_seo_og_image_url, ENT_QUOTES, 'UTF-8'); ?>">
<meta property="og:image:alt" content="<?php echo htmlspecialchars(tcf_seo_brand() . ' — Préparation TCF Canada', ENT_QUOTES, 'UTF-8'); ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo htmlspecialchars($tcf_brand_title, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="twitter:description" content="<?php echo htmlspecialchars($tcf_brand_desc, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="twitter:image" content="<?php echo htmlspecialchars($tcf_seo_og_image_url, ENT_QUOTES, 'UTF-8'); ?>">
<script type="application/ld+json"><?php echo json_encode($tcf_seo_jsonld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP); ?></script>
