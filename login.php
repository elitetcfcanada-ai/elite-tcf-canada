<?php

require_once __DIR__ . '/includes/config.php';

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
        $_SESSION['error'] = $err;
        header('Location: login.php');
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Insertion simple et robuste compatible avec Hostinger
        $stmt = $pdo->prepare(
            "INSERT INTO users (name, email, password, role, subscription_type, status, created_at) VALUES (?, ?, ?, 'user', 'free', 'active', NOW())"
        );
        $stmt->execute([$name, $email, $password_hash]);
        
        $user_id = (int) $pdo->lastInsertId();
        if ($user_id <= 0) {
            throw new RuntimeException('Erreur lors de la création du compte.');
        }
        
        // Récupérer l'utilisateur créé
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new RuntimeException('Impossible de charger le compte créé.');
        }

        tcf_login_apply_session($user, $pdo);
        $_SESSION['success'] = 'Bienvenue ! Votre compte a été créé avec succès.';
        
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
            $_SESSION['error'] = 'Cet email est déjà utilisé.';
        } else {
            error_log('TCF inscription PDO: ' . $e->getMessage());
            $_SESSION['error'] = "Erreur technique lors de l'inscription. Code: $code";
        }
        header('Location: login.php');
        exit;
    } catch (Throwable $e) {
        error_log('TCF inscription: ' . $e->getMessage());
        $_SESSION['error'] = "Impossible de finaliser l'inscription: " . $e->getMessage();
        header('Location: login.php');
        exit;
    }
}

// --- Connexion directe (sans code e-mail) ---
if (isset($_POST['login_start'])) {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    
    if ($email === '' || $password === '') {
        $_SESSION['error'] = 'Tous les champs sont obligatoires.';
        header('Location: login.php');
        exit;
    }
    
    try {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $_SESSION['error'] = 'Email ou mot de passe incorrect.';
            header('Location: login.php');
            exit;
        }
        
        if (!password_verify($password, $user['password'])) {
            $_SESSION['error'] = 'Email ou mot de passe incorrect.';
            header('Location: login.php');
            exit;
        }
        
        // Vérifier le statut du compte si la colonne existe
        if (isset($user['status']) && $user['status'] === 'inactive') {
            $_SESSION['error'] = 'Ce compte est désactivé. Contactez l\'administrateur.';
            header('Location: login.php');
            exit;
        }

        tcf_login_apply_session($user, $pdo);
        tcf_login_redirect_logged_user($user, isset($_POST['login_next']) ? (string) $_POST['login_next'] : null);
        
    } catch (PDOException $e) {
        error_log('TCF connexion PDO: ' . $e->getMessage());
        $_SESSION['error'] = 'Erreur technique lors de la connexion. Veuillez réessayer.';
        header('Location: login.php');
        exit;
    }
}

$tcf_flash_error = $_SESSION['error'] ?? null;
$tcf_flash_success = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $tcf_brand_title = 'Inscription / Connexion — ELITE TCF CANADA';
    include __DIR__ . '/includes/tcf_brand_head.php';
    ?>
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="Assets/css/theme-vars.css">
    <link rel="stylesheet" href="Assets/css/style_login.css">
    <title>Inscription / Connexion — ELITE TCF CANADA</title>
</head>

<body>
    <div class="container">
        <?php if ($tcf_flash_error !== null && $tcf_flash_error !== ''): ?>
            <div class="error-message tcf-login-flash" style="display: block; text-align: center; margin-bottom: 15px; grid-column: 1 / -1;">
                <?php echo htmlspecialchars((string) $tcf_flash_error); ?>
            </div>
        <?php endif; ?>
        <?php if ($tcf_flash_success !== null && $tcf_flash_success !== ''): ?>
            <div class="success-message tcf-login-flash" style="display: block; text-align: center; margin-bottom: 15px; color: green; grid-column: 1 / -1;">
                <?php echo htmlspecialchars((string) $tcf_flash_success); ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire de Connexion -->
        <div class="form-box login">
            <form id="loginForm" method="post">
                <input type="hidden" name="login_start" value="1">
                <?php if ($tcf_login_next !== null): ?>
                    <input type="hidden" name="login_next" value="<?php echo htmlspecialchars($tcf_login_next); ?>">
                <?php endif; ?>
                <h1>Connexion</h1>

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
    <script src="Assets/javascript/script_login.js"></script>
</body>

</html>
