<?php
/**
 * Recrée les lignes videos à partir des fichiers encore présents dans uploads/videos.
 * Usage: ?key=REPAIR_TCF_2026
 */
declare(strict_types=1);

$key = (string) ($_GET['key'] ?? (PHP_SAPI === 'cli' ? ($argv[1] ?? '') : ''));
if ($key !== 'REPAIR_TCF_2026') {
    http_response_code(403);
    echo "Accès refusé.\n";
    exit;
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/video_duration.php';

header('Content-Type: text/html; charset=utf-8');
echo '<h1>Récupération vidéos depuis uploads/</h1><pre>';

$root = dirname(__DIR__);
$videosDir = $root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'videos';
$thumbsDir = $root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'thumbnails';

if (!is_dir($videosDir)) {
    echo "Dossier uploads/videos introuvable.\n";
    exit;
}

$existing = $pdo->query('SELECT video_url FROM videos')->fetchAll(PDO::FETCH_COLUMN);
$existingMap = [];
foreach ($existing as $u) {
    $existingMap[str_replace('\\', '/', (string) $u)] = true;
}

$files = glob($videosDir . DIRECTORY_SEPARATOR . '*.{mp4,webm,mov,avi,mkv,MP4,WEBM,MOV,AVI,MKV}', GLOB_BRACE) ?: [];
echo 'Fichiers trouvés: ' . count($files) . "\n";

$thumbs = is_dir($thumbsDir)
    ? (glob($thumbsDir . DIRECTORY_SEPARATOR . '*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE) ?: [])
    : [];
sort($thumbs);

$inserted = 0;
foreach ($files as $i => $abs) {
    $base = basename($abs);
    $rel = 'uploads/videos/' . $base;
    if (isset($existingMap[$rel])) {
        echo "SKIP déjà en BDD: {$rel}\n";
        continue;
    }

    $thumbRel = '';
    if (isset($thumbs[$i])) {
        $thumbRel = 'uploads/thumbnails/' . basename($thumbs[$i]);
    } elseif (count($thumbs) > 0) {
        $thumbRel = 'uploads/thumbnails/' . basename($thumbs[0]);
    }

    $title = pathinfo($base, PATHINFO_FILENAME);
    $title = preg_replace('/[_-]+/', ' ', $title) ?: $base;
    $title = trim((string) $title);
    if ($title === '') {
        $title = 'Vidéo ' . ($i + 1);
    }

    $duration = null;
    try {
        $duration = tcf_probe_video_duration_for_db($abs);
    } catch (Throwable $e) {
    }

    $stmt = $pdo->prepare(
        'INSERT INTO videos (title, description, thumbnail_url, video_url, visibility, duration, created_at)
         VALUES (?, ?, ?, ?, \'public\', ?, NOW())'
    );
    $stmt->execute([
        $title,
        'Vidéo récupérée automatiquement depuis uploads/',
        $thumbRel !== '' ? $thumbRel : null,
        $rel,
        $duration,
    ]);
    $id = (int) $pdo->lastInsertId();
    echo "INSERT #{$id} {$title} -> {$rel} (thumb={$thumbRel})\n";
    if ($id > 0) {
        $inserted++;
    }
}

$vis = (int) $pdo->query("SELECT COUNT(*) FROM videos WHERE visibility IN ('public','premium')")->fetchColumn();
echo "\nInsérées: {$inserted}\nVisibles: {$vis}\n";
echo "DONE — republiez depuis l'admin si une miniature manque.\n</pre>";
