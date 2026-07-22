<?php

declare(strict_types=1);

$script = basename($_SERVER['SCRIPT_NAME'] ?? '');
if (in_array($script, ['profile_api.php'], true)) {
    return;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || empty($_SESSION['user_id'])) {
    return;
}

$userId = (int) $_SESSION['user_id'];

if (isset($_POST['mark_as_read'], $_POST['notification_id'])) {
    global $pdo;
    $nid = (int) $_POST['notification_id'];
    $pdo->prepare(
        'UPDATE notifications SET is_read = 1 WHERE id = ? AND (user_id IS NULL OR user_id = ?)'
    )->execute([$nid, $userId]);
    tcf_profile_redirect_back();
}

if (isset($_POST['mark_all_read'])) {
    global $pdo;
    $role = (string) ($_SESSION['role'] ?? 'user');
    if (in_array($role, ['admin', 'super_admin'], true)) {
        $pdo->prepare(
            "UPDATE notifications SET is_read = 1 WHERE type IN ('video','topic','message','user','update','video_comment','testimonial','subscription','subscription_staff','exam') AND (user_id IS NULL OR user_id = ?)"
        )->execute([$userId]);
    } else {
        $pdo->prepare(
            "UPDATE notifications n
             INNER JOIN users u ON u.id = ?
             SET n.is_read = 1
             WHERE n.type IN ('video','topic','message','user','update','subscription','exam')
               AND (
                 n.user_id = ?
                 OR (
                   n.user_id IS NULL
                   AND n.created_at >= u.created_at
                   AND NOT (n.type = 'subscription' AND n.user_id IS NULL)
                 )
               )"
        )->execute([$userId, $userId]);
    }
    tcf_profile_redirect_back();
}

function tcf_profile_set_flash(string $type, string $message): void
{
    $_SESSION['profile_flash'] = ['type' => $type, 'message' => $message];
}

function tcf_profile_redirect_back(): void
{
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($ref !== '' && $host !== '') {
        $refHost = parse_url($ref, PHP_URL_HOST);
        if ($refHost && strcasecmp((string) $refHost, $host) === 0) {
            header('Location: ' . $ref);
            exit;
        }
    }
    header('Location: ' . site_href('index.php'));
    exit;
}

function tcf_profile_delete_old_avatar_file(PDO $pdo, int $uid): void
{
    $stmt = $pdo->prepare('SELECT avatar FROM users WHERE id = ?');
    $stmt->execute([$uid]);
    $old = $stmt->fetchColumn();
    if ($old && is_string($old) && $old !== '') {
        $path = tcf_avatar_storage_dir() . DIRECTORY_SEPARATOR . basename($old);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

function tcf_profile_save_avatar_file(int $userId, string $binary, string $ext): ?string
{
    $ext = strtolower($ext);
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
        return null;
    }
    $dir = tcf_avatar_storage_dir();
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $fn = 'avatar_' . $userId . '_' . time() . '.' . $ext;
    $path = $dir . '/' . $fn;
    if (file_put_contents($path, $binary) === false) {
        return null;
    }
    return $fn;
}

// --- Actions ---

if (isset($_POST['update_avatar'])) {
    global $pdo;
    $saved = null;
    $dataUrl = trim((string) ($_POST['avatar_data_url'] ?? ''));

    if ($dataUrl !== '' && preg_match('#^data:image/(jpeg|jpg|png|webp);base64,#i', $dataUrl, $m)) {
        $raw = base64_decode(substr(strstr($dataUrl, ','), 1) ?: '', true);
        if ($raw !== false && strlen($raw) > 100 && strlen($raw) < 3 * 1024 * 1024) {
            $ext = strtolower($m[1]) === 'jpeg' || strtolower($m[1]) === 'jpg' ? 'jpg' : strtolower($m[1]);
            tcf_profile_delete_old_avatar_file($pdo, $userId);
            $saved = tcf_profile_save_avatar_file($userId, $raw, $ext);
        }
    } elseif (!empty($_FILES['avatar']['tmp_name']) && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
        $err = (int) ($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err === UPLOAD_ERR_OK && ($_FILES['avatar']['size'] ?? 0) <= 3 * 1024 * 1024) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($_FILES['avatar']['tmp_name']) ?: '';
            $map = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ];
            if (isset($map[$mime])) {
                $raw = (string) file_get_contents($_FILES['avatar']['tmp_name']);
                tcf_profile_delete_old_avatar_file($pdo, $userId);
                $saved = tcf_profile_save_avatar_file($userId, $raw, $map[$mime]);
            }
        }
    }

    if ($saved) {
        try {
            $pdo->prepare('UPDATE users SET avatar = ?, updated_at = NOW() WHERE id = ?')->execute([$saved, $userId]);
        } catch (Throwable $e) {
            $pdo->prepare('UPDATE users SET avatar = ? WHERE id = ?')->execute([$saved, $userId]);
        }
        tcf_profile_set_flash('success', 'Photo de profil enregistrée.');
    } else {
        tcf_profile_set_flash('error', 'Image non valide ou trop volumineuse. Utilisez JPG, PNG ou WebP (max 3 Mo) et validez le recadrage avec OK.');
    }
    tcf_profile_redirect_back();
}
