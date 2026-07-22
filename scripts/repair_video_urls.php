<?php
declare(strict_types=1);
/**
 * Normalise video_url / thumbnail_url en chemins relatifs uploads/...
 * Web : scripts/repair_video_urls.php?key=REPAIR_TCF_2026
 */
$key = (string) ($_GET['key'] ?? '');
if ($key !== 'REPAIR_TCF_2026') {
    http_response_code(403);
    exit('Forbidden');
}

require_once dirname(__DIR__) . '/includes/config.php';

header('Content-Type: text/plain; charset=utf-8');
echo "=== repair_video_urls ===\n";

function tcf_normalize_upload_stored(?string $stored): string
{
    $rel = tcf_uploads_relative_path($stored);
    if ($rel === '' || preg_match('#^https?://#i', $rel)) {
        return $stored !== null ? trim($stored) : '';
    }
    return $rel;
}

try {
    $rows = $pdo->query('SELECT id, title, video_url, thumbnail_url, visibility FROM videos ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    echo 'videos=' . count($rows) . "\n\n";
    $upd = $pdo->prepare('UPDATE videos SET video_url = ?, thumbnail_url = ? WHERE id = ?');
    $fixed = 0;
    $missing = 0;
    foreach ($rows as $r) {
        $id = (int) $r['id'];
        $oldV = (string) ($r['video_url'] ?? '');
        $oldT = (string) ($r['thumbnail_url'] ?? '');
        $newV = tcf_normalize_upload_stored($oldV);
        $newT = tcf_normalize_upload_stored($oldT);
        $changed = ($newV !== $oldV) || ($newT !== $oldT);
        if ($changed) {
            $upd->execute([$newV, $newT, $id]);
            $fixed++;
            echo "#{$id} FIXED\n  video: {$oldV}\n     -> {$newV}\n";
            if ($oldT !== $newT) {
                echo "  thumb: {$oldT}\n     -> {$newT}\n";
            }
        }
        $fs = tcf_uploads_fs_path($newV);
        $exists = ($fs !== '' && is_file($fs));
        $href = tcf_uploads_public_href($newV);
        echo sprintf(
            "#%d [%s] %s | file=%s | href=%s\n",
            $id,
            (string) $r['visibility'],
            (string) $r['title'],
            $exists ? 'OK' : 'MISSING',
            $href
        );
        if (!$exists && $newV !== '' && !preg_match('#^https?://#i', $newV)) {
            $missing++;
        }
        echo "\n";
    }
    echo "updated_rows={$fixed}\nmissing_files={$missing}\n";
    echo "DONE\n";
} catch (Throwable $e) {
    echo 'ERR: ' . $e->getMessage() . "\n";
}
