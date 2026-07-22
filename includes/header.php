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
// Panneau notifications = drawer PHP (même UX que le profil). Plus de notifications.js concurrent.
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
        tcf_maybe_notify_subscription_reminders($pdo, $user);
    }

    // Fonction pour récupérer les notifications non lues
    function getUnreadNotifications($pdo, $user_id, $user_role) {
        if (in_array($user_role, ['admin', 'super_admin'], true)) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0 AND type IN ('video', 'topic', 'message', 'user', 'update', 'video_comment', 'testimonial', 'subscription', 'subscription_staff', 'exam')");
            $stmt->execute([$user_id]);
        } else {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) AS count FROM notifications n
                 INNER JOIN users u ON u.id = ?
                 WHERE n.is_read = 0
                   AND n.type IN ('video','topic','message','user','update','subscription','exam')
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

<?php
$tcf_mail_href = tcf_site_mailto($tcf_site_contact);
$tcf_tel_href = tcf_site_tel($tcf_site_contact);
$tcf_wa_href = tcf_site_whatsapp_url($tcf_site_contact);
$tcf_tg_href = tcf_site_telegram_url($tcf_site_contact);
?>
<!--------------------- debut de la section du header le plus haut--------------------------------------------->
<div class="top-header">
    <div class="out-box">
        <div class="insite-box tcf-topbar-row">
            <a class="th-item th-email" href="<?php echo htmlspecialchars($tcf_mail_href); ?>" style="color:#fff!important;text-decoration:none!important;">
                <i class="bx bxs-envelope" aria-hidden="true"></i>
                <span class="th-text" style="color:#fff!important;"><?php echo htmlspecialchars($tcf_site_contact['email']); ?></span>
            </a>
            <a class="th-item th-phone" href="<?php echo htmlspecialchars($tcf_tel_href); ?>" style="color:#fff!important;text-decoration:none!important;">
                <i class="bx bxs-phone-call" aria-hidden="true"></i>
                <span class="th-text" style="color:#fff!important;"><?php echo htmlspecialchars($tcf_site_contact['phone_display']); ?></span>
            </a>
            <span class="th-item th-hours">
                <i class="bx bxs-alarm" aria-hidden="true"></i>
                <span class="th-text"><?php echo htmlspecialchars($tcf_site_contact['hours']); ?></span>
            </span>
            <span class="th-follow-label">Follow Us:</span>
            <div class="social">
                <a href="<?php echo htmlspecialchars($tcf_wa_href); ?>" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" style="color:#fff!important;"><i class="bx bxl-whatsapp"></i></a>
                <a href="#" aria-label="Facebook" style="color:#fff!important;"><i class="bx bxl-facebook"></i></a>
                <a href="#" aria-label="Instagram" style="color:#fff!important;"><i class="bx bxl-instagram"></i></a>
                <a href="#" aria-label="TikTok" style="color:#fff!important;"><i class="bx bxl-tiktok"></i></a>
                <a href="<?php echo htmlspecialchars($tcf_tg_href); ?>" target="_blank" rel="noopener noreferrer" aria-label="Telegram" style="color:#fff!important;"><i class="bx bxl-telegram"></i></a>
            </div>
        </div>
    </div>
