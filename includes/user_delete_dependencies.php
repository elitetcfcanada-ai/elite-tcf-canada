<?php

declare(strict_types=1);

require_once __DIR__ . '/avatar_helper.php';

/**
 * Supprime les lignes qui référencent un utilisateur avant DELETE FROM users
 * (évite les erreurs 1451 sur notifications, analytics, etc.).
 */
function tcf_delete_user_dependencies(PDO $pdo, int $userId): void
{
    $uid = $userId;

    tcf_avatar_delete_all_files_for_user($uid);

    try {
        $pdo->prepare('DELETE FROM notifications WHERE user_id = ?')->execute([$uid]);
    } catch (Throwable $e) {
    }

    try {
        $pdo->prepare('DELETE FROM analytics WHERE user_id = ?')->execute([$uid]);
    } catch (Throwable $e) {
    }

    try {
        $pdo->prepare('DELETE FROM activities WHERE user_id = ?')->execute([$uid]);
    } catch (Throwable $e) {
    }

    try {
        $pdo->prepare('DELETE FROM site_visit_logs WHERE user_id = ?')->execute([$uid]);
    } catch (Throwable $e) {
    }
}
