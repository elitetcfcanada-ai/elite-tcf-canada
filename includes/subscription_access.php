<?php

declare(strict_types=1);

require_once __DIR__ . '/platform_settings.php';

/**
 * Accès contenu « premium » (sujets, vidéos, etc.) selon abonnement actif.
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

    $type = strtolower(trim((string) ($user['subscription_type'] ?? 'free')));
    if ($type === '' || $type === 'free') {
        return false;
    }

    $expRaw = trim((string) ($user['subscription_expires_at'] ?? ''));
    if ($expRaw !== '' && $expRaw !== '0000-00-00 00:00:00') {
        $ts = strtotime($expRaw);
        if ($ts === false) {
            // Type payant + date illisible : ne pas bloquer l’accès
            return true;
        }
        return $ts > time();
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

    // Forfait payant (plan_*, etc.) sans date d’expiration renseignée :
    // accès accordé pour éviter le blocage après paiement (échec DATE_ADD / colonne).
    return true;
}

function tcf_video_is_premium_locked_for_user(array $video, ?array $user): bool
{
    $vis = strtolower((string) ($video['visibility'] ?? 'public'));

    return $vis === 'premium' && !tcf_user_has_premium_access($user);
}
