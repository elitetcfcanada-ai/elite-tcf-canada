<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

header('Content-Type: application/json; charset=utf-8');

function chat_json(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function chat_staff_roles(): array
{
    return ['admin', 'super_admin'];
}

function chat_is_staff_role(string $role): bool
{
    return in_array($role, chat_staff_roles(), true);
}

function chat_is_admin(): bool
{
    return chat_is_staff_role((string) ($_SESSION['role'] ?? ''));
}

function chat_require_login(): int
{
    if (empty($_SESSION['user_id'])) {
        chat_json(['ok' => false, 'message' => 'Connexion requise.'], 401);
    }
    return (int) $_SESSION['user_id'];
}

function chat_ensure_schema(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_chat_threads (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            thread_type ENUM('direct','group') NOT NULL DEFAULT 'direct',
            title VARCHAR(180) DEFAULT NULL,
            avatar VARCHAR(255) DEFAULT NULL,
            admins_only_post TINYINT(1) NOT NULL DEFAULT 0,
            created_by INT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_tcf_chat_threads_type (thread_type),
            KEY idx_tcf_chat_threads_updated (updated_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );

    try {
        $pdo->exec('ALTER TABLE tcf_chat_threads ADD COLUMN avatar VARCHAR(255) NULL DEFAULT NULL AFTER title');
    } catch (Throwable $e) {
    }
    try {
        $pdo->exec('ALTER TABLE tcf_chat_threads ADD COLUMN admins_only_post TINYINT(1) NOT NULL DEFAULT 0 AFTER avatar');
    } catch (Throwable $e) {
    }

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_chat_thread_members (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            thread_id INT UNSIGNED NOT NULL,
            user_id INT NOT NULL,
            joined_by INT DEFAULT NULL,
            joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_tcf_chat_member (thread_id, user_id),
            KEY idx_tcf_chat_member_user (user_id),
            CONSTRAINT fk_tcf_chat_member_thread FOREIGN KEY (thread_id) REFERENCES tcf_chat_threads(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_chat_messages (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            thread_id INT UNSIGNED NOT NULL,
            sender_id INT NOT NULL,
            message TEXT NOT NULL,
            edited_at DATETIME DEFAULT NULL,
            deleted_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_tcf_chat_msg_thread (thread_id, created_at),
            CONSTRAINT fk_tcf_chat_msg_thread FOREIGN KEY (thread_id) REFERENCES tcf_chat_threads(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );

    try {
        $pdo->exec('ALTER TABLE tcf_chat_messages ADD COLUMN edited_at DATETIME NULL DEFAULT NULL AFTER message');
    } catch (Throwable $e) {
    }
    try {
        $pdo->exec('ALTER TABLE tcf_chat_messages ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL AFTER edited_at');
    } catch (Throwable $e) {
    }

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_chat_presence_settings (
            user_id INT NOT NULL PRIMARY KEY,
            is_visible TINYINT(1) NOT NULL DEFAULT 1,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );
}

function chat_touch_activity(PDO $pdo, int $uid): void
{
    try {
        $pdo->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?")->execute([$uid]);
    } catch (Throwable $e) {
    }
}

function chat_user_can_access_thread(PDO $pdo, int $threadId, int $uid): bool
{
    $st = $pdo->prepare("SELECT 1 FROM tcf_chat_thread_members WHERE thread_id = ? AND user_id = ? LIMIT 1");
    $st->execute([$threadId, $uid]);
    return (bool) $st->fetchColumn();
}

function chat_profile_href(int $uid): string
{
    return site_href('index.php') . '#profile-' . $uid;
}

function chat_avatar_href(?string $avatar): string
{
    $av = $avatar ? basename((string) $avatar) : '';
    return $av !== '' && function_exists('tcf_avatar_public_url') ? (string) tcf_avatar_public_url($av) : '';
}

function chat_group_storage_dir(): string
{
    /* chat_api.php est à la racine du site (ex. tcf3/) — pas dirname(__DIR__) qui remonte au-dessus du projet */
    return __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'chat_groups';
}

function chat_group_public_url(?string $filename): string
{
    if ($filename === null || $filename === '') {
        return '';
    }
    $safe = basename((string) $filename);
    if ($safe === '' || preg_match('/[^a-zA-Z0-9._-]/', $safe)) {
        return '';
    }
    $full = chat_group_storage_dir() . DIRECTORY_SEPARATOR . $safe;
    $url = site_href('uploads/chat_groups/' . rawurlencode($safe));
    if (is_file($full)) {
        $url .= '?t=' . (string) filemtime($full);
    }

    return $url;
}

/**
 * Enregistre une image data URL pour un groupe ; supprime l’ancienne entrée disque si besoin.
 */
function chat_save_group_avatar(PDO $pdo, int $threadId, string $dataUrl): ?string
{
    $dataUrl = trim($dataUrl);
    if ($dataUrl === '' || !preg_match('#^data:image/(jpeg|jpg|png|webp);base64,(.+)$#i', $dataUrl, $m)) {
        return null;
    }
    $ext = strtolower($m[1]) === 'jpeg' ? 'jpg' : strtolower($m[1]);
    if (!in_array($ext, ['jpg', 'png', 'webp'], true)) {
        return null;
    }
    $raw = base64_decode($m[2], true);
    if ($raw === false || strlen($raw) > 2_500_000) {
        return null;
    }
    $dir = chat_group_storage_dir();
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
        return null;
    }

    $st = $pdo->prepare('SELECT avatar FROM tcf_chat_threads WHERE id = ? LIMIT 1');
    $st->execute([$threadId]);
    $old = $st->fetchColumn();
    if ($old !== false && $old !== null && $old !== '') {
        $oldPath = $dir . DIRECTORY_SEPARATOR . basename((string) $old);
        if (is_file($oldPath)) {
            @unlink($oldPath);
        }
    }

    $name = 'group_' . $threadId . '_' . time() . '.' . $ext;
    $path = $dir . DIRECTORY_SEPARATOR . $name;
    if (file_put_contents($path, $raw) === false) {
        return null;
    }

    return $name;
}

function chat_thread_group_can_user_send(PDO $pdo, int $threadId, int $userId): bool
{
    $st = $pdo->prepare("SELECT thread_type, admins_only_post FROM tcf_chat_threads WHERE id = ? LIMIT 1");
    $st->execute([$threadId]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row || ($row['thread_type'] ?? '') !== 'group') {
        return true;
    }
    if ((int) ($row['admins_only_post'] ?? 0) !== 1) {
        return true;
    }
    $r = (string) ($_SESSION['role'] ?? '');

    return $r === 'admin' || $r === 'super_admin';
}

function chat_effective_online_sql(): string
{
    return "CASE WHEN u.role IN ('admin','super_admin')
            THEN CASE WHEN COALESCE(ps.is_visible,1)=1 THEN 1 ELSE 0 END
            ELSE 1 END";
}

/** Messagerie réservée aux comptes admin / super_admin uniquement. */
function chat_thread_is_internal_only(PDO $pdo, int $threadId): bool
{
    if ($threadId <= 0) {
        return false;
    }
    $st = $pdo->prepare('SELECT thread_type FROM tcf_chat_threads WHERE id = ? LIMIT 1');
    $st->execute([$threadId]);
    $type = (string) ($st->fetchColumn() ?: '');
    if ($type === 'direct') {
        $st2 = $pdo->prepare(
            'SELECT u.role FROM tcf_chat_thread_members tm
             INNER JOIN users u ON u.id = tm.user_id
             WHERE tm.thread_id = ?'
        );
        $st2->execute([$threadId]);
        $roles = $st2->fetchAll(PDO::FETCH_COLUMN) ?: [];
        if (count($roles) !== 2) {
            return false;
        }
        foreach ($roles as $r) {
            if (!chat_is_staff_role((string) $r)) {
                return false;
            }
        }

        return true;
    }
    if ($type === 'group') {
        $st3 = $pdo->prepare(
            "SELECT COUNT(*) FROM tcf_chat_thread_members tm
             INNER JOIN users u ON u.id = tm.user_id
             WHERE tm.thread_id = ? AND u.role NOT IN ('admin','super_admin') AND u.status = 'active'"
        );
        $st3->execute([$threadId]);

        return (int) $st3->fetchColumn() === 0;
    }

    return false;
}

try {
    chat_ensure_schema($pdo);
    $uid = chat_require_login();
    if (!chat_is_admin()) {
        chat_json(['ok' => false, 'message' => 'Chat réservé aux administrateurs.'], 403);
    }
    chat_touch_activity($pdo, $uid);

    $body = [];
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $raw = (string) file_get_contents('php://input');
        $j = json_decode($raw, true);
        $body = is_array($j) ? $j : $_POST;
    } else {
        $body = $_GET;
    }
    $action = (string) ($body['action'] ?? '');

    if ($action === 'bootstrap') {
        $st = $pdo->prepare(
            "SELECT u.id, u.name, u.email, u.role, u.avatar, u.last_activity,
                    COALESCE(ps.is_visible, 1) AS is_visible,
                    " . chat_effective_online_sql() . " AS effective_visible
             FROM users u
             LEFT JOIN tcf_chat_presence_settings ps ON ps.user_id = u.id
             WHERE u.id = ? LIMIT 1"
        );
        $st->execute([$uid]);
        $me = $st->fetch(PDO::FETCH_ASSOC) ?: null;
        if ($me) {
            $me['avatar_url'] = chat_avatar_href($me['avatar'] ?? null);
        }

        $onlineCount = 0;
        try {
            $stO = $pdo->query(
                "SELECT COUNT(*) FROM users u
                 LEFT JOIN tcf_chat_presence_settings ps ON ps.user_id = u.id
                 WHERE u.last_activity IS NOT NULL
                   AND TIMESTAMPDIFF(SECOND, u.last_activity, NOW()) <= 300
                   AND (" . chat_effective_online_sql() . ") = 1"
            );
            $onlineCount = (int) $stO->fetchColumn();
        } catch (Throwable $e) {
            $onlineCount = 0;
        }

        $hasGroup = false;
        try {
            $stG = $pdo->prepare(
                "SELECT 1
                 FROM tcf_chat_thread_members tm
                 INNER JOIN tcf_chat_threads t ON t.id = tm.thread_id
                 WHERE tm.user_id = ? AND t.thread_type = 'group'
                 LIMIT 1"
            );
            $stG->execute([$uid]);
            $hasGroup = (bool) $stG->fetchColumn();
        } catch (Throwable $e) {
            $hasGroup = false;
        }

        chat_json([
            'ok' => true,
            'me' => $me,
            'is_admin' => chat_is_admin(),
            'online_count' => (chat_is_admin() || $hasGroup) ? $onlineCount : null,
            'can_view_online_count' => chat_is_admin() || $hasGroup,
            'assistance_user' => null,
            'profile_href' => chat_profile_href($uid),
        ]);
    }

    if ($action === 'list_threads') {
        $sql = "
            SELECT t.id, t.thread_type, t.title, t.avatar, t.admins_only_post, t.updated_at,
                   MAX(m.created_at) AS last_message_at,
                   SUBSTRING_INDEX(
                       MAX(CONCAT(
                           DATE_FORMAT(m.created_at, '%Y-%m-%d %H:%i:%s'), '||',
                           CASE WHEN m.deleted_at IS NOT NULL THEN '[Message supprimé]' ELSE m.message END
                       )),
                       '||', -1
                   ) AS last_message
            FROM tcf_chat_threads t
            INNER JOIN tcf_chat_thread_members tm ON tm.thread_id = t.id AND tm.user_id = ?
            LEFT JOIN tcf_chat_messages m ON m.thread_id = t.id
            GROUP BY t.id, t.thread_type, t.title, t.avatar, t.admins_only_post, t.updated_at
            ORDER BY COALESCE(MAX(m.created_at), t.updated_at) DESC";
        $st = $pdo->prepare($sql);
        $st->execute([$uid]);
        $threads = $st->fetchAll(PDO::FETCH_ASSOC);

        foreach ($threads as &$th) {
            $thId = (int) $th['id'];
            if (($th['thread_type'] ?? '') === 'direct') {
                $st2 = $pdo->prepare(
                    "SELECT u.id, u.name, u.role, u.avatar, u.last_activity, COALESCE(ps.is_visible,1) AS is_visible
                     FROM tcf_chat_thread_members tm
                     INNER JOIN users u ON u.id = tm.user_id
                     LEFT JOIN tcf_chat_presence_settings ps ON ps.user_id = u.id
                     WHERE tm.thread_id = ? AND tm.user_id <> ?
                     LIMIT 1"
                );
                $st2->execute([$thId, $uid]);
                $peer = $st2->fetch(PDO::FETCH_ASSOC) ?: null;
                if ($peer) {
                    $visible = (in_array($peer['role'], ['admin', 'super_admin'], true) ? ((int) ($peer['is_visible'] ?? 1) === 1) : true);
                    $online = !empty($peer['last_activity']) && (time() - (int) strtotime((string) $peer['last_activity']) <= 300) && $visible;
                    $th['title'] = $peer['name'] ?? 'Conversation';
                    $th['peer'] = [
                        'id' => (int) $peer['id'],
                        'name' => (string) ($peer['name'] ?? ''),
                        'role' => (string) ($peer['role'] ?? ''),
                        'avatar_url' => chat_avatar_href($peer['avatar'] ?? ''),
                        'online' => $online,
                        'profile_href' => chat_profile_href((int) $peer['id']),
                    ];
                }
            } else {
                $st3 = $pdo->prepare("SELECT COUNT(*) FROM tcf_chat_thread_members WHERE thread_id = ?");
                $st3->execute([$thId]);
                $th['member_count'] = (int) $st3->fetchColumn();
                try {
                    $stOn = $pdo->prepare(
                        "SELECT COUNT(*)
                         FROM tcf_chat_thread_members tm
                         INNER JOIN users u ON u.id = tm.user_id
                         LEFT JOIN tcf_chat_presence_settings ps ON ps.user_id = u.id
                         WHERE tm.thread_id = ?
                           AND u.last_activity IS NOT NULL
                           AND TIMESTAMPDIFF(SECOND, u.last_activity, NOW()) <= 300
                           AND (" . chat_effective_online_sql() . ") = 1"
                    );
                    $stOn->execute([$thId]);
                    $th['online_count'] = (int) $stOn->fetchColumn();
                } catch (Throwable $e) {
                    $th['online_count'] = 0;
                }
            }
            $th['admins_only_post'] = (int) ($th['admins_only_post'] ?? 0);
            $th['group_avatar_url'] = (($th['thread_type'] ?? '') === 'group')
                ? chat_group_public_url($th['avatar'] ?? null)
                : '';
        }
        unset($th);

        $threads = array_values(array_filter($threads, function ($th) use ($pdo) {
            $tid = (int) ($th['id'] ?? 0);

            return $tid > 0 && chat_thread_is_internal_only($pdo, $tid);
        }));

        chat_json(['ok' => true, 'threads' => $threads]);
    }

    if ($action === 'list_users') {
        if (!chat_is_admin()) {
            chat_json(['ok' => false, 'message' => 'Action réservée aux admins.'], 403);
        }
        $st = $pdo->prepare(
            "SELECT u.id, u.name, u.email, u.role, u.last_activity, COALESCE(ps.is_visible,1) AS is_visible
             FROM users u
             LEFT JOIN tcf_chat_presence_settings ps ON ps.user_id = u.id
             WHERE u.status = 'active' AND u.id <> ?
               AND u.role IN ('admin', 'super_admin')
             ORDER BY u.role DESC, u.name ASC"
        );
        $st->execute([$uid]);
        $users = $st->fetchAll(PDO::FETCH_ASSOC);
        foreach ($users as &$u) {
            $role = (string) ($u['role'] ?? '');
            $u['role_label'] = $role === 'super_admin' ? 'Super administrateur' : 'Administrateur';
            $visible = (chat_is_staff_role($role) ? ((int) ($u['is_visible'] ?? 1) === 1) : true);
            $u['online'] = !empty($u['last_activity']) && (time() - (int) strtotime((string) $u['last_activity']) <= 300) && $visible;
            $u['profile_href'] = chat_profile_href((int) $u['id']);
        }
        unset($u);
        chat_json(['ok' => true, 'users' => $users]);
    }

    if ($action === 'ensure_direct') {
        $targetId = (int) ($body['target_user_id'] ?? 0);
        if ($targetId <= 0 || $targetId === $uid) {
            chat_json(['ok' => false, 'message' => 'Utilisateur cible invalide.'], 400);
        }
        $stT = $pdo->prepare('SELECT role FROM users WHERE id = ? LIMIT 1');
        $stT->execute([$targetId]);
        $targetRole = (string) ($stT->fetchColumn() ?: '');
        if (!chat_is_staff_role($targetRole)) {
            chat_json(['ok' => false, 'message' => 'Messagerie réservée entre membres du staff.'], 403);
        }

        $st = $pdo->prepare(
            "SELECT t.id
             FROM tcf_chat_threads t
             INNER JOIN tcf_chat_thread_members m1 ON m1.thread_id = t.id AND m1.user_id = ?
             INNER JOIN tcf_chat_thread_members m2 ON m2.thread_id = t.id AND m2.user_id = ?
             WHERE t.thread_type = 'direct'
             LIMIT 1"
        );
        $st->execute([$uid, $targetId]);
        $threadId = (int) ($st->fetchColumn() ?: 0);

        if ($threadId <= 0) {
            $pdo->beginTransaction();
            try {
                $ins = $pdo->prepare("INSERT INTO tcf_chat_threads (thread_type, title, created_by) VALUES ('direct', NULL, ?)");
                $ins->execute([$uid]);
                $threadId = (int) $pdo->lastInsertId();
                $insM = $pdo->prepare("INSERT INTO tcf_chat_thread_members (thread_id, user_id, joined_by) VALUES (?, ?, ?)");
                $insM->execute([$threadId, $uid, $uid]);
                $insM->execute([$threadId, $targetId, $uid]);
                $pdo->commit();
            } catch (Throwable $e) {
                $pdo->rollBack();
                chat_json(['ok' => false, 'message' => 'Impossible de créer la conversation directe.'], 500);
            }
        }

        chat_json(['ok' => true, 'thread_id' => $threadId]);
    }

    if ($action === 'create_group') {
        if (!chat_is_admin()) {
            chat_json(['ok' => false, 'message' => 'Action réservée aux admins.'], 403);
        }
        $title = trim((string) ($body['title'] ?? ''));
        $clean = [];

        $emails = $body['member_emails'] ?? null;
        if (is_array($emails) && $emails !== []) {
            $stE = $pdo->prepare('SELECT id FROM users WHERE LOWER(TRIM(email)) = ? LIMIT 1');
            foreach ($emails as $em) {
                $e = strtolower(trim((string) $em));
                if ($e === '' || !filter_var($e, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }
                $stE->execute([$e]);
                $mid = (int) ($stE->fetchColumn() ?: 0);
                if ($mid > 0 && $mid !== $uid) {
                    $clean[$mid] = true;
                }
            }
        }

        $members = $body['member_ids'] ?? [];
        if (is_array($members)) {
            foreach ($members as $m) {
                $i = (int) $m;
                if ($i > 0 && $i !== $uid) {
                    $clean[$i] = true;
                }
            }
        }

        $memberIds = array_keys($clean);
        if ($title === '' || count($memberIds) < 1) {
            chat_json(['ok' => false, 'message' => 'Nom du groupe et au moins un membre valide requis (e-mail reconnu sur la plateforme).'], 400);
        }
        if ($memberIds !== []) {
            $placeholders = implode(',', array_fill(0, count($memberIds), '?'));
            $stRoles = $pdo->prepare(
                "SELECT id, role FROM users WHERE id IN ($placeholders)"
            );
            $stRoles->execute($memberIds);
            $staffOnly = [];
            while ($rr = $stRoles->fetch(PDO::FETCH_ASSOC)) {
                $rid = (int) ($rr['id'] ?? 0);
                $rrole = (string) ($rr['role'] ?? '');
                if ($rid > 0 && chat_is_staff_role($rrole)) {
                    $staffOnly[$rid] = true;
                }
            }
            $memberIds = array_keys($staffOnly);
        }
        if (count($memberIds) < 1) {
            chat_json(['ok' => false, 'message' => 'Ajoutez au moins un administrateur ou super-administrateur au groupe.'], 400);
        }
        $adminsOnly = !empty($body['admins_only_post']) ? 1 : 0;
        $pdo->beginTransaction();
        $threadId = 0;
        try {
            $ins = $pdo->prepare("INSERT INTO tcf_chat_threads (thread_type, title, created_by, admins_only_post) VALUES ('group', ?, ?, ?)");
            $ins->execute([$title, $uid, $adminsOnly]);
            $threadId = (int) $pdo->lastInsertId();
            $insM = $pdo->prepare("INSERT INTO tcf_chat_thread_members (thread_id, user_id, joined_by) VALUES (?, ?, ?)");
            $insM->execute([$threadId, $uid, $uid]);
            foreach ($memberIds as $mid) {
                $insM->execute([$threadId, $mid, $uid]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            chat_json(['ok' => false, 'message' => 'Création du groupe impossible.'], 500);
        }

        $dataUrl = trim((string) ($body['avatar_data_url'] ?? ''));
        if ($threadId > 0 && $dataUrl !== '') {
            $fn = chat_save_group_avatar($pdo, $threadId, $dataUrl);
            if ($fn !== null) {
                try {
                    $pdo->prepare('UPDATE tcf_chat_threads SET avatar = ? WHERE id = ?')->execute([$fn, $threadId]);
                } catch (Throwable $e) {
                }
            }
        }

        chat_json(['ok' => true, 'thread_id' => $threadId, 'message' => 'Groupe créé.']);
    }

    if ($action === 'update_group') {
        if (!chat_is_admin()) {
            chat_json(['ok' => false, 'message' => 'Action réservée aux administrateurs.'], 403);
        }
        $threadId = (int) ($body['thread_id'] ?? 0);
        if ($threadId <= 0 || !chat_user_can_access_thread($pdo, $threadId, $uid)) {
            chat_json(['ok' => false, 'message' => 'Groupe inaccessible.'], 403);
        }
        if (!chat_thread_is_internal_only($pdo, $threadId)) {
            chat_json(['ok' => false, 'message' => 'Groupe non autorisé.'], 403);
        }
        $st = $pdo->prepare("SELECT thread_type, avatar FROM tcf_chat_threads WHERE id = ? LIMIT 1");
        $st->execute([$threadId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row || ($row['thread_type'] ?? '') !== 'group') {
            chat_json(['ok' => false, 'message' => 'Ce fil n’est pas un groupe.'], 400);
        }

        $title = trim((string) ($body['title'] ?? ''));
        if ($title === '' || mb_strlen($title) > 180) {
            chat_json(['ok' => false, 'message' => 'Nom du groupe invalide (1–180 caractères).'], 400);
        }

        $sets = ['title = ?'];
        $params = [$title];
        if (array_key_exists('admins_only_post', $body)) {
            $sets[] = 'admins_only_post = ?';
            $params[] = !empty($body['admins_only_post']) ? 1 : 0;
        }

        $removeAvatar = !empty($body['remove_avatar']);
        $dataUrl = trim((string) ($body['avatar_data_url'] ?? ''));
        if ($removeAvatar) {
            $sets[] = 'avatar = NULL';
            $oldAv = $row['avatar'] ?? null;
            if ($oldAv !== null && $oldAv !== '') {
                $p = chat_group_storage_dir() . DIRECTORY_SEPARATOR . basename((string) $oldAv);
                if (is_file($p)) {
                    @unlink($p);
                }
            }
        } elseif ($dataUrl !== '') {
            $fn = chat_save_group_avatar($pdo, $threadId, $dataUrl);
            if ($fn === null) {
                chat_json(['ok' => false, 'message' => 'Image du groupe invalide (JPG, PNG ou WebP).'], 400);
            }
            $sets[] = 'avatar = ?';
            $params[] = $fn;
        }

        $params[] = $threadId;
        $sql = 'UPDATE tcf_chat_threads SET ' . implode(', ', $sets) . ' WHERE id = ?';
        $pdo->prepare($sql)->execute($params);
        chat_json(['ok' => true, 'message' => 'Groupe mis à jour.']);
    }

    if ($action === 'get_messages') {
        $threadId = (int) ($body['thread_id'] ?? 0);
        if ($threadId <= 0 || !chat_user_can_access_thread($pdo, $threadId, $uid)) {
            chat_json(['ok' => false, 'message' => 'Conversation inaccessible.'], 403);
        }
        if (!chat_thread_is_internal_only($pdo, $threadId)) {
            chat_json(['ok' => false, 'message' => 'Conversation non autorisée.'], 403);
        }
        $stT = $pdo->prepare("SELECT thread_type, title, avatar, admins_only_post FROM tcf_chat_threads WHERE id = ? LIMIT 1");
        $stT->execute([$threadId]);
        $trow = $stT->fetch(PDO::FETCH_ASSOC) ?: [];
        $adminsOnly = (int) ($trow['admins_only_post'] ?? 0);
        $isGroup = ($trow['thread_type'] ?? '') === 'group';
        $canSend = !$isGroup || $adminsOnly !== 1 || chat_is_admin();

        $limit = (int) ($body['limit'] ?? 40);
        $limit = max(1, min(80, $limit));
        $beforeId = (int) ($body['before_id'] ?? 0);
        $afterId = (int) ($body['after_id'] ?? 0);

        $threadPayload = [
            'thread_type' => (string) ($trow['thread_type'] ?? ''),
            'title' => (string) ($trow['title'] ?? ''),
            'group_avatar_url' => chat_group_public_url($trow['avatar'] ?? null),
            'admins_only_post' => $adminsOnly,
            'can_send' => $canSend,
        ];

        $baseSql =
            "SELECT m.id, m.thread_id, m.sender_id, m.message, m.edited_at, m.deleted_at, m.created_at,
                    u.name AS sender_name, u.role AS sender_role, u.avatar AS sender_avatar
             FROM tcf_chat_messages m
             INNER JOIN users u ON u.id = m.sender_id
             WHERE m.thread_id = ? ";

        if ($afterId > 0) {
            $st = $pdo->prepare(
                $baseSql . ' AND m.id > ? ORDER BY m.id ASC LIMIT ' . $limit
            );
            $st->execute([$threadId, $afterId]);
            $messages = $st->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($beforeId > 0) {
            $st = $pdo->prepare(
                $baseSql . ' AND m.id < ? ORDER BY m.id DESC LIMIT ' . $limit
            );
            $st->execute([$threadId, $beforeId]);
            $messages = $st->fetchAll(PDO::FETCH_ASSOC);
            $messages = array_reverse($messages);
        } else {
            $st = $pdo->prepare(
                $baseSql . ' ORDER BY m.id DESC LIMIT ' . $limit
            );
            $st->execute([$threadId]);
            $messages = $st->fetchAll(PDO::FETCH_ASSOC);
            $messages = array_reverse($messages);
        }

        foreach ($messages as &$m) {
            $m['mine'] = ((int) $m['sender_id'] === $uid);
            $m['profile_href'] = chat_profile_href((int) $m['sender_id']);
            $m['sender_avatar_url'] = chat_avatar_href($m['sender_avatar'] ?? '');
            $m['is_deleted'] = !empty($m['deleted_at']);
            $m['is_edited'] = !empty($m['edited_at']) && empty($m['deleted_at']);
            if (!empty($m['deleted_at'])) {
                $m['message'] = '';
            }
        }
        unset($m);

        $hasMoreOlder = false;
        $oldestId = null;
        $newestId = null;
        if ($messages !== []) {
            $ids = array_map(function (array $row): int {
                return (int) ($row['id'] ?? 0);
            }, $messages);
            $oldestId = min($ids);
            $newestId = max($ids);
            if ($afterId <= 0) {
                $chk = $pdo->prepare('SELECT 1 FROM tcf_chat_messages WHERE thread_id = ? AND id < ? LIMIT 1');
                $chk->execute([$threadId, $oldestId]);
                $hasMoreOlder = (bool) $chk->fetchColumn();
            }
        }

        chat_json([
            'ok' => true,
            'messages' => $messages,
            'thread' => $threadPayload,
            'has_more_older' => $hasMoreOlder,
            'oldest_id' => $oldestId,
            'newest_id' => $newestId,
            'mode' => $afterId > 0 ? 'newer' : ($beforeId > 0 ? 'older' : 'initial'),
        ]);
    }

    if ($action === 'send_message') {
        $threadId = (int) ($body['thread_id'] ?? 0);
        $message = trim((string) ($body['message'] ?? ''));
        if ($threadId <= 0 || $message === '') {
            chat_json(['ok' => false, 'message' => 'Message vide ou conversation invalide.'], 400);
        }
        if (!chat_user_can_access_thread($pdo, $threadId, $uid)) {
            chat_json(['ok' => false, 'message' => 'Conversation inaccessible.'], 403);
        }
        if (!chat_thread_is_internal_only($pdo, $threadId)) {
            chat_json(['ok' => false, 'message' => 'Conversation non autorisée.'], 403);
        }
        if (!chat_thread_group_can_user_send($pdo, $threadId, $uid)) {
            chat_json(['ok' => false, 'message' => 'Seuls les administrateurs peuvent écrire dans ce groupe.'], 403);
        }
        if (mb_strlen($message) > 4000) {
            chat_json(['ok' => false, 'message' => 'Message trop long (max 4000).'], 400);
        }
        $ins = $pdo->prepare("INSERT INTO tcf_chat_messages (thread_id, sender_id, message) VALUES (?, ?, ?)");
        $ins->execute([$threadId, $uid, $message]);
        $messageId = (int) $pdo->lastInsertId();
        $pdo->prepare("UPDATE tcf_chat_threads SET updated_at = NOW() WHERE id = ?")->execute([$threadId]);

        try {
            $stR = $pdo->prepare("SELECT user_id FROM tcf_chat_thread_members WHERE thread_id = ? AND user_id <> ?");
            $stR->execute([$threadId, $uid]);
            $recipients = $stR->fetchAll(PDO::FETCH_COLUMN) ?: [];
            $snip = mb_strlen($message) > 110 ? (mb_substr($message, 0, 110) . '…') : $message;
            $sNameSt = $pdo->prepare("SELECT name FROM users WHERE id = ? LIMIT 1");
            $sNameSt->execute([$uid]);
            $senderName = (string) ($sNameSt->fetchColumn() ?: 'Utilisateur');
            $insN = $pdo->prepare(
                "INSERT INTO notifications (user_id, type, title, content, deep_link) VALUES (?, 'message', ?, ?, ?)"
            );
            foreach ($recipients as $rid) {
                $insN->execute([(int) $rid, 'Nouveau message chat', $senderName . ' : ' . $snip, 'messages.php']);
            }
        } catch (Throwable $e) {
        }

        chat_json(['ok' => true, 'message' => 'Envoyé.', 'message_id' => $messageId]);
    }

    if ($action === 'edit_message') {
        $messageId = (int) ($body['message_id'] ?? 0);
        $newText = trim((string) ($body['message'] ?? ''));
        if ($messageId <= 0 || $newText === '') {
            chat_json(['ok' => false, 'message' => 'Message invalide.'], 400);
        }
        if (mb_strlen($newText) > 4000) {
            chat_json(['ok' => false, 'message' => 'Message trop long (max 4000).'], 400);
        }
        $st = $pdo->prepare(
            'SELECT m.id, m.thread_id, m.sender_id, m.deleted_at
             FROM tcf_chat_messages m WHERE m.id = ? LIMIT 1'
        );
        $st->execute([$messageId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row || !empty($row['deleted_at'])) {
            chat_json(['ok' => false, 'message' => 'Message introuvable.'], 404);
        }
        $threadId = (int) ($row['thread_id'] ?? 0);
        if (!chat_user_can_access_thread($pdo, $threadId, $uid) || !chat_thread_is_internal_only($pdo, $threadId)) {
            chat_json(['ok' => false, 'message' => 'Conversation inaccessible.'], 403);
        }
        if ((int) ($row['sender_id'] ?? 0) !== $uid) {
            chat_json(['ok' => false, 'message' => 'Vous ne pouvez modifier que vos propres messages.'], 403);
        }
        $pdo->prepare(
            'UPDATE tcf_chat_messages SET message = ?, edited_at = NOW() WHERE id = ?'
        )->execute([$newText, $messageId]);
        $pdo->prepare('UPDATE tcf_chat_threads SET updated_at = NOW() WHERE id = ?')->execute([$threadId]);
        chat_json(['ok' => true, 'message' => 'Message modifié.']);
    }

    if ($action === 'delete_message') {
        $messageId = (int) ($body['message_id'] ?? 0);
        if ($messageId <= 0) {
            chat_json(['ok' => false, 'message' => 'Message invalide.'], 400);
        }
        $st = $pdo->prepare(
            'SELECT m.id, m.thread_id, m.sender_id, m.deleted_at
             FROM tcf_chat_messages m WHERE m.id = ? LIMIT 1'
        );
        $st->execute([$messageId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row || !empty($row['deleted_at'])) {
            chat_json(['ok' => false, 'message' => 'Message introuvable.'], 404);
        }
        $threadId = (int) ($row['thread_id'] ?? 0);
        if (!chat_user_can_access_thread($pdo, $threadId, $uid) || !chat_thread_is_internal_only($pdo, $threadId)) {
            chat_json(['ok' => false, 'message' => 'Conversation inaccessible.'], 403);
        }
        $senderId = (int) ($row['sender_id'] ?? 0);
        $sessionRole = (string) ($_SESSION['role'] ?? '');
        if ($senderId !== $uid && $sessionRole !== 'super_admin') {
            chat_json(['ok' => false, 'message' => 'Suppression non autorisée.'], 403);
        }
        $pdo->prepare(
            'UPDATE tcf_chat_messages SET deleted_at = NOW(), message = ? WHERE id = ?'
        )->execute(['', $messageId]);
        $pdo->prepare('UPDATE tcf_chat_threads SET updated_at = NOW() WHERE id = ?')->execute([$threadId]);
        chat_json(['ok' => true, 'message' => 'Message supprimé.']);
    }

    if ($action === 'list_group_members') {
        $threadId = (int) ($body['thread_id'] ?? 0);
        if ($threadId <= 0 || !chat_user_can_access_thread($pdo, $threadId, $uid)) {
            chat_json(['ok' => false, 'message' => 'Groupe inaccessible.'], 403);
        }
        $stT = $pdo->prepare("SELECT thread_type FROM tcf_chat_threads WHERE id = ? LIMIT 1");
        $stT->execute([$threadId]);
        if ((string) ($stT->fetchColumn() ?: '') !== 'group') {
            chat_json(['ok' => false, 'message' => 'Ce fil n’est pas un groupe.'], 400);
        }
        $st = $pdo->prepare(
            "SELECT u.id, u.name, u.email, u.role
             FROM tcf_chat_thread_members tm
             INNER JOIN users u ON u.id = tm.user_id
             WHERE tm.thread_id = ?
             ORDER BY u.role DESC, u.name ASC"
        );
        $st->execute([$threadId]);
        $members = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($members as &$mem) {
            $r = (string) ($mem['role'] ?? '');
            $mem['role_label'] = $r === 'super_admin' ? 'Super administrateur' : 'Administrateur';
        }
        unset($mem);
        chat_json(['ok' => true, 'members' => $members]);
    }

    if ($action === 'sync_group_members') {
        if (!chat_is_admin()) {
            chat_json(['ok' => false, 'message' => 'Action réservée aux administrateurs.'], 403);
        }
        $threadId = (int) ($body['thread_id'] ?? 0);
        if ($threadId <= 0 || !chat_user_can_access_thread($pdo, $threadId, $uid)) {
            chat_json(['ok' => false, 'message' => 'Groupe inaccessible.'], 403);
        }
        if (!chat_thread_is_internal_only($pdo, $threadId)) {
            chat_json(['ok' => false, 'message' => 'Groupe non autorisé.'], 403);
        }
        $stT = $pdo->prepare("SELECT thread_type FROM tcf_chat_threads WHERE id = ? LIMIT 1");
        $stT->execute([$threadId]);
        if ((string) ($stT->fetchColumn() ?: '') !== 'group') {
            chat_json(['ok' => false, 'message' => 'Ce fil n’est pas un groupe.'], 400);
        }
        $addIds = [];
        foreach (($body['add_member_ids'] ?? []) as $mid) {
            $i = (int) $mid;
            if ($i > 0 && $i !== $uid) {
                $addIds[$i] = true;
            }
        }
        $removeIds = [];
        foreach (($body['remove_member_ids'] ?? []) as $mid) {
            $i = (int) $mid;
            if ($i > 0 && $i !== $uid) {
                $removeIds[$i] = true;
            }
        }
        if ($addIds !== []) {
            $placeholders = implode(',', array_fill(0, count($addIds), '?'));
            $stRoles = $pdo->prepare("SELECT id, role FROM users WHERE id IN ($placeholders) AND status = 'active'");
            $stRoles->execute(array_keys($addIds));
            $validAdd = [];
            while ($rr = $stRoles->fetch(PDO::FETCH_ASSOC)) {
                if (chat_is_staff_role((string) ($rr['role'] ?? ''))) {
                    $validAdd[(int) $rr['id']] = true;
                }
            }
            $insM = $pdo->prepare(
                'INSERT IGNORE INTO tcf_chat_thread_members (thread_id, user_id, joined_by) VALUES (?, ?, ?)'
            );
            foreach (array_keys($validAdd) as $newId) {
                $insM->execute([$threadId, $newId, $uid]);
            }
        }
        if ($removeIds !== []) {
            $del = $pdo->prepare('DELETE FROM tcf_chat_thread_members WHERE thread_id = ? AND user_id = ?');
            foreach (array_keys($removeIds) as $rmId) {
                $del->execute([$threadId, $rmId]);
            }
        }
        chat_json(['ok' => true, 'message' => 'Membres mis à jour.']);
    }

    if ($action === 'set_presence_visibility') {
        if (!chat_is_admin()) {
            chat_json(['ok' => false, 'message' => 'Seuls les admins peuvent désactiver leur présence.'], 403);
        }
        $visible = (int) (($body['is_visible'] ?? 1) ? 1 : 0);
        $st = $pdo->prepare(
            "INSERT INTO tcf_chat_presence_settings (user_id, is_visible) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE is_visible = VALUES(is_visible), updated_at = NOW()"
        );
        $st->execute([$uid, $visible]);
        chat_json(['ok' => true, 'is_visible' => $visible]);
    }

    chat_json(['ok' => false, 'message' => 'Action inconnue.'], 400);
} catch (Throwable $e) {
    error_log('chat_api: ' . $e->getMessage());
    chat_json(['ok' => false, 'message' => 'Erreur serveur chat.'], 500);
}

