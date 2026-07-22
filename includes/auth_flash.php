<?php
declare(strict_types=1);

/**
 * Flash messages connexion / inscription (login page + toast site).
 *
 * @param 'success'|'error'|'warning'|'info' $type
 * @param 'login'|'register'|null $form
 */
function tcf_auth_flash(string $type, string $message, ?string $form = null): void
{
    $_SESSION['auth_flash'] = [
        'type' => $type,
        'message' => $message,
        'form' => $form,
    ];
}

/** @return array{type:string,message:string,form:?string}|null */
function tcf_auth_flash_consume(): ?array
{
    if (isset($_SESSION['auth_flash']) && is_array($_SESSION['auth_flash']) && !empty($_SESSION['auth_flash']['message'])) {
        $flash = $_SESSION['auth_flash'];
        unset($_SESSION['auth_flash'], $_SESSION['error'], $_SESSION['success']);
        return [
            'type' => (string) ($flash['type'] ?? 'info'),
            'message' => (string) $flash['message'],
            'form' => isset($flash['form']) ? (string) $flash['form'] : null,
        ];
    }

    if (!empty($_SESSION['error'])) {
        $msg = (string) $_SESSION['error'];
        unset($_SESSION['error'], $_SESSION['auth_flash']);
        return ['type' => 'error', 'message' => $msg, 'form' => null];
    }
    if (!empty($_SESSION['success'])) {
        $msg = (string) $_SESSION['success'];
        unset($_SESSION['success'], $_SESSION['auth_flash']);
        return ['type' => 'success', 'message' => $msg, 'form' => null];
    }

    unset($_SESSION['auth_flash']);
    return null;
}
