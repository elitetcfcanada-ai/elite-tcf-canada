<?php

declare(strict_types=1);

/**
 * Optimisation vidéo à la publication (style plateformes) :
 * - H.264 + AAC + faststart (lecture fluide)
 * - CRF élevé (qualité visuelle préservée)
 * - plafonnage doux de la résolution (max 1280px sur le grand côté)
 *
 * Ne fait rien si ffmpeg est indisponible (Hostinger shared souvent sans binaire).
 */

function tcf_ffmpeg_bin(): ?string
{
    static $cached = false;
    static $bin = null;
    if ($cached) {
        return $bin;
    }
    $cached = true;
    if (!function_exists('shell_exec') || !is_callable('shell_exec')) {
        return null;
    }
    $candidates = ['ffmpeg', 'ffmpeg.exe'];
    foreach ($candidates as $c) {
        $out = @shell_exec($c . ' -version 2>&1');
        if (is_string($out) && stripos($out, 'ffmpeg') !== false) {
            $bin = $c;
            return $bin;
        }
    }
    return null;
}

/**
 * Optimise un fichier uploadé. Retourne le chemin relatif uploads/... à stocker en base.
 *
 * @return string|null chemin relatif (même ou nouveau), null si échec total
 */
function tcf_video_optimize_uploaded_file(string $relativeUploadsPath): ?string
{
    $rel = str_replace('\\', '/', trim($relativeUploadsPath));
    if ($rel === '' || !preg_match('#^uploads/#i', $rel)) {
        return $relativeUploadsPath !== '' ? $relativeUploadsPath : null;
    }
    $abs = tcf_uploads_fs_path($rel);
    if ($abs === '' || !is_file($abs) || !is_readable($abs)) {
        return $rel;
    }

    $ffmpeg = tcf_ffmpeg_bin();
    if ($ffmpeg === null) {
        return $rel;
    }

    $dir = dirname($abs);
    $base = pathinfo($abs, PATHINFO_FILENAME);
    $outAbs = $dir . DIRECTORY_SEPARATOR . $base . '_stream.mp4';
    // Éviter collision
    if (is_file($outAbs)) {
        $outAbs = $dir . DIRECTORY_SEPARATOR . $base . '_' . bin2hex(random_bytes(3)) . '_stream.mp4';
    }

    // Qualité haute (CRF 20) + faststart + max 1280 sur le grand côté (ratio préservé)
    $vf = "scale='min(1280,iw)':'min(1280,ih)':force_original_aspect_ratio=decrease";
    $cmd = $ffmpeg
        . ' -y -i ' . escapeshellarg($abs)
        . ' -map 0:v:0 -map 0:a:0?'
        . ' -c:v libx264 -preset medium -crf 20 -pix_fmt yuv420p'
        . ' -vf ' . escapeshellarg($vf)
        . ' -c:a aac -b:a 160k -ac 2'
        . ' -movflags +faststart'
        . ' -max_muxing_queue_size 9999'
        . ' ' . escapeshellarg($outAbs)
        . ' 2>&1';

    @set_time_limit(0);
    $prev = ini_get('max_execution_time');
    @ini_set('max_execution_time', '0');
    $log = @shell_exec($cmd);
    if ($prev !== false) {
        @ini_set('max_execution_time', (string) $prev);
    }

    if (!is_file($outAbs) || filesize($outAbs) < 1024) {
        @unlink($outAbs);
        error_log('tcf_video_optimize: échec ffmpeg — conservation du fichier original. ' . substr((string) $log, 0, 500));
        return $rel;
    }

    $inSize = (int) filesize($abs);
    $outSize = (int) filesize($outAbs);
    // Garder l'original s'il est déjà plus léger (déjà bien compressé) ET en mp4
    $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
    $preferOut = ($ext !== 'mp4') || ($outSize > 0 && $outSize <= (int) ($inSize * 0.98));
    if (!$preferOut) {
        @unlink($outAbs);
        return $rel;
    }

    @unlink($abs);
    $outRel = 'uploads/videos/' . basename($outAbs);
    return $outRel;
}
