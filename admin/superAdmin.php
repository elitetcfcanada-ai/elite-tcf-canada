<?php
session_start();
require_once __DIR__ . '/../includes/admin_upload_limits.php';
tcf_admin_apply_upload_limits();
require_once '../includes/config.php';
require_once __DIR__ . '/channel_handlers.php';
require_once __DIR__ . '/../includes/site_contact.php';
require_once __DIR__ . '/../includes/channel_branding.php';
require_once __DIR__ . '/../includes/video_duration.php';
$tcf_brand_default_name = (string) (tcf_site_contact()['brand'] ?? 'ELITE TCF CANADA');
$tcf_brand_default_tag = tcf_channel_branding_default_tagline();

// Vérifier si l'utilisateur est connecté et est un super admin ou admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'admin')) {
    header("Location: ../login.php");
    exit();
}

$isSuperAdmin = ($_SESSION['role'] === 'super_admin');
$isAdmin = ($_SESSION['role'] === 'admin');

function saRequireSuperAdminJson(): void
{
    if (($_SESSION['role'] ?? '') !== 'super_admin') {
        echo json_encode(['success' => false, 'message' => 'Accès réservé au super administrateur.']);
        exit();
    }
}

/**
 * @param array<int, array<string, mixed>> $users
 * @return array<int, array<string, mixed>>
 */
function tcf_enrich_users_with_activity_days(PDO $pdo, array $users): array
{
    foreach ($users as &$u) {
        $u['activity_days_count'] = 0;
        $u['activity_last_date'] = null;
        try {
            $st = $pdo->prepare('SELECT COUNT(*) FROM user_activity_days WHERE user_id = ?');
            $st->execute([(int) $u['id']]);
            $u['activity_days_count'] = (int) $st->fetchColumn();
            $st2 = $pdo->prepare('SELECT MAX(activity_date) FROM user_activity_days WHERE user_id = ?');
            $st2->execute([(int) $u['id']]);
            $last = $st2->fetchColumn();
            $u['activity_last_date'] = $last ? (string) $last : null;
        } catch (Throwable $e) {
        }
    }
    unset($u);

    return $users;
}

// Traitement des différentes actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'get_users':
            getUsers();
            break;
        case 'add_user':
            addUser();
            break;
        case 'update_user':
            updateUser();
            break;
        case 'delete_user':
            deleteUser();
            break;
        case 'get_videos':
            getVideos();
            break;
        case 'add_video':
            addVideo();
            break;
        case 'update_video':
            updateVideo();
            break;
        case 'delete_video':
            deleteVideo();
            break;
        case 'get_testimonials':
            getTestimonials();
            break;
        case 'delete_testimonial':
            deleteTestimonial();
            break;
        case 'update_testimonial':
            updateTestimonial();
            break;
        case 'get_playlists':
            getPlaylists();
            break;
        case 'save_playlist':
            savePlaylist();
            break;
        case 'delete_playlist':
            deletePlaylist();
            break;
        case 'get_channel_posts':
            getChannelPosts();
            break;
        case 'save_channel_post':
            saveChannelPost();
            break;
        case 'delete_channel_post':
            deleteChannelPost();
            break;
        case 'get_topics':
            getTopics();
            break;
        case 'add_topic':
            addTopic();
            break;
        case 'update_topic':
            updateTopic();
            break;
        case 'delete_topic':
            deleteTopic();
            break;
        case 'get_messages':
            getMessages();
            break;
        case 'add_message':
            addMessage();
            break;
        case 'update_message':
            updateMessage();
            break;
        case 'delete_message':
            deleteMessage();
            break;
        case 'get_admins':
            getAdmins();
            break;
        case 'add_admin':
            addAdmin();
            break;
        case 'update_admin':
            updateAdmin();
            break;
        case 'delete_admin':
            deleteAdmin();
            break;
        case 'demote_to_user':
            demoteToUser();
            break;
        case 'get_stats':
            getStats();
            break;
        case 'get_activities':
            getActivities();
            break;
        case 'get_notifications':
            getNotifications();
            break;
        case 'mark_notification_read':
            markNotificationRead();
            break;
        case 'get_chat_messages':
            getChatMessages();
            break;
        case 'send_chat_message':
            sendChatMessage();
            break;
        case 'get_traceability':
            getTraceability();
            break;
        case 'get_subscription_payments':
            getSubscriptionPaymentsAdmin();
            break;
        case 'get_subscription_revenue_stats':
            getSubscriptionRevenueStatsAdmin();
            break;
        case 'get_subscription_plans_admin':
            getSubscriptionPlansAdmin();
            break;
        case 'get_subscriptions_platform_mode':
            getSubscriptionsPlatformModeAdmin();
            break;
        case 'set_subscriptions_platform_mode':
            setSubscriptionsPlatformModeAdmin();
            break;
        case 'save_subscription_plan':
            saveSubscriptionPlanAdmin();
            break;
        case 'create_subscription_plan':
            createSubscriptionPlanAdmin();
            break;
        case 'delete_subscription_plan':
            deleteSubscriptionPlanAdmin();
            break;
        case 'get_channel_branding':
            getChannelBranding();
            break;
        case 'save_channel_branding':
            saveChannelBranding();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
            exit();
    }
}

// Fonctions de gestion des utilisateurs
function getUsers()
{
    saRequireSuperAdminJson();
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT id, name, email, role, subscription_type, status, avatar, created_at, last_activity FROM users WHERE role = 'user'");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $stmt = $pdo->query("SELECT id, name, email, role, subscription_type, status, avatar, created_at FROM users WHERE role = 'user'");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    $users = tcf_enrich_users_with_activity_days($pdo, $users);
    foreach ($users as &$u) {
        $synced = tcf_sync_user_avatar_from_disk($pdo, (int) $u['id'], $u['avatar'] ?? null);
        $u['avatar_url'] = $synced ? tcf_avatar_public_url($synced) : null;
        $u['is_online'] = tcf_user_is_online(isset($u['last_activity']) ? (string) $u['last_activity'] : null);
    }
    unset($u);
    echo json_encode(['success' => true, 'data' => $users]);
    exit();
}

