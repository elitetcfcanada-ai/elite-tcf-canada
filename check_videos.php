<?php
require_once __DIR__ . '/includes/config.php';

echo "<h1>Vérification des vidéos</h1>";

try {
    $stmt = $pdo->query("SELECT id, title, video_url, thumbnail_url, visibility FROM videos LIMIT 5");
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Vidéos dans la base de données</h2>";
    echo "<table border='1' style='border-collapse:collapse;'>";
    echo "<tr><th>ID</th><th>Titre</th><th>URL vidéo</th><th>URL miniature</th><th>Visibilité</th></tr>";
    foreach ($videos as $video) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($video['id']) . "</td>";
        echo "<td>" . htmlspecialchars($video['title']) . "</td>";
        echo "<td>" . htmlspecialchars($video['video_url']) . "</td>";
        echo "<td>" . htmlspecialchars($video['thumbnail_url']) . "</td>";
        echo "<td>" . htmlspecialchars($video['visibility']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Vérifier si les fichiers existent
    echo "<h2>Vérification des fichiers</h2>";
    foreach ($videos as $video) {
        $videoPath = __DIR__ . '/uploads/' . $video['video_url'];
        $thumbPath = __DIR__ . '/uploads/' . $video['thumbnail_url'];
        
        echo "<h3>Vidéo ID: " . htmlspecialchars($video['id']) . "</h3>";
        echo "<p>URL vidéo: " . htmlspecialchars($video['video_url']) . "</p>";
        echo "<p>Chemin complet: " . htmlspecialchars($videoPath) . "</p>";
        
        if (file_exists($videoPath)) {
            echo "<p style='color:green'>✓ Fichier vidéo existe</p>";
            echo "<p>Taille: " . filesize($videoPath) . " octets</p>";
        } else {
            echo "<p style='color:red'>✗ Fichier vidéo n'existe pas</p>";
        }
        
        if ($thumbPath && file_exists($thumbPath)) {
            echo "<p style='color:green'>✓ Fichier miniature existe</p>";
        } else {
            echo "<p style='color:orange'>⚠ Fichier miniature n'existe pas</p>";
        }
        
        // Vérifier si c'est une URL YouTube/Vimeo
        if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)#', $video['video_url'])) {
            echo "<p style='color:blue'>📺 URL YouTube détectée</p>";
        } elseif (preg_match('#vimeo\.com/#', $video['video_url'])) {
            echo "<p style='color:blue'>📺 URL Vimeo détectée</p>";
        }
        
        echo "<hr>";
    }
    
} catch (Throwable $e) {
    echo "<p style='color:red'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}
