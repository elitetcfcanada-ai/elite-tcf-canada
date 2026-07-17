<?php
require_once __DIR__ . '/includes/config.php';

echo "<h1>Vérification des tables d'épreuves</h1>";

// Vérifier la table tcf_ce_exams
echo "<h2>Table tcf_ce_exams (Compréhension Écrite)</h2>";
try {
    $stmt = $pdo->query("DESCRIBE tcf_ce_exams");
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
    
    // Compter les enregistrements
    $count = $pdo->query("SELECT COUNT(*) FROM tcf_ce_exams")->fetchColumn();
    echo "<p>Nombre d'épreuves: $count</p>";
    
    // Afficher les épreuves
    if ($count > 0) {
        $stmt = $pdo->query("SELECT id, title, slug, is_published FROM tcf_ce_exams");
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
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur: " . $e->getMessage() . "</p>";
}

// Vérifier la table tcf_co_exams
echo "<h2>Table tcf_co_exams (Compréhension Orale)</h2>";
try {
    $stmt = $pdo->query("DESCRIBE tcf_co_exams");
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
    
    // Compter les enregistrements
    $count = $pdo->query("SELECT COUNT(*) FROM tcf_co_exams")->fetchColumn();
    echo "<p>Nombre d'épreuves: $count</p>";
    
    // Afficher les épreuves
    if ($count > 0) {
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
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur: " . $e->getMessage() . "</p>";
}
