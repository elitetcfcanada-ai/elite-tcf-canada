<?php
require_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $tcf_brand_title = 'Mot de passe oublié — ELITE TCF CANADA';
    include __DIR__ . '/includes/tcf_brand_head.php';
    ?>
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/theme-vars.css')); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(site_href('Assets/css/style_resetPassword.css')); ?>">
    <title>Mot de passe oublié — ELITE TCF CANADA</title>
</head>
<body>

<div class="container reset-container" id="tcf-reset-root" data-api-url="<?php echo htmlspecialchars(site_href('auth_api.php')); ?>" data-login-url="<?php echo htmlspecialchars(site_href('login.php')); ?>">
    <div class="form-box reset-form">
        <form id="email-form" class="active-step">
            <div class="reset-icon">
                <i class="bx bxs-lock-alt"></i>
            </div>
            <h1>Mot de passe oublié</h1>
            <p class="instruction-text">Nous enverrons un code à 6 chiffres (valide 1 minute). Vous pourrez le renvoyer si besoin.</p>
            <div class="input-group">
                <input type="email" id="reset-email" name="email" placeholder="Votre e-mail" required autocomplete="email">
            </div>
            <button type="submit" class="btn" id="btn-reset-send">Envoyer le code</button>
            <p class="login-link">Retour à la <a href="<?php echo htmlspecialchars(site_href('login.php')); ?>">connexion</a></p>
        </form>

        <form id="code-form" class="hidden-step">
            <div class="reset-icon">
                <i class="bx bxs-check-shield"></i>
            </div>
            <h1>Vérification</h1>
            <p class="instruction-text" id="code-instruction">Code à 6 chiffres</p>
            <input type="hidden" id="reset-email-hidden" value="">
            <div class="input-group">
                <input type="text" id="reset-otp" inputmode="numeric" pattern="[0-9]*" maxlength="6" placeholder="000000" required autocomplete="one-time-code">
            </div>
            <button type="submit" class="btn" id="btn-reset-verify">Vérifier le code</button>
            <p class="resend-link">Pas reçu ? <button type="button" class="link-btn" id="resend-code">Renvoyer le code</button></p>
        </form>

        <form id="password-form" class="hidden-step">
            <div class="reset-icon">
                <i class="bx bxs-key"></i>
            </div>
            <h1>Nouveau mot de passe</h1>
            <p class="instruction-text">8+ caractères, majuscule, minuscule, chiffre et symbole.</p>
            <div class="input-group">
                <input type="password" id="reset-new1" placeholder="Nouveau mot de passe" required minlength="8" autocomplete="new-password">
                <input type="password" id="reset-new2" placeholder="Confirmation" required minlength="8" autocomplete="new-password">
            </div>
            <button type="submit" class="btn" id="btn-reset-save">Enregistrer</button>
        </form>
    </div>
    <div class="instructions-box">
        <div class="instructions-panel instructions-left">
            <h1>Sécurité</h1>
            <p id="instruction-side">Indiquez l’e-mail de votre compte pour recevoir le code.</p>
        </div>
    </div>
</div>

<div id="reset-toast" class="reset-toast" hidden></div>
<script src="<?php echo htmlspecialchars(site_href('Assets/javascript/script_resetpassword.js')); ?>"></script>
</body>
</html>
