<?php

declare(strict_types=1);

/**
 * Médias durables : fichier disque + copie BLOB en base.
 * Si le fichier disparaît (déploiement git, wipe Hostinger), media_serve.php
 * restitue toujours l’image/audio depuis MySQL.
 */

require_once __DIR__ . '/tcf_schema.php';
require_once __DIR__ . '/media_blob.php';

function tcf_persistent_media_ensure_table(PDO $pdo): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS persistent_media (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                path_key VARCHAR(500) NOT NULL,
                kind VARCHAR(32) NOT NULL DEFAULT 'file',
                mime VARCHAR(80) NOT NULL DEFAULT 'application/octet-stream',
                data LONGBLOB NOT NULL,
                byte_size INT UNSIGNED NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_pm_path (path_key(191)),
                KEY idx_pm_kind (kind)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
        );
    } catch (Throwable $e) {
        error_log('tcf_persistent_media_ensure_table: ' . $e->getMessage());
        $done = false;
    }
}

function tcf_persistent_media_normalize_path(?string $stored): string
{
    $raw = trim((string) ($stored ?? ''));
    if ($raw === '') {
        return '';
    }
    // Ne jamais traiter media_serve.php comme un chemin fichier
    if (preg_match('/media_serve\.php/i', $raw)) {
        return '';
    }
    $rel = tcf_uploads_relative_path($raw);
    if ($rel === '' || preg_match('#^https?://#i', $rel)) {
        return '';
    }
    if (!str_starts_with(strtolower($rel), 'uploads/')) {
        return '';
    }

    return $rel;
}

/**
 * Résout une référence (uploads/…, URL locale, media_serve?type=pm&id=) vers path_key uploads/…
 */
function tcf_persistent_media_resolve_path_key(PDO $pdo, ?string $stored): string
{
    $raw = trim((string) ($stored ?? ''));
    if ($raw === '') {
        return '';
    }
    if (preg_match('/media_serve\.php/i', $raw)
        && preg_match('/(?:\?|&)type=pm(?:&|$)/i', $raw)
        && preg_match('/(?:\?|&)id=(\d+)/i', $raw, $m)
    ) {
        tcf_persistent_media_ensure_table($pdo);
        try {
            $st = $pdo->prepare('SELECT path_key FROM persistent_media WHERE id = ? LIMIT 1');
            $st->execute([(int) $m[1]]);
            $pk = $st->fetchColumn();
            if (is_string($pk) && $pk !== '') {
                return tcf_persistent_media_normalize_path($pk);
            }
        } catch (Throwable $e) {
        }
        return '';
    }

    return tcf_persistent_media_normalize_path($raw);
}

/**
 * Enregistre (ou met à jour) un fichier uploads/… en BLOB.
 *
 * @return int ID persistent_media (0 si échec)
 */
function tcf_persistent_media_store_from_path(PDO $pdo, string $relativeOrAbs, string $kind = 'file'): int
{
    tcf_persistent_media_ensure_table($pdo);
    $rel = tcf_persistent_media_resolve_path_key($pdo, $relativeOrAbs);
    if ($rel === '') {
        $rel = tcf_persistent_media_normalize_path($relativeOrAbs);
    }
    $abs = '';
    if ($rel !== '') {
        $abs = tcf_uploads_fs_path($rel);
    }
    if ($abs === '' || !is_file($abs)) {
        $candidate = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativeOrAbs);
        if (is_file($candidate)) {
            $abs = $candidate;
            if ($rel === '') {
                $root = realpath(dirname(__DIR__));
                $absReal = realpath($abs);
                if ($root && $absReal && str_starts_with($absReal, $root)) {
                    $rel = ltrim(str_replace('\\', '/', substr($absReal, strlen($root))), '/');
                    $rel = tcf_persistent_media_normalize_path($rel);
                }
            }
        }
    }
    if ($rel === '' || $abs === '' || !is_file($abs)) {
        return 0;
    }

    $blob = tcf_schema_read_file_blob($abs);
    if ($blob === null) {
        return 0;
    }
    $mime = (string) ($blob['mime'] ?? 'application/octet-stream');
    $data = (string) $blob['data'];
    $size = strlen($data);
    if ($size <= 0) {
        return 0;
    }

    try {
        // Évite les échecs silencieux sur gros fichiers
        try {
            @$pdo->exec('SET SESSION max_allowed_packet = 67108864');
        } catch (Throwable $e) {
        }
        $st = $pdo->prepare(
            'INSERT INTO persistent_media (path_key, kind, mime, data, byte_size)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE mime = VALUES(mime), data = VALUES(data),
               byte_size = VALUES(byte_size), kind = VALUES(kind), updated_at = NOW()'
        );
        $st->execute([$rel, $kind, $mime, $data, $size]);
        $idSt = $pdo->prepare('SELECT id FROM persistent_media WHERE path_key = ? LIMIT 1');
        $idSt->execute([$rel]);
        return (int) ($idSt->fetchColumn() ?: 0);
    } catch (Throwable $e) {
        error_log('tcf_persistent_media_store_from_path: ' . $e->getMessage());
        return 0;
    }
}

