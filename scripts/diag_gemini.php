<?php
declare(strict_types=1);

/**
 * Diagnostic Gemini (aucune clé affichée).
 * Web : /scripts/diag_gemini.php?key=REPAIR_TCF_2026
 */

$cli = PHP_SAPI === 'cli';
if (!$cli && (string) ($_GET['key'] ?? '') !== 'REPAIR_TCF_2026') {
    http_response_code(403);
    echo "Forbidden\n";
    exit;
}

header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/../includes/gemini_client.php';

$key = tcf_gemini_api_key();
echo 'key_configured=' . ($key !== '' ? 'yes' : 'no') . "\n";
echo 'curl=' . (function_exists('curl_init') ? 'yes' : 'no') . "\n";

if ($key === '') {
    echo "status=NO_KEY\n";
    exit(1);
}

$err = '';
$body = [
    'contents' => [[
        'role' => 'user',
        'parts' => [['text' => 'Réponds uniquement par le mot OK.']],
    ]],
    'generationConfig' => [
        'temperature' => 0,
        'maxOutputTokens' => 32,
    ],
];
$decoded = tcf_gemini_generate($body, $key, $err, 25);
if (!is_array($decoded)) {
    echo "status=FAIL\n";
    echo 'error=' . $err . "\n";
    exit(1);
}

$text = tcf_gemini_extract_text($decoded);
echo "status=OK\n";
echo 'reply_preview=' . mb_substr($text, 0, 80) . "\n";
