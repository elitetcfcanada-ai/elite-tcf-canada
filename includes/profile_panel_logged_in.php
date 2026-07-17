<?php

declare(strict_types=1);

/**
 * Panneau profil + notifications + modales (utilisateur connecté).
 * Prérequis : $pdo, $user (ligne users complète + avatar_display_url), $_SESSION['user_id'].
 */
if (empty($user) || empty($user['id'])) {
    return;
}

require_once __DIR__ . '/subscription_access.php';
require_once __DIR__ . '/subscription_plans_data.php';
require_once __DIR__ . '/platform_settings.php';

$tcf_sub_sales_enabled = isset($pdo) && tcf_subscription_sales_enabled($pdo);
$tcf_sub_platform_free = isset($pdo) && tcf_subscriptions_platform_disabled($pdo);

if (!function_exists('tcf_get_all_notifications_for_panel')) {
    function tcf_get_all_notifications_for_panel(PDO $pdo, int $user_id, string $role): array
    {
        if (in_array($role, ['admin', 'super_admin'], true)) {
            $types = ['video', 'topic', 'message', 'user', 'update', 'video_comment', 'testimonial', 'subscription', 'subscription_staff'];
            $ph = implode(',', array_fill(0, count($types), '?'));
            $stmt = $pdo->prepare(
                "SELECT * FROM notifications WHERE (user_id = ? OR user_id IS NULL) AND type IN ($ph) ORDER BY created_at DESC"
            );
            $stmt->execute(array_merge([$user_id], $types));

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        /* Apprenants : pas d’historique global avant inscription ; pas de notifications « staff » */
        $types = ['video', 'topic', 'message', 'user', 'update', 'subscription'];
        $ph = implode(',', array_fill(0, count($types), '?'));
        $stmt = $pdo->prepare(
            "SELECT n.* FROM notifications n
             INNER JOIN users u ON u.id = ?
             WHERE n.type IN ($ph)
               AND (
                 n.user_id = ?
                 OR (
                   n.user_id IS NULL
                   AND n.created_at >= u.created_at
                   AND NOT (n.type = 'subscription' AND n.user_id IS NULL)
                 )
               )
             ORDER BY n.created_at DESC"
        );
        $stmt->execute(array_merge([$user_id], $types, [$user_id]));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$notifications = tcf_get_all_notifications_for_panel($pdo, (int) $_SESSION['user_id'], (string) ($user['role'] ?? ''));
$tcf_is_staff_notifications = in_array((string) ($user['role'] ?? ''), ['admin', 'super_admin'], true);
$tcf_profile_panel_hide_notifications = !empty($tcf_profile_panel_hide_notifications);

$tcf_sub_payment_rows = [];
if (($user['role'] ?? '') === 'user') {
    try {
        $stp = $pdo->prepare(
            'SELECT plan_key, plan_label, amount, currency, payment_method, created_at FROM subscription_payments WHERE user_id = ? ORDER BY created_at DESC LIMIT 30'
        );
        $stp->execute([(int) $user['id']]);
        $tcf_sub_payment_rows = $stp->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $tcf_sub_payment_rows = [];
    }
}
?>
<div id="tcf-profile-api-config" class="tcf-sr-only" hidden
    data-api-url="<?php echo htmlspecialchars(site_href('profile_api.php')); ?>"
    data-user-email="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
    <?php if (!empty($tcf_is_staff_notifications)): ?>data-superadmin-endpoint="<?php echo htmlspecialchars(site_href('admin/superAdmin.php')); ?>"<?php endif; ?>
    aria-hidden="true"></div>
<?php if (!$tcf_profile_panel_hide_notifications): ?>
<!-- Page de notifications (seulement pour utilisateurs connectés) -->
<div class="notification-overlay" id="notificationOverlay"></div>
<div class="notification-page" id="notificationPage">
    <div class="notification-header">
        <div class="notification-header__top">
            <h2>Notifications</h2>
            <button type="button" class="notification-header__close" id="closeNotifications" aria-label="Fermer">
                <i class="bx bx-x" style="font-size: 2rem;"></i>
            </button>
        </div>
        <?php if (count($notifications) > 0): ?>
        <form method="POST" class="notification-header__mark-all">
            <button type="submit" name="mark_all_read" class="btn">Tout marquer comme lu</button>
        </form>
        <?php endif; ?>
    </div>
    <div class="notification-content">
        <?php if (count($notifications) > 0): ?>
            <?php foreach ($notifications as $notification): ?>
                <?php
                $tcf_notif_deep_href = '';
                if (!empty($tcf_is_staff_notifications) && !empty($notification['deep_link'])) {
                    $tcf_notif_deep_href = site_href(trim((string) $notification['deep_link']));
                }
                ?>
                <div class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?><?php echo $tcf_notif_deep_href !== '' ? ' notification-item--tcf-deep' : ''; ?>"<?php echo $tcf_notif_deep_href !== '' ? ' data-tcf-notif-deep="' . htmlspecialchars($tcf_notif_deep_href) . '" data-tcf-notif-id="' . (int) $notification['id'] . '"' : ''; ?>>
                    <?php
                    $icon = 'bx bx-info-circle';
                    switch ($notification['type']) {
                        case 'video':
                            $icon = 'bx bx-video';
                            break;
                        case 'topic':
                            $icon = 'bx bx-book';
                            break;
                        case 'message':
                            $icon = 'bx bx-message';
                            break;
                        case 'user':
                            $icon = 'bx bx-user-plus';
                            break;
                        case 'video_comment':
                            $icon = 'bx bx-message-rounded-dots';
                            break;
                        case 'testimonial':
                            $icon = 'bx bx-quote-alt-left';
                            break;
                        case 'subscription':
                            $icon = 'bx bx-gift';
                            break;
                        case 'subscription_staff':
                            $icon = 'bx bx-briefcase';
                            break;
                        default:
                            $icon = 'bx bx-bell';
                            break;
                    }
                    $fullContent = (string) $notification['content'];
                    $notifType = (string) ($notification['type'] ?? '');
                    ?>
                    <i class='<?php echo $icon; ?> notification-icon'></i>
                    <div class="notification-text">
                        <div class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></div>
                        <?php if ($notifType === 'message' || $notifType === 'subscription'): ?>
                            <?php
                            /* Messages communautaires : comme WhatsApp — tout le texte est présent ; troncature visuelle + « Voir plus » */
                            $norm = str_replace(["\r\n", "\r"], "\n", $fullContent);
                            $lineCount = $fullContent === '' ? 0 : substr_count($norm, "\n") + 1;
                            $charLen = function_exists('mb_strlen') ? mb_strlen($fullContent) : strlen($fullContent);
                            $needsToggle = $charLen > 180 || $lineCount > 3;
                            ?>
                        <div class="notification-desc tcf-notif-body tcf-notif-body--wa<?php echo $needsToggle ? ' tcf-notif-body--trunc' : ''; ?>"
                             data-tcf-notif-expanded="<?php echo $needsToggle ? '0' : '1'; ?>">
                            <div class="tcf-notif-text"><?php echo nl2br(htmlspecialchars($fullContent)); ?></div>
                            <?php if ($needsToggle): ?>
                            <button type="button" class="tcf-notif-toggle" aria-expanded="false">Voir plus</button>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                            <?php
                            $excerptLen = 200;
                            if (function_exists('mb_strlen') && function_exists('mb_substr')) {
                                $long = mb_strlen($fullContent) > $excerptLen;
                                $excerpt = $long ? mb_substr($fullContent, 0, $excerptLen) . '…' : $fullContent;
                            } else {
                                $long = strlen($fullContent) > $excerptLen;
                                $excerpt = $long ? substr($fullContent, 0, $excerptLen) . '…' : $fullContent;
                            }
                            ?>
                        <div class="notification-desc tcf-notif-body" data-tcf-notif-expanded="0">
                            <span class="tcf-notif-excerpt"><?php echo nl2br(htmlspecialchars($excerpt)); ?></span>
                            <?php if ($long): ?>
                            <span class="tcf-notif-full" hidden><?php echo nl2br(htmlspecialchars($fullContent)); ?></span>
                            <button type="button" class="tcf-notif-toggle" aria-expanded="false">Voir plus</button>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <div class="notification-time">
                            <?php
                            $date = new DateTime($notification['created_at']);
                            echo $date->format('d/m/Y à H:i');
                            ?>
                        </div>
                    </div>
                    <?php if (!$notification['is_read']): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                            <button type="submit" name="mark_as_read" class="btn" style="padding: 2px 5px; font-size: 0.8rem;">Marquer comme lu</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; padding: 20px; color: #888;">Aucune notification pour le moment.</p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Panneau profil (données MySQL — compte TCF Canada) -->
<div class="profile-overlay profile-overlay-v2" id="profileOverlay"></div>
<div class="profile-page profile-drawer-v2" id="profilePage">
    <div class="profile-drawer-v2__top">
        <div>
            <p class="profile-drawer-v2__eyebrow">Compte</p>
            <h2 class="profile-drawer-v2__title">Mon espace</h2>
        </div>
        <button type="button" class="profile-drawer-v2__close" id="closeProfile" aria-label="Fermer">
            <i class="bx bx-x"></i>
        </button>
    </div>
    <div class="profile-drawer-v2__scroll">
        <?php
        $panelAvatarUrl = $user['avatar_display_url'] ?? null;
        $memberSince = !empty($user['created_at']) ? new DateTime($user['created_at']) : null;
        $lastLogin = !empty($user['last_login']) ? new DateTime($user['last_login']) : null;
        $accountStatus = ($user['status'] ?? 'active') === 'active' ? 'Actif' : 'Inactif';
        $roleKey = $user['role'] ?? 'user';
        if ($roleKey === 'super_admin') {
            $roleLabel = 'Super admin';
        } elseif ($roleKey === 'admin') {
            $roleLabel = 'Administrateur';
        } else {
            $roleLabel = 'Apprenant';
        }
        $days_remaining = 0;
        $progress_percentage = 0;
        $expiration_date = null;
        $subType = (string) ($user['subscription_type'] ?? 'free');
        $expRaw = $user['subscription_expires_at'] ?? null;
        $expStr = ($expRaw !== null && $expRaw !== '') ? (string) $expRaw : '';
        $hasDbExpiry = $expStr !== '' && !str_starts_with($expStr, '0000-00-00');

        if ($hasDbExpiry) {
            try {
                $expiration_date = new DateTime($expStr);
            } catch (Throwable $e) {
                $expiration_date = null;
            }
        }
        // Ancien modèle : pas de date en base, seulement mensuel / annuel → période depuis l’inscription
        if ($expiration_date === null && $subType !== 'free' && !empty($user['created_at'])) {
            if (in_array($subType, ['monthly', 'annual'], true)) {
                try {
                    $created_date = new DateTime($user['created_at']);
                    $expiration_date = clone $created_date;
                    if ($subType === 'monthly') {
                        $expiration_date->modify('+1 month');
                    } else {
                        $expiration_date->modify('+1 year');
                    }
                } catch (Throwable $e) {
                    $expiration_date = null;
                }
            }
        }

        if ($expiration_date !== null && $subType !== 'free') {
            $current_date = new DateTime();
            if ($current_date < $expiration_date) {
                $diffSec = $expiration_date->getTimestamp() - $current_date->getTimestamp();
                $days_remaining = (int) max(0, (int) ceil($diffSec / 86400.0));
            } else {
                $days_remaining = 0;
            }

            $period_start = null;
            if ($hasDbExpiry) {
                $period_start = tcf_subscription_period_start_from_type_and_expiry($subType, $expiration_date);
            }
            if ($period_start === null && !$hasDbExpiry && !empty($user['created_at'])) {
                try {
                    $period_start = new DateTime($user['created_at']);
                } catch (Throwable $e) {
                    $period_start = null;
                }
            }

            if ($period_start !== null) {
                $total_days = max(1, $period_start->diff($expiration_date)->days);
                if ($current_date < $period_start) {
                    $progress_percentage = 0;
                } elseif ($current_date >= $expiration_date) {
                    $progress_percentage = 100;
                } else {
                    $elapsed = $period_start->diff($current_date)->days;
                    $progress_percentage = min(100, max(0, ($elapsed / $total_days) * 100));
                }
            }
        }
        $tcf_cal_y = (int) date('Y');
        $tcf_cal_m = (int) date('n');
        $tcf_activity_dates = [];
        $monthsFrCal = [
            1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril', 5 => 'mai', 6 => 'juin',
            7 => 'juillet', 8 => 'août', 9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
        ];
        $tcf_cal_title = ($monthsFrCal[$tcf_cal_m] ?? '') . ' ' . $tcf_cal_y;
        if (!empty($user['id'])) {
            try {
                $tcfCalStmt = $pdo->prepare(
                    'SELECT activity_date FROM user_activity_days WHERE user_id = ? AND YEAR(activity_date) = ? AND MONTH(activity_date) = ?'
                );
                $tcfCalStmt->execute([(int) $user['id'], $tcf_cal_y, $tcf_cal_m]);
                while ($tcfCalRow = $tcfCalStmt->fetch(PDO::FETCH_ASSOC)) {
                    $tcf_activity_dates[$tcfCalRow['activity_date']] = true;
                }
            } catch (Throwable $e) {
            }
        }
        $tcf_join_date_str = !empty($user['created_at']) ? substr((string) $user['created_at'], 0, 10) : '';
        $tcf_today_str = date('Y-m-d');
        ?>
        <section class="profile-card-v2 profile-card-v2--hero">
            <div class="profile-card-v2__avatar-wrap">
                <div class="profile-avatar <?php echo $panelAvatarUrl ? 'profile-avatar--photo' : 'profile-avatar--placeholder'; ?>">
                    <?php if ($panelAvatarUrl): ?>
                        <img src="<?php echo htmlspecialchars($panelAvatarUrl); ?>" alt="" loading="lazy" decoding="async">
                    <?php else: ?>
                        <i class="bx bx-user" aria-hidden="true"></i>
                    <?php endif; ?>
                </div>
                <button type="button" class="profile-card-v2__avatar-edit" id="editAvatarBtn" aria-label="Modifier la photo">
                    <i class="bx bxs-camera"></i>
                </button>
            </div>
            <h1 class="profile-name" id="profileName"><?php echo htmlspecialchars($user['name']); ?></h1>
            <p class="profile-email profile-card-v2__email"><?php echo htmlspecialchars($user['email']); ?></p>
            <div class="profile-meta-badges">
                <span class="profile-badge profile-badge--role"><?php echo htmlspecialchars($roleLabel); ?></span>
                <span class="profile-badge profile-badge--status"><?php echo htmlspecialchars($accountStatus); ?></span>
            </div>
        </section>

        <section class="profile-card-v2">
            <h3 class="profile-card-v2__heading"><i class="bx bx-bar-chart-alt-2"></i> Activité</h3>
            <div class="profile-stats-grid">
                <div class="profile-stat-card">
                    <span class="profile-stat-label">Membre depuis</span>
                    <strong class="profile-stat-value"><?php echo $memberSince ? $memberSince->format('d/m/Y') : '—'; ?></strong>
                </div>
                <div class="profile-stat-card">
                    <span class="profile-stat-label">Dernière connexion</span>
                    <strong class="profile-stat-value"><?php echo $lastLogin ? $lastLogin->format('d/m/Y H:i') : '—'; ?></strong>
                </div>
            </div>
            <div class="profile-cal" id="profileActivityCalendar"
                data-join="<?php echo htmlspecialchars($tcf_join_date_str); ?>"
                data-today="<?php echo htmlspecialchars($tcf_today_str); ?>"
                data-year="<?php echo (int) $tcf_cal_y; ?>"
                data-month="<?php echo (int) $tcf_cal_m; ?>">
                <p class="profile-cal__subtitle">Jours de visite sur le site</p>
                <div class="profile-cal__toolbar">
                    <button type="button" class="profile-cal__nav" id="profileCalPrev" aria-label="Mois précédent">
                        <i class="bx bx-chevron-left"></i>
                    </button>
                    <h4 class="profile-cal__title" id="profileCalTitle"><?php echo htmlspecialchars($tcf_cal_title); ?></h4>
                    <button type="button" class="profile-cal__nav" id="profileCalNext" aria-label="Mois suivant">
                        <i class="bx bx-chevron-right"></i>
                    </button>
                </div>
                <div class="profile-cal__legend">
                    <span class="profile-cal__legend-item"><span class="profile-cal__sw profile-cal__sw--present"></span> Présent</span>
                    <span class="profile-cal__legend-item"><span class="profile-cal__sw profile-cal__sw--absent"></span> Absent</span>
                </div>
                <div class="profile-cal__weekdays" aria-hidden="true">
                    <span>Lun</span><span>Mar</span><span>Mer</span><span>Jeu</span><span>Ven</span><span>Sam</span><span>Dim</span>
                </div>
                <div class="profile-cal__grid" id="profileCalGrid" role="grid" aria-label="Calendrier de présence">
                    <?php echo tcf_profile_activity_calendar_cells($tcf_cal_y, $tcf_cal_m, $tcf_activity_dates, $tcf_today_str, $tcf_join_date_str ?: null); ?>
                </div>
            </div>
        </section>

        <?php $tcf_is_staff_profile = in_array($user['role'] ?? '', ['admin', 'super_admin'], true); ?>
        <section class="profile-card-v2">
            <h3 class="profile-card-v2__heading"><i class="bx bx-crown"></i> Abonnement</h3>
            <?php if ($tcf_is_staff_profile): ?>
            <div class="profile-sub-v2 profile-sub-v2--staff">
                <p class="profile-staff-note"><i class="bx bx-shield-quarter"></i> Compte <?php echo $user['role'] === 'super_admin' ? 'super administrateur' : 'administrateur'; ?> : l’abonnement premium n’est pas requis pour accéder aux outils d’administration.</p>
                <div class="profile-sub-v2__row">
                    <span class="subscription-badge subscription-badge--staff">Accès staff</span>
                    <a class="subscription-manage-link" href="<?php echo htmlspecialchars(site_href('index.php')); ?>">Accueil du site</a>
                </div>
            </div>
            <?php else: ?>
            <?php
            $tcf_premium_ok = tcf_user_has_premium_access($user);
            $tcf_expires_fmt = '';
            if (!empty($user['subscription_expires_at'])) {
                try {
                    $tcf_expires_fmt = (new DateTime((string) $user['subscription_expires_at']))->format('d/m/Y à H:i');
                } catch (Throwable $e) {
                    $tcf_expires_fmt = '';
                }
            }
            ?>
            <div class="profile-sub-v2">
                <div class="profile-sub-v2__row">
                    <span class="subscription-badge"><?php echo htmlspecialchars(tcf_subscription_label($user['subscription_type'] ?? 'free')); ?></span>
                    <?php if ($tcf_sub_sales_enabled): ?>
                    <a class="subscription-manage-link" href="<?php echo htmlspecialchars(site_href('abonnement.php')); ?>">Gérer mon abonnement</a>
                    <?php endif; ?>
                </div>
                <p class="profile-sub-v2__premium-status<?php echo $tcf_premium_ok ? ' is-active' : ' is-inactive'; ?>" role="status">
                    <?php if ($tcf_sub_platform_free): ?>
                        <i class="bx bx-check-circle" aria-hidden="true"></i> Mode gratuit plateforme : tout le contenu <strong>premium</strong> est accessible.
                    <?php elseif ($tcf_premium_ok): ?>
                        <i class="bx bx-check-circle" aria-hidden="true"></i> Accès au contenu <strong>premium</strong> actif (chaîne Vidéos, etc.).
                    <?php elseif (($user['subscription_type'] ?? 'free') === 'free'): ?>
                        <i class="bx bx-info-circle" aria-hidden="true"></i> Sans formule payante : les vidéos marquées « premium » restent réservées aux abonnés.
                    <?php else: ?>
                        <i class="bx bx-error-circle" aria-hidden="true"></i> Votre accès payant n’est plus valide — renouvelez depuis la page Abonnement.
                    <?php endif; ?>
                </p>
                <?php if ($tcf_expires_fmt !== ''): ?>
                <p class="profile-sub-v2__expiry">Fin d’accès prévue : <strong><?php echo htmlspecialchars($tcf_expires_fmt); ?></strong></p>
                <?php endif; ?>
                <?php if (($user['subscription_type'] ?? 'free') !== 'free'): ?>
                <div class="progress-container">
                    <div class="progress-info">
                        <span>Temps restant sur la période en cours</span>
                        <span id="daysRemaining"><?php echo (int) $days_remaining; ?> jour<?php echo (int) $days_remaining !== 1 ? 's' : ''; ?></span>
                    </div>
                    <div class="progress-bar profile-sub-v2__bar">
                        <div class="progress-fill" id="progressFill" style="width: <?php echo htmlspecialchars((string) $progress_percentage); ?>%;"></div>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (count($tcf_sub_payment_rows) > 0): ?>
                <h4 class="profile-sub-v2__history-title">Historique des paiements</h4>
                <div class="profile-sub-history-wrap">
                    <table class="profile-sub-history-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Formule</th>
                                <th>Montant</th>
                                <th>Moyen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tcf_sub_payment_rows as $pr): ?>
                            <tr>
                                <td><?php echo htmlspecialchars((string) ($pr['created_at'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars((string) ($pr['plan_label'] !== '' && $pr['plan_label'] !== null ? $pr['plan_label'] : ($pr['plan_key'] ?? ''))); ?></td>
                                <td><?php echo htmlspecialchars(number_format((float) ($pr['amount'] ?? 0), 2, ',', ' ')); ?> <?php echo htmlspecialchars((string) ($pr['currency'] ?? 'USD')); ?></td>
                                <td><?php echo htmlspecialchars((string) ($pr['payment_method'] ?? '')); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </section>

        <section class="profile-card-v2">
            <h3 class="profile-card-v2__heading"><i class="bx bx-cog"></i> Paramètres du compte</h3>
            <nav class="profile-v2-menu" aria-label="Paramètres">
                <button type="button" class="profile-v2-menu__row" id="usernameValue">
                    <span class="profile-v2-menu__icon"><i class="bx bx-edit-alt"></i></span>
                    <span class="profile-v2-menu__mid">
                        <small>Nom affiché</small>
                        <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                    </span>
                    <i class="bx bx-chevron-right profile-v2-menu__chev"></i>
                </button>
                <div class="profile-v2-menu__row profile-v2-menu__row--static">
                    <span class="profile-v2-menu__icon"><i class="bx bx-envelope"></i></span>
                    <span class="profile-v2-menu__mid">
                        <small>Adresse e-mail</small>
                        <strong><?php echo htmlspecialchars($user['email']); ?></strong>
                    </span>
                </div>
                <button type="button" class="profile-v2-menu__row" id="changePasswordBtn">
                    <span class="profile-v2-menu__icon"><i class="bx bx-lock-alt"></i></span>
                    <span class="profile-v2-menu__mid">
                        <small>Mot de passe</small>
                        <strong>Modifier en toute sécurité</strong>
                    </span>
                    <i class="bx bx-chevron-right profile-v2-menu__chev"></i>
                </button>
            </nav>
        </section>
    </div>
    <div class="profile-drawer-v2__footer">
        <button type="button" class="tcf-btn tcf-btn--ghost" id="cancelBtn">Fermer</button>
        <a href="<?php echo htmlspecialchars(site_href('logout.php')); ?>" class="tcf-btn tcf-btn--primary"><i class="bx bx-log-out"></i> Déconnexion</a>
    </div>
</div>

<!-- Modale nom (API JSON → MySQL) -->
<div class="tcf-modal-premium" id="usernameModal" style="display:none" aria-hidden="true">
    <div class="tcf-modal-premium__backdrop" data-close-username="1"></div>
    <div class="tcf-modal-premium__dialog" role="dialog" aria-labelledby="usernameModalTitle">
        <button type="button" class="tcf-modal-premium__x" id="closeUsernameModal" aria-label="Fermer">&times;</button>
        <div class="tcf-modal-premium__head">
            <span class="tcf-modal-premium__icon"><i class="bx bx-user-circle"></i></span>
            <h3 id="usernameModalTitle">Nom affiché</h3>
            <p>Visible sur votre espace et enregistré en base de données.</p>
        </div>
        <label class="tcf-field-label" for="usernameInput">Nom d'utilisateur</label>
        <input type="text" class="tcf-field-input" id="usernameInput" value="<?php echo htmlspecialchars($user['name']); ?>" minlength="4" maxlength="100" autocomplete="username">
        <p class="tcf-field-hint" id="usernameModalError" hidden></p>
        <div class="tcf-modal-premium__actions">
            <button type="button" class="tcf-btn tcf-btn--ghost" id="cancelUsernameEdit">Annuler</button>
            <button type="button" class="tcf-btn tcf-btn--primary" id="saveUsernameBtn">
                <span class="tcf-btn__label">Enregistrer</span>
                <span class="tcf-btn__spinner" hidden aria-hidden="true"></span>
            </button>
        </div>
    </div>
</div>

<!-- Modale photo (POST classique → MySQL + fichier) -->
<div class="modal" id="avatarModal">
    <div class="modal-content modal-content--crop tcf-modal-avatar">
        <div class="modal-header">
            <h3 class="modal-title">Photo de profil</h3>
            <button type="button" class="close-btn" id="closeAvatarModal">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data" id="avatarForm">
            <input type="hidden" name="update_avatar" value="1">
            <input type="hidden" name="avatar_data_url" id="avatar_data_url" value="">
            <p class="modal-hint">Image JPG, PNG ou WebP — recadrez puis <strong>OK</strong>, puis enregistrez.</p>
            <div class="avatar-crop-stage">
                <div class="avatar-crop-box">
                    <img src="" alt="" id="avatarCropImage" class="avatar-crop-img">
                </div>
            </div>
            <input type="file" id="avatarUpload" name="avatar" accept="image/jpeg,image/png,image/webp" style="display: none;">
            <div class="avatar-modal-actions">
                <button type="button" class="tcf-btn tcf-btn--ghost" id="uploadAvatarBtn">Choisir une image</button>
                <button type="button" class="tcf-btn tcf-btn--secondary" id="avatarCropOk" disabled>OK — valider le cadrage</button>
            </div>
            <div class="avatar-preview" id="avatarPreviewBox" hidden>
                <p class="avatar-preview__title">Aperçu avant enregistrement</p>
                <div class="avatar-preview__img-wrap">
                    <img src="" alt="Aperçu de la photo de profil recadrée" id="avatarPreviewImage" class="avatar-preview__img">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="tcf-btn tcf-btn--ghost" id="cancelAvatarEdit">Annuler</button>
                <button type="submit" class="tcf-btn tcf-btn--primary" id="avatarSaveBtn" disabled>Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- Wizard mot de passe (API JSON → MySQL + e-mail) -->
<div class="tcf-modal-premium" id="passwordModal" style="display:none" aria-hidden="true">
    <div class="tcf-modal-premium__backdrop" data-close-password="1"></div>
    <div class="tcf-modal-premium__dialog tcf-modal-premium__dialog--wide" role="dialog" aria-labelledby="pwdModalTitle">
        <button type="button" class="tcf-modal-premium__x" id="closePasswordModal" aria-label="Fermer">&times;</button>
        <div class="tcf-modal-premium__head">
            <span class="tcf-modal-premium__icon tcf-modal-premium__icon--lock"><i class="bx bx-shield-quarter"></i></span>
            <h3 id="pwdModalTitle">Sécurité du compte</h3>
            <p>Définissez directement un nouveau mot de passe — la mise à jour est enregistrée en base.</p>
        </div>
        <div class="pwd-premium__steps" aria-hidden="true">
            <div class="pwd-premium__step is-active" data-pwd-step-indicator="1"><span>1</span><small>Code</small></div>
            <div class="pwd-premium__step-line"></div>
            <div class="pwd-premium__step" data-pwd-step-indicator="2"><span>2</span><small>Vérifier</small></div>
            <div class="pwd-premium__step-line"></div>
            <div class="pwd-premium__step" data-pwd-step-indicator="3"><span>3</span><small>Nouveau</small></div>
        </div>
        <div class="pwd-premium__alert" id="pwdPremiumAlert" role="alert" hidden></div>

        <div class="pwd-premium__panel is-visible" id="pwdPremiumPanel1">
            <p class="pwd-premium__lead">La vérification par code est désactivée. Utilisez directement le formulaire de nouveau mot de passe.</p>
            <button type="button" class="tcf-btn tcf-btn--primary tcf-btn--block tcf-btn--lg" id="pwdBtnSendCode">
                <span class="tcf-btn__label">Recevoir le code</span>
                <span class="tcf-btn__spinner" hidden></span>
            </button>
            <button type="button" class="tcf-btn tcf-btn--ghost tcf-btn--block" id="pwdBtnResend" hidden>Renvoyer le code</button>
        </div>

        <div class="pwd-premium__panel" id="pwdPremiumPanel2">
            <p class="pwd-premium__lead">Saisissez les 6 chiffres reçus par e-mail.</p>
            <div class="pwd-otp" id="pwdOtpRow" role="group" aria-label="Code à 6 chiffres">
                <?php for ($i = 0; $i < 6; $i++): ?>
                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="pwd-otp__box" data-otp-index="<?php echo $i; ?>" autocomplete="one-time-code" aria-label="Chiffre <?php echo $i + 1; ?>">
                <?php endfor; ?>
            </div>
            <button type="button" class="tcf-btn tcf-btn--primary tcf-btn--block" id="pwdBtnVerify">
                <span class="tcf-btn__label">Vérifier le code</span>
                <span class="tcf-btn__spinner" hidden></span>
            </button>
            <button type="button" class="tcf-btn tcf-btn--text tcf-btn--block" id="pwdBackToRequest">← Retour</button>
        </div>

        <div class="pwd-premium__panel" id="pwdPremiumPanel3" aria-hidden="true">
            <p class="pwd-premium__lead pwd-premium__lead--small">Définissez un mot de passe fort (8+ caractères, majuscule, minuscule, chiffre, symbole).</p>
            <label class="tcf-field-label" for="current_password">Ancien mot de passe</label>
            <div class="tcf-password-wrap">
                <input type="password" class="tcf-field-input" id="current_password" minlength="8" autocomplete="current-password">
                <button type="button" class="tcf-password-wrap__toggle" id="toggleCurrentPassword" aria-label="Afficher le mot de passe"><i class="bx bx-hide"></i></button>
            </div>
            <label class="tcf-field-label" for="new_password">Nouveau mot de passe</label>
            <div class="tcf-password-wrap">
                <input type="password" class="tcf-field-input" id="new_password" minlength="8" autocomplete="new-password">
                <button type="button" class="tcf-password-wrap__toggle" id="toggleNewPassword" aria-label="Afficher le mot de passe"><i class="bx bx-hide"></i></button>
            </div>
            <label class="tcf-field-label" for="confirm_password">Confirmation</label>
            <div class="tcf-password-wrap">
                <input type="password" class="tcf-field-input" id="confirm_password" minlength="8" autocomplete="new-password">
                <button type="button" class="tcf-password-wrap__toggle" id="toggleConfirmPassword" aria-label="Afficher le mot de passe"><i class="bx bx-hide"></i></button>
            </div>
            <p class="tcf-field-hint">Minimum 8 caractères — identique aux règles d'inscription.</p>
            <button type="button" class="tcf-btn tcf-btn--primary tcf-btn--block tcf-btn--lg" id="pwdBtnFinalSave">
                <span class="tcf-btn__label">Enregistrer le mot de passe</span>
                <span class="tcf-btn__spinner" hidden></span>
            </button>
        </div>

        <div class="pwd-premium__footer">
            <button type="button" class="tcf-btn tcf-btn--text" id="cancelPasswordEdit">Fermer</button>
        </div>
    </div>
</div>

<?php if (empty($tcf_profile_panel_skip_assets)) { ?>
<?php /* profile_panel.css est déjà chargé via @import dans Assets/css/header_footer.css — pas de second <link> (évite doublons / conflits de cascade). */ ?>
<link rel="stylesheet" href="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.css">
<script src="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script src="<?php echo htmlspecialchars(site_href('Assets/javascript/profile_panel.js')); ?>"></script>
<?php } ?>