function tcf_persistent_media_id_for_path(PDO $pdo, ?string $stored): int
{
    $rel = tcf_persistent_media_resolve_path_key($pdo, $stored);
    if ($rel === '') {
        $rel = tcf_persistent_media_normalize_path($stored);
    }
    if ($rel === '') {
        return 0;
    }
    tcf_persistent_media_ensure_table($pdo);
    try {
        $st = $pdo->prepare('SELECT id FROM persistent_media WHERE path_key = ? LIMIT 1');
        $st->execute([$rel]);
        return (int) ($st->fetchColumn() ?: 0);
    } catch (Throwable $e) {
        return 0;
    }
}

/**
 * Si le fichier manque mais qu’un BLOB existe, le réécrit sur disque.
 */
function tcf_persistent_media_restore_file(PDO $pdo, ?string $stored): bool
{
    $rel = tcf_persistent_media_normalize_path($stored);
    if ($rel === '') {
        return false;
    }
    $abs = tcf_uploads_fs_path($rel);
    if ($abs !== '' && is_file($abs)) {
        return true;
    }
    tcf_persistent_media_ensure_table($pdo);
    try {
        $st = $pdo->prepare('SELECT data FROM persistent_media WHERE path_key = ? LIMIT 1');
        $st->execute([$rel]);
        $data = $st->fetchColumn();
        if (!is_string($data) || $data === '') {
            return false;
        }
        $dir = dirname($abs);
        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
            return false;
        }
        return @file_put_contents($abs, $data) !== false;
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * URL publique durable pour un chemin uploads/…
 * Préfère media_serve (BLOB) ; restaure le fichier si besoin ; sinon site_href.
 */
function tcf_persistent_media_public_href(PDO $pdo, ?string $stored, string $kind = 'file'): string
{
    $rel = tcf_persistent_media_resolve_path_key($pdo, $stored);
    if ($rel === '') {
        if ($stored !== null && preg_match('#^https?://#i', trim((string) $stored))
            && !preg_match('#/(uploads/)#i', (string) $stored)
            && !preg_match('/media_serve\.php/i', (string) $stored)
        ) {
            return trim((string) $stored);
        }
        return '';
    }

    $abs = tcf_uploads_fs_path($rel);
    $hasFile = $abs !== '' && is_file($abs);
    $pmId = tcf_persistent_media_id_for_path($pdo, $rel);

    if ($pmId <= 0 && $hasFile) {
        $pmId = tcf_persistent_media_store_from_path($pdo, $rel, $kind);
    }
    if ($pmId > 0 && !$hasFile) {
        tcf_persistent_media_restore_file($pdo, $rel);
        $hasFile = $abs !== '' && is_file($abs);
    }
    if ($pmId > 0) {
        return tcf_media_serve_href('pm', $pmId);
    }
    if ($hasFile) {
        return tcf_uploads_public_href($rel);
    }

    return tcf_uploads_public_href($rel);
}

/**
 * Indexe tous les fichiers déjà présents dans uploads/co_media/ (réparation).
 *
 * @return int nombre de médias synchronisés
 */
function tcf_persistent_media_backfill_co_dir(PDO $pdo): int
{
    $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'co_media';
    if (!is_dir($dir)) {
        return 0;
    }
    $n = 0;
    $files = @scandir($dir) ?: [];
    foreach ($files as $f) {
        if ($f === '.' || $f === '..' || $f === '.htaccess' || $f === '.gitkeep') {
            continue;
        }
        $abs = $dir . DIRECTORY_SEPARATOR . $f;
        if (!is_file($abs)) {
            continue;
        }
        $rel = 'uploads/co_media/' . $f;
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        $kind = in_array($ext, ['mp3', 'wav', 'ogg', 'm4a', 'aac'], true) ? 'co_audio' : 'co_image';
        if (tcf_persistent_media_store_from_path($pdo, $rel, $kind) > 0) {
            $n++;
        }
    }

    return $n;
}

function tcf_media_stream_persistent(PDO $pdo, int $id): bool
{
    if ($id <= 0) {
        return false;
    }
    tcf_persistent_media_ensure_table($pdo);
    try {
        $st = $pdo->prepare('SELECT path_key, mime, data FROM persistent_media WHERE id = ? LIMIT 1');
        $st->execute([$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return false;
    }
    if (!$row) {
        return false;
    }

    $rel = (string) ($row['path_key'] ?? '');
    $abs = $rel !== '' ? tcf_uploads_fs_path($rel) : '';
    $mime = trim((string) ($row['mime'] ?? ''));
    if ($mime === '') {
        $mime = 'application/octet-stream';
    }
    $isVideoLike = str_starts_with($mime, 'video/') || str_starts_with($mime, 'audio/');

    if ($abs !== '' && is_file($abs)) {
        return tcf_media_stream_file($abs, $mime, $isVideoLike);
    }

    $data = $row['data'] ?? null;
    if (!is_string($data) || $data === '') {
        return false;
    }
    // Tente une restauration pour les prochains accès directs
    if ($abs !== '') {
        $dir = dirname($abs);
        if (is_dir($dir) || @mkdir($dir, 0755, true)) {
            @file_put_contents($abs, $data);
        }
    }

    return tcf_media_stream_bytes($data, $mime, $isVideoLike);
}

/** Colonnes BLOB annonces / community_posts. */
function tcf_annonce_ensure_image_blob_columns(PDO $pdo): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    foreach (['annonces', 'community_posts'] as $table) {
        try {
            if (!tcf_schema_has_table($pdo, $table)) {
                continue;
            }
            $cols = $pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_COLUMN) ?: [];
            $colSet = array_fill_keys(array_map('strtolower', $cols), true);
            if (empty($colSet['image_data'])) {
                $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN image_data MEDIUMBLOB NULL DEFAULT NULL");
            }
            if (empty($colSet['image_mime'])) {
                $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN image_mime VARCHAR(80) NULL DEFAULT NULL");
            }
        } catch (Throwable $e) {
            error_log("tcf_annonce_ensure_image_blob_columns {$table}: " . $e->getMessage());
        }
    }
}

function tcf_annonce_store_image_blob(PDO $pdo, int $postId, string $relativePath): void
{
    if ($postId <= 0) {
        return;
    }
    tcf_annonce_ensure_image_blob_columns($pdo);
    $rel = tcf_persistent_media_normalize_path($relativePath);
    $abs = $rel !== '' ? tcf_uploads_fs_path($rel) : '';
    if ($abs === '' || !is_file($abs)) {
        return;
    }
    $blob = tcf_schema_read_file_blob($abs);
    if ($blob === null) {
        return;
    }
    $table = function_exists('tcf_community_posts_table')
        ? tcf_community_posts_table($pdo)
        : (tcf_schema_has_table($pdo, 'annonces') ? 'annonces' : 'community_posts');
    try {
        $pdo->prepare("UPDATE `{$table}` SET image_data = ?, image_mime = ? WHERE id = ?")
            ->execute([$blob['data'], $blob['mime'], $postId]);
    } catch (Throwable $e) {
        error_log('tcf_annonce_store_image_blob: ' . $e->getMessage());
    }
    // Miroir path-based pour cohérence avec CO / media_serve?type=pm
    tcf_persistent_media_store_from_path($pdo, $rel, 'annonce');
}

function tcf_annonce_clear_image_blob(PDO $pdo, int $postId): void
{
    if ($postId <= 0) {
        return;
    }
    tcf_annonce_ensure_image_blob_columns($pdo);
    $table = function_exists('tcf_community_posts_table')
        ? tcf_community_posts_table($pdo)
        : (tcf_schema_has_table($pdo, 'annonces') ? 'annonces' : 'community_posts');
    try {
        $pdo->prepare("UPDATE `{$table}` SET image_data = NULL, image_mime = NULL WHERE id = ?")
            ->execute([$postId]);
    } catch (Throwable $e) {
    }
}

function tcf_annonce_has_image_blob(PDO $pdo, int $postId): bool
{
    if ($postId <= 0) {
        return false;
    }
    tcf_annonce_ensure_image_blob_columns($pdo);
    $table = function_exists('tcf_community_posts_table')
        ? tcf_community_posts_table($pdo)
        : (tcf_schema_has_table($pdo, 'annonces') ? 'annonces' : 'community_posts');
    try {
        $st = $pdo->prepare("SELECT OCTET_LENGTH(image_data) FROM `{$table}` WHERE id = ? LIMIT 1");
        $st->execute([$postId]);
        $len = $st->fetchColumn();
        return $len !== false && (int) $len > 0;
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * URL d’image d’annonce : media_serve (BLOB ligne) prioritaire, sinon persistent_media / fichier.
 */
function tcf_annonce_image_href(PDO $pdo, int $postId, ?string $imageUrl): string
{
    $rel = tcf_persistent_media_normalize_path($imageUrl);
    if ($postId > 0) {
        $hasBlob = tcf_annonce_has_image_blob($pdo, $postId);
        $abs = $rel !== '' ? tcf_uploads_fs_path($rel) : '';
        $hasFile = $abs !== '' && is_file($abs);
        if (!$hasBlob && $hasFile) {
            tcf_annonce_store_image_blob($pdo, $postId, $rel);
            $hasBlob = true;
        }
        if ($hasBlob || $hasFile) {
            return tcf_media_serve_href('annonce', $postId);
        }
    }
    if ($rel !== '') {
        return tcf_persistent_media_public_href($pdo, $rel, 'annonce');
    }

    return '';
}

function tcf_media_stream_annonce(PDO $pdo, int $postId): bool
{
    if ($postId <= 0) {
        return false;
    }
    tcf_annonce_ensure_image_blob_columns($pdo);
    $table = function_exists('tcf_community_posts_table')
        ? tcf_community_posts_table($pdo)
        : (tcf_schema_has_table($pdo, 'annonces') ? 'annonces' : 'community_posts');
    try {
        $st = $pdo->prepare("SELECT image_url, image_data, image_mime FROM `{$table}` WHERE id = ? LIMIT 1");
        $st->execute([$postId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return false;
    }
    if (!$row) {
        return false;
    }

    $rel = tcf_persistent_media_normalize_path((string) ($row['image_url'] ?? ''));
    $abs = $rel !== '' ? tcf_uploads_fs_path($rel) : '';
    $mime = trim((string) ($row['image_mime'] ?? ''));
    if ($mime === '' || !str_starts_with($mime, 'image/')) {
        $mime = 'image/jpeg';
    }

    if ($abs !== '' && is_file($abs)) {
        return tcf_media_stream_file($abs, $mime, false);
    }

    $data = $row['image_data'] ?? null;
    if (is_string($data) && $data !== '') {
        if ($abs !== '') {
            $dir = dirname($abs);
            if (is_dir($dir) || @mkdir($dir, 0755, true)) {
                @file_put_contents($abs, $data);
            }
        }
        return tcf_media_stream_bytes($data, $mime, false);
    }

    // Dernier recours : table persistent_media
    if ($rel !== '') {
        $pmId = tcf_persistent_media_id_for_path($pdo, $rel);
        if ($pmId > 0) {
            return tcf_media_stream_persistent($pdo, $pmId);
        }
    }

    return false;
}
