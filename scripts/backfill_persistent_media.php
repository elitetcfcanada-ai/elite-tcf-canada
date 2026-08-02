<?php
declare(strict_types=1);

/**
 * Backfill BLOB pour annonces + médias CO déjà présents sur disque.
 * CLI ou : /scripts/backfill_persistent_media.php?key=REPAIR_TCF_2026
 */

$cli = PHP_SAPI === 'cli';
if (!$cli && (string) ($_GET['key'] ?? '') !== 'REPAIR_TCF_2026') {
    http_response_code(403);
    echo "Forbidden\n";
    exit;
}

header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/community_posts_helper.php';
require_once __DIR__ . '/../includes/persistent_media.php';
require_once __DIR__ . '/../includes/tcf_schema.php';

echo "=== Backfill persistent media ===\n";
tcf_persistent_media_ensure_table($pdo);
tcf_annonce_ensure_image_blob_columns($pdo);

$ann = 0;
$table = tcf_community_posts_table($pdo);
$sql = $table === 'annonces'
    ? "SELECT id, image_url FROM annonces WHERE kind='post' AND image_url IS NOT NULL AND image_url <> ''"
    : "SELECT id, image_url FROM community_posts WHERE image_url IS NOT NULL AND image_url <> ''";
foreach ($pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $id = (int) $row['id'];
    $url = (string) $row['image_url'];
    tcf_annonce_store_image_blob($pdo, $id, $url);
    if (tcf_annonce_has_image_blob($pdo, $id) || tcf_persistent_media_id_for_path($pdo, $url) > 0) {
        $ann++;
        echo "annonce #{$id} ok\n";
    } else {
        echo "annonce #{$id} missing_file={$url}\n";
    }
}

$co = 0;
if (tcf_schema_has_table($pdo, 'comprehension_orale')) {
    $rows = $pdo->query("SELECT id, content_json FROM comprehension_orale WHERE kind='exam'")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $json = json_decode((string) ($row['content_json'] ?? ''), true);
        $questions = is_array($json['questions'] ?? null) ? $json['questions'] : [];
        foreach ($questions as $q) {
            foreach (['image_src' => 'co_image', 'audio_src' => 'co_audio'] as $field => $kind) {
                $path = trim((string) ($q[$field] ?? ''));
                if ($path === '') {
                    continue;
                }
                $id = tcf_persistent_media_store_from_path($pdo, $path, $kind);
                if ($id > 0) {
                    $co++;
                    echo "co exam#{$row['id']} {$field} -> pm#{$id}\n";
                } else {
                    echo "co exam#{$row['id']} missing {$path}\n";
                }
            }
        }
    }
} elseif (tcf_schema_has_table($pdo, 'tcf_co_questions')) {
    $rows = $pdo->query('SELECT id, image_src, audio_src FROM tcf_co_questions')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        foreach ([['image_src', 'co_image'], ['audio_src', 'co_audio']] as [$field, $kind]) {
            $path = trim((string) ($row[$field] ?? ''));
            if ($path === '') {
                continue;
            }
            $id = tcf_persistent_media_store_from_path($pdo, $path, $kind);
            if ($id > 0) {
                $co++;
                echo "co q#{$row['id']} {$field} -> pm#{$id}\n";
            }
        }
    }
}

echo "OK annonces_touched={$ann} co_media_stored={$co}\n";
