<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/subscription_access.php';
require_once __DIR__ . '/includes/community_posts_helper.php';

$viewer = null;
if (!empty($_SESSION['user_id'])) {
    $st = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $st->execute([(int) $_SESSION['user_id']]);
    $viewer = $st->fetch(PDO::FETCH_ASSOC) ?: null;
}
$loggedIn = $viewer !== null;
$apiUrl = site_href('community_api.php');
$loginUrl = site_href('login.php?next=' . rawurlencode('posts.php'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $tcf_brand_title = 'Annonces — ELITE TCF CANADA';
    include __DIR__ . '/includes/tcf_brand_head.php';
    ?>
    <title>Annonces — ELITE TCF CANADA</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="Assets/css/theme-vars.css">
    <link rel="stylesheet" href="Assets/css/header_footer.css">
    <link rel="stylesheet" href="Assets/css/style_tcf.css">
    <link rel="stylesheet" href="Assets/css/community_posts.css?v=annonce-fb-6">
</head>
<body class="tcf-posts-page">
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="tcp-feed-page">
    <header class="tcp-feed-hero">
        <h1>Annonces</h1>
    </header>

    <div class="tcp-feed" id="tcpFeed" data-api="<?php echo htmlspecialchars($apiUrl); ?>" data-logged="<?php echo $loggedIn ? '1' : '0'; ?>" data-login="<?php echo htmlspecialchars($loginUrl); ?>">
        <div class="tcp-feed__loading"><i class="bx bx-loader-alt bx-spin"></i> Chargement…</div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
<?php include __DIR__ . '/includes/cookie_banner.php'; ?>
<script src="Assets/javascript/community_posts.js?v=annonce-cards-2"></script>
</body>
</html>
