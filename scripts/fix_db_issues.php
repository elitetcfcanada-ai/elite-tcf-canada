<?php

require_once __DIR__ . '/../includes/config.php';

try {
    echo "<h1>Réparation AUTO_INCREMENT</h1>";
    
    // Récupérer toutes les tables
    $tablesStmt = $pdo->query("SHOW TABLES");
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);
    
    $fixed = 0;
    
    foreach ($tables as $table) {
        // Vérifier si la table a une colonne 'id'
        $colsStmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'id'");
        $col = $colsStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($col) {
            $type = $col['Type']; // e.g. int(11) or int(10) unsigned
            $extra = $col['Extra'];
            
            if (stripos($extra, 'auto_increment') === false) {
                // La colonne id n'a pas auto_increment
                echo "Correction de la table <strong>$table</strong> (ajout AUTO_INCREMENT)...<br>";
                
                // Extraire si NOT NULL
                $null = ($col['Null'] === 'NO') ? 'NOT NULL' : '';
                
                $sql = "ALTER TABLE `$table` MODIFY `id` $type $null AUTO_INCREMENT";
                
                try {
                    $pdo->exec($sql);
                    echo "<span style='color:green;'>Succès.</span><br>";
                    $fixed++;
                } catch (PDOException $e) {
                    echo "<span style='color:red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</span><br>";
                }
            } else {
                // Déjà auto_increment
                // echo "Table $table a déjà AUTO_INCREMENT.<br>";
            }
        }
    }
    
    echo "<h3>Terminé. $fixed table(s) corrigée(s).</h3>";
    echo "<p>Veuillez supprimer ce script après exécution en ligne.</p>";
    
} catch (Exception $e) {
    die("Erreur fatale : " . $e->getMessage());
}
