<?php
declare(strict_types=1);

/**
 * Client Gemini partagé (EE / EO / assistant).
 */

function tcf_gemini_api_key(): string
{
    $apiKey = '';
    $keyFile = __DIR__ . '/gemini_key.php';
    if (is_file($keyFile)) {
        $fromFile = include $keyFile;
        if (is_string($fromFile) && trim($fromFile) !== '') {
            $apiKey = trim($fromFile);
        }
    }
    if ($apiKey === '') {
        $apiKey = trim((string) getenv('GEMINI_API_KEY'));
    }
    if ($apiKey === '' && !empty($_SERVER['GEMINI_API_KEY'])) {
        $apiKey = trim((string) $_SERVER['GEMINI_API_KEY']);
    }
    return $apiKey;
}

/** Modèles à essayer (ordre : stables d’abord ; éviter les alias « thinking » trop gourmands). */
function tcf_gemini_models(): array
{
    return [
        'gemini-2.0-flash',
        'gemini-2.0-flash-001',
        'gemini-flash-latest',
        'gemini-1.5-flash-latest',
        'gemini-1.5-flash',
    ];
}

/**
 * Appelle Gemini generateContent. Retourne le JSON décodé ou null.
 *
 * @param array<string,mixed> $body
 * @param-out string $lastError
 */
function tcf_gemini_generate(array $body, string $apiKey, ?string &$lastError = null, int $timeout = 40): ?array
{
    $lastError = '';
    if ($apiKey === '') {
        $lastError = 'Clé Gemini non configurée.';
        return null;
    }
    if (!function_exists('curl_init')) {
        $lastError = 'Extension cURL PHP manquante.';
        return null;
    }

    foreach (tcf_gemini_models() as $modelName) {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/'
            . rawurlencode($modelName)
            . ':generateContent?key='
            . rawurlencode($apiKey);

        $ch = curl_init($url);
        if ($ch === false) {
            $lastError = 'Impossible d’initialiser cURL.';
            continue;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 12,
        ]);

        $response = curl_exec($ch);
        $curlErr = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            $lastError = 'Erreur réseau Gemini: ' . $curlErr;
            continue;
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            $lastError = 'Réponse Gemini invalide.';
            continue;
        }

        if ($status >= 400) {
            $rawMsg = (string) ($decoded['error']['message'] ?? ('HTTP ' . $status));
            $lower = strtolower($rawMsg);
            if (str_contains($lower, 'leaked') || str_contains($lower, 'api key not valid') || str_contains($lower, 'permission denied')) {
                $lastError = 'Clé Gemini invalide. Mettez à jour includes/gemini_key.php ou GEMINI_API_KEY.';
            } elseif (str_contains($lower, 'quota') || str_contains($lower, 'rate limit') || $status === 429) {
                $lastError = 'Quota Gemini dépassé. Réessayez plus tard ou activez la facturation Google AI.';
            } elseif (str_contains($lower, 'not found') || $status === 404) {
                $lastError = 'Modèle indisponible: ' . $modelName;
                continue;
            } else {
                $lastError = $rawMsg;
            }
            continue;
        }

        // Certains modèles (flash-latest → 3.x) brûlent les tokens en « thinking »
        // et renvoient content vide avec finishReason MAX_TOKENS.
        $text = tcf_gemini_extract_text($decoded);
        $finish = (string) ($decoded['candidates'][0]['finishReason'] ?? '');
        if ($text === '' && ($finish === 'MAX_TOKENS' || $finish === 'OTHER' || $finish === '')) {
            $lastError = 'Réponse vide du modèle ' . $modelName . ($finish !== '' ? " ($finish)" : '');
            continue;
        }

        return $decoded;
    }

    if ($lastError === '') {
        $lastError = "Impossible de joindre l'IA pour le moment.";
    }
    return null;
}

function tcf_gemini_extract_text(?array $decoded): string
{
    if (!is_array($decoded)) {
        return '';
    }
    $text = '';
    foreach (($decoded['candidates'][0]['content']['parts'] ?? []) as $part) {
        $textPart = trim((string) ($part['text'] ?? ''));
        if ($textPart !== '') {
            $text .= ($text !== '' ? "\n\n" : '') . $textPart;
        }
    }
    return trim($text);
}
