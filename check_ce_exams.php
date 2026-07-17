<?php
require_once __DIR__ . '/includes/config.php';

echo "<h1>Vérification des examens de compréhension écrite</h1>";

try {
    // Vérifier les examens
    $stmt = $pdo->query("SELECT id, slug, title, subtitle, visibility, published_at FROM tcf_ce_exams ORDER BY published_at DESC LIMIT 10");
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Examens dans la base de données</h2>";
    echo "<p>Nombre d'examens : <strong>" . count($exams) . "</strong></p>";
    
    if (count($exams) > 0) {
        echo "<table border='1' style='border-collapse:collapse;'>";
        echo "<tr><th>ID</th><th>Slug</th><th>Titre</th><th>Sous-titre</th><th>Visibilité</th><th>Publié</th></tr>";
        foreach ($exams as $exam) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($exam['id']) . "</td>";
            echo "<td>" . htmlspecialchars($exam['slug']) . "</td>";
            echo "<td>" . htmlspecialchars($exam['title']) . "</td>";
            echo "<td>" . htmlspecialchars($exam['subtitle']) . "</td>";
            echo "<td>" . htmlspecialchars($exam['visibility']) . "</td>";
            echo "<td>" . htmlspecialchars($exam['published_at']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Vérifier les questions pour chaque examen
        echo "<h2>Questions par examen</h2>";
        foreach ($exams as $exam) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tcf_ce_questions WHERE exam_id = ?");
            $stmt->execute([$exam['id']]);
            $questionCount = $stmt->fetchColumn();
            
            echo "<p>Examen ID {$exam['id']} ({$exam['title']}): <strong>{$questionCount}</strong> questions</p>";
            
            if ($questionCount > 0) {
                $stmt = $pdo->prepare("SELECT id, question_text FROM tcf_ce_questions WHERE exam_id = ? LIMIT 2");
                $stmt->execute([$exam['id']]);
                $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<ul>";
                foreach ($questions as $q) {
                    echo "<li>ID {$q['id']}: " . htmlspecialchars(substr($q['question_text'], 0, 50)) . "...</li>";
                }
                echo "</ul>";
            }
        }
    } else {
        echo "<p>Aucun examen trouvé dans la base de données.</p>";
    }
    
    // Vérifier la réponse de l'API
    echo "<h2>Test de l'API get_exams_public</h2>";
    $_POST['action'] = 'get_exams_public';
    include __DIR__ . '/ce_api.php';
    
} catch (Throwable $e) {
    echo "<p style='color:red'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}
