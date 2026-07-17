<?php
/**
 * Crée ou met à jour l’administrateur admin@gmail.com (mot de passe : password)
 * Usage : php tools/ensure_admin_user.php
 */
require_once __DIR__ . '/../includes/config.php';

$email = 'admin@gmail.com';
$password = 'password';
$name = 'Administrateur';
$hash = password_hash($password, PASSWORD_DEFAULT);

$st = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$st->execute([$email]);
$existing = $st->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    $up = $pdo->prepare("UPDATE users SET name = ?, password = ?, role = 'admin', status = 'active' WHERE id = ?");
    $up->execute([$name, $hash, (int) $existing['id']]);
    echo "Admin mis à jour (id={$existing['id']}, email={$email})\n";
} else {
    $ins = $pdo->prepare("INSERT INTO users (name, email, password, role, subscription_type, status) VALUES (?, ?, ?, 'admin', 'free', 'active')");
    $ins->execute([$name, $email, $hash]);
    echo "Admin créé (id=" . $pdo->lastInsertId() . ", email={$email})\n";
}

echo "Connexion : {$email} / {$password}\n";
