<?php

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/site_contact.php';
require_once __DIR__ . '/includes/video_duration.php';
require_once __DIR__ . '/includes/media_blob.php';
require_once __DIR__ . '/includes/subscription_access.php';

$viewer = null;
if (!empty($_SESSION['user_id'])) {
    try {
        $stU = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stU->execute([(int) $_SESSION['user_id']]);
        $viewer = $stU->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Throwable $e) {
        $viewer = null;
    }
}

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

foreach ($videosList as &$tcfVidRow) {
    $t = (string) ($tcfVidRow['title'] ?? '');
    if (mb_strlen($t) > 100) {
        $tcfVidRow['title'] = mb_substr($t, 0, 100);
    }
}
unset($tcfVidRow);

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

$subscribeHref = empty($_SESSION['user_id'])
    ? site_href('login.php?next=' . rawurlencode('abonnement.php'))
    : site_href('abonnement.php');

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    $tcf_brand_title = 'Vidéos TCF Canada | ELITE TCF CANADA';
    $tcf_brand_desc = 'Regardez les vidéos de préparation TCF Canada sur ELITE TCF CANADA : conseils, méthodes et entraînements pour réussir l\'examen.';
    $tcf_brand_keywords = 'vidéos TCF Canada, formation TCF Canada, ELITE TCF CANADA, cours TCF en ligne, préparation TCF vidéo, examen TCF IRCC';
    include __DIR__ . '/includes/tcf_brand_head.php';
    ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/theme-vars.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/header_footer.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/style_tcf.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/tcf-videos.css')); ?>?v=video-filters-1">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
</head>
<body class="tcf-videos-simple">
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="tcf-videos-simple__main">
    <div class="tcf-videos-simple__toolbar">
        <h1 class="tcf-videos-simple__title">Vidéos</h1>
        <div class="tcf-videos-simple__filters" role="tablist" aria-label="Filtrer les vidéos">
            <button type="button" class="tcf-videos-simple__filter is-active" data-filter="gratuit" role="tab" aria-selected="true">Gratuit</button>
            <button type="button" class="tcf-videos-simple__filter" data-filter="premium" role="tab" aria-selected="false">Premium</button>
        </div>
    </div>

    <?php if (count($videosList) === 0): ?>
        <p class="tcf-videos-simple__empty">Aucune vidéo publique pour le moment.</p>
    <?php else: ?>
        <div class="tcf-videos-simple__grid" id="tcfVideosGrid">
            <?php foreach ($videosList as $v): ?>
            <?php
            $vidId = (int) ($v['id'] ?? 0);
            $thumb = tcf_video_media_href($pdo, $vidId, $v['thumbnail_url'] ?? '', 'thumbnail');
            $durLabel = tcf_video_duration_label($v);
            $isPremium = strtolower((string) ($v['visibility'] ?? 'public')) === 'premium';
            $isLocked = tcf_video_is_premium_locked_for_user($v, $viewer);
            $cardHref = $isLocked ? $subscribeHref : tcf_video_watch_href($vidId);
            $filterKey = $isPremium ? 'premium' : 'gratuit';
            $cardClass = 'tcf-videos-simple__card' . ($isLocked ? ' is-premium-locked' : '') . ($isPremium ? ' is-premium' : '');
            ?>
            <article class="<?php echo htmlspecialchars($cardClass); ?>" data-visibility="<?php echo htmlspecialchars($filterKey); ?>">
                <a class="tcf-videos-simple__link" href="<?php echo htmlspecialchars($cardHref); ?>"<?php echo $isLocked ? ' title="Abonnement requis"' : ''; ?>>
                    <div class="tcf-videos-simple__thumb">
                        <?php if ($thumb !== ''): ?>
                            <img src="<?php echo htmlspecialchars($thumb); ?>" alt="" loading="lazy">
                        <?php endif; ?>
                        <?php if ($isPremium): ?>
                            <span class="tcf-videos-simple__ribbon" aria-hidden="true">Premium</span>
                        <?php endif; ?>
                        <?php if ($durLabel !== ''): ?>
                            <span class="tcf-tv-duration"><?php echo htmlspecialchars($durLabel); ?></span>
                        <?php endif; ?>
                        <span class="tcf-videos-simple__play" aria-hidden="true">
                            <span class="tcf-videos-simple__play-wrap">
                                <i class="bx bx-play-circle"></i>
                                <?php if ($isLocked): ?>
                                    <span class="tcf-videos-simple__lock" title="Verrouillé">
                                        <i class="bx bxs-lock-alt"></i>
                                    </span>
                                <?php endif; ?>
                            </span>
                        </span>
                    </div>
                    <h2 class="tcf-videos-simple__card-title"><?php echo htmlspecialchars($v['title'] ?? ''); ?></h2>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
        <p class="tcf-videos-simple__empty tcf-videos-simple__filter-empty" id="tcfVideosFilterEmpty" hidden>Aucune vidéo dans cette catégorie.</p>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<?php include __DIR__ . '/includes/cookie_banner.php'; ?>
<script src="<?php echo htmlspecialchars(site_href('Assets/javascript/script_tcf.js')); ?>"></script>
<script>
(function () {
    var filters = document.querySelectorAll('.tcf-videos-simple__filter');
    var cards = document.querySelectorAll('#tcfVideosGrid .tcf-videos-simple__card');
    var emptyEl = document.getElementById('tcfVideosFilterEmpty');
    if (!filters.length || !cards.length) return;

    function applyFilter(key) {
        var visible = 0;
        cards.forEach(function (card) {
            var match = card.getAttribute('data-visibility') === key;
            card.hidden = !match;
            if (match) visible += 1;
        });
        filters.forEach(function (btn) {
            var on = btn.getAttribute('data-filter') === key;
            btn.classList.toggle('is-active', on);
            btn.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        if (emptyEl) emptyEl.hidden = visible > 0;
    }

    filters.forEach(function (btn) {
        btn.addEventListener('click', function () {
            applyFilter(btn.getAttribute('data-filter') || 'gratuit');
        });
    });

    applyFilter('gratuit');
})();
</script>
</body>
</html>
