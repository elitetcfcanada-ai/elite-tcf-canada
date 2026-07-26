<?php

declare(strict_types=1);

/**
 * Likes / commentaires vidéo stockés en JSON sur la table videos (schéma consolidé).
 */

function tcf_videos_list_select_sql(string $alias = 'v'): string
{
    // Pas de BLOB (video_data/thumbnail_data) — évite de casser l’admin JSON.
    return "SELECT {$alias}.id, {$alias}.title, {$alias}.description, {$alias}.thumbnail_url, {$alias}.video_url,
            {$alias}.visibility, {$alias}.duration, {$alias}.views, {$alias}.likes,
            {$alias}.comments_json, {$alias}.created_at, {$alias}.updated_at";
}

/**
 * @param list<array<string,mixed>> $videos
 * @return list<array<string,mixed>>
 */
function tcf_videos_normalize_list_rows(array $videos): array
{
    foreach ($videos as &$v) {
        $comments = tcf_video_decode_comments(isset($v['comments_json']) ? (string) $v['comments_json'] : null);
        $v['comments_count'] = count($comments);
        unset($v['comments_json'], $v['likes_json'], $v['video_data'], $v['thumbnail_data']);
    }
    unset($v);
    return $videos;
}

/**
 * @return list<array{user_id:int,created_at?:?string}>
 */
function tcf_video_decode_likes(?string $json): array
{
    if ($json === null || trim($json) === '') {
        return [];
    }
    $data = json_decode($json, true);
    if (!is_array($data)) {
        return [];
    }
    $out = [];
    foreach ($data as $row) {
        if (is_array($row) && isset($row['user_id'])) {
            $out[] = [
                'user_id' => (int) $row['user_id'],
                'created_at' => $row['created_at'] ?? null,
            ];
        } elseif (is_numeric($row)) {
            $out[] = ['user_id' => (int) $row, 'created_at' => null];
        }
    }
    return $out;
}

/**
 * @return list<array<string,mixed>>
 */
function tcf_video_decode_comments(?string $json): array
{
    if ($json === null || trim($json) === '') {
        return [];
    }
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function tcf_video_encode_json(array $data): string
{
    return json_encode(array_values($data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
}

function tcf_video_user_liked(array $likes, int $userId): bool
{
    foreach ($likes as $row) {
        if ((int) ($row['user_id'] ?? 0) === $userId) {
            return true;
        }
    }
    return false;
}

/**
 * @return array{likes:int,user_liked:bool}
 */
function tcf_video_toggle_like(PDO $pdo, int $videoId, int $userId): array
{
    $st = $pdo->prepare('SELECT likes_json FROM videos WHERE id = ? LIMIT 1');
    $st->execute([$videoId]);
    $raw = $st->fetchColumn();
    $likes = tcf_video_decode_likes(is_string($raw) ? $raw : null);
    $liked = false;
    $next = [];
    foreach ($likes as $row) {
        if ((int) ($row['user_id'] ?? 0) === $userId) {
            $liked = true;
            continue;
        }
        $next[] = $row;
    }
    if (!$liked) {
        $next[] = ['user_id' => $userId, 'created_at' => date('Y-m-d H:i:s')];
        $userLiked = true;
    } else {
        $userLiked = false;
    }
    $count = count($next);
    $pdo->prepare('UPDATE videos SET likes_json = ?, likes = ? WHERE id = ?')
        ->execute([tcf_video_encode_json($next), $count, $videoId]);
    return ['likes' => $count, 'user_liked' => $userLiked];
}

/**
 * @return array<string,mixed>
 */
function tcf_video_add_comment(PDO $pdo, int $videoId, int $userId, string $body, ?int $parentId = null): array
{
    $st = $pdo->prepare('SELECT comments_json FROM videos WHERE id = ? LIMIT 1');
    $st->execute([$videoId]);
    $raw = $st->fetchColumn();
    $comments = tcf_video_decode_comments(is_string($raw) ? $raw : null);
    $maxId = 0;
    foreach ($comments as $c) {
        $maxId = max($maxId, (int) ($c['id'] ?? 0));
    }
    $entry = [
        'id' => $maxId + 1,
        'user_id' => $userId,
        'parent_id' => $parentId,
        'body' => $body,
        'created_at' => date('Y-m-d H:i:s'),
    ];
    $comments[] = $entry;
    $pdo->prepare('UPDATE videos SET comments_json = ? WHERE id = ?')
        ->execute([tcf_video_encode_json($comments), $videoId]);
    return $entry;
}

/**
 * Enrichit les commentaires avec nom/rôle utilisateur.
 *
 * @param list<array<string,mixed>> $comments
 * @return list<array<string,mixed>>
 */
function tcf_video_comments_with_users(PDO $pdo, array $comments): array
{
    $ids = [];
    foreach ($comments as $c) {
        $uid = (int) ($c['user_id'] ?? 0);
        if ($uid > 0) {
            $ids[$uid] = true;
        }
    }
    $map = [];
    if ($ids) {
        $in = implode(',', array_map('intval', array_keys($ids)));
        foreach ($pdo->query("SELECT id, name, role FROM users WHERE id IN ($in)") as $u) {
            $map[(int) $u['id']] = $u;
        }
    }
    foreach ($comments as &$c) {
        $uid = (int) ($c['user_id'] ?? 0);
        $c['user_name'] = (string) ($map[$uid]['name'] ?? 'Utilisateur');
        $c['user_role'] = (string) ($map[$uid]['role'] ?? 'user');
    }
    unset($c);
    return $comments;
}
