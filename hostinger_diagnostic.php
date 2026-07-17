<?php
/*
Fichier de diagnostic pour Hostinger
Ce fichier permet de tester la configuration et la connexion à la base de données
*/

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnostic Hostinger - Elite TCF Canada</h1>";
echo "<h2>Informations PHP</h2>";
echo "<pre>";
echo "Version PHP: " . phpversion() . "\n";
echo "Extensions chargées: " . implode(', ', get_loaded_extensions()) . "\n";
echo "Upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "Post_max_size: " . ini_get('post_max_size') . "\n";
echo "Memory_limit: " . ini_get('memory_limit') . "\n";
echo "Max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "</pre>";

echo "<h2>Informations Serveur</h2>";
echo "<pre>";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Non défini') . "\n";
echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'Non défini') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'Non défini') . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'Non défini') . "\n";
echo "HTTPS: " . ($_SERVER['HTTPS'] ?? 'Non défini') . "\n";
echo "</pre>";

echo "<h2>Test de connexion à la base de données</h2>";
echo "<pre>";

$host = 'localhost';
$dbname = 'u648716817_tcf_canada';
$username = 'u648716817_tcf_canada';
$password = 'Audrey300%';

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connexion réussie à la base de données\n";
    
    // Test de requête
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✓ Tables trouvées: " . implode(', ', $tables) . "\n";
    
    // Test de la table users
    if (in_array('users', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $userCount = $stmt->fetchColumn();
        echo "✓ Nombre d'utilisateurs: $userCount\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Erreur de connexion: " . $e->getMessage() . "\n";
}

echo "</pre>";

echo "<h2>Vérification des dossiers d'upload</h2>";
echo "<pre>";

$uploadDirs = [
    'uploads',
    'uploads/avatars',
    'uploads/channel',
    'uploads/channel_posts',
    'uploads/co_media',
    'uploads/trainers'
];

foreach ($uploadDirs as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    if (is_dir($fullPath)) {
        $writable = is_writable($fullPath) ? '✓' : '✗';
        echo "$writable $dir (existe, permissions: " . substr(sprintf('%o', fileperms($fullPath)), -4) . ")\n";
    } else {
        echo "✗ $dir (n'existe pas)\n";
    }
}

echo "</pre>";

echo "<h2>Test d'écriture dans uploads</h2>";
echo "<pre>";

$testFile = __DIR__ . '/uploads/test_hostinger_' . time() . '.txt';
if (file_put_contents($testFile, 'Test Hostinger')) {
    echo "✓ Écriture réussie dans uploads/\n";
    unlink($testFile);
    echo "✓ Suppression réussie du fichier de test\n";
} else {
    echo "✗ Impossible d'écrire dans uploads/\n";
}

echo "</pre>";

echo "<h2>Configuration des chemins</h2>";
echo "<pre>";
echo "Chemin absolu du projet: " . __DIR__ . "\n";
echo "Chemin racine calculé: " . realpath(dirname(__DIR__)) . "\n";
echo "</pre>";

echo "<h2>Variables d'environnement</h2>";
echo "<pre>";
$envVars = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_PORT'];
foreach ($envVars as $var) {
    $value = getenv($var);
    echo "$var: " . ($value ? '✓ Définie' : '✗ Non définie') . "\n";
}
echo "</pre>";

echo "<h2>Recommandations pour Hostinger</h2>";
echo "<ul>";
echo "<li>Assurez-vous que les dossiers uploads/ et ses sous-dossiers ont les permissions 755 ou 777</li>";
echo "<li>Vérifiez que la base de données existe et que les identifiants sont corrects</li>";
echo "<li>Activez les extensions PHP: pdo_mysql, mbstring, json, curl</li>";
echo "<li>Augmentez upload_max_filesize et post_max_size dans php.ini si nécessaire</li>";
echo "<li>Supprimez ce fichier après diagnostic pour des raisons de sécurité</li>";
echo "</ul>";
