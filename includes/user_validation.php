<?php

/**
 * Règles alignées sur l'inscription (login.php).
 * Retourne un message d'erreur ou null si tout est valide.
 */
function tcf_validate_registration_name_email_password(
    string $name,
    string $email,
    string $password,
    string $confirmPassword,
    PDO $pdo,
    ?int $excludeUserId = null
): ?string {
    $name = trim($name);
    $email = trim($email);

    if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
        return 'Tous les champs sont obligatoires.';
    }
    if (strlen($name) < 4) {
        return 'Le nom doit contenir au moins 4 caractères.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "L'adresse email n'est pas valide.";
    }
    if ($password !== $confirmPassword) {
        return 'Les mots de passe ne correspondent pas.';
    }
    if (strlen($password) < 8) {
        return 'Le mot de passe doit contenir au moins 8 caractères.';
    }
    $strength = tcf_validate_password_strength($password);
    if ($strength !== null) {
        return $strength;
    }

    $sql = 'SELECT id FROM users WHERE email = ?';
    $params = [$email];
    if ($excludeUserId !== null) {
        $sql .= ' AND id != ?';
        $params[] = $excludeUserId;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    if ($stmt->rowCount() > 0) {
        return 'Cet email est déjà utilisé.';
    }

    return null;
}

/** Règles de complexité (alignées inscription / admin). */
function tcf_validate_password_strength(string $password): ?string
{
    if (!preg_match('/[A-Z]/u', $password)) {
        return 'Le mot de passe doit contenir au moins une majuscule.';
    }
    if (!preg_match('/[a-z]/u', $password)) {
        return 'Le mot de passe doit contenir au moins une minuscule.';
    }
    if (!preg_match('/\d/u', $password)) {
        return 'Le mot de passe doit contenir au moins un chiffre.';
    }
    if (!preg_match('/[^\p{L}\p{N}]/u', $password)) {
        return 'Le mot de passe doit contenir au moins un symbole (ex. !@#$%).';
    }

    return null;
}

/** Mot de passe + confirmation (changement de mot de passe). */
function tcf_validate_password_pair(string $password, string $confirmPassword): ?string
{
    if ($password === '' || $confirmPassword === '') {
        return 'Tous les champs sont obligatoires.';
    }
    if ($password !== $confirmPassword) {
        return 'Les mots de passe ne correspondent pas.';
    }
    if (strlen($password) < 8) {
        return 'Le mot de passe doit contenir au moins 8 caractères.';
    }

    return tcf_validate_password_strength($password);
}
