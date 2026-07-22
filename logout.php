<?php

require_once __DIR__ . '/includes/config.php';

if (function_exists('tcf_remember_revoke_current')) {
    tcf_remember_revoke_current($pdo);
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $p['path'] ?? '/',
        'domain' => $p['domain'] ?? '',
        'secure' => !empty($p['secure']),
        'httponly' => !empty($p['httponly']),
        'samesite' => $p['samesite'] ?? 'Lax',
    ]);
}
session_destroy();

header('Location: ' . site_href('index.php'));
exit;
