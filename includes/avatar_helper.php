<?php

declare(strict_types=1);

/**
 * Dossier physique des avatars (synchronisé avec la colonne users.avatar).
 */
function tcf_avatar_storage_dir(): string
{
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';
}

/**
 * La valeur en base ressemble à un nom de fichier image (pas une ancienne initiale type « SA », « V »).
 */
function tcf_avatar_looks_like_image_file(string $stored): bool
{
    $stored = trim($stored);
    if ($stored === '') {
        return false;
    }
    if (preg_match('/^avatar_\d+/i', $stored)) {
        return true;
    }
    if (preg_match('/\.(jpe?g|png|webp)$/i', $stored)) {
        return true;
    }
    return false;
}

/**
 * Vérifie qu'un nom de fichier avatar appartient bien à l'utilisateur donné.
 * Convention acceptée: avatar_{userId}_*.{jpg|jpeg|png|webp}
 */
function tcf_avatar_belongs_to_user(int $userId, string $filename): bool
{
    $base = basename(trim($filename));
    if ($base === '') {
        return false;
    }
    return (bool) preg_match('/^avatar_' . preg_quote((string) $userId, '/') . '_.+\.(jpe?g|png|webp)$/i', $base);
}

/**
 * Supprime tous les fichiers avatar_{userId}_* du disque (à la suppression de compte).
 */
function tcf_avatar_delete_all_files_for_user(int $userId): void
{
    if ($userId <= 0) {
        return;
    }
    $dir = tcf_avatar_storage_dir();
    if (!is_dir($dir)) {
        return;
    }
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        $found = glob($dir . DIRECTORY_SEPARATOR . 'avatar_' . $userId . '_*.' . $ext) ?: [];
        foreach ($found as $path) {
            if (is_file($path) && tcf_avatar_belongs_to_user($userId, basename($path))) {
                @unlink($path);
            }
        }
    }
}

/**
 * True si users.avatar_data contient une image.
 */
