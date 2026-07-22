<?php

declare(strict_types=1);

/**
 * Connexion persistante (« Rester connecté ») via cookie HttpOnly + jetons en base.
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

function tcf_remember_ensure_table(PDO $pdo): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS tcf_remember_tokens (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id INT NOT NULL,
            selector VARCHAR(64) NOT NULL,
            token_hash VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_tcf_remember_selector (selector),
            KEY idx_tcf_remember_user (user_id),
            KEY idx_tcf_remember_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $done = true;
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
    if ($userId <= 0) {
        return;
    }
    tcf_remember_ensure_table($pdo);
    try {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
    } catch (Throwable $e) {
        return;
    }
    $hash = hash('sha256', $validator);
    $expiresAt = (new DateTimeImmutable('+' . TCF_REMEMBER_DAYS . ' days'))->format('Y-m-d H:i:s');
    try {
        $pdo->prepare('DELETE FROM tcf_remember_tokens WHERE user_id = ? OR expires_at < NOW()')->execute([$userId]);
        $pdo->prepare(
            'INSERT INTO tcf_remember_tokens (user_id, selector, token_hash, expires_at) VALUES (?, ?, ?, ?)'
        )->execute([$userId, $selector, $hash, $expiresAt]);
    } catch (Throwable $e) {
        return;
    }
    tcf_remember_set_cookie($selector . ':' . $validator, time() + (TCF_REMEMBER_DAYS * 86400));
}

function tcf_remember_revoke_current(PDO $pdo): void
{
    $raw = (string) ($_COOKIE[TCF_REMEMBER_COOKIE] ?? '');
    tcf_remember_clear_cookie();
    if ($raw === '' || strpos($raw, ':') === false) {
        return;
    }
    [$selector] = explode(':', $raw, 2);
    if (!preg_match('/^[a-f0-9]{32}$/', $selector)) {
        return;
    }
    try {
        tcf_remember_ensure_table($pdo);
        $pdo->prepare('DELETE FROM tcf_remember_tokens WHERE selector = ?')->execute([$selector]);
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
    if ($raw === '' || strpos($raw, ':') === false) {
        return;
    }
    [$selector, $validator] = explode(':', $raw, 2);
    if (!preg_match('/^[a-f0-9]{32}$/', $selector) || !preg_match('/^[a-f0-9]{64}$/', $validator)) {
        tcf_remember_clear_cookie();
        return;
    }
    try {
        tcf_remember_ensure_table($pdo);
        $st = $pdo->prepare(
            'SELECT t.id, t.user_id, t.token_hash, t.expires_at, u.*
             FROM tcf_remember_tokens t
             INNER JOIN users u ON u.id = t.user_id
             WHERE t.selector = ?
             LIMIT 1'
        );
        $st->execute([$selector]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            tcf_remember_clear_cookie();
            return;
        }
        if (strtotime((string) $row['expires_at']) < time()) {
            $pdo->prepare('DELETE FROM tcf_remember_tokens WHERE id = ?')->execute([(int) $row['id']]);
            tcf_remember_clear_cookie();
            return;
        }
        if (!hash_equals((string) $row['token_hash'], hash('sha256', $validator))) {
            $pdo->prepare('DELETE FROM tcf_remember_tokens WHERE user_id = ?')->execute([(int) $row['user_id']]);
            tcf_remember_clear_cookie();
            return;
        }
        if (isset($row['status']) && $row['status'] === 'inactive') {
            tcf_remember_revoke_current($pdo);
            return;
        }
        // Rotation du jeton
        $pdo->prepare('DELETE FROM tcf_remember_tokens WHERE id = ?')->execute([(int) $row['id']]);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        $_SESSION['user_id'] = (int) $row['user_id'];
        $_SESSION['username'] = $row['name'] ?? '';
        $_SESSION['email'] = $row['email'] ?? '';
        $_SESSION['role'] = $row['role'] ?? 'user';
        $_SESSION['is_admin'] = in_array($row['role'] ?? '', ['admin', 'super_admin'], true);
        tcf_remember_issue($pdo, (int) $row['user_id']);
    } catch (Throwable $e) {
        // Table absente / DB locale cassée : ignorer
    }
}
