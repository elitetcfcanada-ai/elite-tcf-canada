<?php
declare(strict_types=1);

/**
 * Normalise le contenu riche (décodage si stocké en entités HTML).
 */
function tcf_normalize_rich(?string $text): string
{
    $t = trim((string) $text);
    if ($t === '') {
        return '';
    }

    if (preg_match('/&lt;\/?[a-z]/i', $t) && !preg_match('/<[a-z]/i', $t)) {
        $t = html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    return $t;
}

/**
 * Affiche du contenu admin/seed : HTML interprété, ou texte brut avec retours à la ligne.
 */
function tcf_format_rich(?string $text): string
{
    $t = tcf_normalize_rich($text);
    if ($t === '') {
        return '';
    }

    if (preg_match('/<[a-z][\s\S]*>/i', $t)) {
        return $t;
    }

    return nl2br(htmlspecialchars($t, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), false);
}
