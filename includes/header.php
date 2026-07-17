<?php
require_once __DIR__ . '/site_contact.php';
require_once __DIR__ . '/avatar_helper.php';
require_once __DIR__ . '/subscription_access.php';
require_once __DIR__ . '/tcf_notifications_helper.php';
$tcf_site_contact = tcf_site_contact();

// Empêcher le zoom sur mobile
$viewport_meta = '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">';

// Vérifier si l'utilisateur est connecté
$is_logged_in = isset($_SESSION['user_id']) && $_SESSION['user_id'];

// Inclure le CSS des notifications si l'utilisateur est connecté
$notifications_css_link = '';
$tcf_notifications_css = '';
$tcf_notifications_js = '';
if ($is_logged_in) {
    $tcf_notifications_css = site_href('Assets/css/notifications.css');
    $tcf_notifications_js = site_href('Assets/javascript/notifications.js?v=' . filemtime(__DIR__ . '/../Assets/javascript/notifications.js'));
    $notifications_css_link = '<link rel="stylesheet" href="' . htmlspecialchars($tcf_notifications_css) . '">';
}
$user = null;
$unread_count = 0;
$profile_flash = $_SESSION['profile_flash'] ?? null;
unset($_SESSION['profile_flash']);

if ($is_logged_in) {
    // Récupérer les informations de l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $syncedAvatar = tcf_sync_user_avatar_from_disk($pdo, (int) $user['id'], $user['avatar'] ?? null);
        $user['avatar_resolved'] = $syncedAvatar;
        $user['avatar_display_url'] = tcf_avatar_public_url($syncedAvatar);
        tcf_maybe_notify_subscription_expired($pdo, $user);
    }

    // Fonction pour récupérer les notifications non lues
    function getUnreadNotifications($pdo, $user_id, $user_role) {
        if (in_array($user_role, ['admin', 'super_admin'], true)) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0 AND type IN ('video', 'topic', 'message', 'user', 'update', 'video_comment', 'testimonial', 'subscription', 'subscription_staff')");
            $stmt->execute([$user_id]);
        } else {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) AS count FROM notifications n
                 INNER JOIN users u ON u.id = ?
                 WHERE n.is_read = 0
                   AND n.type IN ('video','topic','user','update','subscription')
                   AND (
                     n.user_id = ?
                     OR (
                       n.user_id IS NULL
                       AND n.created_at >= u.created_at
                       AND NOT (n.type = 'subscription' AND n.user_id IS NULL)
                     )
                   )"
            );
            $stmt->execute([$user_id, $user_id]);
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['count'] ?? 0);
    }

    $unread_count = getUnreadNotifications($pdo, $_SESSION['user_id'], (string) ($user['role'] ?? ''));
}
?>

<?php echo $notifications_css_link; ?>

<!--------------------- debut de la section du header le plus haut--------------------------------------------->
<div class="top-header">
    <div class="out-box">
        <div class="insite-box">
            <div class="col-1 ">
                <span><i class="bx bxs-envelope"></i> <?php echo htmlspecialchars($tcf_site_contact['email']); ?></span>
                <span><i class="bx bxs-phone-call"></i> <?php echo htmlspecialchars($tcf_site_contact['phone_display']); ?></span>
                <span><i class='bx bxs-alarm'></i> <?php echo htmlspecialchars($tcf_site_contact['hours']); ?></span>
            </div>
            <div class="col-2">
                <span>Follow Us:</span>
                <div class="social">
                    <a href="#"><i class='bx bxl-whatsapp'></i></a>
                    <a href="#"> <i class='bx bxl-facebook'></i></a>
                    <a href="#"> <i class='bx bxl-instagram'></i></a>
                    <a href="#"><i class='bx bxl-tiktok'></i></a>
                    <a href="#"><i class='bx bxl-telegram'></i></a>

                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($is_logged_in && !empty($profile_flash)): ?>
<div class="tcf-toast tcf-toast--<?php echo htmlspecialchars($profile_flash['type']); ?>" role="status">
    <?php echo htmlspecialchars($profile_flash['message']); ?>
</div>
<?php endif; ?>

