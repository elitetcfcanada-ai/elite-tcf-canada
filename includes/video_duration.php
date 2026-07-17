<?php

/**
 * Durée vidéo : lecture via ffprobe / ffmpeg si disponibles, sinon null (pas de valeur factice).
 */

/**
 * Indique si une valeur stockée en base représente une durée à afficher (exclut zéro / NULL).
 */
function tcf_video_duration_is_meaningful(?string $stored): bool
{
    if ($stored === null) {
        return false;
    }
    $d = trim($stored);
    if ($d === '') {
        return false;
    }
    if (!preg_match('/^(?:(\d{1,3}):)?(\d{1,2}):(\d{1,2})(\.\d+)?$/', $d, $m)) {
        return true;
    }
    $h = isset($m[1]) && $m[1] !== '' ? (int) $m[1] : 0;
    $mi = (int) $m[2];
    $s = (int) $m[3];
    $frac = isset($m[4]) ? (float) $m[4] : 0.0;
    $total = $h * 3600 + $mi * 60 + $s + $frac;

    return $total > 0.001;
}

function tcf_format_seconds_to_time3(float $secs): string
{
    $totalMs = (int) round($secs * 1000.0);
    $hours = intdiv($totalMs, 3600000);
    $rem = $totalMs % 3600000;
    $minutes = intdiv($rem, 60000);
    $rem2 = $rem % 60000;
    $seconds = intdiv($rem2, 1000);
    $ms = $rem2 % 1000;

    return sprintf('%02d:%02d:%02d.%03d', $hours, $minutes, $seconds, $ms);
}

/**
 * @return float|null secondes > 0 si lecture OK
 */
function tcf_probe_video_file_duration_seconds(string $filePathFs): ?float
{
    if ($filePathFs === '' || !is_readable($filePathFs)) {
        return null;
    }
    if (!function_exists('shell_exec')) {
        return null;
    }

    $q = escapeshellarg($filePathFs);

    $raw = @shell_exec('ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 ' . $q);
    if ($raw !== null && $raw !== false && $raw !== '') {
        $secs = (float) trim((string) $raw);
        if (is_finite($secs) && $secs > 0) {
            return $secs;
        }
    }

    $raw2 = @shell_exec('ffmpeg -i ' . $q . ' 2>&1');
    if ($raw2 !== null && $raw2 !== false && preg_match('/Duration:\s*(\d{2}):(\d{2}):(\d{2})(\.\d+)?/m', (string) $raw2, $m)) {
        $h = (int) $m[1];
        $mi = (int) $m[2];
        $s = (int) $m[3];
        $frac = isset($m[4]) ? (float) $m[4] : 0.0;
        $total = $h * 3600 + $mi * 60 + $s + $frac;
        if (is_finite($total) && $total > 0) {
            return $total;
        }
    }

    return null;
}

/**
 * Chaîne HH:MM:SS.mmm pour la colonne duration, ou null si indisponible.
 */
function tcf_probe_video_duration_for_db(string $filePathFs): ?string
{
    $s = tcf_probe_video_file_duration_seconds($filePathFs);
    if ($s === null) {
        return null;
    }

    return tcf_format_seconds_to_time3($s);
}
