<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/admin_notifications.php';
require_once __DIR__ . '/includes/subscription_access.php';
require_once __DIR__ . '/includes/channel_branding.php';
require_once __DIR__ . '/includes/video_social.php';
require_once __DIR__ . '/includes/tcf_schema.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (($method === 'POST' || $method === 'PUT') && !empty($_SERVER['CONTENT_TYPE']) && strpos((string) $_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $rawIn = (string) file_get_contents('php://input');
    if ($rawIn !== '') {
        $j = json_decode($rawIn, true);
        if (is_array($j)) {
            $_POST = array_merge($_POST, $j);
        }
    }
}

function tcf_videos_api_json(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function tcf_videos_api_staff(): bool
{
    $r = $_SESSION['role'] ?? '';
    return $r === 'admin' || $r === 'super_admin';
}

/**
 * Réponses admin : nom + avatar = identité chaîne (paramètres chaîne). Commentaires racine : nom utilisateur.
 *
 * @param array{title: string, logo_href: string} $brand
 * @return array{user_name: string, avatar_url: string, is_staff: bool}
 */
function tcf_videos_api_comment_public_face(array $brand, string $dbUserName, string $userRole, mixed $parentId): array
{
    $staff = in_array($userRole, ['admin', 'super_admin'], true);
    $isReply = $parentId !== null && $parentId !== '';
    $asChannel = $staff && $isReply;

    return [
        'user_name' => $asChannel ? $brand['title'] : $dbUserName,
        'avatar_url' => $asChannel ? (string) ($brand['logo_href'] ?? '') : '',
        'is_staff' => $staff,
    ];
}

function tcf_videos_api_require_login(): int
{
    if (empty($_SESSION['user_id'])) {
        tcf_videos_api_json(['ok' => false, 'message' => 'Connexion requise.'], 401);
    }
    return (int) $_SESSION['user_id'];
}

function tcf_videos_api_sync_likes_count(PDO $pdo, int $videoId): int
{
    $st = $pdo->prepare('SELECT likes_json, likes FROM videos WHERE id = ? LIMIT 1');
    $st->execute([$videoId]);
    $row = $st->fetch(PDO::FETCH_ASSOC) ?: [];
    $likes = tcf_video_decode_likes(isset($row['likes_json']) ? (string) $row['likes_json'] : null);
    $n = count($likes);
    if ($n !== (int) ($row['likes'] ?? 0)) {
        $pdo->prepare('UPDATE videos SET likes = ? WHERE id = ?')->execute([$n, $videoId]);
    }
    return $n;
}

function tcf_videos_api_video_exists(PDO $pdo, int $videoId): bool
{
    $st = $pdo->prepare('SELECT 1 FROM videos WHERE id = ? LIMIT 1');
    $st->execute([$videoId]);

    return (bool) $st->fetchColumn();
}

/**
 * @return array<string, mixed>|null
 */
function tcf_videos_api_channel_post_public(PDO $pdo, int $postId): ?array
{
    $st = $pdo->prepare("SELECT id, post_type, poll_options_json FROM channel_posts WHERE id = ? AND visibility = 'public'");
    $st->execute([$postId]);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

/**
 * @return array<string, mixed>|null
 */
function tcf_videos_api_channel_post_social_state(PDO $pdo, int $postId, int $userId): ?array
{
    $post = tcf_videos_api_channel_post_public($pdo, $postId);
    if ($post === null) {
        return null;
    }
    $st = $pdo->prepare('SELECT COUNT(*) FROM channel_post_likes WHERE post_id = ?');
    $st->execute([$postId]);
    $likes = (int) $st->fetchColumn();
    $userLiked = false;
    if ($userId > 0) {
        $st = $pdo->prepare('SELECT 1 FROM channel_post_likes WHERE post_id = ? AND user_id = ?');
        $st->execute([$postId, $userId]);
        $userLiked = (bool) $st->fetchColumn();
    }
    $out = [
        'likes' => $likes,
        'user_liked' => $userLiked,
        'post_type' => (string) ($post['post_type'] ?? 'text'),
        'poll' => null,
    ];
    if (($post['post_type'] ?? '') === 'poll') {
        $opts = json_decode((string) ($post['poll_options_json'] ?? ''), true);
        if (!is_array($opts)) {
            $opts = [];
        }
        $n = count($opts);
        $counts = array_fill(0, $n, 0);
        $st = $pdo->prepare('SELECT option_index, COUNT(*) AS c FROM channel_post_poll_votes WHERE post_id = ? GROUP BY option_index');
        $st->execute([$postId]);
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $ix = (int) $r['option_index'];
            if ($ix >= 0 && $ix < $n) {
                $counts[$ix] = (int) $r['c'];
            }
        }
        $userVote = null;
        $voteLocked = false;
        if ($userId > 0) {
            $st = $pdo->prepare('SELECT option_index, voted_at FROM channel_post_poll_votes WHERE post_id = ? AND user_id = ?');
            $st->execute([$postId, $userId]);
            $vrow = $st->fetch(PDO::FETCH_ASSOC);
            if ($vrow && isset($vrow['option_index'])) {
                $userVote = (int) $vrow['option_index'];
                $ts = strtotime((string) ($vrow['voted_at'] ?? ''));
                if ($ts !== false && (time() - $ts) >= 60) {
                    $voteLocked = true;
                }
            }
        }
        $out['poll'] = [
            'options' => $opts,
            'counts' => $counts,
            'user_vote' => $userVote,
            'vote_locked' => $voteLocked,
        ];
    }

    return $out;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

try {

    switch ($action) {

        case 'state': {
            if ($method !== 'GET') {
                tcf_videos_api_json(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
            }
            $vid = (int) ($_GET['video_id'] ?? 0);
            if ($vid <= 0) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Vidéo invalide.'], 400);
            }
            if (!tcf_videos_api_video_exists($pdo, $vid)) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Vidéo introuvable.'], 404);
            }
            $uid = !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
            $st = $pdo->prepare('SELECT likes, views, likes_json FROM videos WHERE id = ?');
            $st->execute([$vid]);
            $row = $st->fetch(PDO::FETCH_ASSOC) ?: [];
            $likesArr = tcf_video_decode_likes(isset($row['likes_json']) ? (string) $row['likes_json'] : null);
            $likes = count($likesArr) > 0 ? count($likesArr) : (int) ($row['likes'] ?? 0);
            $views = (int) ($row['views'] ?? 0);
            $userLiked = $uid > 0 ? tcf_video_user_liked($likesArr, $uid) : false;
            tcf_videos_api_json([
                'ok' => true,
                'likes' => $likes,
                'views' => $views,
                'user_liked' => $userLiked,
                'logged_in' => $uid > 0,
                'is_staff' => tcf_videos_api_staff(),
            ]);
        }

        case 'view': {
            if ($method !== 'POST') {
                tcf_videos_api_json(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
            }
            $vid = (int) ($_POST['video_id'] ?? 0);
            if ($vid <= 0) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Vidéo invalide.'], 400);
            }
            if (!tcf_videos_api_video_exists($pdo, $vid)) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Vidéo introuvable.'], 404);
            }
            // Compteur de vues : aligné sur watch.php (public + premium lisibles pour tous)
            if (!isset($_SESSION['tcf_video_view_once']) || !is_array($_SESSION['tcf_video_view_once'])) {
                $_SESSION['tcf_video_view_once'] = [];
            }
            $counted = empty($_SESSION['tcf_video_view_once'][$vid]);
            if ($counted) {
                $_SESSION['tcf_video_view_once'][$vid] = 1;
                $pdo->prepare('UPDATE videos SET views = COALESCE(views, 0) + 1 WHERE id = ?')->execute([$vid]);
            }
            $st = $pdo->prepare('SELECT views FROM videos WHERE id = ?');
            $st->execute([$vid]);
            $views = (int) $st->fetchColumn();
            tcf_videos_api_json(['ok' => true, 'views' => $views, 'counted' => $counted]);
        }

        case 'comments': {
            if ($method !== 'GET') {
                tcf_videos_api_json(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
            }
            $vid = (int) ($_GET['video_id'] ?? 0);
            if ($vid <= 0) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Vidéo invalide.'], 400);
            }
            if (!tcf_videos_api_video_exists($pdo, $vid)) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Vidéo introuvable.'], 404);
            }
            $st = $pdo->prepare('SELECT comments_json FROM videos WHERE id = ? LIMIT 1');
            $st->execute([$vid]);
            $raw = $st->fetchColumn();
            $rows = tcf_video_comments_with_users($pdo, tcf_video_decode_comments(is_string($raw) ? $raw : null));
            usort($rows, static function (array $a, array $b): int {
                $ap = (($a['parent_id'] ?? null) === null || $a['parent_id'] === '') ? 0 : 1;
                $bp = (($b['parent_id'] ?? null) === null || $b['parent_id'] === '') ? 0 : 1;
                if ($ap !== $bp) {
                    return $ap <=> $bp;
                }
                return strcmp((string) ($a['created_at'] ?? ''), (string) ($b['created_at'] ?? ''));
            });
            $brand = tcf_channel_branding_front($pdo);
            $roots = [];
            $repliesByParent = [];
            foreach ($rows as $r) {
                $pid = $r['parent_id'] ?? null;
                $face = tcf_videos_api_comment_public_face(
                    $brand,
                    (string) ($r['user_name'] ?? 'Utilisateur'),
                    (string) ($r['user_role'] ?? 'user'),
                    $pid
                );
                $entry = [
                    'id' => (int) ($r['id'] ?? 0),
                    'user_name' => $face['user_name'],
                    'avatar_url' => $face['avatar_url'],
                    'body' => (string) ($r['body'] ?? ''),
                    'created_at' => (string) ($r['created_at'] ?? ''),
                    'is_staff' => $face['is_staff'],
                ];
                if ($pid === null || $pid === '') {
                    $roots[] = $entry;
                } else {
                    $pi = (int) $pid;
                    if (!isset($repliesByParent[$pi])) {
                        $repliesByParent[$pi] = [];
                    }
                    $repliesByParent[$pi][] = $entry;
                }
            }
            foreach ($roots as &$root) {
                $rid = $root['id'];
                $root['replies'] = $repliesByParent[$rid] ?? [];
            }
            unset($root);
            tcf_videos_api_json(['ok' => true, 'comments' => $roots]);
        }

        case 'like': {
            if ($method !== 'POST') {
                tcf_videos_api_json(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
            }
            $uid = tcf_videos_api_require_login();
            $vid = (int) ($_POST['video_id'] ?? 0);
            if ($vid <= 0) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Vidéo invalide.'], 400);
            }
            if (!tcf_videos_api_video_exists($pdo, $vid)) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Vidéo introuvable.'], 404);
            }
            $tog = tcf_video_toggle_like($pdo, $vid, $uid);
            tcf_videos_api_json(['ok' => true, 'likes' => $tog['likes'], 'user_liked' => $tog['user_liked']]);
        }

        case 'comment': {
            if ($method !== 'POST') {
                tcf_videos_api_json(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
            }
            $uid = tcf_videos_api_require_login();
            $vid = (int) ($_POST['video_id'] ?? 0);
            $body = trim((string) ($_POST['body'] ?? ''));
            if ($vid <= 0) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Vidéo invalide.'], 400);
            }
            if ($body === '' || mb_strlen($body) > 2000) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Message invalide (1 à 2000 caractères).'], 400);
            }
            if (!tcf_videos_api_video_exists($pdo, $vid)) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Vidéo introuvable.'], 404);
            }
            $body = preg_replace("/[ \t]+/u", ' ', $body) ?? $body;
            $body = preg_replace("/\n{3,}/u", "\n\n", $body) ?? $body;
            $body = trim($body);
            $created = tcf_video_add_comment($pdo, $vid, $uid, $body, null);
            $newId = (int) ($created['id'] ?? 0);
            if (!tcf_videos_api_staff()) {
                $stT = $pdo->prepare('SELECT title FROM videos WHERE id = ?');
                $stT->execute([$vid]);
                $vtitle = (string) ($stT->fetchColumn() ?: 'Vidéo');
                $stN = $pdo->prepare('SELECT name FROM users WHERE id = ?');
                $stN->execute([$uid]);
                $uname = (string) ($stN->fetchColumn() ?: 'Utilisateur');
                $snippet = mb_strlen($body) > 120 ? mb_substr($body, 0, 120) . '…' : $body;
                $dl = 'admin/superAdmin.php?sa_focus=video_comment&video_id=' . $vid . '&comment_id=' . $newId;
                tcf_add_staff_notification(
                    $pdo,
                    'video_comment',
                    'Nouveau commentaire sur une vidéo',
                    $uname . ' : « ' . $snippet . ' » — ' . $vtitle,
                    $dl
                );
            }
            tcf_videos_api_json(['ok' => true, 'message' => 'Commentaire publié.', 'comment_id' => $newId]);
        }

        case 'reply': {
            if ($method !== 'POST') {
                tcf_videos_api_json(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
            }
            if (!tcf_videos_api_staff()) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Réservé aux administrateurs.'], 403);
            }
            $uid = tcf_videos_api_require_login();
            $vid = (int) ($_POST['video_id'] ?? 0);
            $parentId = (int) ($_POST['parent_id'] ?? 0);
            $body = trim((string) ($_POST['body'] ?? ''));
            if ($vid <= 0 || $parentId <= 0) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Paramètres invalides.'], 400);
            }
            if ($body === '' || mb_strlen($body) > 2000) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Message invalide (1 à 2000 caractères).'], 400);
            }
            $body = preg_replace("/[ \t]+/u", ' ', $body) ?? $body;
            $body = preg_replace("/\n{3,}/u", "\n\n", $body) ?? $body;
            $body = trim($body);
            $st = $pdo->prepare('SELECT comments_json FROM videos WHERE id = ? LIMIT 1');
            $st->execute([$vid]);
            $comments = tcf_video_decode_comments((string) ($st->fetchColumn() ?: ''));
            $parent = null;
            foreach ($comments as $c) {
                if ((int) ($c['id'] ?? 0) === $parentId) {
                    $parent = $c;
                    break;
                }
            }
            if (!$parent) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Commentaire introuvable.'], 404);
            }
            if (($parent['parent_id'] ?? null) !== null && $parent['parent_id'] !== '') {
                tcf_videos_api_json(['ok' => false, 'message' => 'Vous ne pouvez répondre qu’au commentaire principal.'], 400);
            }
            tcf_video_add_comment($pdo, $vid, $uid, $body, $parentId);
            tcf_videos_api_json(['ok' => true, 'message' => 'Réponse publiée.']);
        }

        case 'channel_state': {
            if ($method !== 'GET') {
                tcf_videos_api_json(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
            }
            $count = 0;
            $subscribed = false;
            $uid = !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
            try {
                if (tcf_schema_has_table($pdo, 'channel_subscribers')) {
                    $count = (int) $pdo->query('SELECT COUNT(*) FROM channel_subscribers')->fetchColumn();
                    if ($uid > 0) {
                        $st = $pdo->prepare('SELECT 1 FROM channel_subscribers WHERE user_id = ?');
                        $st->execute([$uid]);
                        $subscribed = (bool) $st->fetchColumn();
                    }
                }
            } catch (Throwable $e) {
                $count = 0;
                $subscribed = false;
            }
            $videoCount = (int) $pdo->query('SELECT COUNT(*) FROM videos')->fetchColumn();
            tcf_videos_api_json([
                'ok' => true,
                'subscribers' => $count,
                'subscribed' => $subscribed,
                'video_count' => $videoCount,
                'logged_in' => $uid > 0,
            ]);
        }

        case 'channel_subscribe': {
            if ($method !== 'POST') {
                tcf_videos_api_json(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
            }
            $uid = tcf_videos_api_require_login();
            $st = $pdo->prepare('SELECT 1 FROM channel_subscribers WHERE user_id = ?');
            $st->execute([$uid]);
            if ($st->fetchColumn()) {
                $pdo->prepare('DELETE FROM channel_subscribers WHERE user_id = ?')->execute([$uid]);
                $subscribed = false;
            } else {
                $pdo->prepare('INSERT INTO channel_subscribers (user_id) VALUES (?)')->execute([$uid]);
                $subscribed = true;
            }
            $st = $pdo->query('SELECT COUNT(*) FROM channel_subscribers');
            $count = (int) $st->fetchColumn();
            $videoCount = (int) $pdo->query('SELECT COUNT(*) FROM videos')->fetchColumn();
            tcf_videos_api_json([
                'ok' => true,
                'subscribers' => $count,
                'subscribed' => $subscribed,
                'video_count' => $videoCount,
            ]);
        }

        case 'channel_post_state': {
            if ($method !== 'GET') {
                tcf_videos_api_json(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
            }
            $pid = (int) ($_GET['post_id'] ?? 0);
            if ($pid <= 0) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Publication invalide.'], 400);
            }
            $uid = !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
            $state = tcf_videos_api_channel_post_social_state($pdo, $pid, $uid);
            if ($state === null) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Publication introuvable.'], 404);
            }
            $out = array_merge(['ok' => true, 'logged_in' => $uid > 0, 'is_staff' => tcf_videos_api_staff()], $state);
            tcf_videos_api_json($out);
        }

        case 'channel_posts_state': {
            if ($method !== 'GET') {
                tcf_videos_api_json(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
            }
            $raw = (string) ($_GET['ids'] ?? '');
            $parts = preg_split('/\s*,\s*/', $raw);
            $ids = [];
            foreach ($parts as $p) {
                $i = (int) $p;
                if ($i > 0) {
                    $ids[] = $i;
                }
            }
            $ids = array_slice(array_unique($ids), 0, 40);
            $uid = !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
            $byId = [];
            foreach ($ids as $pid) {
                $state = tcf_videos_api_channel_post_social_state($pdo, $pid, $uid);
                if ($state !== null) {
                    $byId[(string) $pid] = $state;
                }
            }
            tcf_videos_api_json([
                'ok' => true,
                'logged_in' => $uid > 0,
                'is_staff' => tcf_videos_api_staff(),
                'by_id' => $byId,
            ]);
        }

        case 'channel_post_comments': {
            if ($method !== 'GET') {
                tcf_videos_api_json(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
            }
            $pid = (int) ($_GET['post_id'] ?? 0);
            if ($pid <= 0) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Publication invalide.'], 400);
            }
            if (tcf_videos_api_channel_post_public($pdo, $pid) === null) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Publication introuvable.'], 404);
            }
            $st = $pdo->prepare(
                'SELECT c.id, c.post_id, c.user_id, c.parent_id, c.body, c.created_at, u.name AS user_name, u.role AS user_role
                 FROM channel_post_comments c
                 INNER JOIN users u ON u.id = c.user_id
                 WHERE c.post_id = ?
                 ORDER BY c.parent_id IS NULL DESC, c.created_at ASC'
            );
            $st->execute([$pid]);
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            $brand = tcf_channel_branding_front($pdo);
            $roots = [];
            $repliesByParent = [];
            foreach ($rows as $r) {
                $pcom = $r['parent_id'];
                $face = tcf_videos_api_comment_public_face(
                    $brand,
                    (string) $r['user_name'],
                    (string) $r['user_role'],
                    $pcom
                );
                $entry = [
                    'id' => (int) $r['id'],
                    'user_name' => $face['user_name'],
                    'avatar_url' => $face['avatar_url'],
                    'body' => (string) $r['body'],
                    'created_at' => (string) $r['created_at'],
                    'is_staff' => $face['is_staff'],
                ];
                if ($pcom === null || $pcom === '') {
                    $roots[] = $entry;
                } else {
                    $ppi = (int) $pcom;
                    if (!isset($repliesByParent[$ppi])) {
                        $repliesByParent[$ppi] = [];
                    }
                    $repliesByParent[$ppi][] = $entry;
                }
            }
            foreach ($roots as &$root) {
                $rid = $root['id'];
                $root['replies'] = $repliesByParent[$rid] ?? [];
            }
            unset($root);
            tcf_videos_api_json(['ok' => true, 'comments' => $roots]);
        }

        case 'channel_post_like': {
            if ($method !== 'POST') {
                tcf_videos_api_json(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
            }
            $uid = tcf_videos_api_require_login();
            $pid = (int) ($_POST['post_id'] ?? 0);
            if ($pid <= 0) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Publication invalide.'], 400);
            }
            if (tcf_videos_api_channel_post_public($pdo, $pid) === null) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Publication introuvable.'], 404);
            }
            $st = $pdo->prepare('SELECT 1 FROM channel_post_likes WHERE post_id = ? AND user_id = ?');
            $st->execute([$pid, $uid]);
            if ($st->fetchColumn()) {
                $pdo->prepare('DELETE FROM channel_post_likes WHERE post_id = ? AND user_id = ?')->execute([$pid, $uid]);
                $userLiked = false;
            } else {
                $pdo->prepare('INSERT INTO channel_post_likes (post_id, user_id) VALUES (?, ?)')->execute([$pid, $uid]);
                $userLiked = true;
            }
            $st = $pdo->prepare('SELECT COUNT(*) FROM channel_post_likes WHERE post_id = ?');
            $st->execute([$pid]);
            $likes = (int) $st->fetchColumn();
            tcf_videos_api_json(['ok' => true, 'likes' => $likes, 'user_liked' => $userLiked]);
        }

        case 'channel_post_comment': {
            if ($method !== 'POST') {
                tcf_videos_api_json(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
            }
            $uid = tcf_videos_api_require_login();
            $pid = (int) ($_POST['post_id'] ?? 0);
            $body = trim((string) ($_POST['body'] ?? ''));
            if ($pid <= 0) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Publication invalide.'], 400);
            }
            if ($body === '' || mb_strlen($body) > 2000) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Message invalide (1 à 2000 caractères).'], 400);
            }
            if (tcf_videos_api_channel_post_public($pdo, $pid) === null) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Publication introuvable.'], 404);
            }
            $body = preg_replace("/[ \t]+/u", ' ', $body) ?? $body;
            $body = preg_replace("/\n{3,}/u", "\n\n", $body) ?? $body;
            $body = trim($body);
            $pdo->prepare('INSERT INTO channel_post_comments (post_id, user_id, parent_id, body) VALUES (?, ?, NULL, ?)')
                ->execute([$pid, $uid, $body]);
            tcf_videos_api_json(['ok' => true, 'message' => 'Commentaire publié.']);
        }

        case 'channel_post_reply': {
            if ($method !== 'POST') {
                tcf_videos_api_json(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
            }
            if (!tcf_videos_api_staff()) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Réservé aux administrateurs.'], 403);
            }
            $uid = tcf_videos_api_require_login();
            $pid = (int) ($_POST['post_id'] ?? 0);
            $parentId = (int) ($_POST['parent_id'] ?? 0);
            $body = trim((string) ($_POST['body'] ?? ''));
            if ($pid <= 0 || $parentId <= 0) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Paramètres invalides.'], 400);
            }
            if ($body === '' || mb_strlen($body) > 2000) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Message invalide (1 à 2000 caractères).'], 400);
            }
            if (tcf_videos_api_channel_post_public($pdo, $pid) === null) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Publication introuvable.'], 404);
            }
            $body = preg_replace("/[ \t]+/u", ' ', $body) ?? $body;
            $body = preg_replace("/\n{3,}/u", "\n\n", $body) ?? $body;
            $body = trim($body);
            $st = $pdo->prepare('SELECT id, post_id, parent_id FROM channel_post_comments WHERE id = ?');
            $st->execute([$parentId]);
            $parent = $st->fetch(PDO::FETCH_ASSOC);
            if (!$parent || (int) $parent['post_id'] !== $pid) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Commentaire introuvable.'], 404);
            }
            if ($parent['parent_id'] !== null) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Vous ne pouvez répondre qu’au commentaire principal.'], 400);
            }
            $pdo->prepare('INSERT INTO channel_post_comments (post_id, user_id, parent_id, body) VALUES (?, ?, ?, ?)')
                ->execute([$pid, $uid, $parentId, $body]);
            tcf_videos_api_json(['ok' => true, 'message' => 'Réponse publiée.']);
        }

        case 'channel_post_poll_vote': {
            if ($method !== 'POST') {
                tcf_videos_api_json(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
            }
            $uid = tcf_videos_api_require_login();
            $pid = (int) ($_POST['post_id'] ?? 0);
            $optIdx = (int) ($_POST['option_index'] ?? -1);
            if ($pid <= 0 || $optIdx < 0) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Paramètres invalides.'], 400);
            }
            $post = tcf_videos_api_channel_post_public($pdo, $pid);
            if ($post === null) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Publication introuvable.'], 404);
            }
            if (($post['post_type'] ?? '') !== 'poll') {
                tcf_videos_api_json(['ok' => false, 'message' => 'Ce n’est pas un sondage.'], 400);
            }
            $opts = json_decode((string) ($post['poll_options_json'] ?? ''), true);
            if (!is_array($opts) || $optIdx >= count($opts)) {
                tcf_videos_api_json(['ok' => false, 'message' => 'Option invalide.'], 400);
            }
            $n = count($opts);
            $stEx = $pdo->prepare('SELECT option_index, voted_at FROM channel_post_poll_votes WHERE post_id = ? AND user_id = ?');
            $stEx->execute([$pid, $uid]);
            $existing = $stEx->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $ts = strtotime((string) ($existing['voted_at'] ?? ''));
                if ($ts !== false && (time() - $ts) >= 60) {
                    $counts = array_fill(0, $n, 0);
                    $st = $pdo->prepare('SELECT option_index, COUNT(*) AS c FROM channel_post_poll_votes WHERE post_id = ? GROUP BY option_index');
                    $st->execute([$pid]);
                    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
                        $ix = (int) $r['option_index'];
                        if ($ix >= 0 && $ix < $n) {
                            $counts[$ix] = (int) $r['c'];
                        }
                    }
                    tcf_videos_api_json([
                        'ok' => true,
                        'locked' => true,
                        'counts' => $counts,
                        'user_vote' => (int) $existing['option_index'],
                    ]);
                }
                $pdo->prepare('UPDATE channel_post_poll_votes SET option_index = ? WHERE post_id = ? AND user_id = ?')->execute([$optIdx, $pid, $uid]);
            } else {
                $pdo->prepare('INSERT INTO channel_post_poll_votes (post_id, user_id, option_index) VALUES (?, ?, ?)')->execute([$pid, $uid, $optIdx]);
            }
            $counts = array_fill(0, $n, 0);
            $st = $pdo->prepare('SELECT option_index, COUNT(*) AS c FROM channel_post_poll_votes WHERE post_id = ? GROUP BY option_index');
            $st->execute([$pid]);
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $ix = (int) $r['option_index'];
                if ($ix >= 0 && $ix < $n) {
                    $counts[$ix] = (int) $r['c'];
                }
            }
            tcf_videos_api_json(['ok' => true, 'counts' => $counts, 'user_vote' => $optIdx, 'locked' => false]);
        }

        default:
            tcf_videos_api_json(['ok' => false, 'message' => 'Action inconnue.'], 400);
    }
} catch (Throwable $e) {
    error_log('videos_api: ' . $e->getMessage());
    $em = $e->getMessage();
    if (strpos($em, 'video_likes') !== false || strpos($em, 'video_comments') !== false || strpos($em, 'channel_subscribers') !== false
        || strpos($em, 'channel_post_likes') !== false || strpos($em, 'channel_post_comments') !== false || strpos($em, 'channel_post_poll_votes') !== false) {
        tcf_videos_api_json(['ok' => false, 'message' => 'Tables manquantes. Importez database/tcf.sql.'], 503);
    }
    tcf_videos_api_json(['ok' => false, 'message' => 'Erreur serveur.'], 500);
}
