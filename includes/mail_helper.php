<?php

/**
 * Envoi d'un e-mail texte UTF-8. Sur XAMPP, mail() est souvent non configuré :
 * le code peut être journalisé dans uploads/mail_fallback.log (voir appelant).
 */
function tcf_send_plain_mail(string $to, string $subject, string $body): bool
{
    $subject = str_replace(["\r", "\n"], '', $subject);
    $headers = "MIME-Version: 1.0\r\nContent-type: text/plain; charset=UTF-8\r\n";

    return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
}

function tcf_log_mail_fallback(string $message): void
{
    $dir = dirname(__DIR__) . '/uploads';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $path = $dir . '/mail_fallback.log';
    $line = date('c') . ' ' . $message . "\n";
    @file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
}
