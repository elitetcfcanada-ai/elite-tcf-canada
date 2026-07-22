<?php

declare(strict_types=1);

/**
 * Insertion générique dans `notifications` (site + admin).
 */
function tcf_notification_insert(PDO $pdo, ?int $user_id, string $type, string $title, string $content, ?string $deep_link = null): void
{
    try {
        if ($deep_link !== null && $deep_link !== '') {
            $stmt = $pdo->prepare('INSERT INTO notifications (user_id, type, title, content, deep_link) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$user_id, $type, $title, $content, $deep_link]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO notifications (user_id, type, title, content) VALUES (?, ?, ?, ?)');
            $stmt->execute([$user_id, $type, $title, $content]);
        }
    } catch (Throwable $e) {
        error_log('tcf_notification_insert: ' . $e->getMessage());
    }
}

/**
 * Message de bienvenue pour un apprenant (idempotent).
 */
function tcf_ensure_welcome_notification(PDO $pdo, ?array $user): void
{
    if (!$user || empty($user['id'])) {
        return;
    }
    if (($user['role'] ?? '') !== 'user') {
        return;
    }
    $uid = (int) $user['id'];
    try {
        $st = $pdo->prepare(
            "SELECT 1 FROM notifications WHERE user_id = ? AND type = 'welcome' LIMIT 1"
        );
        $st->execute([$uid]);
        if ($st->fetchColumn()) {
            return;
        }
    } catch (Throwable $e) {
        return;
    }

    $link = function_exists('site_href') ? site_href('index.php') : 'index.php';
    tcf_notification_insert(
        $pdo,
        $uid,
        'welcome',
        'Bienvenue sur ELITE TCF Canada',
        'Votre compte est prêt. Explorez les épreuves, les vidéos et suivez votre progression.',
        $link
    );
}

/**
 * Notifie uniquement les comptes `user` déjà inscrits à l’instant donné
 * (vidéos, épreuves, annonces — les inscriptions ultérieures ne reçoivent pas cette alerte).
 */
function tcf_notify_users_registered_before(
    PDO $pdo,
    string $type,
    string $title,
    string $content,
    ?string $deep_link = null,
    ?string $beforeDatetime = null
): int {
    $before = $beforeDatetime ?? date('Y-m-d H:i:s');
    try {
        $stmt = $pdo->prepare(
            "SELECT id FROM users
             WHERE role = 'user' AND id > 0
               AND created_at IS NOT NULL AND created_at <= ?
               AND (status IS NULL OR status = '' OR status = 'active')"
        );
        $stmt->execute([$before]);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e) {
        error_log('tcf_notify_users_registered_before: ' . $e->getMessage());
        return 0;
    }

    $n = 0;
    foreach ($ids as $id) {
        tcf_notification_insert($pdo, (int) $id, $type, $title, $content, $deep_link);
        $n++;
    }

    return $n;
}

/**
 * Supprime les notifications dont le deep_link contient un fragment
 * (ex. "exam_id=12", "watch.php?v=3", "epreuve_ee.php?id=5").
 */
