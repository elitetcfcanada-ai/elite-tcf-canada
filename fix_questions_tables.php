<?php
require_once __DIR__ . '/includes/config.php';

echo "<h1>Correction des tables de questions</h1>";

// Corriger tcf_ce_questions
echo "<h2>Table tcf_ce_questions</h2>";
try {
    // Vérifier auto_increment
    $stmt = $pdo->query("SHOW COLUMNS FROM tcf_ce_questions LIKE 'id'");
    $colInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (strpos($colInfo['Extra'], 'auto_increment') !== false) {
        echo "<p>Auto_increment déjà activé sur id.</p>";
    } else {
        echo "<p>Ajout de la clé primaire et auto_increment...</p>";
        $pdo->exec("ALTER TABLE tcf_ce_questions ADD PRIMARY KEY (id)");
        $pdo->exec("ALTER TABLE tcf_ce_questions MODIFY COLUMN id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT");
    }
    
    // Nettoyer les réponses orphelines
    $stmt = $pdo->query("SELECT COUNT(*) FROM tcf_ce_answers WHERE question_id NOT IN (SELECT id FROM tcf_ce_questions)");
    $orphanAnswers = $stmt->fetchColumn();
    if ($orphanAnswers > 0) {
        echo "<p>Suppression de $orphanAnswers réponses orphelines...</p>";
        $pdo->exec("DELETE FROM tcf_ce_answers WHERE question_id NOT IN (SELECT id FROM tcf_ce_questions)");
    }
    
    echo "<p>Correction terminée.</p>";
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur: " . $e->getMessage() . "</p>";
}

// Corriger tcf_ce_answers
echo "<h2>Table tcf_ce_answers</h2>";
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM tcf_ce_answers LIKE 'id'");
    $colInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (strpos($colInfo['Extra'], 'auto_increment') !== false) {
        echo "<p>Auto_increment déjà activé sur id.</p>";
    } else {
        echo "<p>Ajout de la clé primaire et auto_increment...</p>";
        $pdo->exec("ALTER TABLE tcf_ce_answers ADD PRIMARY KEY (id)");
        $pdo->exec("ALTER TABLE tcf_ce_answers MODIFY COLUMN id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT");
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur: " . $e->getMessage() . "</p>";
}

// Corriger tcf_co_questions
echo "<h2>Table tcf_co_questions</h2>";
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM tcf_co_questions LIKE 'id'");
    $colInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (strpos($colInfo['Extra'], 'auto_increment') !== false) {
        echo "<p>Auto_increment déjà activé sur id.</p>";
    } else {
        echo "<p>Ajout de la clé primaire et auto_increment...</p>";
        $pdo->exec("ALTER TABLE tcf_co_questions ADD PRIMARY KEY (id)");
        $pdo->exec("ALTER TABLE tcf_co_questions MODIFY COLUMN id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT");
    }
    
    // Nettoyer les réponses orphelines
    $stmt = $pdo->query("SELECT COUNT(*) FROM tcf_co_answers WHERE question_id NOT IN (SELECT id FROM tcf_co_questions)");
    $orphanAnswers = $stmt->fetchColumn();
    if ($orphanAnswers > 0) {
        echo "<p>Suppression de $orphanAnswers réponses orphelines...</p>";
        $pdo->exec("DELETE FROM tcf_co_answers WHERE question_id NOT IN (SELECT id FROM tcf_co_questions)");
    }
    
    echo "<p>Correction terminée.</p>";
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur: " . $e->getMessage() . "</p>";
}

// Corriger tcf_co_answers
echo "<h2>Table tcf_co_answers</h2>";
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM tcf_co_answers LIKE 'id'");
    $colInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (strpos($colInfo['Extra'], 'auto_increment') !== false) {
        echo "<p>Auto_increment déjà activé sur id.</p>";
    } else {
        echo "<p>Ajout de la clé primaire et auto_increment...</p>";
        $pdo->exec("ALTER TABLE tcf_co_answers ADD PRIMARY KEY (id)");
        $pdo->exec("ALTER TABLE tcf_co_answers MODIFY COLUMN id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT");
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur: " . $e->getMessage() . "</p>";
}

echo "<h2>État final</h2>";
echo "<p>tcf_ce_questions: " . $pdo->query("SELECT COUNT(*) FROM tcf_ce_questions")->fetchColumn() . " questions</p>";
echo "<p>tcf_ce_answers: " . $pdo->query("SELECT COUNT(*) FROM tcf_ce_answers")->fetchColumn() . " réponses</p>";
echo "<p>tcf_co_questions: " . $pdo->query("SELECT COUNT(*) FROM tcf_co_questions")->fetchColumn() . " questions</p>";
echo "<p>tcf_co_answers: " . $pdo->query("SELECT COUNT(*) FROM tcf_co_answers")->fetchColumn() . " réponses</p>";

echo "<p style='color:green'><strong>Correction terminée !</strong></p>";