<!-----------------------------------------debut configuration de la navbar--------------------------------------->
<header class="header">
    <div class="logo">
        <h1><?php echo tcf_brand_logo_img(['class' => 'tcf-brand-logo tcf-brand-logo--header', 'size' => 38]); ?> ELITE TCF <span>CANADA</span></h1>
    </div>
    <nav>
        <div class="navbar">
            <ul class="navLinks">
                <li><a href="<?php echo htmlspecialchars(site_href('index.php')); ?>" style="--i:1;">Accueil</a></li>
                <li><a href="<?php echo htmlspecialchars(site_href('videos.php')); ?>">Vidéos</a></li>
                <li><a href="<?php echo htmlspecialchars(site_href('Expresion_ecrite.php')); ?>">Expression Écrite</a></li>
                <li><a href="<?php echo htmlspecialchars(site_href('Expresion_orale.php')); ?>">Expression Orale</a></li>
                <li><a href="<?php echo htmlspecialchars(site_href('comprehesion_ecrite.php')); ?>">Compréhension Écrite</a></li>
                <li><a href="<?php echo htmlspecialchars(site_href('comprehension_orale.php')); ?>">Compréhension Orale</a></li>
            </ul>
        </div>
        <div class="others<?php echo $is_logged_in ? ' nav-others--user' : ''; ?>">
            <?php if ($is_logged_in): ?>
                <!-- Interface utilisateur connecté -->
                <a href="#" class="notification-icon" id="showNotifications">
                    <i class='bx bx-bell'></i>
                    <?php if ($unread_count > 0): ?>
                        <span class="notification-badge"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </a>
                <i class='bx bx-search'></i>
                <i class='bx bx-menu' id="menuBTN"></i>
                <span class="login nav-profile-trigger" id="showProfile" title="Mon profil" role="button" tabindex="0">
                    <span class="nav-avatar-wrap">
                        <?php $navAvatar = $user['avatar_display_url'] ?? null; ?>
                        <?php if ($navAvatar): ?>
                            <img src="<?php echo htmlspecialchars($navAvatar); ?>" alt="" class="nav-avatar-img" width="48" height="48" loading="lazy" decoding="async">
                        <?php else: ?>
                            <span class="nav-avatar-fallback"><i class="bx bx-user" aria-hidden="true"></i></span>
                        <?php endif; ?>
                    </span>
                </span>
            <?php else: ?>
                <!-- Interface utilisateur non connecté -->
                <a href="<?php echo htmlspecialchars(site_href('login.php')); ?>" class="notification-icon">
                    <i class='bx bx-bell'></i>
                </a>
                <i class='bx bx-search'></i>
                <i class='bx bx-menu' id="menuBTN"></i>
                <a href="<?php echo htmlspecialchars(site_href('login.php')); ?>" class="login-btn" id="showLogin" title="Connexion">
                    <i class='bx bx-log-in' aria-hidden="true"></i>
                    <span class="login-btn__label">Connexion</span>
                </a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<?php
if ($is_logged_in && !empty($user['id'])) {
    include __DIR__ . '/profile_panel_logged_in.php';
}
?>

