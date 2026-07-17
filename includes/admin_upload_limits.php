<?php
declare(strict_types=1);

/**
 * Limites d'upload admin (vidéos volumineuses).
 * Note : upload_max_filesize / post_max_size doivent aussi être suffisants dans php.ini.
 */
function tcf_admin_apply_upload_limits(): void
{
    @ini_set('upload_max_filesize', '8192M');
    @ini_set('post_max_size', '8192M');
    @ini_set('max_execution_time', '7200');
    @ini_set('max_input_time', '7200');
    @ini_set('memory_limit', '1024M');
}

function tcf_upload_error_message(int $code): string
{
    return match ($code) {
        UPLOAD_ERR_INI_SIZE => 'Fichier trop volumineux pour PHP (upload_max_filesize / post_max_size dans php.ini XAMPP). Valeurs recommandées : 8192M ou plus.',
        UPLOAD_ERR_FORM_SIZE => 'Fichier trop volumineux (limite du formulaire HTML).',
        UPLOAD_ERR_PARTIAL => 'Transfert interrompu. Réessayez avec une connexion stable.',
        UPLOAD_ERR_NO_FILE => 'Aucun fichier reçu par le serveur.',
        UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire PHP manquant (upload_tmp_dir).',
        UPLOAD_ERR_CANT_WRITE => 'Impossible d\'écrire le fichier sur le disque (droits uploads/).',
        UPLOAD_ERR_EXTENSION => 'Upload bloqué par une extension PHP du serveur.',
        default => 'Erreur lors du transfert du fichier (code ' . $code . ').',
    };
}
