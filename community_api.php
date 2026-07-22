<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/subscription_access.php';
require_once __DIR__ . '/includes/community_posts_helper.php';
require_once __DIR__ . '/includes/tcf_notifications_helper.php';
require_once __DIR__ . '/includes/admin_roles.php';
if (is_file(__DIR__ . '/includes/visitor_id.php')) {
    require_once __DIR__ . '/includes/visitor_id.php';
}

header('Content-Type: application/json; charset=utf-8');

function cp_json(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function cp_user(PDO $pdo): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $st = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $st->execute([(int) $_SESSION['user_id']]);
    $u = $st->fetch(PDO::FETCH_ASSOC);

    return $u ?: null;
}

function cp_is_admin(): bool
{
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'super_admin'], true);
}

$action = trim((string) ($_POST['action'] ?? $_GET['action'] ?? ''));
tcf_community_posts_ensure_tables($pdo);
$user = cp_user($pdo);

try {
    switch ($action) {
        case 'list': {
            $rows = tcf_community_posts_list_for_viewer($pdo, $user, 60);
            cp_json([
                'success' => true,
                'data' => $rows,
                'logged_in' => $user !== null,
                'can_like' => $user !== null,
            ]);
        }

        case 'like_toggle': {
            if (!$user) {
                cp_json(['success' => false, 'need_login' => true, 'message' => 'Connexion requise pour aimer une annonce.'], 401);
            }
            $postId = (int) ($_POST['post_id'] ?? 0);
            if ($postId <= 0) {
                cp_json(['success' => false, 'message' => 'Annonce invalide.'], 422);
            }
            $st = $pdo->prepare('SELECT id, visibility, is_published FROM community_posts WHERE id = ? LIMIT 1');
            $st->execute([$postId]);
            $post = $st->fetch(PDO::FETCH_ASSOC);
            if (!$post || !(int) $post['is_published']) {
                cp_json(['success' => false, 'message' => 'Annonce introuvable.'], 404);
            }
            if (!tcf_community_user_can_view_post($user, (string) $post['visibility'])) {
                cp_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }
            $uid = (int) $user['id'];
            $chk = $pdo->prepare('SELECT 1 FROM community_post_likes WHERE post_id = ? AND user_id = ?');
            $chk->execute([$postId, $uid]);
            if ($chk->fetchColumn()) {
                $pdo->prepare('DELETE FROM community_post_likes WHERE post_id = ? AND user_id = ?')->execute([$postId, $uid]);
                $liked = false;
            } else {
                $pdo->prepare('INSERT INTO community_post_likes (post_id, user_id) VALUES (?, ?)')->execute([$postId, $uid]);
                $liked = true;
            }
            $cSt = $pdo->prepare('SELECT COUNT(*) FROM community_post_likes WHERE post_id = ?');
            $cSt->execute([$postId]);
            $cnt = (int) $cSt->fetchColumn();
            cp_json(['success' => true, 'liked' => $liked, 'likes_count' => $cnt]);
        }

        case 'admin_list': {
            if (!cp_is_admin()) {
                cp_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }
            $st = $pdo->query(
                "SELECT p.*,
                    (SELECT COUNT(*) FROM community_post_likes l WHERE l.post_id = p.id) AS likes_count,
                    (SELECT COUNT(*) FROM community_post_views v WHERE v.post_id = p.id) AS views_count
                 FROM community_posts p
                 ORDER BY p.created_at DESC
                 LIMIT 200"
            );
            $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as &$r) {
                $img = trim((string) ($r['image_url'] ?? ''));
                $r['image_href'] = $img !== '' ? site_href(ltrim($img, '/')) : '';
                $r['visibility_label'] = tcf_community_visibility_label((string) ($r['visibility'] ?? 'registered'));
                $r['likes_count'] = (int) ($r['likes_count'] ?? 0);
                $r['views_count'] = (int) ($r['views_count'] ?? 0);
            }
            unset($r);
            cp_json(['success' => true, 'data' => $rows]);
        }

        case 'admin_save': {
            if (!cp_is_admin()) {
                cp_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }
            $id = (int) ($_POST['id'] ?? 0);
            // Conserver les retours à la ligne internes ; trim uniquement aux extrémités
            $body = trim((string) ($_POST['body'] ?? ''));
            $linkUrl = tcf_community_normalize_link((string) ($_POST['link_url'] ?? ''));
            $rawLink = trim((string) ($_POST['link_url'] ?? ''));
            if ($rawLink !== '' && $linkUrl === null) {
                cp_json(['success' => false, 'message' => 'Lien invalide. Utilisez une URL http(s).'], 422);
            }
            $visibility = strtolower(trim((string) ($_POST['visibility'] ?? 'registered')));
            if (!in_array($visibility, ['visitors', 'registered', 'premium'], true)) {
                $visibility = 'registered';
            }
            $isPublished = isset($_POST['is_published']) && $_POST['is_published'] === '0' ? 0 : 1;
            if ($body === '') {
                cp_json(['success' => false, 'message' => 'Le texte de l’annonce est obligatoire.'], 422);
            }

            $imageUrl = null;
            $clearImage = !empty($_POST['clear_image']);
            if (isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $dir = __DIR__ . '/uploads/community_posts';
                if (!is_dir($dir)) {
                    @mkdir($dir, 0755, true);
                }
                $ext = strtolower(pathinfo((string) $_FILES['image']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                    cp_json(['success' => false, 'message' => 'Image : JPG, PNG, WebP ou GIF uniquement.'], 422);
                }
                $name = 'cp_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $dest = $dir . DIRECTORY_SEPARATOR . $name;
                if (!move_uploaded_file((string) $_FILES['image']['tmp_name'], $dest)) {
                    cp_json(['success' => false, 'message' => 'Échec upload image.'], 500);
                }
                $imageUrl = 'uploads/community_posts/' . $name;
            }

            $uid = (int) ($_SESSION['user_id'] ?? 0);
            if ($id > 0) {
                $st = $pdo->prepare('SELECT image_url, is_published FROM community_posts WHERE id = ?');
                $st->execute([$id]);
                $old = $st->fetch(PDO::FETCH_ASSOC);
                if (!$old) {
                    cp_json(['success' => false, 'message' => 'Annonce introuvable.'], 404);
                }
                $finalImg = (string) ($old['image_url'] ?? '');
                if ($clearImage) {
                    $finalImg = '';
                }
                if ($imageUrl !== null) {
                    $finalImg = $imageUrl;
                }
                $wasPublished = (int) ($old['is_published'] ?? 0) === 1;
                $pdo->prepare(
                    'UPDATE community_posts SET body=?, image_url=?, link_url=?, visibility=?, is_published=?, updated_at=NOW() WHERE id=?'
                )->execute([$body, $finalImg !== '' ? $finalImg : null, $linkUrl, $visibility, $isPublished, $id]);
                if ($isPublished && !$wasPublished) {
                    $excerpt = function_exists('mb_substr') ? mb_substr($body, 0, 140) : substr($body, 0, 140);
                    if ((function_exists('mb_strlen') ? mb_strlen($body) : strlen($body)) > 140) {
                        $excerpt .= '…';
                    }
                    try {
                        tcf_notify_users_registered_before(
                            $pdo,
                            'message',
                            'Nouvelle annonce communautaire',
                            $excerpt,
                            site_href('posts.php?id=' . $id)
                        );
                    } catch (Throwable $e) {
                        error_log('community post notify: ' . $e->getMessage());
                    }
                }
                cp_json(['success' => true, 'message' => 'Annonce mise à jour.', 'id' => $id]);
            }

            $pdo->prepare(
                'INSERT INTO community_posts (body, image_url, link_url, visibility, is_published, created_by) VALUES (?,?,?,?,?,?)'
            )->execute([$body, $imageUrl, $linkUrl, $visibility, $isPublished, $uid > 0 ? $uid : null]);
            $newId = (int) $pdo->lastInsertId();

            if ($isPublished && $newId > 0) {
                $excerpt = function_exists('mb_substr') ? mb_substr($body, 0, 140) : substr($body, 0, 140);
                if ((function_exists('mb_strlen') ? mb_strlen($body) : strlen($body)) > 140) {
                    $excerpt .= '…';
                }
                try {
                    tcf_notify_users_registered_before(
                        $pdo,
                        'message',
                        'Nouvelle annonce communautaire',
                        $excerpt,
                        site_href('posts.php?id=' . $newId)
                    );
                } catch (Throwable $e) {
                    error_log('community post notify: ' . $e->getMessage());
                }
            }
            cp_json(['success' => true, 'message' => 'Annonce publiée.', 'id' => $newId]);
        }

        case 'admin_delete': {
            if (!cp_is_admin()) {
                cp_json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }
            if (!tcf_is_super_admin()) {
                cp_json(['success' => false, 'message' => 'Seul le super administrateur peut supprimer une annonce.'], 403);
            }
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                cp_json(['success' => false, 'message' => 'ID invalide.'], 422);
            }
            $bodyPrev = '';
            try {
                $stB = $pdo->prepare('SELECT body FROM community_posts WHERE id = ?');
                $stB->execute([$id]);
                $bodyPrev = (string) ($stB->fetchColumn() ?: '');
            } catch (Throwable $e) {
            }
            $pdo->prepare('DELETE FROM community_post_likes WHERE post_id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM community_post_views WHERE post_id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM community_posts WHERE id = ?')->execute([$id]);
            tcf_delete_notifications_matching($pdo, 'posts.php?id=' . $id);
            if ($bodyPrev !== '') {
                $excerpt = function_exists('mb_substr') ? mb_substr($bodyPrev, 0, 140) : substr($bodyPrev, 0, 140);
                if ((function_exists('mb_strlen') ? mb_strlen($bodyPrev) : strlen($bodyPrev)) > 140) {
                    $excerpt .= '…';
                }
                tcf_delete_notifications_by_type_payload(
                    $pdo,
                    'message',
                    'Nouvelle annonce communautaire',
                    $excerpt
                );
            }
            cp_json(['success' => true, 'message' => 'Annonce supprimée.']);
        }

        default:
            cp_json(['success' => false, 'message' => 'Action inconnue.'], 400);
    }
} catch (Throwable $e) {
    cp_json(['success' => false, 'message' => $e->getMessage()], 500);
}
