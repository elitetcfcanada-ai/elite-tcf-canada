<?php
require_once __DIR__ . '/includes/config.php';

echo "<h1>Vérification des tables de questions</h1>";

// Vérifier tcf_ce_questions
echo "<h2>Table tcf_ce_questions</h2>";
try {
    $stmt = $pdo->query("DESCRIBE tcf_ce_questions");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        foreach ($col as $val) {
            echo "<td>" . htmlspecialchars($val ?? '') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    $count = $pdo->query("SELECT COUNT(*) FROM tcf_ce_questions")->fetchColumn();
    echo "<p>Nombre de questions: $count</p>";
    
    // Vérifier les exam_id = 0
    $stmt = $pdo->query("SELECT COUNT(*) FROM tcf_ce_questions WHERE exam_id = 0");
    $countZero = $stmt->fetchColumn();
    if ($countZero > 0) {
        echo "<p style='color:red'>$countZero questions avec exam_id = 0</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur: " . $e->getMessage() . "</p>";
}

// Vérifier tcf_ce_answers
echo "<h2>Table tcf_ce_answers</h2>";
try {
    $stmt = $pdo->query("DESCRIBE tcf_ce_answers");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        foreach ($col as $val) {
            echo "<td>" . htmlspecialchars($val ?? '') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    $count = $pdo->query("SELECT COUNT(*) FROM tcf_ce_answers")->fetchColumn();
    echo "<p>Nombre de réponses: $count</p>";
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur: " . $e->getMessage() . "</p>";
}

// Vérifier tcf_co_questions
echo "<h2>Table tcf_co_questions</h2>";
try {
    $stmt = $pdo->query("DESCRIBE tcf_co_questions");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        foreach ($col as $val) {
            echo "<td>" . htmlspecialchars($val ?? '') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    $count = $pdo->query("SELECT COUNT(*) FROM tcf_co_questions")->fetchColumn();
    echo "<p>Nombre de questions: $count</p>";
    
    // Vérifier les exam_id = 0
    $stmt = $pdo->query("SELECT COUNT(*) FROM tcf_co_questions WHERE exam_id = 0");
    $countZero = $stmt->fetchColumn();
    if ($countZero > 0) {
        echo "<p style='color:red'>$countZero questions avec exam_id = 0</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur: " . $e->getMessage() . "</p>";
}

// Vérifier tcf_co_answers
echo "<h2>Table tcf_co_answers</h2>";
try {
    $stmt = $pdo->query("DESCRIBE tcf_co_answers");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        foreach ($col as $val) {
            echo "<td>" . htmlspecialchars($val ?? '') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    $count = $pdo->query("SELECT COUNT(*) FROM tcf_co_answers")->fetchColumn();
    echo "<p>Nombre de réponses: $count</p>";
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur: " . $e->getMessage() . "</p>";
}
