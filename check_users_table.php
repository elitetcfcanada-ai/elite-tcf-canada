<?php
require_once __DIR__ . '/includes/config.php';

echo "<h1>Vérification de la table users pour l'inscription</h1>";

try {
    // Vérifier la structure de la table users
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Colonnes de la table users</h2>";
    echo "<table border='1' style='border-collapse:collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Vérifier s'il y a des utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $count = $stmt->fetchColumn();
    echo "<p>Nombre d'utilisateurs dans la table : <strong>" . $count . "</strong></p>";
    
    // Vérifier le dernier utilisateur créé
    if ($count > 0) {
        $stmt = $pdo->query("SELECT id, name, email, role, subscription_type, status, created_at FROM users ORDER BY id DESC LIMIT 1");
        $lastUser = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<h2>Dernier utilisateur créé</h2>";
        echo "<pre>";
        print_r($lastUser);
        echo "</pre>";
    }
    
    // Tester l'INSERT avec la même structure que login.php
    echo "<h2>Test d'INSERT (simulation)</h2>";
    $testEmail = 'test_' . time() . '@example.com';
    $testName = 'Test User';
    $testPassword = password_hash('Test1234!', PASSWORD_DEFAULT);
    
    try {
        // Premier essai avec toutes les colonnes
        $stmt = $pdo->prepare(
            'INSERT INTO users (name, email, password, role, subscription_type, subscription_expires_at, status, avatar) VALUES (?, ?, ?, ?, ?, NULL, ?, NULL)'
        );
        $stmt->execute([$testName, $testEmail, $testPassword, 'user', 'free', 'active']);
        $testId = $pdo->lastInsertId();
        echo "<p style='color:green'>✓ INSERT réussi avec toutes les colonnes (ID: $testId)</p>";
        
        // Nettoyer le test
        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$testId]);
        echo "<p style='color:blue'>✓ Test nettoyé</p>";
        
    } catch (Throwable $e) {
        echo "<p style='color:red'>✗ INSERT échoué : " . htmlspecialchars($e->getMessage()) . "</p>";
        
        // Essai fallback 1
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO users (name, email, password, role, subscription_type, status, created_at, avatar) VALUES (?, ?, ?, 'user', 'free', 'active', NOW(), NULL)"
            );
            $stmt->execute([$testName, $testEmail, $testPassword]);
            $testId = $pdo->lastInsertId();
            echo "<p style='color:green'>✓ INSERT réussi avec fallback 1 (ID: $testId)</p>";
            $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$testId]);
        } catch (Throwable $e2) {
            echo "<p style='color:red'>✗ Fallback 1 échoué : " . htmlspecialchars($e2->getMessage()) . "</p>";
            
            // Essai fallback 2
            try {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, created_at, avatar) VALUES (?, ?, ?, 'user', NOW(), NULL)");
                $stmt->execute([$testName, $testEmail, $testPassword]);
                $testId = $pdo->lastInsertId();
                echo "<p style='color:green'>✓ INSERT réussi avec fallback 2 (ID: $testId)</p>";
                $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$testId]);
            } catch (Throwable $e3) {
                echo "<p style='color:red'>✗ Fallback 2 échoué : " . htmlspecialchars($e3->getMessage()) . "</p>";
            }
        }
    }
    
} catch (Throwable $e) {
    echo "<p style='color:red'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p><strong>Diagnostic terminé.</strong></p>";
