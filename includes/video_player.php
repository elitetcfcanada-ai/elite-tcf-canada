<?php

declare(strict_types=1);

/**
 * Affiche un lecteur vidéo (fichier local ou iframe YouTube/Vimeo).
 *
 * @param array{id?:string,class?:string,poster?:string,controls?:bool} $opts
 */
function tcf_render_video_player(string $videoUrl, array $opts = []): void
{
    $id = isset($opts['id']) ? (string) $opts['id'] : 'tcf-video-player';
    $class = isset($opts['class']) ? (string) $opts['class'] : '';
    $poster = isset($opts['poster']) ? (string) $opts['poster'] : '';
    $controls = !isset($opts['controls']) || (bool) $opts['controls'];
    $url = trim($videoUrl);

    if ($url === '') {
        echo '<div class="tcf-video-unavailable"><i class="bx bx-error-circle"></i><p>Aucun fichier vidéo associé.</p></div>';
        return;
    }

    if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([a-zA-Z0-9_-]{6,})#', $url, $m)) {
        $embed = 'https://www.youtube.com/embed/' . $m[1] . '?rel=0';
        echo '<div class="tcf-video-embed-wrap">';
        echo '<iframe class="tcf-video-embed' . ($class !== '' ? ' ' . htmlspecialchars($class) : '') . '"';
        echo ' id="' . htmlspecialchars($id) . '"';
        echo ' src="' . htmlspecialchars($embed) . '"';
        echo ' title="Lecteur vidéo" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>';
        echo '</div>';
        return;
    }

    if (preg_match('#vimeo\.com/(?:video/)?(\d+)#', $url, $m)) {
        $embed = 'https://player.vimeo.com/video/' . $m[1];
        echo '<div class="tcf-video-embed-wrap">';
        echo '<iframe class="tcf-video-embed' . ($class !== '' ? ' ' . htmlspecialchars($class) : '') . '"';
        echo ' id="' . htmlspecialchars($id) . '"';
        echo ' src="' . htmlspecialchars($embed) . '"';
        echo ' title="Lecteur vidéo" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen loading="lazy"></iframe>';
        echo '</div>';
        return;
    }

    $pathOnly = (string) (parse_url($url, PHP_URL_PATH) ?: $url);
    $ext = strtolower(pathinfo($pathOnly, PATHINFO_EXTENSION));
    $mimeMap = [
        'mp4' => 'video/mp4',
        'm4v' => 'video/mp4',
        'webm' => 'video/webm',
        'ogg' => 'video/ogg',
        'ogv' => 'video/ogg',
        'mov' => 'video/quicktime',
    ];
    $mime = $mimeMap[$ext] ?? '';

    $cls = 'tcf-html5-video' . ($class !== '' ? ' ' . htmlspecialchars($class) : '');
    echo '<video id="' . htmlspecialchars($id) . '" class="' . $cls . '"';
    if ($controls) {
        echo ' controls';
    }
    echo ' playsinline webkit-playsinline preload="metadata" controlslist="nodownload"';
    if ($poster !== '') {
        echo ' poster="' . htmlspecialchars($poster) . '"';
    }
    echo '>';
    echo '<source src="' . htmlspecialchars($url) . '"';
    if ($mime !== '') {
        echo ' type="' . htmlspecialchars($mime) . '"';
    }
    echo '>';
    echo 'Votre navigateur ne supporte pas la lecture vidéo.';
    echo '</video>';
    echo '<div class="tcf-video-error-msg" id="' . htmlspecialchars($id) . '-error" hidden role="alert">';
    echo '<i class="bx bx-error-circle"></i>';
    echo '<p>Impossible de lire cette vidéo. Le fichier est peut‑être absent du serveur (uploads non synchronisés) ou dans un format non supporté.</p>';
    echo '</div>';
}
