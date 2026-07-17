<?php
require_once __DIR__ . '/includes/config.php';

echo "<h1>Correction de la table tcf_co_exams</h1>";

try {
    // D'abord supprimer les enregistrements avec id = 0
    $stmt = $pdo->query("SELECT COUNT(*) FROM tcf_co_exams WHERE id = 0");
    $countZero = $stmt->fetchColumn();
    
    if ($countZero > 0) {
        echo "<p>Suppression des $countZero enregistrements avec id = 0...</p>";
        $pdo->exec("DELETE FROM tcf_co_exams WHERE id = 0");
        echo "<p>Enregistrements avec id = 0 supprimés.</p>";
    } else {
        echo "<p>Aucun enregistrement avec id = 0.</p>";
    }
    
    // Vérifier si la colonne id a déjà une clé primaire
    $stmt = $pdo->query("SHOW KEYS FROM tcf_co_exams WHERE Key_name = 'PRIMARY'");
    $hasPrimary = $stmt->fetch();
    
    if ($hasPrimary) {
        echo "<p>La table a déjà une clé primaire. Vérification de l'auto_increment...</p>";
    } else {
        echo "<p>Ajout de la clé primaire sur la colonne id...</p>";
        $pdo->exec("ALTER TABLE tcf_co_exams ADD PRIMARY KEY (id)");
    }
    
    // Vérifier l'auto_increment
    $stmt = $pdo->query("SHOW COLUMNS FROM tcf_co_exams LIKE 'id'");
    $colInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (strpos($colInfo['Extra'], 'auto_increment') !== false) {
        echo "<p>L'auto_increment est déjà activé sur id.</p>";
    } else {
        echo "<p>Ajout de l'auto_increment sur la colonne id...</p>";
        $pdo->exec("ALTER TABLE tcf_co_exams MODIFY COLUMN id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT");
    }
    
    // Réinitialiser l'auto_increment
    $pdo->exec("ALTER TABLE tcf_co_exams AUTO_INCREMENT = 1");
    echo "<p>Auto_increment réinitialisé à 1.</p>";
    
    // Vérifier les tables associées
    echo "<h2>Vérification des tables associées</h2>";
    
    // tcf_co_questions
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM tcf_co_questions WHERE exam_id = 0");
        $countZeroQuestions = $stmt->fetchColumn();
        if ($countZeroQuestions > 0) {
            echo "<p>Suppression de $countZeroQuestions questions avec exam_id = 0...</p>";
            $pdo->exec("DELETE FROM tcf_co_questions WHERE exam_id = 0");
        }
    } catch (PDOException $e) {
        echo "<p>Table tcf_co_questions: " . $e->getMessage() . "</p>";
    }
    
    // tcf_co_answers
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM tcf_co_answers WHERE question_id IN (SELECT id FROM tcf_co_questions WHERE exam_id = 0)");
        $countZeroAnswers = $stmt->fetchColumn();
        if ($countZeroAnswers > 0) {
            echo "<p>Suppression de $countZeroAnswers réponses associées...</p>";
            $pdo->exec("DELETE FROM tcf_co_answers WHERE question_id IN (SELECT id FROM tcf_co_questions WHERE exam_id = 0)");
        }
    } catch (PDOException $e) {
        echo "<p>Table tcf_co_answers: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>État final de tcf_co_exams</h2>";
    $stmt = $pdo->query("SELECT id, title, slug, is_published FROM tcf_co_exams");
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Title</th><th>Slug</th><th>Published</th></tr>";
    foreach ($exams as $exam) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($exam['id'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($exam['title'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($exam['slug'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($exam['is_published'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p style='color:green'><strong>Correction terminée avec succès !</strong></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur: " . $e->getMessage() . "</p>";
}