function tcf_user_has_avatar_blob(PDO $pdo, int $userId): bool
{
    if ($userId <= 0) {
        return false;
    }
    try {
        $st = $pdo->prepare('SELECT OCTET_LENGTH(avatar_data) FROM users WHERE id = ? LIMIT 1');
        $st->execute([$userId]);
        $len = $st->fetchColumn();

        return $len !== false && (int) $len > 0;
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * Relie un fichier orphelin avatar_{id}_* si la colonne avatar est vide
 * et qu’un seul fichier correspondant existe encore sur le disque.
 */
function tcf_avatar_recover_orphan_file(PDO $pdo, int $userId): ?string
{
    if ($userId <= 0) {
        return null;
    }
    $dir = tcf_avatar_storage_dir();
    if (!is_dir($dir)) {
        return null;
    }
    $found = [];
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        foreach (glob($dir . DIRECTORY_SEPARATOR . 'avatar_' . $userId . '_*.' . $ext) ?: [] as $path) {
            $base = basename($path);
            if (is_file($path) && tcf_avatar_belongs_to_user($userId, $base)) {
                $found[$base] = $path;
            }
        }
    }
    if (count($found) !== 1) {
        return null;
    }
    $bases = array_keys($found);
    $base = (string) $bases[0];
    try {
        $pdo->prepare('UPDATE users SET avatar = ? WHERE id = ? AND (avatar IS NULL OR avatar = \'\')')->execute([$base, $userId]);
    } catch (Throwable $e) {
    }
    // Persiste aussi en BLOB si colonne disponible
    tcf_user_store_avatar_blob_from_file($pdo, $userId, $found[$base]);

    return $base;
}

/**
 * Enregistre avatar_data / avatar_mime depuis un fichier local.
 */
function tcf_user_store_avatar_blob_from_file(PDO $pdo, int $userId, string $absPath): void
{
    if ($userId <= 0 || $absPath === '' || !is_file($absPath)) {
        return;
    }
    $raw = @file_get_contents($absPath);
    if ($raw === false || $raw === '') {
        return;
    }
    $mime = 'image/jpeg';
    $fi = @getimagesize($absPath);
    if (is_array($fi) && !empty($fi['mime'])) {
        $mime = (string) $fi['mime'];
    }
    try {
        $pdo->prepare('UPDATE users SET avatar_data = ?, avatar_mime = ? WHERE id = ?')
            ->execute([$raw, $mime, $userId]);
    } catch (Throwable $e) {
        // Colonnes absentes sur anciens schémas
    }
}

/**
 * Trouve le fichier réel sur le disque, corrige la BDD si besoin.
 *
 * Si le fichier manque mais qu’un BLOB avatar_data existe, on conserve la référence
 * (servie via media_serve.php) au lieu de vider users.avatar.
 */
function tcf_sync_user_avatar_from_disk(PDO $pdo, int $userId, ?string $dbAvatar): ?string
{
    $dir = tcf_avatar_storage_dir();
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    $dbTrim = trim((string) ($dbAvatar ?? ''));
    if ($dbTrim === '') {
        return tcf_avatar_recover_orphan_file($pdo, $userId);
    }

    $base = basename($dbTrim);
    if (!tcf_avatar_belongs_to_user($userId, $base)) {
        // Ne pas effacer si un BLOB est encore présent
        if (tcf_user_has_avatar_blob($pdo, $userId)) {
            return $base !== '' ? $base : null;
        }
        try {
            $pdo->prepare('UPDATE users SET avatar = NULL WHERE id = ?')->execute([$userId]);
        } catch (Throwable $e) {
        }

        return tcf_avatar_recover_orphan_file($pdo, $userId);
    }

    $pathPrimary = $dir . DIRECTORY_SEPARATOR . $base;
    if (is_file($pathPrimary)) {
        if (!tcf_user_has_avatar_blob($pdo, $userId)) {
            tcf_user_store_avatar_blob_from_file($pdo, $userId, $pathPrimary);
        }

        return $base;
    }

    $tryFiles = [];
    if (tcf_avatar_looks_like_image_file($base)) {
        $tryFiles[] = $base;
        if (preg_match('/^(.+)\.j$/i', $base, $m)) {
            $tryFiles[] = $m[1] . '.jpg';
            $tryFiles[] = $m[1] . '.jpeg';
        }
        if (preg_match('/^(.+)\.(jp|pn|we)$/i', $base, $m)) {
            $stem = $m[1];
            $tryFiles[] = $stem . '.jpg';
            $tryFiles[] = $stem . '.jpeg';
            $tryFiles[] = $stem . '.png';
            $tryFiles[] = $stem . '.webp';
        }
    }

    foreach (array_unique($tryFiles) as $fn) {
        if ($fn === '' || !tcf_avatar_belongs_to_user($userId, $fn)) {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $fn;
        if (is_file($path)) {
            if ($dbTrim !== $fn) {
                try {
                    $pdo->prepare('UPDATE users SET avatar = ? WHERE id = ?')->execute([$fn, $userId]);
                } catch (Throwable $e) {
                }
            }
            if (!tcf_user_has_avatar_blob($pdo, $userId)) {
                tcf_user_store_avatar_blob_from_file($pdo, $userId, $path);
            }

            return $fn;
        }
    }

    // Fichier absent : garder la ref si BLOB dispo (media_serve), sinon récupérer / nettoyer
    if (tcf_user_has_avatar_blob($pdo, $userId)) {
        return $base;
    }

    $recovered = tcf_avatar_recover_orphan_file($pdo, $userId);
    if ($recovered !== null) {
        return $recovered;
    }

    try {
        $pdo->prepare('UPDATE users SET avatar = NULL WHERE id = ?')->execute([$userId]);
    } catch (Throwable $e) {
    }

    return null;
}

/**
 * URL publique avec cache-bust (évite l’ancienne image après changement).
 */
function tcf_avatar_public_url(?string $resolvedFilename): ?string
{
    if ($resolvedFilename === null || $resolvedFilename === '') {
        return null;
    }
    $safe = basename($resolvedFilename);
    $url = site_href('uploads/avatars/' . rawurlencode($safe));
    $full = tcf_avatar_storage_dir() . DIRECTORY_SEPARATOR . $safe;
    if (is_file($full)) {
        $url .= '?t=' . (string) filemtime($full);
    }

    return $url;
}

/**
 * URL affichable pour un utilisateur (fichier uploads, sinon BLOB via media_serve).
 */
function tcf_user_avatar_display_url(PDO $pdo, int $userId, ?string $dbAvatar = null): ?string
{
    if ($userId <= 0) {
        return null;
    }
    if ($dbAvatar === null) {
        try {
            $st = $pdo->prepare('SELECT avatar FROM users WHERE id = ? LIMIT 1');
            $st->execute([$userId]);
            $dbAvatar = $st->fetchColumn();
            $dbAvatar = $dbAvatar !== false ? (string) $dbAvatar : null;
        } catch (Throwable $e) {
            $dbAvatar = null;
        }
    }

    $resolved = tcf_sync_user_avatar_from_disk($pdo, $userId, $dbAvatar);
    if ($resolved) {
        $fileUrl = tcf_avatar_public_url($resolved);
        $full = tcf_avatar_storage_dir() . DIRECTORY_SEPARATOR . basename($resolved);
        if ($fileUrl && is_file($full)) {
            return $fileUrl;
        }
    }

    if (tcf_user_has_avatar_blob($pdo, $userId)) {
        require_once __DIR__ . '/media_blob.php';

        return tcf_media_serve_href('avatar', $userId) . '&t=' . (string) time();
    }

    return null;
}
