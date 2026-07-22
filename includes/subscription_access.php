<?php

declare(strict_types=1);

require_once __DIR__ . '/platform_settings.php';

/**
 * Accès contenu « premium » (vidéos chaîne, etc.) selon abonnement actif.
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
    $type = (string) ($user['subscription_type'] ?? 'free');
    if ($type === '' || $type === 'free') {
        return false;
    }
    $expRaw = $user['subscription_expires_at'] ?? null;
    if ($expRaw !== null && $expRaw !== '') {
        $ts = strtotime((string) $expRaw);
        return $ts !== false && $ts > time();
    }
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
    if (preg_match('/^plan_[a-z0-9]+$/', $type)) {
        $expPlan = $user['subscription_expires_at'] ?? null;
        if ($expPlan === null || $expPlan === '') {
            return false;
        }
        $tsP = strtotime((string) $expPlan);

        return $tsP !== false && $tsP > time();
    }

    return false;
}

function tcf_video_is_premium_locked_for_user(array $video, ?array $user): bool
{
    $vis = strtolower((string) ($video['visibility'] ?? 'public'));

    return $vis === 'premium' && !tcf_user_has_premium_access($user);
}
