<?php
require_once __DIR__ . '/includes/config.php';

echo "<h1>État des épreuves et leurs questions</h1>";

// Vérifier tcf_ce_exams avec leurs questions
echo "<h2>Compréhension Écrite</h2>";
try {
    $stmt = $pdo->query("SELECT id, title, slug, is_published FROM tcf_ce_exams ORDER BY id");
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($exams as $exam) {
        $examId = $exam['id'];
        echo "<h3>ID: $examId - " . htmlspecialchars($exam['title']) . "</h3>";
        
        // Compter les questions
        $stmtQ = $pdo->prepare("SELECT COUNT(*) FROM tcf_ce_questions WHERE exam_id = ?");
        $stmtQ->execute([$examId]);
        $qCount = $stmtQ->fetchColumn();
        echo "<p>Questions: $qCount</p>";
        
        if ($qCount > 0) {
            $stmtQ2 = $pdo->prepare("SELECT id, question_text FROM tcf_ce_questions WHERE exam_id = ? ORDER BY sort_order");
            $stmtQ2->execute([$examId]);
            $questions = $stmtQ2->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($questions as $q) {
                echo "<p>Question ID {$q['id']}: " . htmlspecialchars(substr($q['question_text'], 0, 50)) . "...</p>";
                
                // Compter les réponses
                $stmtA = $pdo->prepare("SELECT COUNT(*) FROM tcf_ce_answers WHERE question_id = ?");
                $stmtA->execute([$q['id']]);
                $aCount = $stmtA->fetchColumn();
                echo "<p style='margin-left:20px'>Réponses: $aCount</p>";
            }
        }
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur: " . $e->getMessage() . "</p>";
}

// Vérifier tcf_co_exams avec leurs questions
echo "<h2>Compréhension Orale</h2>";
try {
    $stmt = $pdo->query("SELECT id, title, slug, is_published FROM tcf_co_exams ORDER BY id");
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($exams as $exam) {
        $examId = $exam['id'];
        echo "<h3>ID: $examId - " . htmlspecialchars($exam['title']) . "</h3>";
        
        // Compter les questions
        $stmtQ = $pdo->prepare("SELECT COUNT(*) FROM tcf_co_questions WHERE exam_id = ?");
        $stmtQ->execute([$examId]);
        $qCount = $stmtQ->fetchColumn();
        echo "<p>Questions: $qCount</p>";
        
        if ($qCount > 0) {
            $stmtQ2 = $pdo->prepare("SELECT id, question_text FROM tcf_co_questions WHERE exam_id = ? ORDER BY sort_order");
            $stmtQ2->execute([$examId]);
            $questions = $stmtQ2->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($questions as $q) {
                echo "<p>Question ID {$q['id']}: " . htmlspecialchars(substr($q['question_text'], 0, 50)) . "...</p>";
                
                // Compter les réponses
                $stmtA = $pdo->prepare("SELECT COUNT(*) FROM tcf_co_answers WHERE question_id = ?");
                $stmtA->execute([$q['id']]);
                $aCount = $stmtA->fetchColumn();
                echo "<p style='margin-left:20px'>Réponses: $aCount</p>";
            }
        }
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur: " . $e->getMessage() . "</p>";
}
