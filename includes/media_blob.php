<?php

declare(strict_types=1);

require_once __DIR__ . '/tcf_schema.php';

function tcf_media_serve_href(string $type, int $id): string
{
    return site_href('media_serve.php?type=' . rawurlencode($type) . '&id=' . $id);
}

function tcf_video_blob_available(PDO $pdo, int $videoId, string $part): bool
{
    if ($videoId <= 0) {
        return false;
    }
    $col = $part === 'thumbnail' ? 'thumbnail_data' : 'video_data';
    try {
        $st = $pdo->prepare("SELECT OCTET_LENGTH({$col}) FROM videos WHERE id = ? LIMIT 1");
        $st->execute([$videoId]);
        $len = $st->fetchColumn();

        return $len !== false && (int) $len > 0;
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * URL publique vidéo / miniature : fichier local si présent, sinon endpoint BLOB.
 */
function tcf_video_media_href(PDO $pdo, int $videoId, ?string $storedUrl, string $part = 'video'): string
{
    $href = tcf_uploads_public_href($storedUrl);
    if ($storedUrl !== null && $storedUrl !== '') {
        $fs = tcf_uploads_fs_path($storedUrl);
        if ($fs !== '' && is_file($fs)) {
            return $href;
        }
    }
    if (tcf_video_blob_available($pdo, $videoId, $part)) {
        return tcf_media_serve_href($part === 'thumbnail' ? 'video_thumb' : 'video', $videoId);
    }

    return $href;
}

function tcf_video_blob_max_bytes(PDO $pdo): int
{
    try {
        @$pdo->exec('SET GLOBAL max_allowed_packet = 67108864');
    } catch (Throwable $e) {
    }
    $packet = 16 * 1024 * 1024;
    try {
        $sess = (int) $pdo->query('SELECT @@session.max_allowed_packet')->fetchColumn();
        $glob = (int) $pdo->query('SELECT @@global.max_allowed_packet')->fetchColumn();
        $packet = max($sess, $glob, $packet);
    } catch (Throwable $e) {
    }
    // Marge sécurité (requête UPDATE + overhead)
    $safe = max(1024 * 1024, $packet - (512 * 1024));
    return min($safe, 48 * 1024 * 1024);
}

/**
 * Persiste vidéo / miniature en BLOB (en plus du chemin fichier).
 * Miniature et vidéo sont enregistrées séparément pour ne pas tout perdre si la vidéo est trop lourde.
 */
function tcf_video_store_blobs_from_paths(PDO $pdo, int $videoId, ?string $thumbUrl, ?string $videoUrl): void
{
    if ($videoId <= 0) {
        return;
    }
    $max = tcf_video_blob_max_bytes($pdo);

    if ($thumbUrl) {
        $abs = tcf_schema_resolve_upload_path($thumbUrl);
        if ($abs !== '' && is_file($abs)) {
            $size = (int) filesize($abs);
            if ($size > 0 && $size <= $max) {
                $blob = tcf_schema_read_file_blob($abs);
                if ($blob) {
                    try {
                        $pdo->prepare('UPDATE videos SET thumbnail_data=?, thumbnail_mime=? WHERE id=?')
                            ->execute([$blob['data'], $blob['mime'], $videoId]);
                    } catch (Throwable $e) {
                        error_log('tcf_video_store_blobs thumb: ' . $e->getMessage());
                    }
                }
            }
        }
    }

    if ($videoUrl) {
        $abs = tcf_schema_resolve_upload_path($videoUrl);
        if ($abs !== '' && is_file($abs)) {
            $size = (int) filesize($abs);
            if ($size > 0 && $size <= $max) {
                $blob = tcf_schema_read_file_blob($abs);
                if ($blob) {
                    try {
                        $pdo->prepare('UPDATE videos SET video_data=?, video_mime=? WHERE id=?')
                            ->execute([$blob['data'], $blob['mime'], $videoId]);
                    } catch (Throwable $e) {
                        error_log('tcf_video_store_blobs video: ' . $e->getMessage());
                    }
                }
            } else {
                error_log("tcf_video_store_blobs: vidéo #$videoId trop volumineuse ($size > $max) — fichier uploads conservé");
            }
        }
    }
}

/**
 * Stream avatar BLOB (users.avatar_data) to stdout.
 */
function tcf_media_stream_avatar_blob(PDO $pdo, int $userId): bool
{
    if ($userId <= 0) {
        return false;
    }
    try {
        $st = $pdo->prepare('SELECT avatar_data, avatar_mime FROM users WHERE id = ? LIMIT 1');
        $st->execute([$userId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return false;
    }
    if (!$row) {
        return false;
    }
    $data = $row['avatar_data'] ?? null;
    if (!is_string($data) || $data === '') {
        return false;
    }
    $mime = trim((string) ($row['avatar_mime'] ?? ''));
    if ($mime === '' || strpos($mime, 'image/') !== 0) {
        $mime = 'image/jpeg';
    }
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . (string) strlen($data));
    header('Cache-Control: private, max-age=86400');
    echo $data;

    return true;
}

/**
 * Stream a video/thumbnail BLOB to stdout (headers not sent yet).
 */
function tcf_media_stream_video_blob(PDO $pdo, string $type, int $id): bool
{
    if ($id <= 0) {
        return false;
    }
    if ($type === 'video') {
        $st = $pdo->prepare('SELECT video_data, video_mime FROM videos WHERE id = ? LIMIT 1');
    } elseif ($type === 'video_thumb') {
        $st = $pdo->prepare('SELECT thumbnail_data, thumbnail_mime FROM videos WHERE id = ? LIMIT 1');
    } else {
        return false;
    }
    $st->execute([$id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return false;
    }
    $data = $type === 'video' ? ($row['video_data'] ?? null) : ($row['thumbnail_data'] ?? null);
    if (!is_string($data) || $data === '') {
        return false;
    }
    $mime = trim((string) ($row['video_mime'] ?? $row['thumbnail_mime'] ?? ''));
    if ($mime === '') {
        $mime = $type === 'video' ? 'video/mp4' : 'image/jpeg';
    }
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . (string) strlen($data));
    header('Cache-Control: private, max-age=86400');
    echo $data;

    return true;
}
