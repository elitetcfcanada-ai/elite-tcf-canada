<?php

declare(strict_types=1);

/**
 * Playlists chaîne, publications type YouTube, liaison vidéo ↔ playlists.
 */

function tcf_parse_playlist_ids_from_post(): array
{
    $raw = $_POST['playlist_ids'] ?? '[]';
    if (is_array($raw)) {
        $ids = $raw;
    } else {
        $ids = json_decode((string) $raw, true);
    }
    if (!is_array($ids)) {
        return [];
    }
    $out = [];
    foreach ($ids as $id) {
        $i = (int) $id;
        if ($i > 0) {
            $out[] = $i;
        }
    }

    return array_values(array_unique($out));
}

function tcf_sync_video_playlists(PDO $pdo, int $videoId, array $playlistIds): void
{
    $pdo->prepare('DELETE FROM playlist_videos WHERE video_id = ?')->execute([$videoId]);
    $sort = 0;
    foreach ($playlistIds as $pid) {
        $st = $pdo->prepare('SELECT id FROM channel_playlists WHERE id = ?');
        $st->execute([$pid]);
        if (!$st->fetchColumn()) {
            continue;
        }
        $sort++;
        $pdo->prepare('INSERT INTO playlist_videos (playlist_id, video_id, sort_order) VALUES (?, ?, ?)')
            ->execute([$pid, $videoId, $sort]);
    }
}

function tcf_enrich_videos_with_playlists(PDO $pdo, array $videos): array
{
    if ($videos === []) {
        return [];
    }
    $ids = array_map(static fn ($v) => (int) $v['id'], $videos);
    $in = implode(',', array_fill(0, count($ids), '?'));
    $st = $pdo->prepare("SELECT playlist_id, video_id FROM playlist_videos WHERE video_id IN ($in)");
    $st->execute($ids);
    $map = [];
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $vid = (int) $row['video_id'];
        if (!isset($map[$vid])) {
            $map[$vid] = [];
        }
        $map[$vid][] = (int) $row['playlist_id'];
    }
    foreach ($videos as &$v) {
        $id = (int) $v['id'];
        $v['playlist_ids'] = $map[$id] ?? [];
    }
    unset($v);

    return $videos;
}

