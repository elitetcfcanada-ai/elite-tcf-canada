<?php

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/site_contact.php';
require_once __DIR__ . '/includes/video_duration.php';
require_once __DIR__ . '/includes/subscription_access.php';

$videosList = [];
try {
    $videosList = $pdo->query(
        "SELECT id, title, thumbnail_url, video_url, visibility, views, duration, created_at
         FROM videos
         WHERE visibility IN ('public', 'premium')
         ORDER BY created_at DESC"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $videosList = [];
}

$tcfVideoAccessUser = null;
if (!empty($_SESSION['user_id'])) {
    try {
        $stU = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stU->execute([(int) $_SESSION['user_id']]);
        $tcfVideoAccessUser = $stU->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Throwable $e) {
        $tcfVideoAccessUser = null;
    }
}

function tcf_video_watch_href(int $videoId): string
{
    return site_href('watch.php?v=' . max(0, $videoId));
}

function tcf_video_duration_label(array $v): string
{
    $dur = isset($v['duration']) ? (string) $v['duration'] : '';
    if (!tcf_video_duration_is_meaningful($dur)) {
        return '';
    }
    if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})/', trim($dur), $m)) {
        $h = (int) $m[1];
        $mi = (int) $m[2];
        $s = (int) $m[3];
        if ($h > 0) {
            return sprintf('%d:%02d:%02d', $h, $mi, $s);
        }
        return sprintf('%d:%02d', $mi, $s);
    }
    return trim($dur);
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $tcf_brand_title = 'Vidéos — ELITE TCF CANADA';
    include __DIR__ . '/includes/tcf_brand_head.php';
    ?>
    <title>Vidéos — ELITE TCF CANADA</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/theme-vars.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/header_footer.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/style_tcf.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/tcf-videos.css')); ?>">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
</head>
<body class="tcf-videos-simple">
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="tcf-videos-simple__main">
    <h1 class="tcf-videos-simple__title">Vidéos</h1>

    <?php if (count($videosList) === 0): ?>
        <p class="tcf-videos-simple__empty">Aucune vidéo publique pour le moment. Les vidéos en « Privé » ne s’affichent pas ici — publiez-les en Public ou Premium depuis l’admin.</p>
    <?php else: ?>
        <div class="tcf-videos-simple__grid">
            <?php foreach ($videosList as $v): ?>
            <?php
            $vidId = (int) ($v['id'] ?? 0);
            $thumb = tcf_uploads_public_href($v['thumbnail_url'] ?? '');
            $durLabel = tcf_video_duration_label($v);
            $watchHref = tcf_video_watch_href($vidId);
            $isPremium = strtolower((string) ($v['visibility'] ?? '')) === 'premium';
            $isLocked = $isPremium && !tcf_user_has_premium_access($tcfVideoAccessUser);
            ?>
            <article class="tcf-videos-simple__card<?php echo $isLocked ? ' tcf-videos-simple__card--locked' : ''; ?>">
                <a class="tcf-videos-simple__link" href="<?php echo htmlspecialchars($watchHref); ?>">
                    <div class="tcf-videos-simple__thumb">
                        <?php if ($isLocked): ?><span class="tcf-tv-premium-badge">Premium</span><?php endif; ?>
                        <?php if ($thumb !== ''): ?>
                            <img src="<?php echo htmlspecialchars($thumb); ?>" alt="" loading="lazy">
                        <?php endif; ?>
                        <?php if ($durLabel !== ''): ?>
                            <span class="tcf-tv-duration"><?php echo htmlspecialchars($durLabel); ?></span>
                        <?php endif; ?>
                        <span class="tcf-videos-simple__play"><i class="bx <?php echo $isLocked ? 'bx-lock-alt' : 'bx-play-circle'; ?>"></i></span>
                    </div>
                    <h2 class="tcf-videos-simple__card-title"><?php echo htmlspecialchars($v['title'] ?? ''); ?></h2>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<?php include __DIR__ . '/includes/cookie_banner.php'; ?>
<script src="<?php echo htmlspecialchars(site_href('Assets/javascript/script_tcf.js')); ?>"></script>
</body>
</html>