function tcf_delete_notifications_matching(PDO $pdo, string $deepLinkFragment): int
{
    $frag = trim($deepLinkFragment);
    if ($frag === '') {
        return 0;
    }
    try {
        $st = $pdo->prepare(
            'DELETE FROM notifications WHERE deep_link IS NOT NULL AND deep_link LIKE ?'
        );
        $st->execute(['%' . $frag . '%']);
        return (int) $st->rowCount();
    } catch (Throwable $e) {
        error_log('tcf_delete_notifications_matching: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Supprime les notifications d’un type donné filtrées par titre et/ou contenu
 * (utile quand le deep_link est générique, ex. posts.php).
 */
function tcf_delete_notifications_by_type_payload(
    PDO $pdo,
    string $type,
    ?string $title = null,
    ?string $content = null
): int {
    $type = trim($type);
    if ($type === '') {
        return 0;
    }
    $title = $title !== null ? trim($title) : null;
    $content = $content !== null ? trim($content) : null;
    if (($title === null || $title === '') && ($content === null || $content === '')) {
        return 0;
    }
    try {
        $sql = 'DELETE FROM notifications WHERE type = ?';
        $params = [$type];
        if ($title !== null && $title !== '') {
            $sql .= ' AND title = ?';
            $params[] = $title;
        }
        if ($content !== null && $content !== '') {
            $sql .= ' AND content = ?';
            $params[] = $content;
        }
        $st = $pdo->prepare($sql);
        $st->execute($params);
        return (int) $st->rowCount();
    } catch (Throwable $e) {
        error_log('tcf_delete_notifications_by_type_payload: ' . $e->getMessage());
        return 0;
    }
}

function tcf_notification_exists_recent(
    PDO $pdo,
    int $userId,
    string $type,
    string $title,
    int $withinDays = 30
): bool {
    try {
        $st = $pdo->prepare(
            "SELECT 1 FROM notifications
             WHERE user_id = ? AND type = ? AND title = ?
               AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             LIMIT 1"
        );
        $st->execute([$userId, $type, $title, max(1, $withinDays)]);
        return (bool) $st->fetchColumn();
    } catch (Throwable $e) {
        return true;
    }
}

/**
 * Rappels abonnement : J-7, J-3, J-1 et expiration.
 * Appelé à chaque chargement de page (header) — dédupliqué.
 */
function tcf_maybe_notify_subscription_reminders(PDO $pdo, ?array $user): void
{
    if (!$user || empty($user['id'])) {
        return;
    }
    if (($user['role'] ?? '') !== 'user') {
        return;
    }
    $type = (string) ($user['subscription_type'] ?? 'free');
    if ($type === '' || $type === 'free') {
        return;
    }

    $expRaw = (string) ($user['subscription_expires_at'] ?? '');
    if ($expRaw === '') {
        return;
    }
    $expTs = strtotime($expRaw);
    if ($expTs === false) {
        return;
    }

    $uid = (int) $user['id'];
    $link = function_exists('site_href') ? site_href('abonnement.php') : 'abonnement.php';
    $expFmt = date('d/m/Y', $expTs);

    try {
        $today = new DateTimeImmutable('today');
        $expDay = new DateTimeImmutable(date('Y-m-d', $expTs));
        $daysLeft = (int) $today->diff($expDay)->format('%r%a');
    } catch (Throwable $e) {
        return;
    }

    $milestones = [
        7 => [
            'title' => 'Abonnement : 7 jours restants',
            'content' => 'Votre abonnement expire dans 7 jours (le ' . $expFmt . '). Renouvelez pour garder l’accès premium.',
        ],
        3 => [
            'title' => 'Abonnement : 3 jours restants',
            'content' => 'Votre abonnement expire dans 3 jours (le ' . $expFmt . '). Renouvelez pour garder l’accès premium.',
        ],
        1 => [
            'title' => 'Abonnement : 1 jour restant',
            'content' => 'Votre abonnement expire demain (le ' . $expFmt . '). Renouvelez pour garder l’accès premium.',
        ],
    ];

    if ($daysLeft > 0 && isset($milestones[$daysLeft])) {
        $m = $milestones[$daysLeft];
        if (!tcf_notification_exists_recent($pdo, $uid, 'subscription', $m['title'], 45)) {
            tcf_notification_insert($pdo, $uid, 'subscription', $m['title'], $m['content'], $link);
        }
        return;
    }

    // Expiré (jour J ou après)
    if ($daysLeft < 0 || ($daysLeft === 0 && $expTs <= time())) {
        tcf_maybe_notify_subscription_expired($pdo, $user);
    }
}

/**
 * Ajoute une notification utilisateur quand l'abonnement vient d'expirer.
 * Conservé pour compatibilité ; préférer tcf_maybe_notify_subscription_reminders.
 */
function tcf_maybe_notify_subscription_expired(PDO $pdo, ?array $user): void
{
    if (!$user || empty($user['id'])) {
        return;
    }
    if (($user['role'] ?? '') !== 'user') {
        return;
    }
    $type = (string) ($user['subscription_type'] ?? 'free');
    if ($type === '' || $type === 'free') {
        return;
    }
    if (function_exists('tcf_user_has_premium_access') && tcf_user_has_premium_access($user)) {
        return;
    }
    $expRaw = (string) ($user['subscription_expires_at'] ?? '');
    if ($expRaw === '') {
        return;
    }
    $expTs = strtotime($expRaw);
    if ($expTs === false || $expTs > time()) {
        return;
    }

    $uid = (int) $user['id'];
    $title = 'Abonnement terminé';
    $expFmt = date('d/m/Y H:i', $expTs);
    $content = 'Votre abonnement est arrivé à échéance le ' . $expFmt . '. Renouvelez pour conserver l’accès premium.';
    $link = function_exists('site_href') ? site_href('abonnement.php') : 'abonnement.php';

    if (tcf_notification_exists_recent($pdo, $uid, 'subscription', $title, 7)) {
        return;
    }

    tcf_notification_insert($pdo, $uid, 'subscription', $title, $content, $link);
}
