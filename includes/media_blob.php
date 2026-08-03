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
 * URL publique vidéo / miniature.
 * Préfère media_serve.php (Range + fichier/BLOB) pour une lecture fiable sur Hostinger.
 */
function tcf_video_media_href(PDO $pdo, int $videoId, ?string $storedUrl, string $part = 'video'): string
{
    $stored = trim((string) ($storedUrl ?? ''));
    if ($stored !== '' && preg_match('#^https?://#i', $stored)) {
        return $stored;
    }

    if ($videoId > 0) {
        $fs = $stored !== '' ? tcf_uploads_fs_path($stored) : '';
        $hasFile = $fs !== '' && is_file($fs);
        $hasBlob = tcf_video_blob_available($pdo, $videoId, $part);
        if ($hasFile || $hasBlob) {
            return tcf_media_serve_href($part === 'thumbnail' ? 'video_thumb' : 'video', $videoId);
        }
    }

    return tcf_uploads_public_href($storedUrl);
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
    $safe = max(1024 * 1024, $packet - (512 * 1024));
    return min($safe, 48 * 1024 * 1024);
}

/**
 * Persiste vidéo / miniature en BLOB (en plus du chemin fichier).
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
 * Stream binaire avec support HTTP Range (essentiel pour <video> HTML5).
 */
function tcf_media_stream_bytes(string $data, string $mime, bool $isVideo = true): bool
{
    if ($data === '') {
        return false;
    }
    $size = strlen($data);
    $start = 0;
    $end = $size - 1;
    $status = 200;

    if (!empty($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/', (string) $_SERVER['HTTP_RANGE'], $m)) {
        if ($m[1] !== '') {
            $start = (int) $m[1];
        }
        if ($m[2] !== '') {
            $end = (int) $m[2];
        }
        if ($end >= $size) {
            $end = $size - 1;
        }
        if ($start > $end || $start >= $size) {
            http_response_code(416);
            header("Content-Range: bytes */{$size}");
            return false;
        }
        $status = 206;
    }

    $length = $end - $start + 1;
    http_response_code($status);
    header('Content-Type: ' . $mime);
    header('Accept-Ranges: bytes');
    header('Content-Length: ' . (string) $length);
    header('Cache-Control: public, max-age=86400');
    if ($isVideo) {
        header('Content-Disposition: inline');
    }
    if ($status === 206) {
        header("Content-Range: bytes {$start}-{$end}/{$size}");
    }

    echo substr($data, $start, $length);
    return true;
}

/**
 * Stream fichier disque avec Range (sans charger tout en mémoire).
 */
function tcf_media_stream_file(string $absPath, string $mime, bool $isVideo = true): bool
{
    if ($absPath === '' || !is_file($absPath) || !is_readable($absPath)) {
        return false;
    }
    $size = (int) filesize($absPath);
    if ($size <= 0) {
        return false;
    }
    $start = 0;
    $end = $size - 1;
    $status = 200;

    if (!empty($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/', (string) $_SERVER['HTTP_RANGE'], $m)) {
        if ($m[1] !== '') {
            $start = (int) $m[1];
        }
        if ($m[2] !== '') {
            $end = (int) $m[2];
        }
        if ($end >= $size) {
            $end = $size - 1;
        }
        if ($start > $end || $start >= $size) {
            http_response_code(416);
            header("Content-Range: bytes */{$size}");
            return false;
        }
        $status = 206;
    }

    $length = $end - $start + 1;
    http_response_code($status);
    header('Content-Type: ' . $mime);
    header('Accept-Ranges: bytes');
    header('Content-Length: ' . (string) $length);
    header('Cache-Control: public, max-age=86400');
    if ($isVideo) {
        header('Content-Disposition: inline');
    }
    if ($status === 206) {
        header("Content-Range: bytes {$start}-{$end}/{$size}");
    }

    $fp = fopen($absPath, 'rb');
    if ($fp === false) {
        return false;
    }
    if ($start > 0) {
        fseek($fp, $start);
    }
    $left = $length;
    while ($left > 0 && !feof($fp) && !connection_aborted()) {
        $chunk = fread($fp, (int) min(8192, $left));
        if ($chunk === false || $chunk === '') {
            break;
        }
        echo $chunk;
        $left -= strlen($chunk);
    }
    fclose($fp);
    return true;
}

function tcf_media_guess_mime_from_path(string $path, bool $isVideo): string
{
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if ($isVideo) {
        $map = [
            'mp4' => 'video/mp4',
            'm4v' => 'video/mp4',
            'webm' => 'video/webm',
            'ogg' => 'video/ogg',
            'ogv' => 'video/ogg',
            'mov' => 'video/mp4', // fallback lecture
        ];
        return $map[$ext] ?? 'video/mp4';
    }
    $map = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
    ];
    return $map[$ext] ?? 'image/jpeg';
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
    return tcf_media_stream_bytes($data, $mime, false);
}

/**
 * Stream vidéo / miniature : fichier disque d'abord (Range), sinon BLOB (Range).
 */
function tcf_media_stream_video_blob(PDO $pdo, string $type, int $id): bool
{
    if ($id <= 0) {
        return false;
    }
    $isVideo = ($type === 'video');
    if (!in_array($type, ['video', 'video_thumb'], true)) {
        return false;
    }

    try {
        if ($isVideo) {
            $st = $pdo->prepare('SELECT video_url, video_data, video_mime FROM videos WHERE id = ? LIMIT 1');
        } else {
            $st = $pdo->prepare('SELECT thumbnail_url, thumbnail_data, thumbnail_mime FROM videos WHERE id = ? LIMIT 1');
    }
    $st->execute([$id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return false;
    }
    if (!$row) {
        return false;
    }

    $urlCol = $isVideo ? 'video_url' : 'thumbnail_url';
    $stored = trim((string) ($row[$urlCol] ?? ''));
    if ($stored !== '' && !preg_match('#^https?://#i', $stored)) {
        $fs = tcf_uploads_fs_path($stored);
        if ($fs !== '' && is_file($fs)) {
            $mime = trim((string) ($row[$isVideo ? 'video_mime' : 'thumbnail_mime'] ?? ''));
            if ($mime === '') {
                $mime = tcf_media_guess_mime_from_path($fs, $isVideo);
            }
            // Forcer video/mp4 pour .mp4 (certains navigateurs refusent un MIME incorrect)
            if ($isVideo && preg_match('/\.mp4$/i', $fs)) {
                $mime = 'video/mp4';
            }
            return tcf_media_stream_file($fs, $mime, $isVideo);
        }
    }

    $data = $isVideo ? ($row['video_data'] ?? null) : ($row['thumbnail_data'] ?? null);
    if (!is_string($data) || $data === '') {
        return false;
    }
    $mime = trim((string) ($row[$isVideo ? 'video_mime' : 'thumbnail_mime'] ?? ''));
    if ($mime === '') {
        $mime = $isVideo ? 'video/mp4' : 'image/jpeg';
    }
    if ($isVideo && (stripos($mime, 'video/') !== 0)) {
        $mime = 'video/mp4';
    }
    return tcf_media_stream_bytes($data, $mime, $isVideo);
}
