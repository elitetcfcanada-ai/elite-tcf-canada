<?php
require_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $tcf_brand_title = 'Abonnement — ELITE TCF CANADA';
    include __DIR__ . '/includes/tcf_brand_head.php';
    ?>
    <title>Abonnement — ELITE TCF CANADA</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/theme-vars.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/header_footer.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/style_tcf.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/subscription_section.css')); ?>">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <style>
        @media (max-width: 1100px) {
            .pricing-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }
        @media (max-width: 350px) {
            .pricing-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</head>

<body class="tcf-page-abonnement">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="tcf-abonnement-main">
        <?php
        require_once __DIR__ . '/includes/platform_settings.php';
        if (tcf_subscription_sales_enabled($pdo)) {
            $tcf_subscription_section_id = 'Abonnement';
            include __DIR__ . '/includes/subscription_plans_section.php';
        } else {
            ?>
            <section class="subscription-section subscription-section--free-mode" id="Abonnement">
                <div class="subscription-header">
                    <h4 class="section-subtitle tcf-sub-kicker"><i class='bx bxs-school'></i> ABONNEMENT</h4>
                    <h2 class="section-title tfc-sub-main-title">Accès <span class="tcf-sub-accent">gratuit</span> pour tous</h2>
                    <p class="tcf-sub-free-mode-note">Les abonnements sont temporairement désactivés. Tout le contenu premium est accessible sans souscription.</p>
                </div>
            </section>
            <?php
        }
        ?>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
    <?php include __DIR__ . '/includes/cookie_banner.php'; ?>

    <script>
        window.TCF_SUBSCRIBE_ENDPOINT = <?php echo json_encode(site_href('subscribe_api.php')); ?>;
        window.TCF_PAYMENT_ENDPOINT = <?php echo json_encode(site_href('payment_api.php')); ?>;
        window.TCF_LOGIN_URL = <?php echo json_encode(site_href('login.php')); ?>;
        window.TCF_SUBSCRIBE_LOGGED_IN = <?php echo !empty($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        window.TCF_SUBSCRIBE_RETURN_PATH = <?php echo json_encode('abonnement.php'); ?>;
    </script>
    <!-- <script src="<?php echo htmlspecialchars(site_href('Assets/javascript/subscription_checkout.js')); ?>" defer></script> -->
    <script src="<?php echo htmlspecialchars(site_href('Assets/javascript/payment_modal.js')); ?>?v=<?php echo filemtime(__DIR__ . '/Assets/javascript/payment_modal.js'); ?>"></script>
    <script src="<?php echo htmlspecialchars(site_href('Assets/javascript/script_tcf.js')); ?>"></script>
    <script>
        (function() {
            var grid = document.querySelector('.pricing-grid');
            var cards = document.querySelectorAll('[data-responsive-card="true"]');
            
            function updateCardsLayout() {
                if (window.innerWidth >= 1100) {
                    // Desktop : 4 colonnes
                    cards.forEach(function(card) {
                        card.style.flex = '0 0 calc(25% - 0.75rem)';
                        card.style.maxWidth = '280px';
                    });
                } else {
                    // Mobile/tablette : 2 colonnes
                    cards.forEach(function(card) {
                        card.style.flex = '0 0 calc(50% - 0.5rem)';
                        card.style.maxWidth = '400px';
                    });
                }
            }
            
            if (grid && cards.length > 0) {
                updateCardsLayout();
                window.addEventListener('resize', updateCardsLayout);
            }
        })();
    </script>
</body>

</html>
