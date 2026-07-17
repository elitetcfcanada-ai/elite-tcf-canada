<?php

declare(strict_types=1);

/** Durée de validité des codes e-mail (connexion, inscription, mot de passe, etc.), en secondes. */
const TCF_OTP_TTL_SECONDS = 60;

/**
 * Génère et enregistre un code à 6 chiffres pour un utilisateur existant.
 *
 * @return array{ok:bool,message?:string,mail_ok?:bool}
 */
function tcf_otp_send_for_user(PDO $pdo, int $userId, string $purpose, string $emailSubject, string $emailBodyIntro): array
{
    require_once __DIR__ . '/mail_helper.php';

    try {
        $stmt = $pdo->prepare('SELECT email, name FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return ['ok' => false, 'message' => 'Compte introuvable.'];
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $pdo->prepare('DELETE FROM user_email_codes WHERE user_id = ? AND purpose = ?')->execute([$userId, $purpose]);
        $ins = $pdo->prepare(
            'INSERT INTO user_email_codes (user_id, code, purpose, expires_at) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ' . (int) TCF_OTP_TTL_SECONDS . ' SECOND))'
        );
        $ins->execute([$userId, $code, $purpose]);

        $body = 'Bonjour ' . ($row['name'] ?? '') . ",\n\n"
            . $emailBodyIntro . "\n\n"
            . 'Votre code : ' . $code . "\n\n"
            . 'Ce code expire dans ' . TCF_OTP_TTL_SECONDS . " secondes. Si vous n'êtes pas à l'origine de cette demande, ignorez ce message.\n";

        $sent = tcf_send_plain_mail((string) $row['email'], $emailSubject, $body);
        if (!$sent) {
            tcf_log_mail_fallback("$purpose user_id=$userId email={$row['email']} code=$code (mail failed)");
            return [
                'ok' => true,
                'message' => "Code généré. Si l'e-mail n'arrive pas (XAMPP), consultez uploads/mail_fallback.log.",
                'mail_ok' => false,
            ];
        }

        return ['ok' => true, 'message' => 'Un code à 6 chiffres vient de vous être envoyé.', 'mail_ok' => true];
    } catch (Throwable $e) {
        return ['ok' => false, 'message' => 'Impossible d\'envoyer le code. Vérifiez la table user_email_codes.'];
    }
}

/**
 * Vérifie le code et le supprime s'il est valide.
 */
function tcf_otp_verify_and_consume(PDO $pdo, int $userId, string $purpose, string $code): bool
{
    $code = preg_replace('/\D/', '', $code);
    if (strlen($code) !== 6) {
        return false;
    }
    try {
        $stmt = $pdo->prepare(
            'SELECT id FROM user_email_codes WHERE user_id = ? AND purpose = ? AND code = ? AND expires_at > NOW() LIMIT 1'
        );
        $stmt->execute([$userId, $purpose, $code]);
        $id = $stmt->fetchColumn();
        if (!$id) {
            return false;
        }
        $pdo->prepare('DELETE FROM user_email_codes WHERE id = ?')->execute([(int) $id]);

        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function tcf_find_user_id_by_email(PDO $pdo, string $email): ?int
{
    $email = trim($email);
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $id = $stmt->fetchColumn();

    return $id ? (int) $id : null;
}
