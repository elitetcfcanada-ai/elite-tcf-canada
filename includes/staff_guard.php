<?php

declare(strict_types=1);

/**
 * Admin / super_admin : uniquement l’espace admin.
 * Pas de navigation « site public » / pages apprenants.
 */

function tcf_is_staff_role(?string $role): bool
{
    return in_array((string) $role, ['admin', 'super_admin'], true);
}

function tcf_session_is_staff(): bool
{
    if (!empty($_SESSION['role']) && tcf_is_staff_role((string) $_SESSION['role'])) {
        return true;
    }
    if (!empty($_SESSION['is_admin'])) {
        return true;
    }

    return false;
}

/**
 * Scripts / zones autorisés hors /admin/ pour un compte staff
 * (APIs utilisées par le tableau de bord, auth, médias).
 */
function tcf_staff_allowed_public_script(string $scriptName, string $scriptPath): bool
{
    $scriptPath = str_replace('\\', '/', $scriptPath);
    if (str_contains($scriptPath, '/admin/')) {
        return true;
    }
    if (str_contains($scriptPath, '/scripts/')) {
        return true;
    }
    if (str_contains($scriptPath, '/Assets/')) {
        return true;
    }

    $allow = [
        'login.php',
        'logout.php',
        'resetPassword.php',
        'forgotPassword.php',
        'media_serve.php',
        'contact_submit.php',
        'payment_webhook.php',
        'robots.php',
    ];
    if (in_array($scriptName, $allow, true)) {
        return true;
    }
    // Toutes les APIs JSON (gestion contenu depuis l’admin)
    if (str_ends_with($scriptName, '_api.php')) {
        return true;
    }

    return false;
}

/**
 * Redirige admin/super_admin hors des pages publiques vers le tableau de bord.
 */
function tcf_staff_enforce_admin_only_space(): void
{
    if (PHP_SAPI === 'cli') {
        return;
    }
    if (!tcf_session_is_staff()) {
        return;
    }

    $scriptPath = (string) ($_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '');
    $scriptName = basename(str_replace('\\', '/', $scriptPath));
    if ($scriptName === '' || $scriptName === 'staff_guard.php') {
        return;
    }
    if (tcf_staff_allowed_public_script($scriptName, $scriptPath)) {
        return;
    }

    // Évite boucle si superAdmin inaccessible
    $target = function_exists('site_href')
        ? site_href('admin/superAdmin.php')
        : '/admin/superAdmin.php';
    if (!headers_sent()) {
        header('Location: ' . $target, true, 302);
    }
    exit;
}
