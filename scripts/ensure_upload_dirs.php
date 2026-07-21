<?php
declare(strict_types=1);

$key = (string) ($_GET['key'] ?? '');
if ($key !== 'REPAIR_TCF_2026') {
    http_response_code(403);
    echo "Accès refusé.\n";
    exit;
}

header('Content-Type: text/html; charset=utf-8');
echo '<h1>Création dossiers uploads</h1><pre>';

$root = dirname(__DIR__);
$dirs = [
    'uploads',
    'uploads/videos',
    'uploads/thumbnails',
    'uploads/avatars',
    'uploads/channel',
    'uploads/channel_posts',
    'uploads/trainers',
    'uploads/co_media',
];

foreach ($dirs as $rel) {
    $path = $root . '/' . $rel;
    if (!is_dir($path)) {
        $ok = @mkdir($path, 0755, true);
        echo ($ok ? 'CREATED ' : 'FAIL ') . $rel . "\n";
    } else {
        echo "EXISTS {$rel}\n";
    }
    $writable = is_dir($path) && is_writable($path);
    echo '  writable=' . ($writable ? 'yes' : 'no') . "\n";
    $keep = $path . '/.gitkeep';
    if (is_dir($path) && !file_exists($keep)) {
        @file_put_contents($keep, '');
    }
}

// Quick users AI check
require_once $root . '/includes/config.php';
try {
    $c = $pdo->query("SHOW COLUMNS FROM users LIKE 'id'")->fetch(PDO::FETCH_ASSOC);
    echo "\nusers.id Extra=" . ($c['Extra'] ?? '') . "\n";
    $email = 'smoke_' . time() . '@example.invalid';
    $pdo->prepare(
        "INSERT INTO users (name,email,password,role,subscription_type,status,created_at)
         VALUES ('Smoke', ?, ?, 'user', 'free', 'active', NOW())"
    )->execute([$email, password_hash('SmokeTest1!', PASSWORD_DEFAULT)]);
    $id = (int) $pdo->lastInsertId();
    echo "test insert id={$id}\n";
    $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
    echo ($id > 0 ? "REGISTRATION_IDS_OK\n" : "REGISTRATION_IDS_FAIL\n");
} catch (Throwable $e) {
    echo 'ERR ' . $e->getMessage() . "\n";
}

echo "\nDONE — republiez vos vidéos depuis l'admin (Public).\n</pre>";
