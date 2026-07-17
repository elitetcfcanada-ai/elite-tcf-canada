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
 * Trouve le fichier réel sur le disque, corrige la BDD si besoin.
 *
 * Important : ne jamais « deviner » une photo sur le disque si users.avatar est vide.
 * Sinon, après suppression d'un compte, le même id AUTO_INCREMENT peut réutiliser d'anciens fichiers.
 * Seule la valeur en base (ou un upload explicite) lie un utilisateur à un fichier.
 */
function tcf_sync_user_avatar_from_disk(PDO $pdo, int $userId, ?string $dbAvatar): ?string
{
    $dir = tcf_avatar_storage_dir();
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    if (!is_dir($dir)) {
        return null;
    }

    $dbTrim = trim((string) ($dbAvatar ?? ''));
    if ($dbTrim === '') {
        return null;
    }

    $base = basename($dbTrim);
    if (!tcf_avatar_belongs_to_user($userId, $base)) {
        try {
            $pdo->prepare('UPDATE users SET avatar = NULL WHERE id = ?')->execute([$userId]);
        } catch (Throwable $e) {
        }
        return null;
    }

    $pathPrimary = $dir . DIRECTORY_SEPARATOR . $base;
    if (is_file($pathPrimary)) {
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
            return $fn;
        }
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
