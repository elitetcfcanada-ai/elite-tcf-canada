<?php

declare(strict_types=1);

/**
 * Rôles staff admin / super_admin.
 */

function tcf_session_role(): string
{
    return (string) ($_SESSION['role'] ?? '');
}

function tcf_is_super_admin(): bool
{
    return tcf_session_role() === 'super_admin';
}

/** Admin ou super_admin */
function tcf_is_staff_admin(): bool
{
    return in_array(tcf_session_role(), ['admin', 'super_admin'], true);
}

/**
 * Un admin (non super) ne peut pas supprimer les contenus publiés.
 */
function tcf_staff_can_delete_content(): bool
{
    return tcf_is_super_admin();
}

/**
 * JSON 403 si pas super admin.
 *
 * @param callable(array,int):void $jsonExit
 */
function tcf_require_super_admin_json(callable $jsonExit, string $message = 'Accès réservé au super administrateur.'): void
{
    if (!tcf_is_super_admin()) {
        $jsonExit(['success' => false, 'message' => $message], 403);
    }
}
