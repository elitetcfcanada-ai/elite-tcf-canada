<?php

declare(strict_types=1);

/**
 * Réglages globaux de la plateforme (ex. mode gratuit temporaire).
 */
function tcf_platform_settings_ensure(PDO $pdo): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS tcf_platform_settings (
            setting_key VARCHAR(64) NOT NULL,
            setting_value VARCHAR(255) NOT NULL DEFAULT \'\',
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (setting_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $defaults = [
        'subscriptions_disabled' => '0',
    ];
    $st = $pdo->prepare('SELECT 1 FROM tcf_platform_settings WHERE setting_key = ?');
    $ins = $pdo->prepare('INSERT INTO tcf_platform_settings (setting_key, setting_value) VALUES (?, ?)');
    foreach ($defaults as $key => $val) {
        $st->execute([$key]);
        if (!$st->fetchColumn()) {
            $ins->execute([$key, $val]);
        }
    }
    $done = true;
}

function tcf_platform_setting_get(PDO $pdo, string $key, string $default = ''): string
{
    tcf_platform_settings_ensure($pdo);
    try {
        $st = $pdo->prepare('SELECT setting_value FROM tcf_platform_settings WHERE setting_key = ? LIMIT 1');
        $st->execute([$key]);
        $v = $st->fetchColumn();
        return $v !== false ? (string) $v : $default;
    } catch (Throwable $e) {
        return $default;
    }
}

function tcf_platform_setting_set(PDO $pdo, string $key, string $value): void
{
    tcf_platform_settings_ensure($pdo);
    $st = $pdo->prepare(
        'INSERT INTO tcf_platform_settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $st->execute([$key, $value]);
    tcf_platform_settings_bump_cache();
}

/** Invalide les caches statiques des helpers de réglages (après écriture). */
function tcf_platform_settings_bump_cache(): void
{
    $GLOBALS['tcf_platform_settings_cache_gen'] = (int) ($GLOBALS['tcf_platform_settings_cache_gen'] ?? 0) + 1;
}

function tcf_platform_settings_cache_generation(): int
{
    return (int) ($GLOBALS['tcf_platform_settings_cache_gen'] ?? 0);
}

/** Abonnements désactivés : tout le contenu premium est accessible sans paiement. */
function tcf_subscriptions_platform_disabled(?PDO $pdo = null): bool
{
    static $cache = null;
    static $cacheGen = -1;
    $gen = tcf_platform_settings_cache_generation();
    if ($cache !== null && $cacheGen === $gen) {
        return $cache;
    }
    if ($pdo === null) {
        global $pdo;
    }
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        $cache = false;
        $cacheGen = $gen;
        return false;
    }
    $cache = tcf_platform_setting_get($pdo, 'subscriptions_disabled', '0') === '1';
    $cacheGen = $gen;
    return $cache;
}

/** Vente / cartes d’abonnement visibles côté utilisateur. */
function tcf_subscription_sales_enabled(?PDO $pdo = null): bool
{
    return !tcf_subscriptions_platform_disabled($pdo);
}
