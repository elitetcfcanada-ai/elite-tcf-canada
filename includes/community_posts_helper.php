<?php

declare(strict_types=1);

/**
 * Annonces communautaires (image + texte + likes).
 */

function tcf_community_posts_ensure_tables(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS community_posts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            body TEXT NOT NULL,
            image_url VARCHAR(500) DEFAULT NULL,
            visibility ENUM('visitors','registered','premium') NOT NULL DEFAULT 'registered',
            is_published TINYINT(1) NOT NULL DEFAULT 1,
            created_by INT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_cp_pub (is_published, created_at),
            KEY idx_cp_vis (visibility)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS community_post_likes (
            post_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (post_id, user_id),
            KEY idx_cpl_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS community_post_views (
            post_id INT UNSIGNED NOT NULL,
            viewer_key VARCHAR(64) NOT NULL,
            user_id INT UNSIGNED NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (post_id, viewer_key),
            KEY idx_cpv_post (post_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function tcf_community_visibility_label(string $v): string
{
    return match ($v) {
        'visitors' => 'Tout le monde (visiteurs inclus)',
        'premium' => 'Abonnés payants uniquement',
        default => 'Membres inscrits',
    };
}

/**
 * L’utilisateur courant peut-il voir ce poste ?
 * @param array<string,mixed>|null $user
 */
function tcf_community_user_can_view_post(?array $user, string $visibility): bool
{
    $visibility = strtolower(trim($visibility));
    if ($visibility === 'visitors') {
        return true;
    }
    if ($user === null || empty($user['id'])) {
        return false;
    }
    $role = (string) ($user['role'] ?? 'user');
    if (in_array($role, ['admin', 'super_admin'], true)) {
        return true;
    }
    if ($visibility === 'registered') {
        return true;
    }
    if ($visibility === 'premium') {
        if (!function_exists('tcf_user_has_premium_access')) {
            require_once __DIR__ . '/subscription_access.php';
        }

        return tcf_user_has_premium_access($user);
    }

    return false;
}

/**
 * Clé anonyme / utilisateur pour compter une vue unique par personne.
 */
function tcf_community_viewer_key(?array $user): string
{
    $uid = (int) ($user['id'] ?? 0);
    if ($uid > 0) {
        return 'u:' . $uid;
    }
    if (function_exists('tcf_visitor_id')) {
        $vid = trim((string) tcf_visitor_id());
        if ($vid !== '') {
            return 'v:' . substr($vid, 0, 60);
        }
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        return 's:' . substr(session_id(), 0, 60);
    }

    return 's:anon';
}

/**
 * Enregistre une vue unique (ignore si déjà vue).
 */
function tcf_community_record_view(PDO $pdo, int $postId, ?array $user): void
{
    if ($postId <= 0) {
        return;
    }
    tcf_community_posts_ensure_tables($pdo);
    $key = tcf_community_viewer_key($user);
    $uid = (int) ($user['id'] ?? 0);
    try {
        $pdo->prepare(
            'INSERT IGNORE INTO community_post_views (post_id, viewer_key, user_id) VALUES (?, ?, ?)'
        )->execute([$postId, $key, $uid > 0 ? $uid : null]);
    } catch (Throwable $e) {
        // ignore
    }
}

/**
 * @return list<array<string,mixed>>
 */
function tcf_community_posts_list_for_viewer(PDO $pdo, ?array $user, int $limit = 50): array
{
    tcf_community_posts_ensure_tables($pdo);
    $limit = max(1, min(100, $limit));
    $uid = (int) ($user['id'] ?? 0);
    $st = $pdo->query(
        "SELECT p.*,
            (SELECT COUNT(*) FROM community_post_likes l WHERE l.post_id = p.id) AS likes_count
         FROM community_posts p
         WHERE p.is_published = 1
         ORDER BY p.created_at DESC
         LIMIT $limit"
    );
    $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $liked = [];
    if ($uid > 0 && $rows) {
        $ids = array_map(static fn ($r) => (int) $r['id'], $rows);
        $in = implode(',', array_fill(0, count($ids), '?'));
        $ls = $pdo->prepare("SELECT post_id FROM community_post_likes WHERE user_id = ? AND post_id IN ($in)");
        $ls->execute(array_merge([$uid], $ids));
        foreach ($ls->fetchAll(PDO::FETCH_COLUMN) as $pid) {
            $liked[(int) $pid] = true;
        }
    }
    $out = [];
    foreach ($rows as $r) {
        $vis = (string) ($r['visibility'] ?? 'registered');
        if (!tcf_community_user_can_view_post($user, $vis)) {
            continue;
        }
        $pid = (int) $r['id'];
        tcf_community_record_view($pdo, $pid, $user);
        $img = trim((string) ($r['image_url'] ?? ''));
        $r['image_href'] = $img !== '' ? (function_exists('site_href') ? site_href(ltrim($img, '/')) : '/' . ltrim($img, '/')) : '';
        $r['liked_by_me'] = !empty($liked[$pid]);
        $r['likes_count'] = (int) ($r['likes_count'] ?? 0);
        $r['visibility_label'] = tcf_community_visibility_label($vis);
        /* Pas d’auteur / avatar côté public */
        unset($r['author_name'], $r['created_by']);
        $out[] = $r;
    }

    return $out;
}

function tcf_community_drop_channel_tables(PDO $pdo): void
{
    $tables = [
        'channel_post_poll_votes',
        'channel_post_comments',
        'channel_post_likes',
        'channel_posts',
        'playlist_videos',
        'channel_playlists',
        'channel_subscribers',
        'channel_branding',
    ];
    foreach ($tables as $t) {
        try {
            $pdo->exec("DROP TABLE IF EXISTS `$t`");
        } catch (Throwable $e) {
            // ignore
        }
    }
}
