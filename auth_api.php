<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/user_validation.php';
require_once __DIR__ . '/includes/auth_otp.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

$raw = (string) file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) {
    $body = $_POST;
}

$action = (string) ($body['action'] ?? '');

switch ($action) {
    case 'reset_send': {
        $email = trim((string) ($body['email'] ?? ''));
        $uid = tcf_find_user_id_by_email($pdo, $email);
        // Ne pas révéler si l'e-mail existe
        if ($uid === null) {
            echo json_encode(['ok' => true, 'message' => 'Si cette adresse est enregistrée, un code vient de vous être envoyé.']);
            break;
        }
        $r = tcf_otp_send_for_user(
            $pdo,
            $uid,
            'password_reset',
            'Code de réinitialisation ELITE TCF CANADA',
            'Réinitialisation de votre mot de passe sur ELITE TCF CANADA.'
        );
        echo json_encode([
            'ok' => $r['ok'],
            'message' => $r['ok'] ? 'Si cette adresse est enregistrée, un code vient de vous être envoyé.' : ($r['message'] ?? 'Erreur.'),
            'mail_ok' => $r['mail_ok'] ?? true,
        ]);
        break;
    }

    case 'reset_verify': {
        $email = trim((string) ($body['email'] ?? ''));
        $code = (string) ($body['code'] ?? '');
        $uid = tcf_find_user_id_by_email($pdo, $email);
        if ($uid === null || !tcf_otp_verify_and_consume($pdo, $uid, 'password_reset', $code)) {
            echo json_encode(['ok' => false, 'message' => 'Code incorrect ou expiré. Redemandez un code.']);
            break;
        }
        $_SESSION['tcf_reset_uid'] = $uid;
        $_SESSION['tcf_reset_until'] = time() + 600;
        echo json_encode(['ok' => true, 'message' => 'Code vérifié. Choisissez un nouveau mot de passe.']);
        break;
    }

    case 'reset_finish': {
        $uid = (int) ($_SESSION['tcf_reset_uid'] ?? 0);
        $until = (int) ($_SESSION['tcf_reset_until'] ?? 0);
        if ($uid <= 0 || $until < time()) {
            unset($_SESSION['tcf_reset_uid'], $_SESSION['tcf_reset_until']);
            echo json_encode(['ok' => false, 'message' => 'Session expirée. Recommencez depuis l’e-mail.']);
            break;
        }
        $new = (string) ($body['new_password'] ?? '');
        $confirm = (string) ($body['confirm_password'] ?? '');
        $err = tcf_validate_password_pair($new, $confirm);
        if ($err !== null) {
            echo json_encode(['ok' => false, 'message' => $err]);
            break;
        }
        $hash = password_hash($new, PASSWORD_DEFAULT);
        try {
            $pdo->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?')->execute([$hash, $uid]);
        } catch (Throwable $e) {
            $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hash, $uid]);
        }
        unset($_SESSION['tcf_reset_uid'], $_SESSION['tcf_reset_until']);
        echo json_encode(['ok' => true, 'message' => 'Mot de passe mis à jour. Vous pouvez vous connecter.']);
        break;
    }

    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Action inconnue.']);
}
