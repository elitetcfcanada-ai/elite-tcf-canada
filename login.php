<?php

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/tcf_notifications_helper.php';
require_once __DIR__ . '/includes/admin_notifications.php';
require_once __DIR__ . '/includes/auth_flash.php';

/**
 * Paramètre next / redirect interne uniquement (fichier .php sous le site).
 */
function tcf_login_safe_next(?string $raw): ?string
{
    if ($raw === null || $raw === '') {
        return null;
    }
    $raw = trim(str_replace('\\', '/', $raw));
    if ($raw === '' || strpos($raw, '..') !== false) {
        return null;
    }
    if (preg_match('#^https?:#i', $raw)) {
        return null;
    }
    // Délimiteur ~ (et non #) : sinon [^#] est coupé et ']' devient un modificateur invalide.
    if (!preg_match('~^[A-Za-z0-9_.\-/]+\.php(\?[^#]*)?(#[\w\-]*)?$~', $raw)) {
        return null;
    }

    return $raw;
}

$tcf_login_next = tcf_login_safe_next(isset($_GET['next']) ? (string) $_GET['next'] : null);

function tcf_login_redirect_logged_user(array $user, ?string $nextPost = null): void
{
    $next = tcf_login_safe_next($nextPost);
    if ($next !== null && ($user['role'] ?? '') === 'user') {
        header('Location: ' . site_href($next));
        exit;
    }
    switch ($user['role'] ?? 'user') {
        case 'super_admin':
        case 'admin':
            header('Location: admin/superAdmin.php');
            break;
        default:
            header('Location: index.php');
    }
    exit;
}

function tcf_login_apply_session(array $user, PDO $pdo): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['username'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['is_admin'] = in_array($user['role'], ['admin', 'super_admin'], true);
    $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([(int) $user['id']]);
}

// Déjà connecté → espace personnel (pas le formulaire login)
if (!empty($_SESSION['user_id']) && empty($_POST['login_start']) && empty($_POST['register_start'])) {
    try {
        $st = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $st->execute([(int) $_SESSION['user_id']]);
        $already = $st->fetch(PDO::FETCH_ASSOC);
        if ($already) {
            tcf_login_redirect_logged_user($already, isset($_GET['next']) ? (string) $_GET['next'] : null);
        }
    } catch (Throwable $e) {
        // laisser afficher le formulaire
    }
}

// Ancien flux OTP : nettoyer pour ne pas laisser d’écran bloqué
unset($_SESSION['tcf_reg_pending'], $_SESSION['tcf_login_pending_uid']);

// --- Inscription directe (sans code e-mail) ---
if (isset($_POST['register_start'])) {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirmPassword'] ?? '');

    $err = tcf_validate_registration_name_email_password($name, $email, $password, $confirmPassword, $pdo);
    if ($err !== null) {
        tcf_auth_flash('error', $err, 'register');
        $q = tcf_login_safe_next(isset($_POST['register_next']) ? (string) $_POST['register_next'] : null);
        header('Location: login.php' . ($q !== null ? ('?next=' . rawurlencode($q)) : ''));
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Insertion sans id manuel → AUTO_INCREMENT obligatoire sur users.id
        $stmt = $pdo->prepare(
            "INSERT INTO users (name, email, password, role, subscription_type, status, created_at) VALUES (?, ?, ?, 'user', 'free', 'active', NOW())"
        );
        $stmt->execute([$name, $email, $password_hash]);
        
        $user_id = (int) $pdo->lastInsertId();
        if ($user_id <= 0) {
            try {
                $pdo->exec('DELETE FROM users WHERE id = 0 AND email = ' . $pdo->quote($email));
            } catch (Throwable $e) {
            }
            throw new RuntimeException(
                'Erreur ID utilisateur (AUTO_INCREMENT). Demandez à l\'admin d\'exécuter scripts/repair_database.php?key=REPAIR_TCF_2026'
            );
        }
        
        // Récupérer l'utilisateur créé
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new RuntimeException('Impossible de charger le compte créé.');
        }

        tcf_login_apply_session($user, $pdo);
        tcf_remember_issue($pdo, (int) $user['id']);
        $welcomeName = trim((string) ($user['name'] ?? $name));
        tcf_auth_flash(
            'success',
            $welcomeName !== ''
                ? ('Bienvenue, ' . $welcomeName . ' ! Votre compte a été créé avec succès.')
                : 'Bienvenue ! Votre compte a été créé avec succès.'
        );

        tcf_notification_insert(
            $pdo,
            $user_id,
            'welcome',
            'Bienvenue sur ELITE TCF Canada',
            'Votre compte est prêt. Explorez les épreuves, les vidéos et suivez votre progression.',
            site_href('index.php')
        );

        tcf_add_staff_notification(
            $pdo,
            'user',
            'Nouvel inscrit',
            $name . ' (' . $email . ') vient de créer un compte.',
            'admin/superAdmin.php?sa_focus=user&id=' . $user_id
        );
        
        $regNext = tcf_login_safe_next(isset($_POST['register_next']) ? (string) $_POST['register_next'] : null);
        if ($regNext !== null && ($user['role'] ?? '') === 'user') {
            header('Location: ' . site_href($regNext));
            exit;
        }
        header('Location: index.php');
        exit;
        
    } catch (PDOException $e) {
        $code = isset($e->errorInfo[1]) ? (int) $e->errorInfo[1] : 0;
        if ($code === 1062) {
            tcf_auth_flash('error', 'Cet email est déjà utilisé. Connectez-vous ou utilisez une autre adresse.', 'register');
        } else {
            error_log('TCF inscription PDO: ' . $e->getMessage());
            tcf_auth_flash('error', "Erreur technique lors de l'inscription. Veuillez réessayer.", 'register');
        }
        $q = tcf_login_safe_next(isset($_POST['register_next']) ? (string) $_POST['register_next'] : null);
        header('Location: login.php' . ($q !== null ? ('?next=' . rawurlencode($q)) : ''));
        exit;
    } catch (Throwable $e) {
        error_log('TCF inscription: ' . $e->getMessage());
        tcf_auth_flash('error', "Impossible de finaliser l'inscription. Veuillez réessayer.", 'register');
        $q = tcf_login_safe_next(isset($_POST['register_next']) ? (string) $_POST['register_next'] : null);
        header('Location: login.php' . ($q !== null ? ('?next=' . rawurlencode($q)) : ''));
        exit;
    }
}

