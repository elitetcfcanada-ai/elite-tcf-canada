<?php
/*
Script de diagnostic complet pour Hostinger
Identifie les problèmes d'inscription, connexion et affichage des médias
*/

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnostic Complet Hostinger - Elite TCF Canada</h1>";
echo "<style>body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; } .success { color: green; } .error { color: red; } .warning { color: orange; } .section { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; } pre { background: #fff; padding: 10px; border: 1px solid #ddd; }</style>";

// 1. Test de connexion base de données
echo "<div class='section'><h2>1. Connexion Base de Données</h2><pre>";

$host = 'localhost';
$dbname = 'u648716817_tcf_canada';
$username = 'u648716817_tcf_canada';
$password = 'Audrey300%';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<span class='success'>✓ Connexion réussie à la base de données</span>\n";
    
    // Vérifier les tables critiques
    $criticalTables = ['users', 'videos', 'subscriptions', 'payments'];
    foreach ($criticalTables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<span class='success'>✓ Table '$table' existe</span>\n";
        } else {
            echo "<span class='error'>✗ Table '$table' MANQUE</span>\n";
        }
    }
    
    // Vérifier structure table users
    $stmt = $pdo->query("DESCRIBE users");
    $userColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $requiredColumns = ['id', 'email', 'password', 'name', 'subscription_type', 'role'];
    foreach ($requiredColumns as $col) {
        if (in_array($col, $userColumns)) {
            echo "<span class='success'>✓ Colonne '$col' dans users</span>\n";
        } else {
            echo "<span class='error'>✗ Colonne '$col' MANQUE dans users</span>\n";
        }
    }
    
} catch (PDOException $e) {
    echo "<span class='error'>✗ Erreur de connexion: " . $e->getMessage() . "</span>\n";
}
echo "</pre></div>";

// 2. Test Sessions PHP
echo "<div class='section'><h2>2. Configuration Sessions PHP</h2><pre>";
echo "Session status: " . session_status() . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Session save path: " . session_save_path() . "\n";
echo "Session cookie params: " . print_r(session_get_cookie_params(), true) . "\n";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "<span class='success'>✓ Session démarrée</span>\n";
} else {
    echo "<span class='warning'>⚠ Session déjà active</span>\n";
}

$_SESSION['test_hostinger'] = time();
echo "<span class='success'>✓ Test écriture session: " . $_SESSION['test_hostinger'] . "</span>\n";
echo "</pre></div>";

// 3. Test Permissions Dossiers
echo "<div class='section'><h2>3. Permissions Dossiers Uploads</h2><pre>";

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
        $writable = is_writable($fullPath);
        $readable = is_readable($fullPath);
        $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
        
        if ($writable && $readable) {
            echo "<span class='success'>✓ $dir (permissions: $perms)</span>\n";
        } else {
            echo "<span class='error'>✗ $dir (permissions: $perms, writable: " . ($writable ? 'yes' : 'no') . ", readable: " . ($readable ? 'yes' : 'no') . ")</span>\n";
        }
    } else {
        echo "<span class='error'>✗ $dir (n'existe pas)</span>\n";
    }
}

// Test écriture
$testFile = __DIR__ . '/uploads/test_hostinger_' . time() . '.txt';
if (@file_put_contents($testFile, 'Test Hostinger')) {
    echo "<span class='success'>✓ Écriture test réussie dans uploads/</span>\n";
    @unlink($testFile);
    echo "<span class='success'>✓ Suppression test réussie</span>\n";
} else {
    echo "<span class='error'>✗ Écriture impossible dans uploads/</span>\n";
}
echo "</pre></div>";

// 4. Test Chemins et URLs
echo "<div class='section'><h2>4. Configuration Chemins et URLs</h2><pre>";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Non défini') . "\n";
echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'Non défini') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'Non défini') . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'Non défini') . "\n";
echo "HTTPS: " . ($_SERVER['HTTPS'] ?? 'Non défini') . "\n";
echo "Chemin absolu projet: " . __DIR__ . "\n";
echo "Chemin racine calculé: " . realpath(dirname(__DIR__)) . "\n";

// Test fonction site_href
if (function_exists('site_href')) {
    echo "site_href('uploads/test.png'): " . site_href('uploads/test.png') . "\n";
    echo "site_url(): " . site_url() . "\n";
} else {
    echo "<span class='warning'>⚠ Fonction site_href non disponible (config.php non chargé)</span>\n";
}
echo "</pre></div>";

