<?php
/**
 * Identifiant stable d'un visiteur (connecté ou anonyme), stocké dans un
 * cookie HttpOnly à durée longue. Utilisé pour dédupliquer les vues d'épreuves
 * lorsque l'utilisateur n'est pas connecté.
 */

declare(strict_types=1);

if (!function_exists('tcf_visitor_id')) {

    function tcf_visitor_id(): string
    {
        if (!empty($_COOKIE['tcf_vid'])) {
            $existing = (string) $_COOKIE['tcf_vid'];
            if (preg_match('/^[a-f0-9]{32}$/', $existing)) {
                return $existing;
            }
        }

        try {
            $vid = bin2hex(random_bytes(16));
        } catch (Throwable $e) {
            $vid = bin2hex((string) openssl_random_pseudo_bytes(16));
        }

        $base = function_exists('tcf_base_uri') ? tcf_base_uri() : '';
        $cookiePath = $base !== '' ? $base . '/' : '/';

        $params = [
            'expires' => time() + (86400 * 365 * 2),
            'path' => $cookiePath,
            'httponly' => true,
            'samesite' => 'Lax',
        ];
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $params['secure'] = true;
        }

        if (!headers_sent()) {
            @setcookie('tcf_vid', $vid, $params);
        }
        $_COOKIE['tcf_vid'] = $vid;

        return $vid;
    }
}
