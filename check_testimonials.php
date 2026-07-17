<?php
require_once __DIR__ . '/includes/config.php';

echo "<h1>Vérification des témoignages</h1>";

try {
    // Vérifier la table testimonials
    $stmt = $pdo->query("SELECT COUNT(*) FROM testimonials");
    $count = $stmt->fetchColumn();
    echo "<p>Nombre de témoignages dans la base de données : <strong>" . $count . "</strong></p>";
    
    if ($count > 0) {
        $stmt = $pdo->query("SELECT id, author_name, content, rating, is_published, created_at FROM testimonials ORDER BY created_at DESC LIMIT 10");
        $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2>Derniers témoignages</h2>";
        echo "<table border='1' style='border-collapse:collapse;'>";
        echo "<tr><th>ID</th><th>Auteur</th><th>Contenu</th><th>Note</th><th>Publié</th><th>Date</th></tr>";
        foreach ($testimonials as $t) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($t['id']) . "</td>";
            echo "<td>" . htmlspecialchars($t['author_name']) . "</td>";
            echo "<td>" . htmlspecialchars(substr($t['content'], 0, 50)) . "...</td>";
            echo "<td>" . htmlspecialchars($t['rating']) . "</td>";
            echo "<td>" . ($t['is_published'] ? 'Oui' : 'Non') . "</td>";
            echo "<td>" . htmlspecialchars($t['created_at']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Aucun témoignage trouvé dans la base de données.</p>";
    }
    
    // Vérifier l'API testimonials_api.php
    echo "<h2>Test de l'API testimonials</h2>";
    $stmt = $pdo->query("SELECT * FROM testimonials LIMIT 1");
    $testimonial = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($testimonial) {
        echo "<h3>Exemple de témoignage</h3>";
        echo "<pre>" . htmlspecialchars(json_encode($testimonial, JSON_PRETTY_PRINT)) . "</pre>";
    }
    
} catch (Throwable $e) {
    echo "<p style='color:red'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}