// --- Connexion directe (sans code e-mail) ---
if (isset($_POST['login_start'])) {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    
    $loginNextKeep = tcf_login_safe_next(isset($_POST['login_next']) ? (string) $_POST['login_next'] : null);
    $loginBack = 'login.php' . ($loginNextKeep !== null ? ('?next=' . rawurlencode($loginNextKeep)) : '');

    if ($email === '' && $password === '') {
        tcf_auth_flash('error', 'Veuillez saisir votre email et votre mot de passe.', 'login');
        header('Location: ' . $loginBack);
        exit;
    }
    if ($email === '') {
        tcf_auth_flash('error', 'Veuillez saisir votre adresse email.', 'login');
        header('Location: ' . $loginBack);
        exit;
    }
    if ($password === '') {
        tcf_auth_flash('error', 'Veuillez saisir votre mot de passe.', 'login');
        header('Location: ' . $loginBack);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        tcf_auth_flash('error', "L'adresse email n'est pas valide.", 'login');
        header('Location: ' . $loginBack);
        exit;
    }

    try {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            tcf_auth_flash('error', 'Email ou mot de passe incorrect.', 'login');
            header('Location: ' . $loginBack);
            exit;
        }

        $hash = (string) ($user['password'] ?? '');
        if ($hash === '' || !password_verify($password, $hash)) {
            tcf_auth_flash('error', 'Email ou mot de passe incorrect.', 'login');
            header('Location: ' . $loginBack);
            exit;
        }

        $status = strtolower(trim((string) ($user['status'] ?? 'active')));
        if ($status === 'inactive' || $status === 'disabled') {
            tcf_auth_flash('error', 'Ce compte est désactivé. Contactez le support.', 'login');
            header('Location: ' . $loginBack);
            exit;
        }
        if ($status === 'banned' || $status === 'suspended') {
            tcf_auth_flash('error', 'Ce compte est suspendu. Contactez le support.', 'login');
            header('Location: ' . $loginBack);
            exit;
        }
        if ($status !== '' && $status !== 'active') {
            tcf_auth_flash('error', 'Impossible de vous connecter pour le moment (compte non actif).', 'login');
            header('Location: ' . $loginBack);
            exit;
        }

        tcf_login_apply_session($user, $pdo);
        tcf_remember_issue($pdo, (int) $user['id']);
        $welcomeName = trim((string) ($user['name'] ?? ''));
        tcf_auth_flash(
            'success',
            $welcomeName !== ''
                ? ('Bienvenue, ' . $welcomeName . ' ! Connexion réussie.')
                : 'Bienvenue ! Connexion réussie.'
        );
        tcf_login_redirect_logged_user($user, isset($_POST['login_next']) ? (string) $_POST['login_next'] : null);

    } catch (PDOException $e) {
        error_log('TCF connexion PDO: ' . $e->getMessage());
        tcf_auth_flash('error', 'Erreur technique lors de la connexion. Veuillez réessayer.', 'login');
        header('Location: ' . $loginBack);
        exit;
    }
}

