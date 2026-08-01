<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/gemini_client.php';

$key = tcf_gemini_api_key();
if ($key === '') {
    echo "NO_KEY\n";
    exit(1);
}

$body = json_encode([
    'contents' => [[
        'role' => 'user',
        'parts' => [['text' => 'Réponds uniquement par le mot OK.']],
    ]],
    'generationConfig' => [
        'temperature' => 0,
        'maxOutputTokens' => 64,
    ],
], JSON_UNESCAPED_UNICODE);

$models = array_merge(tcf_gemini_models(), [
    'gemini-2.5-flash',
    'gemini-2.5-flash-lite',
    'gemini-2.0-flash-lite',
    'gemini-flash-lite-latest',
]);
$models = array_values(array_unique($models));

foreach ($models as $modelName) {
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/'
        . rawurlencode($modelName)
        . ':generateContent?key='
        . rawurlencode($key);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 12,
    ]);
    $response = curl_exec($ch);
    $curlErr = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        echo "{$modelName} => CURL {$curlErr}\n";
        continue;
    }
    $decoded = json_decode((string) $response, true);
    if ($status >= 400) {
        $msg = is_array($decoded) ? (string) ($decoded['error']['message'] ?? ('HTTP ' . $status)) : ('HTTP ' . $status);
        // Ne pas afficher de détails trop longs
        $msg = mb_substr(preg_replace('/\s+/', ' ', $msg) ?? $msg, 0, 160);
        echo "{$modelName} => FAIL {$status} | {$msg}\n";
        continue;
    }
    $text = tcf_gemini_extract_text(is_array($decoded) ? $decoded : null);
    $finish = (string) ($decoded['candidates'][0]['finishReason'] ?? '');
    echo "{$modelName} => OK text_len=" . strlen($text) . " finish={$finish} preview=" . mb_substr($text, 0, 40) . "\n";
}
