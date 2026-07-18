<?php

require_once __DIR__ . '/../includes/config.php';

try {
    echo "<h1>Réparation Base de Données (Suppression doublons & AUTO_INCREMENT)</h1>";
    
    $tablesStmt = $pdo->query("SHOW TABLES");
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);
    
    $fixed = 0;
    
    foreach ($tables as $table) {
        $colsStmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'id'");
        $col = $colsStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($col) {
            // Nettoyage des doublons potentiels liés à l'id = 0 (problème classique d'absence d'auto_increment)
            try {
                // On garde la ligne la plus récente (si on a un created_at), ou une au hasard.
                // Mais s'il y a un problème de doublons strict, on nettoie les ID = 0
                $pdo->exec("DELETE FROM `$table` WHERE `id` = 0");
            } catch (Exception $e) {}
            
            $type = $col['Type']; 
            $extra = $col['Extra'];
            $key = $col['Key'];
            
            if (stripos($extra, 'auto_increment') === false) {
                echo "Correction de la table <strong>$table</strong>...<br>";
                
                // Si la colonne n'est pas clé primaire, l'ajouter
                if ($key !== 'PRI') {
                    try {
                        $pdo->exec("ALTER TABLE `$table` ADD PRIMARY KEY (`id`)");
                    } catch (Exception $e) {}
                }
                
                $null = ($col['Null'] === 'NO') ? 'NOT NULL' : '';
                $sql = "ALTER TABLE `$table` MODIFY `id` $type $null AUTO_INCREMENT";
                
                try {
                    $pdo->exec($sql);
                    echo "<span style='color:green;'>AUTO_INCREMENT ajouté avec succès.</span><br>";
                    $fixed++;
                } catch (PDOException $e) {
                    echo "<span style='color:red;'>Erreur (table $table) : " . htmlspecialchars($e->getMessage()) . "</span><br>";
                }
            }
        }
    }
    
    echo "<h3>Terminé. $fixed table(s) corrigée(s).</h3>";
    echo "<p>Veuillez supprimer ce script après l'avoir exécuté en ligne.</p>";
    
} catch (Exception $e) {
    die("Erreur fatale : " . $e->getMessage());
}
