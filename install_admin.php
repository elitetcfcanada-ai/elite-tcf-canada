<?php
/*
Script d'installation d'administrateur sécurisé
Usage: Accédez à ce fichier via le navigateur après avoir importé la base de données
Ce script crée un administrateur via un formulaire sécurisé
SUPPRIMEZ CE FICHIER APRÈS UTILISATION
*/

require_once __DIR__ . '/includes/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Tous les champs sont obligatoires.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Les mots de passe ne correspondent pas.';
    } elseif (strlen($password) < 8) {
        $error = 'Le mot de passe doit contenir au moins 8 caractères.';
    } else {
        try {
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $error = 'Cet email est déjà utilisé.';
            } else {
                // Créer l'administrateur
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare(
                    "INSERT INTO users (name, email, password, role, subscription_type, status, created_at) VALUES (?, ?, ?, 'admin', 'free', 'active', NOW())"
                );
                $stmt->execute([$name, $email, $password_hash]);
                
                $success = 'Administrateur créé avec succès! Vous pouvez maintenant vous connecter avec ' . htmlspecialchars($email);
            }
        } catch (PDOException $e) {
            $error = 'Erreur lors de la création: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Administrateur - Elite TCF Canada</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        button:hover { background: #0056b3; }
        .error { color: red; padding: 10px; background: #fee; border: 1px solid #fcc; border-radius: 4px; margin-bottom: 15px; }
        .success { color: green; padding: 10px; background: #efe; border: 1px solid #cfc; border-radius: 4px; margin-bottom: 15px; }
        .warning { color: orange; padding: 10px; background: #ffe; border: 1px solid #ffc; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h1>Installation Administrateur</h1>
    
    <div class="warning">
        ⚠️ <strong>IMPORTANT:</strong> Supprimez ce fichier après avoir créé l'administrateur pour des raisons de sécurité.
    </div>
    
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
        <p><a href="login.php">Aller à la page de connexion</a></p>
    <?php else: ?>
        <form method="post">
            <div class="form-group">
                <label for="name">Nom complet:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe (min. 8 caractères):</label>
                <input type="password" id="password" name="password" required minlength="8">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe:</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
            </div>
            <button type="submit">Créer l'administrateur</button>
        </form>
    <?php endif; ?>
</body>
</html>
