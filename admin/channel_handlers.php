<?php

declare(strict_types=1);

/**
 * Anciennes liaisons playlist/chaîne — désactivées (tables supprimées).
 * Conservé pour ne pas casser les appels résiduels côté vidéos.
 */

function tcf_parse_playlist_ids_from_post(): array
{
    return [];
}

function tcf_sync_video_playlists(PDO $pdo, int $videoId, array $playlistIds): void
{
    // no-op
}

/**
 * @param list<array<string,mixed>> $videos
 * @return list<array<string,mixed>>
 */
function tcf_enrich_videos_with_playlists(PDO $pdo, array $videos): array
{
    foreach ($videos as &$v) {
        $v['playlist_ids'] = [];
    }
    unset($v);

    return $videos;
}

function getPlaylists(): void
{
    echo json_encode(['success' => true, 'data' => []]);
    exit();
}

function savePlaylist(): void
{
    echo json_encode(['success' => false, 'message' => 'Les playlists chaîne ont été retirées.']);
    exit();
}

function deletePlaylist(): void
{
    echo json_encode(['success' => false, 'message' => 'Les playlists chaîne ont été retirées.']);
    exit();
}

function getChannelPosts(): void
{
    echo json_encode(['success' => true, 'data' => []]);
    exit();
}

function saveChannelPost(): void
{
    echo json_encode(['success' => false, 'message' => 'Utilisez Gestion des annonces (annonces communautaires).']);
    exit();
}

function deleteChannelPost(): void
{
    echo json_encode(['success' => false, 'message' => 'Les publications chaîne ont été retirées.']);
    exit();
}

function getChannelBranding(): void
{
    echo json_encode(['success' => false, 'message' => 'Paramètres chaîne retirés.']);
    exit();
}

function saveChannelBranding(): void
{
    echo json_encode(['success' => false, 'message' => 'Paramètres chaîne retirés.']);
    exit();
}
