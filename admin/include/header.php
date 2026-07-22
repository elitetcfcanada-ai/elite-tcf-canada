<?php
declare(strict_types=1);

// On suppose que admin/bootstrap.php a déjà été inclus AVANT ce fichier
// et que $currentPage est défini dans chaque page (index, pages/*).
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - ELITE TCF CANADA</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/superAdmin.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/tcf-brand-logo.css')); ?>">
</head>
<body>
    <!-- Barre latérale verticale -->
    <div class="sidebar">
        <div class="logo-container">
            <div class="logo">
                <?php echo tcf_brand_logo_img(['class' => 'tcf-brand-logo tcf-brand-logo--admin', 'size' => 32]); ?>
            </div>
            <div class="logo-text">ELITE TCF <span>CANADA</span></div>
        </div>

        <a href="index.php" class="menu-item <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
            <i class='bx bxs-dashboard'></i>
            <span>Tableau de bord</span>
        </a>

        <?php if ($isSuperAdmin): ?>
        <a href="pages/utilisateurs.php" class="menu-item <?php echo $currentPage === 'users' ? 'active' : ''; ?>">
            <i class='bx bxs-user'></i>
            <span>Gestion Utilisateurs</span>
        </a>
        <?php endif; ?>

        <a href="pages/videos.php" class="menu-item <?php echo $currentPage === 'videos' ? 'active' : ''; ?>">
            <i class='bx bxs-video'></i>
            <span>Gestion Vidéos</span>
        </a>

        <a href="pages/messages.php" class="menu-item <?php echo $currentPage === 'messages' ? 'active' : ''; ?>">
            <i class='bx bxs-megaphone'></i>
            <span>Gestion des annonces</span>
        </a>

        <a href="pages/sujets.php" class="menu-item <?php echo $currentPage === 'topics' ? 'active' : ''; ?>">
            <i class='bx bxs-book'></i>
            <span>Gestion des sujets</span>
        </a>

        <?php if ($isSuperAdmin): ?>
        <a href="pages/admins.php" class="menu-item <?php echo $currentPage === 'admins' ? 'active' : ''; ?>">
            <i class='bx bxs-shield'></i>
            <span>Gestion administrateurs</span>
        </a>
        <?php endif; ?>

        <a href="../logout.php" class="menu-item">
            <i class='bx bx-log-out'></i>
            <span>Déconnexion</span>
        </a>
    </div>

    <!-- Contenu principal -->
    <div class="main-content">
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration — TCF Canada</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/theme-vars.css')); ?>">
    <link rel="stylesheet" href="../Assets/css/style_superAdmin.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/profile_panel.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/tcf-responsive-pills.css')); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
</head>

<body class="tcf-admin-app dark-mode">
    <?php if (!empty($profile_flash)): ?>
        <div class="tcf-toast tcf-toast--<?php echo htmlspecialchars((string) $profile_flash['type']); ?>" role="status">
            <?php echo htmlspecialchars((string) $profile_flash['message']); ?>
        </div>
    <?php endif; ?>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-container">
            <div class="logo">
                <?php echo tcf_brand_logo_img(['class' => 'tcf-brand-logo tcf-brand-logo--admin', 'size' => 32]); ?>
            </div>
            <div class="logo-text">TCF <span>Canada</span></div>
        </div>

        <div class="menu-item active" data-target="dashboard">
            <i class="bx bxs-dashboard"></i>
            <span>Tableau de bord</span>
        </div>

        <?php if ($isSuperAdmin): ?>
            <div class="menu-item" data-target="users">
                <i class="bx bxs-user"></i>
                <span>Gestion Utilisateurs</span>
            </div>
        <?php endif; ?>

        <div class="menu-item" data-target="videos">
            <i class="bx bxs-video"></i>
            <span>Gestion Vidéos</span>
        </div>

        <div class="menu-item" data-target="messages">
            <i class="bx bxs-megaphone"></i>
            <span>Gestion des annonces</span>
        </div>

        <div class="menu-item" id="topics-menu">
            <i class="bx bxs-book"></i>
            <span>Gestion des sujets</span>
            <i class="bx bx-chevron-down" style="margin-left: auto;"></i>
        </div>

        <div class="sub-menu" id="topics-submenu">
            <div class="sub-item" data-target="topics-written">Compréhension écrite</div>
            <div class="sub-item" data-target="topics-oral">Compréhension orale</div>
            <div class="sub-item" data-target="topics-expression">Expression écrite</div>
            <div class="sub-item" data-target="topics-speaking">Expression orale</div>
        </div>

        <?php if ($isSuperAdmin): ?>
            <div class="menu-item" data-target="admins">
                <i class="bx bxs-shield"></i>
                <span>Gestion administrateurs</span>
            </div>
        <?php endif; ?>

        <div class="menu-item" data-target="analytics">
            <i class="bx bxs-bar-chart-alt-2"></i>
            <span>Analyse vidéo</span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header admin-dashboard-header">
            <div class="page-title">Tableau de Bord Super Administrateur</div>
            <div class="admin-info">
                <div class="dark-mode-toggle" id="dark-mode-toggle" title="Thème">
                    <i class="bx bx-moon"></i>
                </div>
                <div class="notifications" id="notifications-btn">
                    <a href="#" class="notification-icon sa-nav-notification-icon" id="showNotifications" aria-label="Notifications" title="Notifications">
                        <i class="bx bx-bell"></i>
                        <span class="notification-badge" id="notification-count" style="display:none;">0</span>
                    </a>
                </div>
                <a href="#" class="admin-profile-trigger" id="showProfile" title="Mon profil" aria-label="Mon profil">
                    <span class="admin-nav-avatar-wrap nav-avatar-wrap">
                        <?php if (!empty($tcf_profile_panel_user) && !empty($tcf_profile_panel_user['avatar_display_url'])): ?>
                            <img src="<?php echo htmlspecialchars((string) $tcf_profile_panel_user['avatar_display_url']); ?>" alt="" class="admin-nav-avatar-img nav-avatar-img" width="40" height="40" loading="lazy" decoding="async">
                        <?php else: ?>
                            <span class="admin-nav-avatar-fallback nav-avatar-fallback"><i class="bx bx-user" aria-hidden="true"></i></span>
                        <?php endif; ?>
                    </span>
                </a>
            </div>
        </div>

