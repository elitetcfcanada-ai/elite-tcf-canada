<?php
declare(strict_types=1);

/**
 * Installe / met à jour includes/gemini_key.php sur le serveur.
 * Web (POST JSON) : /scripts/set_gemini_key.php?key=REPAIR_TCF_2026
 * Body : {"api_key":"AIza..."}
 * Ne renvoie jamais la clé.
 */

header('Content-Type: application/json; charset=utf-8');

if ((string) ($_GET['key'] ?? '') !== 'REPAIR_TCF_2026') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Forbidden']);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'POST only']);
    exit;
}

$raw = (string) file_get_contents('php://input');
$payload = json_decode($raw, true);
$apiKey = '';
if (is_array($payload)) {
    $apiKey = trim((string) ($payload['api_key'] ?? ''));
}
if ($apiKey === '') {
    $apiKey = trim((string) ($_POST['api_key'] ?? ''));
}

if ($apiKey === '' || !preg_match('/^AIza[0-9A-Za-z_\-]{20,}$/', $apiKey)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Clé Gemini invalide (format).']);
    exit;
}

$target = dirname(__DIR__) . '/includes/gemini_key.php';
$content = "<?php\ndeclare(strict_types=1);\n\nreturn " . var_export($apiKey, true) . ";\n";

if (@file_put_contents($target, $content) === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Impossible d’écrire gemini_key.php (droits).']);
    exit;
}

@chmod($target, 0640);

echo json_encode(['ok' => true, 'message' => 'Clé Gemini installée.']);
