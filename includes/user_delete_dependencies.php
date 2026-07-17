<?php

declare(strict_types=1);

require_once __DIR__ . '/avatar_helper.php';

/**
 * Supprime les lignes qui référencent un utilisateur avant DELETE FROM users
 * (évite les erreurs 1451 sur notifications, analytics, chat_messages, etc.).
 */
function tcf_delete_user_dependencies(PDO $pdo, int $userId): void
{
    $uid = $userId;

    tcf_avatar_delete_all_files_for_user($uid);

    // Chat TCF (chat_api.php) — pas de FK vers users, mais données orphelines / présence
    try {
        $pdo->prepare('DELETE FROM tcf_chat_presence_settings WHERE user_id = ?')->execute([$uid]);
    } catch (Throwable $e) {
    }
    try {
        $pdo->prepare('DELETE FROM tcf_chat_thread_members WHERE user_id = ?')->execute([$uid]);
    } catch (Throwable $e) {
    }

    try {
        $pdo->prepare('DELETE FROM chat_messages WHERE user_id = ? OR admin_id = ?')->execute([$uid, $uid]);
    } catch (Throwable $e) {
    }

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
