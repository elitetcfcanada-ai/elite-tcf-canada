<?php
session_start();
require_once __DIR__ . '/../includes/admin_upload_limits.php';
tcf_admin_apply_upload_limits();
require_once '../includes/config.php';
require_once __DIR__ . '/channel_handlers.php';
require_once __DIR__ . '/../includes/site_contact.php';
require_once __DIR__ . '/../includes/video_duration.php';
require_once __DIR__ . '/../includes/tcf_notifications_helper.php';
require_once __DIR__ . '/../includes/community_posts_helper.php';
require_once __DIR__ . '/../includes/tcf_legacy_tables.php';
require_once __DIR__ . '/../includes/media_blob.php';
require_once __DIR__ . '/../includes/video_optimize.php';
require_once __DIR__ . '/../includes/video_social.php';
require_once __DIR__ . '/../includes/tcf_schema.php';
require_once __DIR__ . '/../includes/partners_helper.php';
try {
    tcf_community_posts_ensure_tables($pdo);
    tcf_community_drop_channel_tables($pdo);
} catch (Throwable $e) {
    // ignore bootstrap DB cleanup errors
}

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
        case 'get_admin_dashboard':
            getAdminDashboardStats();
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
        $u['avatar_url'] = tcf_user_avatar_display_url($pdo, (int) $u['id'], $u['avatar'] ?? null);
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
        $stmt = $pdo->query(tcf_videos_list_select_sql('v') . ' FROM videos v ORDER BY v.created_at DESC');
        $videos = tcf_videos_normalize_list_rows($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
        foreach ($videos as &$v) {
            $v['thumbnail_href'] = tcf_video_media_href($pdo, (int) ($v['id'] ?? 0), $v['thumbnail_url'] ?? '', 'thumbnail');
            $v['video_href'] = tcf_video_media_href($pdo, (int) ($v['id'] ?? 0), $v['video_url'] ?? '', 'video');
        }
        unset($v);
        try {
            $videos = tcf_enrich_videos_with_playlists($pdo, $videos);
        } catch (Throwable $e) {
        }
        echo json_encode(['success' => true, 'data' => $videos], JSON_UNESCAPED_UNICODE);
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
        $description = trim((string) ($_POST['description'] ?? ''));
        $visibility = strtolower(trim((string) ($_POST['visibility'] ?? 'public')));
        if (!in_array($visibility, ['public', 'private', 'premium'], true)) {
            $visibility = 'public';
        }
        if ($title === '') {
            echo json_encode(['success' => false, 'message' => 'Le titre de la vidéo est obligatoire.']);
            exit();
        }
        if (mb_strlen($title) > 100) {
            echo json_encode(['success' => false, 'message' => 'Le titre ne doit pas dépasser 100 caractères.']);
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
        // Compression légère H.264 / faststart si ffmpeg disponible (qualité préservée)
        $optimized = tcf_video_optimize_uploaded_file($video_url);
        if (is_string($optimized) && $optimized !== '') {
            $video_url = $optimized;
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
            tcf_video_store_blobs_from_paths($pdo, $newVid, $thumbnail_url, $video_url);
            try {
                tcf_sync_video_playlists($pdo, $newVid, tcf_parse_playlist_ids_from_post());
            } catch (Throwable $e) {
            }
            addActivity($_SESSION['user_id'], 'video', 'Nouvelle vidéo publiée', "La vidéo '$title' a été publiée");

            // Uniquement les utilisateurs déjà inscrits au moment de la publication
            try {
                $deep = site_href('watch.php?v=' . $newVid);
                tcf_notify_users_registered_before(
                    $pdo,
                    'video',
                    'Nouvelle vidéo publiée',
                    "La vidéo « $title » est maintenant disponible.",
                    $deep
                );
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
        $description = trim((string) ($_POST['description'] ?? ''));
        $visibility = strtolower(trim((string) ($_POST['visibility'] ?? 'public')));
        if (!in_array($visibility, ['public', 'private', 'premium'], true)) {
            $visibility = 'public';
        }
        if ($id <= 0 || $title === '') {
            echo json_encode(['success' => false, 'message' => 'Vidéo invalide (id/titre).']);
            exit();
        }
        if (mb_strlen($title) > 100) {
            echo json_encode(['success' => false, 'message' => 'Le titre ne doit pas dépasser 100 caractères.']);
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
            $optimized = tcf_video_optimize_uploaded_file($video_url);
            if (is_string($optimized) && $optimized !== '') {
                $video_url = $optimized;
            }
            $duration = tcf_probe_video_duration_for_db(tcf_uploads_fs_path($video_url));
        }
        $stmt = $pdo->prepare("UPDATE videos SET title = ?, description = ?, thumbnail_url = ?, video_url = ?, visibility = ?, duration = ? WHERE id = ?");
        $success = $stmt->execute([$title, $description, $thumbnail_url, $video_url, $visibility, $duration, $id]);

        if ($success) {
            tcf_video_store_blobs_from_paths($pdo, (int) $id, $thumbnail_url, $video_url);
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
    saRequireSuperAdminJson();
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
            tcf_delete_notifications_matching($pdo, 'watch.php?v=' . (int) $id);
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
        $table = tcf_testimonials_table($pdo);
        $stmt = $pdo->query(
            "SELECT t.id, t.author_name, t.content, t.user_id, t.rating, t.created_at, u.avatar AS user_avatar
             FROM `{$table}` t
             LEFT JOIN users u ON u.id = t.user_id
             ORDER BY t.created_at DESC"
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($rows as &$t) {
            $uid = (int) ($t['user_id'] ?? 0);
            $t['avatar_url'] = $uid > 0
                ? tcf_user_avatar_display_url($pdo, $uid, isset($t['user_avatar']) ? (string) $t['user_avatar'] : null)
                : null;
            unset($t['user_avatar']);
        }
        unset($t);
        echo json_encode(['success' => true, 'data' => $rows], JSON_UNESCAPED_UNICODE);
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
        $table = tcf_testimonials_table($pdo);
        $stmt = $pdo->prepare("DELETE FROM `{$table}` WHERE id = ?");
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
        $table = tcf_testimonials_table($pdo);
        $stmt = $pdo->prepare("UPDATE `{$table}` SET author_name = ?, content = ?, rating = ? WHERE id = ?");
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
            $topicId = (int) $pdo->lastInsertId();
            $deep = $topicId > 0 ? site_href('Expresion_ecrite.php') : site_href('index.php');
            if ($type === 'eo' || stripos((string) $type, 'oral') !== false) {
                $deep = site_href('Expresion_orale.php');
            } elseif ($type === 'ce' || stripos((string) $type, 'ecrite') !== false) {
                $deep = site_href('comprehesion_ecrite.php');
            } elseif ($type === 'co' || stripos((string) $type, 'orale') !== false) {
                $deep = site_href('comprehension_orale.php');
            }
            try {
                tcf_notify_users_registered_before(
                    $pdo,
                    'topic',
                    'Nouveau sujet ajouté',
                    "Le sujet « $title » est maintenant disponible.",
                    $deep
                );
            } catch (Throwable $e) {
                error_log('Erreur notifications sujet: ' . $e->getMessage());
            }
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
    saRequireSuperAdminJson();
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
            // Anciennes notifs topic (lien générique) : nettoyage par titre si connu
            if (!empty($topic['title'])) {
                tcf_delete_notifications_by_type_payload(
                    $pdo,
                    'topic',
                    'Nouveau sujet ajouté',
                    "Le sujet « {$topic['title']} » est maintenant disponible."
                );
            }
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
                    addNotification((int) $userId, 'message', $notifTitle, $notifContent, 'posts.php');
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
    saRequireSuperAdminJson();
    try {
        $id = $_POST['id'];
        $notifTitle = null;
        $notifContent = null;
        try {
            $st = $pdo->prepare('SELECT subject, content FROM community_messages WHERE id = ?');
            $st->execute([$id]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $subject = trim((string) ($row['subject'] ?? ''));
                $content = trim((string) ($row['content'] ?? ''));
                $defaultTitle = 'Nouvelle annonce';
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
                    $notifContent = $content !== '' ? $content : '—';
                }
            }
        } catch (Throwable $e) {
        }

        $stmt = $pdo->prepare("DELETE FROM community_messages WHERE id = ?");
        $success = $stmt->execute([$id]);

        if ($success) {
            if ($notifTitle !== null) {
                tcf_delete_notifications_by_type_payload($pdo, 'message', $notifTitle, $notifContent);
            }
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
        $a['avatar_url'] = tcf_user_avatar_display_url($pdo, (int) $a['id'], $a['avatar'] ?? null);
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

// Fonctions pour les statistiques
function tcf_admin_has_visit_logs(): bool
{
    global $pdo;
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    if (tcf_visiteurs_available($pdo)) {
        $ok = true;
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

function tcf_admin_normalize_range(string $range): string
{
    $range = preg_replace('/[^a-z0-9]/', '', strtolower($range));
    $allowed = ['today', '7d', '30d', '90d', 'year'];
    return in_array($range, $allowed, true) ? $range : '30d';
}

/** Visites uniques (1 appareil / jour) sur la période. */
function tcf_admin_unique_visits_sql(string $range = 'today'): string
{
    global $pdo;
    $range = tcf_admin_normalize_range($range);
    $w = tcf_trace_sql_where($range, 'created_at');
    if (tcf_visiteurs_available($pdo)) {
        return "SELECT COUNT(*) FROM (
            SELECT 1 FROM visiteurs
            WHERE kind = 'site' AND {$w}
              AND visitor_key IS NOT NULL AND visitor_key != ''
            GROUP BY visitor_key, DATE(created_at)
        ) tcf_uv";
    }

    return "SELECT COUNT(*) FROM (
        SELECT 1 FROM site_visit_logs
        WHERE {$w} AND session_id IS NOT NULL AND session_id != ''
        GROUP BY session_id, DATE(created_at)
    ) tcf_uv";
}

function tcf_admin_visitors_today_sql(): string
{
    return tcf_admin_unique_visits_sql('today');
}

function tcf_trace_sql_where(string $range, string $dateCol = 'created_at'): string
{
    switch (tcf_admin_normalize_range($range)) {
        case 'today':
            return "DATE($dateCol) = CURDATE()";
        case '7d':
            return "$dateCol >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)";
        case '30d':
            return "$dateCol >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)";
        case '90d':
            return "$dateCol >= DATE_SUB(CURDATE(), INTERVAL 89 DAY)";
        case 'year':
            return "$dateCol >= DATE_SUB(CURDATE(), INTERVAL 364 DAY)";
        default:
            return "$dateCol >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)";
    }
}

function getTraceability()
{
    saRequireSuperAdminJson();
    global $pdo;
    $range = tcf_admin_normalize_range((string) ($_POST['range'] ?? '30d'));

    $empty = [
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
        'unique_visits' => 0,
    ];

    if (!tcf_admin_has_visit_logs()) {
        echo json_encode(['success' => true, 'data' => $empty]);
        exit();
    }

    try {
        $wVisit = tcf_trace_sql_where($range, 'v.created_at');
        $wUser = tcf_trace_sql_where($range, 'u.created_at');
        $wPay = tcf_trace_sql_where($range, 'p.created_at');
        $useVisiteurs = tcf_visiteurs_available($pdo);
        $bucket = 'DATE(v.created_at)';
        $ub = 'DATE(u.created_at)';
        $pb = 'DATE(p.created_at)';

        // Visites uniques / jour (pas des pages vues)
        if ($useVisiteurs) {
            $stmt = $pdo->query(
                "SELECT {$bucket} AS lb, COUNT(DISTINCT v.visitor_key) AS c
                 FROM visiteurs v
                 WHERE v.kind = 'site' AND {$wVisit}
                   AND v.visitor_key IS NOT NULL AND v.visitor_key != ''
                 GROUP BY lb ORDER BY lb"
            );
        } else {
            $stmt = $pdo->query(
                "SELECT {$bucket} AS lb, COUNT(DISTINCT v.session_id) AS c
                 FROM site_visit_logs v
                 WHERE {$wVisit} AND v.session_id IS NOT NULL AND v.session_id != ''
                 GROUP BY lb ORDER BY lb"
            );
        }
        $vis = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->query(
            "SELECT {$ub} AS lb, COUNT(*) AS c FROM users u WHERE u.role = 'user' AND {$wUser} GROUP BY lb ORDER BY lb"
        );
        $usr = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $payc = [];
        $rev = [];
        try {
            $payTable = tcf_subscription_payments_table($pdo);
            $payWhere = tcf_subscription_payments_revenue_where($payTable, 'p');
            $wPayP = tcf_trace_sql_where($range, 'p.created_at');
            $stmt = $pdo->query(
                "SELECT DATE(p.created_at) AS lb, COUNT(*) AS c
                 FROM `{$payTable}` p
                 WHERE ({$payWhere}) AND {$wPayP}
                 GROUP BY lb ORDER BY lb"
            );
            $payc = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = $pdo->query(
                "SELECT DATE(p.created_at) AS lb, COALESCE(SUM(p.amount),0) AS s
                 FROM `{$payTable}` p
                 WHERE ({$payWhere}) AND {$wPayP}
                 GROUP BY lb ORDER BY lb"
            );
            $rev = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            try {
                $stmt = $pdo->query(
                    "SELECT {$pb} AS lb, COUNT(*) AS c FROM payments p WHERE p.status = 'completed' AND {$wPay} GROUP BY lb ORDER BY lb"
                );
                $payc = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt = $pdo->query(
                    "SELECT {$pb} AS lb, COALESCE(SUM(p.amount),0) AS s FROM payments p WHERE p.status = 'completed' AND {$wPay} GROUP BY lb ORDER BY lb"
                );
                $rev = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Throwable $e2) {
                $payc = [];
                $rev = [];
            }
        }

        if ($useVisiteurs) {
            $stmt = $pdo->query(
                "SELECT v.country_code AS code, v.country_name AS name,
                        COUNT(DISTINCT v.visitor_key) AS c
                 FROM visiteurs v
                 WHERE v.kind = 'site' AND {$wVisit}
                   AND v.country_code IS NOT NULL AND v.country_code != ''
                   AND v.visitor_key IS NOT NULL AND v.visitor_key != ''
                 GROUP BY v.country_code, v.country_name
                 ORDER BY c DESC
                 LIMIT 12"
            );
        } else {
            $stmt = $pdo->query(
                "SELECT v.country_code AS code, v.country_name AS name,
                        COUNT(DISTINCT v.session_id) AS c
                 FROM site_visit_logs v
                 WHERE {$wVisit} AND v.country_code IS NOT NULL AND v.country_code != ''
                 GROUP BY v.country_code, v.country_name
                 ORDER BY c DESC
                 LIMIT 12"
            );
        }
        $vcountries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => [
                'range' => $range,
                'unique_visits' => sa_safe_count($pdo, tcf_admin_unique_visits_sql($range)),
                'visits_labels' => array_column($vis, 'lb'),
                'visits_values' => array_map('intval', array_column($vis, 'c')),
                'users_labels' => array_column($usr, 'lb'),
                'users_values' => array_map('intval', array_column($usr, 'c')),
                'payments_count_labels' => array_column($payc, 'lb'),
                'payments_count_values' => array_map('intval', array_column($payc, 'c')),
                'revenue_labels' => array_column($rev, 'lb'),
                'revenue_values' => array_map('floatval', array_column($rev, 's')),
                'visit_countries' => $vcountries,
            ],
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
    }
    exit();
}

function sa_safe_count(PDO $pdo, string $sql): int
{
    try {
        return (int) $pdo->query($sql)->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

/**
 * @return array{labels: list<string>, values: list<int>}
 */
function sa_admin_exam_views_series(PDO $pdo, int $days = 14): array
{
    $days = max(7, min(60, $days));
    $labels = [];
    $map = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime('-' . $i . ' days'));
        $labels[] = $d;
        $map[$d] = 0;
    }
    $tables = [
        'tcf_ce_exam_views',
        'tcf_co_exam_views',
        'tcf_ee_exam_views',
        'tcf_eo_exam_views',
    ];
    $since = $labels[0] ?? date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
    foreach ($tables as $table) {
        try {
            $st = $pdo->prepare(
                "SELECT DATE(viewed_at) AS d, COUNT(*) AS c
                 FROM {$table}
                 WHERE viewed_at >= ? AND viewed_at < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                 GROUP BY DATE(viewed_at)"
            );
            $st->execute([$since . ' 00:00:00']);
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $d = (string) ($row['d'] ?? '');
                if (isset($map[$d])) {
                    $map[$d] += (int) ($row['c'] ?? 0);
                }
            }
        } catch (Throwable $e) {
            // table may be absent
        }
    }
    $values = [];
    foreach ($labels as $d) {
        $values[] = (int) ($map[$d] ?? 0);
    }
    $shortLabels = array_map(static function (string $d): string {
        $ts = strtotime($d);
        return $ts ? date('d/m', $ts) : $d;
    }, $labels);

    return ['labels' => $shortLabels, 'values' => $values];
}

function getAdminDashboardStats(): void
{
    global $pdo;
    if (!in_array(($_SESSION['role'] ?? ''), ['admin', 'super_admin'], true)) {
        echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
        exit();
    }

    $videosTotal = sa_safe_count($pdo, 'SELECT COUNT(*) FROM videos');
    $videosPublic = sa_safe_count($pdo, "SELECT COUNT(*) FROM videos WHERE visibility IN ('public','premium') OR visibility IS NULL OR visibility=''");
    $videoViews = sa_safe_count($pdo, 'SELECT COALESCE(SUM(views), 0) FROM videos');
    $videoComments = 0;
    try {
        $stVc = $pdo->query("SELECT comments_json FROM videos WHERE comments_json IS NOT NULL AND comments_json != '' AND comments_json != '[]'");
        foreach ($stVc->fetchAll(PDO::FETCH_COLUMN) as $rawComments) {
            $decoded = json_decode((string) $rawComments, true);
            if (is_array($decoded)) {
                $videoComments += count($decoded);
            }
        }
    } catch (Throwable $e) {
        $videoComments = sa_safe_count($pdo, 'SELECT COUNT(*) FROM video_comments');
    }

    if (tcf_schema_has_table($pdo, 'comprehension_ecrite')) {
        $ceTotal = sa_safe_count($pdo, "SELECT COUNT(*) FROM comprehension_ecrite WHERE kind='exam'");
        $cePub = sa_safe_count($pdo, "SELECT COUNT(*) FROM comprehension_ecrite WHERE kind='exam' AND is_published=1");
        $ceViews = sa_safe_count($pdo, "SELECT COALESCE(SUM(views_count), 0) FROM comprehension_ecrite WHERE kind='exam'");
    } else {
        $ceTotal = sa_safe_count($pdo, 'SELECT COUNT(*) FROM tcf_ce_exams');
        $cePub = sa_safe_count($pdo, 'SELECT COUNT(*) FROM tcf_ce_exams WHERE is_published=1');
        $ceViews = sa_safe_count($pdo, 'SELECT COUNT(*) FROM tcf_ce_exam_views');
    }
    if (tcf_schema_has_table($pdo, 'comprehension_orale')) {
        $coTotal = sa_safe_count($pdo, "SELECT COUNT(*) FROM comprehension_orale WHERE kind='exam'");
        $coPub = sa_safe_count($pdo, "SELECT COUNT(*) FROM comprehension_orale WHERE kind='exam' AND is_published=1");
        $coViews = sa_safe_count($pdo, "SELECT COALESCE(SUM(views_count), 0) FROM comprehension_orale WHERE kind='exam'");
    } else {
        $coTotal = sa_safe_count($pdo, 'SELECT COUNT(*) FROM tcf_co_exams');
        $coPub = sa_safe_count($pdo, 'SELECT COUNT(*) FROM tcf_co_exams WHERE is_published=1');
        $coViews = sa_safe_count($pdo, 'SELECT COUNT(*) FROM tcf_co_exam_views');
    }
    if (tcf_schema_has_table($pdo, 'expression_ecrite')) {
        $eeTotal = sa_safe_count($pdo, "SELECT COUNT(*) FROM expression_ecrite WHERE kind='exam'");
        $eePub = sa_safe_count($pdo, "SELECT COUNT(*) FROM expression_ecrite WHERE kind='exam' AND is_published=1");
        $eeViews = sa_safe_count($pdo, "SELECT COALESCE(SUM(views_count), 0) FROM expression_ecrite WHERE kind='exam'");
    } else {
        $eeTotal = sa_safe_count($pdo, 'SELECT COUNT(*) FROM tcf_ee_exams');
        $eePub = sa_safe_count($pdo, 'SELECT COUNT(*) FROM tcf_ee_exams WHERE is_published=1');
        $eeViews = sa_safe_count($pdo, 'SELECT COUNT(*) FROM tcf_ee_exam_views');
    }
    if (tcf_schema_has_table($pdo, 'expression_orale')) {
        $eoTotal = sa_safe_count($pdo, "SELECT COUNT(*) FROM expression_orale WHERE kind='exam'");
        $eoPub = sa_safe_count($pdo, "SELECT COUNT(*) FROM expression_orale WHERE kind='exam' AND is_published=1");
        $eoViews = sa_safe_count($pdo, "SELECT COALESCE(SUM(views_count), 0) FROM expression_orale WHERE kind='exam'");
    } else {
        $eoTotal = sa_safe_count($pdo, 'SELECT COUNT(*) FROM tcf_eo_exams');
        $eoPub = sa_safe_count($pdo, 'SELECT COUNT(*) FROM tcf_eo_exams WHERE is_published=1');
        $eoViews = sa_safe_count($pdo, 'SELECT COUNT(*) FROM tcf_eo_exam_views');
    }

    $annTotal = 0;
    $annPub = 0;
    $annViews = 0;
    $cpTable = tcf_community_posts_table($pdo);
    if ($cpTable === 'annonces') {
        $annTotal = sa_safe_count($pdo, "SELECT COUNT(*) FROM annonces WHERE kind='post'");
        $annPub = sa_safe_count($pdo, "SELECT COUNT(*) FROM annonces WHERE kind='post' AND is_published=1");
        try {
            $stAnn = $pdo->query("SELECT views_json FROM annonces WHERE kind='post' AND views_json IS NOT NULL AND views_json != '' AND views_json != '[]'");
            foreach ($stAnn->fetchAll(PDO::FETCH_COLUMN) as $rawViews) {
                $decoded = json_decode((string) $rawViews, true);
                if (is_array($decoded)) {
                    $annViews += count($decoded);
                }
            }
        } catch (Throwable $e) {
            $annViews = 0;
        }
    } else {
        $annTotal = sa_safe_count($pdo, 'SELECT COUNT(*) FROM community_posts');
        $annPub = sa_safe_count($pdo, 'SELECT COUNT(*) FROM community_posts WHERE is_published=1');
        $annViews = sa_safe_count($pdo, 'SELECT COUNT(*) FROM community_post_views');
    }

    $visitorsToday = 0;
    if (tcf_admin_has_visit_logs()) {
        $visitorsToday = sa_safe_count($pdo, tcf_admin_visitors_today_sql());
    } else {
        $visitorsToday = sa_safe_count(
            $pdo,
            'SELECT COUNT(DISTINCT ip_address) FROM analytics WHERE DATE(created_at) = CURDATE()'
        );
    }

    $examsTotal = $ceTotal + $coTotal + $eeTotal + $eoTotal;
    $examsPublished = $cePub + $coPub + $eePub + $eoPub;
    $examViewsTotal = $ceViews + $coViews + $eeViews + $eoViews;

    $topVideos = [];
    try {
        $st = $pdo->query(
            'SELECT id, title, COALESCE(views, 0) AS views, visibility
             FROM videos
             ORDER BY COALESCE(views, 0) DESC, id DESC
             LIMIT 8'
        );
        $topVideos = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        $topVideos = [];
    }

    $series = sa_admin_exam_views_series($pdo, 14);

    echo json_encode([
        'success' => true,
        'data' => [
            'visitors_today' => $visitorsToday,
            'videos' => [
                'total' => $videosTotal,
                'listed' => $videosPublic,
                'views' => $videoViews,
                'comments' => $videoComments,
            ],
            'exams' => [
                'total' => $examsTotal,
                'published' => $examsPublished,
                'views' => $examViewsTotal,
                'by_skill' => [
                    'ce' => ['total' => $ceTotal, 'published' => $cePub, 'views' => $ceViews],
                    'co' => ['total' => $coTotal, 'published' => $coPub, 'views' => $coViews],
                    'ee' => ['total' => $eeTotal, 'published' => $eePub, 'views' => $eeViews],
                    'eo' => ['total' => $eoTotal, 'published' => $eoPub, 'views' => $eoViews],
                ],
            ],
            'announcements' => [
                'total' => $annTotal,
                'published' => $annPub,
                'views' => $annViews,
            ],
            'charts' => [
                'content_mix' => [
                    'labels' => ['Vidéos', 'CE', 'CO', 'EE', 'EO', 'Annonces'],
                    'values' => [$videosTotal, $ceTotal, $coTotal, $eeTotal, $eoTotal, $annTotal],
                ],
                'exam_views_by_skill' => [
                    'labels' => ['CE', 'CO', 'EE', 'EO'],
                    'values' => [$ceViews, $coViews, $eeViews, $eoViews],
                ],
                'exam_views_trend' => $series,
                'top_videos' => [
                    'labels' => array_map(static function ($r) {
                        $t = trim((string) ($r['title'] ?? 'Sans titre'));
                        if (function_exists('mb_strlen') && mb_strlen($t) > 28) {
                            return mb_substr($t, 0, 28) . '…';
                        }
                        if (strlen($t) > 28) {
                            return substr($t, 0, 28) . '…';
                        }
                        return $t;
                    }, $topVideos),
                    'values' => array_map(static fn ($r) => (int) ($r['views'] ?? 0), $topVideos),
                ],
            ],
            'top_videos' => $topVideos,
        ],
    ]);
    exit();
}

function getStats()
{
    saRequireSuperAdminJson();
    global $pdo;
    try {
        $range = tcf_admin_normalize_range((string) ($_POST['range'] ?? 'today'));

        $usersCount = sa_safe_count($pdo, "SELECT COUNT(*) FROM users WHERE role = 'user'");
        $adminsCount = sa_safe_count($pdo, "SELECT COUNT(*) FROM users WHERE role IN ('admin','super_admin')");
        $usersActive = sa_safe_count($pdo, "SELECT COUNT(*) FROM users WHERE role = 'user' AND status = 'active'");

        $visitorsCount = 0;
        if (tcf_admin_has_visit_logs()) {
            $visitorsCount = sa_safe_count($pdo, tcf_admin_unique_visits_sql($range));
        } else {
            $wAnalytics = tcf_trace_sql_where($range, 'created_at');
            $visitorsCount = sa_safe_count(
                $pdo,
                "SELECT COUNT(DISTINCT ip_address) FROM analytics WHERE {$wAnalytics}"
            );
        }

        $subsCount = sa_safe_count(
            $pdo,
            "SELECT COUNT(*) FROM users
             WHERE role = 'user'
               AND status = 'active'
               AND subscription_type != 'free'
               AND (subscription_expires_at IS NULL OR subscription_expires_at > NOW())"
        );

        $revenuePayments = 0.0;
        try {
            $stmt = $pdo->query(
                "SELECT COALESCE(SUM(amount), 0) AS total FROM payments
                 WHERE status = 'completed'
                   AND MONTH(created_at) = MONTH(CURDATE())
                   AND YEAR(created_at) = YEAR(CURDATE())"
            );
            $revenuePayments = (float) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (Throwable $e) {
            $revenuePayments = 0.0;
        }

        $revenueSubsMonth = 0.0;
        $paymentsCount = 0;
        try {
            $payTable = tcf_subscription_payments_table($pdo);
            $payWhereRev = tcf_subscription_payments_revenue_where($payTable);
            $payWhereHist = tcf_subscription_payments_history_where($payTable);
            $stmt = $pdo->query(
                "SELECT COALESCE(SUM(amount), 0) FROM `{$payTable}`
                 WHERE ({$payWhereRev})
                   AND MONTH(created_at) = MONTH(CURDATE())
                   AND YEAR(created_at) = YEAR(CURDATE())"
            );
            $revenueSubsMonth = (float) $stmt->fetchColumn();
            $paymentsCount = sa_safe_count($pdo, "SELECT COUNT(*) FROM `{$payTable}` WHERE ({$payWhereHist})");
        } catch (Throwable $e) {
            $revenueSubsMonth = 0.0;
            $paymentsCount = 0;
        }

        $revenue = $revenuePayments + $revenueSubsMonth;

        $plansTable = tcf_subscription_plans_table($pdo);
        $plansTotal = sa_safe_count($pdo, "SELECT COUNT(*) FROM `{$plansTable}`");
        $plansActive = sa_safe_count($pdo, "SELECT COUNT(*) FROM `{$plansTable}` WHERE is_active = 1");

        $videosTotal = sa_safe_count($pdo, 'SELECT COUNT(*) FROM videos');
        $videoViews = sa_safe_count($pdo, 'SELECT COALESCE(SUM(views), 0) FROM videos');

        $temTable = tcf_testimonials_table($pdo);
        $testimonialsTotal = sa_safe_count($pdo, "SELECT COUNT(*) FROM `{$temTable}`");
        $testimonialsPub = sa_safe_count($pdo, "SELECT COUNT(*) FROM `{$temTable}` WHERE is_published = 1");

        $partTable = tcf_partners_table($pdo);
        $partnersTotal = sa_safe_count($pdo, "SELECT COUNT(*) FROM `{$partTable}`");
        $partnersPub = sa_safe_count($pdo, "SELECT COUNT(*) FROM `{$partTable}` WHERE is_published = 1");

        $annTotal = 0;
        $annPub = 0;
        $cpTable = tcf_community_posts_table($pdo);
        if ($cpTable === 'annonces') {
            $annTotal = sa_safe_count($pdo, "SELECT COUNT(*) FROM annonces WHERE kind = 'post'");
            $annPub = sa_safe_count($pdo, "SELECT COUNT(*) FROM annonces WHERE kind = 'post' AND is_published = 1");
        } else {
            $annTotal = sa_safe_count($pdo, 'SELECT COUNT(*) FROM community_posts');
            $annPub = sa_safe_count($pdo, 'SELECT COUNT(*) FROM community_posts WHERE is_published = 1');
        }

        if (tcf_schema_has_table($pdo, 'comprehension_ecrite')) {
            $ceTotal = sa_safe_count($pdo, "SELECT COUNT(*) FROM comprehension_ecrite WHERE kind = 'exam'");
            $coTotal = sa_safe_count($pdo, "SELECT COUNT(*) FROM comprehension_orale WHERE kind = 'exam'");
            $eeTotal = sa_safe_count($pdo, "SELECT COUNT(*) FROM expression_ecrite WHERE kind = 'exam'");
            $eoTotal = sa_safe_count($pdo, "SELECT COUNT(*) FROM expression_orale WHERE kind = 'exam'");
        } else {
            $ceTotal = sa_safe_count($pdo, 'SELECT COUNT(*) FROM tcf_ce_exams');
            $coTotal = sa_safe_count($pdo, 'SELECT COUNT(*) FROM tcf_co_exams');
            $eeTotal = sa_safe_count($pdo, 'SELECT COUNT(*) FROM tcf_ee_exams');
            $eoTotal = sa_safe_count($pdo, 'SELECT COUNT(*) FROM tcf_eo_exams');
        }
        $examsTotal = $ceTotal + $coTotal + $eeTotal + $eoTotal;

        $activitiesCount = 0;
        if (tcf_schema_has_table($pdo, 'activites')) {
            $activitiesCount = sa_safe_count($pdo, "SELECT COUNT(*) FROM activites WHERE kind = 'log'");
        } else {
            $activitiesCount = sa_safe_count($pdo, 'SELECT COUNT(*) FROM activities');
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'range' => $range,
                'users' => $usersCount,
                'users_active' => $usersActive,
                'admins' => $adminsCount,
                'visitors' => $visitorsCount,
                'subs' => $subsCount,
                'revenue' => $revenue,
                'revenue_subscription_demo' => $revenueSubsMonth,
                'revenue_payments_gateway' => $revenuePayments,
                'videos' => $videosTotal,
                'video_views' => $videoViews,
                'testimonials' => $testimonialsTotal,
                'testimonials_published' => $testimonialsPub,
                'partners' => $partnersTotal,
                'partners_published' => $partnersPub,
                'announcements' => $annTotal,
                'announcements_published' => $annPub,
                'plans' => $plansTotal,
                'plans_active' => $plansActive,
                'payments' => $paymentsCount,
                'activities' => $activitiesCount,
                'exams' => $examsTotal,
                'exams_by_skill' => [
                    'ce' => $ceTotal,
                    'co' => $coTotal,
                    'ee' => $eeTotal,
                    'eo' => $eoTotal,
                ],
                'charts' => [
                    'platform_mix' => [
                        'labels' => [
                            'Utilisateurs',
                            'Vidéos',
                            'Épreuves',
                            'Témoignages',
                            'Partenaires',
                            'Annonces',
                            'Forfaits',
                        ],
                        'values' => [
                            $usersCount,
                            $videosTotal,
                            $examsTotal,
                            $testimonialsTotal,
                            $partnersTotal,
                            $annTotal,
                            $plansTotal,
                        ],
                    ],
                ],
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
        $payTable = tcf_subscription_payments_table($pdo);
        $paySel = tcf_subscription_payments_select_sql($payTable, 'sp');
        $payWhere = tcf_subscription_payments_history_where($payTable, 'sp');
        $stmt = $pdo->query(
            $paySel . ", u.name AS user_name, u.email AS user_email
             FROM `{$payTable}` sp
             LEFT JOIN users u ON u.id = sp.user_id
             WHERE ({$payWhere})
             ORDER BY sp.created_at DESC
             LIMIT 500"
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $rows], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        error_log('getSubscriptionPaymentsAdmin: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'data' => [],
            'message' => 'Impossible de charger l’historique des paiements.',
        ], JSON_UNESCAPED_UNICODE);
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
        $payTable = tcf_subscription_payments_table($pdo);
        $payWhere = tcf_subscription_payments_revenue_where($payTable);
        $stM = $pdo->query(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COALESCE(SUM(amount), 0) AS total
             FROM `{$payTable}`
             WHERE ({$payWhere})
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
        $payTable = tcf_subscription_payments_table($pdo);
        $payWhere = tcf_subscription_payments_revenue_where($payTable);
        $total = (float) $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM `{$payTable}` WHERE ({$payWhere})")->fetchColumn();
        $month = (float) $pdo->query(
            "SELECT COALESCE(SUM(amount), 0) FROM `{$payTable}` WHERE ({$payWhere}) AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())"
        )->fetchColumn();
        $count = (int) $pdo->query("SELECT COUNT(*) FROM `{$payTable}` WHERE ({$payWhere})")->fetchColumn();
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
            'message' => $disabled ? 'Désactivée' : 'Activée',
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
        $table = tcf_subscription_plans_table($pdo);
        $mxSt = $pdo->query("SELECT COALESCE(MAX(sort_order), 0) FROM `{$table}`");
        $mx = $mxSt ? (int) $mxSt->fetchColumn() : 0;
        if ($table === 'abonnements') {
            $st = $pdo->prepare(
                'INSERT INTO abonnements (plan_key, tier, badge, title, price_label, price_xaf, duration_days, features_json, sort_order, is_active) VALUES (?, ?, ?, ?, ?, 0, 7, ?, ?, 1)'
            );
            $st->execute([$planKey, $tier, $badge, $badge !== '' ? $badge : $tier, '$', $feats, $mx + 1]);
        } else {
            $st = $pdo->prepare(
                'INSERT INTO subscription_plan_catalog (plan_key, tier, badge, price, currency, duration_days, features_json, sort_order, is_active) VALUES (?, ?, ?, 0, ?, 7, ?, ?, 1)'
            );
            $st->execute([$planKey, $tier, $badge, '$', $feats, $mx + 1]);
        }
        echo json_encode(['success' => true, 'message' => 'Forfait ajouté. Complétez les informations puis enregistrez.']);
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        if (str_contains($msg, 'abonnements') || str_contains($msg, 'subscription_plan_catalog') || str_contains($msg, 'Unknown table')) {
            echo json_encode(['success' => false, 'message' => 'Table catalogue absente — importez database/tcf.sql.']);
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
        $table = tcf_subscription_plans_table($pdo);
        $st = $pdo->prepare("SELECT plan_key FROM `{$table}` WHERE id = ?");
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
        $pdo->prepare("DELETE FROM `{$table}` WHERE id = ?")->execute([$id]);
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
    // Devise catalogue : toujours le dollar (affichage plateforme).
    $currency = '$';
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
        $table = tcf_subscription_plans_table($pdo);
        $chk = $pdo->prepare("SELECT id FROM `{$table}` WHERE id = ?");
        $chk->execute([$id]);
        if (!$chk->fetchColumn()) {
            echo json_encode(['success' => false, 'message' => 'Formule introuvable (id).']);
            exit();
        }
        if ($table === 'abonnements') {
            $st = $pdo->prepare(
                'UPDATE abonnements SET tier = ?, badge = ?, title = ?, price_label = ?, price_xaf = ?, duration_days = ?, features_json = ?, sort_order = ?, is_active = ? WHERE id = ?'
            );
            $st->execute([$tier, $badge, $badge !== '' ? $badge : $tier, $currency, (int) round($price), $duration, $featuresJson, $sortOrder, $isActive, $id]);
        } else {
            $st = $pdo->prepare(
                'UPDATE subscription_plan_catalog SET tier = ?, badge = ?, price = ?, currency = ?, duration_days = ?, features_json = ?, sort_order = ?, is_active = ? WHERE id = ?'
            );
            $st->execute([$tier, $badge, $price, $currency, $duration, $featuresJson, $sortOrder, $isActive, $id]);
        }
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
        if (tcf_schema_has_table($pdo, 'activites')) {
            $stmt = $pdo->prepare(
                "
            SELECT a.id, a.user_id, a.type, a.title, a.description, a.icon, a.created_at,
                   u.name AS user_name, u.email AS user_email
            FROM activites a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE a.kind = 'log'
            ORDER BY a.created_at DESC, a.id DESC
            LIMIT {$lim}"
            );
        } else {
            $stmt = $pdo->prepare(
                "
            SELECT a.id, a.user_id, a.type, a.title, a.description, a.icon, a.created_at,
                   u.name AS user_name, u.email AS user_email
            FROM activities a
            LEFT JOIN users u ON a.user_id = u.id
            ORDER BY a.created_at DESC, a.id DESC
            LIMIT {$lim}"
            );
        }
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
                "SELECT COUNT(*) FROM notifications WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0 AND type IN ('video', 'topic', 'message', 'user', 'update', 'video_comment', 'testimonial', 'subscription', 'subscription_staff', 'exam')"
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

        tcf_log_activity($pdo, $user_id !== null ? (int) $user_id : null, $type, $title, $description, $icon);
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
if (!function_exists('tcf_auth_flash_consume')) {
    require_once dirname(__DIR__) . '/includes/auth_flash.php';
}
$auth_flash = tcf_auth_flash_consume();
if (empty($profile_flash) && !empty($auth_flash)) {
    $profile_flash = $auth_flash;
}

$tcf_profile_panel_user = null;
try {
    $stmtSaProf = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmtSaProf->execute([(int) $_SESSION['user_id']]);
    $tcf_profile_panel_user = $stmtSaProf->fetch(PDO::FETCH_ASSOC);
    if ($tcf_profile_panel_user) {
        $saAvUrl = tcf_user_avatar_display_url($pdo, (int) $tcf_profile_panel_user['id'], $tcf_profile_panel_user['avatar'] ?? null);
        $tcf_profile_panel_user['avatar_resolved'] = $saAvUrl ? ($tcf_profile_panel_user['avatar'] ?? '1') : null;
        $tcf_profile_panel_user['avatar_display_url'] = $saAvUrl;
    }
} catch (Throwable $e) {
    $tcf_profile_panel_user = null;
}

$tcf_sa_nav_unread = 0;
if ($tcf_profile_panel_user) {
    try {
        $navQuery = "SELECT COUNT(*) FROM notifications WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0 AND type IN ('video', 'topic', 'message', 'user', 'update', 'video_comment', 'testimonial', 'subscription', 'subscription_staff', 'exam')";
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
        $u['avatar_url'] = tcf_user_avatar_display_url($pdo, (int) $u['id'], $u['avatar'] ?? null);
        $u['is_online'] = tcf_user_is_online(isset($u['last_activity']) ? (string) $u['last_activity'] : null);
    }
    unset($u);
    try {
        $videos = tcf_videos_normalize_list_rows(
            $pdo->query(tcf_videos_list_select_sql('v') . ' FROM videos v ORDER BY v.created_at DESC')->fetchAll(PDO::FETCH_ASSOC) ?: []
        );
        foreach ($videos as &$vid) {
            $vid['thumbnail_href'] = tcf_video_media_href($pdo, (int) ($vid['id'] ?? 0), $vid['thumbnail_url'] ?? '', 'thumbnail');
            $vid['video_href'] = tcf_video_media_href($pdo, (int) ($vid['id'] ?? 0), $vid['video_url'] ?? '', 'video');
        }
        unset($vid);
        try {
            $videos = tcf_enrich_videos_with_playlists($pdo, $videos);
        } catch (Throwable $e) {
        }
    } catch (Throwable $e) {
        $videos = [];
    }
    $topics = [];
    try {
        if (tcf_schema_has_table($pdo, 'sujets')) {
            $topics = $pdo->query("SELECT id, title, slug, type, visibility, is_published, created_at FROM sujets ORDER BY created_at DESC LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Throwable $e) {
        $topics = [];
    }
    try {
        $admins = $pdo->query("SELECT id, name, email, role, status, avatar, last_login, last_activity, created_at FROM users WHERE role IN ('admin', 'super_admin')")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $admins = $pdo->query("SELECT id, name, email, role, status, avatar, last_login, created_at FROM users WHERE role IN ('admin', 'super_admin')")->fetchAll(PDO::FETCH_ASSOC);
    }
    foreach ($admins as &$a) {
        $a['avatar_url'] = tcf_user_avatar_display_url($pdo, (int) $a['id'], $a['avatar'] ?? null);
        $a['is_online'] = tcf_user_is_online(isset($a['last_activity']) ? (string) $a['last_activity'] : null);
    }
    unset($a);
    $messages = [];
    try {
        if (tcf_schema_has_table($pdo, 'annonces')) {
            $messages = $pdo->query("SELECT id, body, visibility, is_published, created_by, created_at FROM annonces WHERE kind='message' ORDER BY created_at DESC LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Throwable $e) {
        $messages = [];
    }
    $activities = [];
    if ($isSuperAdmin) {
        $notifications = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $notifications = $pdo->query(
            "SELECT * FROM notifications
             WHERE type IN ('video', 'topic', 'message', 'user', 'update', 'video_comment', 'testimonial', 'subscription', 'subscription_staff', 'exam')
             ORDER BY created_at DESC LIMIT 20"
        )->fetchAll(PDO::FETCH_ASSOC);
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
    $tcf_brand_desc = 'Espace d\'administration ELITE TCF CANADA — gestion utilisateurs, vidéos, sujets et annonces.';
    $tcf_seo_robots = 'noindex, nofollow';
    $tcf_seo_skip_title = true;
    include __DIR__ . '/../includes/tcf_brand_head.php';
    ?>
    <title><?php echo htmlspecialchars($tcf_brand_title); ?></title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../Assets/css/sa-theme.css">
    <script src="../Assets/javascript/sa-theme.js"></script>
    <link rel="stylesheet" href="../Assets/css/superAdmin.css?v=sa-ui-v9">
    <link rel="stylesheet" href="../Assets/css/tcf-brand-logo.css">
    <link rel="stylesheet" href="../Assets/css/sa_subscription_plans.css?v=usd-fixed-2">
    <link rel="stylesheet" href="../Assets/css/sa-partners.css?v=partners-16x9-contain-5">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/profile_panel.css')); ?>?v=notif-tout-lu-14">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/tcf-responsive-pills.css')); ?>">
    <link rel="stylesheet" href="../Assets/css/admin-mobile-nav.css?v=sa-notif-nav-15">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/tcf-ui-layers.css')); ?>?v=sa-notif-layers-14">
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
            <div class="menu-item" data-target="admins">
                <i class='bx bxs-shield'></i>
                <span>Gestion des administrateurs</span>
            </div>
        <?php endif; ?>

        <div class="menu-item" id="videos-menu">
            <i class='bx bxs-video'></i>
            <span>Gestion Vidéos</span>
            <i class='bx bx-chevron-down' style="margin-left: auto;"></i>
        </div>
        <div class="sub-menu" id="videos-submenu">
            <div class="sub-item" data-target="videos">Vidéos</div>
            <div class="sub-item" data-target="analytics">Analyse vidéo</div>
        </div>

        <?php if ($isSuperAdmin): ?>
        <div class="menu-item" data-target="testimonials">
            <i class='bx bxs-quote-left' aria-hidden="true"></i>
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
            <span>Annonces</span>
        </div>
        <div class="menu-item" data-target="partners">
            <i class='bx bxs-briefcase' aria-hidden="true"></i>
            <span>Partenaires</span>
        </div>
        <?php else: ?>
        <div class="menu-item" data-target="messages">
            <i class='bx bxs-megaphone'></i>
            <span>Annonces</span>
        </div>
        <?php endif; ?>

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
                    <span class="admin-nav-avatar-wrap nav-avatar-wrap">
                        <?php if (!empty($tcf_profile_panel_user['avatar_display_url'])): ?>
                            <img src="<?php echo htmlspecialchars($tcf_profile_panel_user['avatar_display_url']); ?>" alt="" class="admin-nav-avatar-img nav-avatar-img" width="40" height="40" loading="lazy" decoding="async">
                        <?php else: ?>
                            <span class="admin-nav-avatar-fallback nav-avatar-fallback"><i class="bx bx-user" aria-hidden="true"></i></span>
                        <?php endif; ?>
                    </span>
                </span>
            </div>
        </div>

        <!-- Dashboard Section -->
        <div id="dashboard" class="content-section active">
            <?php if ($isSuperAdmin): ?>
            <div class="sa-admin-dash" id="sa-super-dash">
                <div class="sa-super-toolbar">
                    <label class="sa-super-toolbar__period">
                        <span>Période</span>
                        <select id="trace-range" class="form-control" aria-label="Période des statistiques">
                            <option value="today" selected>Aujourd’hui</option>
                            <option value="7d">7 jours</option>
                            <option value="30d">1 mois</option>
                            <option value="90d">3 mois</option>
                            <option value="year">1 an</option>
                        </select>
                    </label>
                </div>

                <div class="stats-container sa-admin-dash__stats sa-super-kpi-grid" aria-label="Statistiques plateforme">
                    <button type="button" class="stat-card sa-kpi-card" data-goto="users">
                        <div class="stat-icon sa-admin-ico--visit"><i class='bx bxs-user'></i></div>
                        <div class="stat-info">
                            <h3 id="users-count">0</h3>
                            <p>Utilisateurs</p>
                            <span class="sa-kpi-card__meta" id="sa-mod-users-meta">0 actifs</span>
                        </div>
                    </button>
                    <div class="stat-card sa-kpi-card sa-kpi-card--accent" id="sa-visitors-card">
                        <div class="stat-icon sa-admin-ico--views"><i class='bx bxs-group'></i></div>
                        <div class="stat-info">
                            <h3 id="visitors-count">0</h3>
                            <p id="visitors-label">Visiteurs</p>
                        </div>
                    </div>
                    <button type="button" class="stat-card sa-kpi-card" data-goto="subscription-revenue">
                        <div class="stat-icon sa-admin-ico--exam"><i class='bx bxs-crown'></i></div>
                        <div class="stat-info">
                            <h3 id="subs-count">0</h3>
                            <p>Abonnements actifs</p>
                        </div>
                    </button>
                    <button type="button" class="stat-card sa-kpi-card" data-goto="subscription-revenue">
                        <div class="stat-icon sa-admin-ico--revenue"><i class='bx bxs-dollar-circle'></i></div>
                        <div class="stat-info">
                            <h3 id="revenue-count">$0.00</h3>
                            <p>Revenu du mois ($)</p>
                        </div>
                    </button>
                    <button type="button" class="stat-card sa-kpi-card" data-goto="admins">
                        <div class="stat-icon sa-admin-ico--admin"><i class='bx bxs-shield'></i></div>
                        <div class="stat-info">
                            <h3 id="sa-mod-admins">0</h3>
                            <p>Administrateurs</p>
                        </div>
                    </button>
                    <button type="button" class="stat-card sa-kpi-card" data-goto="videos">
                        <div class="stat-icon sa-admin-ico--video"><i class='bx bxs-video'></i></div>
                        <div class="stat-info">
                            <h3 id="sa-mod-videos">0</h3>
                            <p>Vidéos</p>
                            <span class="sa-kpi-card__meta" id="sa-mod-videos-meta">0 vues</span>
                        </div>
                    </button>
                    <button type="button" class="stat-card sa-kpi-card" data-goto="testimonials">
                        <div class="stat-icon sa-admin-ico--quote"><i class='bx bxs-quote-left'></i></div>
                        <div class="stat-info">
                            <h3 id="sa-mod-testimonials">0</h3>
                            <p>Témoignages</p>
                            <span class="sa-kpi-card__meta" id="sa-mod-testimonials-meta">0 publiés</span>
                        </div>
                    </button>
                    <button type="button" class="stat-card sa-kpi-card" data-goto="partners">
                        <div class="stat-icon sa-admin-ico--partner"><i class='bx bxs-briefcase'></i></div>
                        <div class="stat-info">
                            <h3 id="sa-mod-partners">0</h3>
                            <p>Partenaires</p>
                            <span class="sa-kpi-card__meta" id="sa-mod-partners-meta">0 publiés</span>
                        </div>
                    </button>
                    <button type="button" class="stat-card sa-kpi-card" data-goto="messages">
                        <div class="stat-icon sa-admin-ico--msg"><i class='bx bxs-megaphone'></i></div>
                        <div class="stat-info">
                            <h3 id="sa-mod-announcements">0</h3>
                            <p>Annonces</p>
                            <span class="sa-kpi-card__meta" id="sa-mod-announcements-meta">0 publiées</span>
                        </div>
                    </button>
                    <button type="button" class="stat-card sa-kpi-card" data-goto="subscription-plans">
                        <div class="stat-icon sa-admin-ico--plan"><i class='bx bx-credit-card'></i></div>
                        <div class="stat-info">
                            <h3 id="sa-mod-plans">0</h3>
                            <p>Forfaits</p>
                            <span class="sa-kpi-card__meta" id="sa-mod-plans-meta">0 actifs</span>
                        </div>
                    </button>
                    <button type="button" class="stat-card sa-kpi-card" data-goto="subscription-payments">
                        <div class="stat-icon sa-admin-ico--pay"><i class='bx bx-receipt'></i></div>
                        <div class="stat-info">
                            <h3 id="sa-mod-payments">0</h3>
                            <p>Paiements</p>
                        </div>
                    </button>
                    <button type="button" class="stat-card sa-kpi-card" data-goto="topics-written">
                        <div class="stat-icon sa-admin-ico--ce"><i class='bx bxs-book'></i></div>
                        <div class="stat-info">
                            <h3 id="sa-mod-ce">0</h3>
                            <p>CE</p>
                        </div>
                    </button>
                    <button type="button" class="stat-card sa-kpi-card" data-goto="topics-oral">
                        <div class="stat-icon sa-admin-ico--co"><i class='bx bxs-headphones'></i></div>
                        <div class="stat-info">
                            <h3 id="sa-mod-co">0</h3>
                            <p>CO</p>
                        </div>
                    </button>
                    <button type="button" class="stat-card sa-kpi-card" data-goto="topics-expression">
                        <div class="stat-icon sa-admin-ico--ee"><i class='bx bxs-pencil'></i></div>
                        <div class="stat-info">
                            <h3 id="sa-mod-ee">0</h3>
                            <p>EE</p>
                        </div>
                    </button>
                    <button type="button" class="stat-card sa-kpi-card" data-goto="topics-speaking">
                        <div class="stat-icon sa-admin-ico--eo"><i class='bx bxs-microphone'></i></div>
                        <div class="stat-info">
                            <h3 id="sa-mod-eo">0</h3>
                            <p>EO</p>
                        </div>
                    </button>
                </div>

                <div class="charts-section sa-admin-dash__charts">
                    <div class="chart-card sa-super-overview-card">
                        <div class="chart-header">
                            <div class="chart-title">Activité plateforme</div>
                        </div>
                        <div class="chart-container sa-admin-chart sa-admin-chart--overview">
                            <canvas id="saOverviewChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="charts-section sa-admin-dash__charts">
                    <div class="chart-card">
                        <div class="chart-header">
                            <div class="chart-title">Top pays — visiteurs uniques</div>
                        </div>
                        <div class="chart-container sa-admin-chart">
                            <canvas id="traceCountriesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Dashboard contenu (admin) -->
            <div class="sa-admin-dash" id="sa-admin-dash">
                <header class="sa-admin-dash__hero">
                    <div>
                        <p class="sa-admin-dash__kicker">Espace administrateur</p>
                        <h2 class="sa-admin-dash__title">Tableau de bord contenu</h2>
                    </div>
                </header>

                <div class="stats-container sa-admin-dash__stats">
                    <div class="stat-card">
                        <div class="stat-icon sa-admin-ico--video"><i class='bx bxs-video'></i></div>
                        <div class="stat-info">
                            <h3 id="adm-dash-videos">0</h3>
                            <p>Vidéos publiées</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon sa-admin-ico--exam"><i class='bx bxs-book-open'></i></div>
                        <div class="stat-info">
                            <h3 id="adm-dash-exams">0</h3>
                            <p>Épreuves (toutes compétences)</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon sa-admin-ico--views"><i class='bx bx-show'></i></div>
                        <div class="stat-info">
                            <h3 id="adm-dash-exam-views">0</h3>
                            <p>Consultations d’épreuves</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon sa-admin-ico--msg"><i class='bx bxs-megaphone'></i></div>
                        <div class="stat-info">
                            <h3 id="adm-dash-announcements">0</h3>
                            <p>Annonces</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon sa-admin-ico--visit"><i class='bx bxs-group'></i></div>
                        <div class="stat-info">
                            <h3 id="adm-dash-visitors">0</h3>
                            <p>Visiteurs aujourd’hui</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon sa-admin-ico--comment"><i class='bx bxs-comment-detail'></i></div>
                        <div class="stat-info">
                            <h3 id="adm-dash-comments">0</h3>
                            <p>Commentaires vidéos</p>
                        </div>
                    </div>
                </div>

                <div class="sa-admin-dash__skills" aria-label="Répartition des épreuves">
                    <article class="sa-admin-skill" data-goto="topics-written">
                        <span class="sa-admin-skill__label">Compréhension écrite</span>
                        <strong id="adm-dash-ce">0</strong>
                        <span class="sa-admin-skill__meta" id="adm-dash-ce-meta">0 publiées · 0 vues</span>
                    </article>
                    <article class="sa-admin-skill" data-goto="topics-oral">
                        <span class="sa-admin-skill__label">Compréhension orale</span>
                        <strong id="adm-dash-co">0</strong>
                        <span class="sa-admin-skill__meta" id="adm-dash-co-meta">0 publiées · 0 vues</span>
                    </article>
                    <article class="sa-admin-skill" data-goto="topics-expression">
                        <span class="sa-admin-skill__label">Expression écrite</span>
                        <strong id="adm-dash-ee">0</strong>
                        <span class="sa-admin-skill__meta" id="adm-dash-ee-meta">0 publiées · 0 vues</span>
                    </article>
                    <article class="sa-admin-skill" data-goto="topics-speaking">
                        <span class="sa-admin-skill__label">Expression orale</span>
                        <strong id="adm-dash-eo">0</strong>
                        <span class="sa-admin-skill__meta" id="adm-dash-eo-meta">0 publiées · 0 vues</span>
                    </article>
                </div>

                <div class="charts-section sa-admin-dash__charts">
                    <div class="chart-card">
                        <div class="chart-header">
                            <div class="chart-title">Répartition du contenu</div>
                        </div>
                        <div class="chart-container sa-admin-chart">
                            <canvas id="admDashMixChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-card">
                        <div class="chart-header">
                            <div class="chart-title">Consultations par compétence</div>
                        </div>
                        <div class="chart-container sa-admin-chart">
                            <canvas id="admDashSkillViewsChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="charts-section sa-admin-dash__charts">
                    <div class="chart-card trace-chart-wide">
                        <div class="chart-header">
                            <div class="chart-title">Consultations d’épreuves — 14 jours</div>
                        </div>
                        <div class="chart-container sa-admin-chart sa-admin-chart--tall">
                            <canvas id="admDashTrendChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-card">
                        <div class="chart-header">
                            <div class="chart-title">Vidéos les plus vues</div>
                        </div>
                        <div class="chart-container sa-admin-chart sa-admin-chart--tall">
                            <canvas id="admDashTopVideosChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="sa-admin-dash__actions">
                    <button type="button" class="sa-admin-action" data-goto="videos"><i class='bx bxs-video'></i> Gérer les vidéos</button>
                    <button type="button" class="sa-admin-action" data-goto="topics-written"><i class='bx bxs-book'></i> Gérer les épreuves</button>
                    <button type="button" class="sa-admin-action" data-goto="messages"><i class='bx bxs-megaphone'></i> Annonces</button>
                    <button type="button" class="sa-admin-action" data-goto="analytics"><i class='bx bx-bar-chart-alt-2'></i> Analyse vidéo</button>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Journal d'activité admin (déplacé depuis le tableau de bord) -->
        <div id="recent-activity" class="content-section" style="display:none;" aria-hidden="true">
            <div class="dashboard-section sa-activity-page">
                <div class="section-header sa-activity-page-head">
                    <div class="sa-activity-page-intro">
                        <div class="section-title">Activité récente</div>
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

        <!-- Comptes admin / super_admin -->
        <?php if ($isSuperAdmin): ?>
        <div id="admins" class="content-section" style="display:none;">
            <div class="dashboard-section">
                <div class="section-header">
                    <div class="section-title">Gestion des administrateurs</div>
                    <button type="button" class="btn btn-primary" id="add-admin-btn">
                        <i class='bx bx-plus'></i> Ajouter un administrateur
                    </button>
                </div>

                <div class="sa-admin-form-panel" id="admin-form-panel" hidden>
                    <div class="sa-admin-form-panel__head">
                        <h3 class="sa-admin-form-panel__title" id="admin-form-title">Ajouter un administrateur</h3>
                        <button type="button" class="btn btn-outline btn-sm" id="admin-form-cancel">Fermer</button>
                    </div>
                    <form id="admin-form-modal" class="sa-admin-form-inline">
                        <input type="hidden" id="admin-edit-id">
                        <div class="sa-admin-form-grid">
                            <div class="form-group">
                                <label class="form-label" for="admin-name">Nom complet</label>
                                <input type="text" class="form-control" id="admin-name" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="admin-email">Email</label>
                                <input type="email" class="form-control" id="admin-email" required>
                            </div>
                            <div class="form-group admin-password-fields">
                                <label class="form-label" for="admin-password">Mot de passe</label>
                                <input type="password" class="form-control" id="admin-password" autocomplete="new-password" minlength="8">
                            </div>
                            <div class="form-group admin-password-fields">
                                <label class="form-label" for="admin-password-confirm">Confirmer le mot de passe</label>
                                <input type="password" class="form-control" id="admin-password-confirm" autocomplete="new-password" minlength="8">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="admin-role">Rôle</label>
                                <select class="form-control" id="admin-role" required>
                                    <option value="admin">Administrateur</option>
                                    <option value="super_admin">Super Administrateur</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="admin-status">Statut</label>
                                <select class="form-control" id="admin-status">
                                    <option value="active">Actif</option>
                                    <option value="inactive">Inactif</option>
                                </select>
                            </div>
                        </div>
                        <div class="sa-admin-form-actions">
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
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
                    <div class="form-group sa-video-title-group">
                        <label class="form-label" for="video-title">Titre de la vidéo</label>
                        <input type="text" class="form-control" id="video-title" name="title" required autocomplete="off" aria-describedby="video-title-counter">
                        <div class="sa-video-title-meta">
                            <span id="video-title-hint" class="sa-video-title-hint">Comme YouTube : maximum 100 caractères</span>
                            <span id="video-title-counter" class="sa-video-title-counter" aria-live="polite">100</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description <span style="font-weight:400;color:#64748b;">(optionnel)</span></label>
                        <textarea class="form-control" id="video-description" name="description" rows="3" placeholder="Résumé ou contexte de la vidéo (facultatif)"></textarea>
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

        <!-- Témoignages -->
        <div id="testimonials" class="content-section" style="display:none;">
            <div class="dashboard-section">
                <div class="section-header">
                    <div class="section-title">Témoignages</div>
                    <button type="button" class="btn btn-primary" id="sa-testi-refresh-btn">
                        <i class="bx bx-refresh"></i> Actualiser
                    </button>
                </div>
                <div class="sa-testi-stats" id="sa-testi-stats">
                    <div class="sa-testi-stat-card">
                        <i class="bx bxs-comment-detail"></i>
                        <div>
                            <span class="sa-testi-stat-val" id="sa-testi-count">—</span>
                            <span class="sa-testi-stat-lbl">Total</span>
                        </div>
                    </div>
                    <div class="sa-testi-stat-card">
                        <i class="bx bxs-star"></i>
                        <div>
                            <span class="sa-testi-stat-val" id="sa-testi-avg">—</span>
                            <span class="sa-testi-stat-lbl">Note moyenne</span>
                        </div>
                    </div>
                    <div class="sa-testi-stat-card">
                        <i class="bx bxs-award"></i>
                        <div>
                            <span class="sa-testi-stat-val" id="sa-testi-five">—</span>
                            <span class="sa-testi-stat-lbl">5 étoiles</span>
                        </div>
                    </div>
                </div>

                <div class="sa-testi-toolbar">
                    <div class="sa-testi-search-wrap">
                        <i class="bx bx-search"></i>
                        <input type="search" id="sa-testi-search" placeholder="Rechercher auteur ou contenu…" autocomplete="off">
                    </div>
                    <select id="sa-testi-filter-rating" class="sa-testi-select">
                        <option value="">Toutes les notes</option>
                        <option value="5">5 étoiles</option>
                        <option value="4">4 étoiles</option>
                        <option value="3">3 étoiles</option>
                        <option value="2">2 étoiles</option>
                        <option value="1">1 étoile</option>
                        <option value="0">Sans note</option>
                    </select>
                </div>

                <div class="sa-testi-grid" id="sa-testi-grid">
                    <div class="sa-testi-loading">
                        <i class="bx bx-loader-alt bx-spin"></i> Chargement…
                    </div>
                </div>
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
                    <div class="sa-sub-pro-toolbar sa-sub-pro-toolbar--inline">
                        <button type="button" class="btn btn-primary" id="sa-sub-platform-toggle-btn" data-disabled="0">
                            <i class="bx bx-check-circle"></i> <span id="sa-sub-platform-toggle-label">Activée</span>
                        </button>
                        <button type="button" class="btn btn-primary" id="sa-sub-add-plan-btn">
                            <i class="bx bx-plus"></i> Ajouter un forfait
                        </button>
                    </div>
                </div>
                <div id="sa-sub-platform-toggle" hidden aria-hidden="true">
                    <p id="sa-sub-platform-toggle-desc"></p>
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

        <!-- Annonces -->
        <div id="messages" class="content-section" style="display:none;">
            <div class="dashboard-section">
                <div class="section-header">
                    <div class="section-title">Annonces</div>
                    <button type="button" class="btn btn-primary" id="add-message-btn">
                        <i class='bx bx-plus'></i> Nouvelle annonce
                    </button>
                </div>

                <form id="message-form" style="display:none;" enctype="multipart/form-data">
                    <input type="hidden" id="message-edit-id" value="">
                    <div class="form-group">
                        <label class="form-label" for="message-content">Texte / description</label>
                        <textarea class="form-control" id="message-content" rows="6" maxlength="8000" required placeholder="Écrivez votre annonce…&#10;Entrée = nouvelle ligne"></textarea>
                        <small style="color:#64748b;display:block;margin-top:6px;">Astuce : appuyez sur Entrée pour passer à la ligne suivante.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="message-link">Lien (optionnel)</label>
                        <input type="url" class="form-control" id="message-link" maxlength="1000" placeholder="https://exemple.com/page" inputmode="url" autocomplete="url">
                        <small style="color:#64748b;display:block;margin-top:6px;">Affiché sous le texte, cliquable pour les lecteurs.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="message-image">Image (optionnel — JPG, PNG, WebP, GIF)</label>
                        <input type="file" class="form-control" id="message-image" accept="image/jpeg,image/png,image/webp,image/gif">
                        <img id="message-image-preview" alt="" style="display:none;max-height:160px;margin-top:8px;border-radius:8px;border:1px solid #e2e8f0;">
                        <label style="display:none;margin-top:8px;font-size:13px;font-weight:400;" id="message-clear-image-wrap">
                            <input type="checkbox" id="message-clear-image" value="1">
                            Retirer l’image actuelle
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="message-visibility">Qui peut voir cette annonce ?</label>
                        <select class="form-control" id="message-visibility">
                            <option value="registered">Membres inscrits</option>
                            <option value="premium">Abonnés payants uniquement</option>
                            <option value="visitors">Tout le monde (visiteurs inclus)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="message-published">Statut</label>
                        <select class="form-control" id="message-published">
                            <option value="1">Publiée</option>
                            <option value="0">Brouillon</option>
                        </select>
                    </div>
                    <div class="form-buttons">
                        <button type="button" class="btn btn-outline" id="cancel-message-btn">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>

                <div class="section-header" style="margin-top: 30px;">
                    <div class="section-title">Annonces publiées</div>
                </div>
                <div class="sa-msg-grid" id="messages-container"></div>
            </div>
        </div>

        <!-- Partenaires (logos page d'accueil) -->
        <div id="partners" class="content-section" style="display:none;">
            <div class="dashboard-section">
                <div class="section-header">
                    <div class="section-title">Partenaires</div>
                    <button type="button" class="btn btn-primary" id="add-partner-btn">
                        <i class="bx bx-plus"></i> Ajouter un partenaire
                    </button>
                </div>

                <form id="partner-form" style="display:none;" enctype="multipart/form-data">
                    <input type="hidden" id="partner-edit-id" value="">
                    <div class="form-group">
                        <label class="form-label" for="partner-name">Nom de l’entreprise</label>
                        <input type="text" class="form-control" id="partner-name" maxlength="160" required placeholder="Ex. Nom de l’entreprise">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="partner-logo">Logo (JPG, PNG, WebP, GIF — max. 4 Mo)</label>
                        <input type="file" class="form-control" id="partner-logo" accept="image/jpeg,image/png,image/webp,image/gif">
                        <img id="partner-logo-preview" alt="Aperçu du logo">
                        <small style="color:#64748b;display:block;margin-top:6px;" id="partner-logo-hint">Obligatoire à la création. En modification, laissez vide pour conserver le logo actuel.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="partner-website">Site web (optionnel)</label>
                        <input type="url" class="form-control" id="partner-website" maxlength="1000" placeholder="https://entreprise.com" inputmode="url" autocomplete="url">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="partner-sort">Ordre d’affichage</label>
                        <input type="number" class="form-control" id="partner-sort" value="0" step="1">
                        <small style="color:#64748b;display:block;margin-top:6px;">Plus le chiffre est petit, plus le logo apparaît tôt.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="partner-published">Statut</label>
                        <select class="form-control" id="partner-published">
                            <option value="1">Publié sur l’accueil</option>
                            <option value="0">Brouillon (masqué)</option>
                        </select>
                    </div>
                    <div class="form-buttons">
                        <button type="button" class="btn btn-outline" id="cancel-partner-btn">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>

                <div class="section-header" style="margin-top: 28px;">
                    <div class="section-title">Logos publiés</div>
                </div>
                <div class="sa-partners-grid" id="partners-container"></div>
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

                <!-- Expression écrite (BDD) -->
                <div id="ee-admin-manager" style="display:none; margin-top: 12px;">
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

                    <form id="ee-exam-json-form" style="display:none;">
                        <input type="hidden" id="ee-json-exam-id">
                        <div class="form-group">
                            <label class="form-label">Titre de l'épreuve</label>
                            <input type="text" class="form-control" id="ee-json-exam-title" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sous-titre (optionnel)</label>
                            <input type="text" class="form-control" id="ee-json-exam-subtitle">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Visibilité</label>
                            <select class="form-control" id="ee-json-exam-visibility">
                                <option value="gratuit">Gratuit</option>
                                <option value="premium">Premium</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="display:flex;align-items:center;gap:.5rem;">
                                <input type="checkbox" id="ee-json-exam-published" checked>
                                <span>Publier immédiatement</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fichier JSON des combinaisons</label>
                            <input type="file" class="form-control" id="ee-json-file" accept=".json,application/json">
                            <small style="color:#64748b;display:block;margin-top:6px;">Tableau de combinaisons, ou objet <code>{"combinations":[...]}</code>. Chaque combinaison : title, tasks[{task_number, prompt, correction, documents[{title, content}]}].</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ou coller le JSON</label>
                            <textarea class="form-control" id="ee-json-paste" rows="8" placeholder='[{"title":"Combinaison 1","tasks":[{"task_number":1,"prompt":"...","documents":[{"content":"..."}]}]}]'></textarea>
                        </div>
                        <div class="form-buttons">
                            <button type="button" class="btn btn-outline" id="ee-json-cancel-btn">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer depuis JSON</button>
                        </div>
                    </form>

                    <div class="dashboard-section" style="margin-top:16px;">
                        <div class="section-header">
                            <div class="section-title">Consignes Expression Écrite</div>
                            <button type="button" class="btn btn-primary" id="ee-open-consignes-btn"><i class='bx bx-edit'></i> Modifier les consignes</button>
                        </div>
                        <form id="ee-consignes-bundle-form" style="display:none;">
                            <div class="form-group">
                                <label class="form-label">Consigne Tâche 1 — Message court (60-120 mots)</label>
                                <textarea class="form-control" id="ee-consigne-tache1" rows="12" required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Consigne Tâche 2 — Article / narration (120-150 mots)</label>
                                <textarea class="form-control" id="ee-consigne-tache2" rows="12" required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Consigne Tâche 3 — Texte argumentatif (120-180 mots)</label>
                                <textarea class="form-control" id="ee-consigne-tache3" rows="12" required></textarea>
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
                            <small style="color:#64748b;">Auto: les 3 épreuves les plus récentes restent gratuites par défaut; vous pouvez forcer Premium.</small>
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
                                <label class="form-label">Structure & scoring (HTML)</label>
                                <textarea class="form-control" id="ce-consigne-structure" rows="10" required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">5 techniques essentielles (HTML)</label>
                                <textarea class="form-control" id="ce-consigne-techniques" rows="10" required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Erreurs courantes (HTML)</label>
                                <textarea class="form-control" id="ce-consigne-erreurs" rows="10" required></textarea>
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
                            <small style="color:#64748b;">Auto: les 3 épreuves les plus récentes restent gratuites par défaut; vous pouvez forcer Premium.</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="display:flex;align-items:center;gap:.5rem;">
                                <input type="checkbox" id="co-exam-published" checked>
                                <span>Publier immédiatement</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Durée du test (minutes)</label>
                            <input type="number" class="form-control" id="co-duration-minutes" value="35" min="1" max="180">
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
                            <input type="number" class="form-control" id="co-json-duration-minutes" value="35" min="1" max="180">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fichier JSON des questions</label>
                            <input type="file" class="form-control" id="co-json-file" accept=".json,application/json">
                            <small style="color:#64748b;display:block;margin-top:6px;">Champs : question_text, points, image_src, audio_text (script audio), correct_index (0–3), answers[{text}].</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ou coller le JSON</label>
                            <textarea class="form-control" id="co-json-paste" rows="8" placeholder='[{"question_text":"...","audio_text":"Bonjour…","correct_index":0,"answers":[{"text":"A"}]}]'></textarea>
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
                                <label class="form-label">Structure & scoring (HTML)</label>
                                <textarea class="form-control" id="co-consigne-structure" rows="10" required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">5 techniques essentielles (HTML)</label>
                                <textarea class="form-control" id="co-consigne-techniques" rows="10" required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Erreurs courantes (HTML)</label>
                                <textarea class="form-control" id="co-consigne-erreurs" rows="10" required></textarea>
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

                <!-- Expression orale (BDD) -->
                <div id="eo-admin-manager" style="display:none; margin-top: 12px;">
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

                    <form id="eo-exam-json-form" style="display:none;">
                        <input type="hidden" id="eo-json-exam-id">
                        <div class="form-group">
                            <label class="form-label">Titre de l'épreuve</label>
                            <input type="text" class="form-control" id="eo-json-exam-title" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sous-titre (optionnel)</label>
                            <input type="text" class="form-control" id="eo-json-exam-subtitle">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Visibilité</label>
                            <select class="form-control" id="eo-json-exam-visibility">
                                <option value="gratuit">Gratuit</option>
                                <option value="premium">Premium</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="display:flex;align-items:center;gap:.5rem;">
                                <input type="checkbox" id="eo-json-exam-published" checked>
                                <span>Publier immédiatement</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fichier JSON des parties</label>
                            <input type="file" class="form-control" id="eo-json-file" accept=".json,application/json">
                            <small style="color:#64748b;display:block;margin-top:6px;">Tableau de parties, ou objet <code>{"parts":[...]}</code>. Chaque partie publiée doit avoir <strong>exactement 5 sujets</strong> : task_key (tache1|tache2|tache3), subjects[{title, prompt, correction}].</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ou coller le JSON</label>
                            <textarea class="form-control" id="eo-json-paste" rows="8" placeholder='[{"task_key":"tache2","part_title":"Partie 1","subjects":[{"title":"...","prompt":"..."},"...×5"]}]'></textarea>
                        </div>
                        <div class="form-buttons">
                            <button type="button" class="btn btn-outline" id="eo-json-cancel-btn">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer depuis JSON</button>
                        </div>
                    </form>

                    <div class="dashboard-section" style="margin-top:16px;">
                        <div class="section-header">
                            <div class="section-title">Consignes Expression Orale</div>
                            <button type="button" class="btn btn-primary" id="eo-open-consignes-btn"><i class='bx bx-edit'></i> Modifier les consignes</button>
                        </div>
                        <form id="eo-consignes-bundle-form" style="display:none;">
                            <div class="form-group">
                                <label class="form-label">Consigne Tâche 1 — Présentation / entretien dirigé (2 min • 3/20)</label>
                                <textarea class="form-control" id="eo-consigne-tache1" rows="12" required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Consigne Tâche 2 — Exercice en interaction (2 min + 3 min 30 • 7/20)</label>
                                <textarea class="form-control" id="eo-consigne-tache2" rows="12" required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Consigne Tâche 3 — Expression d’un point de vue (4 min 30 • 10/20)</label>
                                <textarea class="form-control" id="eo-consigne-tache3" rows="12" required></textarea>
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
                        <?php foreach (tcf_subscription_plans_catalog(true) as $tcf_plan_opt): ?>
                            <?php
                            // Masquer les cartes incomplètes (prix 0 / brouillon admin).
                            $tcfPayXaf = (int) ($tcf_plan_opt['payment_xaf'] ?? 0);
                            $tcfPrice = (float) ($tcf_plan_opt['price'] ?? 0);
                            if ($tcfPayXaf < 100 && $tcfPrice <= 0) {
                                continue;
                            }
                            $tcfLabel = trim(($tcf_plan_opt['tier'] ?? '') . ' — ' . ($tcf_plan_opt['badge'] ?? ''));
                            if ($tcfLabel === '' || $tcfLabel === '—') {
                                continue;
                            }
                            ?>
                            <option value="<?php echo htmlspecialchars((string) ($tcf_plan_opt['key'] ?? '')); ?>">
                                <?php echo htmlspecialchars($tcfLabel); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-hint">Uniquement les forfaits actifs configurés (Abonnements → Forfaits).</small>
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
        window.TCF_SITE_PUBLIC = <?php echo json_encode(rtrim(site_href(''), '/')); ?>;
        window.TCF_COMMUNITY_API = <?php echo json_encode(site_href('community_api.php')); ?>;
        window.TCF_PARTNERS_API = <?php echo json_encode(site_href('partners_api.php')); ?>;
    </script>
    <script src="../Assets/javascript/tcf-tts.js?v=6"></script>
    <script src="../Assets/javascript/superAdmin.ui.js?v=sa-ui-v9"></script>
    <script src="../Assets/javascript/admin-mobile-nav.js?v=sa-ui-v7"></script>

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
    <script src="<?php echo htmlspecialchars(site_href('Assets/javascript/tcf-assistant-widget.js?v=gemini-fix-1')); ?>"></script>

    <?php
    if (!empty($tcf_profile_panel_user['id'])) {
        $user = $tcf_profile_panel_user;
        $tcf_profile_panel_skip_assets = true;
        include __DIR__ . '/../includes/profile_panel_logged_in.php';
        ?>
    <script src="<?php echo htmlspecialchars(site_href('Assets/javascript/profile_panel.js')); ?>?v=notif-ui-14"></script>
        <?php
    }
    ?>
</body>

</html>