<style>
/* === Header ELITE TCF CANADA — règles uniformes embarquées === */
header.header { position: sticky; top: 0; z-index: 1000; width: 100%; max-width: 100%; box-sizing: border-box; }
header.header .logo h1 {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    white-space: nowrap;
    margin: 0;
    line-height: 1.1;
    letter-spacing: 0.5px;
    flex-shrink: 0;
}
header.header .logo h1 img.tcf-brand-logo { flex-shrink: 0; }
header.header .logo h1 span { color: var(--main-color, #d30d0d); }

/* Bouton hamburger : caché par défaut, visible <=991px */
header.header #menuBTN {
    display: none;
    font-size: 1.9rem;
    line-height: 1;
    cursor: pointer;
    color: var(--text-color, #141622);
    padding: 0.3rem;
    border-radius: 8px;
    transition: background 0.15s ease, color 0.15s ease, transform 0.2s ease;
}
header.header #menuBTN:hover { background: rgba(211, 13, 13, 0.08); color: var(--main-color, #d30d0d); }
header.header #menuBTN.bx-x { transform: rotate(90deg); color: var(--main-color, #d30d0d); }

@media (max-width: 1100px) {
    header.header .logo h1 { font-size: 1.25rem; }
    header.header .logo h1 .tcf-brand-logo--header { --tcf-brand-logo-size: 34px; }
    header.header { width: 100%; max-width: 100%; box-sizing: border-box; }
}

@media (max-width: 1200px) and (min-width: 992px) {
    header.header nav .navbar .navLinks {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        width: 100%;
        flex-direction: column;
        gap: 0;
        background: #fff;
        padding: 0.5rem 0;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12);
        border-top: 1px solid #e5e7eb;
        z-index: 1001;
        max-height: calc(100vh - 80px);
        overflow-y: auto;
    }
    header.header nav .navbar .navLinks.active { display: flex; }
    header.header #menuBTN { display: inline-flex !important; align-items: center; justify-content: center; }
    header.header nav .others {
        flex-shrink: 0;
        margin-left: auto;
    }
    header.header nav .others.nav-others--user .nav-profile-trigger {
        display: inline-flex !important;
    }
}

@media (max-width: 991px) {
    header.header { padding: 0.5rem 1rem; width: 100%; max-width: 100%; box-sizing: border-box; }
    header.header .logo h1 { font-size: 0.95rem; }
    header.header .logo h1 .tcf-brand-logo--header { --tcf-brand-logo-size: 28px; }
    header.header #menuBTN { display: inline-flex !important; align-items: center; justify-content: center; }

    header.header nav .navbar .navLinks {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        width: 100%;
        max-width: 100%;
        flex-direction: column;
        gap: 0;
        background: #fff;
        padding: 0.5rem 0;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12);
        border-top: 1px solid #e5e7eb;
        z-index: 1001;
        max-height: calc(100vh - 70px);
        overflow-y: auto;
        animation: tcfNavSlide 0.18s ease;
        box-sizing: border-box;
    }
    header.header nav .navbar .navLinks.active { display: flex; }
    header.header nav .navbar .navLinks li {
        width: 100%;
        max-width: 100%;
        padding: 0;
        text-align: left;
        border-bottom: 1px solid #f1f5f9;
        box-sizing: border-box;
    }
    header.header nav .navbar .navLinks li:last-child { border-bottom: 0; }
    header.header nav .navbar .navLinks li a {
        display: block;
        padding: 0.85rem 1.25rem;
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-color, #141622);
        animation: none;
        opacity: 1;
        width: 100%;
        box-sizing: border-box;
    }
    header.header nav .navbar .navLinks li a:hover,
    header.header nav .navbar .navLinks li a.active {
        background: rgba(211, 13, 13, 0.06);
        color: var(--main-color, #d30d0d);
    }

    body.tcf-nav-open { overflow: hidden; }
    
    header.header nav .others > i.bx-search { display: none; }

    nav .others.nav-others--user > a.notification-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.35rem;
        height: 2.35rem;
        padding: 0;
        margin: 0 0.5rem 0 0;
        border-radius: 10px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        box-sizing: border-box;
        color: var(--text-color, #141622);
        flex-shrink: 0;
    }

    nav .others.nav-others--user > a.notification-icon i {
        font-size: 1.35rem;
        line-height: 1;
        color: var(--text-color, #141622);
    }

    nav .others.nav-others--user > a.notification-icon:hover i {
        color: var(--main-color, #d30d0d);
        border-color: rgba(211, 13, 13, 0.25);
    }
    
    /* Bouton de connexion visible sur mobile - icône seulement */
    header.header nav .others .login-btn {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        width: 2.35rem;
        height: 2.35rem;
        padding: 0;
        background: var(--main-color, #d30d0d);
        color: #fff;
        border-radius: 10px;
        text-decoration: none;
        flex-shrink: 0;
    }
    
    header.header nav .others .login-btn .login-btn__label {
        display: none;
    }
    
    header.header nav .others .login-btn i {
        font-size: 1.35rem;
    }
    
    header.header nav .others .login-btn:hover {
        background: rgba(211, 13, 13, 0.85);
    }
}

@media (max-width: 600px) {
    header.header { padding: 0.45rem 0.85rem; gap: 0.5rem; width: 100%; max-width: 100%; box-sizing: border-box; }
    header.header .logo h1 { font-size: 0.85rem; }
    header.header .logo h1 .tcf-brand-logo--header { --tcf-brand-logo-size: 26px; }
    header.header nav .others { gap: 0.4rem !important; }
    header.header nav .others > i,
    header.header nav .others > a.notification-icon i { font-size: 1.35rem; }
    header.header #menuBTN { font-size: 1.7rem; }
}

@media (max-width: 400px) {
    header.header { padding: 0.4rem 0.75rem; width: 100%; max-width: 100%; box-sizing: border-box; }
    header.header .logo h1 { font-size: 0.75rem; }
    header.header .logo h1 .tcf-brand-logo--header { --tcf-brand-logo-size: 24px; }
    header.header nav .others { gap: 0.35rem !important; }
    header.header #menuBTN { font-size: 1.5rem; }
}

@keyframes tcfNavSlide {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Icône de notification réduite */
.notification-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    padding: 0;
    margin: 0 0.4rem 0 0;
    border-radius: 6px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    box-sizing: border-box;
    color: var(--main-color, #d30d0d);
    flex-shrink: 0;
    text-decoration: none;
    transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
}

.notification-icon:hover {
    background: rgba(211, 13, 13, 0.06);
    color: var(--main-color, #d30d0d);
    border-color: rgba(211, 13, 13, 0.25);
}

.notification-icon i {
    font-size: 12px;
    line-height: 1;
    color: var(--main-color, #d30d0d);
}

.notification-icon:hover i {
    color: var(--main-color, #d30d0d);
}

/* Badge de notification réduit */
.notification-badge {
    position: absolute;
    top: -3px;
    right: -3px;
    min-width: 12px;
    height: 12px;
    padding: 0 3px;
    background: var(--main-color, #d30d0d);
    color: #ffffff;
    border-radius: 6px;
    font-size: 8px;
    font-weight: 700;
    line-height: 12px;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1.5px solid #ffffff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
}

.notification-icon {
    position: relative;
}

/* Réduire la taille du texte du top header */
.top-header .col-1 span,
.top-header .col-2 span {
    font-size: 6px;
    white-space: nowrap;
}

.top-header .social i {
    font-size: 6px;
}

/* Responsive : réduire progressivement la taille du texte sur mobile */
@media (max-width: 1200px) {
    .top-header .col-1 span,
    .top-header .col-2 span {
        font-size: 5px;
    }
    
    .top-header .social i {
        font-size: 5px;
    }
}

@media (max-width: 992px) {
    .top-header .col-1 span,
    .top-header .col-2 span {
        font-size: 4px;
    }
    
    .top-header .social i {
        font-size: 4px;
    }
}

@media (max-width: 768px) {
    .top-header .col-1 span,
    .top-header .col-2 span {
        font-size: 3px;
    }
    
    .top-header .social i {
        font-size: 3px;
    }
}

@media (max-width: 600px) {
    .top-header .col-1 span,
    .top-header .col-2 span {
        font-size: 2.5px;
    }
    
    .top-header .social i {
        font-size: 2.5px;
    }
}

@media (max-width: 480px) {
    .top-header .col-1 span,
    .top-header .col-2 span {
        font-size: 2px;
    }
    
    .top-header .social i {
        font-size: 2px;
    }
}
</style>

<script>
(function () {
    if (window.__tcfHeaderMenuInit) return;
    window.__tcfHeaderMenuInit = true;

    function bindHeaderMenu() {
        var btn = document.getElementById('menuBTN');
        var nav = document.querySelector('header.header .navLinks');
        if (!btn || !nav || btn.dataset.tcfBound === '1') return;
        btn.dataset.tcfBound = '1';

        btn.setAttribute('role', 'button');
        btn.setAttribute('tabindex', '0');
        btn.setAttribute('aria-controls', 'tcf-nav-links');
        btn.setAttribute('aria-expanded', 'false');
        btn.setAttribute('aria-label', 'Ouvrir le menu');
        nav.id = nav.id || 'tcf-nav-links';

        function close() {
            nav.classList.remove('active');
            btn.classList.remove('bx-x');
            btn.classList.add('bx-menu');
            btn.setAttribute('aria-expanded', 'false');
            btn.setAttribute('aria-label', 'Ouvrir le menu');
            document.body.classList.remove('tcf-nav-open');
        }
        function open() {
            nav.classList.add('active');
            btn.classList.remove('bx-menu');
            btn.classList.add('bx-x');
            btn.setAttribute('aria-expanded', 'true');
            btn.setAttribute('aria-label', 'Fermer le menu');
            document.body.classList.add('tcf-nav-open');
        }
        function toggle() {
            if (nav.classList.contains('active')) close(); else open();
        }

        btn.addEventListener('click', function (e) { e.preventDefault(); toggle(); });
        btn.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); }
        });

        nav.addEventListener('click', function (e) {
            var a = e.target.closest && e.target.closest('a');
            if (a) close();
        });

        document.addEventListener('click', function (e) {
            if (!nav.classList.contains('active')) return;
            if (e.target.closest('header.header')) return;
            close();
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 1200) close();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindHeaderMenu);
    } else {
        bindHeaderMenu();
    }
})();

// Notifications JavaScript
<?php if ($is_logged_in): ?>
(function () {
    var script = document.createElement('script');
    script.src = '<?php echo htmlspecialchars($tcf_notifications_js); ?>';
    script.defer = true;
    document.head.appendChild(script);
})();
<?php endif; ?>
</script>