<?php
require_once __DIR__ . '/includes/config.php';

echo "<h1>Correction de la table testimonials</h1>";

try {
    $pdo->beginTransaction();
    
    // Sauvegarder les données existantes
    $stmt = $pdo->query("SELECT author_name, content, user_id, rating, created_at FROM testimonials");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Sauvegarde de " . count($data) . " témoignages...</p>";
    
    // Supprimer la table existante
    $pdo->exec("DROP TABLE IF EXISTS testimonials");
    echo "<p>Table supprimée...</p>";
    
    // Recréer la table avec la structure correcte
    $pdo->exec("CREATE TABLE testimonials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        author_name VARCHAR(120) NOT NULL,
        content TEXT NOT NULL,
        user_id INT NULL,
        rating TINYINT NULL,
        is_published TINYINT(1) NOT NULL DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NULL
    )");
    echo "<p>Nouvelle table créée...</p>";
    
    // Réinsérer les données avec des ID corrects
    $insert = $pdo->prepare("INSERT INTO testimonials (author_name, content, user_id, rating, is_published, created_at) VALUES (?, ?, ?, ?, 1, ?)");
    
    foreach ($data as $row) {
        $insert->execute([
            $row['author_name'],
            $row['content'],
            $row['user_id'],
            $row['rating'],
            $row['created_at']
        ]);
    }
    
    echo "<p>Réinsertion des données terminée...</p>";
    
    $pdo->commit();
    echo "<p style='color:green; font-weight:bold;'>✓ Table testimonials corrigée avec succès !</p>";
    
    // Vérifier
    $stmt = $pdo->query("SELECT COUNT(*) FROM testimonials");
    $count = $stmt->fetchColumn();
    echo "<p>Nombre de témoignages après correction : <strong>" . $count . "</strong></p>";
    
} catch (Throwable $e) {
    $pdo->rollBack();
    echo "<p style='color:red'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}
