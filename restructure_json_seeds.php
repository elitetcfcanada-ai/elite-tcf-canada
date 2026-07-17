<?php
/**
 * Script pour restructurer les fichiers JSON de seeds
 * Convertit les retours à la ligne en <br> pour l'affichage HTML
 */

function convertNewlinesToBr($text) {
    if (!is_string($text)) {
        return $text;
    }
    // Convertir \n en <br>
    return str_replace("\n", "<br>", $text);
}

function processJsonFile($inputFile, $outputFile) {
    $json = file_get_contents($inputFile);
    $data = json_decode($json, true);
    
    if (!is_array($data)) {
        echo "<p style='color:red'>Erreur: Impossible de décoder $inputFile</p>";
        return false;
    }
    
    function processItem($item) {
        foreach ($item as $key => $value) {
            if (is_string($value)) {
                // Convertir les retours à la ligne pour les champs textuels
                if (in_array($key, ['situation', 'text', 'question_text', 'question', 'body', 'title', 'subtitle'])) {
                    $item[$key] = convertNewlinesToBr($value);
                }
            } elseif (is_array($value)) {
                $item[$key] = processItem($value);
            }
        }
        return $item;
    }
    
    $processed = processItem($data);
    
    $jsonOutput = json_encode($processed, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($outputFile, $jsonOutput);
    
    echo "<p style='color:green'>✓ $inputFile → $outputFile</p>";
    return true;
}

echo "<h1>Restructuration des fichiers JSON seeds</h1>";

$seedsDir = __DIR__ . '/database/seeds';
$outputDir = __DIR__ . '/database/seeds_restructured';

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Traiter les fichiers JSON dans database/seeds
$files = glob($seedsDir . '/*.json');
foreach ($files as $file) {
    $basename = basename($file);
    $outputFile = $outputDir . '/' . $basename;
    processJsonFile($file, $outputFile);
}

echo "<p><strong>Terminé !</strong> Les fichiers restructurés sont dans <code>database/seeds_restructured</code></p>";
