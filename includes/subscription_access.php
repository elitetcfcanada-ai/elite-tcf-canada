<?php

declare(strict_types=1);

require_once __DIR__ . '/platform_settings.php';

/**
 * Les forfaits admin (plan_c_*) dépassent l’ancien ENUM users.subscription_type.
 * À appeler tôt (config) pour que chaque requête puisse enregistrer un vrai plan_key.
 */
function tcf_users_ensure_subscription_type_varchar(PDO $pdo): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    try {
        $st = $pdo->query("SHOW COLUMNS FROM users LIKE 'subscription_type'");
        $col = $st ? $st->fetch(PDO::FETCH_ASSOC) : false;
        if (!$col) {
            return;
        }
        $type = strtolower((string) ($col['Type'] ?? ''));
        if (str_starts_with($type, 'varchar') || str_starts_with($type, 'char') || str_contains($type, 'text')) {
            return;
        }
        $pdo->exec("ALTER TABLE users MODIFY subscription_type VARCHAR(64) NOT NULL DEFAULT 'free'");
    } catch (Throwable $e) {
        error_log('tcf_users_ensure_subscription_type_varchar: ' . $e->getMessage());
        $done = false;
    }
}

/**
 * Répare les comptes dont expires_at est futur mais subscription_type vide/free (bug ENUM).
 *
 * @return int Nombre de lignes corrigées
 */
function tcf_repair_users_with_active_expiry(PDO $pdo): int
{
    tcf_users_ensure_subscription_type_varchar($pdo);
    $fixed = 0;
    try {
        $st = $pdo->query(
            "SELECT id, subscription_type, subscription_expires_at
             FROM users
             WHERE role = 'user'
               AND subscription_expires_at IS NOT NULL
               AND subscription_expires_at > NOW()
               AND (
                 subscription_type IS NULL
                 OR TRIM(subscription_type) = ''
                 OR subscription_type = 'free'
               )
             LIMIT 200"
        );
        $rows = $st ? ($st->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
    } catch (Throwable $e) {
        return 0;
    }

    $upd = $pdo->prepare('UPDATE users SET subscription_type = ? WHERE id = ? AND role = \'user\'');
    $hist = null;
    try {
        require_once __DIR__ . '/tcf_legacy_tables.php';
        if (tcf_historique_abonnements_available($pdo)) {
            $hist = $pdo->prepare(
                "SELECT plan_key FROM historique_abonnements
                 WHERE user_id = ? AND plan_key IS NOT NULL AND plan_key <> ''
                   AND LOWER(status) IN ('paid','completed','success','complete')
                 ORDER BY id DESC LIMIT 1"
            );
        }
    } catch (Throwable $e) {
        $hist = null;
    }

    foreach ($rows as $row) {
        $uid = (int) ($row['id'] ?? 0);
        if ($uid <= 0) {
            continue;
        }
        $planKey = 'plan_1w';
        if ($hist) {
            try {
                $hist->execute([$uid]);
                $pk = trim((string) ($hist->fetchColumn() ?: ''));
                if ($pk !== '' && strtolower($pk) !== 'free') {
                    $planKey = $pk;
                }
            } catch (Throwable $e) {
            }
        }
        try {
            $upd->execute([$planKey, $uid]);
            $fixed++;
        } catch (Throwable $e) {
        }
    }

    return $fixed;
}

/**
 * True si la date d’expiration (chaîne MySQL / DateTime) est encore dans le futur.
 */
function tcf_subscription_expiry_is_active(mixed $expiresAt): bool
{
    if ($expiresAt === null || $expiresAt === false) {
        return false;
    }
    if ($expiresAt instanceof DateTimeInterface) {
        return $expiresAt->getTimestamp() > time();
    }
    $expRaw = trim((string) $expiresAt);
    if ($expRaw === '' || str_starts_with($expRaw, '0000-00-00')) {
        return false;
    }
    try {
        return (new DateTimeImmutable($expRaw))->getTimestamp() > time();
    } catch (Throwable $e) {
        $ts = strtotime($expRaw);

        return $ts !== false && $ts > time();
    }
}

/**
 * Accès contenu « premium » (sujets, vidéos, etc.) selon abonnement actif.
 *
 * Règle prioritaire : une date d’expiration future = accès actif
 * (même si subscription_type a été vidé par un ancien ENUM MySQL).
 */
function tcf_user_has_premium_access(?array $user): bool
{
    global $pdo;
    if (isset($pdo) && $pdo instanceof PDO && tcf_subscriptions_platform_disabled($pdo)) {
        return true;
    }
    if ($user === null || $user === []) {
        return false;
    }
    $role = (string) ($user['role'] ?? '');
    if (in_array($role, ['admin', 'super_admin'], true)) {
        return true;
    }

    // Priorité absolue : période payante encore valide en base.
    if (tcf_subscription_expiry_is_active($user['subscription_expires_at'] ?? null)) {
        return true;
    }

    $type = strtolower(trim((string) ($user['subscription_type'] ?? 'free')));
    if ($type === '' || $type === 'free') {
        return false;
    }

    $expRaw = trim((string) ($user['subscription_expires_at'] ?? ''));
    if ($expRaw !== '' && $expRaw !== '0000-00-00 00:00:00') {
        // Date présente mais déjà expirée (branche ci-dessus a échoué)
        return false;
    }

    // Compat anciens comptes monthly/annual sans expires_at
    if (in_array($type, ['monthly', 'annual'], true)) {
        $created = $user['created_at'] ?? null;
        if ($created === null || $created === '') {
            return true;
        }
        try {
            $start = new DateTime((string) $created);
            $end = clone $start;
            if ($type === 'monthly') {
                $end->modify('+1 month');
            } else {
                $end->modify('+1 year');
            }
            return new DateTime() < $end;
        } catch (Throwable $e) {
            return true;
        }
    }

    // Forfait payant (plan_*, etc.) sans date d’expiration renseignée.
    return true;
}

function tcf_video_is_premium_locked_for_user(array $video, ?array $user): bool
{
    $vis = strtolower((string) ($video['visibility'] ?? 'public'));

    return $vis === 'premium' && !tcf_user_has_premium_access($user);
}
