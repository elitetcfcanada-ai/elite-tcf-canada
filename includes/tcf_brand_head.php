<?php
/**
 * Identité navigateur — favicon, couleur d’onglet, PWA (site + admin).
 * À inclure dans le <head> de chaque page publique ou admin.
 *
 * Optionnel avant include : $tcf_brand_title, $tcf_brand_desc, $FOOTER_ASSET_PREFIX
 */
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
$tcf_brand_title = $tcf_brand_title ?? 'ELITE TCF CANADA';
$tcf_brand_desc = $tcf_brand_desc ?? 'Préparation à l\'examen TCF Canada — compréhension et expression, écrites et orales.';
?>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link rel="icon" href="<?php echo htmlspecialchars($tcf_favicon_href, ENT_QUOTES, 'UTF-8'); ?>" type="image/svg+xml" sizes="any">
<link rel="alternate icon" href="<?php echo htmlspecialchars($tcf_favicon_jpg_href, ENT_QUOTES, 'UTF-8'); ?>" type="image/jpeg" sizes="512x512">
<link rel="apple-touch-icon" href="<?php echo htmlspecialchars($tcf_favicon_jpg_href, ENT_QUOTES, 'UTF-8'); ?>" sizes="512x512">
<link rel="manifest" href="<?php echo htmlspecialchars($tcf_manifest_href, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="application-name" content="ELITE TCF CANADA">
<meta name="apple-mobile-web-app-title" content="ELITE TCF">
<meta name="theme-color" content="#141622" id="tcf-theme-color-meta">
<meta name="description" content="<?php echo htmlspecialchars($tcf_brand_desc, ENT_QUOTES, 'UTF-8'); ?>">
<meta property="og:site_name" content="ELITE TCF CANADA">
<meta property="og:title" content="<?php echo htmlspecialchars($tcf_brand_title, ENT_QUOTES, 'UTF-8'); ?>">
<meta property="og:description" content="<?php echo htmlspecialchars($tcf_brand_desc, ENT_QUOTES, 'UTF-8'); ?>">
<meta property="og:type" content="website">
<meta property="og:image" content="<?php echo htmlspecialchars($tcf_favicon_jpg_href, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="<?php echo htmlspecialchars($tcf_brand_title, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="twitter:description" content="<?php echo htmlspecialchars($tcf_brand_desc, ENT_QUOTES, 'UTF-8'); ?>">
<meta name="twitter:image" content="<?php echo htmlspecialchars($tcf_favicon_jpg_href, ENT_QUOTES, 'UTF-8'); ?>">