function getPlaylists()
{
    global $pdo;
    try {
        $stmt = $pdo->query(
            'SELECT p.*, u.name AS author_name,
             (SELECT COUNT(*) FROM playlist_videos pv WHERE pv.playlist_id = p.id) AS video_count
             FROM channel_playlists p
             LEFT JOIN users u ON u.id = p.created_by
             ORDER BY p.created_at DESC'
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $pid = (int) $row['id'];
            $st2 = $pdo->prepare('SELECT video_id FROM playlist_videos WHERE playlist_id = ? ORDER BY sort_order ASC, id ASC');
            $st2->execute([$pid]);
            $row['video_ids'] = array_map('intval', $st2->fetchAll(PDO::FETCH_COLUMN));
        }
        unset($row);
        echo json_encode(['success' => true, 'data' => $rows]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function savePlaylist()
{
    global $pdo;
    try {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $title = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $visibility = in_array($_POST['visibility'] ?? '', ['public', 'private'], true) ? $_POST['visibility'] : 'public';
        $videoIdsRaw = $_POST['video_ids'] ?? '[]';
        $videoIds = is_array($videoIdsRaw) ? $videoIdsRaw : json_decode((string) $videoIdsRaw, true);
        if (!is_array($videoIds)) {
            $videoIds = [];
        }
        $videoIds = array_values(array_unique(array_filter(array_map('intval', $videoIds))));

        if ($title === '' || mb_strlen($title) > 255) {
            echo json_encode(['success' => false, 'message' => 'Titre invalide.']);
            exit();
        }

        $uid = (int) $_SESSION['user_id'];

        if ($id <= 0) {
            $stmt = $pdo->prepare('INSERT INTO channel_playlists (title, description, visibility, created_by) VALUES (?, ?, ?, ?)');
            $stmt->execute([$title, $description === '' ? null : $description, $visibility, $uid]);
            $id = (int) $pdo->lastInsertId();
            addActivity($uid, 'video', 'Playlist créée', "Playlist « $title »");
        } else {
            $stmt = $pdo->prepare('UPDATE channel_playlists SET title = ?, description = ?, visibility = ? WHERE id = ?');
            $stmt->execute([$title, $description === '' ? null : $description, $visibility, $id]);
        }

        $pdo->prepare('DELETE FROM playlist_videos WHERE playlist_id = ?')->execute([$id]);
        $sort = 0;
        foreach ($videoIds as $vid) {
            $st = $pdo->prepare('SELECT id FROM videos WHERE id = ?');
            $st->execute([$vid]);
            if (!$st->fetchColumn()) {
                continue;
            }
            $sort++;
            $pdo->prepare('INSERT INTO playlist_videos (playlist_id, video_id, sort_order) VALUES (?, ?, ?)')
                ->execute([$id, $vid, $sort]);
        }

        echo json_encode(['success' => true, 'message' => 'Playlist enregistrée.', 'id' => $id]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function deletePlaylist()
{
    global $pdo;
    try {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identifiant invalide.']);
            exit();
        }
        $pdo->prepare('DELETE FROM channel_playlists WHERE id = ?')->execute([$id]);
        addActivity((int) $_SESSION['user_id'], 'video', 'Playlist supprimée', "Playlist #$id supprimée");
        echo json_encode(['success' => true, 'message' => 'Playlist supprimée.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

/**
 * @return list<string>
 */
function tcf_channel_normalize_poll_options(string $raw): array
{
    $lines = preg_split('/\r\n|\r|\n/', $raw);
    $out = [];
    foreach ($lines as $line) {
        $t = trim((string) $line);
        if ($t !== '') {
            $out[] = $t;
        }
    }

    return array_values(array_slice($out, 0, 10));
}

/**
 * @return array{ok:bool, path:string, message:string}
 */
function tcf_channel_handle_post_image_upload(): array
{
    if (empty($_FILES['image']) || !is_array($_FILES['image'])) {
        return ['ok' => false, 'path' => '', 'message' => ''];
    }
    $f = $_FILES['image'];
    $err = (int) ($f['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($err === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'path' => '', 'message' => ''];
    }
    if ($err !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'path' => '', 'message' => 'Envoi du fichier impossible.'];
    }
    $tmp = (string) ($f['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return ['ok' => false, 'path' => '', 'message' => 'Fichier invalide.'];
    }
    $fi = new finfo(FILEINFO_MIME_TYPE);
    $mime = $fi->file($tmp) ?: '';
    $extMap = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    if (!isset($extMap[$mime])) {
        return ['ok' => false, 'path' => '', 'message' => 'Image JPG, PNG, WebP ou GIF uniquement.'];
    }
    $max = 5 * 1024 * 1024;
    $size = (int) ($f['size'] ?? 0);
    if ($size <= 0 || $size > $max) {
        return ['ok' => false, 'path' => '', 'message' => 'Image trop volumineuse (max 5 Mo).'];
    }
    $root = realpath(dirname(__DIR__));
    if ($root === false) {
        return ['ok' => false, 'path' => '', 'message' => 'Erreur serveur.'];
    }
    $dir = $root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'channel_posts';
    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
        return ['ok' => false, 'path' => '', 'message' => 'Impossible de créer le dossier d’upload.'];
    }
    $base = 'chp_' . bin2hex(random_bytes(8)) . '.' . $extMap[$mime];
    $dest = $dir . DIRECTORY_SEPARATOR . $base;
    if (!@move_uploaded_file($tmp, $dest)) {
        return ['ok' => false, 'path' => '', 'message' => 'Enregistrement de l’image impossible.'];
    }

    return ['ok' => true, 'path' => 'uploads/channel_posts/' . $base, 'message' => ''];
}

function tcf_channel_unlink_post_image(?string $stored): void
{
    if ($stored === null || $stored === '') {
        return;
    }
    $fs = tcf_uploads_fs_path($stored);
    if ($fs !== '' && is_file($fs)) {
        @unlink($fs);
    }
}

function getChannelPosts()
{
    global $pdo;
    try {
        $stmt = $pdo->query(
            'SELECT cp.*, u.name AS author_name,
             v.title AS video_title
             FROM channel_posts cp
             LEFT JOIN users u ON u.id = cp.author_user_id
             LEFT JOIN videos v ON v.id = cp.video_id
             ORDER BY cp.created_at DESC'
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['image_public_href'] = tcf_uploads_public_href($row['image_url'] ?? '');
            $decoded = json_decode((string) ($row['poll_options_json'] ?? ''), true);
            $row['poll_options'] = is_array($decoded) ? $decoded : [];
            $row['poll_counts'] = [];
            $row['poll_pcts'] = [];
            if (($row['post_type'] ?? '') === 'poll' && $row['poll_options'] !== []) {
                $n = count($row['poll_options']);
                $counts = array_fill(0, $n, 0);
                $pid = (int) ($row['id'] ?? 0);
                if ($pid > 0) {
                    try {
                        $stc = $pdo->prepare('SELECT option_index, COUNT(*) AS c FROM channel_post_poll_votes WHERE post_id = ? GROUP BY option_index');
                        $stc->execute([$pid]);
                        foreach ($stc->fetchAll(PDO::FETCH_ASSOC) as $vr) {
                            $ix = (int) $vr['option_index'];
                            if ($ix >= 0 && $ix < $n) {
                                $counts[$ix] = (int) $vr['c'];
                            }
                        }
                    } catch (Throwable $e) {
                    }
                }
                $row['poll_counts'] = $counts;
                $totalVotes = array_sum($counts);
                $pcts = [];
                foreach ($counts as $c) {
                    $pcts[] = $totalVotes > 0 ? (int) round((100 * $c) / $totalVotes) : 0;
                }
                $row['poll_pcts'] = $pcts;
            }
        }
        unset($row);
        echo json_encode(['success' => true, 'data' => $rows]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function saveChannelPost()
{
    global $pdo;
    try {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $postType = (string) ($_POST['post_type'] ?? 'text');
        if (!in_array($postType, ['text', 'image', 'poll'], true)) {
            $postType = 'text';
        }
        $title = trim((string) ($_POST['title'] ?? ''));
        $title = $title === '' ? null : $title;
        $body = trim((string) ($_POST['body'] ?? ''));
        $visibility = in_array($_POST['visibility'] ?? '', ['public', 'private'], true) ? $_POST['visibility'] : 'public';
        $videoId = isset($_POST['video_id']) && $_POST['video_id'] !== '' ? (int) $_POST['video_id'] : null;
        $removeImage = isset($_POST['remove_image']) && (string) $_POST['remove_image'] === '1';
        $pollOptions = tcf_channel_normalize_poll_options((string) ($_POST['poll_options'] ?? ''));
        $pollJson = null;

        if (mb_strlen($body) > 8000) {
            echo json_encode(['success' => false, 'message' => 'Texte trop long (max 8000 caractères).']);
            exit();
        }

        if ($postType === 'poll') {
            if (count($pollOptions) < 2) {
                echo json_encode(['success' => false, 'message' => 'Sondage : indiquez au moins 2 options (une par ligne).']);
                exit();
            }
            $pollJson = json_encode($pollOptions, JSON_UNESCAPED_UNICODE);
            if ($title === null && $body === '') {
                echo json_encode(['success' => false, 'message' => 'Sondage : renseignez une question (titre ou texte).']);
                exit();
            }
        }

        if ($postType === 'text') {
            if ($title === null && $body === '') {
                echo json_encode(['success' => false, 'message' => 'Renseignez un titre ou un texte.']);
                exit();
            }
        }

        if ($videoId !== null && $videoId > 0) {
            $st = $pdo->prepare('SELECT id FROM videos WHERE id = ?');
            $st->execute([$videoId]);
            if (!$st->fetchColumn()) {
                $videoId = null;
            }
        } else {
            $videoId = null;
        }

        $uid = (int) $_SESSION['user_id'];
        $oldRow = null;
        if ($id > 0) {
            $st = $pdo->prepare('SELECT author_user_id, post_type, image_url, poll_options_json FROM channel_posts WHERE id = ?');
            $st->execute([$id]);
            $oldRow = $st->fetch(PDO::FETCH_ASSOC);
            if (!$oldRow) {
                echo json_encode(['success' => false, 'message' => 'Publication introuvable.']);
                exit();
            }
        }

        $upload = tcf_channel_handle_post_image_upload();
        if ($upload['message'] !== '' && !$upload['ok']) {
            echo json_encode(['success' => false, 'message' => $upload['message']]);
            exit();
        }

        $imageUrl = null;
        if ($postType === 'image') {
            if ($upload['ok'] && $upload['path'] !== '') {
                if ($oldRow !== null && !empty($oldRow['image_url']) && $oldRow['image_url'] !== $upload['path']) {
                    tcf_channel_unlink_post_image($oldRow['image_url']);
                }
                $imageUrl = $upload['path'];
            } elseif ($id > 0 && $oldRow !== null) {
                if ($removeImage) {
                    tcf_channel_unlink_post_image($oldRow['image_url'] ?? null);
                    $imageUrl = null;
                } else {
                    $imageUrl = $oldRow['image_url'] ?: null;
                }
            }
            if ($imageUrl === null || $imageUrl === '') {
                echo json_encode(['success' => false, 'message' => 'Publication image : ajoutez une image (JPG, PNG, WebP ou GIF).']);
                exit();
            }
        } else {
            if ($oldRow !== null && (($oldRow['post_type'] ?? '') === 'image' || !empty($oldRow['image_url']))) {
                tcf_channel_unlink_post_image($oldRow['image_url'] ?? null);
            }
            $imageUrl = null;
        }

        if ($postType !== 'poll') {
            $pollJson = null;
        }

        if ($id > 0 && $postType === 'poll' && $oldRow !== null && $pollJson !== null) {
            $oldPoll = (string) ($oldRow['poll_options_json'] ?? '');
            if ($oldPoll !== $pollJson) {
                try {
                    $pdo->prepare('DELETE FROM channel_post_poll_votes WHERE post_id = ?')->execute([$id]);
                } catch (PDOException $e) {
                    // table absente si migration non appliquée
                }
            }
        }

        if ($id <= 0) {
            $stmt = $pdo->prepare(
                'INSERT INTO channel_posts (author_user_id, post_type, title, body, image_url, poll_options_json, video_id, visibility) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$uid, $postType, $title, $body, $imageUrl, $pollJson, $videoId, $visibility]);
            addActivity($uid, 'message', 'Publication chaîne', 'Nouvelle publication');
        } else {
            $stmt = $pdo->prepare(
                'UPDATE channel_posts SET post_type = ?, title = ?, body = ?, image_url = ?, poll_options_json = ?, video_id = ?, visibility = ? WHERE id = ?'
            );
            $stmt->execute([$postType, $title, $body, $imageUrl, $pollJson, $videoId, $visibility, $id]);
        }

        echo json_encode(['success' => true, 'message' => 'Publication enregistrée.']);
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        if (str_contains($msg, 'post_type') || str_contains($msg, 'Unknown column')) {
            echo json_encode(['success' => false, 'message' => 'Base à jour requise : importez database/tcf.sql.']);
            exit();
        }
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $msg]);
    }
    exit();
}

function deleteChannelPost()
{
    global $pdo;
    try {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identifiant invalide.']);
            exit();
        }
        $st = $pdo->prepare('SELECT image_url FROM channel_posts WHERE id = ?');
        $st->execute([$id]);
        $img = $st->fetchColumn();
        if ($img !== false && $img !== null && (string) $img !== '') {
            tcf_channel_unlink_post_image((string) $img);
        }
        $pdo->prepare('DELETE FROM channel_posts WHERE id = ?')->execute([$id]);
        addActivity((int) $_SESSION['user_id'], 'message', 'Publication supprimée', "Post #$id");
        echo json_encode(['success' => true, 'message' => 'Publication supprimée.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function getChannelBranding(): void
{
    global $pdo;
    require_once __DIR__ . '/../includes/channel_branding.php';
    try {
        $data = tcf_channel_branding_for_admin($pdo);
        echo json_encode(['success' => true, 'data' => $data]);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

function saveChannelBranding(): void
{
    global $pdo;
    require_once __DIR__ . '/../includes/channel_branding.php';
    try {
        $title = trim((string) ($_POST['title'] ?? ''));
        $tagline = trim((string) ($_POST['tagline'] ?? ''));
        if (mb_strlen($title) > 255) {
            echo json_encode(['success' => false, 'message' => 'Titre trop long (255 caractères max).']);
            exit();
        }
        if (mb_strlen($tagline) > 800) {
            echo json_encode(['success' => false, 'message' => 'Description trop longue (800 caractères max).']);
            exit();
        }

        $logoPath = null;
        $bannerPath = null;
        try {
            $st = $pdo->query('SELECT logo_url, banner_url FROM channel_branding WHERE id = 1 LIMIT 1');
            $row = $st ? $st->fetch(PDO::FETCH_ASSOC) : false;
            if ($row) {
                if (!empty($row['logo_url'])) {
                    $logoPath = (string) $row['logo_url'];
                }
                if (!empty($row['banner_url'])) {
                    $bannerPath = (string) $row['banner_url'];
                }
            }
        } catch (Throwable $e) {
            $logoPath = null;
            $bannerPath = null;
        }

        if (!empty($_FILES['logo']['tmp_name']) && is_uploaded_file((string) $_FILES['logo']['tmp_name'])) {
            $up = uploadFile($_FILES['logo'], 'channel');
            if ($up === false) {
                echo json_encode(['success' => false, 'message' => 'Logo : format ou taille invalide (JPG, PNG, WebP, GIF — max 8 Mo).']);
                exit();
            }
            if ($logoPath !== null && $logoPath !== '') {
                tcf_admin_unlink_upload($logoPath);
            }
            $logoPath = $up;
        } elseif (!empty($_POST['remove_logo'])) {
            if ($logoPath !== null && $logoPath !== '') {
                tcf_admin_unlink_upload($logoPath);
            }
            $logoPath = null;
        }

        if (!empty($_FILES['banner']['tmp_name']) && is_uploaded_file((string) $_FILES['banner']['tmp_name'])) {
            $up = uploadFile($_FILES['banner'], 'channel');
            if ($up === false) {
                echo json_encode(['success' => false, 'message' => 'Bannière : format ou taille invalide (JPG, PNG, WebP, GIF — max 8 Mo).']);
                exit();
            }
            if ($bannerPath !== null && $bannerPath !== '') {
                tcf_admin_unlink_upload($bannerPath);
            }
            $bannerPath = $up;
        } elseif (!empty($_POST['remove_banner'])) {
            if ($bannerPath !== null && $bannerPath !== '') {
                tcf_admin_unlink_upload($bannerPath);
            }
            $bannerPath = null;
        }

        $sql = 'INSERT INTO channel_branding (id, title, tagline, logo_url, banner_url) VALUES (1, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE title = VALUES(title), tagline = VALUES(tagline), logo_url = VALUES(logo_url), banner_url = VALUES(banner_url)';
        $pdo->prepare($sql)->execute([$title, $tagline, $logoPath, $bannerPath]);
        addActivity((int) $_SESSION['user_id'], 'video', 'Identité chaîne', 'Paramètres chaîne mis à jour');

        $data = tcf_channel_branding_for_admin($pdo);
        echo json_encode(['success' => true, 'message' => 'Identité de la chaîne enregistrée.', 'data' => $data]);
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        if (str_contains($msg, 'channel_branding') || str_contains($msg, 'Unknown table')) {
            echo json_encode(['success' => false, 'message' => 'Importez database/tcf.sql (table channel_branding).']);
            exit();
        }
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $msg]);
    }
    exit();
}
