<?php

declare(strict_types=1);

/**
 * Notifications réservées au staff (lignes user_id NULL, types dédiés).
 * deep_link : chemin relatif site, ex. admin/superAdmin.php?sa_focus=video_comment&video_id=1&comment_id=2
 */
function tcf_add_staff_notification(PDO $pdo, string $type, string $title, string $content, ?string $deepLink = null): void
{
    if (!in_array($type, ['video_comment', 'testimonial'], true)) {
        return;
    }
    try {
        if ($deepLink !== null && $deepLink !== '') {
            $st = $pdo->prepare('INSERT INTO notifications (user_id, type, title, content, deep_link) VALUES (NULL, ?, ?, ?, ?)');
            $st->execute([$type, $title, $content, $deepLink]);
        } else {
            $st = $pdo->prepare('INSERT INTO notifications (user_id, type, title, content) VALUES (NULL, ?, ?, ?)');
            $st->execute([$type, $title, $content]);
        }
    } catch (Throwable $e) {
        error_log('tcf_add_staff_notification: ' . $e->getMessage());
    }
}
