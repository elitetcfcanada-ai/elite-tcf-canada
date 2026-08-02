<?php
declare(strict_types=1);

/**
 * Envoie la clé locale vers Hostinger (ne l’affiche jamais).
 * CLI : php scripts/sync_gemini_key_to_prod.php
 */

require_once __DIR__ . '/../includes/gemini_client.php';

$apiKey = tcf_gemini_api_key();
if ($apiKey === '' || strlen($apiKey) < 20 || preg_match('/\s/', $apiKey)) {
    fwrite(STDERR, "LOCAL_KEY=missing_or_invalid\n");
    exit(1);
}

$url = 'https://elitetcfcanada.online/scripts/set_gemini_key.php?key=REPAIR_TCF_2026';
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode(['api_key' => $apiKey], JSON_UNESCAPED_UNICODE),
    CURLOPT_TIMEOUT => 45,
    CURLOPT_CONNECTTIMEOUT => 20,
]);
$res = curl_exec($ch);
$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

if ($res === false) {
    echo "SYNC_FAIL curl={$err}\n";
    exit(1);
}

echo "http={$code}\n";
$data = json_decode((string) $res, true);
if (is_array($data)) {
    echo 'ok=' . (!empty($data['ok']) ? 'yes' : 'no') . "\n";
    echo 'message=' . (string) ($data['message'] ?? '') . "\n";
    exit(!empty($data['ok']) ? 0 : 1);
}

echo 'raw=' . mb_substr((string) $res, 0, 200) . "\n";
exit(1);
