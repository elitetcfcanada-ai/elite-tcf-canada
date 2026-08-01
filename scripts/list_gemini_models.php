<?php
declare(strict_types=1);

/**
 * Liste les modèles Gemini accessibles (sans afficher la clé).
 * CLI : php scripts/list_gemini_models.php
 */

require_once __DIR__ . '/../includes/gemini_client.php';

$key = tcf_gemini_api_key();
if ($key === '') {
    echo "NO_KEY\n";
    exit(1);
}

$url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . rawurlencode($key);
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 25,
]);
$res = curl_exec($ch);
$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

echo "http={$code}\n";
if ($res === false) {
    echo "curl_error={$err}\n";
    exit(1);
}

$data = json_decode((string) $res, true);
if (!is_array($data)) {
    echo "invalid_json\n";
    echo mb_substr((string) $res, 0, 300) . "\n";
    exit(1);
}
if (isset($data['error'])) {
    echo 'api_error=' . (string) ($data['error']['message'] ?? 'unknown') . "\n";
    exit(1);
}

$names = [];
foreach (($data['models'] ?? []) as $m) {
    $name = (string) ($m['name'] ?? '');
    $methods = $m['supportedGenerationMethods'] ?? [];
    if (!is_array($methods) || !in_array('generateContent', $methods, true)) {
        continue;
    }
    $short = preg_replace('#^models/#', '', $name) ?: $name;
    $names[] = $short;
}
sort($names);
echo "count=" . count($names) . "\n";
foreach ($names as $n) {
    // Priorité visuelle : flash / gemini
    if (stripos($n, 'flash') !== false || stripos($n, 'gemini') !== false) {
        echo $n . "\n";
    }
}