function addUser()
{
    saRequireSuperAdminJson();
    global $pdo;
    try {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $subscription_type = (string) ($_POST['subscription'] ?? 'free');
        $status = $_POST['status'] ?? 'active';
        $password = (string) ($_POST['password'] ?? '');
        $confirmPassword = (string) ($_POST['confirmPassword'] ?? '');

        $subErr = tcf_subscription_validate_user_type_for_save($subscription_type, true);
        if ($subErr !== null) {
            echo json_encode(['success' => false, 'message' => $subErr]);
            exit();
        }

        $v = tcf_validate_registration_name_email_password($name, $email, $password, $confirmPassword, $pdo);
        if ($v !== null) {
            echo json_encode(['success' => false, 'message' => $v]);
            exit();
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $subExpires = tcf_subscription_expires_at_for_assignment($subscription_type);
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, subscription_type, subscription_expires_at, status, avatar) VALUES (?, ?, ?, \'user\', ?, ?, ?, NULL)');
        $success = $stmt->execute([$name, $email, $hashedPassword, $subscription_type, $subExpires, $status]);

        if ($success) {
            $newUid = (int) $pdo->lastInsertId();
            if ($newUid <= 0) {
                try {
                    $pdo->exec('DELETE FROM users WHERE id = 0 AND email = ' . $pdo->quote($email));
                } catch (Throwable $e) {
                }
                echo json_encode([
                    'success' => false,
                    'message' => 'AUTO_INCREMENT users cassé. Exécutez scripts/repair_database.php?key=REPAIR_TCF_2026',
                ]);
                exit();
            }
            addActivity($_SESSION['user_id'], 'user', 'Nouvel utilisateur ajouté', "L'utilisateur $name a été ajouté");
            echo json_encode(['success' => true, 'message' => 'Utilisateur ajouté avec succès.', 'id' => $newUid]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout de l\'utilisateur.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function updateUser()
{
    saRequireSuperAdminJson();
    global $pdo;
    try {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $subscription_type = (string) ($_POST['subscription'] ?? 'free');
        $status = (string) ($_POST['status'] ?? 'active');
        $password = (string) ($_POST['password'] ?? '');
        $confirmPassword = (string) ($_POST['confirmPassword'] ?? '');

        if ($id <= 0 || $name === '' || $email === '') {
            echo json_encode(['success' => false, 'message' => 'Informations utilisateur incomplètes.']);
            exit();
        }

        $subErr = tcf_subscription_validate_user_type_for_save($subscription_type, false);
        if ($subErr !== null) {
            echo json_encode(['success' => false, 'message' => $subErr]);
            exit();
        }

        // Vérifier si l'email existe déjà pour un autre utilisateur
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé par un autre utilisateur.']);
            exit();
        }

        $stmtPrev = $pdo->prepare('SELECT subscription_type, subscription_expires_at FROM users WHERE id = ?');
        $stmtPrev->execute([$id]);
        $prevRow = $stmtPrev->fetch(PDO::FETCH_ASSOC);
        if (!$prevRow) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
            exit();
        }
        $prevType = trim((string) ($prevRow['subscription_type'] ?? 'free'));
        $newType = trim($subscription_type);
        $sameType = ($prevType === $newType);
        $prevExpiresRaw = $prevRow['subscription_expires_at'] ?? null;
        $prevExpiresStr = ($prevExpiresRaw !== null && $prevExpiresRaw !== '') ? (string) $prevExpiresRaw : '';
        $invalidPrevDate = $prevExpiresStr === '' || str_starts_with($prevExpiresStr, '0000-00-00');
        $prevTs = !$invalidPrevDate ? strtotime($prevExpiresStr) : false;
        $wasExpired = ($prevTs !== false && $prevTs <= time());

        if ($newType === '' || $newType === 'free') {
            $subExpires = null;
        } elseif (!$sameType || $prevTs === false || $wasExpired) {
            $subExpires = tcf_subscription_expires_at_for_assignment($newType);
        } else {
            $subExpires = $prevExpiresStr;
        }

        $success = false;
        if ($password !== '' || $confirmPassword !== '') {
            if ($password !== $confirmPassword) {
                echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas.']);
                exit();
            }
            if (strlen($password) < 8) {
                echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères.']);
                exit();
            }
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, subscription_type = ?, subscription_expires_at = ?, status = ?, password = ? WHERE id = ?');
            $success = $stmt->execute([$name, $email, $subscription_type, $subExpires, $status, $hashedPassword, $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, subscription_type = ?, subscription_expires_at = ?, status = ? WHERE id = ?');
            $success = $stmt->execute([$name, $email, $subscription_type, $subExpires, $status, $id]);
        }

        if ($success) {
            addActivity($_SESSION['user_id'], 'user', 'Utilisateur modifié', "L'utilisateur $name a été modifié");
            echo json_encode(['success' => true, 'message' => 'Utilisateur mis à jour avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour de l\'utilisateur.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function deleteUser()
{
    saRequireSuperAdminJson();
    global $pdo;
    require_once __DIR__ . '/../includes/user_delete_dependencies.php';

    try {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identifiant utilisateur invalide.']);
            exit();
        }

        $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ? AND role = 'user'");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable ou compte non apprenant.']);
            exit();
        }

        $pdo->beginTransaction();
        try {
            tcf_delete_user_dependencies($pdo, $id);
            $del = $pdo->prepare('DELETE FROM users WHERE id = ? AND role = ?');
            $del->execute([$id, 'user']);
            if ($del->rowCount() === 0) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'Suppression impossible.']);
                exit();
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }

        addActivity($_SESSION['user_id'], 'user', 'Utilisateur supprimé', "L'utilisateur {$user['name']} a été supprimé");
        echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé avec succès.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
    }
    exit();
}

// Fonctions de gestion des vidéos
function getVideos()
{
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT v.*, (SELECT COUNT(*) FROM video_comments vc WHERE vc.video_id = v.id) AS comments_count FROM videos v ORDER BY v.created_at DESC");
        $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($videos as &$v) {
            $v['thumbnail_href'] = tcf_uploads_public_href($v['thumbnail_url'] ?? '');
            $v['video_href'] = tcf_uploads_public_href($v['video_url'] ?? '');
        }
        unset($v);
        try {
            $videos = tcf_enrich_videos_with_playlists($pdo, $videos);
        } catch (Throwable $e) {
        }
        echo json_encode(['success' => true, 'data' => $videos]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function addVideo()
{
    global $pdo;
    try {
        $title = trim((string) ($_POST['title'] ?? ''));
        $description = (string) ($_POST['description'] ?? '');
        $visibility = strtolower(trim((string) ($_POST['visibility'] ?? 'public')));
        if (!in_array($visibility, ['public', 'private', 'premium'], true)) {
            $visibility = 'public';
        }
        if ($title === '') {
            echo json_encode(['success' => false, 'message' => 'Le titre de la vidéo est obligatoire.']);
            exit();
        }

        // Gérer l'upload de la miniature
        $thumbnail_url = '';
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $thumbnail_url = uploadFile($_FILES['thumbnail'], 'thumbnails');
            if (!$thumbnail_url) {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'upload de la miniature. Type ou taille non valide.']);
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Veuillez sélectionner une miniature.']);
            exit();
        }

        // Gérer l'upload de la vidéo (pas de limite de taille côté application)
        $video_url = '';
        $duration = null;
        if (!isset($_FILES['video'])) {
            tcf_admin_unlink_upload($thumbnail_url);
            echo json_encode(['success' => false, 'message' => 'Veuillez sélectionner une vidéo.']);
            exit();
        }
        $videoUploadErr = (int) ($_FILES['video']['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($videoUploadErr !== UPLOAD_ERR_OK) {
            tcf_admin_unlink_upload($thumbnail_url);
            $msg = $videoUploadErr === UPLOAD_ERR_NO_FILE
                ? 'Veuillez sélectionner une vidéo.'
                : tcf_upload_error_message($videoUploadErr);
            echo json_encode(['success' => false, 'message' => $msg]);
            exit();
        }
        $video_url = uploadFile($_FILES['video'], 'videos');
        if (!$video_url) {
            tcf_admin_unlink_upload($thumbnail_url);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'upload de la vidéo. Vérifiez le format (MP4, WebM, MOV, AVI, MKV…) et les droits du dossier uploads/videos.']);
            exit();
        }
        $duration = tcf_probe_video_duration_for_db(tcf_uploads_fs_path($video_url));

        $stmt = $pdo->prepare("INSERT INTO videos (title, description, thumbnail_url, video_url, visibility, duration) VALUES (?, ?, ?, ?, ?, ?)");
        $success = $stmt->execute([$title, $description, $thumbnail_url, $video_url, $visibility, $duration]);

        if ($success) {
            $newVid = (int) $pdo->lastInsertId();
            if ($newVid <= 0) {
                // Table videos sans AUTO_INCREMENT → id=0 (vidéo invisible pour les utilisateurs)
                tcf_admin_unlink_upload($thumbnail_url);
                tcf_admin_unlink_upload($video_url);
                try {
                    $pdo->exec('DELETE FROM videos WHERE id = 0');
                } catch (Throwable $e) {
                }
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur ID vidéo (AUTO_INCREMENT cassé). Exécutez scripts/repair_database.php?key=REPAIR_TCF_2026 puis réessayez.',
                ]);
                exit();
            }
            try {
                tcf_sync_video_playlists($pdo, $newVid, tcf_parse_playlist_ids_from_post());
            } catch (Throwable $e) {
            }
            addActivity($_SESSION['user_id'], 'video', 'Nouvelle vidéo publiée', "La vidéo '$title' a été publiée");
            
            // Envoyer notification à tous les utilisateurs (role user)
            try {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'user' AND id > 0");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
                foreach ($users as $userId) {
                    addNotification((int) $userId, 'video', 'Nouvelle vidéo publiée', "La vidéo '$title' a été publiée", 'videos.php');
                }
            } catch (Throwable $e) {
                error_log('Erreur lors de l\'envoi des notifications vidéo: ' . $e->getMessage());
            }
            
            echo json_encode(['success' => true, 'message' => 'Vidéo publiée avec succès.', 'id' => $newVid]);
        } else {
            tcf_admin_unlink_upload($thumbnail_url);
            tcf_admin_unlink_upload($video_url);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la publication de la vidéo.']);
        }
    } catch (PDOException $e) {
        error_log("Erreur base de données: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function updateVideo()
{
    global $pdo;
    try {
        $id = (int) ($_POST['id'] ?? 0);
        $title = trim((string) ($_POST['title'] ?? ''));
        $description = (string) ($_POST['description'] ?? '');
        $visibility = strtolower(trim((string) ($_POST['visibility'] ?? 'public')));
        if (!in_array($visibility, ['public', 'private', 'premium'], true)) {
            $visibility = 'public';
        }
        if ($id <= 0 || $title === '') {
            echo json_encode(['success' => false, 'message' => 'Vidéo invalide (id/titre).']);
            exit();
        }

        // Récupérer les anciennes URLs
        $stmt = $pdo->prepare("SELECT thumbnail_url, video_url, duration FROM videos WHERE id = ?");
        $stmt->execute([$id]);
        $video = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$video) {
            echo json_encode(['success' => false, 'message' => 'Vidéo introuvable.']);
            exit();
        }

        $thumbnail_url = $video['thumbnail_url'];
        $video_url = $video['video_url'];
        $duration = $video['duration'] ?? null;

        // Gérer l'upload de la nouvelle miniature
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            tcf_admin_unlink_upload($thumbnail_url);
            $thumbnail_url = uploadFile($_FILES['thumbnail'], 'thumbnails');
            if (!$thumbnail_url) {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'upload de la miniature.']);
                exit();
            }
        }

        // Gérer l'upload de la nouvelle vidéo (pas de limite de taille côté application)
        if (isset($_FILES['video']) && (int) ($_FILES['video']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $videoUploadErr = (int) $_FILES['video']['error'];
            if ($videoUploadErr !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => tcf_upload_error_message($videoUploadErr)]);
                exit();
            }
            tcf_admin_unlink_upload($video_url);
            $video_url = uploadFile($_FILES['video'], 'videos');
            if (!$video_url) {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'upload de la vidéo. Vérifiez le format et le dossier uploads/videos.']);
                exit();
            }
            $duration = tcf_probe_video_duration_for_db(tcf_uploads_fs_path($video_url));
        }
        $stmt = $pdo->prepare("UPDATE videos SET title = ?, description = ?, thumbnail_url = ?, video_url = ?, visibility = ?, duration = ? WHERE id = ?");
        $success = $stmt->execute([$title, $description, $thumbnail_url, $video_url, $visibility, $duration, $id]);

        if ($success) {
            try {
                tcf_sync_video_playlists($pdo, (int) $id, tcf_parse_playlist_ids_from_post());
            } catch (Throwable $e) {
            }
            addActivity($_SESSION['user_id'], 'video', 'Vidéo modifiée', "La vidéo '$title' a été modifiée");
            echo json_encode(['success' => true, 'message' => 'Vidéo modifiée avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification de la vidéo.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function deleteVideo()
{
    global $pdo;
    try {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("SELECT thumbnail_url, video_url, title FROM videos WHERE id = ?");
        $stmt->execute([$id]);
        $video = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($video) {
            tcf_admin_unlink_upload($video['thumbnail_url'] ?? '');
            tcf_admin_unlink_upload($video['video_url'] ?? '');
        }

        $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
        $success = $stmt->execute([$id]);

        if ($success) {
            addActivity($_SESSION['user_id'], 'video', 'Vidéo supprimée', "La vidéo '{$video['title']}' a été supprimée");
            echo json_encode(['success' => true, 'message' => 'Vidéo supprimée avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression de la vidéo.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function getTestimonials()
{
    global $pdo;
    try {
        $stmt = $pdo->query('SELECT id, author_name, content, user_id, rating, created_at FROM testimonials ORDER BY created_at DESC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $rows]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function deleteTestimonial()
{
    global $pdo;
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'super_admin'], true)) {
        echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
        exit();
    }
    try {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Identifiant invalide.']);
            exit();
        }
        $stmt = $pdo->prepare('DELETE FROM testimonials WHERE id = ?');
        $success = $stmt->execute([$id]);
        if ($success) {
            addActivity($_SESSION['user_id'], 'message', 'Témoignage supprimé', "Témoignage #$id supprimé");
            echo json_encode(['success' => true, 'message' => 'Témoignage supprimé.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function updateTestimonial()
{
    global $pdo;
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'super_admin'], true)) {
        echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
        exit();
    }
    try {
        $id      = (int)   ($_POST['id']          ?? 0);
        $author  = trim(   ($_POST['author_name'] ?? ''));
        $content = trim(   ($_POST['content']     ?? ''));
        $rating  = (int)   ($_POST['rating']      ?? 0);
        if ($id <= 0 || $author === '' || $content === '') {
            echo json_encode(['success' => false, 'message' => 'Champs obligatoires manquants.']);
            exit();
        }
        if ($rating < 0 || $rating > 5) $rating = 0;
        $stmt = $pdo->prepare('UPDATE testimonials SET author_name = ?, content = ?, rating = ? WHERE id = ?');
        $ok   = $stmt->execute([$author, $content, $rating, $id]);
        if ($ok) {
            addActivity($_SESSION['user_id'], 'message', 'Témoignage modifié', "Témoignage #$id modifié");
            echo json_encode(['success' => true, 'message' => 'Témoignage mis à jour.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données : ' . $e->getMessage()]);
    }
    exit();
}

// Fonctions pour les sujets
function getTopics()
{
    global $pdo;
    try {
        $type = $_POST['type'] ?? '';
        $sql = "SELECT * FROM topics";
        if (!empty($type)) {
            $sql .= " WHERE type = ?";
        }
        $sql .= " ORDER BY created_at DESC";

        $stmt = $pdo->prepare($sql);
        if (!empty($type)) {
            $stmt->execute([$type]);
        } else {
            $stmt->execute();
        }

        $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $topics]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function addTopic()
{
    global $pdo;
    try {
        $title = $_POST['title'];
        $type = $_POST['type'];
        $visibility = $_POST['visibility'];

        $json_file = '';
        if (isset($_FILES['json_file']) && $_FILES['json_file']['error'] === UPLOAD_ERR_OK) {
            $json_file = uploadFile($_FILES['json_file'], 'topics');
            if (!$json_file) {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'upload du fichier.']);
                exit();
            }
        }

        $stmt = $pdo->prepare("INSERT INTO topics (title, type, visibility, json_file) VALUES (?, ?, ?, ?)");
        $success = $stmt->execute([$title, $type, $visibility, $json_file]);

        if ($success) {
            addActivity($_SESSION['user_id'], 'topic', 'Nouveau sujet ajouté', "Le sujet '$title' a été ajouté");
            addNotification(null, 'topic', 'Nouveau sujet ajouté', "Le sujet '$title' a été ajouté");
            echo json_encode(['success' => true, 'message' => 'Sujet ajouté avec succès.']);
        } else {
            tcf_admin_unlink_upload($json_file);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout du sujet.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function updateTopic()
{
    global $pdo;
    try {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $type = $_POST['type'];
        $visibility = $_POST['visibility'];

        // Récupérer l'ancien fichier
        $stmt = $pdo->prepare("SELECT json_file FROM topics WHERE id = ?");
        $stmt->execute([$id]);
        $topic = $stmt->fetch(PDO::FETCH_ASSOC);

        $json_file = $topic['json_file'];

        if (isset($_FILES['json_file']) && $_FILES['json_file']['error'] === UPLOAD_ERR_OK) {
            tcf_admin_unlink_upload($json_file);
            $json_file = uploadFile($_FILES['json_file'], 'topics');
            if (!$json_file) {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'upload du fichier JSON.']);
                exit();
            }
        }

        $stmt = $pdo->prepare("UPDATE topics SET title = ?, type = ?, visibility = ?, json_file = ? WHERE id = ?");
        $success = $stmt->execute([$title, $type, $visibility, $json_file, $id]);

        if ($success) {
            addActivity($_SESSION['user_id'], 'topic', 'Sujet modifié', "Le sujet '$title' a été modifié");
            echo json_encode(['success' => true, 'message' => 'Sujet mis à jour avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du sujet.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function deleteTopic()
{
    global $pdo;
    try {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("SELECT json_file, title FROM topics WHERE id = ?");
        $stmt->execute([$id]);
        $topic = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($topic) {
            tcf_admin_unlink_upload($topic['json_file'] ?? '');
        }

        $stmt = $pdo->prepare("DELETE FROM topics WHERE id = ?");
        $success = $stmt->execute([$id]);

        if ($success) {
            addActivity($_SESSION['user_id'], 'topic', 'Sujet supprimé', "Le sujet '{$topic['title']}' a été supprimé");
            echo json_encode(['success' => true, 'message' => 'Sujet supprimé avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression du sujet.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

// Fonctions pour les messages communautaires
function getMessages()
{
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM community_messages ORDER BY created_at DESC");
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $messages]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function addMessage()
{
    global $pdo;
    try {
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $content = trim((string) ($_POST['content'] ?? ''));
        $recipients = $_POST['recipients'];

        $stmt = $pdo->prepare("INSERT INTO community_messages (subject, content, recipients) VALUES (?, ?, ?)");
        $success = $stmt->execute([$subject, $content, $recipients]);

        if ($success) {
            addActivity($_SESSION['user_id'], 'message', 'Nouveau message communautaire', "Le message '$subject' a été envoyé");

            $defaultTitle = 'Nouveau message communautaire';
            if ($subject !== '' && $content !== '') {
                if ($subject === $content) {
                    $notifTitle = $defaultTitle;
                    $notifContent = $subject;
                } else {
                    $notifTitle = $subject;
                    $notifContent = $content;
                }
            } elseif ($subject !== '') {
                $notifTitle = $defaultTitle;
                $notifContent = $subject;
            } else {
                $notifTitle = $defaultTitle;
                $notifContent = $content;
            }
            if ($notifContent === '') {
                $notifContent = '—';
            }
            if (function_exists('mb_strlen') && function_exists('mb_substr')) {
                if (mb_strlen($notifTitle) > 255) {
                    $notifTitle = mb_substr($notifTitle, 0, 252) . '…';
                }
            } elseif (strlen($notifTitle) > 255) {
                $notifTitle = substr($notifTitle, 0, 252) . '…';
            }

            // Envoyer notification à tous les utilisateurs connectés
            try {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'user'");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
                foreach ($users as $userId) {
                    addNotification((int) $userId, 'message', $notifTitle, $notifContent, 'messages.php');
                }
            } catch (Throwable $e) {
                error_log('Erreur lors de l\'envoi des notifications message communautaire: ' . $e->getMessage());
            }

            echo json_encode(['success' => true, 'message' => 'Message envoyé avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi du message.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function updateMessage()
{
    global $pdo;
    try {
        $id = $_POST['id'];
        $subject = $_POST['subject'];
        $content = $_POST['content'];
        $recipients = $_POST['recipients'];

        $stmt = $pdo->prepare("UPDATE community_messages SET subject = ?, content = ?, recipients = ? WHERE id = ?");
        $success = $stmt->execute([$subject, $content, $recipients, $id]);

        if ($success) {
            addActivity($_SESSION['user_id'], 'message', 'Message modifié', "Le message '$subject' a été modifié");
            echo json_encode(['success' => true, 'message' => 'Message modifié avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification du message.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function deleteMessage()
{
    global $pdo;
    try {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM community_messages WHERE id = ?");
        $success = $stmt->execute([$id]);

        if ($success) {
            addActivity($_SESSION['user_id'], 'message', 'Message supprimé', "Un message communautaire a été supprimé");
            echo json_encode(['success' => true, 'message' => 'Message supprimé avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression du message.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

// Fonctions pour les administrateurs
function getAdmins()
{
    saRequireSuperAdminJson();
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT id, name, email, role, status, avatar, last_login, last_activity, created_at FROM users WHERE role IN ('admin', 'super_admin')");
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $stmt = $pdo->query("SELECT id, name, email, role, status, avatar, last_login, created_at FROM users WHERE role IN ('admin', 'super_admin')");
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    foreach ($admins as &$a) {
        $synced = tcf_sync_user_avatar_from_disk($pdo, (int) $a['id'], $a['avatar'] ?? null);
        $a['avatar_url'] = $synced ? tcf_avatar_public_url($synced) : null;
        $a['is_online'] = tcf_user_is_online(isset($a['last_activity']) ? (string) $a['last_activity'] : null);
    }
    unset($a);
    echo json_encode(['success' => true, 'data' => $admins]);
    exit();
}

function addAdmin()
{
    saRequireSuperAdminJson();
    global $pdo;
    try {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $role = (string) ($_POST['role'] ?? 'admin');
        $status = $_POST['status'] ?? 'active';
        $password = (string) ($_POST['password'] ?? '');
        $confirmPassword = (string) ($_POST['confirmPassword'] ?? '');

        if (!in_array($role, ['admin', 'super_admin'], true)) {
            echo json_encode(['success' => false, 'message' => 'Rôle invalide.']);
            exit();
        }

        $v = tcf_validate_registration_name_email_password($name, $email, $password, $confirmPassword, $pdo);
        if ($v !== null) {
            echo json_encode(['success' => false, 'message' => $v]);
            exit();
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status, avatar) VALUES (?, ?, ?, ?, ?, NULL)");
        $success = $stmt->execute([$name, $email, $hashedPassword, $role, $status]);

        if ($success) {
            addActivity($_SESSION['user_id'], 'admin', 'Nouvel administrateur ajouté', "L'administrateur $name a été ajouté");
            echo json_encode(['success' => true, 'message' => 'Administrateur ajouté avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout de l\'administrateur.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function updateAdmin()
{
    saRequireSuperAdminJson();
    global $pdo;
    try {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $status = $_POST['status'];

        if (!in_array($role, ['admin', 'super_admin'], true)) {
            echo json_encode(['success' => false, 'message' => 'Rôle invalide.']);
            exit();
        }

        // Empêcher la modification de son propre compte
        if ($id == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas modifier votre propre compte super admin.']);
            exit();
        }

        // Vérifier si l'email existe déjà pour un autre utilisateur
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé par un autre utilisateur.']);
            exit();
        }

        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ?, status = ? WHERE id = ?");
        $success = $stmt->execute([$name, $email, $role, $status, $id]);

        if ($success) {
            addActivity($_SESSION['user_id'], 'admin', 'Administrateur modifié', "L'administrateur $name a été modifié");
            echo json_encode(['success' => true, 'message' => 'Administrateur mis à jour avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour de l\'administrateur.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function deleteAdmin()
{
    saRequireSuperAdminJson();
    global $pdo;
    try {
        $id = $_POST['id'];

        // Empêcher la suppression de soi-même
        if ($id == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte.']);
            exit();
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role IN ('admin', 'super_admin')");
        $success = $stmt->execute([$id]);

        if ($success && $stmt->rowCount() > 0) {
            addActivity($_SESSION['user_id'], 'admin', 'Administrateur supprimé', "Un administrateur a été supprimé");
            echo json_encode(['success' => true, 'message' => 'Administrateur supprimé avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression de l\'administrateur.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function demoteToUser()
{
    saRequireSuperAdminJson();
    global $pdo;
    try {
        $id = $_POST['id'];

        // Empêcher la rétrogradation de soi-même
        if ($id == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas rétrograder votre propre compte.']);
            exit();
        }

        $stmt = $pdo->prepare("UPDATE users SET role = 'user' WHERE id = ? AND role = 'admin'");
        $success = $stmt->execute([$id]);

        if ($success && $stmt->rowCount() > 0) {
            // Récupérer le nom de l'administrateur rétrogradé
            $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            addActivity($_SESSION['user_id'], 'admin', 'Administrateur rétrogradé', "L'administrateur {$user['name']} a été rétrogradé utilisateur");
            echo json_encode(['success' => true, 'message' => 'Administrateur rétrogradé avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la rétrogradation de l\'administrateur.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

// Fonctions pour le chat
function getChatMessages()
{
    global $pdo;
    try {
        $user_id = $_POST['user_id'] ?? null;

        if (!$user_id) {
            echo json_encode(['success' => false, 'message' => 'ID utilisateur manquant']);
            exit();
        }

        $stmt = $pdo->prepare("
            SELECT cm.*, u.name as user_name, a.name as admin_name 
            FROM chat_messages cm 
            LEFT JOIN users u ON cm.user_id = u.id 
            LEFT JOIN users a ON cm.admin_id = a.id 
            WHERE cm.user_id = ? 
            ORDER BY cm.created_at ASC
        ");
        $stmt->execute([$user_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $messages]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function sendChatMessage()
{
    global $pdo;
    try {
        $user_id = $_POST['user_id'];
        $message = $_POST['message'];
        $is_admin = 1; // Toujours admin car c'est le super admin qui envoie

        $stmt = $pdo->prepare("INSERT INTO chat_messages (user_id, admin_id, message, is_admin) VALUES (?, ?, ?, ?)");
        $success = $stmt->execute([$user_id, $_SESSION['user_id'], $message, $is_admin]);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Message envoyé avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi du message.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

// Fonctions pour les statistiques
function tcf_admin_has_visit_logs(): bool
{
    global $pdo;
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    try {
        $pdo->query('SELECT 1 FROM site_visit_logs LIMIT 1');
        $ok = true;
    } catch (Throwable $e) {
        $ok = false;
    }
    return $ok;
}

function tcf_trace_sql_where(string $range, string $dateCol = 'created_at'): string
{
    switch ($range) {
        case '7d':
            return "$dateCol >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        case '30d':
            return "$dateCol >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        case '90d':
            return "$dateCol >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)";
        case 'year':
            return "YEAR($dateCol) = YEAR(CURDATE())";
        case 'all':
        default:
            return '1=1';
    }
}

function getTraceability()
{
    global $pdo;
    $range = preg_replace('/[^a-z0-9]/', '', strtolower($_POST['range'] ?? '30d'));
    if (!in_array($range, ['7d', '30d', '90d', 'year', 'all'], true)) {
        $range = '30d';
    }

    if (!tcf_admin_has_visit_logs()) {
        echo json_encode([
            'success' => true,
            'data' => [
                'range' => $range,
                'visits_labels' => [],
                'visits_values' => [],
                'users_labels' => [],
                'users_values' => [],
                'payments_count_labels' => [],
                'payments_count_values' => [],
                'revenue_labels' => [],
                'revenue_values' => [],
                'visit_countries' => [],
                'signup_countries' => [],
                'traffic_visits' => [],
                'traffic_signups' => [],
            ],
        ]);
        exit();
    }

    try {
        $wVisit = tcf_trace_sql_where($range, 'v.created_at');
        $wUser = tcf_trace_sql_where($range, 'u.created_at');
        $wPay = tcf_trace_sql_where($range, 'p.created_at');

        if ($range === 'all') {
            $bucket = "DATE_FORMAT(v.created_at, '%Y-%m')";
        } else {
            $bucket = 'DATE(v.created_at)';
        }
        $stmt = $pdo->query("SELECT $bucket AS lb, COUNT(*) AS c FROM site_visit_logs v WHERE $wVisit GROUP BY lb ORDER BY lb");
        $vis = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($range === 'all') {
            $ub = "DATE_FORMAT(u.created_at, '%Y-%m')";
        } else {
            $ub = 'DATE(u.created_at)';
        }
        $stmt = $pdo->query("SELECT $ub AS lb, COUNT(*) AS c FROM users u WHERE u.role = 'user' AND $wUser GROUP BY lb ORDER BY lb");
        $usr = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($range === 'all') {
            $pb = "DATE_FORMAT(p.created_at, '%Y-%m')";
        } else {
            $pb = 'DATE(p.created_at)';
        }
        $stmt = $pdo->query("SELECT $pb AS lb, COUNT(*) AS c FROM payments p WHERE p.status = 'completed' AND $wPay GROUP BY lb ORDER BY lb");
        $payc = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->query("SELECT $pb AS lb, COALESCE(SUM(p.amount),0) AS s FROM payments p WHERE p.status = 'completed' AND $wPay GROUP BY lb ORDER BY lb");
        $rev = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->query("
            SELECT v.country_code AS code, v.country_name AS name,
                   COUNT(*) AS c,
                   AVG(v.latitude) AS lat, AVG(v.longitude) AS lon
            FROM site_visit_logs v
            WHERE $wVisit AND v.country_code IS NOT NULL AND v.country_code != ''
            GROUP BY v.country_code, v.country_name
            ORDER BY c DESC
            LIMIT 40
        ");
        $vcountries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->query("
            SELECT u.reg_country_code AS code, u.reg_country_name AS name, COUNT(*) AS c
            FROM users u
            WHERE u.role = 'user' AND $wUser
              AND u.reg_country_code IS NOT NULL AND u.reg_country_code != ''
            GROUP BY u.reg_country_code, u.reg_country_name
            ORDER BY c DESC
            LIMIT 40
        ");
        $scountries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->query('
            SELECT country_code AS code, AVG(latitude) AS lat, AVG(longitude) AS lon
            FROM site_visit_logs
            WHERE latitude IS NOT NULL AND longitude IS NOT NULL AND country_code IS NOT NULL
            GROUP BY country_code
        ');
        $centroids = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $centroids[$r['code']] = ['lat' => (float) $r['lat'], 'lon' => (float) $r['lon']];
        }
        foreach ($scountries as &$sr) {
            $c = $sr['code'];
            if (isset($centroids[$c])) {
                $sr['lat'] = $centroids[$c]['lat'];
                $sr['lon'] = $centroids[$c]['lon'];
            } else {
                $sr['lat'] = null;
                $sr['lon'] = null;
            }
        }
        unset($sr);

        $stmt = $pdo->query("
            SELECT v.traffic_source AS src, COUNT(*) AS c
            FROM site_visit_logs v
            WHERE $wVisit
            GROUP BY v.traffic_source
            ORDER BY c DESC
        ");
        $tvis = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->query("
            SELECT u.reg_traffic_source AS src, COUNT(*) AS c
            FROM users u
            WHERE u.role = 'user' AND $wUser AND u.reg_traffic_source IS NOT NULL AND u.reg_traffic_source != ''
            GROUP BY u.reg_traffic_source
            ORDER BY c DESC
        ");
        $tsign = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => [
                'range' => $range,
                'visits_labels' => array_column($vis, 'lb'),
                'visits_values' => array_map('intval', array_column($vis, 'c')),
                'users_labels' => array_column($usr, 'lb'),
                'users_values' => array_map('intval', array_column($usr, 'c')),
                'payments_count_labels' => array_column($payc, 'lb'),
                'payments_count_values' => array_map('intval', array_column($payc, 'c')),
                'revenue_labels' => array_column($rev, 'lb'),
                'revenue_values' => array_map('floatval', array_column($rev, 's')),
                'visit_countries' => $vcountries,
                'signup_countries' => $scountries,
                'traffic_visits' => $tvis,
                'traffic_signups' => $tsign,
            ],
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function getStats()
{
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
        $usersCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        $visitorsCount = 0;
        if (tcf_admin_has_visit_logs()) {
            $stmt = $pdo->query("SELECT COUNT(DISTINCT session_id) as count FROM site_visit_logs WHERE DATE(created_at) = CURDATE()");
            $visitorsCount = (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } else {
            try {
                $stmt = $pdo->query("SELECT COUNT(DISTINCT ip_address) as count FROM analytics WHERE DATE(created_at) = CURDATE()");
                $visitorsCount = (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            } catch (Throwable $e) {
                $visitorsCount = 0;
            }
        }

        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE subscription_type != 'free' AND status = 'active' AND role = 'user'");
        $subsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        $revenuePayments = 0.0;
        try {
            $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'completed' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
            $revenuePayments = (float) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (Throwable $e) {
            $revenuePayments = 0.0;
        }

        $revenueSubsMonth = 0.0;
        try {
            $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM subscription_payments WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
            $revenueSubsMonth = (float) $stmt->fetchColumn();
        } catch (Throwable $e) {
            $revenueSubsMonth = 0.0;
        }

        $revenue = $revenuePayments + $revenueSubsMonth;

        echo json_encode([
            'success' => true,
            'data' => [
                'users' => $usersCount,
                'visitors' => $visitorsCount,
                'subs' => $subsCount,
                'revenue' => $revenue,
                'revenue_subscription_demo' => $revenueSubsMonth,
                'revenue_payments_gateway' => $revenuePayments,
            ],
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function getSubscriptionPaymentsAdmin(): void
{
    global $pdo;
    try {
        $stmt = $pdo->query(
            "SELECT sp.id, sp.user_id, sp.plan_key, sp.plan_label, sp.amount, sp.currency, sp.payment_method, sp.created_at,
                    u.name AS user_name, u.email AS user_email
             FROM subscription_payments sp
             LEFT JOIN users u ON u.id = sp.user_id
             ORDER BY sp.created_at DESC
             LIMIT 500"
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $rows]);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'data' => [], 'message' => 'Table subscription_payments absente ou erreur — importez database/tcf.sql']);
    }
    exit();
}

/**
 * Série mensuelle des revenus (abonnements) sur les 12 derniers mois — pour graphique admin.
 *
 * @return array{labels: list<string>, values: list<float>}
 */
function tcf_sa_subscription_revenue_chart_last12m(PDO $pdo): array
{
    $monthsFr = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
    $byMonth = [];
    try {
        $stM = $pdo->query(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COALESCE(SUM(amount), 0) AS total
             FROM subscription_payments
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')"
        );
        foreach ($stM->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $byMonth[(string) $r['ym']] = (float) $r['total'];
        }
    } catch (Throwable $e) {
    }
    $labels = [];
    $values = [];
    for ($i = 11; $i >= 0; $i--) {
        $dt = new DateTimeImmutable('first day of this month');
        $dt = $dt->modify('-' . $i . ' months');
        $key = $dt->format('Y-m');
        $mi = (int) $dt->format('n') - 1;
        $labels[] = ($monthsFr[$mi] ?? $dt->format('m')) . ' ' . $dt->format('Y');
        $values[] = round($byMonth[$key] ?? 0.0, 2);
    }

    return ['labels' => $labels, 'values' => $values];
}

function getSubscriptionRevenueStatsAdmin(): void
{
    global $pdo;
    try {
        $total = (float) $pdo->query('SELECT COALESCE(SUM(amount), 0) FROM subscription_payments')->fetchColumn();
        $month = (float) $pdo->query(
            'SELECT COALESCE(SUM(amount), 0) FROM subscription_payments WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())'
        )->fetchColumn();
        $count = (int) $pdo->query('SELECT COUNT(*) FROM subscription_payments')->fetchColumn();
        $chart = tcf_sa_subscription_revenue_chart_last12m($pdo);
        echo json_encode([
            'success' => true,
            'data' => [
                'total_revenue' => $total,
                'month_revenue' => $month,
                'transactions' => $count,
                'chart' => $chart,
            ],
        ]);
    } catch (Throwable $e) {
        $chart = tcf_sa_subscription_revenue_chart_last12m($pdo);
        echo json_encode([
            'success' => false,
            'data' => [
                'total_revenue' => 0.0,
                'month_revenue' => 0.0,
                'transactions' => 0,
                'chart' => $chart,
            ],
            'message' => 'Impossible de calculer les revenus abonnements (table ou colonne manquante).',
        ]);
    }
    exit();
}

function getSubscriptionPlansAdmin(): void
{
    try {
        $rows = tcf_subscription_plans_catalog_admin();
        echo json_encode(['success' => true, 'data' => $rows], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'data' => [], 'message' => $e->getMessage()]);
    }
    exit();
}

function getSubscriptionsPlatformModeAdmin(): void
{
    global $pdo;
    require_once __DIR__ . '/../includes/platform_settings.php';
    try {
        $disabled = tcf_subscriptions_platform_disabled($pdo);
        echo json_encode([
            'success' => true,
            'disabled' => $disabled,
            'message' => $disabled
                ? 'Mode gratuit actif : tout le contenu premium est accessible sans abonnement.'
                : 'Abonnements actifs : les cartes et paiements sont visibles côté utilisateur.',
        ], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'disabled' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

function setSubscriptionsPlatformModeAdmin(): void
{
    global $pdo;
    require_once __DIR__ . '/../includes/platform_settings.php';
    $disabled = isset($_POST['disabled']) && (string) $_POST['disabled'] === '1';
    try {
        tcf_platform_setting_set($pdo, 'subscriptions_disabled', $disabled ? '1' : '0');
        echo json_encode([
            'success' => true,
            'disabled' => $disabled,
            'message' => $disabled
                ? 'Abonnements désactivés. Le premium est gratuit pour tous les utilisateurs.'
                : 'Abonnements réactivés. Les cartes d’abonnement sont de nouveau visibles.',
        ], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

function createSubscriptionPlanAdmin(): void
{
    global $pdo;
    try {
        $planKey = 'plan_c_' . bin2hex(random_bytes(5));
        if (strlen($planKey) > 32) {
            $planKey = 'plan_c_' . substr(sha1((string) microtime(true)), 0, 24);
        }
        $tier = trim((string) ($_POST['tier'] ?? ''));
        if ($tier === '') {
            $tier = 'Nouveau forfait';
        }
        $badge = trim((string) ($_POST['badge'] ?? ''));
        if ($badge === '') {
            $badge = 'À configurer';
        }
        $feats = json_encode(tcf_subscription_default_features(), JSON_UNESCAPED_UNICODE);
        if ($feats === false) {
            $feats = '[]';
        }
        $mxSt = $pdo->query('SELECT COALESCE(MAX(sort_order), 0) FROM subscription_plan_catalog');
        $mx = $mxSt ? (int) $mxSt->fetchColumn() : 0;
        $st = $pdo->prepare(
            'INSERT INTO subscription_plan_catalog (plan_key, tier, badge, price, currency, duration_days, features_json, sort_order, is_active) VALUES (?, ?, ?, 0, ?, 7, ?, ?, 1)'
        );
        $st->execute([$planKey, $tier, $badge, '$', $feats, $mx + 1]);
        echo json_encode(['success' => true, 'message' => 'Forfait ajouté. Complétez les informations puis enregistrez.']);
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        if (str_contains($msg, 'subscription_plan_catalog') || str_contains($msg, 'Unknown table')) {
            echo json_encode(['success' => false, 'message' => 'Table catalogue absente — exécutez la migration SQL fournie avec le projet (subscription_plan_catalog).']);
            exit();
        }
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $msg]);
    }
    exit();
}

function deleteSubscriptionPlanAdmin(): void
{
    global $pdo;
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Forfait invalide.']);
        exit();
    }
    try {
        $st = $pdo->prepare('SELECT plan_key FROM subscription_plan_catalog WHERE id = ?');
        $st->execute([$id]);
        $key = $st->fetchColumn();
        if ($key === false || $key === null) {
            echo json_encode(['success' => false, 'message' => 'Forfait introuvable.']);
            exit();
        }
        $planKey = (string) $key;
        $cSt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE subscription_type = ?');
        $cSt->execute([$planKey]);
        if ((int) $cSt->fetchColumn() > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Suppression impossible : des comptes membres sont encore associés à ce forfait. Désactivez-le ou modifiez ces comptes avant de supprimer.',
            ]);
            exit();
        }
        $pdo->prepare('DELETE FROM subscription_plan_catalog WHERE id = ?')->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Forfait supprimé.']);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
    exit();
}

function saveSubscriptionPlanAdmin(): void
{
    global $pdo;
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Identifiant de formule invalide.']);
        exit();
    }
    $tier = trim((string) ($_POST['tier'] ?? ''));
    $badge = trim((string) ($_POST['badge'] ?? ''));
    $priceRaw = str_replace(',', '.', trim((string) ($_POST['price'] ?? '0')));
    $price = is_numeric($priceRaw) ? (float) $priceRaw : -1.0;
    $currency = trim((string) ($_POST['currency'] ?? '$'));
    if (strlen($currency) > 8) {
        $currency = '$';
    }
    $duration = (int) ($_POST['duration_days'] ?? 7);
    if ($duration < 1) {
        $duration = 7;
    }
    if ($duration > 730) {
        $duration = 730;
    }
    $sortOrder = (int) ($_POST['sort_order'] ?? 0);
    $isActive = isset($_POST['is_active']) && (string) $_POST['is_active'] === '1' ? 1 : 0;

    $featuresRaw = (string) ($_POST['features'] ?? '');
    $lines = preg_split('/\r\n|\r|\n/', $featuresRaw);
    $feats = [];
    foreach ($lines as $ln) {
        $ln = trim($ln);
        if ($ln !== '') {
            $feats[] = $ln;
        }
    }
    if ($feats === []) {
        $feats = tcf_subscription_default_features();
    }
    $featuresJson = json_encode($feats, JSON_UNESCAPED_UNICODE);
    if ($featuresJson === false) {
        $featuresJson = '[]';
    }

    if ($tier === '' || $badge === '') {
        echo json_encode(['success' => false, 'message' => 'Le palier et la durée affichée sont obligatoires.']);
        exit();
    }
    if ($price < 0 || $price > 999999.99) {
        echo json_encode(['success' => false, 'message' => 'Montant invalide.']);
        exit();
    }

    try {
        $chk = $pdo->prepare('SELECT id FROM subscription_plan_catalog WHERE id = ?');
        $chk->execute([$id]);
        if (!$chk->fetchColumn()) {
            echo json_encode(['success' => false, 'message' => 'Formule introuvable (id).']);
            exit();
        }
        $st = $pdo->prepare(
            'UPDATE subscription_plan_catalog SET tier = ?, badge = ?, price = ?, currency = ?, duration_days = ?, features_json = ?, sort_order = ?, is_active = ? WHERE id = ?'
        );
        $st->execute([$tier, $badge, $price, $currency, $duration, $featuresJson, $sortOrder, $isActive, $id]);
        echo json_encode(['success' => true, 'message' => 'Formule enregistrée. Elle apparaît sur la page Abonnement.']);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    }
    exit();
}

// Fonctions pour les activités récentes
function getActivities()
{
    global $pdo;
    try {
        $limit = 500;
        if (isset($_POST['limit']) && $_POST['limit'] !== '') {
            $limit = min(800, max(1, (int) $_POST['limit']));
        }
        $lim = (string) $limit;
        $stmt = $pdo->prepare(
            "
            SELECT a.id, a.user_id, a.type, a.title, a.description, a.icon, a.created_at,
                   u.name AS user_name, u.email AS user_email
            FROM activities a
            LEFT JOIN users u ON a.user_id = u.id
            ORDER BY a.created_at DESC, a.id DESC
            LIMIT {$lim}"
        );
        $stmt->execute();
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $activities]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

// Fonctions pour les notifications
function getNotifications()
{
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 20");
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $unread = 0;
        $uid = (int) ($_SESSION['user_id'] ?? 0);
        if ($uid > 0) {
            $stU = $pdo->prepare(
                "SELECT COUNT(*) FROM notifications WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0 AND type IN ('video', 'topic', 'message', 'user', 'update', 'video_comment', 'testimonial', 'subscription', 'subscription_staff')"
            );
            $stU->execute([$uid]);
            $unread = (int) $stU->fetchColumn();
        }
        echo json_encode(['success' => true, 'data' => $notifications, 'unread_count' => $unread]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function markNotificationRead()
{
    global $pdo;
    try {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        $success = $stmt->execute([$id]);

        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

// Fonctions utilitaires
function addActivity($user_id, $type, $title, $description)
{
    global $pdo;
    try {
        $icons = [
            'user' => 'bx bxs-user',
            'video' => 'bx bxs-video',
            'topic' => 'bx bxs-book',
            'message' => 'bx bxs-message',
            'admin' => 'bx bxs-shield'
        ];

        $icon = $icons[$type] ?? 'bx bxs-bell';

        $stmt = $pdo->prepare("INSERT INTO activities (user_id, type, title, description, icon) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $type, $title, $description, $icon]);
    } catch (PDOException $e) {
        error_log("Erreur activité: " . $e->getMessage());
    }
}

function addNotification($user_id, $type, $title, $content, $deep_link = null)
{
    global $pdo;
    try {
        if ($deep_link !== null && $deep_link !== '') {
            $stmt = $pdo->prepare('INSERT INTO notifications (user_id, type, title, content, deep_link) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$user_id, $type, $title, $content, $deep_link]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO notifications (user_id, type, title, content) VALUES (?, ?, ?, ?)');
            $stmt->execute([$user_id, $type, $title, $content]);
        }
    } catch (PDOException $e) {
        error_log("Erreur notification: " . $e->getMessage());
    }
}

function tcf_admin_unlink_upload(?string $stored): void
{
    $fs = tcf_uploads_fs_path($stored);
    if ($fs !== '' && is_file($fs)) {
        @unlink($fs);
    }
}

function uploadFile($file, $folder)
{
    $root = dirname(__DIR__);
    $targetDir = $root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($file['name']));
    $targetPath = $targetDir . $fileName;

    $allowedTypes = [];
    $maxSize = null;
    if ($folder === 'thumbnails') {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'image/webp'];
        $maxSize = 10 * 1024 * 1024; // 10MB
    } elseif ($folder === 'videos') {
        // Pas de plafond applicatif : seule la config PHP (php.ini) peut limiter.
        $allowedTypes = [
            'video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/x-msvideo',
            'video/x-matroska', 'video/mpeg', 'application/octet-stream',
        ];
        $allowedVideoExt = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', 'm4v', 'mpeg', 'mpg'];
        $ext = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedVideoExt, true)) {
            error_log('Extension vidéo non autorisée: ' . $ext);
            return false;
        }
    } elseif ($folder === 'topics') {
        $allowedTypes = ['application/json'];
        $maxSize = 5 * 1024 * 1024; // 5MB
    } elseif ($folder === 'channel') {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'image/webp'];
        $maxSize = 8 * 1024 * 1024; // 8MB (bannière large)
    }

    $fileType = '';
    if (is_uploaded_file($file['tmp_name'] ?? '')) {
        $fileType = (string) (@mime_content_type($file['tmp_name']) ?: '');
    }
    if ($folder === 'videos') {
        if ($fileType !== '' && !in_array($fileType, $allowedTypes, true) && $fileType !== 'application/octet-stream') {
            error_log('Type MIME vidéo non reconnu: ' . $fileType);
            return false;
        }
    } elseif ($fileType === '' || !in_array($fileType, $allowedTypes, true)) {
        error_log('Type de fichier non autorisé: ' . $fileType);
        return false;
    }

    if ($maxSize !== null && (int) ($file['size'] ?? 0) > $maxSize) {
        error_log('Fichier trop volumineux: ' . ($file['size'] ?? 0) . ' bytes (max ' . $maxSize . ')');
        return false;
    }

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'uploads/' . $folder . '/' . $fileName;
    }

    error_log("Erreur lors du déplacement du fichier: " . $file['tmp_name'] . " vers " . $targetPath);
    return false;
}

function generateAvatar($name)
{
    $words = explode(' ', trim($name));
    $avatar = '';
    foreach ($words as $word) {
        if (!empty($word)) {
            $avatar .= strtoupper(substr($word, 0, 1));
            if (strlen($avatar) >= 2) break;
        }
    }
    return $avatar ?: 'US';
}

$profile_flash = $_SESSION['profile_flash'] ?? null;
unset($_SESSION['profile_flash']);

$tcf_profile_panel_user = null;
try {
    $stmtSaProf = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmtSaProf->execute([(int) $_SESSION['user_id']]);
    $tcf_profile_panel_user = $stmtSaProf->fetch(PDO::FETCH_ASSOC);
    if ($tcf_profile_panel_user) {
        $saAvSync = tcf_sync_user_avatar_from_disk($pdo, (int) $tcf_profile_panel_user['id'], $tcf_profile_panel_user['avatar'] ?? null);
        $tcf_profile_panel_user['avatar_resolved'] = $saAvSync;
        $tcf_profile_panel_user['avatar_display_url'] = tcf_avatar_public_url($saAvSync);
    }
} catch (Throwable $e) {
    $tcf_profile_panel_user = null;
}

$tcf_sa_nav_unread = 0;
if ($tcf_profile_panel_user) {
    try {
        if ($isSuperAdmin) {
            $navQuery = "SELECT COUNT(*) FROM notifications WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0 AND type IN ('video', 'topic', 'message', 'user', 'update', 'video_comment', 'testimonial', 'subscription', 'subscription_staff')";
        } else {
            $navQuery = "SELECT COUNT(*) FROM notifications WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0 AND type IN ('video', 'topic', 'video_comment')";
        }
        $stNavU = $pdo->prepare($navQuery);
        $stNavU->execute([(int) $tcf_profile_panel_user['id']]);
        $tcf_sa_nav_unread = (int) $stNavU->fetchColumn();
    } catch (Throwable $e) {
        $tcf_sa_nav_unread = 0;
    }
}

// Chargement initial de la page
try {
    // Charger les données initiales
    try {
        $users = $pdo->query("SELECT id, name, email, role, subscription_type, status, avatar, created_at, last_activity FROM users WHERE role = 'user'")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $users = $pdo->query("SELECT id, name, email, role, subscription_type, status, avatar, created_at FROM users WHERE role = 'user'")->fetchAll(PDO::FETCH_ASSOC);
    }
    $users = tcf_enrich_users_with_activity_days($pdo, $users);
    foreach ($users as &$u) {
        $synced = tcf_sync_user_avatar_from_disk($pdo, (int) $u['id'], $u['avatar'] ?? null);
        $u['avatar_url'] = $synced ? tcf_avatar_public_url($synced) : null;
        $u['is_online'] = tcf_user_is_online(isset($u['last_activity']) ? (string) $u['last_activity'] : null);
    }
    unset($u);
    $videos = $pdo->query("SELECT v.*, (SELECT COUNT(*) FROM video_comments vc WHERE vc.video_id = v.id) AS comments_count FROM videos v ORDER BY v.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($videos as &$vid) {
        $vid['thumbnail_href'] = tcf_uploads_public_href($vid['thumbnail_url'] ?? '');
        $vid['video_href'] = tcf_uploads_public_href($vid['video_url'] ?? '');
    }
    unset($vid);
    try {
        $videos = tcf_enrich_videos_with_playlists($pdo, $videos);
    } catch (Throwable $e) {
    }
    $topics = $pdo->query("SELECT * FROM topics ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    try {
        $admins = $pdo->query("SELECT id, name, email, role, status, avatar, last_login, last_activity, created_at FROM users WHERE role IN ('admin', 'super_admin')")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $admins = $pdo->query("SELECT id, name, email, role, status, avatar, last_login, created_at FROM users WHERE role IN ('admin', 'super_admin')")->fetchAll(PDO::FETCH_ASSOC);
    }
    foreach ($admins as &$a) {
        $synced = tcf_sync_user_avatar_from_disk($pdo, (int) $a['id'], $a['avatar'] ?? null);
        $a['avatar_url'] = $synced ? tcf_avatar_public_url($synced) : null;
        $a['is_online'] = tcf_user_is_online(isset($a['last_activity']) ? (string) $a['last_activity'] : null);
    }
    unset($a);
    $messages = $pdo->query("SELECT * FROM community_messages ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    $activities = [];
    if ($isSuperAdmin) {
        $notifications = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $notifications = $pdo->query("SELECT * FROM notifications WHERE type IN ('video', 'topic', 'video_comment') ORDER BY created_at DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $users = $videos = $topics = $admins = $messages = $activities = $notifications = [];
}

// Convertir en JSON pour JavaScript
$users_json = json_encode($users);
$videos_json = json_encode($videos);
$topics_json = json_encode($topics);
$admins_json = json_encode($admins);
$messages_json = json_encode($messages);
$activities_json = json_encode($activities);
$notifications_json = json_encode($notifications);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
    (function () {
        var k = 'tcf_superadmin_theme_v2';
        var t = 'light';
        try {
            var s = localStorage.getItem(k);
            if (s === 'dark' || s === 'light') {
                t = s;
            } else if (localStorage.getItem('tcf_superadmin_theme') === 'dark') {
                t = 'dark';
            }
        } catch (e) {}
        document.documentElement.setAttribute('data-sa-theme', t);
        document.documentElement.style.colorScheme = t;
    })();
    </script>
    <?php
    $tcf_brand_title = ($isSuperAdmin ? 'Super Admin' : 'Administration') . ' — ELITE TCF CANADA';
    $tcf_brand_desc = 'Espace d\'administration ELITE TCF CANADA — gestion utilisateurs, vidéos, sujets et messagerie.';
    include __DIR__ . '/../includes/tcf_brand_head.php';
    ?>
    <title><?php echo htmlspecialchars($tcf_brand_title); ?></title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../Assets/css/sa-theme.css">
    <script src="../Assets/javascript/sa-theme.js"></script>
    <link rel="stylesheet" href="../Assets/css/superAdmin.css">
    <link rel="stylesheet" href="../Assets/css/tcf-brand-logo.css">
    <link rel="stylesheet" href="../Assets/css/sa-staff-chat.css">
    <link rel="stylesheet" href="../Assets/css/sa_subscription_plans.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/profile_panel.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/tcf-responsive-pills.css')); ?>">
    <link rel="stylesheet" href="../Assets/css/admin-mobile-nav.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/tcf-ui-layers.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/tcf-assistant-widget.css')); ?>">
    <link rel="stylesheet" href="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
</head>

<body class="tcf-superadmin-app" data-sa-role="<?php echo $isSuperAdmin ? 'super_admin' : 'admin'; ?>">
<script>
(function () {
    var t = document.documentElement.getAttribute('data-sa-theme') || 'light';
    document.body.setAttribute('data-sa-theme', t);
    document.body.classList.toggle('sa-theme-dark', t === 'dark');
    document.body.classList.toggle('sa-theme-light', t !== 'dark');
})();
</script>
    <?php if (!empty($profile_flash)): ?>
    <div class="tcf-toast tcf-toast--<?php echo htmlspecialchars($profile_flash['type']); ?>" role="status">
        <?php echo htmlspecialchars($profile_flash['message']); ?>
    </div>
    <?php endif; ?>
    <!-- Sidebar -->
    <div class="sidebar" id="saSidebar" aria-label="Navigation administration">
        <button type="button" class="tcf-sa-sidebar-close" id="saSidebarClose" aria-label="Fermer le menu">
            <i class='bx bx-x'></i>
        </button>
        <div class="logo-container">
            <div class="logo">
                <?php echo tcf_brand_logo_img(['class' => 'tcf-brand-logo tcf-brand-logo--admin', 'size' => 32]); ?>
            </div>
            <div class="logo-text">ELITE TCF <span>CANADA</span></div>
        </div>

        <div class="menu-item active" data-target="dashboard">
            <i class='bx bxs-dashboard'></i>
            <span>Tableau de bord</span>
        </div>

        <?php if ($isSuperAdmin): ?>
            <div class="menu-item" data-target="recent-activity">
                <i class='bx bx-pulse'></i>
                <span>Activité récente</span>
            </div>
            <div class="menu-item" data-target="users">
                <i class='bx bxs-user'></i>
                <span>Gestion Utilisateurs</span>
            </div>
            <div class="menu-item" id="site-management-menu">
                <i class='bx bxs-shield'></i>
                <span>Gestion des administrateurs</span>
                <i class='bx bx-chevron-down' style="margin-left: auto;"></i>
            </div>
            <div class="sub-menu" id="site-management-submenu">
                <div class="sub-item" data-target="admins">Membres</div>
            </div>
        <?php endif; ?>

        <div class="menu-item" id="videos-menu">
            <i class='bx bxs-video'></i>
            <span>Gestion Vidéos</span>
            <i class='bx bx-chevron-down' style="margin-left: auto;"></i>
        </div>
        <div class="sub-menu" id="videos-submenu">
            <div class="sub-item" data-target="videos">Vidéos</div>
            <div class="sub-item" data-target="channel-posts">Publications chaîne</div>
            <div class="sub-item" data-target="channel-playlists">Playlists chaîne</div>
            <div class="sub-item" data-target="analytics">Analyse vidéo</div>
            <div class="sub-item" data-target="channel-branding">Paramètres chaîne</div>
        </div>

        <?php if ($isSuperAdmin): ?>
        <div class="menu-item" data-target="testimonials">
            <i class='bx bxs-quote-alt-left'></i>
            <span>Témoignages</span>
        </div>
        <div class="menu-item" id="subscription-menu">
            <i class='bx bx-credit-card'></i>
            <span>Abonnements</span>
            <i class='bx bx-chevron-down' style="margin-left: auto;"></i>
        </div>
        <div class="sub-menu" id="subscription-submenu">
            <div class="sub-item" data-target="subscription-plans">Forfaits</div>
            <div class="sub-item" data-target="subscription-payments">Historique des paiements</div>
            <div class="sub-item" data-target="subscription-revenue">Revenus</div>
        </div>
        <div class="menu-item" data-target="messages">
            <i class='bx bxs-megaphone'></i>
            <span>Gestion des annonces</span>
        </div>
        <?php else: ?>
        <div class="menu-item" data-target="subscription-revenue">
            <i class='bx bx-wallet'></i>
            <span>Revenus</span>
        </div>
        <?php endif; ?>

        <div class="menu-item" data-target="chat">
            <i class='bx bxs-chat'></i>
            <span>Messagerie</span>
        </div>
        <div class="menu-item" id="topics-menu">
            <i class='bx bxs-book'></i>
            <span>Gestion des sujets</span>
            <i class='bx bx-chevron-down' style="margin-left: auto;"></i>
        </div>
        <div class="sub-menu" id="topics-submenu">
            <div class="sub-item" data-target="topics-written">Compréhension écrite</div>
            <div class="sub-item" data-target="topics-oral">Compréhension orale</div>
            <div class="sub-item" data-target="topics-expression">Expression écrite</div>
            <div class="sub-item" data-target="topics-speaking">Expression orale</div>
        </div>

    </div>

    <div class="tcf-sa-sidebar-backdrop" id="saSidebarBackdrop" aria-hidden="true"></div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header admin-dashboard-header">
            <div class="sa-header-start">
                <button type="button" class="tcf-sa-header-menu-btn" id="saMobileMenuBtn" aria-label="Ouvrir le menu" aria-controls="saSidebar">
                    <i class='bx bx-menu' aria-hidden="true"></i>
                </button>
                <div class="page-title" id="sa-page-title"><?php echo $isSuperAdmin ? 'Tableau de bord' : 'Tableau de bord'; ?></div>
            </div>
            <div class="admin-info">
                <button type="button"
                        class="sa-theme-switch"
                        id="sa-theme-toggle"
                        role="switch"
                        aria-checked="false"
                        aria-label="Passer au thème sombre"
                        title="Thème sombre">
                    <span class="sa-theme-switch__track" aria-hidden="true">
                        <span class="sa-theme-switch__thumb"><i class="bx bx-sun"></i></span>
                    </span>
                    <span class="sa-theme-switch__label">Clair</span>
                </button>
                <button type="button"
                        class="sa-nav-assistant-trigger"
                        id="tcfHeaderNavAssistant"
                        aria-label="Assistant IA"
                        aria-controls="tcf-ai-assistant-panel"
                        aria-expanded="false"
                        title="Assistant">
                    <i class='bx bxs-bot' aria-hidden="true"></i>
                </button>
                <a href="#" class="notification-icon sa-nav-notification-icon" id="showNotifications" aria-label="Notifications" title="Notifications">
                    <i class="bx bx-bell"></i>
                    <span class="notification-badge" id="notification-count"<?php echo $tcf_sa_nav_unread > 0 ? '' : ' style="display:none;"'; ?>><?php echo (int) $tcf_sa_nav_unread; ?></span>
                </a>
                <span class="admin-profile-trigger nav-profile-trigger" id="showProfile" title="Mon profil" aria-label="Mon profil" role="button" tabindex="0">
                    <span class="admin-nav-avatar-wrap">
                        <?php if (!empty($tcf_profile_panel_user['avatar_display_url'])): ?>
                            <img src="<?php echo htmlspecialchars($tcf_profile_panel_user['avatar_display_url']); ?>" alt="" class="admin-nav-avatar-img" width="40" height="40" loading="lazy" decoding="async">
                        <?php else: ?>
                            <span class="admin-nav-avatar-fallback"><i class="bx bx-user" aria-hidden="true"></i></span>
                        <?php endif; ?>
                    </span>
                </span>
            </div>
        </div>

        <!-- Dashboard Section -->
        <div id="dashboard" class="content-section active">
            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon users"><i class='bx bxs-user'></i></div>
                    <div class="stat-info">
                        <h3 id="users-count">0</h3>
                        <p>Utilisateurs inscrits</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon visitors"><i class='bx bxs-group'></i></div>
                    <div class="stat-info">
                        <h3 id="visitors-count">0</h3>
                        <p>Visiteurs aujourd’hui (sessions)</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon subs"><i class='bx bxs-crown'></i></div>
                    <div class="stat-info">
                        <h3 id="subs-count">0</h3>
                        <p>Abonnements actifs</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon revenue"><i class='bx bxs-wallet'></i></div>
                    <div class="stat-info">
                        <h3 id="revenue-count">$0</h3>
                        <p>Revenu du mois (passerelle + abonnements)</p>
                    </div>
                </div>
            </div>

            <!-- Traçabilité (données site_visit_logs + users + payments) -->
            <div class="trace-panel">
                <div class="trace-toolbar">
                    <h3 class="trace-title"><i class='bx bx-line-chart'></i> Traçabilité &amp; géographie</h3>
                    <label class="trace-label">Période
                        <select id="trace-range" class="form-control trace-select">
                            <option value="7d">7 derniers jours</option>
                            <option value="30d" selected>30 derniers jours</option>
                            <option value="90d">90 derniers jours</option>
                            <option value="year">Année en cours</option>
                            <option value="all">Depuis toujours (par mois)</option>
                        </select>
                    </label>
                </div>
                <div class="charts-section trace-charts-row">
                    <div class="chart-card trace-chart-wide">
                        <div class="chart-header">
                            <div class="chart-title">Séries temporelles</div>
                            <select id="trace-metric" class="form-control" style="width:auto;">
                                <option value="visits">Visites (pages vues)</option>
                                <option value="users">Inscriptions</option>
                                <option value="subs">Abonnements vendus (paiements)</option>
                                <option value="revenue">Revenus ($)</option>
                            </select>
                        </div>
                        <div class="chart-container trace-chart-tall">
                            <canvas id="traceTimeChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="charts-section">
                    <div class="chart-card">
                        <div class="chart-header">
                            <div class="chart-title">Sources de trafic — visites</div>
                        </div>
                        <div class="chart-container">
                            <canvas id="traceTrafficVisitsChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-card">
                        <div class="chart-header">
                            <div class="chart-title">Sources de trafic — inscriptions</div>
                        </div>
                        <div class="chart-container">
                            <canvas id="traceTrafficSignupsChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="charts-section trace-geo-row">
                    <div class="chart-card trace-map-card">
                        <div class="chart-header trace-geo-header">
                            <div class="chart-title">Carte mondiale</div>
                            <select id="trace-geo-mode" class="form-control trace-geo-select" title="Couche affichée">
                                <option value="visits">Visites par pays</option>
                                <option value="signups">Inscriptions par pays</option>
                            </select>
                        </div>
                        <div id="traceMapGeo" class="trace-map"></div>
                    </div>
                    <div class="chart-card">
                        <div class="chart-header">
                            <div class="chart-title" id="trace-countries-title">Répartition par pays — visites</div>
                        </div>
                        <div class="chart-container trace-chart-tall">
                            <canvas id="traceCountriesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Journal d'activité admin (déplacé depuis le tableau de bord) -->
        <div id="recent-activity" class="content-section" style="display:none;" aria-hidden="true">
            <div class="dashboard-section sa-activity-page">
                <div class="section-header sa-activity-page-head">
                    <div class="sa-activity-page-intro">
                        <div class="section-title">Activité récente</div>
                        <p class="sa-activity-subtitle">
                            Historique des actions enregistrées côté administration — auteur, type, date et heure.
                        </p>
                    </div>
                    <div class="sa-activity-toolbar">
                        <label class="sa-activity-field">
                            <span class="sa-activity-field-label">Type</span>
                            <select id="sa-activity-filter-type" class="form-control">
                                <option value="">Tous les types</option>
                                <option value="user">Utilisateurs</option>
                                <option value="video">Vidéos &amp; chaîne</option>
                                <option value="topic">Sujets</option>
                                <option value="message">Messages &amp; publications</option>
                                <option value="admin">Administrateurs</option>
                                <option value="subscription">Abonnements</option>
                            </select>
                        </label>
                        <label class="sa-activity-field sa-activity-field--grow">
                            <span class="sa-activity-field-label">Recherche</span>
                            <input type="search" id="sa-activity-search" class="form-control" placeholder="Titre, détail, nom ou e-mail…" autocomplete="off">
                        </label>
                        <button type="button" class="btn btn-primary btn-sm sa-activity-refresh-btn" id="sa-activity-refresh" title="Actualiser">
                            <i class="bx bx-refresh" aria-hidden="true"></i> Actualiser
                        </button>
                    </div>
                </div>
                <div class="sa-activity-summary" id="sa-activity-summary" aria-live="polite"></div>
                <div class="sa-activity-feed" id="activity-feed"></div>
            </div>
        </div>

        <!-- Apprenants (rôle user) — super_admin uniquement -->
        <?php if ($isSuperAdmin): ?>
        <div id="users" class="content-section" style="display:none;">
            <div class="dashboard-section">
                <div class="section-header">
                    <div class="section-title">Gestion des Utilisateurs</div>
                    <button type="button" class="btn btn-primary" id="add-user-btn">
                        <i class='bx bx-plus'></i> Ajouter un utilisateur
                    </button>
                </div>

                <div class="table-container">
                    <table id="users-table">
                        <thead>
                            <tr>
                                <th class="sa-th-photo">Photo</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Abonnement</th>
                                <th>Statut</th>
                                <th>Date d'inscription</th>
                                <th>Jours actifs (site)</th>
                                <th>Dernière visite</th>
                                <th class="sa-th-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Comptes admin / super_admin — super_admin, entrée « Membres » -->
        <?php if ($isSuperAdmin): ?>
        <div id="admins" class="content-section" style="display:none;">
            <div class="dashboard-section">
                <div class="section-header">
                    <div class="section-title">Membres</div>
                    <button type="button" class="btn btn-primary" id="add-admin-btn">
                        <i class='bx bx-plus'></i> Ajouter un administrateur
                    </button>
                </div>

                <div class="table-container">
                    <table id="admins-table">
                        <thead>
                            <tr>
                                <th class="sa-th-photo">Photo</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Statut</th>
                                <th>Date d'ajout</th>
                                <th>Dernière connexion</th>
                                <th class="sa-th-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Videos Section -->
        <div id="videos" class="content-section" style="display:none;">
            <div class="dashboard-section sa-videos-showcase">
                <div class="section-header">
                    <div class="section-title">Gestion des Vidéos</div>
                    <button class="btn btn-primary" id="add-video-btn">
                        <i class='bx bx-plus'></i> Ajouter une vidéo
                    </button>
                </div>

                <!-- Formulaire d'ajout/modification de vidéo -->
                <form id="video-form" enctype="multipart/form-data" style="display: none;">
                    <input type="hidden" id="video-edit-id">
                    <div class="form-group">
                        <label class="form-label">Titre de la vidéo</label>
                        <input type="text" class="form-control" id="video-title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="video-description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Miniature</label>
                        <div class="file-upload">
                            <input type="file" id="thumbnail-file" name="thumbnail" accept="image/*">
                            <label for="thumbnail-file" class="upload-label">
                                <i class='bx bx-cloud-upload'></i>
                                <span id="thumbnail-label">Sélectionner une miniature</span>
                            </label>
                        </div>
                        <div id="thumbnail-preview" class="tcf-thumb-preview-wrap">
                            <img id="thumbnail-preview-img" class="tcf-thumb-preview-img" src="" alt="Aperçu de la miniature">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fichier vidéo</label>
                        <div class="file-upload">
                            <input type="file" id="video-file" name="video" accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov,.avi,.mkv,.m4v">
                            <label for="video-file" class="upload-label">
                                <i class='bx bx-cloud-upload'></i>
                                <span id="video-file-label">Sélectionner une vidéo</span>
                            </label>
                        </div>
                        <p style="margin:6px 0 0;font-size:13px;color:#64748b;">Formats : MP4, WebM, MOV, AVI, MKV… Aucune limite de taille dans l’application (seule la config PHP du serveur peut limiter).</p>
                        <div id="video-preview" class="tcf-video-preview-wrap">
                            <video id="video-preview-player" class="tcf-video-preview-player" controls playsinline preload="metadata"></video>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Visibilité</label>
                        <select class="form-control" id="video-visibility" name="visibility" required>
                            <option value="public">Public</option>
                            <option value="private">Privé</option>
                            <option value="premium">Premium</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Playlists (optionnel)</label>
                        <p style="margin:0 0 8px;font-size:13px;color:#64748b;">Cochez les playlists dans lesquelles cette vidéo doit apparaître.</p>
                        <div id="video-playlist-checkboxes" class="tcf-channel-pl-checkboxes" style="max-height:180px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;background:#f8fafc;"></div>
                    </div>
                    <div class="form-buttons">
                        <button type="button" class="btn btn-outline" id="cancel-video-btn">Annuler</button>
                        <button type="submit" class="btn btn-primary" id="video-form-submit">Enregistrer</button>
                    </div>
                </form>

                <!-- Liste des vidéos -->
                <div class="section-header" style="margin-top: 30px;">
                    <div class="section-title">Vidéos publiées</div>
                </div>
                <div class="video-grid" id="videos-grid"></div>

                <div id="admin-video-play-modal" class="tcf-admin-video-modal" aria-hidden="true">
                    <div class="tcf-admin-video-modal__backdrop" data-close-video-modal></div>
                    <div class="tcf-admin-video-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="admin-video-play-title">
                        <button type="button" class="tcf-admin-video-modal__close" data-close-video-modal aria-label="Fermer">&times;</button>
                        <h3 id="admin-video-play-title" class="tcf-admin-video-modal__title"></h3>
                        <video id="admin-video-play-player" controls playsinline preload="none"></video>
                    </div>
                </div>

                <div id="admin-video-comments-modal" class="tcf-admin-video-modal sa-vcmodal" aria-hidden="true">
                    <div class="tcf-admin-video-modal__backdrop" data-close-vcmodal></div>
                    <div class="tcf-admin-video-modal__dialog sa-vcmodal__dialog" role="dialog" aria-modal="true" aria-labelledby="admin-vcm-title">
                        <button type="button" class="tcf-admin-video-modal__close" data-close-vcmodal aria-label="Fermer">&times;</button>
                        <h3 id="admin-vcm-title" class="tcf-admin-video-modal__title"></h3>
                        <video id="admin-vcm-player" controls playsinline preload="metadata"></video>
                        <div id="admin-vcm-threads" class="sa-vcmodal__threads"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Playlists chaîne -->
        <div id="channel-playlists" class="content-section" style="display:none;">
            <div class="dashboard-section">
                <div class="section-header">
                    <div class="section-title">Playlists (chaîne type YouTube)</div>
                    <button type="button" class="btn btn-primary" id="channel-playlist-add-btn">
                        <i class='bx bx-plus'></i> Nouvelle playlist
                    </button>
                </div>
                <p style="color:#64748b;font-size:14px;margin-bottom:16px;">Visibilité publique ou privée. Les vidéos incluses sont choisies ici ou depuis le formulaire vidéo.</p>
                <form id="channel-playlist-form" style="display:none;">
                    <input type="hidden" id="channel-playlist-edit-id" value="">
                    <div class="form-group">
                        <label class="form-label">Titre</label>
                        <input type="text" class="form-control" id="channel-playlist-title" maxlength="255" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="channel-playlist-description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Visibilité</label>
                        <select class="form-control" id="channel-playlist-visibility">
                            <option value="public">Public</option>
                            <option value="private">Privé</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Vidéos dans cette playlist</label>
                        <div id="channel-playlist-video-checkboxes" style="max-height:220px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;background:#f8fafc;"></div>
                    </div>
                    <div class="form-buttons">
                        <button type="button" class="btn btn-outline" id="channel-playlist-cancel-btn">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
                <div class="section-header" style="margin-top:24px;">
                    <div class="section-title">Playlists</div>
                </div>
                <div style="overflow-x:auto;">
                    <table class="table" style="width:100%;border-collapse:collapse;font-size:13px;">
                        <thead>
                            <tr style="border-bottom:1px solid #e2e8f0;text-align:left;">
                                <th style="padding:8px;">Titre</th>
                                <th style="padding:8px;">Visibilité</th>
                                <th style="padding:8px;">Vidéos</th>
                                <th style="padding:8px;">Créée</th>
                                <th class="sa-th-actions" style="padding:8px;width:160px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="channel-playlists-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Publications chaîne -->
        <div id="channel-posts" class="content-section" style="display:none;">
            <div class="dashboard-section">
                <div class="section-header">
                    <div class="section-title">Publications (fil d’actualité)</div>
                    <button type="button" class="btn btn-primary" id="channel-post-add-btn" aria-label="Nouvelle publication" title="Nouvelle publication">
                        <i class="bx bx-plus" aria-hidden="true"></i>
                    </button>
                </div>
                <form id="channel-post-form" style="display:none;" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="channel-post-edit-id" value="">
                    <div class="form-group">
                        <label class="form-label">Type de publication</label>
                        <select class="form-control" name="post_type" id="channel-post-type">
                            <option value="text">Texte</option>
                            <option value="image">Texte + image</option>
                            <option value="poll">Sondage</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Titre (optionnel)</label>
                        <input type="text" class="form-control" name="title" id="channel-post-title" maxlength="255">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Texte / légende / question</label>
                        <textarea class="form-control" name="body" id="channel-post-body" rows="5" maxlength="8000"></textarea>
                    </div>
                    <div class="form-group" id="channel-post-image-wrap" style="display:none;">
                        <label class="form-label">Image (JPG, PNG, WebP, GIF — max 5 Mo)</label>
                        <input type="file" class="form-control" name="image" id="channel-post-image" accept="image/jpeg,image/png,image/webp,image/gif">
                        <img id="channel-post-image-preview" alt="" style="display:none;max-height:140px;margin-top:8px;border-radius:8px;border:1px solid #e2e8f0;">
                        <label style="display:block;margin-top:8px;font-size:13px;font-weight:400;">
                            <input type="checkbox" name="remove_image" id="channel-post-remove-image" value="1">
                            Retirer l’image actuelle
                        </label>
                    </div>
                    <div class="form-group" id="channel-post-poll-wrap" style="display:none;">
                        <label class="form-label">Options du sondage (une par ligne, 2 à 10)</label>
                        <textarea class="form-control" name="poll_options" id="channel-post-poll-options" rows="4" maxlength="4000"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Vidéo liée (optionnel)</label>
                        <select class="form-control" name="video_id" id="channel-post-video-id">
                            <option value="">— Aucune —</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Visibilité</label>
                        <select class="form-control" name="visibility" id="channel-post-visibility">
                            <option value="public">Public</option>
                            <option value="private">Privé</option>
                        </select>
                    </div>
                    <div class="form-buttons">
                        <button type="button" class="btn btn-outline" id="channel-post-cancel-btn">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
                <div class="section-header" style="margin-top:24px;">
                    <div class="section-title">Publications</div>
                </div>
                <div style="overflow-x:auto;">
                    <table class="table" style="width:100%;border-collapse:collapse;font-size:13px;">
                        <thead>
                            <tr style="border-bottom:1px solid #e2e8f0;text-align:left;">
                                <th style="padding:8px;">Date</th>
                                <th style="padding:8px;">Type</th>
                                <th style="padding:8px;">Titre / extrait</th>
                                <th style="padding:8px;">Visibilité</th>
                                <th style="padding:8px;">Vidéo</th>
                                <th class="sa-th-actions" style="padding:8px;width:180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="channel-posts-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Identité chaîne (page Vidéos) -->
        <div id="channel-branding" class="content-section" style="display:none;">
            <div class="dashboard-section sa-branding-wrap">
                <div class="section-header">
                    <div class="section-title">Identité de la chaîne</div>
                </div>
                <p class="sa-branding-lead">Personnalisez l’en-tête public de la page <strong>Vidéos</strong> (style chaîne YouTube) : logo rond, bannière, titre et accroche.</p>
                <div class="sa-branding-grid">
                    <div class="sa-branding-form-card">
                        <form id="channel-branding-form" enctype="multipart/form-data" autocomplete="off">
                            <input type="hidden" name="remove_logo" id="channel-branding-remove-logo" value="0">
                            <input type="hidden" name="remove_banner" id="channel-branding-remove-banner" value="0">
                            <div class="form-group">
                                <label class="form-label" for="channel-branding-title">Titre de la chaîne</label>
                                <input type="text" class="form-control" id="channel-branding-title" name="title" maxlength="255" placeholder="<?php echo htmlspecialchars($tcf_brand_default_name, ENT_QUOTES, 'UTF-8'); ?>">
                                <p class="sa-branding-hint">Laissez vide pour utiliser le nom du site : <strong><?php echo htmlspecialchars($tcf_brand_default_name); ?></strong></p>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="channel-branding-tagline">Description / accroche</label>
                                <textarea class="form-control" id="channel-branding-tagline" name="tagline" rows="3" maxlength="800" placeholder="<?php echo htmlspecialchars((function_exists('mb_substr') ? mb_substr($tcf_brand_default_tag, 0, 80) : substr($tcf_brand_default_tag, 0, 80)) . '…', ENT_QUOTES, 'UTF-8'); ?>"></textarea>
                                <p class="sa-branding-hint">Texte sous le titre. Laissez vide pour le texte par défaut.</p>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="channel-branding-logo">Photo de profil — recadrage carré (512×512 px à l’enregistrement, affichée ~112 px)</label>
                                <input type="file" class="form-control" id="channel-branding-logo" name="logo" accept="image/jpeg,image/png,image/webp,image/gif">
                                <div class="sa-branding-file-row">
                                    <button type="button" class="btn btn-outline btn-sm" id="channel-branding-clear-logo">Retirer le logo personnalisé</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="channel-branding-banner">Bannière — recadrage 4:1 (1600×400 px à l’enregistrement)</label>
                                <input type="file" class="form-control" id="channel-branding-banner" name="banner" accept="image/jpeg,image/png,image/webp,image/gif">
                                <div class="sa-branding-file-row">
                                    <button type="button" class="btn btn-outline btn-sm" id="channel-branding-clear-banner">Retirer la bannière personnalisée</button>
                                </div>
                            </div>
                            <div class="form-buttons">
                                <button type="submit" class="btn btn-primary" id="channel-branding-save-btn"><i class="bx bx-save" style="vertical-align:middle"></i> Enregistrer</button>
                            </div>
                        </form>
                    </div>
                    <div class="sa-branding-preview-card" aria-hidden="true">
                        <div class="sa-branding-preview-label">Aperçu</div>
                        <div class="sa-branding-preview-mock">
                            <div class="sa-branding-preview-cover" id="sa-branding-preview-cover"></div>
                            <div class="sa-branding-preview-body">
                                <img class="sa-branding-preview-avatar" id="sa-branding-preview-avatar" src="" alt="">
                                <div class="sa-branding-preview-text">
                                    <h3 id="sa-branding-preview-title"><i class="bx bxl-youtube"></i> <span id="sa-branding-preview-title-txt"></span></h3>
                                    <p id="sa-branding-preview-tag"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="sa-channel-crop-modal" class="sa-channel-crop-modal" hidden aria-hidden="true">
            <div class="sa-channel-crop-modal__backdrop" id="sa-channel-crop-backdrop"></div>
            <div class="sa-channel-crop-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="sa-channel-crop-title">
                <h2 id="sa-channel-crop-title" class="sa-channel-crop-modal__title">Recadrer l’image</h2>
                <p class="sa-channel-crop-modal__hint" id="sa-channel-crop-hint"></p>
                <div class="sa-channel-crop-wrap">
                    <img id="sa-channel-crop-img" src="" alt="Crop preview" style="max-width:100%;">
                </div>
                <div class="form-buttons" style="margin-top: 15px; text-align: right;">
                    <button type="button" class="btn btn-secondary" id="sa-channel-crop-cancel">Annuler</button>
                    <button type="button" class="btn btn-primary" id="sa-channel-crop-apply">Appliquer</button>
                </div>
            </div>
        </div>

        <!-- Témoignages -->
        <div id="testimonials" class="content-section" style="display:none;">
            <div class="dashboard-section">
                <div class="section-header">
                    <div class="section-title">Témoignages</div>
                    <button type="button" class="btn btn-primary" id="sa-testi-refresh-btn">
                        <i class="bx bx-refresh"></i> Actualiser
                    </button>
                </div>
                <p style="color:var(--sa-muted,#64748b);font-size:14px;margin-bottom:20px;">
                    Gérez les avis publiés sur la page d'accueil. Cliquez sur un témoignage pour voir le contenu complet.
                </p>

                <!-- Stats rapides -->
                <div class="sa-testi-stats" id="sa-testi-stats">
                    <div class="sa-testi-stat-card">
                        <i class="bx bxs-comment-detail"></i>
                        <div>
                            <span class="sa-testi-stat-val" id="sa-testi-count">—</span>
                            <span class="sa-testi-stat-lbl">Total</span>
                        </div>
                    </div>
                    <div class="sa-testi-stat-card">
                        <i class="bx bxs-star" style="color:#f59e0b;"></i>
                        <div>
                            <span class="sa-testi-stat-val" id="sa-testi-avg">—</span>
                            <span class="sa-testi-stat-lbl">Note moyenne</span>
                        </div>
                    </div>
                    <div class="sa-testi-stat-card">
                        <i class="bx bxs-award" style="color:#10b981;"></i>
                        <div>
                            <span class="sa-testi-stat-val" id="sa-testi-five">—</span>
                            <span class="sa-testi-stat-lbl">5 étoiles</span>
                        </div>
                    </div>
                </div>

                <!-- Barre recherche + filtre -->
                <div class="sa-testi-toolbar">
                    <div class="sa-testi-search-wrap">
                        <i class="bx bx-search"></i>
                        <input type="search" id="sa-testi-search" placeholder="Rechercher auteur ou contenu…" autocomplete="off">
                    </div>
                    <select id="sa-testi-filter-rating" class="sa-testi-select">
                        <option value="">Toutes les notes</option>
                        <option value="5">⭐⭐⭐⭐⭐ 5 étoiles</option>
                        <option value="4">⭐⭐⭐⭐ 4 étoiles</option>
                        <option value="3">⭐⭐⭐ 3 étoiles</option>
                        <option value="2">⭐⭐ 2 étoiles</option>
                        <option value="1">⭐ 1 étoile</option>
                        <option value="0">Sans note</option>
                    </select>
                </div>

                <!-- Grille de cartes -->
                <div class="sa-testi-grid" id="sa-testi-grid">
                    <div class="sa-testi-loading">
                        <i class="bx bx-loader-alt bx-spin"></i> Chargement…
                    </div>
                </div>

                <!-- Compteur résultats -->
                <p class="sa-testi-result-count" id="sa-testi-result-count"></p>
            </div>
        </div>

        <!-- Modal détail témoignage -->
        <div class="sa-testi-modal-overlay" id="sa-testi-modal" role="dialog" aria-modal="true" aria-labelledby="sa-testi-modal-title" hidden>
            <div class="sa-testi-modal-box">
                <button class="sa-testi-modal-close" id="sa-testi-modal-close" aria-label="Fermer">
                    <i class="bx bx-x"></i>
                </button>
                
                <!-- Mode affichage -->
                <div id="sa-testi-view-mode">
                    <div class="sa-testi-modal-head">
                        <div class="sa-testi-modal-avatar" id="sa-testi-modal-avatar">A</div>
                        <div>
                            <h3 class="sa-testi-modal-author" id="sa-testi-modal-title"></h3>
                            <div class="sa-testi-modal-stars" id="sa-testi-modal-stars"></div>
                            <span class="sa-testi-modal-date" id="sa-testi-modal-date"></span>
                        </div>
                    </div>
                    <blockquote class="sa-testi-modal-body" id="sa-testi-modal-body"></blockquote>
                    <div class="sa-testi-modal-actions">
                        <button type="button" class="btn btn-outline" id="sa-testi-modal-close2">Fermer</button>
                        <button type="button" class="btn btn-primary" id="sa-testi-modal-edit">
                            <i class="bx bx-edit"></i> Modifier
                        </button>
                        <button type="button" class="btn btn-danger" id="sa-testi-modal-delete">
                            <i class="bx bx-trash"></i> Supprimer ce témoignage
                        </button>
                    </div>
                </div>
                
                <!-- Mode édition -->
                <div id="sa-testi-edit-mode" style="display:none;">
                    <h3 class="sa-testi-modal-author">Modifier le témoignage</h3>
                    <form id="sa-testi-edit-form">
                        <input type="hidden" id="sa-testi-edit-id">
                        <div class="form-group">
                            <label class="form-label">Nom de l'auteur</label>
                            <input type="text" class="form-control" id="sa-testi-edit-author" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Note</label>
                            <select class="form-control" id="sa-testi-edit-rating">
                                <option value="0">Sans note</option>
                                <option value="1">⭐ 1 étoile</option>
                                <option value="2">⭐⭐ 2 étoiles</option>
                                <option value="3">⭐⭐⭐ 3 étoiles</option>
                                <option value="4">⭐⭐⭐⭐ 4 étoiles</option>
                                <option value="5">⭐⭐⭐⭐⭐ 5 étoiles</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contenu du témoignage</label>
                            <textarea class="form-control" id="sa-testi-edit-content" rows="4" required></textarea>
                        </div>
                        <div class="sa-testi-modal-actions">
                            <button type="button" class="btn btn-outline" id="sa-testi-edit-cancel">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <!-- Abonnements : forfaits -->
        <div id="subscription-plans" class="content-section" style="display:none;">
            <div class="dashboard-section">
                <div class="section-header">
                    <div class="section-title">Forfaits d’abonnement</div>
                </div>
                <p class="sa-sub-pro-lead">
                    Définissez les offres visibles sur la page Abonnement : libellés, tarifs, durée d’accès et liste d’avantages.
                    Les membres voient les changements dès enregistrement.
                </p>
                <div class="sa-sub-platform-toggle" id="sa-sub-platform-toggle" role="region" aria-label="Mode abonnements plateforme">
                    <div class="sa-sub-platform-toggle__info">
                        <strong>Abonnements plateforme</strong>
                        <p id="sa-sub-platform-toggle-desc">Chargement…</p>
                    </div>
                    <button type="button" class="btn btn-secondary" id="sa-sub-platform-toggle-btn">
                        <i class="bx bx-power-off"></i> <span id="sa-sub-platform-toggle-label">…</span>
                    </button>
                </div>
                <div class="sa-sub-pro-toolbar">
                    <button type="button" class="btn btn-primary" id="sa-sub-add-plan-btn">
                        <i class="bx bx-plus"></i> Ajouter un forfait
                    </button>
                </div>
                <div class="sa-sub-pro-plans-wrap" role="region" aria-label="Édition des cartes forfait">
                    <div id="sa-plan-catalog-grid" class="sa-plan-catalog-grid">
                        <div class="sa-plan-catalog-loading">Chargement…</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historique des paiements -->
        <div id="subscription-payments" class="content-section" style="display:none;">
            <div class="dashboard-section">
                <div class="section-header">
                    <div class="section-title">Historique des paiements</div>
                </div>
                <p class="sa-sub-pro-lead">
                    Transactions enregistrées lors des souscriptions depuis la page Abonnement.
                </p>
                <div class="table-container" style="overflow-x:auto;">
                    <table class="table" id="sa-subscription-payments-table" style="width:100%;min-width:720px;">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Membre</th>
                                <th>E-mail</th>
                                <th>Formule</th>
                                <th>Montant</th>
                                <th>Moyen</th>
                            </tr>
                        </thead>
                        <tbody id="sa-subscription-payments-tbody">
                            <tr><td colspan="6" style="padding:12px;color:var(--sa-muted);">Chargement…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Synthèse revenus -->
        <div id="subscription-revenue" class="content-section" style="display:none;">
            <div class="dashboard-section">
                <div class="section-header">
                    <div class="section-title">Synthèse des revenus</div>
                </div>
                <p class="sa-sub-pro-lead">
                    Indicateurs agrégés à partir des paiements enregistrés — utiles pour suivre l’activité sur l’offre d’abonnement.
                </p>
                <div class="stats-container" style="margin-bottom:0;">
                    <div class="stat-card">
                        <div class="stat-icon revenue"><i class="bx bx-dollar-circle"></i></div>
                        <div class="stat-info">
                            <h3 id="sa-subrev-total">$0</h3>
                            <p>Revenus cumulés</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon subs"><i class="bx bx-calendar"></i></div>
                        <div class="stat-info">
                            <h3 id="sa-subrev-month">$0</h3>
                            <p>Revenu du mois en cours</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon visitors"><i class="bx bx-receipt"></i></div>
                        <div class="stat-info">
                            <h3 id="sa-subrev-count">0</h3>
                            <p>Nombre de transactions</p>
                        </div>
                    </div>
                </div>
                <div class="chart-card sa-subrev-chart-card" style="margin-top:1.5rem;">
                    <div class="chart-header">
                        <div class="chart-title">Évolution des revenus (12 derniers mois)</div>
                    </div>
                    <div class="chart-container" style="min-height:280px;">
                        <canvas id="sa-subrev-revenue-chart" aria-label="Graphique des revenus par mois"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages Section -->
        <div id="messages" class="content-section" style="display:none;">
            <div class="dashboard-section">
                <div class="section-header">
                    <div class="section-title">Messages Communautaires</div>
                    <button class="btn btn-primary" id="add-message-btn">
                        <i class='bx bx-plus'></i> Nouveau message
                    </button>
                </div>

                <!-- Formulaire de message -->
                <form id="message-form" style="display: none;">
                    <input type="hidden" id="message-edit-id">
                    <div class="form-group">
                        <label class="form-label">Sujet du message</label>
                        <input type="text" class="form-control" id="message-subject" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contenu du message</label>
                        <textarea class="form-control" id="message-content" rows="5" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Destinataires</label>
                        <select class="form-control" id="message-recipients" required>
                            <option value="all">Tous les utilisateurs</option>
                            <option value="active">Utilisateurs actifs</option>
                            <option value="premium">Utilisateurs premium</option>
                            <option value="new">Nouveaux utilisateurs</option>
                            <option value="admins">Administrateurs</option>
                        </select>
                    </div>
                    <div class="form-buttons">
                        <button type="button" class="btn btn-outline" id="cancel-message-btn">Annuler</button>
                        <button type="submit" class="btn btn-primary">Envoyer</button>
                    </div>
                </form>

                <!-- Liste des messages -->
                <div class="section-header" style="margin-top: 30px;">
                    <div class="section-title">Messages envoyés</div>
                </div>
                <div id="messages-container"></div>
            </div>
        </div>

        <!-- Messagerie équipe (admin / super_admin) -->
        <div id="chat" class="content-section" style="display:none;" data-chat-me-id="<?php echo (int) ($_SESSION['user_id'] ?? 0); ?>">
            <div class="dashboard-section">
                <div class="section-header">
                    <div class="section-title">Messagerie équipe</div>
                </div>
                <div class="ssc-app" id="ssc-app">
                    <aside class="ssc-sidebar">
                        <div class="ssc-sidebar-head">
                            <div class="ssc-sidebar-title-row">
                                <h2>Conversations</h2>
                                <span class="ssc-online-pill" id="ssc-online-count">0 en ligne</span>
                            </div>
                            <p class="ssc-sidebar-sub">Échanges réservés aux administrateurs et super-administrateurs — les apprenants n’y ont pas accès.</p>
                            <div class="ssc-toolbar">
                                <button type="button" class="btn btn-primary btn-sm" id="ssc-new-dm"><i class="bx bx-message-add"></i> Nouveau message</button>
                                <button type="button" class="btn btn-outline btn-sm" id="ssc-new-group"><i class="bx bx-group"></i> Groupe</button>
                                <button type="button" class="btn btn-outline btn-sm" id="ssc-refresh"><i class="bx bx-refresh"></i></button>
                                <button type="button" class="btn btn-outline btn-sm" id="ssc-toggle-presence" data-visible="1"><i class="bx bx-wifi"></i> Présence</button>
                            </div>
                            <div class="ssc-search-wrap">
                                <i class="bx bx-search" aria-hidden="true"></i>
                                <input type="search" id="ssc-search" class="ssc-search-input" placeholder="Rechercher…" autocomplete="off" aria-label="Rechercher une conversation">
                            </div>
                        </div>
                        <div class="ssc-thread-list" id="ssc-thread-list"></div>
                    </aside>
                    <main class="ssc-main">
                        <header class="ssc-thread-head">
                            <button type="button" class="ssc-back-btn" id="ssc-back" aria-label="Retour"><i class="bx bx-arrow-back"></i></button>
                            <div class="ssc-head-avatar" id="ssc-head-avatar"><i class="bx bx-message-dots"></i></div>
                            <div class="ssc-head-info">
                                <div class="ssc-head-name" id="ssc-head-name">Messagerie équipe</div>
                                <div class="ssc-head-status" id="ssc-head-status">Administrateurs et super-administrateurs</div>
                            </div>
                            <button type="button" class="btn btn-outline btn-sm" id="ssc-group-settings-btn" hidden><i class="bx bx-cog"></i> Groupe</button>
                        </header>
                        <div class="ssc-messages" id="ssc-messages">
                            <div class="ssc-empty-state"><i class="bx bxs-chat"></i><p>Sélectionnez une conversation pour échanger avec l’équipe.</p></div>
                        </div>
                        <footer class="ssc-composer" id="ssc-composer">
                            <div class="ssc-edit-banner" id="ssc-edit-banner"><span>Modification du message</span><button type="button" class="ssc-edit-cancel" id="ssc-edit-cancel">Annuler</button></div>
                            <div class="ssc-emoji-pop" id="ssc-emoji-pop" hidden></div>
                            <div class="ssc-composer-row">
                                <div class="ssc-composer-tools">
                                    <button type="button" class="ssc-emoji-btn" id="ssc-emoji-btn" title="Emoji">😊</button>
                                </div>
                                <textarea class="ssc-composer-input" id="ssc-composer-input" rows="1" maxlength="4000" placeholder="Écrivez votre message…"></textarea>
                                <button type="button" class="ssc-send-btn" id="ssc-send-btn" aria-label="Envoyer"><i class="bx bxs-send"></i></button>
                            </div>
                        </footer>
                    </main>
                </div>
            </div>
        </div>

        <div class="modal" id="ssc-new-dm-modal" aria-hidden="true" role="dialog">
            <div class="modal-content">
                <button type="button" class="modal-close" data-ssc-modal-close="1" aria-label="Fermer">&times;</button>
                <h2 class="modal-title">Nouveau message</h2>
                <p class="ssc-staff-modal-lead">Choisissez un administrateur ou super-administrateur pour démarrer une conversation privée.</p>
                <input type="search" id="ssc-dm-search" class="form-control" placeholder="Rechercher par nom ou e-mail…" autocomplete="off" style="margin-bottom:0.75rem;">
                <div class="ssc-staff-picker" id="ssc-dm-staff-list"></div>
            </div>
        </div>

        <div class="modal sa-new-group-modal" id="sa-new-group-modal" aria-hidden="true" role="dialog" aria-labelledby="sa-new-group-title">
            <div class="modal-content sa-new-group-modal__content">
                <button type="button" class="modal-close" data-ssc-modal-close="1" aria-label="Fermer">&times;</button>
                <h2 class="modal-title" id="sa-new-group-title">Nouveau groupe</h2>
                <p class="ssc-staff-modal-lead">Créez un groupe et ajoutez des administrateurs ou super-administrateurs. Les apprenants ne peuvent pas y accéder.</p>
                <div class="sa-new-group-grid">
                    <div class="sa-new-group-col sa-new-group-col--settings">
                        <label class="form-label" for="sa-new-group-name">Nom du groupe</label>
                        <input type="text" id="sa-new-group-name" class="form-control" maxlength="180" placeholder="Ex. Groupe TCF avril" autocomplete="off">
                        <label class="form-label" for="sa-new-group-photo">Photo du groupe (optionnel)</label>
                        <input type="file" id="sa-new-group-photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <label class="sa-new-group-check">
                            <input type="checkbox" id="sa-new-group-admins-only">
                            <span>Seuls les administrateurs peuvent écrire (les membres lisent seulement)</span>
                        </label>
                    </div>
                    <div class="sa-new-group-col sa-new-group-col--members">
                        <label class="form-label" for="sa-new-group-email-input">Ajouter par e-mail</label>
                        <div class="sa-new-group-email-row">
                            <input type="email" id="sa-new-group-email-input" class="form-control" placeholder="membre@email.com" autocomplete="off">
                            <button type="button" class="btn btn-outline" id="sa-new-group-email-add">Ajouter</button>
                        </div>
                        <label class="form-label" for="sa-new-group-user-search">Rechercher un membre du staff</label>
                        <input type="search" id="sa-new-group-user-search" class="form-control" placeholder="Nom ou e-mail…" autocomplete="off">
                        <div id="sa-new-group-user-cards" class="sa-new-group-user-cards" role="list"></div>
                        <div class="sa-new-group-selected">
                            <span class="sa-new-group-selected-label">Membres sélectionnés</span>
                            <div id="sa-new-group-chips" class="sa-new-group-chips"></div>
                        </div>
                    </div>
                </div>
                <div class="form-buttons" style="display:flex;gap:0.5rem;justify-content:flex-end;flex-wrap:wrap;">
                    <button type="button" class="btn btn-outline" data-ssc-modal-close="1">Annuler</button>
                    <button type="button" class="btn btn-primary" id="sa-new-group-submit"><i class="bx bx-check"></i> Créer le groupe</button>
                </div>
            </div>
        </div>

        <div class="modal" id="sa-group-settings-modal" aria-hidden="true">
            <div class="modal-content">
                <button type="button" class="modal-close" data-ssc-modal-close="1" aria-label="Fermer">&times;</button>
                <h2 class="modal-title">Paramètres du groupe</h2>
                <input type="hidden" id="sa-group-settings-thread-id" value="">
                <div class="form-group">
                    <label class="form-label" for="sa-group-settings-title">Nom du groupe</label>
                    <input type="text" class="form-control" id="sa-group-settings-title" maxlength="180" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Membres</label>
                    <div class="ssc-group-members-list" id="ssc-group-members-list"></div>
                    <label class="form-label" for="ssc-group-add-search" style="margin-top:0.75rem;">Ajouter un membre</label>
                    <input type="search" id="ssc-group-add-search" class="form-control" placeholder="Rechercher un administrateur…" autocomplete="off">
                    <div class="ssc-staff-picker" id="ssc-group-add-list" style="margin-top:0.5rem;max-height:140px;"></div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="sa-group-settings-avatar">Photo du groupe</label>
                    <input type="file" class="form-control" id="sa-group-settings-avatar" accept="image/jpeg,image/png,image/webp">
                    <button type="button" class="btn btn-outline btn-sm" id="sa-group-settings-remove-avatar" style="margin-top:0.5rem;">Retirer la photo</button>
                </div>
                <div class="form-group">
                    <label class="form-label" style="display:flex;align-items:flex-start;gap:0.5rem;cursor:pointer;font-weight:500;">
                        <input type="checkbox" id="sa-group-settings-admins-only" style="margin-top:0.2rem;">
                        <span>Seuls les administrateurs peuvent envoyer des messages (les membres lisent seulement).</span>
                    </label>
                </div>
                <div class="form-buttons" style="display:flex;gap:0.5rem;justify-content:flex-end;flex-wrap:wrap;">
                    <button type="button" class="btn btn-outline" data-ssc-modal-close="1">Annuler</button>
                    <button type="button" class="btn btn-primary" id="sa-group-settings-save">Enregistrer</button>
                </div>
            </div>
        </div>

        <!-- Topics Section -->
        <div id="topics-section" class="content-section" style="display:none;">
            <div class="dashboard-section">
                <div class="section-header">
                    <div class="section-title" id="topics-section-title">Gestion des Sujets - Compréhension Écrite</div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                        <button class="btn btn-primary" id="add-topic-btn">
                            <i class='bx bx-plus'></i> Ajouter un sujet
                        </button>
                        <button type="button" class="btn btn-primary" id="topic-save-top-btn" style="display:none;">
                            Enregistrer l'épreuve
                        </button>
                        <button type="button" class="btn btn-outline" id="topic-cancel-top-btn" style="display:none;">
                            Annuler
                        </button>
                    </div>
                </div>

                <!-- Ancien module table `topics` (saisie manuelle titre / visibilité — fichier optionnel) -->
                <form id="topic-form" enctype="multipart/form-data" style="display: none;">
                    <input type="hidden" id="topic-edit-id">
                    <p style="color:#64748b;font-size:0.9rem;margin-bottom:12px;">
                        Réservé à d’anciens enregistrements. Pour les épreuves TCF, utilisez les blocs <strong>Compréhension / Expression</strong> ci-dessous : tout se saisit à la main, question par question.
                    </p>
                    <div class="form-group">
                        <label class="form-label">Titre du sujet</label>
                        <input type="text" class="form-control" id="topic-title" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type de sujet</label>
                        <input type="text" class="form-control" id="topic-type" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Visibilité</label>
                        <select class="form-control" id="topic-visibility">
                            <option value="gratuit">Gratuit</option>
                            <option value="premium">Premium</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fichier joint (optionnel)</label>
                        <div class="file-upload">
                            <input type="file" id="json-file" name="json_file" accept=".json,application/json">
                            <label for="json-file" class="upload-label">
                                <i class='bx bx-cloud-upload'></i>
                                <span id="json-file-label">Aucun fichier requis</span>
                            </label>
                        </div>
                        <small style="color:#64748b;">Vous pouvez enregistrer sans fichier.</small>
                    </div>
                    <div class="form-buttons">
                        <button type="button" class="btn btn-outline" id="cancel-topic-btn">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>

                <!-- Expression écrite (BDD) — saisie manuelle des combinaisons / tâches -->
                <div id="ee-admin-manager" style="display:none; margin-top: 12px;">
                    <div class="section-title" style="margin-bottom:8px;font-size:1.05rem;">Expression écrite — publication manuelle</div>
                    <p style="color:#64748b;font-size:0.9rem;margin:0 0 12px;">Ajoutez des combinaisons et les consignes de chaque tâche directement dans le formulaire (sans fichier JSON).</p>
                    <form id="ee-exam-form" style="display:none;">
                        <input type="hidden" id="ee-exam-id">
                        <div class="form-group">
                            <label class="form-label">Titre de l'épreuve</label>
                            <input type="text" class="form-control" id="ee-exam-title" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sous-titre (optionnel)</label>
                            <input type="text" class="form-control" id="ee-exam-subtitle">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Visibilité de l'épreuve</label>
                            <select class="form-control" id="ee-exam-visibility">
                                <option value="gratuit">Gratuit</option>
                                <option value="premium">Premium</option>
                            </select>
                            <small style="color:#64748b;">Auto: les 3 épreuves écrites les plus récentes restent gratuites par défaut; vous pouvez forcer Premium.</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="display:flex;align-items:center;gap:.5rem;">
                                <input type="checkbox" id="ee-exam-published" checked>
                                <span>Publier immédiatement</span>
                            </label>
                        </div>
                        <div id="ee-combos-wrap"></div>
                        <div class="form-buttons" style="justify-content:flex-start;gap:8px;">
                            <button type="button" class="btn btn-outline" id="ee-add-combo-btn"><i class='bx bx-plus'></i> Ajouter une combinaison</button>
                        </div>
                        <div class="form-buttons">
                            <button type="button" class="btn btn-outline" id="ee-cancel-btn">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer l'épreuve</button>
                        </div>
                    </form>

                    <div class="dashboard-section" style="margin-top:16px;">
                        <div class="section-header">
                            <div class="section-title">Consignes Expression Écrite</div>
                            <button type="button" class="btn btn-primary" id="ee-open-consignes-btn"><i class='bx bx-edit'></i> Consigne</button>
                        </div>
                        <form id="ee-consignes-bundle-form" style="display:none;">
                            <div class="form-group">
                                <label class="form-label">Consigne Tâche 1</label>
                                <textarea class="form-control" id="ee-consigne-tache1" rows="4" required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Consigne Tâche 2</label>
                                <textarea class="form-control" id="ee-consigne-tache2" rows="4" required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Consigne Tâche 3</label>
                                <textarea class="form-control" id="ee-consigne-tache3" rows="4" required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Statut</label>
                                <select class="form-control" id="ee-consigne-status">
                                    <option value="1">Public</option>
                                    <option value="0">Brouillon</option>
                                </select>
                            </div>
                            <div class="form-buttons">
                                <button type="button" class="btn btn-outline" id="ee-consigne-cancel-btn">Annuler</button>
                                <button type="submit" class="btn btn-primary" id="ee-consigne-submit-btn">Publier</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Compréhension écrite — quiz BDD -->
                <div id="ce-admin-manager" style="display:none; margin-top: 12px;">
                    <div class="section-title" style="margin-bottom:8px;font-size:1.05rem;">Compréhension écrite</div>
                    <form id="ce-exam-form" style="display:none;">
                        <input type="hidden" id="ce-exam-id">
                        <div class="form-group">
                            <label class="form-label">Titre de l'épreuve</label>
                            <input type="text" class="form-control" id="ce-exam-title" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Visibilité</label>
                            <select class="form-control" id="ce-exam-visibility">
                                <option value="gratuit">Gratuit</option>
                                <option value="premium">Premium</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="display:flex;align-items:center;gap:.5rem;">
                                <input type="checkbox" id="ce-exam-published" checked>
                                <span>Publier immédiatement</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Durée du test (minutes)</label>
                            <input type="number" class="form-control" id="ce-duration-minutes" value="60" min="1" max="180">
                        </div>
                        <div id="ce-questions-wrap"></div>
                        <div class="form-buttons" style="justify-content:flex-start;gap:8px;">
                            <button type="button" class="btn btn-outline" id="ce-add-question-btn"><i class='bx bx-plus'></i> Ajouter une question</button>
                        </div>
                        <div class="form-buttons">
                            <button type="button" class="btn btn-outline" id="ce-cancel-btn">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer l'épreuve</button>
                        </div>
                    </form>

                    <form id="ce-exam-json-form" style="display:none;">
                        <input type="hidden" id="ce-json-exam-id">
                        <div class="form-group">
                            <label class="form-label">Titre de l'épreuve</label>
                            <input type="text" class="form-control" id="ce-json-exam-title" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Visibilité</label>
                            <select class="form-control" id="ce-json-exam-visibility">
                                <option value="gratuit">Gratuit</option>
                                <option value="premium">Premium</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="display:flex;align-items:center;gap:.5rem;">
                                <input type="checkbox" id="ce-json-exam-published" checked>
                                <span>Publier immédiatement</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Durée du test (minutes)</label>
                            <input type="number" class="form-control" id="ce-json-duration-minutes" value="60" min="1" max="180">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fichier JSON des questions</label>
                            <input type="file" class="form-control" id="ce-json-file" accept=".json,application/json">
                            <small style="color:#64748b;display:block;margin-top:6px;">Tableau de questions : situation, question_text, points, correct_index (0–3), answers[{text}] — ou un objet <code>{"questions":[...]}</code>.</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ou coller le JSON</label>
                            <textarea class="form-control" id="ce-json-paste" rows="8" placeholder='[{"question_text":"...","points":3,"correct_index":0,"answers":[{"text":"A"},{"text":"B"}]}]'></textarea>
                        </div>
                        <div class="form-buttons">
                            <button type="button" class="btn btn-outline" id="ce-json-cancel-btn">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer depuis JSON</button>
                        </div>
                    </form>

                    <div class="dashboard-section" style="margin-top:16px;">
                        <div class="section-header">
                            <div class="section-title">Consignes Compréhension Écrite</div>
                            <button type="button" class="btn btn-primary" id="ce-open-consignes-btn"><i class='bx bx-edit'></i> Consigne</button>
                        </div>
                        <form id="ce-consignes-bundle-form" style="display:none;">
                            <div class="form-group">
                                <label class="form-label">Texte (HTML autorisé)</label>
                                <textarea class="form-control" id="ce-consigne-body" rows="8" required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Statut</label>
                                <select class="form-control" id="ce-consigne-status">
                                    <option value="1">Public</option>
                                    <option value="0">Brouillon</option>
                                </select>
                            </div>
                            <div class="form-buttons">
                                <button type="button" class="btn btn-outline" id="ce-consigne-cancel-btn">Annuler</button>
                                <button type="submit" class="btn btn-primary" id="ce-consigne-submit-btn">Publier</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Compréhension orale — quiz BDD -->
                <div id="co-admin-manager" style="display:none; margin-top: 12px;">
                    <div class="section-title" style="margin-bottom:8px;font-size:1.05rem;">Compréhension orale</div>
                    <form id="co-exam-form" style="display:none;">
                        <input type="hidden" id="co-exam-id">
                        <div class="form-group">
                            <label class="form-label">Titre de l'épreuve</label>
                            <input type="text" class="form-control" id="co-exam-title" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Visibilité</label>
                            <select class="form-control" id="co-exam-visibility">
                                <option value="gratuit">Gratuit</option>
                                <option value="premium">Premium</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="display:flex;align-items:center;gap:.5rem;">
                                <input type="checkbox" id="co-exam-published" checked>
                                <span>Publier immédiatement</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Durée du test (minutes)</label>
                            <input type="number" class="form-control" id="co-duration-minutes" value="30" min="1" max="180">
                        </div>
                        <div id="co-questions-wrap"></div>
                        <div class="form-buttons" style="justify-content:flex-start;gap:8px;">
                            <button type="button" class="btn btn-outline" id="co-add-question-btn"><i class='bx bx-plus'></i> Ajouter une question</button>
                        </div>
                        <div class="form-buttons">
                            <button type="button" class="btn btn-outline" id="co-cancel-btn">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer l'épreuve</button>
                        </div>
                    </form>

                    <form id="co-exam-json-form" style="display:none;">
                        <input type="hidden" id="co-json-exam-id">
                        <div class="form-group">
                            <label class="form-label">Titre de l'épreuve</label>
                            <input type="text" class="form-control" id="co-json-exam-title" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Visibilité</label>
                            <select class="form-control" id="co-json-exam-visibility">
                                <option value="gratuit">Gratuit</option>
                                <option value="premium">Premium</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="display:flex;align-items:center;gap:.5rem;">
                                <input type="checkbox" id="co-json-exam-published" checked>
                                <span>Publier immédiatement</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Durée du test (minutes)</label>
                            <input type="number" class="form-control" id="co-json-duration-minutes" value="30" min="1" max="180">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fichier JSON des questions</label>
                            <input type="file" class="form-control" id="co-json-file" accept=".json,application/json">
                            <small style="color:#64748b;display:block;margin-top:6px;">Champs : question_text, points, image_src, audio_src, correct_index (0–3), answers[{text}]. Chemins médias relatifs au site ou URLs.</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ou coller le JSON</label>
                            <textarea class="form-control" id="co-json-paste" rows="8" placeholder='[{"question_text":"...","audio_src":"uploads/co_media/...","correct_index":0,"answers":[{"text":"A"}]}]'></textarea>
                        </div>
                        <div class="form-buttons">
                            <button type="button" class="btn btn-outline" id="co-json-cancel-btn">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer depuis JSON</button>
                        </div>
                    </form>

                    <div class="dashboard-section" style="margin-top:16px;">
                        <div class="section-header">
                            <div class="section-title">Consignes Compréhension Orale</div>
                            <button type="button" class="btn btn-primary" id="co-open-consignes-btn"><i class='bx bx-edit'></i> Consigne</button>
                        </div>
                        <form id="co-consignes-bundle-form" style="display:none;">
                            <div class="form-group">
                                <label class="form-label">Texte (HTML autorisé)</label>
                                <textarea class="form-control" id="co-consigne-body" rows="8" required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Statut</label>
                                <select class="form-control" id="co-consigne-status">
                                    <option value="1">Public</option>
                                    <option value="0">Brouillon</option>
                                </select>
                            </div>
                            <div class="form-buttons">
                                <button type="button" class="btn btn-outline" id="co-consigne-cancel-btn">Annuler</button>
                                <button type="submit" class="btn btn-primary" id="co-consigne-submit-btn">Publier</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Expression orale (BDD) — saisie manuelle des parties / sujets -->
                <div id="eo-admin-manager" style="display:none; margin-top: 12px;">
                    <div class="section-title" style="margin-bottom:8px;font-size:1.05rem;">Expression orale — publication manuelle</div>
                    <p style="color:#64748b;font-size:0.9rem;margin:0 0 12px;">Construisez l’épreuve par partie (onglets Tâche 1, 2 et 3), avec 5 sujets et une correction par sujet.</p>
                    <form id="eo-exam-form" style="display:none;">
                        <input type="hidden" id="eo-exam-id">
                        <div class="form-group">
                            <label class="form-label">Titre de l'épreuve</label>
                            <input type="text" class="form-control" id="eo-exam-title" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sous-titre (optionnel)</label>
                            <input type="text" class="form-control" id="eo-exam-subtitle">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Visibilité de l'épreuve</label>
                            <select class="form-control" id="eo-exam-visibility">
                                <option value="gratuit">Gratuit</option>
                                <option value="premium">Premium</option>
                            </select>
                            <small style="color:#64748b;">Auto: les 3 épreuves orales les plus récentes restent gratuites par défaut; vous pouvez forcer Premium.</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="display:flex;align-items:center;gap:.5rem;">
                                <input type="checkbox" id="eo-exam-published" checked>
                                <span>Publier immédiatement</span>
                            </label>
                        </div>
                        <div id="eo-parts-wrap"></div>
                        <div class="form-buttons" style="justify-content:flex-start;gap:8px;">
                            <button type="button" class="btn btn-outline" id="eo-add-part-btn"><i class='bx bx-plus'></i> Ajouter une partie</button>
                        </div>
                        <div class="form-buttons">
                            <button type="button" class="btn btn-outline" id="eo-cancel-btn">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer l'épreuve</button>
                        </div>
                    </form>

                    <div class="dashboard-section" style="margin-top:16px;">
                        <div class="section-header">
                            <div class="section-title">Consignes Expression Orale</div>
                            <button type="button" class="btn btn-primary" id="eo-open-consignes-btn"><i class='bx bx-edit'></i> Consigne</button>
                        </div>
                        <form id="eo-consignes-bundle-form" style="display:none;">
                            <div class="form-group">
                                <label class="form-label">Consigne Tâche 2</label>
                                <textarea class="form-control" id="eo-consigne-tache2" rows="4" required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Consigne Tâche 3</label>
                                <textarea class="form-control" id="eo-consigne-tache3" rows="4" required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Statut</label>
                                <select class="form-control" id="eo-consigne-status">
                                    <option value="1">Public</option>
                                    <option value="0">Brouillon</option>
                                </select>
                            </div>
                            <div class="form-buttons">
                                <button type="button" class="btn btn-outline" id="eo-consigne-cancel-btn">Annuler</button>
                                <button type="submit" class="btn btn-primary" id="eo-consigne-submit-btn">Publier</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Liste des sujets -->
                <div class="section-header" style="margin-top: 30px;">
                    <div class="section-title">Sujets existants</div>
                </div>
                <div class="table-container">
                    <table id="topics-table">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Type</th>
                                <th>Visibilité</th>
                                <th>Date de publication</th>
                                <th>Vues</th>
                                <th class="sa-th-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Analytics Section --------------------------------------------------------------->
        <div id="analytics" class="content-section" style="display:none;">
            <div class="dashboard-section">
                <div class="section-header">
                    <div class="section-title">Analyse Vidéo</div>
                    <select class="form-control" id="analytics-period" style="width: auto;">
                        <option value="week">Dernière semaine</option>
                        <option value="month">Dernier mois</option>
                        <option value="48h">Dernières 48 heures</option>
                    </select>
                </div>

                <div id="analytics-focus-banner" class="analytics-focus-banner" style="display:none;" role="status">
                    <div class="analytics-focus-banner__text">
                        <strong>Analyse :</strong>
                        <span id="analytics-focus-title"></span>
                        <span id="analytics-focus-stats" class="analytics-focus-banner__stats"></span>
                    </div>
                    <button type="button" class="btn btn-outline btn-sm" id="analytics-focus-clear-btn">Toutes les vidéos</button>
                </div>

                <div class="charts-section">
                    <div class="chart-card">
                        <div class="chart-header">
                            <div class="chart-title" id="analytics-chart-perf-title">Performances des vidéos</div>
                        </div>
                        <div class="chart-container">
                            <canvas id="video-performance-chart"></canvas>
                        </div>
                    </div>
                    <div class="chart-card" id="analytics-audience-card">
                        <div class="chart-header">
                            <div class="chart-title" id="analytics-chart-aud-title">Audience</div>
                        </div>
                        <div class="chart-container">
                            <canvas id="audience-chart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="section-header">
                    <div class="section-title" id="analytics-popular-title">Liste des vidéos</div>
                </div>
                <div class="table-container">
                    <table id="popular-videos-table">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Vues</th>
                                <th>J'aime</th>
                                <th>Commentaires</th>
                                <th>Visibilité</th>
                                <th>Publication</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Modals -->
    <div class="modal" id="quiz-publish-method-modal" aria-hidden="true" role="dialog" aria-labelledby="quiz-publish-method-title">
        <div class="modal-content" style="max-width:440px;">
            <button type="button" class="modal-close" id="quiz-publish-method-close" aria-label="Fermer">&times;</button>
            <h2 class="modal-title" id="quiz-publish-method-title">Nouvelle épreuve</h2>
            <p style="color:#64748b;font-size:0.95rem;margin-bottom:14px;">Choisissez comment publier cette épreuve.</p>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <button type="button" class="btn btn-primary" id="quiz-publish-method-manual">Saisie manuelle</button>
                <button type="button" class="btn btn-outline" id="quiz-publish-method-json">Importer un fichier JSON</button>
            </div>
        </div>
    </div>

    <div class="modal" id="user-modal">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <h2 class="modal-title">Ajouter un utilisateur</h2>
            <form id="user-form-modal">
                <input type="hidden" id="edit-user-id">
                <div class="form-group">
                    <label class="form-label">Nom complet</label>
                    <input type="text" class="form-control" id="user-name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" id="user-email" required>
                </div>
                <div class="form-group user-password-fields">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" id="user-password" autocomplete="new-password" minlength="8">
                    <small class="form-hint">En modification : laissez vide pour conserver le mot de passe actuel.</small>
                </div>
                <div class="form-group user-password-fields">
                    <label class="form-label">Confirmer le mot de passe</label>
                    <input type="password" class="form-control" id="user-password-confirm" autocomplete="new-password" minlength="8">
                </div>
                <div class="form-group">
                    <label class="form-label">Abonnement</label>
                    <select class="form-control" id="user-subscription">
                        <option value="free">Gratuit</option>
                        <?php foreach (tcf_subscription_plans_catalog(false) as $tcf_plan_opt): ?>
                            <option value="<?php echo htmlspecialchars($tcf_plan_opt['key']); ?>">
                                <?php echo htmlspecialchars(trim(($tcf_plan_opt['tier'] ?? '') . ' — ' . ($tcf_plan_opt['badge'] ?? ''))); ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="monthly">⚠ Mensuel (hérité)</option>
                        <option value="annual">⚠ Annuel (hérité)</option>
                    </select>
                    <small class="form-hint">Les formules correspondent à la page Abonnement. Les types « hérité » ne sont plus proposés aux nouveaux comptes.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Statut</label>
                    <select class="form-control" id="user-status">
                        <option value="active">Actif</option>
                        <option value="inactive">Inactif</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        </div>
    </div>

    <div class="modal" id="admin-modal">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <h2 class="modal-title">Ajouter un administrateur</h2>
            <form id="admin-form-modal">
                <input type="hidden" id="admin-edit-id">
                <div class="form-group">
                    <label class="form-label">Nom complet</label>
                    <input type="text" class="form-control" id="admin-name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" id="admin-email" required>
                </div>
                <div class="form-group admin-password-fields">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" id="admin-password" autocomplete="new-password" minlength="8">
                </div>
                <div class="form-group admin-password-fields">
                    <label class="form-label">Confirmer le mot de passe</label>
                    <input type="password" class="form-control" id="admin-password-confirm" autocomplete="new-password" minlength="8">
                </div>
                <div class="form-group">
                    <label class="form-label">Rôle</label>
                    <select class="form-control" id="admin-role" required>
                        <option value="admin">Administrateur</option>
                        <option value="super_admin">Super Administrateur</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Statut</label>
                    <select class="form-control" id="admin-status">
                        <option value="active">Actif</option>
                        <option value="inactive">Inactif</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        </div>
    </div>

    <div class="modal" id="user-profile-view-modal">
        <div class="modal-content">
            <span class="modal-close" id="close-user-profile-view">&times;</span>
            <h2 class="modal-title">Profil utilisateur</h2>
            <div class="user-profile-view-body">
                <div class="user-profile-view-avatar-wrap">
                    <img src="" alt="" id="user-profile-view-img" class="user-profile-view-img" style="display:none;">
                    <div id="user-profile-view-initials" class="user-profile-view-initials"></div>
                    <span class="presence-dot" id="user-profile-view-presence"></span>
                </div>
                <p><strong>Nom :</strong> <span id="user-profile-view-name"></span></p>
                <p><strong>Email :</strong> <span id="user-profile-view-email"></span></p>
                <p><strong>Abonnement :</strong> <span id="user-profile-view-sub"></span></p>
                <p><strong>Statut :</strong> <span id="user-profile-view-status"></span></p>
                <p><strong>Inscription :</strong> <span id="user-profile-view-created"></span></p>
                <p><strong>Jours actifs sur le site :</strong> <span id="user-profile-view-activity-days"></span></p>
                <p><strong>Dernière journée de visite :</strong> <span id="user-profile-view-activity-last"></span></p>
            </div>
        </div>
    </div>

    <!-- Navigation mobile admin (≤900px) -->
    <nav class="tcf-sa-mobile-nav" id="saMobileNav" aria-label="Navigation administration mobile">
        <button type="button" class="tcf-sa-mobile-nav__item is-active" data-sa-tab="home" data-sa-target="dashboard">
            <i class='bx bxs-dashboard' aria-hidden="true"></i>
            <span>Accueil</span>
        </button>
        <button type="button" class="tcf-sa-mobile-nav__item" data-sa-tab="epreuves" data-sa-submenu="topics" aria-controls="saSubnavSheet">
            <i class='bx bx-book-alt' aria-hidden="true"></i>
            <span>Épreuves</span>
        </button>
        <button type="button" class="tcf-sa-mobile-nav__item" data-sa-tab="subscriptions" data-sa-submenu="subscriptions" aria-controls="saSubnavSheet">
            <i class='bx bx-credit-card' aria-hidden="true"></i>
            <span>Abonnements</span>
        </button>
        <button type="button" class="tcf-sa-mobile-nav__item" data-sa-tab="videos" data-sa-submenu="videos" aria-controls="saSubnavSheet">
            <i class='bx bxs-video' aria-hidden="true"></i>
            <span>Vidéos</span>
        </button>
        <button type="button" class="tcf-sa-mobile-nav__item" data-sa-tab="menu" data-sa-open-sidebar="1" aria-controls="saSidebar">
            <i class='bx bx-menu' aria-hidden="true"></i>
            <span>Menu</span>
        </button>
    </nav>

    <div class="tcf-sa-subnav-sheet-overlay" id="saSubnavSheetOverlay" aria-hidden="true"></div>
    <div class="tcf-sa-subnav-sheet" id="saSubnavSheet" role="dialog" aria-modal="true" aria-labelledby="saSubnavSheetTitle">
        <div class="tcf-sa-subnav-sheet__head">
            <h2 id="saSubnavSheetTitle">Menu</h2>
            <button type="button" class="tcf-sa-subnav-sheet__close" id="saSubnavSheetClose" aria-label="Fermer">
                <i class='bx bx-x'></i>
            </button>
        </div>
        <div class="tcf-sa-subnav-sheet__body" id="saSubnavSheetBody"></div>
    </div>

    <!-- Notification Toast -->
    <div class="tcf-admin-toast" id="notification-toast">
        <i class='bx bx-check-circle'></i>
        <span id="notification-text"></span>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/fr.min.js"></script>

    <script>
        window.TCF_SA_IS_SUPER = <?php echo $isSuperAdmin ? 'true' : 'false'; ?>;
        // Passer les données PHP à JavaScript
        var TCF_ADMIN_SESSION_ID_INLINE = <?php echo (int) $_SESSION['user_id']; ?>;
        var usersFromDB = <?php echo $users_json; ?>;
        var videosFromDB = <?php echo $videos_json; ?>;
        var topicsFromDB = <?php echo $topics_json; ?>;
        var adminsFromDB = <?php echo $admins_json; ?>;
        var messagesFromDB = <?php echo $messages_json; ?>;
        var activitiesFromDB = <?php echo $activities_json; ?>;
        var notificationsFromDB = <?php echo $notifications_json; ?>;
    </script>
    <script>
        window.TCF_SA_BRANDING_DEFAULTS = <?php echo json_encode(['title' => $tcf_brand_default_name, 'tag' => $tcf_brand_default_tag], JSON_UNESCAPED_UNICODE); ?>;
        window.TCF_SA_FALLBACK_LOGO = <?php echo json_encode(site_href('Assets/IMAGE/home/canada.jpg')); ?>;
        window.TCF_CHAT_API = <?php echo json_encode(site_href('chat_api.php')); ?>;
        window.TCF_SITE_PUBLIC = <?php echo json_encode(rtrim(site_href(''), '/')); ?>;
    </script>
    <script src="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script src="../Assets/javascript/sa-staff-chat.js"></script>
    <script src="../Assets/javascript/superAdmin.ui.js"></script>
    <script src="../Assets/javascript/admin-mobile-nav.js"></script>

    <div class="tcf-ai-assistant" id="tcf-ai-assistant" data-greeting="Bonjour, je suis votre assistant administration. Comment puis-je vous aider sur la plateforme ?">
        <div class="tcf-ai-assistant__panel" id="tcf-ai-assistant-panel" aria-live="polite">
            <div class="tcf-ai-assistant__head">
                <div class="tcf-ai-assistant__head-text">
                    <strong>Assistant Administration</strong>
                    <span>Aide à la gestion ELITE TCF CANADA.</span>
                </div>
                <button type="button" class="tcf-ai-assistant__close" id="tcf-ai-assistant-close" aria-label="Fermer l'assistant">
                    <i class='bx bx-x' aria-hidden="true"></i>
                </button>
            </div>
            <div class="tcf-ai-assistant__log" id="tcf-ai-assistant-log"></div>
            <div class="tcf-ai-assistant__composer">
                <input class="tcf-ai-assistant__input" id="tcf-ai-assistant-input" type="text" maxlength="1500" placeholder="Posez votre question...">
                <button type="button" class="tcf-ai-assistant__send" id="tcf-ai-assistant-send">Envoyer</button>
            </div>
        </div>
    </div>
    <script>
        window.TCF_ASSISTANT_ENABLED = true;
        window.TCF_ASSISTANT_API = <?php echo json_encode(site_href('gemini_assistant_api.php')); ?>;
        window.TCF_ASSISTANT_LS_KEY = 'tcf_ai_assistant_history_admin_v1';
    </script>
    <script src="<?php echo htmlspecialchars(site_href('Assets/javascript/tcf-assistant-widget.js')); ?>"></script>

    <?php
    if (!empty($tcf_profile_panel_user['id'])) {
        $user = $tcf_profile_panel_user;
        $tcf_profile_panel_skip_assets = true;
        include __DIR__ . '/../includes/profile_panel_logged_in.php';
        ?>
    <script src="<?php echo htmlspecialchars(site_href('Assets/javascript/profile_panel.js')); ?>"></script>
        <?php
    }
    ?>
</body>

</html>