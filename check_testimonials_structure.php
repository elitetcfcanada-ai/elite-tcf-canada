<?php
require_once __DIR__ . '/includes/config.php';

echo "<h1>Structure de la table testimonials</h1>";

try {
    $stmt = $pdo->query("DESCRIBE testimonials");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse:collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Vérifier les témoignages sans is_published
    $stmt = $pdo->query("SELECT id, author_name, content, rating, created_at FROM testimonials ORDER BY created_at DESC LIMIT 10");
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Derniers témoignages</h2>";
    echo "<table border='1' style='border-collapse:collapse;'>";
    echo "<tr><th>ID</th><th>Auteur</th><th>Contenu</th><th>Note</th><th>Date</th></tr>";
    foreach ($testimonials as $t) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($t['id']) . "</td>";
        echo "<td>" . htmlspecialchars($t['author_name']) . "</td>";
        echo "<td>" . htmlspecialchars(substr($t['content'], 0, 50)) . "...</td>";
        echo "<td>" . htmlspecialchars($t['rating']) . "</td>";
        echo "<td>" . htmlspecialchars($t['created_at']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Throwable $e) {
    echo "<p style='color:red'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}
