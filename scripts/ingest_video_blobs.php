<?php
declare(strict_types=1);

/**
 * Recharge video_data / thumbnail_data depuis les fichiers uploads.
 * CLI: php scripts/ingest_video_blobs.php
 * Web: /scripts/ingest_video_blobs.php?key=REPAIR_TCF_2026
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/media_blob.php';

if (PHP_SAPI !== 'cli' && (string) ($_GET['key'] ?? '') !== 'REPAIR_TCF_2026') {
    http_response_code(403);
    exit('Forbidden');
}
header('Content-Type: text/plain; charset=utf-8');
@set_time_limit(0);
@ini_set('memory_limit', '1024M');

try {
    $pdo->exec('SET SESSION max_allowed_packet = 67108864');
} catch (Throwable $e) {
}
$max = tcf_video_blob_max_bytes($pdo);
echo "max_blob_bytes=$max\n";

$rows = $pdo->query('SELECT id, thumbnail_url, video_url FROM videos')->fetchAll(PDO::FETCH_ASSOC);
echo 'videos=' . count($rows) . "\n";
foreach ($rows as $r) {
    $id = (int) $r['id'];
    try {
        tcf_video_store_blobs_from_paths($pdo, $id, $r['thumbnail_url'] ?? null, $r['video_url'] ?? null);
    } catch (Throwable $e) {
        echo "#$id ERR store: " . $e->getMessage() . "\n";
        // reconnect possible
        require __DIR__ . '/../includes/config.php';
    }
    try {
        $st = $pdo->prepare('SELECT OCTET_LENGTH(thumbnail_data) AS t, OCTET_LENGTH(video_data) AS v FROM videos WHERE id=?');
        $st->execute([$id]);
        $len = $st->fetch(PDO::FETCH_ASSOC) ?: [];
        echo "#$id thumb_bytes=" . (int) ($len['t'] ?? 0) . ' video_bytes=' . (int) ($len['v'] ?? 0) . "\n";
    } catch (Throwable $e) {
        echo "#$id ERR read: " . $e->getMessage() . "\n";
    }
}
echo "OK\n";
