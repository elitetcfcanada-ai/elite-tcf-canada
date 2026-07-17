<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/site_contact.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . site_href('index.php#contact'));
    exit;
}

$honeypot = trim((string) ($_POST['website'] ?? ''));
if ($honeypot !== '') {
    header('Location: ' . site_href('index.php#contact'));
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$subject = trim((string) ($_POST['subject'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

if ($name === '' || mb_strlen($name) < 2 || mb_strlen($name) > 200) {
    $_SESSION['contact_flash'] = ['type' => 'err', 'text' => 'Indiquez un nom valide.'];
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['contact_flash'] = ['type' => 'err', 'text' => 'Adresse e-mail invalide.'];
} elseif (mb_strlen($message) < 10) {
    $_SESSION['contact_flash'] = ['type' => 'err', 'text' => 'Votre message doit contenir au moins 10 caractères.'];
} elseif (mb_strlen($message) > 8000) {
    $_SESSION['contact_flash'] = ['type' => 'err', 'text' => 'Message trop long.'];
} else {
    $c = tcf_site_contact();
    $to = $c['email'];
    $subj = '[ELITE TCF CANADA — Contact site] ' . ($subject !== '' ? $subject : 'Message');
    if (mb_strlen($subj) > 200) {
        $subj = mb_substr($subj, 0, 197) . '…';
    }
    $body = "Nom : {$name}\nE-mail : {$email}\n\n" . $message;
    $headers = 'MIME-Version: 1.0' . "\r\n"
        . 'Content-Type: text/plain; charset=UTF-8' . "\r\n"
        . 'From: ' . $to . "\r\n"
        . 'Reply-To: ' . $email . "\r\n";
    @mail($to, '=?UTF-8?B?' . base64_encode($subj) . '?=', $body, $headers);
    $_SESSION['contact_flash'] = ['type' => 'ok', 'text' => 'Merci ! Votre message a été envoyé. Nous vous répondrons dès que possible.'];
}

header('Location: ' . site_href('index.php#contact'));
exit;