$tcf_auth_flash = tcf_auth_flash_consume();
$tcf_flash_form = is_array($tcf_auth_flash) ? ($tcf_auth_flash['form'] ?? null) : null;
$tcf_show_register = ($tcf_flash_form === 'register');
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php
    $tcf_brand_title = 'Connexion | ELITE TCF CANADA';
    $tcf_brand_desc = 'Connectez-vous ou créez votre compte ELITE TCF CANADA pour accéder à la préparation TCF Canada.';
    $tcf_brand_keywords = 'connexion ELITE TCF CANADA, inscription TCF Canada, compte préparation TCF';
    $tcf_seo_robots = 'noindex, follow';
    include __DIR__ . '/includes/tcf_brand_head.php';
    ?>
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="Assets/css/theme-vars.css">
    <link rel="stylesheet" href="Assets/css/style_login.css?v=auth-alert-2">
</head>

<body>
    <?php
    $tcf_alert_html = '';
    if (is_array($tcf_auth_flash) && ($tcf_auth_flash['message'] ?? '') !== '') {
        $atype = (string) ($tcf_auth_flash['type'] ?? 'error');
        if (!in_array($atype, ['success', 'error', 'warning', 'info'], true)) {
            $atype = 'error';
        }
        $icon = $atype === 'success' ? 'bx-check-circle' : ($atype === 'warning' ? 'bx-error' : 'bx-error-circle');
        $tcf_alert_html =
            '<div class="tcf-auth-alert tcf-auth-alert--' . htmlspecialchars($atype) . '" role="alert">'
            . '<i class="bx ' . $icon . '" aria-hidden="true"></i>'
            . '<span>' . htmlspecialchars((string) $tcf_auth_flash['message']) . '</span>'
            . '</div>';
    }
    ?>
    <div class="container<?php echo $tcf_show_register ? ' active' : ''; ?>">

        <!-- Formulaire de Connexion -->
        <div class="form-box login">
            <form id="loginForm" method="post">
                <input type="hidden" name="login_start" value="1">
                <?php if ($tcf_login_next !== null): ?>
                    <input type="hidden" name="login_next" value="<?php echo htmlspecialchars($tcf_login_next); ?>">
                <?php endif; ?>
                <h1>Connexion</h1>
                <?php if (!$tcf_show_register && $tcf_alert_html !== '') {
                    echo $tcf_alert_html;
                } ?>

                <div class="input-box">
                    <input type="email" name="email" id="loginEmail" placeholder="Email" required>
                    <i class='bx bxs-envelope'></i>
                    <span class="error-message" aria-live="polite"></span>
                </div>
                <div class="input-box">
                    <input type="password" name="password" id="loginPassword" placeholder="Mot de passe" required>
                    <i class='bx bxs-lock-alt'></i>
                    <span class="error-message" aria-live="polite"></span>
                </div>
                <div class="forgot-link">
                    <a href="<?php echo htmlspecialchars(site_href('resetPassword.php')); ?>">Mot de passe oublié ?</a>
                </div>
                <button type="submit" class="btn">Connexion</button>
            </form>
        </div>

        <!-- Formulaire d'Inscription -->
        <div class="form-box register">
            <form id="registerForm" method="post">
                <input type="hidden" name="register_start" value="1">
                <?php if ($tcf_login_next !== null): ?>
                    <input type="hidden" name="register_next" value="<?php echo htmlspecialchars($tcf_login_next); ?>">
                <?php endif; ?>
                <h1>Inscription</h1>
                <?php if ($tcf_show_register && $tcf_alert_html !== '') {
                    echo $tcf_alert_html;
                } ?>

                <div class="input-box">
                    <input type="text" id="name" name="name" placeholder="Nom complet" minlength="4" required>
                    <i class='bx bxs-user'></i>
                    <span class="error-message" aria-live="polite"></span>
                </div>
                <div class="input-box">
                    <input type="email" id="email" name="email" placeholder="Email" required>
                    <i class='bx bxs-envelope'></i>
                    <span class="error-message" aria-live="polite"></span>
                </div>
                <div class="input-box">
                    <input type="password" id="password" name="password" placeholder="Mot de passe" minlength="8" required>
                    <i class='bx bxs-lock-alt'></i>
                    <small style="display:block;font-size:11px;color:#64748b;margin-top:4px;">8+ caractères, majuscule, minuscule, chiffre et symbole</small>
                    <span class="error-message" aria-live="polite"></span>
                </div>
                <div class="input-box">
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirmer le mot de passe" required>
                    <i class='bx bxs-lock-alt'></i>
                    <span class="error-message" aria-live="polite"></span>
                </div>
                <button type="submit" class="btn">Créer mon compte</button>
            </form>
        </div>

        <div class="toggle-box">
            <div class="toggle-panel toggle-left">
                <h1>Bienvenue</h1>
                <p>Pas encore de compte ?</p>
                <button type="button" class="btn register-btn">S'inscrire</button>
            </div>
            <div class="toggle-panel toggle-right">
                <h1>Content de vous revoir</h1>
                <p>Déjà inscrit ?</p>
                <button type="button" class="btn login-btn">Connexion</button>
            </div>
        </div>
    </div>
    <script src="Assets/javascript/script_login.js?v=auth-alert-2"></script>
    <?php include __DIR__ . '/includes/cookie_banner.php'; ?>
</body>

</html>
