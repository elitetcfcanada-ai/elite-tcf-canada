<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/media_blob.php';

// Streaming long : pas de tampon PHP qui charge tout en mémoire
@ini_set('zlib.output_compression', '0');
@ini_set('output_buffering', '0');
while (ob_get_level() > 0) {
    @ob_end_clean();
}
@set_time_limit(0);

$type = trim((string) ($_GET['type'] ?? ''));
$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0 || !in_array($type, ['video', 'video_thumb', 'avatar'], true)) {
    http_response_code(400);
    exit('Bad request');
}

if ($type === 'avatar') {
    if (!tcf_media_stream_avatar_blob($pdo, $id)) {
        http_response_code(404);
        exit('Not found');
    }
    exit;
}

if (!tcf_media_stream_video_blob($pdo, $type, $id)) {
    http_response_code(404);
    exit('Not found');
}
