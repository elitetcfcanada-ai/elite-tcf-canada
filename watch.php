<?php

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/site_contact.php';
require_once __DIR__ . '/includes/video_duration.php';
require_once __DIR__ . '/includes/video_player.php';

$videoId = isset($_GET['v']) ? (int) $_GET['v'] : 0;
if ($videoId <= 0) {
    header('Location: ' . site_href('videos.php'));
    exit;
}

$video = null;
try {
    $st = $pdo->prepare(
        "SELECT id, title, thumbnail_url, video_url, visibility, views, likes, duration, created_at
         FROM videos
         WHERE id = ?
         LIMIT 1"
    );
    $st->execute([$videoId]);
    $video = $st->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Throwable $e) {
    $video = null;
}

$isLocked = false;
$isPublic = false;
if ($video !== null) {
    $vis = strtolower((string) ($video['visibility'] ?? 'public'));
    // Lecture ouverte à tous (public + premium) — likes/commentaires restent liés au compte
    if ($vis === 'public' || $vis === 'premium') {
        $isPublic = true;
        $isLocked = false;
    } else {
        $video = null;
    }
}

$pageTitle = $video !== null ? (string) ($video['title'] ?? 'Vidéo') : 'Vidéo introuvable';

$tcfVideoUser = null;
if (!empty($_SESSION['user_id'])) {
    try {
        $stU = $pdo->prepare('SELECT id, name, role FROM users WHERE id = ?');
        $stU->execute([(int) $_SESSION['user_id']]);
        $row = $stU->fetch(PDO::FETCH_ASSOC) ?: null;
        if ($row) {
            $tcfVideoUser = [
                'id' => (int) $row['id'],
                'name' => (string) ($row['name'] ?? ($_SESSION['username'] ?? '')),
                'is_staff' => in_array($row['role'] ?? '', ['admin', 'super_admin'], true),
            ];
        }
    } catch (Throwable $e) {
        $tcfVideoUser = null;
    }
}

$thumb = $video ? tcf_uploads_public_href($video['thumbnail_url'] ?? '') : '';
$vidUrl = ($video && !$isLocked) ? tcf_uploads_public_href($video['video_url'] ?? '') : '';
$likesCount = $video ? (int) ($video['likes'] ?? 0) : 0;
$videosPageHref = site_href('videos.php');
$canInteract = !empty($tcfVideoUser);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $tcf_brand_title = $pageTitle;
    include __DIR__ . '/includes/tcf_brand_head.php';
    ?>
    <title><?php echo htmlspecialchars($pageTitle); ?> — ELITE TCF CANADA</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/theme-vars.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/header_footer.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/style_tcf.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/tcf-videos.css')); ?>?v=watch-back-scroll-2">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
</head>
<body class="tcf-watch-page tcf-watch-page--minimal">
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="tcf-watch-back-bar">
    <a href="<?php echo htmlspecialchars($videosPageHref); ?>"><i class="bx bx-chevron-left"></i> Vidéos</a>
</div>

<?php if ($video === null): ?>
<main class="tcf-watch-minimal" style="text-align:center;padding:2rem 1rem;">
    <p style="color:#606060;">Cette vidéo n'existe pas ou n'est pas publique.</p>
    <p><a href="<?php echo htmlspecialchars($videosPageHref); ?>">Retour aux vidéos</a></p>
</main>
<?php else: ?>
<main class="tcf-watch-minimal">
    <div class="tcf-watch-player-wrap">
        <?php if ($isLocked): ?>
        <div class="tcf-watch-premium-lock">
            <i class="bx bx-lock-alt" aria-hidden="true"></i>
            <p style="margin:0;font-weight:600;">Vidéo réservée aux abonnés</p>
            <p style="margin:0;font-size:0.875rem;color:#ccc;">Abonnez-vous pour lire cette vidéo.</p>
            <a href="<?php echo htmlspecialchars(site_href(empty($_SESSION['user_id']) ? 'login.php' : 'abonnement.php')); ?>"><?php echo empty($_SESSION['user_id']) ? 'Se connecter' : 'Voir les abonnements'; ?></a>
        </div>
        <?php else: ?>
        <?php tcf_render_video_player($vidUrl, ['id' => 'tcf-watch-player', 'poster' => $thumb, 'controls' => true]); ?>
        <?php endif; ?>
    </div>

    <div class="tcf-watch-minimal__head">
        <h1 class="tcf-watch-title"><?php echo htmlspecialchars($video['title'] ?? ''); ?></h1>
        <button type="button" id="tcf-watch-like-btn" class="tcf-watch-like-btn" aria-pressed="false"<?php echo $isLocked ? ' disabled' : ''; ?>>
            <i class="bx bx-like" aria-hidden="true"></i>
            <span class="tcf-watch-like-label">J'aime</span>
            <span id="tcf-watch-like-count"><?php echo (int) $likesCount; ?></span>
        </button>
    </div>

    <?php if (!$canInteract && !$isLocked): ?>
    <p class="tcf-watch-interact-hint">Connectez-vous pour aimer et commenter cette vidéo. <a href="<?php echo htmlspecialchars(site_href('login.php')); ?>">Connexion</a></p>
    <?php endif; ?>

    <section class="tcf-watch-comments" id="tcf-watch-comments" aria-labelledby="tcf-watch-comments-title">
        <h2 class="tcf-watch-comments__head" id="tcf-watch-comments-title">
            <span id="tcf-watch-comment-count-label">Commentaires</span>
        </h2>
        <p id="tcf-watch-login-hint" class="tcf-watch-login-hint" style="display:none;"></p>
        <form id="tcf-watch-comment-form" class="tcf-watch-composer" style="display:none;">
            <div class="tcf-watch-composer__avatar" aria-hidden="true"><i class="bx bx-user"></i></div>
            <div class="tcf-watch-composer__field">
                <label for="tcf-watch-comment-body" class="tcf-sr-only">Votre commentaire</label>
                <textarea id="tcf-watch-comment-body" maxlength="2000" rows="2" placeholder="Ajouter un commentaire…" required></textarea>
                <div class="tcf-watch-composer__actions">
                    <button type="button" class="tcf-watch-composer__cancel" id="tcf-watch-comment-cancel">Annuler</button>
                    <button type="submit" class="tcf-watch-composer__submit">Commenter</button>
                </div>
                <p id="tcf-watch-comment-msg" class="tcf-watch-msg" role="status"></p>
            </div>
        </form>
        <div id="tcf-watch-comments-list" class="tcf-watch-c-list"></div>
    </section>
</main>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
<?php include __DIR__ . '/includes/cookie_banner.php'; ?>
<script src="<?php echo htmlspecialchars(site_href('Assets/javascript/script_tcf.js')); ?>"></script>
<script>
window.TCF_VIDEO_USER = <?php echo json_encode($tcfVideoUser, JSON_UNESCAPED_UNICODE); ?>;
window.TCF_VIDEOS_API = <?php echo json_encode(site_href('videos_api.php')); ?>;
window.TCF_LOGIN_HREF = <?php echo json_encode(site_href('login.php')); ?>;
window.TCF_WATCH_VIDEO_ID = <?php echo (int) $videoId; ?>;
</script>
<script src="<?php echo htmlspecialchars(site_href('Assets/javascript/tcf-watch.js')); ?>"></script>
</body>
</html>
