<?php
require_once __DIR__ . '/includes/config.php';

echo "<h1>Vérification des balises HTML dans les questions de compréhension écrite</h1>";

try {
    // Récupérer une question avec du texte
    $stmt = $pdo->query("SELECT id, exam_id, situation, question_text FROM tcf_ce_questions LIMIT 1");
    $question = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($question) {
        echo "<h2>Question ID: " . htmlspecialchars($question['id']) . "</h2>";
        echo "<h3>Situation (brut de la base de données):</h3>";
        echo "<pre>" . htmlspecialchars($question['situation']) . "</pre>";
        echo "<h3>Situation (interprété HTML):</h3>";
        echo "<div style='border:1px solid #ccc;padding:10px;'>" . $question['situation'] . "</div>";
        
        echo "<h3>Question text (brut de la base de données):</h3>";
        echo "<pre>" . htmlspecialchars($question['question_text']) . "</pre>";
        echo "<h3>Question text (interprété HTML):</h3>";
        echo "<div style='border:1px solid #ccc;padding:10px;'>" . $question['question_text'] . "</div>";
        
        // Vérifier les réponses
        $stmt2 = $pdo->prepare("SELECT answer_key, answer_text FROM tcf_ce_answers WHERE question_id = ? LIMIT 3");
        $stmt2->execute([$question['id']]);
        $answers = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Réponses:</h3>";
        foreach ($answers as $answer) {
            echo "<h4>Réponse " . htmlspecialchars($answer['answer_key']) . " (brut):</h4>";
            echo "<pre>" . htmlspecialchars($answer['answer_text']) . "</pre>";
            echo "<h4>Réponse " . htmlspecialchars($answer['answer_key']) . " (interprété):</h4>";
            echo "<div style='border:1px solid #ccc;padding:10px;'>" . $answer['answer_text'] . "</div>";
        }
    } else {
        echo "<p>Aucune question trouvée dans la base de données.</p>";
    }
    
    // Tester l'API payload
    echo "<h2>Test de l'API payload (ce_exam_to_quiz_payload)</h2>";
    $stmt = $pdo->query("SELECT id FROM tcf_ce_exams LIMIT 1");
    $exam = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($exam) {
        require_once __DIR__ . '/ce_api.php';
        $fullExam = ce_fetch_exam_full($pdo, (int)$exam['id']);
        if ($fullExam) {
            $payload = ce_exam_to_quiz_payload($fullExam);
            echo "<h3>Payload JSON:</h3>";
            echo "<pre>" . htmlspecialchars(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
            
            if (!empty($payload[0])) {
                echo "<h3>Première question - Situation (brut JSON):</h3>";
                echo "<pre>" . htmlspecialchars($payload[0]['situation']) . "</pre>";
                echo "<h3>Première question - Situation (interprété HTML):</h3>";
                echo "<div style='border:1px solid #ccc;padding:10px;'>" . $payload[0]['situation'] . "</div>";
                
                echo "<h3>Première question - Text (brut JSON):</h3>";
                echo "<pre>" . htmlspecialchars($payload[0]['text']) . "</pre>";
                echo "<h3>Première question - Text (interprété HTML):</h3>";
                echo "<div style='border:1px solid #ccc;padding:10px;'>" . $payload[0]['text'] . "</div>";
                
                if (!empty($payload[0]['answers'][0])) {
                    echo "<h3>Première réponse (brut JSON):</h3>";
                    echo "<pre>" . htmlspecialchars($payload[0]['answers'][0]['text']) . "</pre>";
                    echo "<h3>Première réponse (interprété HTML):</h3>";
                    echo "<div style='border:1px solid #ccc;padding:10px;'>" . $payload[0]['answers'][0]['text'] . "</div>";
                }
            }
        }
    }
    
} catch (Throwable $e) {
    echo "<p style='color:red'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p><strong>Diagnostic terminé.</strong></p>";
