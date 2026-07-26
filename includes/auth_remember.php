<?php

declare(strict_types=1);

/**
 * Connexion persistante (« Rester connecté ») via cookie HttpOnly.
 * Stockage consolidé : users.remember_token + users.remember_expires_at
 * (format cookie : selector:validator ; remember_token = selector:sha256(validator))
 */

const TCF_REMEMBER_COOKIE = 'tcf_remember';
const TCF_REMEMBER_DAYS = 60;

function tcf_remember_cookie_path(): string
{
    $base = function_exists('tcf_base_uri') ? tcf_base_uri() : '';
    return $base !== '' ? rtrim($base, '/') . '/' : '/';
}

function tcf_remember_is_https(): bool
{
    return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
}

function tcf_remember_users_columns_ready(PDO $pdo): bool
{
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    try {
        $cols = $pdo->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);
        $ok = in_array('remember_token', $cols, true) && in_array('remember_expires_at', $cols, true);
    } catch (Throwable $e) {
        $ok = false;
    }
    return $ok;
}

function tcf_remember_set_cookie(string $value, int $expires): void
{
    if (headers_sent()) {
        return;
    }
    $params = [
        'expires' => $expires,
        'path' => tcf_remember_cookie_path(),
        'httponly' => true,
        'samesite' => 'Lax',
    ];
    if (tcf_remember_is_https()) {
        $params['secure'] = true;
    }
    setcookie(TCF_REMEMBER_COOKIE, $value, $params);
    if ($expires > time()) {
        $_COOKIE[TCF_REMEMBER_COOKIE] = $value;
    } else {
        unset($_COOKIE[TCF_REMEMBER_COOKIE]);
    }
}

function tcf_remember_clear_cookie(): void
{
    tcf_remember_set_cookie('', time() - 3600);
}

function tcf_remember_issue(PDO $pdo, int $userId): void
{
    if ($userId <= 0 || !tcf_remember_users_columns_ready($pdo)) {
        return;
    }
    try {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
    } catch (Throwable $e) {
        return;
    }
    $hash = hash('sha256', $validator);
    $expiresAt = (new DateTimeImmutable('+' . TCF_REMEMBER_DAYS . ' days'))->format('Y-m-d H:i:s');
    try {
        $pdo->prepare('UPDATE users SET remember_token = ?, remember_expires_at = ? WHERE id = ?')
            ->execute([$selector . ':' . $hash, $expiresAt, $userId]);
    } catch (Throwable $e) {
        return;
    }
    tcf_remember_set_cookie($selector . ':' . $validator, time() + (TCF_REMEMBER_DAYS * 86400));
}

function tcf_remember_revoke_current(PDO $pdo): void
{
    $raw = (string) ($_COOKIE[TCF_REMEMBER_COOKIE] ?? '');
    tcf_remember_clear_cookie();
    if ($raw === '' || strpos($raw, ':') === false || !tcf_remember_users_columns_ready($pdo)) {
        return;
    }
    [$selector] = explode(':', $raw, 2);
    if (!preg_match('/^[a-f0-9]{32}$/', $selector)) {
        return;
    }
    try {
        $st = $pdo->prepare('SELECT id, remember_token FROM users WHERE remember_token LIKE ? LIMIT 1');
        $st->execute([$selector . ':%']);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $pdo->prepare('UPDATE users SET remember_token = NULL, remember_expires_at = NULL WHERE id = ?')
                ->execute([(int) $row['id']]);
        }
    } catch (Throwable $e) {
        // ignore
    }
}

/**
 * Si la session est vide mais le cookie remember est valide → reconnecte.
 */
function tcf_remember_try_resume(PDO $pdo): void
{
    if (!empty($_SESSION['user_id'])) {
        return;
    }
    $raw = (string) ($_COOKIE[TCF_REMEMBER_COOKIE] ?? '');
    if ($raw === '' || strpos($raw, ':') === false || !tcf_remember_users_columns_ready($pdo)) {
        return;
    }
    [$selector, $validator] = explode(':', $raw, 2);
    if (!preg_match('/^[a-f0-9]{32}$/', $selector) || !preg_match('/^[a-f0-9]{64}$/', $validator)) {
        tcf_remember_clear_cookie();
        return;
    }
    try {
        $st = $pdo->prepare(
            'SELECT * FROM users WHERE remember_token LIKE ? AND remember_expires_at IS NOT NULL AND remember_expires_at > NOW() LIMIT 1'
        );
        $st->execute([$selector . ':%']);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            tcf_remember_clear_cookie();
            return;
        }
        $stored = (string) ($row['remember_token'] ?? '');
        $parts = explode(':', $stored, 2);
        $tokenHash = $parts[1] ?? '';
        if ($tokenHash === '' || !hash_equals($tokenHash, hash('sha256', $validator))) {
            $pdo->prepare('UPDATE users SET remember_token = NULL, remember_expires_at = NULL WHERE id = ?')
                ->execute([(int) $row['id']]);
            tcf_remember_clear_cookie();
            return;
        }
        if (isset($row['status']) && $row['status'] === 'inactive') {
            tcf_remember_revoke_current($pdo);
            return;
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        $_SESSION['user_id'] = (int) $row['id'];
        $_SESSION['username'] = $row['name'] ?? '';
        $_SESSION['email'] = $row['email'] ?? '';
        $_SESSION['role'] = $row['role'] ?? 'user';
        $_SESSION['is_admin'] = in_array($row['role'] ?? '', ['admin', 'super_admin'], true);
        tcf_remember_issue($pdo, (int) $row['id']);
    } catch (Throwable $e) {
        // ignore
    }
}
