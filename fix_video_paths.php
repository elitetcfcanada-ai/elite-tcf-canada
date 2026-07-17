<?php
require_once __DIR__ . '/includes/config.php';

echo "<h1>Correction des chemins des vidéos</h1>";

try {
    $pdo->beginTransaction();
    
    // Corriger les chemins qui commencent par "uploads/"
    $stmt = $pdo->query("SELECT id, video_url, thumbnail_url FROM videos WHERE video_url LIKE 'uploads/%' OR thumbnail_url LIKE 'uploads/%'");
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Vidéos avec des chemins à corriger</h2>";
    
    foreach ($videos as $video) {
        $videoUrl = $video['video_url'];
        $thumbUrl = $video['thumbnail_url'];
        
        $newVideoUrl = $videoUrl;
        $newThumbUrl = $thumbUrl;
        
        // Si le chemin commence par "uploads/uploads/", corriger
        if (strpos($videoUrl, 'uploads/uploads/') === 0) {
            $newVideoUrl = str_replace('uploads/uploads/', 'uploads/', $videoUrl);
            echo "<p>Vidéo ID {$video['id']}: $videoUrl → $newVideoUrl</p>";
        }
        
        if (strpos($thumbUrl, 'uploads/uploads/') === 0) {
            $newThumbUrl = str_replace('uploads/uploads/', 'uploads/', $thumbUrl);
            echo "<p>Miniature ID {$video['id']}: $thumbUrl → $newThumbUrl</p>";
        }
        
        // Mettre à jour si nécessaire
        if ($newVideoUrl !== $videoUrl || $newThumbUrl !== $thumbUrl) {
            $updateStmt = $pdo->prepare("UPDATE videos SET video_url = ?, thumbnail_url = ? WHERE id = ?");
            $updateStmt->execute([$newVideoUrl, $newThumbUrl, $video['id']]);
            echo "<p style='color:green'>✓ Mis à jour</p>";
        }
    }
    
    $pdo->commit();
    echo "<p style='color:blue; font-weight:bold;'>✓ Correction terminée</p>";
    
} catch (Throwable $e) {
    $pdo->rollBack();
    echo "<p style='color:red'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}