// 5. Test Configuration PHP
echo "<div class='section'><h2>5. Configuration PHP</h2><pre>";
echo "Version PHP: " . phpversion() . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "max_input_time: " . ini_get('max_input_time') . "\n";

$requiredExtensions = ['pdo_mysql', 'mbstring', 'json', 'curl', 'gd', 'session'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<span class='success'>✓ Extension '$ext' chargée</span>\n";
    } else {
        echo "<span class='error'>✗ Extension '$ext' MANQUE</span>\n";
    }
}
echo "</pre></div>";

// 6. Test Données Videos
echo "<div class='section'><h2>6. Test Données Vidéos</h2><pre>";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM videos");
    $videoCount = $stmt->fetchColumn();
    echo "Nombre de vidéos: $videoCount\n";
    
    if ($videoCount > 0) {
        $stmt = $pdo->query("SELECT id, title, thumbnail_url, video_url, visibility FROM videos LIMIT 3");
        $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Exemples de vidéos:\n";
        foreach ($videos as $video) {
            echo "- ID: {$video['id']}, Titre: {$video['title']}\n";
            echo "  Thumbnail: {$video['thumbnail_url']}\n";
            echo "  Video URL: {$video['video_url']}\n";
            echo "  Visibility: {$video['visibility']}\n";
            
            // Vérifier si fichiers existent
            if (!empty($video['thumbnail_url'])) {
                $thumbPath = __DIR__ . '/' . ltrim(str_replace('\\', '/', $video['thumbnail_url']), '/');
                echo "  Fichier thumbnail existe: " . (file_exists($thumbPath) ? '✓' : '✗') . "\n";
            }
            if (!empty($video['video_url'])) {
                $videoPath = __DIR__ . '/' . ltrim(str_replace('\\', '/', $video['video_url']), '/');
                echo "  Fichier vidéo existe: " . (file_exists($videoPath) ? '✓' : '✗') . "\n";
            }
        }
    } else {
        echo "<span class='warning'>⚠ Aucune vidéo dans la base de données</span>\n";
    }
} catch (PDOException $e) {
    echo "<span class='error'>✗ Erreur: " . $e->getMessage() . "</span>\n";
}
echo "</pre></div>";

// 7. Test Inscription
echo "<div class='section'><h2>7. Test Inscription (Simulation)</h2><pre>";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Générer email de test
    $testEmail = 'test_hostinger_' . time() . '@example.com';
    $testPassword = password_hash('test123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (email, password, name, subscription_type, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $result = $stmt->execute([$testEmail, $testPassword, 'Test Hostinger', 'free', 'user']);
    
    if ($result) {
        echo "<span class='success'>✓ Inscription test réussie</span>\n";
        echo "Email de test: $testEmail\n";
        
        // Nettoyer
        $stmt = $pdo->prepare("DELETE FROM users WHERE email = ?");
        $stmt->execute([$testEmail]);
        echo "<span class='success'>✓ Nettoyage test réussi</span>\n";
    } else {
        echo "<span class='error'>✗ Inscription test échouée</span>\n";
    }
} catch (PDOException $e) {
    echo "<span class='error'>✗ Erreur inscription: " . $e->getMessage() . "</span>\n";
}
echo "</pre></div>";

// 8. Recommandations
echo "<div class='section'><h2>8. Recommandations pour Hostinger</h2><ul>";
echo "<li><strong>Base de données:</strong> Importez database/tcf.sql via phpMyAdmin</li>";
echo "<li><strong>Permissions:</strong> Configurez les dossiers uploads/ à 755 ou 777</li>";
echo "<li><strong>Sessions:</strong> Vérifiez que session.save_path est accessible en écriture</li>";
echo "<li><strong>PHP:</strong> Activez les extensions requises (pdo_mysql, mbstring, json, curl, gd)</li>";
echo "<li><strong>HTTPS:</strong> Configurez SSL/HTTPS sur Hostinger</li>";
echo "<li><strong>Chemins:</strong> Vérifiez que DOCUMENT_ROOT pointe vers le bon dossier</li>";
echo "<li><strong>Fichiers médias:</strong> Uploadez les fichiers vidéos/thumbnails dans uploads/</li>";
echo "<li><strong>Supprimer ce fichier:</strong> Après diagnostic pour la sécurité</li>";
echo "</ul></div>";

echo "<p><em>Diagnostic terminé. Contactez le support technique si des erreurs persistent.</em></p>";
