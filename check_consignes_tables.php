<?php
require_once __DIR__ . '/includes/config.php';

echo "<h1>Vérification des tables de consignes</h1>";

try {
    // Vérifier tcf_ee_consignes
    echo "<h2>Table tcf_ee_consignes (Expression Écrite)</h2>";
    $stmt = $pdo->query("DESCRIBE tcf_ee_consignes");
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
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM tcf_ee_consignes");
    $count = $stmt->fetchColumn();
    echo "<p>Nombre de consignes EE : <strong>" . $count . "</strong></p>";
    
    // Vérifier tcf_eo_consignes
    echo "<h2>Table tcf_eo_consignes (Expression Orale)</h2>";
    $stmt = $pdo->query("DESCRIBE tcf_eo_consignes");
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
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM tcf_eo_consignes");
    $count = $stmt->fetchColumn();
    echo "<p>Nombre de consignes EO : <strong>" . $count . "</strong></p>";
    
    // Vérifier si les tables de compréhension existent
    echo "<h2>Tables de compréhension</h2>";
    $tables = $pdo->query("SHOW TABLES LIKE '%consign%'")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Tables contenant 'consign' : " . implode(', ', $tables) . "</p>";
    
} catch (Throwable $e) {
    echo "<p style='color:red'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}
