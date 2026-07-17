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
