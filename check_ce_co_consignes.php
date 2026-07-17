<?php
require_once __DIR__ . '/includes/config.php';

echo "<h1>Vérification des tables de consignes de compréhension</h1>";

try {
    // Vérifier tcf_ce_consignes
    echo "<h2>Table tcf_ce_consignes (Compréhension Écrite)</h2>";
    $stmt = $pdo->query("DESCRIBE tcf_ce_consignes");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' style='border-collapse:collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM tcf_ce_consignes");
    $count = $stmt->fetchColumn();
    echo "<p>Nombre de consignes CE : <strong>" . $count . "</strong></p>";
    
    if ($count > 0) {
        $stmt = $pdo->query("SELECT * FROM tcf_ce_consignes LIMIT 3");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Exemples de consignes CE existantes :</h3>";
        foreach ($rows as $row) {
            echo "<pre>" . htmlspecialchars(json_encode($row, JSON_PRETTY_PRINT)) . "</pre>";
        }
    }
    
    // Vérifier tcf_co_consignes
    echo "<h2>Table tcf_co_consignes (Compréhension Orale)</h2>";
    $stmt = $pdo->query("DESCRIBE tcf_co_consignes");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' style='border-collapse:collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM tcf_co_consignes");
    $count = $stmt->fetchColumn();
    echo "<p>Nombre de consignes CO : <strong>" . $count . "</strong></p>";
    
    if ($count > 0) {
        $stmt = $pdo->query("SELECT * FROM tcf_co_consignes LIMIT 3");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Exemples de consignes CO existantes :</h3>";
        foreach ($rows as $row) {
            echo "<pre>" . htmlspecialchars(json_encode($row, JSON_PRETTY_PRINT)) . "</pre>";
        }
    }
    
} catch (Throwable $e) {
    echo "<p style='color:red'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}
