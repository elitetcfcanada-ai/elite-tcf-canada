<?php

declare(strict_types=1);

/**
 * Logique métier mot de passe (profil) — utilisée par profile_api.php (JSON).
 * Le changement est direct depuis le profil (sans OTP intermédiaire).
 */

function tcf_profile_password_send_code(PDO $pdo, int $userId): array
{
    require_once __DIR__ . '/auth_otp.php';

    return tcf_otp_send_for_user(
        $pdo,
        $userId,
        'password_change',
        'Code de sécurité ELITE TCF CANADA',
        'Modification de votre mot de passe sur ELITE TCF CANADA.'
    );
}

function tcf_profile_password_verify_code(PDO $pdo, int $userId, string $code): array
{
    require_once __DIR__ . '/auth_otp.php';

    $code = preg_replace('/\D/', '', $code);
    if (strlen($code) !== 6) {
        return ['ok' => false, 'message' => 'Saisissez les 6 chiffres du code reçu.'];
    }
    try {
        if (!tcf_otp_verify_and_consume($pdo, $userId, 'password_change', $code)) {
            return ['ok' => false, 'message' => 'Code incorrect ou expiré (1 min). Demandez un nouveau code.'];
        }
        $_SESSION['tcf_pwd_change_ok'] = $userId;
        $_SESSION['tcf_pwd_change_until'] = time() + 600;

        return ['ok' => true, 'message' => 'Code vérifié. Vous pouvez choisir votre nouveau mot de passe.'];
    } catch (Throwable $e) {
        return ['ok' => false, 'message' => 'Vérification impossible.'];
    }
}

function tcf_profile_password_finalize(PDO $pdo, int $userId, string $current, string $new, string $confirm): array
{
    $current = (string) $current;
    if (trim($current) === '') {
        return ['ok' => false, 'message' => 'Entrez d’abord votre ancien mot de passe.'];
    }

    try {
        $st = $pdo->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
        $st->execute([$userId]);
        $storedHash = (string) ($st->fetchColumn() ?: '');
    } catch (Throwable $e) {
        return ['ok' => false, 'message' => 'Vérification impossible. Réessayez.'];
    }

    if ($storedHash === '') {
        return ['ok' => false, 'message' => 'Ancien mot de passe introuvable pour ce compte.'];
    }
    if (!password_verify($current, $storedHash)) {
        return ['ok' => false, 'message' => 'Ancien mot de passe incorrect.'];
    }

    $err = tcf_validate_password_pair($new, $confirm);
    if ($err !== null) {
        return ['ok' => false, 'message' => $err];
    }
    if ($current === $new) {
        return ['ok' => false, 'message' => 'Le nouveau mot de passe doit être différent de l’ancien.'];
    }

    $hash = password_hash($new, PASSWORD_DEFAULT);
    try {
        $pdo->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?')->execute([$hash, $userId]);
    } catch (Throwable $e) {
        $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hash, $userId]);
    }
    unset($_SESSION['tcf_pwd_change_ok'], $_SESSION['tcf_pwd_change_until']);

    return ['ok' => true, 'message' => 'Votre mot de passe a été mis à jour avec succès.'];
}