</div>
<style id="tcf-topbar-force">
.top-header{display:block!important;width:100%;overflow:hidden}
.top-header .out-box{width:100%;background:var(--main-color,#d30d0d)}
.top-header .insite-box.tcf-topbar-row{
  display:flex!important;flex-direction:row!important;flex-wrap:nowrap!important;
  align-items:center!important;justify-content:center!important;
  gap:clamp(.55rem,1.4vw,1.25rem)!important;
  background:#141622!important;
  padding:.32rem clamp(.7rem,2.8vw,2.75rem)!important;
  min-height:0!important;white-space:nowrap!important;overflow:hidden!important;
  clip-path:polygon(3% 0,97% 0,98.5% 100%,1.5% 100%);
}
.top-header .tcf-topbar-row .th-item,
.top-header .tcf-topbar-row a.th-item,
.top-header .tcf-topbar-row a.th-item:link,
.top-header .tcf-topbar-row a.th-item:visited,
.top-header .tcf-topbar-row a.th-item:hover,
.top-header .tcf-topbar-row a.th-item:active,
.top-header .tcf-topbar-row .th-item .th-text,
.top-header .tcf-topbar-row .th-follow-label{
  color:#fff!important;text-decoration:none!important;
  font-size:clamp(.78rem,.95vw,1rem)!important;font-weight:500!important;
  line-height:1.2!important;white-space:nowrap!important;cursor:pointer;
}
.top-header .tcf-topbar-row .th-hours,
.top-header .tcf-topbar-row .th-hours .th-text,
.top-header .tcf-topbar-row .th-follow-label{cursor:default}
.top-header .tcf-topbar-row .th-item,
.top-header .tcf-topbar-row a.th-item{
  display:inline-flex!important;flex-direction:row!important;flex-wrap:nowrap!important;
  align-items:center!important;gap:.35rem!important;flex-shrink:0!important;
  background:transparent!important;border:0!important;box-shadow:none!important;
}
.top-header .tcf-topbar-row .th-item i{color:var(--main-color,#d30d0d)!important;font-size:clamp(.85rem,1vw,1.1rem)!important;padding:0!important;line-height:1!important}
.top-header .tcf-topbar-row .social{display:inline-flex!important;flex-wrap:nowrap!important;align-items:center!important;gap:.35rem!important;flex-shrink:0!important}
.top-header .tcf-topbar-row .social a,
.top-header .tcf-topbar-row .social a:link,
.top-header .tcf-topbar-row .social a:visited{color:#fff!important;text-decoration:none!important;line-height:1!important}
.top-header .tcf-topbar-row .social a i{color:#fff!important;font-size:clamp(.9rem,1.05vw,1.15rem)!important;padding:0!important}
.top-header .tcf-topbar-row .social a:hover i{color:var(--main-color,#d30d0d)!important}
@media (max-width:1200px){.top-header .tcf-topbar-row .th-hours .th-text{display:none!important}}
@media (max-width:1050px){.top-header .tcf-topbar-row .th-follow-label{display:none!important}}
/* Tablette + téléphone : barre civile entièrement masquée */
@media (max-width:991px){.top-header{display:none!important}}
</style>

<?php if ($is_logged_in && !empty($profile_flash)): ?>
<div class="tcf-toast tcf-toast--<?php echo htmlspecialchars($profile_flash['type']); ?>" role="status">
    <?php echo htmlspecialchars($profile_flash['message']); ?>
</div>
<?php endif; ?>

<!-----------------------------------------debut configuration de la navbar--------------------------------------->
<?php
$tcf_nav_script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
if ($tcf_nav_script === '' || strpos($tcf_nav_script, '.') === false) {
    $tcf_nav_uri = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
    $tcf_nav_script = basename((string) $tcf_nav_uri) ?: 'index.php';
}
$tcf_nav_is = static function (array $files) use ($tcf_nav_script): bool {
    return in_array($tcf_nav_script, $files, true);
};
$tcf_nav_cls = static function (array $files) use ($tcf_nav_is): string {
    return $tcf_nav_is($files) ? 'active' : '';
};
$tcf_nav_aria = static function (array $files) use ($tcf_nav_is): string {
    return $tcf_nav_is($files) ? ' aria-current="page"' : '';
};
?>
<header class="header">
    <div class="logo">
        <h1><?php echo tcf_brand_logo_img(['class' => 'tcf-brand-logo tcf-brand-logo--header', 'size' => 38]); ?> ELITE TCF <span>CANADA</span></h1>
    </div>
    <nav>
        <div class="navbar">
            <ul class="navLinks">
                <li><a href="<?php echo htmlspecialchars(site_href('index.php')); ?>" class="<?php echo $tcf_nav_cls(['index.php']); ?>"<?php echo $tcf_nav_aria(['index.php']); ?>>Accueil</a></li>
                <li><a href="<?php echo htmlspecialchars(site_href('videos.php')); ?>" class="<?php echo $tcf_nav_cls(['videos.php', 'watch.php']); ?>"<?php echo $tcf_nav_aria(['videos.php', 'watch.php']); ?>>Vidéos</a></li>
                <li><a href="<?php echo htmlspecialchars(site_href('Expresion_ecrite.php')); ?>" class="<?php echo $tcf_nav_cls(['Expresion_ecrite.php', 'epreuve_ee.php']); ?>"<?php echo $tcf_nav_aria(['Expresion_ecrite.php', 'epreuve_ee.php']); ?>>Expression Écrite</a></li>
                <li><a href="<?php echo htmlspecialchars(site_href('Expresion_orale.php')); ?>" class="<?php echo $tcf_nav_cls(['Expresion_orale.php', 'epreuve_eo.php']); ?>"<?php echo $tcf_nav_aria(['Expresion_orale.php', 'epreuve_eo.php']); ?>>Expression Orale</a></li>
                <li><a href="<?php echo htmlspecialchars(site_href('comprehesion_ecrite.php')); ?>" class="<?php echo $tcf_nav_cls(['comprehesion_ecrite.php', 'comprehesion_ecrite_quiz.php']); ?>"<?php echo $tcf_nav_aria(['comprehesion_ecrite.php', 'comprehesion_ecrite_quiz.php']); ?>>Compréhension Écrite</a></li>
                <li><a href="<?php echo htmlspecialchars(site_href('comprehension_orale.php')); ?>" class="<?php echo $tcf_nav_cls(['comprehension_orale.php', 'comprehension_orale_quiz.php']); ?>"<?php echo $tcf_nav_aria(['comprehension_orale.php', 'comprehension_orale_quiz.php']); ?>>Compréhension Orale</a></li>
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
header.header {
    position: sticky;
    top: 0;
    z-index: 1000;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    gap: clamp(0.75rem, 2vw, 2rem);
}
header.header .logo {
    flex: 0 0 auto !important;
    position: relative;
    z-index: 3;
    background: #fff;
    padding-right: 1rem;
    isolation: isolate;
}
header.header .logo h1 {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    white-space: nowrap;
    margin: 0;
    line-height: 1.1;
    letter-spacing: 0.5px;
    flex-shrink: 0;
    font-size: clamp(0.95rem, 1.35vw, 1.55rem);
}
header.header .logo h1 .tcf-brand-logo {
    margin-right: 0 !important;
}
header.header .logo h1 img.tcf-brand-logo { flex-shrink: 0; }
header.header .logo h1 span { color: var(--main-color, #d30d0d); }
header.header nav {
    flex: 1 1 auto;
    min-width: 0;
    position: relative;
    z-index: 1;
    margin-left: 0.5rem;
}

/* Bouton hamburger : caché par défaut */
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

/* Desktop : textes plus petits + icônes/bouton réduits plus tôt */
header.header nav .others {
    gap: 0.45rem !important;
}
header.header nav .others > i,
header.header nav .others > a.notification-icon i {
    font-size: 1.05rem !important;
}
header.header nav .navbar .navLinks {
    gap: clamp(0.5rem, 0.95vw, 0.95rem);
}
header.header nav .navbar .navLinks li {
    padding: 0.15rem 0.1rem;
}
header.header nav .navbar .navLinks li a {
    font-size: clamp(0.72rem, 0.82vw, 0.88rem);
    letter-spacing: 0.01em;
    opacity: 1 !important;
    animation: none !important;
}

header.header nav .navbar .navLinks li a.active,
header.header nav .navbar .navLinks li a[aria-current="page"] {
    color: var(--main-color, #d30d0d) !important;
    border-bottom: 2px solid var(--main-color, #d30d0d) !important;
    padding-bottom: 0.2rem !important;
    font-weight: 700 !important;
}

@media (max-width: 1500px) {
    header.header nav .navbar .navLinks { gap: 0.7rem; }
    header.header nav .navbar .navLinks li a { font-size: 0.8rem; }
    header.header nav .others .login-btn {
        padding: 0.42rem 0.9rem !important;
        font-size: 0.88rem !important;
    }
}

@media (max-width: 1400px) {
    header.header .logo h1 { font-size: 1.1rem; }
    header.header .logo h1 .tcf-brand-logo--header { --tcf-brand-logo-size: 32px; }
    header.header nav .navbar .navLinks { gap: 0.55rem; }
    header.header nav .navbar .navLinks li a { font-size: 0.74rem; }
    header.header nav .others { gap: 0.4rem !important; }
    header.header nav .others .login-btn {
        padding: 0.4rem 0.85rem !important;
        font-size: 0.84rem !important;
    }
    header.header nav .others .login-btn i { font-size: 1.02rem !important; }
}

@media (max-width: 1280px) {
    header.header .logo h1 { font-size: 1rem; }
    header.header .logo h1 .tcf-brand-logo--header { --tcf-brand-logo-size: 28px; }
    header.header .logo { padding-right: 0.4rem; }
    header.header nav .navbar .navLinks { gap: 0.45rem; }
    header.header nav .navbar .navLinks li a { font-size: 0.68rem; }
    header.header nav .others { gap: 0.35rem !important; }
    header.header nav .others > i,
    header.header nav .others > a.notification-icon i { font-size: 0.95rem !important; }
    header.header nav .others .login-btn {
        padding: 0.36rem 0.75rem !important;
        font-size: 0.8rem !important;
    }
}

@media (max-width: 1120px) {
    header.header .logo h1 { font-size: 0.9rem; }
    header.header .logo h1 .tcf-brand-logo--header { --tcf-brand-logo-size: 26px; }
    header.header nav .navbar .navLinks { gap: 0.35rem; }
    header.header nav .navbar .navLinks li a { font-size: 0.64rem; }
    header.header { width: 100%; max-width: 100%; box-sizing: border-box; }
}

/* Tablette / mobile : menu hamburger — déroulé pleine largeur sous le header */
@media (max-width: 991px) {
    header.header { padding: 0.5rem 1rem; width: 100%; max-width: 100%; box-sizing: border-box; }
    header.header .logo { padding-right: 0.5rem; }
    header.header .logo h1 { font-size: 0.95rem; }
    header.header .logo h1 .tcf-brand-logo--header { --tcf-brand-logo-size: 28px; }
    header.header #menuBTN { display: inline-flex !important; align-items: center; justify-content: center; }
    header.header nav {
        position: static !important;
        z-index: auto;
    }
    header.header nav .navbar {
        position: static !important;
        flex: 0 0 auto;
        overflow: visible;
    }
    header.header nav .others { flex-shrink: 0; margin-left: auto; }

    header.header nav .navbar .navLinks {
        display: none;
        position: absolute !important;
        top: 100% !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        max-width: none !important;
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0 !important;
        margin: 0 !important;
        background: #fff !important;
        padding: 0 !important;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.14);
        border-top: 1px solid #e5e7eb;
        z-index: 1002 !important;
        max-height: min(70vh, calc(100vh - 4.5rem));
        overflow-x: hidden;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        box-sizing: border-box;
    }
    header.header nav .navbar .navLinks.active { display: flex !important; }
    header.header nav .navbar .navLinks li {
        width: 100% !important;
        max-width: none !important;
        padding: 0 !important;
        text-align: left !important;
        border-bottom: 1px solid #f1f5f9;
        box-sizing: border-box;
    }
    header.header nav .navbar .navLinks li:last-child { border-bottom: 0; }
    header.header nav .navbar .navLinks li a {
        display: block !important;
        padding: 0.95rem 1.35rem !important;
        font-size: 1rem !important;
        font-weight: 600 !important;
        color: var(--text-color, #141622) !important;
        animation: none !important;
        opacity: 1 !important;
        width: 100% !important;
        box-sizing: border-box;
        white-space: normal !important;
    }
    header.header nav .navbar .navLinks li a:hover,
    header.header nav .navbar .navLinks li a.active {
        background: rgba(211, 13, 13, 0.06) !important;
        color: var(--main-color, #d30d0d) !important;
    }

    /* Header fixe sur mobile : reste visible au scroll et quand le menu est ouvert */
    header.header {
        position: fixed !important;
        top: 0;
        left: 0;
        right: 0;
        width: 100%;
        max-width: 100%;
        z-index: 1000;
    }
    html.tcf-has-fixed-header body {
        padding-top: var(--tcf-header-offset, 56px);
    }
    html.tcf-nav-open,
    body.tcf-nav-open {
        overflow: hidden;
        overscroll-behavior: none;
        touch-action: none;
    }
    body.tcf-nav-open header.header {
        position: fixed !important;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1001;
    }

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

</style>

<script>
(function () {
    if (window.__tcfHeaderMenuInit) return;
    window.__tcfHeaderMenuInit = true;

    function syncFixedHeaderOffset() {
        var header = document.querySelector('header.header');
        if (!header) return;
        if (window.innerWidth > 991) {
            document.documentElement.classList.remove('tcf-has-fixed-header');
            document.documentElement.style.removeProperty('--tcf-header-offset');
            return;
        }
        document.documentElement.classList.add('tcf-has-fixed-header');
        var h = Math.ceil(header.getBoundingClientRect().height) || 56;
        document.documentElement.style.setProperty('--tcf-header-offset', h + 'px');
    }

    function bindHeaderMenu() {
        var btn = document.getElementById('menuBTN');
        var nav = document.querySelector('header.header .navLinks');
        syncFixedHeaderOffset();
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
            document.documentElement.classList.remove('tcf-nav-open');
            document.body.classList.remove('tcf-nav-open');
        }
        function open() {
            nav.classList.add('active');
            btn.classList.remove('bx-menu');
            btn.classList.add('bx-x');
            btn.setAttribute('aria-expanded', 'true');
            btn.setAttribute('aria-label', 'Fermer le menu');
            document.documentElement.classList.add('tcf-nav-open');
            document.body.classList.add('tcf-nav-open');
            syncFixedHeaderOffset();
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
            syncFixedHeaderOffset();
            if (window.innerWidth > 991) close();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindHeaderMenu);
    } else {
        bindHeaderMenu();
    }
})();

<?php /* notifications.js retiré : le tiroir #notificationPage (profil) gère l’affichage */ ?>
</script>