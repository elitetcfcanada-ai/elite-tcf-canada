<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

header('Content-Type: application/json; charset=utf-8');

function assistant_json(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function assistant_api_key(): string
{
    $localFile = __DIR__ . '/includes/gemini_key.php';
    if (is_file($localFile)) {
        $fromFile = include $localFile;
        if (is_string($fromFile) && trim($fromFile) !== '') {
            return trim($fromFile);
        }
    }

    $key = trim((string) getenv('GEMINI_API_KEY'));
    if ($key !== '') {
        return $key;
    }
    $serverKey = trim((string) ($_SERVER['GEMINI_API_KEY'] ?? ''));
    if ($serverKey !== '') {
        return $serverKey;
    }

    return '';
}

/**
 * Appel Gemini avec bascule de modèles + correction des rôles.
 * @param list<array{role:string,parts:list<array{text:string}>}> $contents
 * @return array{ok:bool,text?:string,error?:string}
 */
function assistant_call_gemini(string $apiKey, string $systemInstruction, array $contents): array
{
    $body = [
        'systemInstruction' => [
            'parts' => [['text' => $systemInstruction]],
        ],
        'contents' => $contents,
        'generationConfig' => [
            'temperature' => 0.4,
            'topP' => 0.9,
            'maxOutputTokens' => 900,
        ],
    ];

    $modelsToTry = [
        'gemini-2.5-flash',
        'gemini-2.0-flash',
        'gemini-2.0-flash-001',
        'gemini-flash-latest',
    ];

    $lastError = '';

    foreach ($modelsToTry as $modelName) {
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
            CURLOPT_TIMEOUT => 35,
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
                $lastError = 'Clé Gemini invalide ou compromise. Créez une nouvelle clé sur Google AI Studio et mettez-la dans includes/gemini_key.php (ou GEMINI_API_KEY).';
            } elseif (str_contains($lower, 'quota') || str_contains($lower, 'rate limit') || $status === 429) {
                $lastError = 'Quota Gemini dépassé. Réessayez plus tard ou activez la facturation / un autre projet API.';
            } elseif (str_contains($lower, 'not found') || $status === 404) {
                $lastError = 'Modèle indisponible: ' . $modelName;
            } else {
                $lastError = $rawMsg;
            }
            continue;
        }

        $text = '';
        $parts = $decoded['candidates'][0]['content']['parts'] ?? null;
        if (is_array($parts)) {
            foreach ($parts as $part) {
                $textPart = trim((string) ($part['text'] ?? ''));
                if ($textPart !== '') {
                    $text .= ($text !== '' ? "\n\n" : '') . $textPart;
                }
            }
        }

        if ($text === '') {
            $block = (string) ($decoded['candidates'][0]['finishReason'] ?? '');
            $lastError = $block !== ''
                ? 'Réponse vide (finishReason: ' . $block . ').'
                : 'Aucune réponse générée pour le moment.';
            continue;
        }

        return ['ok' => true, 'text' => $text];
    }

    return ['ok' => false, 'error' => ($lastError !== '' ? $lastError : 'Tous les modèles Gemini ont échoué.')];
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    assistant_json(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
}

$role = (string) ($_SESSION['role'] ?? '');
$isAdminStaff = in_array($role, ['admin', 'super_admin'], true);
if (empty($_SESSION['user_id'])) {
    assistant_json(['ok' => false, 'message' => 'Connexion requise pour utiliser l’assistant.'], 401);
}

$raw = (string) file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) {
    assistant_json(['ok' => false, 'message' => 'Requête invalide.'], 400);
}

$message = trim((string) ($payload['message'] ?? ''));
if ($message === '' || mb_strlen($message) < 2) {
    assistant_json(['ok' => false, 'message' => 'Question trop courte.'], 422);
}
if (mb_strlen($message) > 1500) {
    assistant_json(['ok' => false, 'message' => 'Question trop longue.'], 422);
}

$history = $payload['history'] ?? [];
if (!is_array($history)) {
    $history = [];
}

$apiKey = assistant_api_key();
if ($apiKey === '') {
    assistant_json([
        'ok' => false,
        'message' => 'Clé Gemini manquante. Configurez includes/gemini_key.php ou GEMINI_API_KEY.',
    ], 500);
}

$systemInstruction = $isAdminStaff
    ? "Rôle: assistant administration ELITE TCF CANADA (tableau de bord admin / super admin).\n"
        . "Objectif: aider les administrateurs à utiliser la plateforme (gestion utilisateurs, vidéos, abonnements, revenus, témoignages, annonces, sujets TCF).\n"
        . "Règles:\n"
        . "- Réponds en français, ton professionnel et concis.\n"
        . "- Donne des étapes claires dans l'interface admin (menus, sections).\n"
        . "- Ne demande jamais de mots de passe ni de données sensibles.\n"
        . "- Si tu n'es pas certain, dis-le et propose une vérification manuelle.\n"
    : "Rôle: assistant officiel du site ELITE TCF CANADA (plateforme de préparation au TCF Canada).\n"
        . "Objectif: répondre efficacement aux questions de préparation au TCF Canada et à l'utilisation du site ELITE TCF CANADA.\n"
        . "Règles:\n"
        . "- Réponds en français, ton professionnel et chaleureux.\n"
        . "- Donne des réponses actionnables (étapes, checklist, exemples).\n"
        . "- Si la question est ambiguë: pose 1 question de clarification, puis propose une réponse provisoire.\n"
        . "- Si la demande concerne des données personnelles, mots de passe, paiement: explique la limite et oriente vers Support.\n"
        . "- Ne fabrique pas d'informations non sûres; si tu n'es pas certain, dis-le et propose une alternative.\n"
        . "Pages utiles du site: Vidéos, Compréhension écrite/orale, Expression écrite/orale, Support, Connexion/Inscription.\n";

/* Historique propre : rôles alternés user/model (exigence Gemini) */
$contents = [];
$maxHist = 12;
$slice = array_slice($history, max(0, count($history) - $maxHist));
foreach ($slice as $h) {
    if (!is_array($h)) {
        continue;
    }
    $r = (string) ($h['role'] ?? '');
    $t = trim((string) ($h['text'] ?? ''));
    if ($t === '' || mb_strlen($t) > 1500) {
        continue;
    }
    $geminiRole = ($r === 'bot' || $r === 'model' || $r === 'assistant') ? 'model' : 'user';
    $last = $contents[count($contents) - 1] ?? null;
    if ($last && ($last['role'] ?? '') === $geminiRole) {
        /* Fusionner si même rôle consécutif */
        $contents[count($contents) - 1]['parts'][0]['text'] .= "\n" . $t;
        continue;
    }
    $contents[] = [
        'role' => $geminiRole,
        'parts' => [['text' => $t]],
    ];
}

/* La dernière entrée avant la question doit être model ou vide — jamais 2 user d’affilée */
$last = $contents[count($contents) - 1] ?? null;
if ($last && ($last['role'] ?? '') === 'user') {
    $contents[] = [
        'role' => 'model',
        'parts' => [['text' => 'D’accord, je vous écoute.']],
    ];
}

$contents[] = [
    'role' => 'user',
    'parts' => [['text' => $message]],
];

$result = assistant_call_gemini($apiKey, $systemInstruction, $contents);
if (empty($result['ok'])) {
    assistant_json([
        'ok' => false,
        'message' => (string) ($result['error'] ?? 'Erreur Gemini.'),
    ], 502);
}

assistant_json(['ok' => true, 'reply' => (string) $result['text']]);
