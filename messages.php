<?php

require_once __DIR__ . '/includes/config.php';

require_once __DIR__ . '/includes/site_contact.php';



if (empty($_SESSION['user_id'])) {

    header('Location: ' . site_href('login.php') . '?next=' . urlencode('messages.php'));

    exit;

}

$role = (string) ($_SESSION['role'] ?? '');
if ($role !== 'admin' && $role !== 'super_admin') {
    header('Location: ' . site_href('index.php'));
    exit;
}



$c = tcf_site_contact();

?>

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    $tcf_brand_title = 'Messagerie — ' . $c['brand'];
    include __DIR__ . '/includes/tcf_brand_head.php';
    ?>

    <title>Messagerie — <?php echo htmlspecialchars($c['brand']); ?></title>

    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/theme-vars.css')); ?>">

    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/header_footer.css')); ?>">

    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/style_tcf.css')); ?>">

    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/style_support.css')); ?>">

    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/style_messages.css')); ?>">

    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">

</head>

<body>

<?php include __DIR__ . '/includes/header.php'; ?>



<main class="support-main tcf-messages-main tcf-messages-page">

    <section class="support-hero-block">

        <h1><i class="bx bx-message-dots"></i> Messagerie</h1>

    </section>



    <section id="tcf-user-chat-app">

        <div class="tcf-chat-top-bar">

            <div class="tcf-chat-top-bar-start">

                <strong id="tcf-chat-me-status">Vous êtes en ligne</strong>

                <span id="tcf-chat-online-count" class="tcf-chat-online-pill">0 en ligne</span>

            </div>

            <div class="tcf-chat-top-bar-actions" role="group" aria-label="Thème de la messagerie">

                <button type="button" id="tcf-chat-theme-light" class="tcf-chat-theme-btn" aria-pressed="true">Clair</button>

                <button type="button" id="tcf-chat-theme-dark" class="tcf-chat-theme-btn" aria-pressed="false">Sombre</button>

            </div>

        </div>

        <div class="tcf-chat-layout">

            <div id="tcf-chat-threads"></div>

            <div class="tcf-chat-pane">

                <div id="tcf-chat-thread-head" class="tcf-chat-thread-head">
                    <button type="button" class="tcf-chat-back-btn" id="tcf-chat-back" aria-label="Retour aux conversations">
                        <i class="bx bx-arrow-back" aria-hidden="true"></i>
                    </button>
                    <div id="tcf-chat-thread-head-title" class="tcf-chat-thread-head-title">Sélectionnez une conversation</div>
                </div>

                <div class="tcf-chat-messages-outer">

                    <div id="tcf-chat-older-bar" class="tcf-chat-older-bar" hidden role="status">Chargement des messages précédents…</div>

                    <div id="tcf-chat-messages"></div>

                </div>

                <p id="tcf-chat-composer-blocked" class="tcf-chat-composer-blocked" hidden role="status"></p>

                <div class="tcf-chat-input-row" id="tcf-chat-composer-wrap">

                    <button type="button" class="tcf-chat-emoji-btn" id="tcf-chat-emoji-btn" title="Emoji" aria-label="Insérer un emoji">😊</button>

                    <div class="tcf-chat-emoji-popover" id="tcf-chat-emoji-popover" hidden></div>

                    <input type="text" id="tcf-chat-input" class="form-control" placeholder="Écrivez votre message…" maxlength="4000">

                    <button type="button" id="tcf-chat-send" class="btn btn-primary" aria-label="Envoyer"><i class="bx bxs-send" aria-hidden="true"></i> <span class="tcf-chat-send-label">Envoyer</span></button>

                </div>

            </div>

        </div>

        <div class="tcf-chat-bottom-bar">

            <button type="button" id="tcf-chat-refresh" class="btn btn-outline btn-sm"><i class="bx bx-refresh"></i> Actualiser</button>

        </div>

    </section>

</main>



<?php include __DIR__ . '/includes/footer.php'; ?>

<?php include __DIR__ . '/includes/cookie_banner.php'; ?>

<script src="<?php echo htmlspecialchars(site_href('Assets/javascript/script_tcf.js')); ?>"></script>

<script>

window.TCF_CHAT_API = <?php echo json_encode(site_href('chat_api.php')); ?>;

</script>

<script src="<?php echo htmlspecialchars(site_href('Assets/javascript/user_chat.js')); ?>"></script>

</body>

</html>



