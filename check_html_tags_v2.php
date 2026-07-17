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
    
    // Simuler le payload comme le ferait l'API
    echo "<h2>Simulation du payload API (comme ce_exam_to_quiz_payload)</h2>";
    $stmt = $pdo->query("SELECT id FROM tcf_ce_exams LIMIT 1");
    $exam = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($exam) {
        $qSt = $pdo->prepare('SELECT * FROM tcf_ce_questions WHERE exam_id=? ORDER BY sort_order ASC, id ASC');
        $qSt->execute([(int)$exam['id']]);
        $questions = $qSt->fetchAll(PDO::FETCH_ASSOC);
        $aSt = $pdo->prepare('SELECT * FROM tcf_ce_answers WHERE question_id=? ORDER BY sort_order ASC, answer_key ASC');
        foreach ($questions as &$q) {
            $aSt->execute([(int) $q['id']]);
            $q['answers'] = $aSt->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($q);
        
        $out = [];
        $n = 1;
        foreach ($questions as $q) {
            $answers = [];
            foreach ($q['answers'] as $a) {
                $answers[] = [
                    'id' => (string) ($a['answer_key'] ?? 'a'),
                    'text' => (string) ($a['answer_text'] ?? ''),
                    'correct' => !empty($a['is_correct']),
                ];
            }
            $out[] = [
                'id' => $n++,
                'situation' => isset($q['situation']) && (string) $q['situation'] !== '' ? (string) $q['situation'] : '',
                'text' => (string) ($q['question_text'] ?? ''),
                'points' => (int) ($q['points'] ?? 3),
                'answers' => $answers,
            ];
        }
        
        echo "<h3>Payload JSON:</h3>";
        echo "<pre>" . htmlspecialchars(json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
        
        if (!empty($out[0])) {
            echo "<h3>Première question - Situation (brut JSON):</h3>";
            echo "<pre>" . htmlspecialchars($out[0]['situation']) . "</pre>";
            echo "<h3>Première question - Situation (interprété HTML):</h3>";
            echo "<div style='border:1px solid #ccc;padding:10px;'>" . $out[0]['situation'] . "</div>";
            
            echo "<h3>Première question - Text (brut JSON):</h3>";
            echo "<pre>" . htmlspecialchars($out[0]['text']) . "</pre>";
            echo "<h3>Première question - Text (interprété HTML):</h3>";
            echo "<div style='border:1px solid #ccc;padding:10px;'>" . $out[0]['text'] . "</div>";
            
            if (!empty($out[0]['answers'][0])) {
                echo "<h3>Première réponse (brut JSON):</h3>";
                echo "<pre>" . htmlspecialchars($out[0]['answers'][0]['text']) . "</pre>";
                echo "<h3>Première réponse (interprété HTML):</h3>";
                echo "<div style='border:1px solid #ccc;padding:10px;'>" . $out[0]['answers'][0]['text'] . "</div>";
            }
        }
    }
    
} catch (Throwable $e) {
    echo "<p style='color:red'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<p><strong>Diagnostic terminé.</strong></p>";
