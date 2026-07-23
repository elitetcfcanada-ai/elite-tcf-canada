    </div> <!-- .main-content -->

    <?php
    if (empty($tcf_profile_panel_user) && !empty($_SESSION['user_id']) && isset($pdo) && $pdo instanceof PDO) {
        try {
            if (!function_exists('tcf_avatar_public_url')) {
                require_once __DIR__ . '/../../includes/avatar_helper.php';
            }
            $stProf = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
            $stProf->execute([(int) $_SESSION['user_id']]);
            $tcf_profile_panel_user = $stProf->fetch(PDO::FETCH_ASSOC) ?: null;
            if ($tcf_profile_panel_user) {
                $avSync = tcf_sync_user_avatar_from_disk($pdo, (int) $tcf_profile_panel_user['id'], $tcf_profile_panel_user['avatar'] ?? null);
                $tcf_profile_panel_user['avatar_resolved'] = $avSync;
                $tcf_profile_panel_user['avatar_display_url'] = tcf_avatar_public_url($avSync);
            }
        } catch (Throwable $e) {
            $tcf_profile_panel_user = null;
        }
    }

    if (!empty($tcf_profile_panel_user['id'])) {
        $user = $tcf_profile_panel_user;
        $tcf_profile_panel_skip_assets = true;
        include __DIR__ . '/../../includes/profile_panel_logged_in.php';
        echo '<script src="' . htmlspecialchars(site_href('Assets/javascript/profile_panel.js')) . '?v=notif-ui-14"></script>';
    }
    ?>
</body>
</html>
