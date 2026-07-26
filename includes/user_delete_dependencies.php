<?php

declare(strict_types=1);

require_once __DIR__ . '/avatar_helper.php';
require_once __DIR__ . '/tcf_legacy_tables.php';

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

    if (tcf_schema_has_table($pdo, 'activites')) {
        try {
            $pdo->prepare('DELETE FROM activites WHERE user_id = ?')->execute([$uid]);
        } catch (Throwable $e) {
        }
    } else {
        try {
            $pdo->prepare('DELETE FROM activities WHERE user_id = ?')->execute([$uid]);
        } catch (Throwable $e) {
        }
    }

    if (tcf_visiteurs_available($pdo)) {
        try {
            $pdo->prepare("DELETE FROM visiteurs WHERE user_id = ? AND kind = 'site'")->execute([$uid]);
        } catch (Throwable $e) {
        }
    } else {
        try {
            $pdo->prepare('DELETE FROM site_visit_logs WHERE user_id = ?')->execute([$uid]);
        } catch (Throwable $e) {
        }
    }
}
